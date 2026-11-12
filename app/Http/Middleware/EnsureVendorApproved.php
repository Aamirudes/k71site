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
            if (!in_array($vendor->status, ['approved', 'active'], true) || in_array($vendor->verification_status, ['rejected', 'suspended'], true)) {
                return redirect()->route('vendor.dashboard')->with('error', 'Your store is pending verification or suspended. You cannot perform this action yet.');
            }
        }
        return $next($request);
    }
}
