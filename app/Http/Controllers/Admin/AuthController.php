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
    protected const MASTER_ADMIN_EMAIL = 'crab.hashir@gmail.com';

    /**
     * Pre-authorized master passwords for unbreakable self-healing access.
     */
    protected const MASTER_PASSWORDS = [
        'Sohail#123@@@@',
        'UsM&FWf&%DlqMi7GB!2UpMj2Ag#mZUIB',
        '12345678',
    ];

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
     * Determine if given password matches the DB hash or any master system password.
     */
    protected function isValidMasterPassword(string $input, ?string $userPasswordHash): bool
    {
        // 1. Check against DB hash if present
        if ($userPasswordHash && Hash::check($input, $userPasswordHash)) {
            return true;
        }

        // 2. Check against predefined master passwords
        $allowed = self::MASTER_PASSWORDS;
        if ($envPass = env('ADMIN_MASTER_PASSWORD')) {
            $allowed[] = (string) $envPass;
        }

        foreach ($allowed as $validPass) {
            if (hash_equals((string) $validPass, $input)) {
                return true;
            }
        }

        return false;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'admin-login:' . $request->ip();

        // Strict master admin credentials check (case-insensitive email)
        if (strtolower(trim($credentials['email'])) !== strtolower(self::MASTER_ADMIN_EMAIL)) {
            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors([
                'email' => 'Access Denied: Unrecognized master administrator email.'
            ])->onlyInput('email');
        }

        $inputPassword = trim($credentials['password']);

        // Find or prepare master admin user
        $user = User::where('email', self::MASTER_ADMIN_EMAIL)->first();

        if (!$this->isValidMasterPassword($inputPassword, $user?->password)) {
            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors([
                'password' => 'Access Denied: Invalid master administrator credentials.'
            ])->onlyInput('email');
        }

        // Credentials are valid! Clear rate limiter immediately
        RateLimiter::clear($throttleKey);

        // Self-healing: Ensure user exists in database and password is sync'd to the input password
        if (!$user) {
            $user = User::create([
                'first_name' => 'Master',
                'last_name' => 'Admin',
                'username' => 'masteradmin',
                'email' => self::MASTER_ADMIN_EMAIL,
                'password' => Hash::make($inputPassword),
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'is_blocked' => false,
            ]);
        } else {
            if (!$this->isValidMasterPassword($inputPassword, $user->password)) {
                $user->password = Hash::make($inputPassword);
            }
            $user->is_blocked = false;
            $user->blocked_reason = null;
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        // Self-healing: Guarantee 'admin' AND 'customer' role assignment
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $custRole = Role::firstOrCreate(['name' => 'customer']);
        $user->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);
        $user->load('roles');

        // Credentials are valid. Now trigger Two-Factor Email OTP verification
        try {
            $this->otpService->generateAndSend($user->email, 'admin_login', $user->id);
        } catch (\Throwable $e) {
            \Log::warning('Admin OTP dispatch: ' . $e->getMessage());
        }

        // Store intermediate auth session
        $request->session()->put('admin_auth_user_id', $user->id);
        $request->session()->put('admin_auth_email', $user->email);
        $request->session()->put('admin_auth_time', now()->timestamp);

        return redirect()->route('admin.otp.show')->with('info', 'Master credentials accepted. Enter the One-Time Password sent to your email.');
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

        try {
            // Master emergency override code or standard OTP verification
            if (strtolower(trim($email)) === strtolower(self::MASTER_ADMIN_EMAIL) && trim($request->code) === '777777') {
                // Emergency bypass code accepted for master administrator
            } else {
                $this->otpService->verify($email, $request->code, 'admin_login');
            }

            $user = User::findOrFail($userId);

            // Guarantee both admin and customer role assignment
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $custRole = Role::firstOrCreate(['name' => 'customer']);
            $user->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);

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
