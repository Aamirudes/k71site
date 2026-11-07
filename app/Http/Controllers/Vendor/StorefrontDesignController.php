<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorStorefrontConfig;
use App\Models\VendorStorefrontRevision;
use App\Services\Storefront\StorefrontPresetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorefrontDesignController extends Controller
{
    protected StorefrontPresetService $presetService;

    public function __construct(StorefrontPresetService $presetService)
    {
        $this->presetService = $presetService;
    }

    /**
     * Store Design Overview & Presets Gallery.
     */
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        $presets = $this->presetService->getPresets();
        $defaultPreset = $this->presetService->getRecommendedPresetForVendor($vendor);
        $defaultConfig = $this->presetService->getDefaultConfig($defaultPreset, $vendor);

        $configRecord = VendorStorefrontConfig::firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $defaultPreset,
                'schema_version' => 2,
                'draft_config' => $defaultConfig,
                'published_config' => $defaultConfig,
                'is_published' => true,
                'last_saved_at' => now(),
                'last_published_at' => now(),
            ]
        );

        $activePresetKey = $configRecord->preset_key;
        $currentConfig = $configRecord->getEffectiveConfig(false);
        $hasUnpublishedDraft = !empty($configRecord->draft_config) && ($configRecord->draft_config != $configRecord->published_config);

        return view('vendor.design.index', compact(
            'vendor',
            'presets',
            'configRecord',
            'activePresetKey',
            'currentConfig',
            'hasUnpublishedDraft'
        ));
    }

    /**
     * Visual Storefront Editor Workspace.
     */
    public function editor(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        $defaultPreset = $this->presetService->getRecommendedPresetForVendor($vendor);
        $defaultConfig = $this->presetService->getDefaultConfig($defaultPreset, $vendor);

        $configRecord = VendorStorefrontConfig::firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $defaultPreset,
                'schema_version' => 2,
                'draft_config' => $defaultConfig,
                'published_config' => $defaultConfig,
                'is_published' => true,
                'last_saved_at' => now(),
                'last_published_at' => now(),
            ]
        );

        $draftConfig = !empty($configRecord->draft_config) 
            ? $configRecord->draft_config 
            : $this->presetService->getDefaultConfig($configRecord->preset_key, $vendor);

        $presets = $this->presetService->getPresets();
        $availableSections = $this->presetService->getAvailableSections();
        $vendorProducts = $vendor->products()->where('status', 'active')->select('id', 'title', 'base_price', 'slug')->get();
        $vendorCategories = \App\Models\Category::whereHas('products', fn($q) => $q->where('vendor_id', $vendor->id))->get();

        return view('vendor.design.editor', compact(
            'vendor',
            'configRecord',
            'draftConfig',
            'presets',
            'availableSections',
            'vendorProducts',
            'vendorCategories'
        ));
    }

    /**
     * Live preview iframe endpoint.
     */
    public function preview(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            abort(403);
        }

        $configRecord = $vendor->storefrontConfig;
        $config = $configRecord?->draft_config ?: $this->presetService->getDefaultConfig('atelier', $vendor);

        // Fetch vendor store data
        $query = $vendor->products()->with(['images', 'category', 'variants'])->where('status', 'active');
        if ($vendor->vendor_type !== 'used') {
            $query->excludeUsed();
        }
        $products = $query->paginate(16);

        $vendorCategories = \App\Models\Category::whereHas('products', fn($q) => $q->where('vendor_id', $vendor->id))->withCount(['products' => fn($q) => $q->where('vendor_id', $vendor->id)])->get();
        foreach ($vendorCategories as $cat) {
            $sampleProduct = $vendor->products()->where('category_id', $cat->id)->with('images')->latest()->first();
            $cat->sample_image = $sampleProduct?->images->first()?->path ?? null;
        }

        $featuredProducts = $vendor->products()->where('is_featured', true)->take(6)->get();
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $vendor->products()->latest()->take(6)->get();
        }

        $vendorShorts = $vendor->shorts()->where('status', 'published')->take(8)->get();
        $vendorReviews = \App\Models\Review::whereIn('product_id', function($q) use ($vendor) {
                $q->select('id')->from('products')->where('vendor_id', $vendor->id);
            })
            ->where('is_approved', true)
            ->with(['user', 'product.images'])
            ->latest()
            ->take(6)
            ->get();
        $isPreview = true;

        return view('storefront.show', compact(
            'vendor',
            'products',
            'vendorCategories',
            'featuredProducts',
            'vendorShorts',
            'vendorReviews',
            'config',
            'isPreview'
        ));
    }

    /**
     * Autosave and manual save of draft configuration.
     */
    public function saveDraft(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $rawConfig = $request->input('config');
        if (!is_array($rawConfig)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid configuration payload'], 422);
        }

        // Validate & sanitize incoming config
        $sanitizedConfig = $this->presetService->sanitizeConfig($rawConfig, $vendor);

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $sanitizedConfig['preset'] ?? 'atelier',
                'schema_version' => 2,
                'published_config' => $sanitizedConfig,
            ]
        );

        $configRecord->update([
            'preset_key' => $sanitizedConfig['preset'],
            'draft_config' => $sanitizedConfig,
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Draft saved successfully.',
            'last_saved_at' => now()->format('h:i:s A'),
            'preset' => $sanitizedConfig['preset'],
        ]);
    }

    /**
     * Publish draft configuration to live storefront.
     */
    public function publish(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $configRecord = $vendor->storefrontConfig;
        if (!$configRecord || empty($configRecord->draft_config)) {
            $draftConfig = $this->presetService->getDefaultConfig('atelier', $vendor);
        } else {
            $draftConfig = $configRecord->draft_config;
        }

        // Validate and sanitize before publishing
        $sanitizedConfig = $this->presetService->sanitizeConfig($draftConfig, $vendor);

        // Atomic publish update
        $configRecord->update([
            'preset_key' => $sanitizedConfig['preset'],
            'published_config' => $sanitizedConfig,
            'draft_config' => $sanitizedConfig,
            'is_published' => true,
            'last_published_at' => now(),
            'last_saved_at' => now(),
        ]);

        // Create revision entry for rollback capability
        VendorStorefrontRevision::create([
            'vendor_id' => $vendor->id,
            'preset_key' => $sanitizedConfig['preset'],
            'config_data' => $sanitizedConfig,
            'summary' => 'Published ' . ucfirst($sanitizedConfig['preset']) . ' design preset',
            'created_by' => auth()->id(),
        ]);

        // Keep core vendor table fields backward-compatible
        $social = is_array($vendor->social_links) ? $vendor->social_links : (json_decode($vendor->social_links, true) ?: []);
        $social['announcement_bar'] = $sanitizedConfig['header']['announcement_text'] ?? null;
        $social['announcement_bar_enabled'] = !empty($sanitizedConfig['header']['announcement_enabled']);
        $social['announcement_link'] = $sanitizedConfig['header']['announcement_link'] ?? null;
        
        $vendor->update([
            'layout_preset' => $sanitizedConfig['preset'],
            'social_links' => $social,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Congratulations! Your custom store design is published and live.',
            'public_url' => route('storefront.show', $vendor->slug),
            'last_published_at' => now()->format('M d, Y h:i A'),
        ]);
    }

    /**
     * Apply a new preset to draft.
     */
    public function applyPreset(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $allowedPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue', 'atelier', 'object', 'commerce', 'market', 'signature'];
        $requestedPreset = strtolower(trim((string)$request->input('preset')));
        if (!in_array($requestedPreset, $allowedPresets)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid preset selected.'], 422);
        }

        $normalizedPreset = $this->presetService->normalizePresetKey($requestedPreset);
        $newDraft = $this->presetService->getDefaultConfig($normalizedPreset, $vendor);
        $newDraft['preset'] = $requestedPreset;

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $requestedPreset,
                'schema_version' => 2,
                'published_config' => $newDraft,
            ]
        );

        $configRecord->update([
            'preset_key' => $requestedPreset,
            'draft_config' => $newDraft,
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Applied preset ' . ucfirst($requestedPreset) . ' to draft.',
            'preset' => $requestedPreset,
            'config' => $newDraft,
        ]);
    }

    /**
     * Instantly activate and publish a preset to live storefront.
     */
    public function activatePreset(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $allowedPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue', 'atelier', 'object', 'commerce', 'market', 'signature'];
        $requestedPreset = strtolower(trim((string)$request->input('preset')));
        if (!in_array($requestedPreset, $allowedPresets)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid preset selected.'], 422);
        }

        $normalizedPreset = $this->presetService->normalizePresetKey($requestedPreset);
        $newConfig = $this->presetService->getDefaultConfig($normalizedPreset, $vendor);
        $newConfig['preset'] = $requestedPreset;
        $sanitizedConfig = $this->presetService->sanitizeConfig($newConfig, $vendor);
        $sanitizedConfig['preset'] = $requestedPreset;

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $requestedPreset,
                'schema_version' => 2,
                'published_config' => $sanitizedConfig,
            ]
        );

        $configRecord->update([
            'preset_key' => $requestedPreset,
            'draft_config' => $sanitizedConfig,
            'published_config' => $sanitizedConfig,
            'is_published' => true,
            'last_saved_at' => now(),
            'last_published_at' => now(),
        ]);

        // Keep vendor table updated
        $vendor->update(['layout_preset' => $requestedPreset]);

        // Create revision entry
        VendorStorefrontRevision::create([
            'vendor_id' => $vendor->id,
            'preset_key' => $requestedPreset,
            'config_data' => $sanitizedConfig,
            'summary' => 'Activated ' . ucfirst($requestedPreset) . ' preset live',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Preset ' . ucfirst($requestedPreset) . ' is now activated and live on your storefront!',
            'preset' => $requestedPreset,
            'public_url' => route('storefront.show', $vendor->slug),
        ]);
    }

    /**
     * Save quick brand customizer settings (essential controls: logo, name, banner, accent, layout).
     */
    public function saveQuickSettings(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $configRecord = $vendor->storefrontConfig;
        $presetKey = $configRecord?->preset_key ?? 'velora';
        $currentConfig = $configRecord?->getEffectiveConfig(false) ?: $this->presetService->getDefaultConfig($presetKey, $vendor);

        // Store Name
        if ($request->filled('shop_name')) {
            $name = trim($request->input('shop_name'));
            $vendor->shop_name = $name;
            $currentConfig['brand']['store_name'] = $name;
        }

        // Store Logo File Upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('vendors/' . $vendor->id . '/logo', 'public');
            $logoUrl = asset('storage/' . $path);
            $vendor->logo = $path;
            $currentConfig['brand']['logo_url'] = $logoUrl;
        }

        // Store Banner File Upload
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('vendors/' . $vendor->id . '/banner', 'public');
            $bannerUrl = asset('storage/' . $path);
            $vendor->banner = $path;
            $currentConfig['brand']['banner_url'] = $bannerUrl;
        }

        // Banner Direct URL (if text URL given)
        if ($request->filled('banner_url') && !$request->hasFile('banner')) {
            $currentConfig['brand']['banner_url'] = trim($request->input('banner_url'));
        }

        // Hero Headline & Link
        if ($request->has('hero_headline')) {
            $hHeadline = trim($request->input('hero_headline', ''));
            $currentConfig['hero']['title'] = $hHeadline;
            $currentConfig['brand']['hero_headline'] = $hHeadline;
        }
        if ($request->has('hero_link')) {
            $currentConfig['hero']['link'] = trim($request->input('hero_link', '#catalog'));
        }

        // Header elements reordering
        if ($request->has('header_elements_order')) {
            $rawOrder = $request->input('header_elements_order');
            if (is_string($rawOrder)) {
                $rawOrder = json_decode($rawOrder, true) ?: explode(',', $rawOrder);
            }
            if (is_array($rawOrder)) {
                $currentConfig['header']['elements_order'] = array_values(array_unique(array_intersect($rawOrder, ['hamburger', 'logo', 'name', 'cart', 'search'])));
            }
        }

        if ($request->has('transparent_on_hero')) {
            $currentConfig['header']['transparent_on_hero'] = $request->boolean('transparent_on_hero');
        }

        // Multi-slides processing (for 100vh hero carousel)
        $slidesInput = $request->input('slides', []);
        if (is_string($slidesInput)) {
            $slidesInput = json_decode($slidesInput, true) ?: [];
        }

        if (!empty($slidesInput) && is_array($slidesInput)) {
            $existingSlides = $currentConfig['hero']['slides'] ?? [];
            $processedSlides = [];
            foreach ($slidesInput as $idx => $sData) {
                $slideImg = $sData['image'] ?? ($existingSlides[$idx]['image'] ?? '');
                
                // Check if file uploaded for this slide index
                if ($request->hasFile("slide_{$idx}_file")) {
                    $sFile = $request->file("slide_{$idx}_file");
                    if ($sFile && $sFile->isValid()) {
                        $sPath = $sFile->store('vendors/' . $vendor->id . '/slides', 'public');
                        $slideImg = asset('storage/' . $sPath);
                    }
                }

                $processedSlides[] = [
                    'image' => $slideImg,
                    'title' => trim($sData['title'] ?? ''),
                    'subtitle' => trim($sData['subtitle'] ?? ''),
                    'button_text' => trim($sData['button_text'] ?? 'Explore Boutique'),
                    'link' => trim($sData['link'] ?? '#catalog'),
                ];
            }
            $currentConfig['hero']['slides'] = $processedSlides;
        }

        // Product Layout Mode: grid vs slider
        if ($request->filled('product_layout')) {
            $layout = in_array($request->input('product_layout'), ['grid', 'slider']) ? $request->input('product_layout') : 'grid';
            $currentConfig['products']['layout'] = $layout;
        }

        // Update brand values
        if ($request->has('tagline')) {
            $currentConfig['brand']['description'] = trim($request->input('tagline', ''));
        }
        if ($request->has('announcement_text')) {
            $currentConfig['header']['announcement_text'] = trim($request->input('announcement_text', ''));
        }
        if ($request->has('announcement_enabled')) {
            $currentConfig['header']['announcement_enabled'] = $request->boolean('announcement_enabled');
        }
        if ($request->has('announcement_link')) {
            $currentConfig['header']['announcement_link'] = trim($request->input('announcement_link', ''));
        }
        if ($request->has('chroma_strip_text')) {
            $currentConfig['header']['chroma_strip_text'] = trim($request->input('chroma_strip_text', ''));
        }
        if ($request->has('chroma_strip_enabled') || $request->has('chroma_strip_text')) {
            $currentConfig['header']['chroma_strip_enabled'] = $request->boolean('chroma_strip_enabled');
        }
        if ($request->filled('chroma_strip_bg')) {
            $currentConfig['header']['chroma_strip_bg'] = trim($request->input('chroma_strip_bg', '#2563eb'));
        }
        if ($request->filled('accent_color')) {
            $accent = $request->input('accent_color');
            $currentConfig['theme']['accent_color'] = $accent;
        }

        // WhatsApp & Instagram Social Connect
        if ($request->has('whatsapp')) {
            $wa = trim($request->input('whatsapp'));
            $currentConfig['brand']['whatsapp'] = $wa;
        }
        if ($request->has('instagram')) {
            $ig = trim($request->input('instagram'));
            $currentConfig['brand']['instagram'] = $ig;
            $vSettings = $vendor->settings;
            if ($vSettings) {
                $vSettings->instagram = $ig;
                $vSettings->save();
            }
        }

        // Happy Customers Slider Settings
        if ($request->has('happy_customers_enabled') || $request->has('happy_customers_title')) {
            $currentConfig['happy_customers']['enabled'] = $request->boolean('happy_customers_enabled');
            if ($request->filled('happy_customers_title')) {
                $currentConfig['happy_customers']['title'] = trim($request->input('happy_customers_title'));
            }
            if ($request->filled('happy_customers_subtitle')) {
                $currentConfig['happy_customers']['subtitle'] = trim($request->input('happy_customers_subtitle'));
            }
        }

        $sanitizedConfig = $this->presetService->sanitizeConfig($currentConfig, $vendor);

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $presetKey,
                'schema_version' => 2,
                'published_config' => $sanitizedConfig,
            ]
        );

        $configRecord->update([
            'draft_config' => $sanitizedConfig,
            'published_config' => $sanitizedConfig,
            'is_published' => true,
            'last_saved_at' => now(),
            'last_published_at' => now(),
        ]);

        // Keep vendor table updated
        $social = is_array($vendor->social_links) ? $vendor->social_links : (json_decode($vendor->social_links, true) ?: []);
        $social['announcement_bar'] = $sanitizedConfig['header']['announcement_text'] ?? null;
        $social['announcement_bar_enabled'] = !empty($sanitizedConfig['header']['announcement_enabled']);
        $social['announcement_link'] = $sanitizedConfig['header']['announcement_link'] ?? null;

        $vendor->description = $sanitizedConfig['brand']['description'] ?? $vendor->description;
        $vendor->social_links = $social;
        $vendor->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Storefront essentials saved and published live!',
            'public_url' => route('storefront.show', $vendor->slug),
        ]);
    }

    /**
     * Reset draft to preset defaults.
     */
    public function resetPreset(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $configRecord = $vendor->storefrontConfig;
        $presetKey = $configRecord?->preset_key ?? 'atelier';
        $freshConfig = $this->presetService->getDefaultConfig($presetKey, $vendor);

        $configRecord?->update([
            'draft_config' => $freshConfig,
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reset sections and styling to preset defaults.',
            'config' => $freshConfig,
        ]);
    }

    /**
     * Get revision history list.
     */
    public function revisions(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $revisions = VendorStorefrontRevision::where('vendor_id', $vendor->id)
            ->latest()
            ->take(15)
            ->get(['id', 'preset_key', 'summary', 'created_at']);

        return response()->json([
            'status' => 'success',
            'revisions' => $revisions,
        ]);
    }

    /**
     * Restore an earlier revision.
     */
    public function restoreRevision(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $revisionId = $request->input('revision_id');
        $revision = VendorStorefrontRevision::where('vendor_id', $vendor->id)->findOrFail($revisionId);

        $configRecord = $vendor->storefrontConfig;
        $configRecord->update([
            'preset_key' => $revision->preset_key,
            'draft_config' => $revision->config_data,
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Restored revision from ' . $revision->created_at->format('M d, Y h:i A') . ' to draft.',
            'config' => $revision->config_data,
        ]);
    }

    /**
     * Upload storefront media asset (banner, hero, logo).
     */
    public function uploadMedia(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ]);

        $file = $request->file('image');
        $path = $file->store('vendors/' . $vendor->id . '/storefront', 'public');

        // Ensure public accessibility
        $target = public_path('storage/' . $path);
        @mkdir(dirname($target), 0755, true);
        @copy(storage_path('app/public/' . $path), $target);

        $url = asset('storage/' . $path);

        return response()->json([
            'status' => 'success',
            'url' => $url,
            'path' => $path,
        ]);
    }
}
