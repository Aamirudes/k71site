<?php

namespace App\Services;

class ImageService
{
    /**
     * Get optimized image URL with dynamic WebP resizing and caching
     */
    public static function url(?string $path, ?int $width = null): string
    {
        if (empty($path)) {
            return asset('images/product-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Clean leading storage/ if present
        $cleanPath = preg_replace('#^storage/#', '', $path);
        $cleanPath = ltrim($cleanPath, '/');

        // If SVG or no width requested, return standard storage asset
        if (str_ends_with(strtolower($cleanPath), '.svg')) {
            return asset('storage/' . $cleanPath);
        }

        if ($width) {
            return url('/img-optimized/' . $width . '/' . $cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }
}
