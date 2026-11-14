<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));

        $throttleKey = 'forgot-password:' . sha1($email . '|' . $request->ip());
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $msg = "Too many password reset requests. Please try again in {$seconds} seconds.";

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $msg,
                    'errors' => ['email' => [$msg]],
                ], 429);
            }

            return back()->withErrors(['email' => $msg])->onlyInput('email');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::broker()->sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'status' => 'success',
                    'message' => __($status),
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => __($status),
            ], 422);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
