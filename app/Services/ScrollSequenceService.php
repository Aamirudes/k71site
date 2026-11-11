<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ScrollSequenceService
{
    public const ALLOWED_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];
    public const MAX_FRAMES = 360;
    public const MAX_ZIP_UNCOMPRESSED_BYTES = 157286400; // 150 MB safety cap

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
