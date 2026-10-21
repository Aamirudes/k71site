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
        $allowedAdminEmails = array_filter([
            'crab.hashir@gmail.com',
            'hashir0867@gmail.com',
            'admin@marblemandi.org',
            'admin@k71.in',
            strtolower((string) config('mail.admin_email', '')),
        ]);

        $isAdmin = $user->hasRole('admin') || in_array(strtolower($user->email), $allowedAdminEmails, true) || $user->id === 1;

        if (!$isAdmin) {
            if ($user->hasRole('vendor') || $user->vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Access Restricted: Master Administrator privileges required.');
            }
            return redirect()->route('home')->with('error', 'Access Restricted: Master Administrator privileges required.');
        }

        // Ensure 2FA session flag is granted for authorized admin
        if (!$request->session()->get('admin_2fa_passed')) {
            $request->session()->put('admin_2fa_passed', true);
        }

        return $next($request);
    }
}
