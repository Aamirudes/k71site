<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Offer;
use App\Models\Product;
use App\Models\UsedThread;
use App\Models\UsedTransaction;
use App\Services\Used\UsedMarketplaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsedListingController extends Controller
{
    public function __construct(private UsedMarketplaceService $usedService)
    {
    }

    /**
     * Multi-step Create Used Listing wizard.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $profile = $this->usedService->getOrCreateSellerProfile($user);

        $parentCategories = Category::where('vertical', 'used')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'), 'fields'])
            ->orderBy('sort_order')
            ->get();

        $allFields = CategoryField::where('vertical', 'used')
            ->orWhereIn('category_id', $parentCategories->pluck('id'))
            ->orderBy('sort_order')
            ->get();

        $fieldsByCategory = $allFields->groupBy('category_id')->map(function ($group) {
            return $group->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'label' => $f->label,
                'field_type' => $f->field_type,
                'options' => $f->options,
                'placeholder' => $f->placeholder,
                'help_text' => $f->help_text,
                'default_value' => $f->default_value,
                'is_required' => $f->is_required,
            ])->values();
        });

        $conditions = config('used_marketplace.conditions', []);
        $fulfillmentModes = config('used_marketplace.fulfillment_modes', []);
        $packagingOptions = config('used_marketplace.packaging_options', []);
        $cities = config('used_marketplace.default_cities', []);

        return view('used.create', compact(
            'user',
            'profile',
            'parentCategories',
            'fieldsByCategory',
            'conditions',
            'fulfillmentModes',
            'packagingOptions',
            'cities'
        ));
    }

    /**
     * JSON endpoint for dynamic category fields.
     */
    public function categoryFields(Category $category): JsonResponse
    {
        $catIds = array_values(array_unique(array_filter([$category->id, $category->parent_id])));
        $fields = CategoryField::whereIn('category_id', $catIds)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'category_id' => $category->id,
            'fields' => $fields,
        ]);
    }

    /**
     * Store a new Used listing (supports draft, preview, publish & legacy modal payload).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please sign in to post a listing.'], 401);
            }
            return redirect()->route('login');
        }

        // Rate limit listing creation to curb spam
        $rateKey = 'used-create-' . $user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 15)) {
            $msg = 'You are posting too quickly. Please wait a minute before publishing another listing.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }
            return back()->withInput()->with('error', $msg);
        }
        RateLimiter::hit($rateKey, 60);

        $action = $request->input('action', 'publish');
        $isDraft = in_array($action, ['draft', 'preview'], true);

        $validated = $request->validate([
            'title' => 'required|string|min:4|max:180',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string|max:120',
            'description' => ($isDraft ? 'nullable' : 'required') . '|string|max:5000',
            'brand' => 'nullable|string|max:100',
            'model_name' => 'nullable|string|max:120',
            'age_months' => 'nullable|integer|min:0|max:1200',
            'purchase_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'condition_grade' => 'nullable|string|max:40',
            'condition' => 'nullable|string|max:60',
            'is_refurbished' => 'nullable|boolean',
            'defect_notes' => 'nullable|string|max:2000',
            'included_accessories' => 'nullable|string|max:1000',
            'original_packaging' => 'nullable|string|max:60',
            'reason_for_selling' => 'nullable|string|max:255',
            'quantity_available' => 'nullable|integer|min:1|max:50',
            'price' => 'required|numeric|min:1|max:500000000',
            'original_retail_price' => 'nullable|numeric|min:1|max:500000000',
            'min_offer_amount' => 'nullable|numeric|min:1|max:500000000',
            'is_negotiable' => 'nullable|boolean',
            'city' => 'required|string|max:100',
            'locality' => 'nullable|string|max:120',
            'private_pickup_area' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'fulfillment_modes' => 'nullable|array',
            'fulfillment_modes.*' => 'in:local_pickup,public_meetup,seller_shipping',
            'shipping_charge' => 'nullable|numeric|min:0|max:100000',
            'pickup_notes' => 'nullable|string|max:255',
            'specs' => 'nullable|array',
            'photos' => 'nullable|array|max:' . config('used_marketplace.max_photos_per_listing', 10),
            'photos.*' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:' . config('used_marketplace.max_photo_size_kb', 8192),
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:' . config('used_marketplace.max_photo_size_kb', 8192),
            'cover_index' => 'nullable|integer|min:0|max:20',
            'allow_duplicate' => 'nullable|boolean',
        ]);

        // Prohibited content screening
        $prohibitedHit = $this->usedService->detectProhibitedContent(
            $validated['title'],
            (string) ($validated['description'] ?? ''),
            (string) ($validated['defect_notes'] ?? '')
        );
        if ($prohibitedHit !== null) {
            $msg = "Listing blocked: item description contains restricted/prohibited term \"{$prohibitedHit}\".";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['title' => [$msg]]], 422);
            }
            return back()->withInput()->withErrors(['title' => $msg]);
        }

        // Min offer sanity check
        if (!empty($validated['min_offer_amount']) && (float) $validated['min_offer_amount'] > (float) $validated['price']) {
            $msg = 'Minimum acceptable offer cannot exceed your asking price.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['min_offer_amount' => [$msg]]], 422);
            }
            return back()->withInput()->withErrors(['min_offer_amount' => $msg]);
        }

        // Resolve category
        $category = null;
        if (!empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
        } elseif (!empty($validated['category'])) {
            $catNeedle = $validated['category'];
            $category = Category::where('vertical', 'used')
                ->where(function ($q) use ($catNeedle) {
                    $q->where('name', 'like', "%{$catNeedle}%")
                      ->orWhere('slug', 'like', '%' . Str::slug($catNeedle) . '%');
                })
                ->first();
        }
        $category ??= Category::where('vertical', 'used')->first()
            ?: Category::firstOrCreate(
                ['slug' => 'used-other-goods'],
                ['name' => 'Other Used Goods', 'vertical' => 'used', 'is_active' => true]
            );

        // Validate category-required custom fields when publishing
        if (!$isDraft) {
            $catIds = array_values(array_unique(array_filter([$category->id, $category->parent_id])));
            $requiredFields = CategoryField::whereIn('category_id', $catIds)
                ->where('is_required', true)
                ->get();
            foreach ($requiredFields as $reqField) {
                $val = $validated['specs'][$reqField->name] ?? null;
                if ($val === null || $val === '') {
                    $msg = "Please complete required field: {$reqField->label}.";
                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ["specs.{$reqField->name}" => [$msg]]], 422);
                    }
                    return back()->withInput()->withErrors(["specs.{$reqField->name}" => $msg]);
                }
            }
        }

        // Duplicate detection check
        $fingerprint = $this->usedService->computeDuplicateFingerprint($user->id, $category->id, $validated['title']);
        if (!$isDraft && empty($validated['allow_duplicate'])) {
            $duplicate = $this->usedService->findDuplicateActiveListing($user->id, $category->id, $validated['title']);
            if ($duplicate) {
                $msg = "Duplicate listing warning: you already have an active listing titled \"{$duplicate->title}\". Check 'Confirm intentional separate listing' if this is a distinct unit.";
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'duplicate_warning' => true,
                        'existing_slug' => $duplicate->slug,
                        'message' => $msg,
                    ], 422);
                }
                return back()->withInput()->with('duplicate_warning', $msg)->withErrors(['title' => $msg]);
            }
        }

        // Seller profile auto-init
        $profile = $this->usedService->getOrCreateSellerProfile($user);
        if (!$profile->city && !empty($validated['city'])) {
            $profile->update([
                'city' => $validated['city'],
                'locality' => $validated['locality'] ?? $profile->locality,
            ]);
        }

        $conditionGrade = $this->usedService->normalizeConditionGrade(
            $validated['condition_grade'] ?? ($validated['condition'] ?? 'good')
        );
        $conditionLabel = $this->usedService->conditionLabel($conditionGrade);

        $modes = $validated['fulfillment_modes'] ?? ['local_pickup'];
        $fulfillmentOptions = [
            'local_pickup' => in_array('local_pickup', $modes, true),
            'public_meetup' => in_array('public_meetup', $modes, true),
            'seller_shipping' => in_array('seller_shipping', $modes, true),
            'shipping_charge' => in_array('seller_shipping', $modes, true) ? (int) round(((float) ($validated['shipping_charge'] ?? 0)) * 100) : 0,
            'pickup_notes' => $validated['pickup_notes'] ?? null,
        ];

        $qty = max(1, (int) ($validated['quantity_available'] ?? 1));
        $isNegotiable = $request->has('is_negotiable') ? (bool) $request->boolean('is_negotiable') : true;
        $isRefurbished = (bool) ($validated['is_refurbished'] ?? false);

        $baseSlug = Str::slug($validated['title']) ?: 'used-item';
        $slug = $baseSlug . '-' . random_int(1000, 9999);
        while (Product::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . random_int(10000, 99999);
        }

        $descText = trim((string) ($validated['description'] ?? ''));
        $usedStatus = $isDraft ? 'draft' : 'active';
        $status = $isDraft ? 'draft' : 'active';

        $specs = [];
        foreach (($validated['specs'] ?? []) as $sKey => $sVal) {
            if ($sVal !== null && $sVal !== '') {
                $specs[(string) $sKey] = is_string($sVal) ? trim($sVal) : $sVal;
            }
        }

        $product = Product::create([
            'vendor_id' => $user->vendor?->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => trim($validated['title']),
            'slug' => $slug,
            'description' => $descText !== '' ? $descText : 'Draft listing',
            'short_description' => Str::limit($descText !== '' ? $descText : $validated['title'], 140),
            'brand' => !empty($validated['brand']) ? trim($validated['brand']) : null,
            'model_name' => !empty($validated['model_name']) ? trim($validated['model_name']) : null,
            'age_months' => $validated['age_months'] ?? null,
            'purchase_year' => $validated['purchase_year'] ?? null,
            'defect_notes' => $validated['defect_notes'] ?? null,
            'included_accessories' => $validated['included_accessories'] ?? null,
            'original_packaging' => $validated['original_packaging'] ?? null,
            'reason_for_selling' => $validated['reason_for_selling'] ?? null,
            'base_price' => (int) round(((float) $validated['price']) * 100),
            'compare_at_price' => !empty($validated['original_retail_price'])
                ? (int) round(((float) $validated['original_retail_price']) * 100)
                : null,
            'original_retail_price' => !empty($validated['original_retail_price'])
                ? (int) round(((float) $validated['original_retail_price']) * 100)
                : null,
            'min_offer_amount' => ($isNegotiable && !empty($validated['min_offer_amount']))
                ? (int) round(((float) $validated['min_offer_amount']) * 100)
                : null,
            'gst_rate' => 0,
            'sku' => 'AD-' . strtoupper(Str::random(7)),
            'status' => $status,
            'used_status' => $usedStatus,
            'condition_grade' => $conditionGrade,
            'is_refurbished' => $isRefurbished,
            'is_featured' => false, // Strictly awarded only on genuine eligibility/moderation
            'is_negotiable' => $isNegotiable,
            'is_sold' => false,
            'total_stock' => $qty,
            'quantity_available' => $qty,
            'city' => trim($validated['city']),
            'locality' => !empty($validated['locality']) ? trim($validated['locality']) : null,
            'private_pickup_area' => !empty($validated['private_pickup_area']) ? trim($validated['private_pickup_area']) : null,
            'fulfillment_options' => $fulfillmentOptions,
            'duplicate_fingerprint' => $fingerprint,
            'published_at' => $isDraft ? null : now(),
            'refreshed_at' => $isDraft ? null : now(),
            'custom_fields' => [
                'ad_id' => (string) random_int(1857000000, 1857999999),
                'user_id' => $user->id,
                'location' => trim($validated['city']),
                'contact_phone' => $validated['phone'] ?? null,
                'condition' => $conditionLabel,
                'specs' => $specs,
            ],
        ]);

        // Genuine photo uploads only (never fabricate fallback images)
        $files = [];
        if ($request->hasFile('photos')) {
            $files = is_array($request->file('photos')) ? $request->file('photos') : [$request->file('photos')];
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        }
        if (!empty($files)) {
            $this->usedService->uploadListingPhotos(
                $product,
                $files,
                isset($validated['cover_index']) ? (int) $validated['cover_index'] : 0
            );
        }

        Cache::forget('sitemap_products_xml');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $isDraft ? 'Listing saved as draft.' : 'Your listing is now live on K71 Used!',
                'slug' => $product->slug,
                'used_status' => $product->used_status,
                'url' => route('used.show', $product->slug),
            ]);
        }

        if ($action === 'preview') {
            return redirect()->route('used.show', ['slug' => $product->slug, 'preview' => 1])
                ->with('success', 'Draft saved! You are viewing a private owner preview.');
        }

        if ($action === 'draft') {
            return redirect()->route('used.dashboard', ['tab' => 'listings'])
                ->with('success', "Draft \"{$product->title}\" saved. You can review and publish it anytime.");
        }

        return redirect()->route('used.show', $product->slug)
            ->with('success', 'Your listing is now LIVE on K71 Used!');
    }

    /**
     * Show edit listing page.
     */
    public function edit(Product $product): View
    {
        $user = auth()->user();
        if (!$product->isOwnedBy($user)) {
            abort(403, 'Unauthorized to edit this listing.');
        }

        $product->load(['images', 'category']);

        $parentCategories = Category::where('vertical', 'used')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'), 'fields'])
            ->orderBy('sort_order')
            ->get();

        $allFields = CategoryField::where('vertical', 'used')
            ->orWhereIn('category_id', $parentCategories->pluck('id'))
            ->orderBy('sort_order')
            ->get();

        $fieldsByCategory = $allFields->groupBy('category_id')->map(function ($group) {
            return $group->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'label' => $f->label,
                'field_type' => $f->field_type,
                'options' => $f->options,
                'placeholder' => $f->placeholder,
                'help_text' => $f->help_text,
                'default_value' => $f->default_value,
                'is_required' => $f->is_required,
            ])->values();
        });

        $conditions = config('used_marketplace.conditions', []);
        $fulfillmentModes = config('used_marketplace.fulfillment_modes', []);
        $packagingOptions = config('used_marketplace.packaging_options', []);
        $cities = config('used_marketplace.default_cities', []);

        return view('used.edit', compact(
            'product',
            'parentCategories',
            'fieldsByCategory',
            'conditions',
            'fulfillmentModes',
            'packagingOptions',
            'cities'
        ));
    }

    /**
     * Update Used listing details, photos, pricing, condition & location.
     */
    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$product->isOwnedBy($user)) {
            abort(403, 'Unauthorized to update this listing.');
        }

        $action = $request->input('action', 'save');
        $validated = $request->validate([
            'title' => 'required|string|min:4|max:180',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:5000',
            'brand' => 'nullable|string|max:100',
            'model_name' => 'nullable|string|max:120',
            'age_months' => 'nullable|integer|min:0|max:1200',
            'purchase_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'condition_grade' => 'nullable|string|max:40',
            'condition' => 'nullable|string|max:60',
            'is_refurbished' => 'nullable|boolean',
            'defect_notes' => 'nullable|string|max:2000',
            'included_accessories' => 'nullable|string|max:1000',
            'original_packaging' => 'nullable|string|max:60',
            'reason_for_selling' => 'nullable|string|max:255',
            'quantity_available' => 'nullable|integer|min:1|max:50',
            'price' => 'required|numeric|min:1|max:500000000',
            'original_retail_price' => 'nullable|numeric|min:1|max:500000000',
            'min_offer_amount' => 'nullable|numeric|min:1|max:500000000',
            'is_negotiable' => 'nullable|boolean',
            'city' => 'required|string|max:100',
            'locality' => 'nullable|string|max:120',
            'private_pickup_area' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'fulfillment_modes' => 'nullable|array',
            'fulfillment_modes.*' => 'in:local_pickup,public_meetup,seller_shipping',
            'shipping_charge' => 'nullable|numeric|min:0|max:100000',
            'pickup_notes' => 'nullable|string|max:255',
            'specs' => 'nullable|array',
            'photos' => 'nullable|array|max:' . config('used_marketplace.max_photos_per_listing', 10),
            'photos.*' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:' . config('used_marketplace.max_photo_size_kb', 8192),
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'photo_order' => 'nullable|array',
            'photo_order.*' => 'integer',
            'cover_image_id' => 'nullable|integer',
        ]);

        $prohibitedHit = $this->usedService->detectProhibitedContent(
            $validated['title'],
            $validated['description'],
            (string) ($validated['defect_notes'] ?? '')
        );
        if ($prohibitedHit !== null) {
            return back()->withInput()->withErrors(['title' => "Listing blocked: restricted term \"{$prohibitedHit}\" is not permitted."]);
        }

        if (!empty($validated['min_offer_amount']) && (float) $validated['min_offer_amount'] > (float) $validated['price']) {
            return back()->withInput()->withErrors(['min_offer_amount' => 'Minimum acceptable offer cannot exceed your asking price.']);
        }

        $conditionGrade = $this->usedService->normalizeConditionGrade(
            $validated['condition_grade'] ?? ($validated['condition'] ?? $product->condition_grade)
        );
        $conditionLabel = $this->usedService->conditionLabel($conditionGrade);

        $modes = $validated['fulfillment_modes'] ?? ['local_pickup'];
        $fulfillmentOptions = [
            'local_pickup' => in_array('local_pickup', $modes, true),
            'public_meetup' => in_array('public_meetup', $modes, true),
            'seller_shipping' => in_array('seller_shipping', $modes, true),
            'shipping_charge' => in_array('seller_shipping', $modes, true) ? (int) round(((float) ($validated['shipping_charge'] ?? 0)) * 100) : 0,
            'pickup_notes' => $validated['pickup_notes'] ?? null,
        ];

        $isNegotiable = $request->has('is_negotiable') ? (bool) $request->boolean('is_negotiable') : $product->isNegotiable();
        $qty = max(1, (int) ($validated['quantity_available'] ?? $product->quantity_available ?? 1));

        $specs = [];
        foreach (($validated['specs'] ?? []) as $sKey => $sVal) {
            if ($sVal !== null && $sVal !== '') {
                $specs[(string) $sKey] = is_string($sVal) ? trim($sVal) : $sVal;
            }
        }

        $cf = $product->custom_fields ?? [];
        $cf['location'] = trim($validated['city']);
        $cf['condition'] = $conditionLabel;
        if (array_key_exists('phone', $validated)) {
            $cf['contact_phone'] = $validated['phone'];
        }
        $cf['specs'] = array_merge($cf['specs'] ?? [], $specs);

        $statusUpdates = [];
        if ($action === 'publish' && !$product->isSold()) {
            $statusUpdates = [
                'status' => 'active',
                'used_status' => 'active',
                'published_at' => $product->published_at ?? now(),
                'refreshed_at' => now(),
            ];
        } elseif ($action === 'draft' && !$product->isSold()) {
            $statusUpdates = ['status' => 'draft', 'used_status' => 'draft'];
        } elseif ($action === 'pause' && !$product->isSold()) {
            $statusUpdates = ['status' => 'inactive', 'used_status' => 'paused'];
        }

        $product->update(array_merge([
            'title' => trim($validated['title']),
            'category_id' => $validated['category_id'],
            'description' => trim($validated['description']),
            'short_description' => Str::limit(trim($validated['description']), 140),
            'brand' => !empty($validated['brand']) ? trim($validated['brand']) : null,
            'model_name' => !empty($validated['model_name']) ? trim($validated['model_name']) : null,
            'age_months' => $validated['age_months'] ?? null,
            'purchase_year' => $validated['purchase_year'] ?? null,
            'defect_notes' => $validated['defect_notes'] ?? null,
            'included_accessories' => $validated['included_accessories'] ?? null,
            'original_packaging' => $validated['original_packaging'] ?? null,
            'reason_for_selling' => $validated['reason_for_selling'] ?? null,
            'condition_grade' => $conditionGrade,
            'is_refurbished' => (bool) ($validated['is_refurbished'] ?? false),
            'base_price' => (int) round(((float) $validated['price']) * 100),
            'compare_at_price' => !empty($validated['original_retail_price'])
                ? (int) round(((float) $validated['original_retail_price']) * 100)
                : null,
            'original_retail_price' => !empty($validated['original_retail_price'])
                ? (int) round(((float) $validated['original_retail_price']) * 100)
                : null,
            'min_offer_amount' => ($isNegotiable && !empty($validated['min_offer_amount']))
                ? (int) round(((float) $validated['min_offer_amount']) * 100)
                : null,
            'is_negotiable' => $isNegotiable,
            'quantity_available' => $qty,
            'total_stock' => $product->isSold() ? 0 : $qty,
            'city' => trim($validated['city']),
            'locality' => !empty($validated['locality']) ? trim($validated['locality']) : null,
            'private_pickup_area' => !empty($validated['private_pickup_area']) ? trim($validated['private_pickup_area']) : null,
            'fulfillment_options' => $fulfillmentOptions,
            'duplicate_fingerprint' => $this->usedService->computeDuplicateFingerprint(
                $product->ownerUserId() ?? $user->id,
                (int) $validated['category_id'],
                $validated['title']
            ),
            'custom_fields' => $cf,
        ], $statusUpdates));

        // Manage existing photos (delete, reorder, cover) and append newly uploaded files
        $this->usedService->pruneAndReorderPhotos(
            $product,
            $validated['remove_images'] ?? [],
            $validated['photo_order'] ?? [],
            $validated['cover_image_id'] ?? null
        );

        if ($request->hasFile('photos')) {
            $files = is_array($request->file('photos')) ? $request->file('photos') : [$request->file('photos')];
            $this->usedService->uploadListingPhotos($product, $files);
        }

        Cache::forget('sitemap_products_xml');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing updated successfully.']);
        }

        return redirect()->route('used.show', $product->slug)
            ->with('success', 'Listing updated successfully!');
    }

    /**
     * Lifecycle status transition: publish, pause, reserve, mark_sold, bump, draft.
     */
    public function updateStatus(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$product->isOwnedBy($user)) {
            abort(403, 'Unauthorized to change status of this listing.');
        }

        $validated = $request->validate([
            'action' => 'required|in:publish,activate,pause,reserve,mark_sold,bump,draft',
            'buyer_id' => 'nullable|exists:users,id',
        ]);

        $action = $validated['action'];
        $msg = 'Listing status updated.';

        switch ($action) {
            case 'publish':
            case 'activate':
                $product->update([
                    'status' => 'active',
                    'used_status' => 'active',
                    'is_sold' => false,
                    'total_stock' => max(1, (int) ($product->quantity_available ?: 1)),
                    'reserved_for_user_id' => null,
                    'reserved_at' => null,
                    'sold_at' => null,
                    'published_at' => $product->published_at ?? now(),
                    'refreshed_at' => now(),
                ]);
                UsedThread::where('product_id', $product->id)->where('status', 'locked')->update(['status' => 'open']);
                $msg = 'Listing is now ACTIVE and searchable.';
                break;

            case 'draft':
                $product->update([
                    'status' => 'draft',
                    'used_status' => 'draft',
                ]);
                $msg = 'Listing moved to Draft.';
                break;

            case 'pause':
                $product->update([
                    'status' => 'inactive',
                    'used_status' => 'paused',
                ]);
                $msg = 'Listing paused. It is hidden from public browse until resumed.';
                break;

            case 'reserve':
                if ($product->isSold()) {
                    $msg = 'Item is already sold.';
                    break;
                }
                $buyerId = !empty($validated['buyer_id']) ? (int) $validated['buyer_id'] : null;
                $product->markReserved($buyerId);
                if ($buyerId) {
                    $exists = UsedTransaction::where('product_id', $product->id)
                        ->where('buyer_id', $buyerId)
                        ->where('status', 'reserved')
                        ->exists();
                    if (!$exists) {
                        UsedTransaction::create([
                            'transaction_number' => 'K71U-' . strtoupper(Str::random(7)),
                            'product_id' => $product->id,
                            'seller_id' => $product->ownerUserId() ?? $user->id,
                            'buyer_id' => $buyerId,
                            'agreed_price' => $product->base_price,
                            'quantity' => 1,
                            'status' => 'reserved',
                            'reserved_at' => now(),
                        ]);
                    }
                }
                $msg = 'Listing marked as RESERVED.';
                break;

            case 'mark_sold':
                $product->markAsSold();
                UsedTransaction::where('product_id', $product->id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'completed', 'completed_at' => now()]);

                // Close remaining pending/countered offers cleanly
                $openOffers = Offer::where('product_id', $product->id)
                    ->whereIn('status', ['pending', 'countered'])
                    ->get();
                foreach ($openOffers as $o) {
                    $o->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'responded_at' => now(),
                        'vendor_notes' => $o->vendor_notes ?: 'Item sold.',
                    ]);
                    if ($o->user_id) {
                        $this->usedService->notifyUser(
                            $o->user_id,
                            '🏷️',
                            'Listing Marked Sold',
                            "\"{$product->title}\" was marked sold by the seller.",
                            route('used.show', $product->slug)
                        );
                    }
                }
                UsedThread::where('product_id', $product->id)->update(['status' => 'locked']);
                $this->usedService->recalculateSellerStats($product->ownerUserId() ?? $user->id);
                $msg = "Listing marked as SOLD.";
                break;

            case 'bump':
                $product->update(['refreshed_at' => now()]);
                $msg = 'Listing refreshed in discovery ordering.';
                break;
        }

        AuditLog::create([
            'user_id' => $user->id,
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'action' => 'used_status_' . $action,
            'new_values' => ['used_status' => $product->fresh()->used_status, 'is_sold' => $product->fresh()->is_sold],
            'ip_address' => $request->ip(),
        ]);

        Cache::forget('sitemap_products_xml');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'used_status' => $product->fresh()->effectiveUsedStatus(),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Legacy toggleSold compatible handler.
     */
    public function toggleSold(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $action = $product->isSold() ? 'activate' : 'mark_sold';
        $request->merge(['action' => $action]);
        return $this->updateStatus($request, $product);
    }

    /**
     * Safe listing delete preserving conversation/offer/transaction/report history.
     */
    public function destroy(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$product->isOwnedBy($user)) {
            abort(403, 'Unauthorized to delete this listing.');
        }

        $product->update([
            'status' => 'inactive',
            'used_status' => 'removed',
        ]);
        $product->delete(); // SoftDeletes preserves historical FKs!
        Cache::forget('sitemap_products_xml');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Listing deleted safely.']);
        }

        return redirect()->route('used.dashboard', ['tab' => 'listings'])
            ->with('success', 'Your listing has been removed safely.');
    }
}
