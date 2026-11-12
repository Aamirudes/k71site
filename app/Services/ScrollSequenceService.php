<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ScrollSequenceService
{
    public const ALLOWED_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];
    public const MAX_FRAMES = 360;
    public const MAX_ZIP_UNCOMPRESSED_BYTES = 157286400; // 150 MB safety cap

    public static function vendorRelativeDir(int $vendorId): string
    {
        return 'vendors/' . $vendorId . '/scroll-frames';
    }

    /**
     * Resolve per-vendor scroll-scrubbed JPG frame sequence for the Kinetic storefront preset.
     */
    public static function resolveVendorSequence(int|\App\Models\Vendor $vendor, array $config = []): array
    {
        $vendorId = $vendor instanceof \App\Models\Vendor ? (int) $vendor->id : (int) $vendor;
        $relDir = self::vendorRelativeDir($vendorId);
        $uploadedStorageDir = storage_path('app/public/' . $relDir);
        $uploadedPublicDir = public_path('storage/' . $relDir);

        $uploadedFiles = self::scanImageFiles($uploadedStorageDir);
        if (empty($uploadedFiles)) {
            $uploadedFiles = self::scanImageFiles($uploadedPublicDir);
        } elseif (!is_link(public_path('storage'))) {
            self::syncDirectoryMirror($uploadedStorageDir, $uploadedPublicDir);
        }

        $videoUrl = trim((string) ($config['scroll_sequence']['video_url'] ?? ''));

        if (!empty($uploadedFiles)) {
            return [
                'source' => 'uploaded',
                'label' => 'Storefront JPG Motion Frames',
                'directory' => '/storage/' . $relDir,
                'count' => count($uploadedFiles),
                'frames' => array_map(fn (string $f) => '/storage/' . $relDir . '/' . rawurlencode($f), $uploadedFiles),
                'video_url' => $videoUrl ?: null,
            ];
        }

        if ($videoUrl !== '') {
            return [
                'source' => 'video',
                'label' => 'Storefront Motion Video',
                'directory' => null,
                'count' => 0,
                'frames' => [],
                'video_url' => $videoUrl,
            ];
        }

        return [
            'source' => 'fallback',
            'label' => 'Interactive 3D Studio Preview (Upload any video to auto-generate JPG frames)',
            'directory' => null,
            'count' => 0,
            'frames' => [],
            'video_url' => null,
        ];
    }

    /**
     * Store a chunked batch of vendor scroll frames, always converting/writing genuine .jpg files.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public static function storeVendorFrameBatch(\App\Models\Vendor $vendor, array $files, int $startIndex = 1, bool $resetBefore = false, array $config = []): array
    {
        if ($resetBefore) {
            self::clearVendorFrames($vendor);
        }

        $relDir = self::vendorRelativeDir((int) $vendor->id);
        $storageDir = storage_path('app/public/' . $relDir);
        $publicDir = public_path('storage/' . $relDir);
        self::ensureDirectory($storageDir);
        if (!is_link(public_path('storage'))) {
            self::ensureDirectory($publicDir);
        }

        $written = 0;
        $seqIndex = max(1, $startIndex);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }
            if ($seqIndex > self::MAX_FRAMES) {
                break;
            }

            $realPath = $file->getRealPath();
            $imageInfo = $realPath ? @getimagesize($realPath) : false;
            if ($imageInfo === false) {
                continue;
            }

            $targetName = sprintf('frame_%05d.jpg', $seqIndex);
            $destPath = $storageDir . DIRECTORY_SEPARATOR . $targetName;
            $saved = false;

            if ($imageInfo[2] === IMAGETYPE_JPEG) {
                $saved = @copy($realPath, $destPath);
            } elseif (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
                $bytes = @file_get_contents($realPath);
                $gd = $bytes !== false ? @imagecreatefromstring($bytes) : false;
                if ($gd) {
                    $w = imagesx($gd);
                    $h = imagesy($gd);
                    $canvas = imagecreatetruecolor($w, $h);
                    $black = imagecolorallocate($canvas, 0, 0, 0);
                    imagefill($canvas, 0, 0, $black);
                    imagecopy($canvas, $gd, 0, 0, 0, 0, $w, $h);
                    $saved = @imagejpeg($canvas, $destPath, 86);
                    imagedestroy($canvas);
                    imagedestroy($gd);
                }
            }

            if ($saved) {
                if (!is_link(public_path('storage')) && is_dir($publicDir)) {
                    @copy($destPath, $publicDir . DIRECTORY_SEPARATOR . $targetName);
                }
                $written++;
                $seqIndex++;
            }
        }

        return [
            'written' => $written,
            'sequence' => self::resolveVendorSequence($vendor, $config),
        ];
    }

    /**
     * Store uploaded vendor video and attempt server-side ffmpeg JPG frame extraction when available.
     */
    public static function extractVendorVideoFrames(\App\Models\Vendor $vendor, UploadedFile $videoFile, int $targetFrames = 72): array
    {
        $videoRelPath = $videoFile->store('vendors/' . $vendor->id . '/scroll-video', 'public');
        k71_sync_public_storage($videoRelPath);
        $videoUrl = '/storage/' . $videoRelPath;
        $videoFullPath = storage_path('app/public/' . $videoRelPath);

        $extracted = false;
        if (function_exists('exec') && is_file($videoFullPath)) {
            $ffmpegCheck = [];
            $code = 1;
            @exec('ffmpeg -version 2>&1', $ffmpegCheck, $code);
            if ($code === 0) {
                self::clearVendorFrames($vendor);
                $relDir = self::vendorRelativeDir((int) $vendor->id);
                $storageDir = storage_path('app/public/' . $relDir);
                $publicDir = public_path('storage/' . $relDir);
                self::ensureDirectory($storageDir);
                if (!is_link(public_path('storage'))) {
                    self::ensureDirectory($publicDir);
                }

                $targetFrames = max(12, min(140, $targetFrames));
                $pattern = $storageDir . DIRECTORY_SEPARATOR . 'frame_%05d.jpg';
                $cmd = sprintf(
                    'ffmpeg -y -i %s -vf "scale=1280:-2:force_original_aspect_ratio=decrease" -frames:v %d -q:v 4 %s 2>&1',
                    escapeshellarg($videoFullPath),
                    $targetFrames,
                    escapeshellarg($pattern)
                );
                $out = [];
                $exCode = 1;
                @exec($cmd, $out, $exCode);
                if ($exCode === 0 && !empty(self::scanImageFiles($storageDir))) {
                    self::syncDirectoryMirror($storageDir, $publicDir);
                    $extracted = true;
                }
            }
        }

        return [
            'video_url' => $videoUrl,
            'extracted' => $extracted,
            'sequence' => self::resolveVendorSequence($vendor, ['scroll_sequence' => ['video_url' => $videoUrl]]),
        ];
    }

    /**
     * Clear only a single vendor's uploaded JPG scroll frames.
     */
    public static function clearVendorFrames(\App\Models\Vendor $vendor): void
    {
        $relDir = self::vendorRelativeDir((int) $vendor->id);
        foreach ([storage_path('app/public/' . $relDir), public_path('storage/' . $relDir)] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $items = @scandir($dir) ?: [];
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $full = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_file($full)) {
                    @unlink($full);
                }
            }
        }
    }

    /**
     * Discover and sort active scroll-scrubbed image frames.
     * Priority:
     * 1. Admin-uploaded frames (storage/app/public/scroll-frames -> /storage/scroll-frames/...)
     * 2. Optional safe custom subfolder in public/images/<folder>
     * 3. Drop-in folder (public/images/scroll-frames -> /images/scroll-frames/...)
     * 4. Fallback root drop-in folder (public/scroll-frames -> /scroll-frames/...)
     */
    public static function resolveSequence(?string $preferredFolder = null): array
    {
        $uploadedStorageDir = storage_path('app/public/scroll-frames');
        $uploadedPublicDir = public_path('storage/scroll-frames');

        $uploadedFiles = self::scanImageFiles($uploadedStorageDir);
        if (empty($uploadedFiles)) {
            $uploadedFiles = self::scanImageFiles($uploadedPublicDir);
        } elseif (!is_link(public_path('storage'))) {
            // Keep public/storage mirror in sync on environments where symlink is a regular directory
            self::syncDirectoryMirror($uploadedStorageDir, $uploadedPublicDir);
        }

        if (!empty($uploadedFiles)) {
            return [
                'source' => 'uploaded',
                'label' => 'Admin Uploaded Sequence',
                'directory' => '/storage/scroll-frames',
                'count' => count($uploadedFiles),
                'frames' => array_map(fn (string $f) => '/storage/scroll-frames/' . rawurlencode($f), $uploadedFiles),
            ];
        }

        $safeFolder = self::sanitizeSubfolder($preferredFolder);
        if ($safeFolder !== null) {
            $customDir = public_path('images/' . $safeFolder);
            $customFiles = self::scanImageFiles($customDir);
            if (!empty($customFiles)) {
                return [
                    'source' => 'folder',
                    'label' => 'Folder (/images/' . $safeFolder . ')',
                    'directory' => '/images/' . $safeFolder,
                    'count' => count($customFiles),
                    'frames' => array_map(fn (string $f) => '/images/' . $safeFolder . '/' . rawurlencode($f), $customFiles),
                ];
            }
        }

        $imagesFolderDir = public_path('images/scroll-frames');
        $imagesFolderFiles = self::scanImageFiles($imagesFolderDir);
        if (!empty($imagesFolderFiles)) {
            return [
                'source' => 'folder',
                'label' => 'Drop-In Folder (/images/scroll-frames)',
                'directory' => '/images/scroll-frames',
                'count' => count($imagesFolderFiles),
                'frames' => array_map(fn (string $f) => '/images/scroll-frames/' . rawurlencode($f), $imagesFolderFiles),
            ];
        }

        $rootFramesDir = public_path('scroll-frames');
        $rootFramesFiles = self::scanImageFiles($rootFramesDir);
        if (!empty($rootFramesFiles)) {
            return [
                'source' => 'folder',
                'label' => 'Drop-In Folder (/scroll-frames)',
                'directory' => '/scroll-frames',
                'count' => count($rootFramesFiles),
                'frames' => array_map(fn (string $f) => '/scroll-frames/' . rawurlencode($f), $rootFramesFiles),
            ];
        }

        $heroScrollPublicDir = public_path('heroscroll');
        $heroScrollFiles = self::scanImageFiles($heroScrollPublicDir);
        if (!empty($heroScrollFiles)) {
            return [
                'source' => 'folder',
                'label' => 'Drop-In Folder (/heroscroll)',
                'directory' => '/heroscroll',
                'count' => count($heroScrollFiles),
                'frames' => array_map(fn (string $f) => '/heroscroll/' . rawurlencode($f), $heroScrollFiles),
            ];
        }

        return [
            'source' => 'fallback',
            'label' => 'Atmospheric 3D Studio Preview (No custom frames uploaded yet)',
            'directory' => null,
            'count' => 0,
            'frames' => [],
        ];
    }

    /**
     * Store a chunked batch of uploaded image frames cleanly and sequentially.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public static function storeFrameBatch(array $files, int $startIndex = 1, bool $resetBefore = false): array
    {
        if ($resetBefore) {
            self::clearUploadedFrames();
        }

        $storageDir = storage_path('app/public/scroll-frames');
        $publicDir = public_path('storage/scroll-frames');
        self::ensureDirectory($storageDir);
        if (!is_link(public_path('storage'))) {
            self::ensureDirectory($publicDir);
        }

        $written = 0;
        $seqIndex = max(1, $startIndex);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }
            if ($seqIndex > self::MAX_FRAMES) {
                break;
            }

            $realPath = $file->getRealPath();
            $imageInfo = $realPath ? @getimagesize($realPath) : false;
            if ($imageInfo === false) {
                continue;
            }

            $ext = match ($imageInfo[2]) {
                IMAGETYPE_WEBP => 'webp',
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                default => strtolower((string) $file->getClientOriginalExtension()),
            };

            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $targetName = sprintf('frame_%05d.%s', $seqIndex, $ext);
            $destPath = $storageDir . DIRECTORY_SEPARATOR . $targetName;

            if (@copy($realPath, $destPath)) {
                if (!is_link(public_path('storage')) && is_dir($publicDir)) {
                    @copy($destPath, $publicDir . DIRECTORY_SEPARATOR . $targetName);
                }
                $written++;
                $seqIndex++;
            }
        }

        $resolved = self::resolveSequence();
        return [
            'written' => $written,
            'sequence' => $resolved,
        ];
    }

    /**
     * Safely extract image frames from an uploaded ZIP archive with zip-bomb & traversal guards.
     */
    public static function storeZipArchive(UploadedFile $zipFile): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('PHP ZipArchive extension is not enabled on this server.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile->getRealPath()) !== true) {
            throw new \RuntimeException('Unable to open uploaded ZIP file.');
        }

        $validEntries = [];
        $totalBytes = 0;

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat || !isset($stat['name'])) {
                    continue;
                }

                $entryName = str_replace('\\', '/', $stat['name']);
                if (
                    str_ends_with($entryName, '/')
                    || str_contains($entryName, '../')
                    || str_starts_with($entryName, '/')
                    || str_starts_with($entryName, '__MACOSX/')
                ) {
                    continue;
                }

                $baseName = basename($entryName);
                if ($baseName === '' || str_starts_with($baseName, '.')) {
                    continue;
                }

                $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
                if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                    continue;
                }

                $size = (int) ($stat['size'] ?? 0);
                if ($size <= 0 || $size > 5242880) { // Max 5 MB per frame
                    continue;
                }

                $totalBytes += $size;
                if ($totalBytes > self::MAX_ZIP_UNCOMPRESSED_BYTES) {
                    throw new \RuntimeException('ZIP uncompressed size exceeds the 150 MB safety limit.');
                }

                $validEntries[] = [
                    'index' => $i,
                    'sortKey' => $baseName,
                    'ext' => $ext === 'jpeg' ? 'jpg' : $ext,
                ];

                if (count($validEntries) >= self::MAX_FRAMES) {
                    break;
                }
            }

            if (empty($validEntries)) {
                throw new \RuntimeException('No valid WebP, JPG, or PNG frames were found inside the ZIP archive.');
            }

            usort($validEntries, fn (array $a, array $b) => strnatcasecmp($a['sortKey'], $b['sortKey']));

            // Validate contents before clearing active uploaded frames
            $buffers = [];
            foreach ($validEntries as $entry) {
                $raw = $zip->getFromIndex($entry['index']);
                if ($raw === false || @getimagesizefromstring($raw) === false) {
                    continue;
                }
                $buffers[] = [
                    'ext' => $entry['ext'],
                    'data' => $raw,
                ];
            }

            if (empty($buffers)) {
                throw new \RuntimeException('Extracted files were not valid images.');
            }

            self::clearUploadedFrames();
            $storageDir = storage_path('app/public/scroll-frames');
            $publicDir = public_path('storage/scroll-frames');
            self::ensureDirectory($storageDir);
            if (!is_link(public_path('storage'))) {
                self::ensureDirectory($publicDir);
            }

            $seqIndex = 1;
            foreach ($buffers as $buf) {
                $targetName = sprintf('frame_%05d.%s', $seqIndex, $buf['ext']);
                $destPath = $storageDir . DIRECTORY_SEPARATOR . $targetName;
                if (@file_put_contents($destPath, $buf['data']) !== false) {
                    if (!is_link(public_path('storage')) && is_dir($publicDir)) {
                        @file_put_contents($publicDir . DIRECTORY_SEPARATOR . $targetName, $buf['data']);
                    }
                    $seqIndex++;
                }
            }

            return self::resolveSequence();
        } finally {
            $zip->close();
        }
    }

    /**
     * Remove all admin-uploaded scroll frames without affecting repository files.
     */
    public static function clearUploadedFrames(): void
    {
        foreach ([storage_path('app/public/scroll-frames'), public_path('storage/scroll-frames')] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $items = @scandir($dir) ?: [];
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $full = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_file($full)) {
                    @unlink($full);
                }
            }
        }
    }

    /**
     * Scan a directory for allowed image files in natural ascending order.
     *
     * @return string[]
     */
    private static function scanImageFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $entries = @scandir($dir);
        if ($entries === false) {
            return [];
        }

        $images = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }
            $full = $dir . DIRECTORY_SEPARATOR . $entry;
            if (!is_file($full)) {
                continue;
            }
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                $images[] = $entry;
            }
        }

        natsort($images);
        return array_slice(array_values($images), 0, self::MAX_FRAMES);
    }

    private static function sanitizeSubfolder(?string $folder): ?string
    {
        if (empty($folder)) {
            return null;
        }
        $clean = trim($folder, "/\\ \t\n\r\0\x0B");
        $clean = str_replace(['images/', 'public/'], '', $clean);
        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $clean)) {
            return null;
        }
        return $clean;
    }

    private static function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    private static function syncDirectoryMirror(string $srcDir, string $dstDir): void
    {
        if (!is_dir($srcDir)) {
            return;
        }
        self::ensureDirectory($dstDir);
        foreach (self::scanImageFiles($srcDir) as $file) {
            $src = $srcDir . DIRECTORY_SEPARATOR . $file;
            $dst = $dstDir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($dst) || filesize($dst) !== filesize($src)) {
                @copy($src, $dst);
            }
        }
    }
}
