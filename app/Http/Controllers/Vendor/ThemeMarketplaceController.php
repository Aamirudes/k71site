<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\StorefrontTheme;
use App\Models\Vendor;
use App\Models\VendorThemeDraft;
use App\Models\VendorThemeEntitlement;
use App\Models\VendorThemeRequest;
use App\Services\Storefront\ThemeMarketplaceRegistry;
use App\Services\Storefront\ThemeMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ThemeMarketplaceController extends Controller
{
    private const PREVIEW_IGNORED_QUERY_PARAMS = ['page', 'category', 'search', 'sort', 'product'];

    public function __construct(
        protected ThemeMarketplaceRegistry $registry,
        protected ThemeMarketplaceService $service
    ) {}

    private function makeSignedPreviewUrl(string $routeName, string $themeId, int $vendorId): string
    {
        return URL::temporarySignedRoute($routeName, now()->addHours(6), [
            'themeId' => $themeId,
            'v' => $vendorId,
        ], absolute: false);
    }

    private function hasValidPreviewSignature(Request $request): bool
    {
        return $request->hasValidRelativeSignatureWhileIgnoring(self::PREVIEW_IGNORED_QUERY_PARAMS)
            || $request->hasValidSignatureWhileIgnoring(self::PREVIEW_IGNORED_QUERY_PARAMS);
    }

    /**
     * Dedicated Vendor Theme Marketplace Page (61 themes: 11 Classic + 50 Bespoke Studio Themes).
     */
    public function index(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        $this->service->ensureSynced();

        $search = trim((string) $request->input('q', ''));
        $category = trim((string) $request->input('category', 'all'));
        $accessFilter = trim((string) $request->input('access', 'all'));
        $priceFilter = trim((string) $request->input('price', 'all'));
        $sort = trim((string) $request->input('sort', 'featured'));

        $query = StorefrontTheme::query()
            ->where('is_visible_in_catalogue', true);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('theme_id', 'like', "%{$search}%");
            });
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($priceFilter === 'free') {
            $query->where('is_free', true);
        } elseif ($priceFilter === 'under_2500') {
            $query->where('is_free', false)->whereNotNull('price_paise')->where('price_paise', '<=', 250000);
        } elseif ($priceFilter === '2500_7500') {
            $query->where('is_free', false)->whereBetween('price_paise', [250000, 750000]);
        } elseif ($priceFilter === 'above_7500') {
            $query->where('is_free', false)->where('price_paise', '>', 750000);
        } elseif ($priceFilter === 'paid') {
            $query->where('is_free', false);
        }

        match ($sort) {
            'name' => $query->orderBy('name', 'asc'),
            'price_low' => $query->orderByRaw('CASE WHEN price_paise IS NULL THEN 1 ELSE 0 END, price_paise ASC, sort_order ASC'),
            'price_high' => $query->orderByRaw('CASE WHEN price_paise IS NULL THEN 1 ELSE 0 END, price_paise DESC, sort_order ASC'),
            'newest' => $query->orderBy('is_legacy', 'asc')->orderByDesc('id'),
            default => $query->orderBy('is_legacy', 'asc')->orderByDesc('is_featured')->orderBy('sort_order', 'asc'),
        };

        $themes = $query->get();

        $entitlements = VendorThemeEntitlement::where('vendor_id', $vendor->id)
            ->get()
            ->keyBy('theme_id');

        $requests = VendorThemeRequest::where('vendor_id', $vendor->id)
            ->latest('id')
            ->get();

        $latestRequestByTheme = $requests->groupBy('theme_id')->map->first();
        $draftThemeIds = VendorThemeDraft::where('vendor_id', $vendor->id)->pluck('theme_id')->flip()->all();

        $liveThemeId = $this->service->resolvePublicThemeId($vendor);
        $manifests = $this->registry->getAllThemes();

        $enriched = $themes->map(function (StorefrontTheme $theme) use ($vendor, $entitlements, $latestRequestByTheme, $draftThemeIds, $liveThemeId, $manifests) {
            $id = $theme->theme_id;
            $ent = $entitlements->get($id);
            $req = $latestRequestByTheme->get($id);
            $isEntitled = $this->service->isVendorEntitled($vendor, $id);
            $isRevoked = $ent && $ent->isRevoked();
            $isPendingReq = $req && $req->isPending();
            $isLive = ($liveThemeId === $id);
            $hasDraft = isset($draftThemeIds[$id]);

            $state = 'not_entitled';
            if ($isLive) {
                $state = 'live';
            } elseif ($isRevoked) {
                $state = 'revoked';
            } elseif ($isEntitled) {
                $state = 'granted';
            } elseif (!$theme->isReady() || !$theme->is_available_for_request) {
                $state = 'unavailable';
            } elseif ($isPendingReq) {
                $state = 'pending';
            }

            // Expiring server-signed vendor-scoped preview URL (relative for proxy/HTTPS safety)
            $previewUrl = $theme->canBePreviewed()
                ? $this->makeSignedPreviewUrl('vendor.themes.preview', $id, (int) $vendor->id)
                : null;

            return [
                'theme' => $theme,
                'manifest' => $manifests[$id] ?? null,
                'entitlement' => $ent,
                'request' => $req,
                'whatsapp_url' => $req ? $this->service->buildWhatsAppRequestUrl($req) : null,
                'preview_url' => $previewUrl,
                'is_entitled' => $isEntitled,
                'is_revoked' => $isRevoked,
                'is_pending' => $isPendingReq,
                'is_live' => $isLive,
                'has_draft' => $hasDraft,
                'state' => $state,
            ];
        })->filter(function (array $item) use ($accessFilter) {
            return match ($accessFilter) {
                'entitled' => $item['is_entitled'],
                'available' => !$item['is_entitled'] && $item['state'] === 'not_entitled',
                'pending' => $item['is_pending'],
                'live' => $item['is_live'],
                default => true,
            };
        })->values();

        return view('vendor.themes.index', [
            'vendor' => $vendor,
            'items' => $enriched,
            'vendorRequests' => $requests->take(10),
            'liveThemeId' => $liveThemeId,
            'categories' => ThemeMarketplaceRegistry::CATEGORY_LABELS,
            'search' => $search,
            'category' => $category,
            'accessFilter' => $accessFilter,
            'priceFilter' => $priceFilter,
            'sort' => $sort,
            'adminWhatsAppConfigured' => $this->service->getAdminWhatsAppNumber() !== null,
            'service' => $this->service,
        ]);
    }

    /**
     * Standalone New-Tab Preview Workspace ("PREVIEW — NOT LIVE" toolbar + Desktop/Tablet/Mobile/Full-Screen controls).
     * Strictly server-authorized, vendor-bound, expiring-signature checked, read-only, noindex.
     */
    public function preview(Request $request, string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        // Enforce vendor binding & signature validity whenever scoped params are provided
        if ($request->filled('v') && (int) $request->input('v') !== (int) $vendor->id) {
            abort(403, 'Unauthorized preview scope: you may only preview themes against your own storefront.');
        }
        if ($request->has('signature') && !$this->hasValidPreviewSignature($request)) {
            abort(403, 'This theme preview session has expired. Please reopen Preview from the Theme Marketplace.');
        }

        $this->service->ensureSynced();
        $cleanId = strtolower(trim($themeId));
        if (!$this->registry->isValidThemeId($cleanId)) {
            abort(404, 'Requested theme does not exist in the K71 registry.');
        }

        $theme = StorefrontTheme::where('theme_id', $cleanId)->firstOrFail();
        if (!$theme->canBePreviewed()) {
            abort(403, 'Preview is currently disabled for this theme by K71 administration.');
        }

        $manifest = $this->registry->getManifest($cleanId);
        $isEntitled = $this->service->isVendorEntitled($vendor, $cleanId);
        $activeRequest = VendorThemeRequest::where('vendor_id', $vendor->id)
            ->where('theme_id', $cleanId)
            ->latest('id')
            ->first();
        $whatsappUrl = $activeRequest ? $this->service->buildWhatsAppRequestUrl($activeRequest) : null;
        $liveThemeId = $this->service->resolvePublicThemeId($vendor);

        $signedFrameBase = $this->makeSignedPreviewUrl('vendor.themes.preview_frame', $cleanId, (int) $vendor->id);

        // Build instant Prev / Next / Quick-Jump Studio Theme Switcher list (50 bespoke themes)
        $switcherThemes = [];
        $currentIdx = null;
        $seq = 1;
        foreach ($this->registry->getNewThemes() as $swId => $swManifest) {
            if ($swId === $cleanId) {
                $currentIdx = count($switcherThemes);
            }
            $switcherThemes[] = [
                'id' => $swId,
                'num' => str_pad((string) $seq, 2, '0', STR_PAD_LEFT),
                'name' => $swManifest['name'] ?? $swId,
                'url' => route('vendor.themes.preview', ['themeId' => $swId]),
            ];
            $seq++;
        }
        $prevPreviewUrl = null;
        $nextPreviewUrl = null;
        if ($currentIdx !== null && count($switcherThemes) > 1) {
            $totalSw = count($switcherThemes);
            $prevPreviewUrl = $switcherThemes[($currentIdx - 1 + $totalSw) % $totalSw]['url'];
            $nextPreviewUrl = $switcherThemes[($currentIdx + 1) % $totalSw]['url'];
        }

        return response()
            ->view('vendor.themes.preview', [
                'vendor' => $vendor,
                'theme' => $theme,
                'manifest' => $manifest,
                'isEntitled' => $isEntitled,
                'isLive' => $liveThemeId === $cleanId,
                'activeRequest' => $activeRequest,
                'whatsappUrl' => $whatsappUrl,
                'signedFrameBase' => $signedFrameBase,
                'isAdminPreview' => false,
                'returnUrl' => route('vendor.themes.index'),
                'initialPage' => in_array($request->input('page'), ['home', 'collection', 'product'], true) ? $request->input('page') : 'home',
                'switcherThemes' => $switcherThemes,
                'prevPreviewUrl' => $prevPreviewUrl,
                'nextPreviewUrl' => $nextPreviewUrl,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Isolated Preview Iframe Renderer (Strictly Read-Only, Vendor-Bound, Never Mutates Live Store).
     */
    public function previewFrame(Request $request, string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        if ($request->filled('v') && (int) $request->input('v') !== (int) $vendor->id) {
            abort(403, 'Cross-vendor preview access denied.');
        }
        if ($request->has('signature') && !$this->hasValidPreviewSignature($request)) {
            abort(403, 'Preview session expired.');
        }

        $cleanId = strtolower(trim($themeId));
        $manifest = $this->registry->getManifest($cleanId);
        if (!$manifest) {
            abort(404);
        }

        $theme = StorefrontTheme::where('theme_id', $cleanId)->first();
        if ($theme && !$theme->canBePreviewed()) {
            abort(403, 'Preview disabled for this theme.');
        }

        $frameBase = $this->makeSignedPreviewUrl('vendor.themes.preview_frame', $cleanId, (int) $vendor->id);

        return $this->renderIsolatedPreviewFrame($request, $vendor, $manifest, $frameBase, $this->service->isVendorEntitled($vendor, $cleanId));
    }

    /**
     * Shared read-only preview frame renderer (used by both Vendor Preview and Admin QA Preview).
     */
    public function renderIsolatedPreviewFrame(Request $request, Vendor $vendor, array $manifest, string $frameBaseUrl, bool $useVendorDraft = false)
    {
        $cleanId = $manifest['id'];
        $page = (string) $request->input('page', 'home');

        if (!empty($manifest['is_legacy'])) {
            $presetService = app(\App\Services\Storefront\StorefrontPresetService::class);
            $config = $presetService->getDefaultConfig($cleanId, $vendor);
            $config['preset'] = $cleanId;
            $products = $vendor->products()->with(['images', 'category', 'variants'])->where('status', 'active')->paginate(12);
            $vendorCategories = Category::whereHas('products', fn ($q) => $q->where('vendor_id', $vendor->id))->get();
            $featuredProducts = $products->take(4);
            $vendorShorts = collect();
            $vendorReviews = collect();
            $vendorScrollSequence = ['frames' => [], 'count' => 0, 'video_url' => null];
            $isPreview = true;
            $isServiceWebsite = false;
            $providerSite = null;

            return response()
                ->view('storefront.show', compact(
                    'vendor', 'products', 'vendorCategories', 'featuredProducts',
                    'vendorShorts', 'vendorReviews', 'config', 'vendorScrollSequence',
                    'isPreview', 'isServiceWebsite', 'providerSite'
                ))
                ->header('X-Robots-Tag', 'noindex, nofollow')
                ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
        }

        $themeConfig = $useVendorDraft
            ? $this->service->getDraftConfigForTheme($vendor, $cleanId)
            : $this->registry->getDefaultConfig($cleanId, $vendor);

        $realProducts = Product::with(['images', 'category', 'variants'])
            ->withCount(['reviews' => fn ($q) => $q->where('is_approved', true)])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->latest()
            ->take(12)
            ->get();

        $usingSampleProducts = $realProducts->isEmpty();
        $sampleArt = $manifest['layout']['sample_art'] ?? 'fashion-editorial';
        $productsCollection = $usingSampleProducts ? $this->buildSampleShowcaseProducts($vendor, $sampleArt) : $realProducts;

        $vendorCategories = $usingSampleProducts
            ? collect([
                (object) ['slug' => 'featured', 'name' => 'Featured', 'products_count' => 4],
                (object) ['slug' => 'new-arrivals', 'name' => 'New Arrivals', 'products_count' => 3],
                (object) ['slug' => 'essentials', 'name' => 'Essentials', 'products_count' => 3],
            ])
            : Category::whereHas('products', fn ($q) => $q->where('vendor_id', $vendor->id)->where('status', 'active'))
                ->withCount(['products' => fn ($q) => $q->where('vendor_id', $vendor->id)->where('status', 'active')])
                ->get();

        $vendorReviews = Review::whereIn('product_id', function ($q) use ($vendor) {
                $q->select('id')->from('products')->where('vendor_id', $vendor->id);
            })
            ->where('is_approved', true)
            ->with(['user', 'product'])
            ->latest()
            ->take(6)
            ->get();

        $shared = [
            'vendor' => $vendor,
            'manifest' => $manifest,
            'themeConfig' => $themeConfig,
            'vendorCategories' => $vendorCategories,
            'vendorReviews' => $vendorReviews,
            'isCataloguePreview' => true,
            'usingSampleProducts' => $usingSampleProducts,
            'previewFrameBaseUrl' => $frameBaseUrl,
            'isPreview' => true,
        ];

        if ($page === 'product') {
            $reqSlug = (string) $request->input('product', '');
            $product = $productsCollection->firstWhere('slug', $reqSlug) ?? $productsCollection->first();
            $view = view('storefront.themes.product', array_merge($shared, [
                'product' => $product,
                'productReviews' => $vendorReviews->take(3),
                'relatedProducts' => $productsCollection->where('slug', '!=', $product->slug)->take(4),
                'pageTitle' => $product->title,
            ]));
        } elseif ($page === 'collection') {
            $filtered = $productsCollection;
            $qSearch = strtolower(trim((string) $request->input('search', '')));
            if ($qSearch !== '') {
                $filtered = $filtered->filter(fn ($p) => str_contains(strtolower((string) $p->title), $qSearch))->values();
            }
            $view = view('storefront.themes.collection', array_merge($shared, [
                'products' => $filtered,
                'activeCategorySlug' => (string) $request->input('category', ''),
                'pageTitle' => 'Collection Preview',
            ]));
        } else {
            $view = view('storefront.themes.home', array_merge($shared, [
                'products' => $productsCollection,
                'featuredProducts' => $productsCollection->take(6),
                'pageTitle' => $manifest['name'] . ' Preview',
            ]));
        }

        return response($view)
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
    }

    public function requestAccess(Request $request, string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        $validated = $request->validate([
            'vendor_note' => 'nullable|string|max:500',
        ]);

        try {
            $themeRequest = $this->service->createRequest($vendor, $themeId, $validated['vendor_note'] ?? null);
            $waUrl = $this->service->buildWhatsAppRequestUrl($themeRequest);

            return redirect()
                ->route('vendor.themes.index')
                ->with('success', "Theme purchase request {$themeRequest->reference_code} created (Price snapshot: {$themeRequest->formatted_price_snapshot}). Click Continue on WhatsApp to coordinate manual payment verification with K71 Admin.")
                ->with('recent_request_id', $themeRequest->id)
                ->with('recent_whatsapp_url', $waUrl);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelRequest(VendorThemeRequest $themeRequest)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        try {
            $this->service->cancelVendorRequest($vendor, $themeRequest);
            return back()->with('success', "Request {$themeRequest->reference_code} cancelled.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function customize(string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        $this->service->ensureSynced();
        $cleanId = strtolower(trim($themeId));
        $theme = StorefrontTheme::where('theme_id', $cleanId)->firstOrFail();
        $manifest = $this->registry->getManifest($cleanId);
        if (!$manifest) {
            abort(404);
        }

        if (!$this->service->isVendorEntitled($vendor, $cleanId)) {
            return redirect()
                ->route('vendor.themes.index')
                ->with('error', 'Active theme entitlement is required before customizing this theme. Open Preview or Request Access via WhatsApp.');
        }

        if (!empty($manifest['is_legacy'])) {
            return redirect()->route('vendor.design.editor');
        }

        $draftConfig = $this->service->getDraftConfigForTheme($vendor, $cleanId);
        $liveThemeId = $this->service->resolvePublicThemeId($vendor);
        $previewUrl = $this->makeSignedPreviewUrl('vendor.themes.preview', $cleanId, (int) $vendor->id);

        return view('vendor.themes.customize', [
            'vendor' => $vendor,
            'theme' => $theme,
            'manifest' => $manifest,
            'draftConfig' => $draftConfig,
            'isLive' => ($liveThemeId === $cleanId),
            'previewUrl' => $previewUrl,
            'headingFonts' => ThemeMarketplaceRegistry::ALLOWED_HEADING_FONTS,
            'bodyFonts' => ThemeMarketplaceRegistry::ALLOWED_BODY_FONTS,
        ]);
    }

    public function saveDraft(Request $request, string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        $cleanId = strtolower(trim($themeId));
        if (!$this->service->isVendorEntitled($vendor, $cleanId)) {
            abort(403, 'You do not have active entitlement to customize this theme.');
        }

        $payload = $request->input('config', []);
        if (!is_array($payload)) {
            return back()->with('error', 'Invalid configuration submitted.');
        }

        if ($request->hasFile('hero_image_file')) {
            $request->validate(['hero_image_file' => 'image|mimes:jpeg,jpg,png,webp,avif|max:8192']);
            $path = $request->file('hero_image_file')->store("storefronts/{$vendor->id}/themes/{$cleanId}", 'public');
            if (function_exists('k71_sync_public_storage')) {
                k71_sync_public_storage($path);
            }
            $payload['hero']['image_url'] = '/storage/' . ltrim($path, '/');
        }

        if ($request->hasFile('editorial_image_file')) {
            $request->validate(['editorial_image_file' => 'image|mimes:jpeg,jpg,png,webp,avif|max:8192']);
            $path = $request->file('editorial_image_file')->store("storefronts/{$vendor->id}/themes/{$cleanId}", 'public');
            if (function_exists('k71_sync_public_storage')) {
                k71_sync_public_storage($path);
            }
            $payload['editorial']['image_url'] = '/storage/' . ltrim($path, '/');
        }

        try {
            $this->service->saveDraft($vendor, $cleanId, $payload);
            return back()->with('success', 'Draft saved! Your live storefront has not changed — open Preview to inspect your draft or click Publish when ready.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function resetDraft(string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        $cleanId = strtolower(trim($themeId));
        if (!$this->service->isVendorEntitled($vendor, $cleanId)) {
            abort(403, 'Unauthorized theme access.');
        }

        try {
            $this->service->resetDraft($vendor, $cleanId);
            return back()->with('success', 'Draft reset to theme defaults (live storefront untouched).');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function publish(string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        $cleanId = strtolower(trim($themeId));
        if (!$this->service->isVendorEntitled($vendor, $cleanId)) {
            abort(403, 'Active entitlement is required to publish this theme.');
        }

        try {
            $this->service->publishTheme($vendor, $cleanId);
            return back()->with('success', 'Theme published! Your public storefront is now live with your validated configuration.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function buildSampleShowcaseProducts(Vendor $vendor, string $sampleArt = 'fashion-editorial')
    {
        $galleryCycle = [
            $sampleArt,
            $sampleArt === 'fashion-editorial' ? 'fashion-runway' : ($sampleArt === 'horology-watch' ? 'jewelry-macro' : ($sampleArt === 'furniture-interior' ? 'marble-stone' : $sampleArt)),
            $sampleArt === 'tech-hardware' ? 'horology-watch' : ($sampleArt === 'fresh-grocery' ? 'gourmet-coffee' : 'graphic-art'),
            $sampleArt,
            $sampleArt === 'botanical-beauty' ? 'furniture-interior' : 'fashion-editorial',
            $sampleArt,
        ];
        $items = [
            ['slug' => 'preview-item-1', 'title' => 'Preview Showcase Piece 01', 'price' => 249900, 'compare' => 299900, 'cat' => 'Featured', 'sku' => 'PRV-01'],
            ['slug' => 'preview-item-2', 'title' => 'Preview Showcase Piece 02', 'price' => 389900, 'compare' => 0, 'cat' => 'New Arrivals', 'sku' => 'PRV-02'],
            ['slug' => 'preview-item-3', 'title' => 'Preview Showcase Piece 03', 'price' => 179900, 'compare' => 219900, 'cat' => 'Essentials', 'sku' => 'PRV-03'],
            ['slug' => 'preview-item-4', 'title' => 'Preview Showcase Piece 04', 'price' => 549900, 'compare' => 0, 'cat' => 'Featured', 'sku' => 'PRV-04'],
            ['slug' => 'preview-item-5', 'title' => 'Preview Showcase Piece 05', 'price' => 129900, 'compare' => 0, 'cat' => 'Essentials', 'sku' => 'PRV-05'],
            ['slug' => 'preview-item-6', 'title' => 'Preview Showcase Piece 06', 'price' => 429900, 'compare' => 499900, 'cat' => 'New Arrivals', 'sku' => 'PRV-06'],
        ];

        return collect($items)->map(function ($it, $idx) use ($galleryCycle) {
            $imgPlate = asset('images/theme-editorial/' . ($galleryCycle[$idx % count($galleryCycle)]) . '.jpg');
            $p = new Product();
            $p->id = 99000 + $idx;
            $p->slug = $it['slug'];
            $p->title = $it['title'];
            $p->description = 'Preview placeholder shown because this store has no active products yet. Your real catalog items will automatically populate this layout.';
            $p->base_price = $it['price'];
            $p->compare_at_price = $it['compare'];
            $p->total_stock = 10;
            $p->sku = $it['sku'];
            $p->material = 'Sample Material';
            $p->listing_type = 'purchase';
            $p->setRelation('images', collect([(object) ['url' => $imgPlate]]));
            $p->setRelation('category', (object) ['slug' => Str::slug($it['cat']), 'name' => $it['cat']]);
            $p->setRelation('variants', collect([
                (object) ['id' => 1, 'size' => 'S', 'color' => '', 'sku' => $it['sku'] . '-S', 'stock' => 4, 'price_override' => null],
                (object) ['id' => 2, 'size' => 'M', 'color' => '', 'sku' => $it['sku'] . '-M', 'stock' => 6, 'price_override' => null],
                (object) ['id' => 3, 'size' => 'L', 'color' => '', 'sku' => $it['sku'] . '-L', 'stock' => 0, 'price_override' => null],
            ]));
            return $p;
        });
    }
}
