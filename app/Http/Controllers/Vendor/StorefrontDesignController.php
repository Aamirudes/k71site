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

        $presets = $this->presetService->getPresetsForVendor($vendor);
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

        $activePresetKey = $this->presetService->normalizePresetKeyForVendor((string) $configRecord->preset_key, $vendor);
        $currentConfig = $vendor->getStorefrontConfig(false);
        $hasUnpublishedDraft = !empty($configRecord->draft_config) && ($configRecord->draft_config != $configRecord->published_config);
        $vendorScrollSequence = $vendor->hasKineticScrollAccess()
            ? \App\Services\ScrollSequenceService::resolveVendorSequence($vendor, $currentConfig)
            : ['frames' => [], 'count' => 0, 'video_url' => null];

        return view('vendor.design.index', compact(
            'vendor',
            'presets',
            'configRecord',
            'activePresetKey',
            'currentConfig',
            'hasUnpublishedDraft',
            'vendorScrollSequence'
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

        $draftConfig = $vendor->getStorefrontConfig(true);

        $presets = $this->presetService->getPresetsForVendor($vendor);
        $availableSections = $this->presetService->getAvailableSections($vendor);
        $vendorProducts = $vendor->products()->where('status', 'active')->select('id', 'title', 'base_price', 'slug')->get();
        $vendorCategories = \App\Models\Category::whereHas('products', fn($q) => $q->where('vendor_id', $vendor->id))->get();
        $vendorScrollSequence = $vendor->hasKineticScrollAccess()
            ? \App\Services\ScrollSequenceService::resolveVendorSequence($vendor, $draftConfig)
            : ['frames' => [], 'count' => 0, 'video_url' => null];

        return view('vendor.design.editor', compact(
            'vendor',
            'configRecord',
            'draftConfig',
            'presets',
            'availableSections',
            'vendorProducts',
            'vendorCategories',
            'vendorScrollSequence'
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
        $config = $vendor->getStorefrontConfig(true);

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
            ->take(24)
            ->get();
        $isPreview = true;
        $vendorScrollSequence = $vendor->hasKineticScrollAccess()
            ? \App\Services\ScrollSequenceService::resolveVendorSequence($vendor, $config)
            : ['frames' => [], 'count' => 0, 'video_url' => null];

        return view('storefront.show', compact(
            'vendor',
            'products',
            'vendorCategories',
            'featuredProducts',
            'vendorShorts',
            'vendorReviews',
            'config',
            'vendorScrollSequence',
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

        $reqPreset = strtolower(trim((string) ($rawConfig['preset'] ?? '')));
        if ($this->presetService->normalizePresetKey($reqPreset) === 'kinetic' && !$vendor->hasKineticScrollAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Scroll Motion is not enabled for your boutique.'], 403);
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
            $recommended = $vendor->layout_preset ?: $this->presetService->getRecommendedPresetForVendor($vendor);
            $draftConfig = $this->presetService->getDefaultConfig($recommended, $vendor);
        } else {
            $draftConfig = $configRecord->draft_config;
        }

        // Validate and sanitize before publishing
        $sanitizedConfig = $this->presetService->sanitizeConfig($draftConfig, $vendor);

        if (!$configRecord) {
            $configRecord = $vendor->storefrontConfig()->firstOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'preset_key' => $sanitizedConfig['preset'],
                    'schema_version' => 2,
                    'draft_config' => $sanitizedConfig,
                    'published_config' => $sanitizedConfig,
                    'is_published' => true,
                    'last_published_at' => now(),
                    'last_saved_at' => now(),
                ]
            );
        } else {
            // Atomic publish update
            $configRecord->update([
                'preset_key' => $sanitizedConfig['preset'],
                'published_config' => $sanitizedConfig,
                'draft_config' => $sanitizedConfig,
                'is_published' => true,
                'last_published_at' => now(),
                'last_saved_at' => now(),
            ]);
        }

        // Create revision entry for rollback capability
        VendorStorefrontRevision::create([
            'vendor_id' => $vendor->id,
            'preset_key' => $sanitizedConfig['preset'],
            'config_data' => $sanitizedConfig,
            'summary' => 'Published ' . ucfirst($sanitizedConfig['preset']) . ' design preset',
            'created_by' => auth()->id(),
        ]);

        // Keep core vendor table fields backward-compatible
        $social = is_array($vendor->social_links) ? $vendor->social_links : (json_decode((string) ($vendor->social_links ?? ''), true) ?: []);
        $social['announcement_bar'] = $sanitizedConfig['header']['announcement_text'] ?? null;
        $social['announcement_bar_enabled'] = !empty($sanitizedConfig['header']['announcement_enabled']);
        $social['announcement_link'] = $sanitizedConfig['header']['announcement_link'] ?? null;
        
        $vendor->update([
            'layout_preset' => $sanitizedConfig['preset'],
            'social_links' => $social,
        ]);
        $vendor->bumpWebsiteCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Congratulations! Your custom store design is published and live.',
            'public_url' => $vendor->storefront_url,
            'last_published_at' => now()->format('M d, Y h:i A'),
        ]);
    }

    /**
     * Apply a new preset to draft non-destructively (preserving user content & sections).
     */
    public function applyPreset(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $allowedPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue', 'kinetic', 'atelier', 'object', 'commerce', 'market', 'signature'];
        $requestedPreset = strtolower(trim((string)$request->input('preset')));
        if (!in_array($requestedPreset, $allowedPresets)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid preset selected.'], 422);
        }
        if ($this->presetService->normalizePresetKey($requestedPreset) === 'kinetic' && !$vendor->hasKineticScrollAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Scroll Motion is not enabled for your boutique.'], 403);
        }

        $newDraft = $this->presetService->switchPresetPreservingContent($vendor, $requestedPreset);
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
     * Instantly activate and publish a preset to live storefront non-destructively.
     */
    public function activatePreset(Request $request)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $allowedPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue', 'kinetic', 'atelier', 'object', 'commerce', 'market', 'signature'];
        $requestedPreset = strtolower(trim((string)$request->input('preset')));
        if (!in_array($requestedPreset, $allowedPresets)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid preset selected.'], 422);
        }
        if ($this->presetService->normalizePresetKey($requestedPreset) === 'kinetic' && !$vendor->hasKineticScrollAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Scroll Motion is not enabled for your boutique.'], 403);
        }

        $newConfig = $this->presetService->switchPresetPreservingContent($vendor, $requestedPreset);
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
        $vendor->bumpWebsiteCache();

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
            'public_url' => $vendor->storefront_url,
        ]);
    }

    /**
     * Save quick brand customizer settings (essential controls: logo, name, banner, accent, layout).
     */
    public function saveQuickSettings(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            $mirrorToPublic = function (string $relativePath): void {
                k71_sync_public_storage($relativePath);
            };

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
            $mirrorToPublic($path);
            $logoUrl = '/storage/' . $path;
            $vendor->logo = $path;
            $currentConfig['brand']['logo_url'] = $logoUrl;
        }

        // Store Banner File Upload
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('vendors/' . $vendor->id . '/banner', 'public');
            $mirrorToPublic($path);
            $bannerUrl = '/storage/' . $path;
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
                if (!is_array($sData)) continue;
                $slideImg = $sData['image'] ?? ($existingSlides[$idx]['image'] ?? '');
                
                // Check if file uploaded for this slide index
                if ($request->hasFile("slide_{$idx}_file")) {
                    $sFile = $request->file("slide_{$idx}_file");
                    if ($sFile && $sFile->isValid()) {
                        $sPath = $sFile->store('vendors/' . $vendor->id . '/slides', 'public');
                        $mirrorToPublic($sPath);
                        $slideImg = '/storage/' . $sPath;
                    }
                }

                $rawTextColor = trim((string) ($sData['text_color'] ?? ($existingSlides[$idx]['text_color'] ?? '#ffffff')));
                $slideTextColor = preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $rawTextColor) ? $rawTextColor : '#ffffff';

                $processedSlides[] = [
                    'image' => $slideImg,
                    'title' => trim($sData['title'] ?? ''),
                    'subtitle' => trim($sData['subtitle'] ?? ''),
                    'button_text' => trim($sData['button_text'] ?? 'Explore Boutique'),
                    'link' => trim($sData['link'] ?? '#catalog'),
                    'text_color' => $slideTextColor,
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

        // Kinetic Video-to-JPG Scroll Sequence Controls (only for authorized vendors)
        if ($vendor->hasKineticScrollAccess() && (
            $request->has('scroll_seq_enabled')
            || $request->has('scroll_seq_height_vh')
            || $request->has('scroll_seq_beat1_title')
            || $request->has('scroll_seq_beat2_title')
            || $request->has('scroll_seq_beat3_title')
        )) {
            $seqCfg = is_array($currentConfig['scroll_sequence'] ?? null) ? $currentConfig['scroll_sequence'] : [];
            if ($request->has('scroll_seq_enabled')) {
                $seqCfg['enabled'] = $request->boolean('scroll_seq_enabled');
            }
            if ($request->filled('scroll_seq_height_vh')) {
                $seqCfg['height_vh'] = max(180, min(600, (int) $request->input('scroll_seq_height_vh')));
            }
            if ($request->filled('scroll_seq_smoothing')) {
                $seqCfg['smoothing'] = max(0.05, min(0.45, round((float) $request->input('scroll_seq_smoothing'), 2)));
            }
            foreach ([
                'scroll_seq_beat1_kicker' => 'beat1_kicker',
                'scroll_seq_beat1_title' => 'beat1_title',
                'scroll_seq_beat1_desc' => 'beat1_desc',
                'scroll_seq_beat2_kicker' => 'beat2_kicker',
                'scroll_seq_beat2_title' => 'beat2_title',
                'scroll_seq_beat2_desc' => 'beat2_desc',
                'scroll_seq_beat3_kicker' => 'beat3_kicker',
                'scroll_seq_beat3_title' => 'beat3_title',
                'scroll_seq_beat3_desc' => 'beat3_desc',
                'scroll_seq_cta_text' => 'cta_text',
            ] as $inputKey => $cfgKey) {
                if ($request->has($inputKey)) {
                    $seqCfg[$cfgKey] = trim((string) $request->input($inputKey, ''));
                }
            }
            $currentConfig['scroll_sequence'] = $seqCfg;
        }

        // WhatsApp & Instagram Social Connect
        $social = is_array($vendor->social_links) ? $vendor->social_links : (json_decode((string) ($vendor->social_links ?? ''), true) ?: []);
        if ($request->has('whatsapp')) {
            $wa = trim($request->input('whatsapp'));
            $currentConfig['brand']['whatsapp'] = $wa;
            $social['whatsapp'] = $wa;
        }
        if ($request->has('instagram')) {
            $ig = trim($request->input('instagram'));
            $currentConfig['brand']['instagram'] = $ig;
            $social['instagram'] = $ig;
        }

        // Happy Customers Slider Settings & Items Management
        if ($request->has('happy_customers_enabled') || $request->has('happy_customers_title')) {
            $currentConfig['happy_customers']['enabled'] = $request->boolean('happy_customers_enabled');
            if ($request->filled('happy_customers_title')) {
                $currentConfig['happy_customers']['title'] = trim($request->input('happy_customers_title'));
            }
            if ($request->filled('happy_customers_subtitle')) {
                $currentConfig['happy_customers']['subtitle'] = trim($request->input('happy_customers_subtitle'));
            }
        }

        // Delete an existing Happy Customer item by index
        if ($request->filled('delete_hc_idx')) {
            $delIdx = (int) $request->input('delete_hc_idx');
            $existingHc = $currentConfig['happy_customers']['items'] ?? [];
            if (isset($existingHc[$delIdx])) {
                array_splice($existingHc, $delIdx, 1);
                $currentConfig['happy_customers']['items'] = $existingHc;
            }
        }

        // Add a New Vendor Happy Customer Story (real uploaded media or explicit URL only — zero fake stock photos)
        $wantsHcStory = $request->hasFile('new_hc_media_file')
            || $request->filled('new_hc_media_url')
            || $request->filled('new_hc_name')
            || $request->filled('new_hc_comment');

        if ($wantsHcStory) {
            $hcName = trim($request->input('new_hc_name', '')) ?: 'Verified Customer';
            $hcLocation = trim($request->input('new_hc_location', '')) ?: 'India';
            $hcRating = max(1, min(5, (int) $request->input('new_hc_rating', 5)));
            $hcComment = trim($request->input('new_hc_comment', '')) ?: 'Verified customer experience!';
            $hcType = 'image';
            $mediaUrl = '';
            $thumbUrl = '';

            // Handle direct photo or video file upload
            if ($request->hasFile('new_hc_media_file')) {
                $file = $request->file('new_hc_media_file');
                if ($file && $file->isValid()) {
                    $mime = $file->getMimeType() ?: '';
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (str_starts_with($mime, 'video/') || in_array($ext, ['mp4', 'webm', 'mov', 'm4v'])) {
                        $hcType = 'video';
                    } else {
                        $hcType = 'image';
                    }
                    $savedPath = $file->store('vendors/' . $vendor->id . '/happy_customers', 'public');
                    $mirrorToPublic($savedPath);
                    $mediaUrl = '/storage/' . $savedPath;
                    $thumbUrl = $mediaUrl;
                }
            } elseif ($request->filled('new_hc_media_url')) {
                $rawUrl = trim((string) $request->input('new_hc_media_url'));
                if (str_starts_with($rawUrl, '/') || filter_var($rawUrl, FILTER_VALIDATE_URL)) {
                    $mediaUrl = $rawUrl;
                    $thumbUrl = $rawUrl;
                    if (preg_match('/\.(mp4|webm|mov|m4v)(\?|$)/i', $rawUrl)) {
                        $hcType = 'video';
                    }
                }
            }

            if (empty($mediaUrl)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please upload a customer photo or video reel before adding a story.',
                ], 422);
            }

            $existingHc = $currentConfig['happy_customers']['items'] ?? [];
            if (!is_array($existingHc)) {
                $existingHc = [];
            }

            array_unshift($existingHc, [
                'type' => $hcType,
                'media_url' => $mediaUrl,
                'thumb_url' => $thumbUrl,
                'customer_name' => $hcName,
                'location' => $hcLocation,
                'rating' => $hcRating,
                'comment' => $hcComment,
                'product_name' => 'Verified Studio Purchase',
                'verified' => true,
            ]);

            $currentConfig['happy_customers']['items'] = array_slice($existingHc, 0, 20);
            $currentConfig['happy_customers']['enabled'] = true;
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
        $social['announcement_bar'] = $sanitizedConfig['header']['announcement_text'] ?? null;
        $social['announcement_bar_enabled'] = !empty($sanitizedConfig['header']['announcement_enabled']);
        $social['announcement_link'] = $sanitizedConfig['header']['announcement_link'] ?? null;

        $vendor->description = $sanitizedConfig['brand']['description'] ?? $vendor->description;
        $vendor->social_links = $social;
        $vendor->save();
        $vendor->bumpWebsiteCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Storefront essentials & customer stories saved and published live!',
                'public_url' => $vendor->storefront_url,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error saving quick settings: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }
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

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $revision->preset_key,
                'schema_version' => 2,
                'draft_config' => $revision->config_data,
                'published_config' => $revision->config_data,
            ]
        );
        $configRecord->update([
            'preset_key' => $revision->preset_key,
            'draft_config' => $revision->config_data,
            'last_saved_at' => now(),
        ]);
        $vendor->bumpWebsiteCache();

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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif,avif', 'max:15360'],
        ]);

        $file = $request->file('image');
        $path = $file->store('vendors/' . $vendor->id . '/storefront', 'public');

        // Ensure public accessibility when not symlinked
        k71_sync_public_storage($path);

        $url = asset('storage/' . $path);

        return response()->json([
            'status' => 'success',
            'url' => $url,
            'path' => $path,
        ]);
    }

    /**
     * Store auto-generated JPG scroll sequence frames in chunked batches for the Kinetic preset.
     */
    public function uploadScrollFramesBatch(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor || !$vendor->hasKineticScrollAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Scroll Motion is not enabled for your boutique.'], 403);
        }

        $request->validate([
            'frames' => ['required', 'array', 'min:1', 'max:30'],
            'frames.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:6144'],
            'start_index' => ['nullable', 'integer', 'min:1', 'max:' . \App\Services\ScrollSequenceService::MAX_FRAMES],
            'reset_before' => ['nullable', 'boolean'],
            'activate_kinetic' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:500'],
        ]);

        $configRecord = $vendor->storefrontConfig;
        $currentConfig = $configRecord?->getEffectiveConfig(false)
            ?: $this->presetService->getDefaultConfig($vendor->layout_preset ?: 'kinetic', $vendor);

        $result = \App\Services\ScrollSequenceService::storeVendorFrameBatch(
            $vendor,
            $request->file('frames', []),
            (int) $request->input('start_index', 1),
            $request->boolean('reset_before', false),
            $currentConfig
        );

        $activateKinetic = $request->boolean('activate_kinetic', false);
        if ($activateKinetic) {
            $currentConfig = $this->presetService->switchPresetPreservingContent($vendor, 'kinetic', $currentConfig);
            $currentConfig['preset'] = 'kinetic';
        }

        if (!isset($currentConfig['scroll_sequence']) || !is_array($currentConfig['scroll_sequence'])) {
            $currentConfig['scroll_sequence'] = [];
        }
        $currentConfig['scroll_sequence']['enabled'] = true;
        if ($request->filled('video_url')) {
            $currentConfig['scroll_sequence']['video_url'] = trim((string) $request->input('video_url'));
        }

        $sanitizedConfig = $this->presetService->sanitizeConfig($currentConfig, $vendor);
        $presetKey = $activateKinetic ? 'kinetic' : ($configRecord?->preset_key ?: ($sanitizedConfig['preset'] ?? 'kinetic'));
        $sanitizedConfig['preset'] = $presetKey;

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $presetKey,
                'schema_version' => 2,
                'draft_config' => $sanitizedConfig,
                'published_config' => $sanitizedConfig,
                'is_published' => true,
            ]
        );
        $configRecord->update([
            'preset_key' => $presetKey,
            'draft_config' => $sanitizedConfig,
            'published_config' => $sanitizedConfig,
            'is_published' => true,
            'last_saved_at' => now(),
            'last_published_at' => now(),
        ]);

        if ($activateKinetic) {
            $vendor->update(['layout_preset' => 'kinetic']);
        }
        $vendor->bumpWebsiteCache();

        return response()->json([
            'status' => 'success',
            'written' => $result['written'],
            'count' => $result['sequence']['count'] ?? 0,
            'sequence' => $result['sequence'],
            'preset' => $presetKey,
            'public_url' => $vendor->storefront_url,
        ]);
    }

    /**
     * Store uploaded showcase video and run server-side ffmpeg JPG frame extraction when available.
     */
    public function uploadScrollVideo(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor || !$vendor->hasKineticScrollAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Scroll Motion is not enabled for your boutique.'], 403);
        }

        $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,webm,mov,m4v,quicktime', 'max:61440'],
            'target_frames' => ['nullable', 'integer', 'min:12', 'max:140'],
            'activate_kinetic' => ['nullable', 'boolean'],
        ]);

        $res = \App\Services\ScrollSequenceService::extractVendorVideoFrames(
            $vendor,
            $request->file('video'),
            (int) $request->input('target_frames', 72)
        );

        $configRecord = $vendor->storefrontConfig;
        $currentConfig = $configRecord?->getEffectiveConfig(false)
            ?: $this->presetService->getDefaultConfig($vendor->layout_preset ?: 'kinetic', $vendor);

        $activateKinetic = $request->boolean('activate_kinetic', false);
        if ($activateKinetic) {
            $currentConfig = $this->presetService->switchPresetPreservingContent($vendor, 'kinetic', $currentConfig);
            $currentConfig['preset'] = 'kinetic';
        }

        if (!isset($currentConfig['scroll_sequence']) || !is_array($currentConfig['scroll_sequence'])) {
            $currentConfig['scroll_sequence'] = [];
        }
        $currentConfig['scroll_sequence']['enabled'] = true;
        $currentConfig['scroll_sequence']['video_url'] = $res['video_url'];

        $sanitizedConfig = $this->presetService->sanitizeConfig($currentConfig, $vendor);
        $presetKey = $activateKinetic ? 'kinetic' : ($configRecord?->preset_key ?: ($sanitizedConfig['preset'] ?? 'kinetic'));
        $sanitizedConfig['preset'] = $presetKey;

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $presetKey,
                'schema_version' => 2,
                'draft_config' => $sanitizedConfig,
                'published_config' => $sanitizedConfig,
                'is_published' => true,
            ]
        );
        $configRecord->update([
            'preset_key' => $presetKey,
            'draft_config' => $sanitizedConfig,
            'published_config' => $sanitizedConfig,
            'is_published' => true,
            'last_saved_at' => now(),
            'last_published_at' => now(),
        ]);

        if ($activateKinetic) {
            $vendor->update(['layout_preset' => 'kinetic']);
        }
        $vendor->bumpWebsiteCache();

        return response()->json([
            'status' => 'success',
            'video_url' => $res['video_url'],
            'extracted' => $res['extracted'],
            'count' => $res['sequence']['count'] ?? 0,
            'sequence' => $res['sequence'],
            'preset' => $presetKey,
        ]);
    }

    /**
     * Clear all vendor-uploaded JPG scroll sequence frames.
     */
    public function clearScrollFrames(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor || !$vendor->hasKineticScrollAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Scroll Motion is not enabled for your boutique.'], 403);
        }

        \App\Services\ScrollSequenceService::clearVendorFrames($vendor);

        $configRecord = $vendor->storefrontConfig;
        if ($configRecord) {
            $cfg = $configRecord->getEffectiveConfig(false) ?: [];
            if (isset($cfg['scroll_sequence']) && is_array($cfg['scroll_sequence'])) {
                $cfg['scroll_sequence']['video_url'] = '';
            }
            $sanitizedConfig = $this->presetService->sanitizeConfig($cfg, $vendor);
            $configRecord->update([
                'draft_config' => $sanitizedConfig,
                'published_config' => $sanitizedConfig,
                'last_saved_at' => now(),
                'last_published_at' => now(),
            ]);
        }
        $vendor->bumpWebsiteCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Cleared all scroll JPG frames.',
            'sequence' => \App\Services\ScrollSequenceService::resolveVendorSequence($vendor),
        ]);
    }
}
