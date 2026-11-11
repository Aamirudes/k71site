<?php

namespace App\Services\Services;

use App\Models\Vendor;
use App\Models\VendorSubdomainHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubdomainManagerService
{
    public function reservedSubdomains(): array
    {
        return array_values(array_unique(array_map(
            'strtolower',
            array_merge(
                config('k71.reserved_subdomains', []) ?? [],
                config('marketplace.reserved_subdomains', []) ?? [],
                array_keys(config('k71.verticals', []) ?? []),
                [
                    'feasto', 'feato', 'b2b', 'www', 'admin', 'api', 'app',
                    'vendor', 'vendors', 'seller', 'sellers', 'services', 'service',
                    'onboarding', 'projects', 'contracts', 'account', 'login',
                    'register', 'dashboard', 'mail', 'cdn', 'static', 'assets',
                    'support', 'help', 'k71', 'auth', 'oauth',
                ]
            )
        )));
    }

    public function normalize(string $slug): string
    {
        return strtolower(trim($slug));
    }

    public function suggestFromName(string $name): string
    {
        $base = Str::limit(Str::slug($name) ?: 'studio', 24, '');
        $base = trim($base, '-');
        if (strlen($base) < 3) {
            $base = str_pad($base, 3, '0');
        }

        $candidate = $base;
        $counter = 1;
        while (!$this->checkAvailability($candidate)['available']) {
            $suffix = '-' . $counter++;
            $candidate = Str::limit($base, max(1, 30 - strlen($suffix)), '') . $suffix;
        }

        return $candidate;
    }

    public function validateFormat(string $slug): ?string
    {
        if ($slug === '' || strlen($slug) < 3) {
            return 'Website name must be at least 3 characters.';
        }

        if (strlen($slug) > 30) {
            return 'Website name cannot exceed 30 characters.';
        }

        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $slug) || str_contains($slug, '--')) {
            return 'Use only lowercase letters, numbers, and single hyphens between characters.';
        }

        return null;
    }

    public function isReserved(string $slug): bool
    {
        $slug = $this->normalize($slug);
        if (in_array($slug, $this->reservedSubdomains(), true)) {
            return true;
        }

        return DB::table('reserved_slugs')->where('slug', $slug)->exists();
    }

    public function checkAvailability(string $rawSlug, ?Vendor $ignoreVendor = null): array
    {
        $slug = $this->normalize($rawSlug);
        $domain = config('marketplace.domain', 'marblemandi.org');

        if ($error = $this->validateFormat($slug)) {
            return [
                'available' => false,
                'slug' => $slug,
                'message' => $error,
                'preview_url' => $slug ? "{$slug}.{$domain}" : null,
            ];
        }

        if ($this->isReserved($slug)) {
            return [
                'available' => false,
                'slug' => $slug,
                'message' => 'This subdomain is reserved by K71 and cannot be registered.',
                'preview_url' => "{$slug}.{$domain}",
            ];
        }

        $vendorExists = Vendor::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreVendor, fn ($q) => $q->where('id', '!=', $ignoreVendor->id))
            ->exists();

        if ($vendorExists) {
            return [
                'available' => false,
                'slug' => $slug,
                'message' => 'This website address is already claimed by another provider or store.',
                'preview_url' => "{$slug}.{$domain}",
            ];
        }

        $cooldownLocked = VendorSubdomainHistory::where('slug', $slug)
            ->where('cooldown_until', '>', now())
            ->when($ignoreVendor, fn ($q) => $q->where('vendor_id', '!=', $ignoreVendor->id))
            ->exists();

        if ($cooldownLocked) {
            return [
                'available' => false,
                'slug' => $slug,
                'message' => 'This subdomain was recently renamed by another provider and is temporarily protected.',
                'preview_url' => "{$slug}.{$domain}",
            ];
        }

        return [
            'available' => true,
            'slug' => $slug,
            'message' => 'Great news — this website address is available!',
            'preview_url' => "{$slug}.{$domain}",
        ];
    }

    public function renameSubdomain(Vendor $vendor, string $rawNewSlug): string
    {
        $newSlug = $this->normalize($rawNewSlug);
        if ($vendor->slug === $newSlug) {
            return $newSlug;
        }

        $check = $this->checkAvailability($newSlug, $vendor);
        if (!$check['available']) {
            throw new \InvalidArgumentException($check['message']);
        }

        DB::transaction(function () use ($vendor, $newSlug) {
            $oldSlug = $vendor->slug;

            if ($oldSlug && $oldSlug !== $newSlug) {
                VendorSubdomainHistory::updateOrCreate(
                    ['vendor_id' => $vendor->id, 'slug' => $oldSlug],
                    [
                        'is_active_redirect' => true,
                        'released_at' => now(),
                        'cooldown_until' => now()->addDays((int) config('marketplace.slug.deleted_hold_days', 90)),
                    ]
                );
            }

            // If reclaiming a previous slug of the same vendor, deactivate old redirect on newSlug so it never loops
            VendorSubdomainHistory::where('vendor_id', $vendor->id)
                ->where('slug', $newSlug)
                ->update(['is_active_redirect' => false]);

            $vendor->update([
                'slug' => $newSlug,
                'slug_reserved_at' => now(),
            ]);
        });

        $vendor->bumpWebsiteCache();
        Cache::forget('sitemap_stores_xml');

        return $newSlug;
    }

    public function resolveRedirectVendor(string $oldSlug): ?Vendor
    {
        $slug = $this->normalize($oldSlug);
        $history = VendorSubdomainHistory::with('vendor')
            ->where('slug', $slug)
            ->where('is_active_redirect', true)
            ->latest('id')
            ->first();

        if (!$history || !$history->vendor) {
            return null;
        }

        if ($history->vendor->slug === $slug) {
            return null;
        }

        if (!in_array($history->vendor->status, ['approved', 'active'], true)) {
            return null;
        }

        return $history->vendor;
    }

    public function buildRedirectTarget(Request $request, Vendor $targetVendor, string $extraPath = ''): string
    {
        $host = strtolower($request->getHost());
        $extractedSub = function_exists('k71_extract_subdomain') ? k71_extract_subdomain($host) : null;
        $queryString = $request->getQueryString();
        $suffix = $extraPath !== '' ? '/' . ltrim($extraPath, '/') : '';
        $qs = $queryString ? '?' . $queryString : '';

        if ($extractedSub && config('marketplace.use_subdomains', true)) {
            return rtrim($targetVendor->storefront_url, '/') . $suffix . $qs;
        }

        return url('/s/' . $targetVendor->slug . $suffix) . $qs;
    }
}

