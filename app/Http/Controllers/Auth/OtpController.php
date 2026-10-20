<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function show(Request $request)
    {
        $email = $request->session()->get('verify_email');
        if (!$email) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', ['email' => $email]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->session()->get('verify_email');
        $purpose = $request->session()->get('verify_purpose');

        if (!$email || !$purpose) {
            return redirect()->route('login');
        }

        try {
            $this->otpService->verify($email, $request->code, $purpose);

            $user = User::where('email', $email)->first();
            
            if ($purpose === 'registration') {
                $user->update(['email_verified_at' => now()]);
                
                // Clear slug reservation if they registered as a vendor
                if ($user->vendor) {
                    $user->vendor->update(['slug_reserved_at' => null]);
                }

                // Merge guest cart before session regenerate
                \App\Models\Cart::mergeGuestCart($user->id, $request->session()->getId());
                
                // Persistent login across browser sessions
                Auth::login($user, true);
                
                $loginRole = $request->session()->get('login_role');
                $request->session()->forget(['verify_email', 'verify_purpose', 'dev_otp_code', 'login_role']);
                
                // Clean up intended URL: NEVER route public/vendor users to /admin
                if ($request->session()->has('url.intended')) {
                    $intended = $request->session()->get('url.intended');
                    if (str_contains($intended, '/admin')) {
                        $request->session()->forget('url.intended');
                    }
                }

                // Check if vendor
                $isVendor = ($loginRole === 'vendor') || $user->hasRole('vendor') || ($user->vendor !== null);

                if ($isVendor && $loginRole !== 'customer') {
                    // If vendor had an intended vendor URL (e.g. /vendor/products), respect it
                    if ($request->session()->has('url.intended')) {
                        $intended = $request->session()->get('url.intended');
                        if (str_contains($intended, '/vendor/')) {
                            return redirect()->intended(route('vendor.dashboard'))->with('success', 'Email verified successfully!');
                        }
                        $request->session()->forget('url.intended');
                    }
                    return redirect()->route('vendor.dashboard')->with('success', 'Email verified successfully!');
                }

                // Customer flow: clear any vendor intended URL
                if ($request->session()->has('url.intended')) {
                    $intended = $request->session()->get('url.intended');
                    if (str_contains($intended, '/vendor/')) {
                        $request->session()->forget('url.intended');
                    }
                }

                return redirect()->intended(route('home'))->with('success', 'Email verified successfully!');
            }
            
            // Handle other purposes like password reset here if added later
            if ($request->session()->has('url.intended') && str_contains($request->session()->get('url.intended'), '/admin')) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }

    public function resend(Request $request)
    {
        $email = $request->session()->get('verify_email');
        $purpose = $request->session()->get('verify_purpose');

        if (!$email || !$purpose) {
            return response()->json(['error' => 'Session expired'], 400);
        }

        try {
            $user = User::where('email', $email)->first();
            $this->otpService->generateAndSend($email, $purpose, $user?->id);
            
            $response = ['message' => 'Code sent successfully'];
            if (app()->environment('local')) {
                $response['dev_otp_code'] = session('dev_otp_code');
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 429);
        }
    }
}
