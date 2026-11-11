<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorStorefrontConfig;
use App\Models\VendorStorefrontRevision;
use App\Services\Services\ServiceWebsitePresenter;
use App\Services\Services\SubdomainManagerService;
use App\Services\Storefront\StorefrontPresetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteHubController extends Controller
{
    public function __construct(
        protected StorefrontPresetService $presetService,
        protected ServiceWebsitePresenter $presenter,
        protected SubdomainManagerService $subdomains
    ) {}

    protected function resolveServiceVendor(Request $request)
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor()->with('storefrontConfig')->first();
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        return $vendor;
    }

    public function index(Request $request): View
    {
        $vendor = $this->resolveServiceVendor($request);
        $config = $vendor->getStorefrontConfig(true);
        $presets = $this->presetService->getPresets();
        $activePreset = $config['preset'] ?? $vendor->layout_preset ?? 'velora';
        $readiness = $this->presenter->evaluatePublishReadiness($vendor);
        $siteData = $this->presenter->build($vendor, true, $config);
        $domain = config('marketplace.domain', 'marblemandi.org');

        $recentSlugRedirects = $vendor->subdomainHistories()
            ->where('is_active_redirect', true)
            ->latest('id')
            ->take(5)
            ->get();

        return view('vendor.website.index', compact(
            'vendor',
            'config',
            'presets',
            'activePreset',
            'readiness',
            'siteData',
            'domain',
            'recentSlugRedirects'
        ));
    }

    public function switchPreset(Request $request): RedirectResponse
    {
        $vendor = $this->resolveServiceVendor($request);
        $validated = $request->validate([
            'preset_key' => 'required|string|max:50',
        ]);

        $requestedPreset = strtolower(trim($validated['preset_key']));
        $currentConfig = $vendor->getStorefrontConfig(true);
        $nextConfig = $this->presetService->switchPresetPreservingContent($vendor, $requestedPreset, $currentConfig);
        $sanitized = $this->presetService->sanitizeConfig($nextConfig, $vendor);

        $record = VendorStorefrontConfig::firstOrNew(['vendor_id' => $vendor->id]);
        $record->preset_key = $requestedPreset;
        $record->schema_version = $record->schema_version ?: 2;
        $record->draft_config = $sanitized;
        $record->published_config = $sanitized;
        $record->last_saved_at = now();
        $record->is_published = $vendor->isWebsitePublished();
        if ($record->is_published) {
            $record->last_published_at = $record->last_published_at ?: now();
        }
        $record->save();

        $vendor->update(['layout_preset' => $requestedPreset]);
        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Preset switched to "' . strtoupper($requestedPreset) . '" while preserving all your content, portfolio, and services.');
    }

    public function updateIdentity(Request $request): RedirectResponse
    {
        $vendor = $this->resolveServiceVendor($request);

        $validated = $request->validate([
            'shop_name' => 'required|string|max:120',
            'provider_mode' => 'required|in:individual,agency',
            'headline' => 'required|string|max:160',
            'hero_subtitle' => 'nullable|string|max:240',
            'description' => 'required|string|min:15|max:4000',
            'work_mode' => 'required|in:remote,onsite,hybrid',
            'availability_status' => 'required|in:available,busy,away',
            'response_time' => 'nullable|string|max:60',
            'years_experience' => 'nullable|integer|min:0|max:70',
            'hourly_rate' => 'nullable|numeric|min:0|max:500000',
            'skills_input' => 'nullable|string|max:600',
            'languages_input' => 'nullable|string|max:300',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'cta_primary_text' => 'nullable|string|max:40',
            'cta_secondary_text' => 'nullable|string|max:40',
            'external_website' => 'nullable|url|max:255',
            'external_linkedin' => 'nullable|url|max:255',
            'external_github' => 'nullable|url|max:255',
            'external_behance' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:6144',
        ]);

        $skills = collect(explode(',', (string) ($validated['skills_input'] ?? '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();

        $languages = collect(explode(',', (string) ($validated['languages_input'] ?? '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $externalLinks = array_filter([
            'website' => $validated['external_website'] ?? null,
            'linkedin' => $validated['external_linkedin'] ?? null,
            'github' => $validated['external_github'] ?? null,
            'portfolio' => $validated['external_behance'] ?? null,
        ]);

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if ($vendor->logo) {
                k71_delete_public_storage($vendor->logo);
            }
            $logoPath = $request->file('logo')->store('vendors/' . $vendor->id, 'public');
            k71_sync_public_storage($logoPath);
            $vendor->logo = $logoPath;
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($vendor->banner) {
                k71_delete_public_storage($vendor->banner);
            }
            $bannerPath = $request->file('banner')->store('vendors/' . $vendor->id, 'public');
            k71_sync_public_storage($bannerPath);
            $vendor->banner = $bannerPath;
        }

        $vendor->fill([
            'shop_name' => $validated['shop_name'],
            'provider_mode' => $validated['provider_mode'],
            'headline' => $validated['headline'],
            'tagline' => ($validated['hero_subtitle'] ?? null) ?: $validated['headline'],
            'description' => $validated['description'],
            'work_mode' => $validated['work_mode'],
            'availability_status' => $validated['availability_status'],
            'response_time' => ($validated['response_time'] ?? null) ?: $vendor->response_time,
            'years_experience' => $validated['years_experience'] ?? $vendor->years_experience,
            'hourly_rate' => $validated['hourly_rate'] ?? $vendor->hourly_rate,
            'skills_json' => !empty($skills) ? $skills : ($vendor->skills_json ?? []),
            'languages_json' => !empty($languages) ? $languages : ($vendor->languages_json ?? []),
            'city' => $validated['city'] ?? $vendor->city,
            'state' => $validated['state'] ?? $vendor->state,
            'external_links' => $externalLinks,
        ])->save();

        // Sync headline / brand copy into storefront config
        $currentConfig = $vendor->getStorefrontConfig(true);
        $currentConfig['brand']['store_name'] = $validated['shop_name'];
        $currentConfig['brand']['tagline'] = ($validated['hero_subtitle'] ?? null) ?: $validated['headline'];
        $currentConfig['brand']['description'] = $validated['description'];
        $currentConfig['service_site'] = array_merge($currentConfig['service_site'] ?? [], [
            'hero_headline' => $validated['headline'],
            'hero_subtitle' => $validated['hero_subtitle'] ?? '',
            'cta_primary_text' => ($validated['cta_primary_text'] ?? null) ?: 'Start a Project',
            'cta_secondary_text' => ($validated['cta_secondary_text'] ?? null) ?: 'View Work',
        ]);

        $sanitized = $this->presetService->sanitizeConfig($currentConfig, $vendor);
        $record = VendorStorefrontConfig::firstOrNew(['vendor_id' => $vendor->id]);
        $record->preset_key = $sanitized['preset'];
        $record->draft_config = $sanitized;
        $record->published_config = $sanitized;
        $record->save();

        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Website identity, headline, and credentials updated.');
    }

    public function updateSections(Request $request): RedirectResponse
    {
        $vendor = $this->resolveServiceVendor($request);

        $validated = $request->validate([
            'vis_hero' => 'nullable|boolean',
            'vis_portfolio' => 'nullable|boolean',
            'vis_services' => 'nullable|boolean',
            'vis_about' => 'nullable|boolean',
            'vis_reviews' => 'nullable|boolean',
            'vis_contact' => 'nullable|boolean',
            'nav_overview' => 'nullable|string|max:24',
            'nav_work' => 'nullable|string|max:24',
            'nav_services' => 'nullable|string|max:24',
            'nav_about' => 'nullable|string|max:24',
            'nav_reviews' => 'nullable|string|max:24',
            'nav_contact' => 'nullable|string|max:24',
        ]);

        $currentConfig = $vendor->getStorefrontConfig(true);
        $serviceSite = $currentConfig['service_site'] ?? [];
        $serviceSite['section_visibility'] = [
            'hero' => $request->boolean('vis_hero'),
            'portfolio' => $request->boolean('vis_portfolio'),
            'services' => $request->boolean('vis_services'),
            'about' => $request->boolean('vis_about'),
            'reviews' => $request->boolean('vis_reviews'),
            'contact' => $request->boolean('vis_contact'),
        ];
        $serviceSite['nav_labels'] = [
            'overview' => trim((string) ($validated['nav_overview'] ?? '')) ?: 'Overview',
            'work' => trim((string) ($validated['nav_work'] ?? '')) ?: 'Work',
            'services' => trim((string) ($validated['nav_services'] ?? '')) ?: 'Services',
            'about' => trim((string) ($validated['nav_about'] ?? '')) ?: 'About',
            'reviews' => trim((string) ($validated['nav_reviews'] ?? '')) ?: 'Reviews',
            'contact' => trim((string) ($validated['nav_contact'] ?? '')) ?: 'Contact',
        ];
        $currentConfig['service_site'] = $serviceSite;

        $sanitized = $this->presetService->sanitizeConfig($currentConfig, $vendor);
        $record = VendorStorefrontConfig::firstOrNew(['vendor_id' => $vendor->id]);
        $record->preset_key = $sanitized['preset'];
        $record->draft_config = $sanitized;
        $record->published_config = $sanitized;
        $record->save();

        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Navigation labels and website section visibility saved.');
    }

    public function updateContactPrivacy(Request $request): RedirectResponse
    {
        $vendor = $this->resolveServiceVendor($request);

        $validated = $request->validate([
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:25',
            'show_email' => 'nullable|boolean',
            'show_phone' => 'nullable|boolean',
            'show_city' => 'nullable|boolean',
            'allow_direct_enquiry' => 'nullable|boolean',
            'allow_k71_chat' => 'nullable|boolean',
        ]);

        $vendor->update([
            'contact_email' => $validated['contact_email'] ?? $vendor->contact_email,
            'contact_phone' => $validated['contact_phone'] ?? $vendor->contact_phone,
            'public_contact_preferences' => [
                'show_email' => $request->boolean('show_email'),
                'show_phone' => $request->boolean('show_phone'),
                'show_city' => $request->boolean('show_city'),
                'allow_direct_enquiry' => $request->boolean('allow_direct_enquiry'),
                'allow_k71_chat' => $request->boolean('allow_k71_chat'),
            ],
        ]);

        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Public contact privacy settings updated.');
    }

    public function updateSubdomain(Request $request): RedirectResponse
    {
        $vendor = $this->resolveServiceVendor($request);
        $validated = $request->validate([
            'slug' => 'required|string|min:3|max:63',
        ]);

        $check = $this->subdomains->checkAvailability($validated['slug'], $vendor);
        if (!$check['available']) {
            return back()->withErrors(['slug' => $check['message'] ?? 'Subdomain unavailable.'])->withInput();
        }

        $oldSlug = $vendor->slug;
        $this->subdomains->renameSubdomain($vendor, $check['slug']);

        return back()->with('success', "Website subdomain updated from '{$oldSlug}' to '{$vendor->slug}'. Automatic 301 redirects from the old address are now active.");
    }

    public function togglePublish(Request $request): RedirectResponse
    {
        $vendor = $this->resolveServiceVendor($request);
        $action = $request->input('action', $vendor->isWebsitePublished() ? 'unpublish' : 'publish');

        $record = VendorStorefrontConfig::firstOrNew(['vendor_id' => $vendor->id]);
        $currentConfig = $vendor->getStorefrontConfig(true);
        $record->preset_key = $currentConfig['preset'] ?? ($vendor->layout_preset ?: 'velora');

        if ($action === 'unpublish') {
            $vendor->update(['website_status' => 'draft']);
            $record->schema_version = $record->schema_version ?: 2;
            $record->draft_config = $currentConfig;
            $record->is_published = false;
            $record->save();
            $vendor->bumpWebsiteCache();
            \Illuminate\Support\Facades\Cache::forget('sitemap_stores_xml');

            return back()->with('success', 'Your personal website is now in private Draft mode.');
        }

        $readiness = $this->presenter->evaluatePublishReadiness($vendor);
        if (!$readiness['can_publish']) {
            $missingLabels = collect($readiness['missing'])->pluck('label')->implode(', ');
            return back()->with('error', 'Complete required items before publishing: ' . $missingLabels);
        }

        $record->schema_version = $record->schema_version ?: 2;
        $record->draft_config = $currentConfig;
        $record->published_config = $currentConfig;
        $record->is_published = true;
        $record->last_saved_at = now();
        $record->last_published_at = now();
        $record->save();

        VendorStorefrontRevision::create([
            'vendor_id' => $vendor->id,
            'preset_key' => $record->preset_key,
            'config_data' => $currentConfig,
            'summary' => 'Published via Provider Website Hub',
            'created_by' => auth()->id(),
        ]);

        $vendor->update([
            'website_status' => 'published',
            'website_published_at' => $vendor->website_published_at ?: now(),
        ]);
        $vendor->bumpWebsiteCache();
        \Illuminate\Support\Facades\Cache::forget('sitemap_stores_xml');

        return back()->with('success', 'Your personal website is now LIVE at ' . $vendor->storefront_url);
    }
}
