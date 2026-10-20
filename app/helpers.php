<?php

if (!function_exists('optimized_image')) {
    /**
     * Return optimized WebP image URL
     */
    function optimized_image(?string $path, ?int $width = null): string
    {
        return \App\Services\ImageService::url($path, $width);
    }
}

if (!function_exists('k71_extract_subdomain')) {
    /**
     * Extract the subdomain part from any hostname (wildcard compatible for mysite.in, k71.in, localhost).
     */
    function k71_extract_subdomain(?string $host = null): ?string
    {
        $host = strtolower(trim($host ?? request()->getHost()));
        if (empty($host) || filter_var($host, FILTER_VALIDATE_IP) || !str_contains($host, '.')) {
            return null;
        }

        $parts = explode('.', $host);
        $count = count($parts);

        // localhost subdomains, e.g. food.localhost, vendor.localhost (count 2)
        if ($count === 2 && $parts[1] === 'localhost') {
            return $parts[0];
        }

        // Common compound TLDs: .co.in, .org.in, .net.in, .co.uk, etc.
        $compoundTlds = ['co.in', 'net.in', 'org.in', 'gen.in', 'firm.in', 'ind.in', 'co.uk', 'org.uk', 'com.au', 'net.au'];
        $isCompound = false;
        foreach ($compoundTlds as $cTld) {
            if (str_ends_with($host, '.' . $cTld)) {
                $isCompound = true;
                break;
            }
        }

        if ($isCompound) {
            return $count >= 4 ? $parts[0] : null;
        }

        // Standard domains: food.mysite.in, food.k71.in, shop.domain.com (count >= 3)
        return $count >= 3 ? $parts[0] : null;
    }
}

if (!function_exists('k71_root_domain')) {
    /**
     * Dynamically determine the root apex domain from current request host.
     */
    function k71_root_domain(?string $host = null): string
    {
        $host = strtolower(trim($host ?? request()->getHost()));
        if (empty($host) || filter_var($host, FILTER_VALIDATE_IP)) {
            return config('k71.root_domain', 'k71.in');
        }

        $sub = k71_extract_subdomain($host);
        if ($sub) {
            return substr($host, strlen($sub) + 1);
        }

        return $host;
    }
}

if (!function_exists('k71_vertical_url')) {
    /**
     * Return canonical URL for a specific vertical.
     * Generates pure subdomains (e.g. food.mysite.in, food.k71.in, food.localhost)
     * and query params fallback in local IP / LAN testing (e.g. 192.168.1.20:8000).
     */
    function k71_vertical_url(string $vertical = 'main', string $path = ''): string
    {
        $host = request()->getHost();
        $isIp = filter_var($host, FILTER_VALIDATE_IP);
        $cleanPath = ltrim($path, '/');
        $scheme = (app()->environment('production') || request()->secure()) ? 'https' : 'http';
        $port = request()->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';

        $useSubdomains = config('marketplace.use_subdomains', true);

        // Local LAN IP fallback (e.g. 192.168.1.20:8000 or 127.0.0.1:8000) for testing on mobile phone
        if ($isIp) {
            if ($vertical === 'main') {
                return url('/' . $cleanPath);
            }
            $query = 'vertical=' . $vertical;
            if ($cleanPath) {
                return url('/' . $cleanPath . (str_contains($cleanPath, '?') ? '&' : '?') . $query);
            }
            return url('/?' . $query);
        }

        $rootDomain = k71_root_domain($host);

        // Subdirectory mode when subdomains are turned off
        if (!$useSubdomains) {
            if ($vertical === 'main') {
                return "{$scheme}://{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
            }
            $subPath = $cleanPath ? $vertical . '/' . $cleanPath : $vertical;
            return "{$scheme}://{$rootDomain}{$portSuffix}/" . $subPath;
        }

        // Wildcard Subdomains on ANY domain: food.mysite.in, food.k71.in, food.localhost
        if ($vertical === 'main') {
            return "{$scheme}://{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
        }
        return "{$scheme}://{$vertical}.{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
    }
}

if (!function_exists('k71_vendor_url')) {
    /**
     * Return vendor portal URL on wildcard subdomain (vendor.mysite.in, vendor.k71.in).
     */
    function k71_vendor_url(string $path = ''): string
    {
        $host = request()->getHost();
        $isIp = filter_var($host, FILTER_VALIDATE_IP);
        $cleanPath = ltrim($path, '/');
        $scheme = (app()->environment('production') || request()->secure()) ? 'https' : 'http';
        $port = request()->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';

        if ($isIp) {
            return url('/vendor' . ($cleanPath ? '/' . $cleanPath : ''));
        }

        $rootDomain = k71_root_domain($host);
        return "{$scheme}://vendor.{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
    }
}
