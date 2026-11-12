<?php

use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $triggerImage = Setting::get('floating_launcher_trigger_image', null);
            if ($this->shouldResetAssetPath($triggerImage, 0.25)) {
                Setting::set('floating_launcher_trigger_image', null, 'navigation');
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            User::query()->whereNotNull('avatar')->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    if ($this->shouldResetAssetPath($user->avatar, 0.35)) {
                        $user->avatar = null;
                        $user->save();
                    }
                }
            });
        }

        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'logo')) {
            Vendor::query()->whereNotNull('logo')->chunkById(100, function ($vendors) {
                foreach ($vendors as $vendor) {
                    if ($this->shouldResetAssetPath($vendor->logo, 0.50)) {
                        $vendor->logo = null;
                        $vendor->save();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // One-time cleanup migration; intentionally non-destructive on rollback.
    }

    private function shouldResetAssetPath(?string $path, float $blueRatioThreshold): bool
    {
        if (empty($path)) {
            return false;
        }

        $trimmed = trim((string) $path);
        if (preg_match('/(k71-logo|\/images\/logo\.)/i', $trimmed)) {
            return true;
        }

        $relative = ltrim(preg_replace('/^https?:\/\/[^\/]+\//i', '', $trimmed), '/');
        $candidates = [];
        if (str_starts_with($relative, 'storage/')) {
            $sub = substr($relative, strlen('storage/'));
            $candidates[] = storage_path('app/public/' . $sub);
            $candidates[] = public_path($relative);
        } else {
            $candidates[] = public_path($relative);
            $candidates[] = storage_path('app/public/' . $relative);
        }

        foreach ($candidates as $file) {
            if (is_file($file) && $this->isLegacyCobaltBlueImage($file, $blueRatioThreshold)) {
                return true;
            }
        }

        return false;
    }

    private function isLegacyCobaltBlueImage(string $filePath, float $blueRatioThreshold): bool
    {
        if (!function_exists('imagecreatefromstring')) {
            return false;
        }

        $bytes = @file_get_contents($filePath);
        if ($bytes === false || strlen($bytes) < 32) {
            return false;
        }

        $img = @imagecreatefromstring($bytes);
        if (!$img) {
            return false;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        if ($w < 4 || $h < 4) {
            imagedestroy($img);
            return false;
        }

        $stepX = max(1, (int) floor($w / 16));
        $stepY = max(1, (int) floor($h / 16));
        $cobaltPixels = 0;
        $opaqueSamples = 0;

        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                $rgba = imagecolorat($img, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha > 110) {
                    continue;
                }
                $opaqueSamples++;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                // Match the legacy K71 cobalt-blue badge background (~#0B00B8)
                if ($r < 45 && $g < 45 && $b > 115) {
                    $cobaltPixels++;
                }
            }
        }

        imagedestroy($img);

        return $opaqueSamples > 0 && ($cobaltPixels / $opaqueSamples) >= $blueRatioThreshold;
    }
};

