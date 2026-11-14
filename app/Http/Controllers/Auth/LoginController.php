<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:customer,vendor,fleet'],
        ]);

        $rawLogin = trim($credentials['login']);
        $normalizedLogin = strtolower($rawLogin);
        $throttleKey = 'login:' . sha1($normalizedLogin . '|' . $request->ip());

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $msg = "Too many sign-in attempts. Please try again in {$seconds} seconds.";

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['login' => [$msg]]], 429);
            }
            return back()->withErrors(['login' => $msg])->onlyInput('login', 'role');
        }

        $digitsOnly = preg_replace('/[^0-9]/', '', $rawLogin);
        $inputPassword = trim($credentials['password']);
        $preLoginSessionId = $request->session()->getId();
        $preLoginWishlist = (array) $request->session()->get('guest_wishlist', []);

        $authenticated = false;

        // 1. Standard email or username attempt (case-insensitive normalization)
        $loginType = filter_var($rawLogin, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (
            Auth::attempt([$loginType => $normalizedLogin, 'password' => $inputPassword], true) ||
            ($normalizedLogin !== $rawLogin && Auth::attempt([$loginType => $rawLogin, 'password' => $inputPassword], true))
        ) {
            $authenticated = true;
        }

        // 2. Mobile number attempt (supports 10-digit, +91, with spaces or dashes)
        if (!$authenticated && strlen($digitsOnly) >= 10) {
            $last10 = substr($digitsOnly, -10);
            $candidatePhones = [
                $rawLogin,
                $digitsOnly,
                $last10,
                '+91' . $last10,
                '91' . $last10,
            ];

            $phoneUser = \App\Models\User::whereIn('phone', $candidatePhones)->first();
            if ($phoneUser && \Illuminate\Support\Facades\Hash::check($inputPassword, $phoneUser->password)) {
                Auth::login($phoneUser, true);
                $authenticated = true;
            }
        }

        // 3. Username direct lookup fallback
        if (!$authenticated) {
            $usernameUser = \App\Models\User::where('username', $normalizedLogin)->first();
            if ($usernameUser && \Illuminate\Support\Facades\Hash::check($inputPassword, $usernameUser->password)) {
                Auth::login($usernameUser, true);
                $authenticated = true;
            }
        }

        // Always remember authentication so user stays logged in across browser restarts
        if ($authenticated) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $user = Auth::user();

            if ($user->is_blocked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This account has been blocked.',
                        'errors' => ['login' => ['This account has been blocked.']],
                    ], 403);
                }
                return back()->withErrors(['login' => 'This account has been blocked.']);
            }

            // Prevent session fixation while preserving guest cart items
            $request->session()->regenerate();

            // Ensure customer role is assigned for customer logins
            if ($credentials['role'] === 'customer' && !$user->hasRole('customer')) {
                $custRole = \App\Models\Role::firstOrCreate(['name' => 'customer']);
                $user->roles()->syncWithoutDetaching([$custRole->id]);
            }

            // Merge guest cart items captured BEFORE session regeneration
            if (class_exists(\App\Models\Cart::class) && method_exists(\App\Models\Cart::class, 'mergeGuestCart')) {
                \App\Models\Cart::mergeGuestCart($user->id, $preLoginSessionId);
            }

            // Merge guest wishlist items captured BEFORE session regeneration
            if (!empty($preLoginWishlist) && class_exists(\App\Models\Wishlist::class)) {
                $visibility = app(\App\Services\Monetization\VisibilityService::class);
                foreach ($preLoginWishlist as $pid) {
                    $candidate = \App\Models\Product::withoutGlobalScopes()->find((int) $pid);
                    if ($candidate && $visibility->isProductPubliclyVisible($candidate)) {
                        \App\Models\Wishlist::firstOrCreate([
                            'user_id' => $user->id,
                            'product_id' => (int) $pid,
                        ]);
                    }
                }
                $request->session()->forget('guest_wishlist');
            }

            if (!$user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Ensure any intended URL pointing to admin is cleared immediately for public logins
            if ($request->session()->has('url.intended')) {
                $intended = $request->session()->get('url.intended');
                if (str_contains($intended, '/admin')) {
                    $request->session()->forget('url.intended');
                }
            }

            if ($request->expectsJson() || $request->wantsJson()) {
                $jsonRedirect = match ($credentials['role']) {
                    'vendor' => route('vendor.dashboard'),
                    'fleet' => $user->isFleet() ? route('delivery.dashboard') : route('delivery.onboarding'),
                    default => null,
                };
                return response()->json([
                    'success' => true,
                    'message' => 'Signed in successfully!',
                    'csrf_token' => csrf_token(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $credentials['role'],
                    ],
                    'redirect' => $jsonRedirect,
                ]);
            }

            // Role toggle logic
            if ($credentials['role'] === 'vendor') {
                if (!$user->hasRole(UserRole::Vendor->value) && !$user->vendor) {
                    return redirect()->route('vendor.register')->with('info', 'Complete your vendor profile to continue.');
                }

                // If vendor had an intended vendor page (like /vendor/products), take them there
                if ($request->session()->has('url.intended')) {
                    $intended = $request->session()->get('url.intended');
                    if (str_contains($intended, '/vendor/')) {
                        return redirect()->intended(route('vendor.dashboard'));
                    }
                    $request->session()->forget('url.intended');
                }

                return redirect()->route('vendor.dashboard');
            }

            if ($credentials['role'] === 'fleet') {
                $hasFleetPartner = !$user->isAdmin() && !$user->isVendor()
                    && ($user->isFleet() || \App\Models\DeliveryPartner::where('user_id', $user->id)->exists());

                if ($request->session()->has('url.intended')) {
                    $request->session()->forget('url.intended');
                }

                if (!$hasFleetPartner) {
                    return redirect()->route('delivery.onboarding')
                        ->with('info', 'Complete your Fleet Partner profile to access the Fleet Dashboard.');
                }

                $partner = \App\Models\DeliveryPartner::where('user_id', $user->id)->first();
                if ($partner) {
                    session(['delivery_partner_id' => $partner->id]);
                }

                return redirect()->route('delivery.dashboard');
            }

            // If logging in as customer, clear any stale vendor or fleet intended URLs
            if ($request->session()->has('url.intended')) {
                $intended = $request->session()->get('url.intended');
                if (str_contains($intended, '/vendor/') || str_contains($intended, '/delivery/')) {
                    $request->session()->forget('url.intended');
                }
            }

            return redirect()->intended(route('home'));
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 120);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
                'errors' => ['login' => ['The provided credentials do not match our records.']]
            ], 422);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login', 'role');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
