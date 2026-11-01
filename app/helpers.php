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
            return config('k71.root_domain', 'marblemandi.org');
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
     * Defaults to path routes (e.g. marblemandi.org/food) unless subdomains are explicitly enabled.
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

        // Subdirectory mode (standard production for marblemandi.org)
        if (!$useSubdomains) {
            if ($vertical === 'main') {
                return "{$scheme}://{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
            }
            $subPath = $cleanPath ? $vertical . '/' . $cleanPath : $vertical;
            return "{$scheme}://{$rootDomain}{$portSuffix}/" . $subPath;
        }

        // Wildcard Subdomains mode (if explicitly configured)
        if ($vertical === 'main') {
            return "{$scheme}://{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
        }
        return "{$scheme}://{$vertical}.{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
    }
}

if (!function_exists('k71_vendor_url')) {
    /**
     * Return vendor portal URL. Uses path /vendor unless subdomains are explicitly enabled.
     */
    function k71_vendor_url(string $path = ''): string
    {
        $host = request()->getHost();
        $isIp = filter_var($host, FILTER_VALIDATE_IP);
        $cleanPath = ltrim($path, '/');
        $scheme = (app()->environment('production') || request()->secure()) ? 'https' : 'http';
        $port = request()->getPort();
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
        $useSubdomains = config('marketplace.use_subdomains', true);

        if (!$useSubdomains || $isIp) {
            $rootDomain = k71_root_domain($host);
            $subPath = $cleanPath ? 'vendor/' . $cleanPath : 'vendor';
            return "{$scheme}://{$rootDomain}{$portSuffix}/" . $subPath;
        }

        $rootDomain = k71_root_domain($host);
        return "{$scheme}://vendor.{$rootDomain}{$portSuffix}" . ($cleanPath ? '/' . $cleanPath : '');
    }
}

if (!function_exists('format_user_avatar')) {
    /**
     * Return user profile avatar or high-resolution UI avatar fallback.
     */
    function format_user_avatar(?string $avatar, ?string $name = 'User'): string
    {
        if (!empty($avatar)) {
            if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://') || str_starts_with($avatar, '/')) {
                return $avatar;
            }
            return '/storage/' . ltrim($avatar, '/');
        }
        $name = trim($name ?? 'User');
        return 'https://ui-avatars.com/api/?name=' . urlencode($name ?: 'User') . '&background=0095F6&color=ffffff&size=200&bold=true&format=svg';
    }
}

if (!function_exists('format_image_url')) {
    /**
     * Return universally valid image URL handling absolute HTTP/HTTPS, Unsplash, storage paths, and fallbacks.
     */
    function format_image_url(?string $path, ?string $fallback = null): string
    {
        if (empty($path)) {
            return $fallback ?: asset('images/product-placeholder.svg');
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}

