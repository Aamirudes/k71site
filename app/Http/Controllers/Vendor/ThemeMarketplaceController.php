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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThemeMarketplaceController extends Controller
{
    public function __construct(
        protected ThemeMarketplaceRegistry $registry,
        protected ThemeMarketplaceService $service
    ) {}

    /**
     * Vendor Theme Marketplace Catalogue (All 61 themes: 11 Classic + 50 New).
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
        } elseif ($priceFilter === 'paid') {
            $query->where('is_free', false);
        }

        match ($sort) {
            'name' => $query->orderBy('name', 'asc'),
            'price_low' => $query->orderByRaw('CASE WHEN price_paise IS NULL THEN 1 ELSE 0 END, price_paise ASC, sort_order ASC'),
            'price_high' => $query->orderByRaw('CASE WHEN price_paise IS NULL THEN 1 ELSE 0 END, price_paise DESC, sort_order ASC'),
            'newest' => $query->orderByDesc('is_legacy')->orderByDesc('id'),
            default => $query->orderBy('sort_order', 'asc'),
        };

        $themes = $query->get();

        // Load vendor-specific entitlements, active requests, and drafts strictly scoped to this vendor
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

        // Annotate each theme with strict vendor-scoped state
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

            return [
                'theme' => $theme,
                'manifest' => $manifests[$id] ?? null,
                'entitlement' => $ent,
                'request' => $req,
                'whatsapp_url' => $req ? $this->service->buildWhatsAppRequestUrl($req) : null,
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

        $adminWhatsAppConfigured = $this->service->getAdminWhatsAppNumber() !== null;
        $categories = ThemeMarketplaceRegistry::CATEGORY_LABELS;

        return view('vendor.themes.index', [
            'vendor' => $vendor,
            'items' => $enriched,
            'vendorRequests' => $requests->take(10),
            'liveThemeId' => $liveThemeId,
            'categories' => $categories,
            'search' => $search,
            'category' => $category,
            'accessFilter' => $accessFilter,
            'priceFilter' => $priceFilter,
            'sort' => $sort,
            'adminWhatsAppConfigured' => $adminWhatsAppConfigured,
            'service' => $this->service,
        ]);
    }

    /**
     * Isolated Responsive Preview Shell (Desktop / Tablet / Mobile + Home / Collection / PDP switcher).
     * Never modifies live storefront or grants access.
     */
    public function preview(Request $request, string $themeId)
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

        $isEntitled = $this->service->isVendorEntitled($vendor, $cleanId);
        $activeRequest = VendorThemeRequest::where('vendor_id', $vendor->id)
            ->where('theme_id', $cleanId)
            ->latest('id')
            ->first();
        $whatsappUrl = $activeRequest ? $this->service->buildWhatsAppRequestUrl($activeRequest) : null;
        $liveThemeId = $this->service->resolvePublicThemeId($vendor);

        return view('vendor.themes.preview', [
            'vendor' => $vendor,
            'theme' => $theme,
            'manifest' => $manifest,
            'isEntitled' => $isEntitled,
            'isLive' => $liveThemeId === $cleanId,
            'activeRequest' => $activeRequest,
            'whatsappUrl' => $whatsappUrl,
            'initialPage' => in_array($request->input('page'), ['home', 'collection', 'product'], true) ? $request->input('page') : 'home',
        ]);
    }

    /**
     * Isolated Preview Iframe Renderer (Vendor-only authenticated endpoint, noindex, no-store).
     * Uses vendor's draft if entitled, otherwise safe defaults. Never modifies database.
     */
    public function previewFrame(Request $request, string $themeId)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        $cleanId = strtolower(trim($themeId));
        $manifest = $this->registry->getManifest($cleanId);
        if (!$manifest) {
            abort(404);
        }

        $isEntitled = $this->service->isVendorEntitled($vendor, $cleanId);
        $page = (string) $request->input('page', 'home');

        // Legacy 11 theme preview delegates cleanly to storefront.show in isolated preview mode
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
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        $themeConfig = $isEntitled
            ? $this->service->getDraftConfigForTheme($vendor, $cleanId)
            : $this->registry->getDefaultConfig($cleanId, $vendor);

        $realProducts = Product::with(['images', 'category', 'variants'])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->latest()
            ->take(12)
            ->get();

        $usingSampleProducts = $realProducts->isEmpty();
        $productsCollection = $usingSampleProducts ? $this->buildSampleShowcaseProducts($vendor) : $realProducts;

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
                'featuredProducts' => $productsCollection->take(4),
                'pageTitle' => $manifest['name'] . ' Preview',
            ]));
        }

        return response($view)
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Vendor requests access to a paid theme (creates persistent request with price snapshot).
     * NEVER confirms payment or activates theme.
     */
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
                ->with('success', "Theme access request {$themeRequest->reference_code} created (Price snapshot: {$themeRequest->formatted_price_snapshot}). Coordinate payment via WhatsApp — access unlocks once an administrator verifies payment and grants entitlement.")
                ->with('recent_request_id', $themeRequest->id)
                ->with('recent_whatsapp_url', $waUrl);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vendor cancels their own pending request.
     */
    public function cancelRequest(VendorThemeRequest $themeRequest)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403);
        }

        try {
            $this->service->cancelVendorRequest($vendor, $themeRequest);
            return back()->with('success', "Request {$themeRequest->reference_code} has been cancelled.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Curated Theme Customizer for an entitled theme.
     * Strictly blocks unentitled vendors with HTTP 403.
     */
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
                ->route('vendor.themes.preview', $cleanId)
                ->with('error', 'Active theme access is required before customizing this theme. Preview it or request access via WhatsApp.');
        }

        // Legacy themes redirect to the existing Store Design Studio editor
        if (!empty($manifest['is_legacy'])) {
            return redirect()->route('vendor.design.editor');
        }

        $draftConfig = $this->service->getDraftConfigForTheme($vendor, $cleanId);
        $liveThemeId = $this->service->resolvePublicThemeId($vendor);
        $isLive = ($liveThemeId === $cleanId);

        return view('vendor.themes.customize', [
            'vendor' => $vendor,
            'theme' => $theme,
            'manifest' => $manifest,
            'draftConfig' => $draftConfig,
            'isLive' => $isLive,
            'headingFonts' => ThemeMarketplaceRegistry::ALLOWED_HEADING_FONTS,
            'bodyFonts' => ThemeMarketplaceRegistry::ALLOWED_BODY_FONTS,
        ]);
    }

    /**
     * Save Draft Configuration ONLY (never alters live storefront).
     */
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

        // Handle optional hero or editorial image upload safely
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
            return back()->with('success', 'Draft saved! Your live storefront has not changed — preview your draft or click Publish when ready.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reset draft back to theme defaults (does not touch live storefront).
     */
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

    /**
     * Explicit Publish Action (requires active entitlement + ready theme + valid draft).
     */
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

    /**
     * Safe in-memory sample showcase items used ONLY during catalogue preview if a vendor has 0 products yet.
     */
    protected function buildSampleShowcaseProducts(Vendor $vendor)
    {
        $placeholder = asset('images/product-placeholder.svg');
        $items = [
            ['slug' => 'sample-piece-1', 'title' => 'Sample Showcase Item 01', 'price' => 249900, 'compare' => 299900, 'cat' => 'Featured', 'sku' => 'SMP-01'],
            ['slug' => 'sample-piece-2', 'title' => 'Sample Showcase Item 02', 'price' => 389900, 'compare' => 0, 'cat' => 'New Arrivals', 'sku' => 'SMP-02'],
            ['slug' => 'sample-piece-3', 'title' => 'Sample Showcase Item 03', 'price' => 179900, 'compare' => 219900, 'cat' => 'Essentials', 'sku' => 'SMP-03'],
            ['slug' => 'sample-piece-4', 'title' => 'Sample Showcase Item 04', 'price' => 549900, 'compare' => 0, 'cat' => 'Featured', 'sku' => 'SMP-04'],
            ['slug' => 'sample-piece-5', 'title' => 'Sample Showcase Item 05', 'price' => 129900, 'compare' => 0, 'cat' => 'Essentials', 'sku' => 'SMP-05'],
            ['slug' => 'sample-piece-6', 'title' => 'Sample Showcase Item 06', 'price' => 429900, 'compare' => 499900, 'cat' => 'New Arrivals', 'sku' => 'SMP-06'],
        ];

        return collect($items)->map(function ($it, $idx) use ($placeholder) {
            $p = new Product();
            $p->id = 99000 + $idx;
            $p->slug = $it['slug'];
            $p->title = $it['title'];
            $p->description = 'Sample preview item displayed because your store has no active products yet. Your real products will appear automatically once listed.';
            $p->base_price = $it['price'];
            $p->compare_at_price = $it['compare'];
            $p->total_stock = 10;
            $p->sku = $it['sku'];
            $p->listing_type = 'purchase';
            $p->setRelation('images', collect([(object) ['url' => $placeholder]]));
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
