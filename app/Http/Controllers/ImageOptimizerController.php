<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageOptimizerController extends Controller
{
    /**
     * Dynamically optimize and resize public storage images to modern WebP format
     */
    public function render(Request $request, $width, $path)
    {
        $width = min(max((int)$width, 32), 1600);
        $fullOriginalPath = storage_path('app/public/' . $path);

        if (!file_exists($fullOriginalPath) || !is_file($fullOriginalPath)) {
            // Check public/images fallback
            $publicPath = public_path($path);
            if (file_exists($publicPath) && is_file($publicPath)) {
                $fullOriginalPath = $publicPath;
            } else {
                $placeholder = public_path('images/product-placeholder.svg');
                if (file_exists($placeholder)) {
                    return response()->file($placeholder, [
                        'Content-Type' => 'image/svg+xml',
                        'Cache-Control' => 'public, max-age=86400',
                    ]);
                }
                return response('Image not found', 404);
            }
        }

        $extension = strtolower(pathinfo($fullOriginalPath, PATHINFO_EXTENSION));

        // SVG files are vectors, serve directly
        if ($extension === 'svg') {
            return response()->file($fullOriginalPath, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // Cache directory
        $cacheDir = storage_path('app/public/cache/' . $width);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $fileHash = md5($path . '_' . filemtime($fullOriginalPath));
        $cacheFile = $cacheDir . '/' . $fileHash . '.webp';

        if (file_exists($cacheFile)) {
            return response()->file($cacheFile, [
                'Content-Type' => 'image/webp',
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
            ]);
        }

        // Generate optimized WebP
        $optimized = $this->createWebp($fullOriginalPath, $cacheFile, $width);

        if ($optimized && file_exists($cacheFile)) {
            return response()->file($cacheFile, [
                'Content-Type' => 'image/webp',
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
            ]);
        }

        // Fallback to original
        return response()->file($fullOriginalPath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function createWebp($sourcePath, $destinationPath, $targetWidth)
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
            return false;
        }

        $content = @file_get_contents($sourcePath);
        if (!$content) {
            return false;
        }

        $srcImage = @imagecreatefromstring($content);
        if (!$srcImage) {
            return false;
        }

        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        if ($origWidth <= 0 || $origHeight <= 0) {
            imagedestroy($srcImage);
            return false;
        }

        // Calculate proportional height
        $targetWidth = min($targetWidth, $origWidth);
        $targetHeight = (int) round(($origHeight / $origWidth) * $targetWidth);

        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Preserve transparency for PNG / WebP
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

        $success = @imagewebp($dstImage, $destinationPath, 78);

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return $success;
    }
}
