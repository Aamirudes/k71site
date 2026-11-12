<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Role;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Models\VendorStorefrontConfig;
use App\Models\VendorStorefrontRevision;
use App\Services\Services\ServiceWebsitePresenter;
use App\Services\Services\SubdomainManagerService;
use App\Services\Storefront\StorefrontPresetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ServiceOnboardingController extends Controller
{
    public function __construct(
        protected SubdomainManagerService $subdomains,
        protected StorefrontPresetService $presetService,
        protected ServiceWebsitePresenter $presenter
    ) {}

    public function show(Request $request)
    {
        $categories = Category::where('vertical', 'services')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $vendor = auth()->user()?->vendor;
        $presets = $this->presetService->getPresetsForVendor($vendor);
        $step = (int) $request->query('step', $vendor?->onboarding_step ?: 1);
        $step = max(1, min(5, $step));

        $activeConfig = $vendor?->storefrontConfig;
        $rawSelected = $activeConfig?->preset_key ?? $vendor?->layout_preset ?? 'velora';
        $selectedPreset = $this->presetService->normalizePresetKeyForVendor((string) $rawSelected, $vendor);
        $effectiveConfig = $vendor ? $vendor->getStorefrontConfig(true) : [];
        $readiness = $vendor ? $this->presenter->evaluatePublishReadiness($vendor) : null;
        $portfolioCount = $vendor ? $vendor->portfolioProjects()->count() : 0;
        $servicesCount = $vendor ? Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('vertical', 'services')->count() : 0;
        $suggestedSlug = $vendor?->slug ?: ($vendor?->shop_name ? $this->subdomains->suggestFromName($vendor->shop_name) : '');
        $domain = config('marketplace.domain', 'marblemandi.org');

        return view('services.become-seller', compact(
            'categories',
            'presets',
            'vendor',
            'step',
            'selectedPreset',
            'effectiveConfig',
            'readiness',
            'portfolioCount',
            'servicesCount',
            'suggestedSlug',
            'domain'
        ))->with('existingVendor', $vendor);
    }

    public function checkSubdomain(Request $request)
    {
        $slug = (string) $request->query('slug', '');
        $vendor = auth()->user()?->vendor;

        return response()->json(
            $this->subdomains->checkAvailability($slug, $vendor)
        );
    }

    public function saveStep(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('info', 'Please sign in to save your provider website onboarding progress.');
        }

        $user = auth()->user();
        $step = (int) $request->input('step', 1);

        if ($step === 1) {
            return $this->handleStep1Identity($request, $user);
        }

        $vendor = $user->vendor()->with('storefrontConfig')->first();
        if (!$vendor) {
            return redirect()->route('services.onboarding', ['step' => 1])
                ->with('error', 'Please complete Step 1 (Identity) first.');
        }

        return match ($step) {
            2 => $this->handleStep2Subdomain($request, $vendor),
            3 => $this->handleStep3Preset($request, $vendor),
            4 => $this->handleStep4Portfolio($request, $vendor),
            5 => $this->handleStep5Publish($request, $vendor),
            default => redirect()->route('services.onboarding', ['step' => 1]),
        };
    }

    protected function handleStep1Identity(Request $request, $user)
    {
        $validated = $request->validate([
            'provider_mode' => 'required|in:individual,agency',
            'shop_name' => 'required|string|max:100',
            'headline' => 'required|string|max:180',
            'description' => 'required|string|min:15|max:3000',
            'skills' => 'required|string|max:400',
            'languages' => 'nullable|string|max:200',
            'work_mode' => 'required|in:remote,onsite,hybrid',
            'city' => 'nullable|string|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'years_experience' => 'nullable|integer|min:0|max:60',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $skillsArray = array_values(array_filter(array_map('trim', explode(',', $validated['skills']))));
        $languagesArray = !empty($validated['languages'])
            ? array_values(array_filter(array_map('trim', explode(',', $validated['languages']))))
            : ['English', 'Hindi'];

        $cleanPhone = preg_replace('/\D+/', '', (string) ($validated['contact_phone'] ?? $user->phone ?? '')) ?: null;
        if ($cleanPhone && strlen($cleanPhone) > 15) {
            $cleanPhone = substr($cleanPhone, -10);
        }

        $vendor = $user->vendor;
        $isNew = false;

        if (!$vendor) {
            $trashed = Vendor::onlyTrashed()->where('user_id', $user->id)->first();
            if ($trashed) {
                $trashed->restore();
                $vendor = $trashed;
            } else {
                $isNew = true;
                $draftSlug = $this->subdomains->suggestFromName($validated['shop_name']);
                $vendor = Vendor::create([
                    'user_id' => $user->id,
                    'slug' => $draftSlug,
                    'shop_name' => $validated['shop_name'],
                    'provider_mode' => $validated['provider_mode'],
                    'headline' => $validated['headline'],
                    'tagline' => $validated['headline'],
                    'vendor_type' => 'service',
                    'description' => $validated['description'],
                    'skills_json' => $skillsArray,
                    'languages_json' => $languagesArray,
                    'work_mode' => $validated['work_mode'],
                    'city' => ($validated['city'] ?? null) ?: 'India',
                    'hourly_rate' => isset($validated['hourly_rate']) && $validated['hourly_rate'] !== '' ? (int) $validated['hourly_rate'] : null,
                    'years_experience' => $validated['years_experience'] ?? null,
                    'contact_email' => ($validated['contact_email'] ?? null) ?: $user->email,
                    'contact_phone' => $cleanPhone,
                    'email' => $user->email,
                    'status' => 'pending',
                    'verification_status' => 'pending_review',
                    'operating_sectors' => ['services'],
                    'approved_at' => null,
                    'website_status' => 'draft',
                    'onboarding_step' => 2,
                    'layout_preset' => 'velora',
                    'public_contact_preferences' => [
                        'show_email' => true,
                        'show_phone' => false,
                        'show_city' => true,
                        'allow_direct_enquiry' => true,
                        'allow_k71_chat' => true,
                    ],
                    'rating' => 0,
                    'completed_projects' => 0,
                ]);
            }
        }

        if (!$isNew) {
            $vendor->update([
                'shop_name' => $validated['shop_name'],
                'provider_mode' => $validated['provider_mode'],
                'headline' => $validated['headline'],
                'tagline' => $validated['headline'],
                'vendor_type' => 'service',
                'description' => $validated['description'],
                'skills_json' => $skillsArray,
                'languages_json' => $languagesArray,
                'work_mode' => $validated['work_mode'],
                'city' => ($validated['city'] ?? null) ?: ($vendor->city ?: 'India'),
                'hourly_rate' => isset($validated['hourly_rate']) && $validated['hourly_rate'] !== '' ? (int) $validated['hourly_rate'] : $vendor->hourly_rate,
                'years_experience' => $validated['years_experience'] ?? $vendor->years_experience,
                'contact_email' => ($validated['contact_email'] ?? null) ?: ($vendor->contact_email ?: $user->email),
                'contact_phone' => $cleanPhone ?? $vendor->contact_phone,
                'status' => in_array($vendor->status, ['approved', 'active'], true) ? $vendor->status : 'approved',
                'approved_at' => $vendor->approved_at ?: now(),
                'onboarding_step' => max((int) ($vendor->onboarding_step ?? 1), 2),
            ]);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('vendors/' . $vendor->id . '/logo', 'public');
            k71_sync_public_storage($path);
            $vendor->update(['logo' => $path]);
        }

        $this->ensureVendorSetup($user, $vendor, false);

        return redirect()->route('services.onboarding', ['step' => 2])
            ->with('success', 'Step 1 saved! Now claim your personal website subdomain.');
    }

    protected function handleStep2Subdomain(Request $request, Vendor $vendor)
    {
        $request->validate([
            'subdomain' => 'required|string|min:3|max:30',
        ]);

        try {
            $newSlug = $this->subdomains->renameSubdomain($vendor, (string) $request->input('subdomain'));
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['subdomain' => $e->getMessage()]);
        }

        $vendor->update([
            'onboarding_step' => max((int) ($vendor->onboarding_step ?? 2), 3),
        ]);

        return redirect()->route('services.onboarding', ['step' => 3])
            ->with('success', "Subdomain `{$newSlug}` reserved for your website! Now choose your design preset.");
    }

    protected function handleStep3Preset(Request $request, Vendor $vendor)
    {
        $allowed = array_keys($this->presetService->getPresetsForVendor($vendor));
        $requested = strtolower(trim((string) $request->input('preset_key', 'velora')));

        if (!in_array($requested, $allowed, true)) {
            return back()->withErrors(['preset_key' => 'Please select a valid K71 website preset.']);
        }

        $draftConfig = $this->presetService->switchPresetPreservingContent($vendor, $requested);
        $draftConfig['preset'] = $requested;
        $sanitized = $this->presetService->sanitizeConfig($draftConfig, $vendor);
        $sanitized['preset'] = $requested;

        $isPublished = $vendor->isWebsitePublished();

        $configRecord = VendorStorefrontConfig::firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $requested,
                'schema_version' => 2,
                'draft_config' => $sanitized,
                'published_config' => $isPublished ? $sanitized : [],
                'is_published' => $isPublished,
                'last_saved_at' => now(),
            ]
        );

        $configRecord->update([
            'preset_key' => $requested,
            'draft_config' => $sanitized,
            'published_config' => $isPublished ? $sanitized : ($configRecord->published_config ?? []),
            'last_saved_at' => now(),
        ]);

        $vendor->update([
            'layout_preset' => $requested,
            'onboarding_step' => max((int) ($vendor->onboarding_step ?? 3), 4),
        ]);
        $vendor->bumpWebsiteCache();

        return redirect()->route('services.onboarding', ['step' => 4])
            ->with('success', ucfirst($requested) . ' preset selected! Set up your portfolio showcase & hero.');
    }

    protected function handleStep4Portfolio(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'hero_headline' => 'nullable|string|max:180',
            'hero_subtitle' => 'nullable|string|max:240',
            'about_title' => 'nullable|string|max:100',
            'cta_primary_text' => 'nullable|string|max:40',
            'website_link' => 'nullable|url|max:255',
            'linkedin_link' => 'nullable|url|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'portfolio_title' => 'nullable|string|max:180',
            'portfolio_category' => 'nullable|string|max:80',
            'portfolio_summary' => 'nullable|string|max:1000',
            'portfolio_cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ]);

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('vendors/' . $vendor->id . '/banner', 'public');
            k71_sync_public_storage($path);
            $vendor->banner = $path;
        }

        $links = array_filter([
            'website' => $validated['website_link'] ?? null,
            'linkedin' => $validated['linkedin_link'] ?? null,
        ]);
        if (!empty($links)) {
            $vendor->external_links = array_merge($vendor->external_links ?? [], $links);
        }

        if (!empty($validated['hero_headline'])) {
            $vendor->headline = $validated['hero_headline'];
        }
        $vendor->onboarding_step = max((int) ($vendor->onboarding_step ?? 4), 5);
        $vendor->save();

        // Update draft config with Step 4 customization
        $preset = $vendor->layout_preset ?: 'velora';
        $config = $vendor->getStorefrontConfig(true);
        $config['preset'] = $preset;
        $config['brand']['banner_url'] = $vendor->banner_url;
        $config['service_site'] = array_merge($config['service_site'] ?? [], array_filter([
            'hero_headline' => $validated['hero_headline'] ?? null,
            'hero_subtitle' => $validated['hero_subtitle'] ?? null,
            'about_title' => $validated['about_title'] ?? null,
            'cta_primary_text' => $validated['cta_primary_text'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''));

        $sanitized = $this->presetService->sanitizeConfig($config, $vendor);
        VendorStorefrontConfig::updateOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $preset,
                'schema_version' => 2,
                'draft_config' => $sanitized,
                'last_saved_at' => now(),
            ]
        );

        // Optional inline Portfolio Case Study creation
        if (!empty($validated['portfolio_title'])) {
            $coverPath = null;
            if ($request->hasFile('portfolio_cover')) {
                $coverPath = $request->file('portfolio_cover')->store('vendors/' . $vendor->id . '/portfolio', 'public');
                k71_sync_public_storage($coverPath);
            }
            $baseSlug = Str::slug($validated['portfolio_title']) ?: 'case-study';
            $pSlug = $baseSlug;
            $c = 1;
            while (PortfolioProject::where('vendor_id', $vendor->id)->where('slug', $pSlug)->exists()) {
                $pSlug = $baseSlug . '-' . $c++;
            }
            PortfolioProject::create([
                'vendor_id' => $vendor->id,
                'title' => $validated['portfolio_title'],
                'slug' => $pSlug,
                'work_category' => ($validated['portfolio_category'] ?? null) ?: 'Featured Work',
                'summary' => ($validated['portfolio_summary'] ?? null) ?: 'Featured client case study delivered through K71.',
                'description' => ($validated['portfolio_summary'] ?? null) ?: 'Featured client case study.',
                'cover_image' => $coverPath,
                'is_featured' => true,
                'status' => 'published',
            ]);
        }

        $vendor->bumpWebsiteCache();

        return redirect()->route('services.onboarding', ['step' => 5])
            ->with('success', 'Portfolio setup saved! Review your launch readiness checklist below.');
    }

    protected function handleStep5Publish(Request $request, Vendor $vendor)
    {
        $action = $request->input('publish_action', 'publish');

        if ($action === 'draft') {
            $vendor->update(['website_status' => 'draft']);
            if ($vendor->storefrontConfig) {
                $vendor->storefrontConfig->update(['is_published' => false]);
            }
            $vendor->bumpWebsiteCache();
            Cache::forget('sitemap_stores_xml');

            return redirect()->route('vendor.website.index')
                ->with('info', 'Your website has been saved as a Private Draft. Only you can view it via preview mode until you publish.');
        }

        $readiness = $this->presenter->evaluatePublishReadiness($vendor);
        if (!$readiness['can_publish']) {
            return redirect()->route('services.onboarding', ['step' => 5])
                ->with('error', 'Please complete all required onboarding items before publishing your website live.');
        }

        $presetKey = $vendor->layout_preset ?: 'velora';
        $configRecord = $vendor->storefrontConfig()->first();
        $draft = $configRecord?->draft_config ?: $this->presetService->getDefaultConfig($presetKey, $vendor);
        if (empty($draft['preset'])) {
            $draft['preset'] = $presetKey;
        }
        $sanitized = $this->presetService->sanitizeConfig($draft, $vendor);

        VendorStorefrontConfig::updateOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $sanitized['preset'],
                'schema_version' => 2,
                'draft_config' => $sanitized,
                'published_config' => $sanitized,
                'is_published' => true,
                'last_saved_at' => now(),
                'last_published_at' => now(),
            ]
        );

        $vendor->update([
            'status' => 'approved',
            'approved_at' => $vendor->approved_at ?: now(),
            'website_status' => 'published',
            'website_published_at' => now(),
            'onboarding_step' => 5,
        ]);

        VendorStorefrontRevision::create([
            'vendor_id' => $vendor->id,
            'preset_key' => $sanitized['preset'],
            'config_data' => $sanitized,
            'summary' => 'Launched personal service website live',
            'created_by' => auth()->id(),
        ]);

        $vendor->bumpWebsiteCache();
        Cache::forget('sitemap_stores_xml');

        return redirect()->route('vendor.website.index')
            ->with('success', 'Your personal service website is now LIVE at ' . $vendor->storefront_url . '!');
    }

    protected function ensureVendorSetup($user, Vendor $vendor, bool $publishConfig): void
    {
        VendorSetting::firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'shipping_type' => 'flat',
                'shipping_flat_rate' => 0,
                'processing_days' => 1,
                'return_window_days' => 0,
                'upi_enabled' => true,
                'cod_enabled' => false,
                'service_radius_km' => 25,
                'service_experience_years' => (int) ($vendor->years_experience ?? 1),
                'service_working_hours' => '9:00 AM - 7:00 PM',
            ]
        );

        $preset = $vendor->layout_preset ?: 'velora';
        $defaultConfig = $this->presetService->getDefaultConfig($preset, $vendor);
        VendorStorefrontConfig::firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $preset,
                'schema_version' => 2,
                'draft_config' => $defaultConfig,
                'published_config' => $publishConfig ? $defaultConfig : [],
                'is_published' => $publishConfig,
                'last_saved_at' => now(),
            ]
        );

        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web']);
        $user->roles()->syncWithoutDetaching([$customerRole->id, $vendorRole->id]);
        $user->unsetRelation('roles');
        $user->unsetRelation('vendor');
    }
}

