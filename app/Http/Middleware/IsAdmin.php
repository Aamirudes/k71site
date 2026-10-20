<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Master administrator sign-in required.');
        }

        $user = Auth::user();

        $masterEmail = strtolower(config('mail.admin_email', 'crab.hashir@gmail.com'));

        // Enforce exact master admin account
        if (!$user->hasRole('admin') || !in_array(strtolower($user->email), [$masterEmail, 'crab.hashir@gmail.com'], true)) {
            // Do not log the user out; redirect them to their rightful dashboard
            if ($user->hasRole('vendor') || $user->vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Access Restricted: Only the Master Administrator can access the Master Admin Console.');
            }

            return redirect()->route('home')->with('error', 'Access Restricted: Only the Master Administrator can access the Master Admin Console.');
        }

        // Enforce Two-Factor OTP verification for the session
        if (!$request->session()->get('admin_2fa_passed')) {
            return redirect()->route('admin.login')->with('error', 'Two-Factor OTP verification is required to access the Master Admin Console.');
        }

        return $next($request);
    }
}
