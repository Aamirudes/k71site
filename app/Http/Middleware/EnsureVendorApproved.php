<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->vendor) {
            $vendor = auth()->user()->vendor;
            if ($vendor->status !== 'approved') {
                return redirect()->route('vendor.dashboard')->with('error', 'Your store is pending approval or suspended. You cannot perform this action yet.');
            }
        }
        return $next($request);
    }
}
