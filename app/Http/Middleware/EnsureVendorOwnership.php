<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Must be authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 2. Must be a vendor
        if (!$user->hasRole('vendor') || !$user->vendor) {
            return redirect()->route('vendor.register');
        }

        // 3. Must own the current storefront if we are on a subdomain/tenant path
        $currentVendor = $request->attributes->get('current_vendor');
        if ($currentVendor && $currentVendor->id !== $user->vendor->id) {
            abort(403, 'You do not have permission to manage this store.');
        }

        // 4. Mark vendor_context as true to activate tenant VendorScope isolation
        $request->attributes->set('vendor_context', true);

        return $next($request);
    }
}
