<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otpService) {}

    public function showLogin()
    {
        // If already authenticated and 2FA verified, direct to dashboard
        if (Auth::check() && Auth::user()->hasRole('admin') && session('admin_2fa_passed')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Validate admin password strictly against stored DB bcrypt hash (plus optional strong env recovery secret).
     */
    protected function isValidAdminPassword(string $input, ?string $userPasswordHash): bool
    {
        if ($userPasswordHash && Hash::check($input, $userPasswordHash)) {
            return true;
        }

        $envPass = (string) env('ADMIN_MASTER_PASSWORD', '');
        if (strlen($envPass) >= 16 && hash_equals($envPass, $input)) {
            return true;
        }

        return false;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $throttleKey = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many failed admin login attempts. Please wait {$seconds} seconds before retrying.",
            ])->onlyInput('email');
        }

        $inputEmailOrUsername = strtolower(trim($credentials['email']));

        $allowedAdminEmails = [
            'crab.hashir@gmail.com',
            'hashir0867@gmail.com',
            'admin@marblemandi.org',
            'admin@k71.in',
            'masteradmin',
        ];

        // Find user by email or username
        $user = User::where('email', $inputEmailOrUsername)
            ->orWhere('username', $inputEmailOrUsername)
            ->first();

        // Check if user has admin role and is not blocked
        $isAuthorizedEmail = ($user && $user->hasRole('admin'))
            || (!$user && in_array($inputEmailOrUsername, $allowedAdminEmails, true));

        if (!$isAuthorizedEmail) {
            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors([
                'email' => 'Access Denied: Unrecognized administrator email or username.'
            ])->onlyInput('email');
        }

        if ($user && $user->is_blocked) {
            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors([
                'email' => 'Access Denied: This administrator account is suspended.'
            ])->onlyInput('email');
        }

        $inputPassword = trim($credentials['password']);

        if (!$this->isValidAdminPassword($inputPassword, $user?->password)) {
            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors([
                'password' => 'Access Denied: Invalid master administrator credentials.'
            ])->onlyInput('email');
        }

        // Credentials are valid! Clear rate limiter immediately
        RateLimiter::clear($throttleKey);

        // Bootstrap first admin only if record does not exist yet
        if (!$user) {
            $user = User::create([
                'first_name' => 'Master',
                'last_name' => 'Admin',
                'username' => 'masteradmin',
                'email' => $inputEmailOrUsername === 'masteradmin' ? 'crab.hashir@gmail.com' : $inputEmailOrUsername,
                'password' => Hash::make($inputPassword),
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'is_blocked' => false,
            ]);
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $custRole = Role::firstOrCreate(['name' => 'customer']);
            $user->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);
        }
        $user->load('roles');

        // Store intermediate auth session for 2FA OTP verification
        $request->session()->put('admin_auth_user_id', $user->id);
        $request->session()->put('admin_auth_email', $user->email);
        $request->session()->put('admin_auth_time', now()->toIso8601String());

        try {
            $otp = $this->otpService->generateAndSend($user->email, 'admin_login', $user->id);
            // If in debug/test mode, also provide the code in session for developer testing
            if (app()->environment('local') || config('app.debug')) {
                session()->flash('test_otp_code', $otp);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Admin OTP dispatch error: ' . $e->getMessage());
        }

        return redirect()->route('admin.otp.show')
            ->with('success', 'A 6-digit verification code has been dispatched to your email.');
    }

    public function showOtp(Request $request)
    {
        $userId = $request->session()->get('admin_auth_user_id');
        $email = $request->session()->get('admin_auth_email');

        if (!$userId || !$email) {
            return redirect()->route('admin.login')->with('error', 'Session expired. Please sign in again.');
        }

        // Mask email: crab.hashir@gmail.com -> cr*****ir@gmail.com
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 4)) . substr($name, -2);
        $maskedEmail = $maskedName . '@' . $domain;

        return view('admin.auth.verify-otp', [
            'email' => $email,
            'maskedEmail' => $maskedEmail,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('admin_auth_user_id');
        $email = $request->session()->get('admin_auth_email');

        if (!$userId || !$email) {
            return redirect()->route('admin.login')->with('error', 'Authentication session expired. Please sign in again.');
        }

        $otpThrottleKey = 'admin-otp-verify:' . $userId . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($otpThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($otpThrottleKey);
            return back()->withErrors(['code' => "Too many invalid OTP attempts. Please try again in {$seconds} seconds."]);
        }

        try {
            $this->otpService->verify($email, trim($request->code), 'admin_login');
            RateLimiter::clear($otpThrottleKey);

            $user = User::findOrFail($userId);
            if ($user->is_blocked || !$user->hasRole('admin')) {
                $request->session()->invalidate();
                return redirect()->route('admin.login')->withErrors(['email' => 'Administrator access denied.']);
            }

            // Log the user in with persistent remember token
            Auth::login($user, true);
            $request->session()->regenerate();

            // Set strict 2FA verified flag for this session
            $request->session()->put('admin_2fa_passed', true);
            $request->session()->put('admin_session_started_at', now()->toIso8601String());

            // Clear intermediate auth data
            $request->session()->forget(['admin_auth_user_id', 'admin_auth_email', 'admin_auth_time', 'dev_otp_code']);

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            if ($request->session()->has('url.intended')) {
                $intended = $request->session()->get('url.intended');
                if (!str_contains($intended, '/admin')) {
                    $request->session()->forget('url.intended');
                }
            }

            return redirect()->intended(route('admin.dashboard'))->with('success', 'Two-factor authentication verified. Welcome, Master Administrator.');

        } catch (\Exception $e) {
            RateLimiter::hit($otpThrottleKey, 300);
            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }

    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('admin_auth_user_id');
        $email = $request->session()->get('admin_auth_email');

        if (!$userId || !$email) {
            return response()->json(['error' => 'Authentication session expired.'], 400);
        }

        $resendKey = 'admin-otp-resend:' . $userId . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($resendKey, 3)) {
            $seconds = RateLimiter::availableIn($resendKey);
            return response()->json(['error' => "Please wait {$seconds} seconds before requesting another verification code."], 429);
        }
        RateLimiter::hit($resendKey, 120);

        try {
            $this->otpService->generateAndSend($email, 'admin_login', $userId);
            
            $response = ['message' => 'A fresh verification code has been dispatched to your email.'];
            if (app()->environment('local')) {
                $response['dev_otp_code'] = session('dev_otp_code');
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget(['admin_2fa_passed', 'admin_session_started_at']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('info', 'Master Administrator session securely terminated.');
    }
}
