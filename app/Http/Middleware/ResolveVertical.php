<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveVertical
{
    /**
     * Handle an incoming request and resolve active K71 vertical.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $verticals = config('k71.verticals', []);
        
        $detectedKey = 'main';

        // 1. Check query override (convenient in development/testing, e.g. ?vertical=food)
        if ($request->has('vertical') && isset($verticals[$request->query('vertical')])) {
            $detectedKey = $request->query('vertical');
        } else {
            // 2. Wildcard subdomain check for ANY domain (mysite.in, k71.in, localhost)
            $subdomain = k71_extract_subdomain($host);
            if ($subdomain && isset($verticals[$subdomain])) {
                $detectedKey = $subdomain;
            }
        }

        $useSubdomains = config('marketplace.use_subdomains', false);

        // If subdomains are disabled, detect vertical directly from the request path
        if (!$useSubdomains && $detectedKey === 'main') {
            foreach (['shorts', 'food', 'services', 'used', 'enquiry'] as $vPrefix) {
                if ($request->is($vPrefix . '*')) {
                    $detectedKey = $vPrefix;
                    break;
                }
            }
        }

        // If subdomains are disabled, redirect incoming subdomain requests to live path routes (food.marblemandi.org -> marblemandi.org/food)
        if (!$useSubdomains && $detectedKey !== 'main' && !empty($subdomain) && !filter_var($host, FILTER_VALIDATE_IP) && app()->environment('production')) {
            $rootDomain = k71_root_domain($host);
            $port = $request->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
            $scheme = (app()->environment('production') || $request->secure()) ? 'https' : 'http';
            $remaining = ltrim($request->path(), '/');
            $targetUrl = "{$scheme}://{$rootDomain}{$portSuffix}/{$detectedKey}" . ($remaining ? '/' . $remaining : '');
            if ($request->getQueryString()) {
                $targetUrl .= '?' . $request->getQueryString();
            }
            return redirect()->away($targetUrl, 301);
        }

        // Canonical 301 redirect on production root domain: Redirect folder routes to pure subdomains ONLY if subdomains are enabled
        if ($useSubdomains && $detectedKey === 'main' && !filter_var($host, FILTER_VALIDATE_IP) && app()->environment('production')) {
            $rootDomain = k71_root_domain($host);
            foreach (['shorts', 'food', 'services', 'used', 'enquiry'] as $vPrefix) {
                if ($request->is($vPrefix . '*')) {
                    $path = $request->path();
                    $remaining = ltrim(substr($path, strlen($vPrefix)), '/');
                    return redirect()->away(k71_vertical_url($vPrefix, $remaining), 301);
                }
            }
        }

        $activeVertical = $verticals[$detectedKey] ?? $verticals['main'];
        $request->attributes->set('k71_vertical', $activeVertical);
        $request->attributes->set('k71_vertical_key', $detectedKey);

        // Share globally with Blade templates
        view()->share('k71_vertical', $activeVertical);
        view()->share('k71_vertical_key', $detectedKey);
        view()->share('k71_all_verticals', $verticals);

        return $next($request);
    }
}
