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
        $isPreview = true;

        return view('storefront.show', compact(
            'vendor',
            'products',
            'vendorCategories',
            'featuredProducts',
            'vendorShorts',
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

        $presetKey = $request->input('preset');
        if (!in_array($presetKey, ['atelier', 'object', 'commerce', 'market', 'signature'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid preset selected.'], 422);
        }

        $newDraft = $this->presetService->getDefaultConfig($presetKey, $vendor);

        $configRecord = $vendor->storefrontConfig()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'preset_key' => $presetKey,
                'schema_version' => 2,
                'published_config' => $newDraft,
            ]
        );

        $configRecord->update([
            'preset_key' => $presetKey,
            'draft_config' => $newDraft,
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Applied preset ' . ucfirst($presetKey) . ' to draft.',
            'preset' => $presetKey,
            'config' => $newDraft,
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
