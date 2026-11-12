<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaUploadService
{
    public const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash', 'ps1', 'cgi', 'pl',
        'py', 'rb', 'asp', 'aspx', 'jsp', 'html', 'htm', 'xhtml', 'svg', 'js', 'mjs', 'htaccess',
    ];

    public const DELIVERABLE_MIME_EXT_MAP = [
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'application/octet-stream' => null, // Allowed only when validated against trusted extension allowlist below
    ];

    public const SAFE_DELIVERABLE_EXTENSIONS = [
        'pdf', 'zip', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'txt', 'csv',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'fig', 'psd', 'ai',
        'mp4', 'webm', 'mov', 'mp3', 'wav',
    ];

    /**
     * Safely validate and store a workspace/service deliverable or requirement attachment.
     *
     * @return array{name: string, path: string, size: int, mime: string}
     */
    public function storeSafeDeliverable(UploadedFile $file, string $directory, string $errorKey = 'files'): array
    {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                $errorKey => 'One of the uploaded files failed to transfer cleanly.',
            ]);
        }

        $clientExt = strtolower((string) $file->getClientOriginalExtension());
        if ($clientExt !== '' && in_array($clientExt, self::BLOCKED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                $errorKey => "File extension '.{$clientExt}' is blocked for security reasons.",
            ]);
        }

        $detectedMime = strtolower((string) $file->getMimeType());
        $mappedExt = self::DELIVERABLE_MIME_EXT_MAP[$detectedMime] ?? false;

        if ($mappedExt === false) {
            throw ValidationException::withMessages([
                $errorKey => "Unsupported deliverable file MIME type ({$detectedMime}).",
            ]);
        }

        $finalExt = $mappedExt;
        if ($finalExt === null) {
            if (!in_array($clientExt, self::SAFE_DELIVERABLE_EXTENSIONS, true)) {
                throw ValidationException::withMessages([
                    $errorKey => 'Binary attachments must have an approved extension (fig, psd, ai, pdf, zip, doc, image, video).',
                ]);
            }
            $finalExt = $clientExt;
        }

        $safeDirectory = trim(str_replace(['..', '\\'], ['', '/'], $directory), '/');
        $safeFilename = Str::uuid()->toString() . '.' . $finalExt;
        $storedPath = $file->storeAs($safeDirectory, $safeFilename, 'public');

        $cleanOriginalName = trim(preg_replace('/[^\w.\-\s()]+/', '_', $file->getClientOriginalName())) ?: $safeFilename;

        return [
            'name' => Str::limit($cleanOriginalName, 140, ''),
            'path' => $storedPath,
            'size' => (int) $file->getSize(),
            'mime' => $detectedMime,
        ];
    }
}
