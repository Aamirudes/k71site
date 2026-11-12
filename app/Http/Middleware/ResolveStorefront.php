<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStorefront
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $useSubdomains = config('marketplace.use_subdomains', true);
        $host = strtolower($request->getHost());
        $extractedSlug = k71_extract_subdomain($host);
        $slug = null;

        if ($useSubdomains && $extractedSlug) {
            $rootDomain = k71_root_domain($host);
            $port = $request->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
            $scheme = (app()->environment('production') || $request->secure()) ? 'https' : 'http';

            // 1. Admin subdomain (admin.mysite.in -> /admin)
            if ($extractedSlug === 'admin') {
                if (!$request->is('admin*')) {
                    return redirect()->to("{$scheme}://{$rootDomain}{$portSuffix}/admin");
                }
                return $next($request);
            }

            // 2. Vendor Portal subdomain (vendor.mysite.in, vendor.k71.in, vendor.localhost)
            if (in_array($extractedSlug, ['vendor', 'vendors', 'seller', 'sellers'])) {
                if ($request->path() === '/') {
                    if (auth()->check() && auth()->user()->hasRole('vendor')) {
                        return redirect()->to("{$scheme}://{$host}{$portSuffix}/vendor/dashboard");
                    }
                    return redirect()->to("{$scheme}://{$host}{$portSuffix}/sell");
                }
                return $next($request);
            }

            // 3. Vertical subdomains (food, services, used, enquiry, shorts, feasto, b2b) are platform hubs
            if (in_array($extractedSlug, ['feasto', 'feato', 'food', 'services', 'used', 'enquiry', 'b2b', 'shorts']) || array_key_exists($extractedSlug, config('k71.verticals', []))) {
                return $next($request);
            }

            // 4. www points to main marketplace
            if ($extractedSlug !== 'www') {
                $slug = $extractedSlug;
            }
        }

        // Support path-based tenancy as fallback (/s/{shop-slug})
        if (!$slug) {
            $prefix = config('marketplace.storefront_path_prefix', 's');
            if ($request->is("$prefix/*")) {
                $slug = $request->segment(2); // /s/shop1 -> segment 2 is shop1
            }
        }

        if ($slug) {
            $lowerSlug = strtolower($slug);

            // 1. Allow vertical platform hubs through without treating as vendor stores
            if (in_array($lowerSlug, ['feasto', 'feato', 'food', 'services', 'used', 'enquiry', 'b2b', 'shorts']) || array_key_exists($lowerSlug, config('k71.verticals', []))) {
                return $next($request);
            }

            // 2. Allow vendor / seller portal routes through
            if (in_array($lowerSlug, ['vendor', 'vendors', 'seller', 'sellers', 'admin'])) {
                return $next($request);
            }

            // Check if this slug is reserved
            $subdomainManager = app(\App\Services\Services\SubdomainManagerService::class);
            if ($subdomainManager->isReserved($lowerSlug)) {
                abort(404, 'Store not found.');
            }

            // Find the vendor
            $vendor = Vendor::where('slug', $lowerSlug)->first();

            if (!$vendor) {
                $redirectVendor = $subdomainManager->resolveRedirectVendor($lowerSlug);
                if ($redirectVendor) {
                    $path = trim($request->path(), '/');
                    $prefix = trim((string) config('marketplace.storefront_path_prefix', 's'), '/');
                    $extraPath = '';
                    if ($prefix !== '' && str_starts_with(strtolower($path), $prefix . '/' . $lowerSlug)) {
                        $extraPath = ltrim(substr($path, strlen($prefix . '/' . $lowerSlug)), '/');
                    } elseif ($path !== '') {
                        $extraPath = $path;
                    }
                    $targetUrl = $subdomainManager->buildRedirectTarget($request, $redirectVendor, $extraPath);
                    return redirect()->away($targetUrl, 301);
                }

                abort(404, 'Store not found.');
            }

            // Check if vendor is active & publicly visible (or accessing own vendor dashboard/onboarding/preview/platform endpoints)
            $isVendorRoute = $request->is('dashboard*', 'vendor*', 'services/onboarding*');
            $isPlatformInfraRoute = $request->is('sitemap*.xml', 'robots.txt', 'feeds/*', 'api/*', 'account*', 'login', 'register', 'logout', 'sell*');
            $isOwnerPreview = $request->boolean('preview') && auth()->check() && (auth()->user()->vendor?->id === $vendor->id || auth()->user()->hasRole('admin'));
            if (!$isVendorRoute && !$isPlatformInfraRoute && !$isOwnerPreview) {
                if ($vendor->isServices() && $vendor->website_status === 'draft' && !in_array($vendor->status, ['suspended', 'closed'], true) && $vendor->verification_status !== 'rejected') {
                    return response()
                        ->view('errors.website-unpublished', ['vendor' => $vendor], 404)
                        ->header('X-Robots-Tag', 'noindex, nofollow')
                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                }
                if (!app(\App\Services\Monetization\VisibilityService::class)->isVendorVisibleInAnySector($vendor)) {
                    return response()->view('errors.store-suspended', ['vendor' => $vendor], 403);
                }
            }

            // Bind the vendor to the request and service container
            $request->attributes->set('current_vendor', $vendor);
            app()->instance('current_vendor', $vendor);

            // Also mark if we are in the vendor dashboard context.
            // This is used by VendorScope to isolate queries.
            // A request to /dashboard or /vendor on a storefront is vendor context.
            if ($isVendorRoute) {
                $request->attributes->set('vendor_context', true);
            }
        }

        return $next($request);
    }
}
