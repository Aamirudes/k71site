<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Vendor;
use App\Services\Monetization\EntitlementService;
use App\Services\Monetization\VisibilityService;
use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function __construct(
        protected VisibilityService $visibility,
        protected EntitlementService $entitlements,
        protected NotificationCenterService $notifications
    ) {}

    /**
     * Display the public B2B & Custom Requirements Marketplace Hub (enquiry.k71.in).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $selectedCategory = $request->query('cat');
        $city = $request->query('city');
        $urgency = $request->query('urgency');
        $minBudget = $request->query('min_budget');
        $maxBudget = $request->query('max_budget');

        if ($minBudget !== null && is_numeric($minBudget) && $maxBudget !== null && is_numeric($maxBudget)) {
            if ((float) $minBudget > (float) $maxBudget) {
                [$minBudget, $maxBudget] = [$maxBudget, $minBudget];
            }
        }

        // Fetch active enquiry categories
        $enquiryCategories = Category::where('vertical', 'enquiry')
            ->where('is_active', true)
            ->withCount(['enquiries' => fn($q) => $q->where('status', 'open')])
            ->orderBy('sort_order')
            ->get();

        // Available distinct cities from open enquiries
        $availableCities = Enquiry::whereIn('status', ['open', 'quoted'])
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values();

        // Main RFQs / Enquiries Query (never match private buyer names/phones on public search)
        $query = Enquiry::with(['category', 'vendor', 'quotes'])
            ->whereIn('status', ['open', 'quoted']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($selectedCategory) {
            $query->whereHas('category', fn($q) => $q->where('slug', $selectedCategory));
        }

        if ($city) {
            $query->where('city', $city);
        }

        if ($urgency) {
            $query->where('urgency', $urgency);
        }

        if ($minBudget !== null && is_numeric($minBudget)) {
            $query->where('target_budget', '>=', (int) ($minBudget * 100));
        }

        if ($maxBudget !== null && is_numeric($maxBudget)) {
            $query->where('target_budget', '<=', (int) ($maxBudget * 100));
        }

        $enquiries = $query->latest()->paginate(12)->withQueryString();

        // Fetch strictly recommended B2B & Wholesale products (excluding retail, food, footwear, clothing, services, used)
        $b2bQuery = Product::with(['vendor', 'images', 'category'])
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('vertical', 'enquiry')
                  ->orWhere('listing_type', 'enquiry')
                  ->orWhere('listing_type', 'quote_only')
                  ->orWhereHas('category', function($cq) {
                      $cq->where('vertical', 'enquiry')
                         ->orWhere('slug', 'like', '%marble%')
                         ->orWhere('slug', 'like', '%granite%')
                         ->orWhere('slug', 'like', '%machin%')
                         ->orWhere('slug', 'like', '%stone%')
                         ->orWhere('slug', 'like', '%industrial%');
                  });
            })
            ->whereNotIn('vertical', ['food', 'services', 'used'])
            ->whereDoesntHave('category', function($cq) {
                $cq->whereIn('vertical', ['food', 'services', 'used', 'footwear', 'clothing'])
                   ->orWhere('slug', 'like', '%food%')
                   ->orWhere('slug', 'like', '%burger%')
                   ->orWhere('slug', 'like', '%restaurant%')
                   ->orWhere('slug', 'like', '%shoe%')
                   ->orWhere('slug', 'like', '%footwear%')
                   ->orWhere('slug', 'like', '%cloth%')
                   ->orWhere('slug', 'like', '%apparel%')
                   ->orWhere('slug', 'like', '%shirt%');
            });
        $this->visibility->scopeVisibleProducts($b2bQuery, 'enquiry');
        $b2bProducts = $b2bQuery->take(8)->get();

        // Fetch verified B2B / Industrial / Marble / Machinery suppliers strictly
        $verifiedSuppliersQuery = Vendor::where(function($q) {
                $q->whereIn('vendor_type', ['marble', 'machinery', 'b2b', 'wholesale', 'industrial', 'manufacturer'])
                  ->orWhereHas('products', function($pq) {
                      $pq->where('vertical', 'enquiry')
                         ->orWhere('listing_type', 'enquiry');
                  });
            })
            ->whereNotIn('vendor_type', ['food', 'restaurant', 'clothing', 'footwear', 'services']);
        $this->visibility->scopeVisibleVendors($verifiedSuppliersQuery, 'enquiry');
        $verifiedSuppliers = $verifiedSuppliersQuery
            ->withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->orderByDesc('products_count')
            ->take(6)
            ->get();

        $visibleSupplierCountQuery = Vendor::query();
        $this->visibility->scopeVisibleVendors($visibleSupplierCountQuery, 'enquiry');

        $stats = [
            'total_rfqs' => Enquiry::count(),
            'total_quotes' => Quote::count(),
            'verified_suppliers' => $visibleSupplierCountQuery->count(),
            'total_categories' => $enquiryCategories->count(),
        ];

        return view('enquiry.index', compact(
            'enquiryCategories',
            'availableCities',
            'enquiries',
            'b2bProducts',
            'verifiedSuppliers',
            'stats',
            'search',
            'selectedCategory',
            'city',
            'urgency',
            'minBudget',
            'maxBudget'
        ));
    }

    /**
     * Show buyer requirement submission form.
     */
    public function create(Request $request): View
    {
        $categories = Category::where('vertical', 'enquiry')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $targetVendor = null;
        if ($request->has('vendor_id')) {
            $candidateVendor = Vendor::find($request->query('vendor_id'));
            if ($candidateVendor && $this->visibility->isVendorPubliclyVisible($candidateVendor, 'enquiry')) {
                $targetVendor = $candidateVendor;
            }
        }

        $targetProduct = null;
        if ($request->has('product_id')) {
            $candidateProduct = Product::find($request->query('product_id'));
            if ($candidateProduct && $this->visibility->isProductPubliclyVisible($candidateProduct)) {
                $targetProduct = $candidateProduct;
            }
        }

        return view('enquiry.create', compact('categories', 'targetVendor', 'targetProduct'));
    }

    /**
     * Store new buyer requirement / RFQ.
     */
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('is_active', true)],
            'description' => 'required|string|max:2000',
            'quantity' => 'required|string|max:100',
            'target_budget' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'urgency' => 'nullable|string|in:immediate,within_1_week,flexible',
            'buyer_name' => 'required|string|max:120',
            'buyer_phone' => 'required|string|max:20',
            'buyer_email' => 'nullable|email|max:150',
            'vendor_id' => 'nullable|exists:vendors,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if (!empty($validated['vendor_id'])) {
            $targetVendor = Vendor::find($validated['vendor_id']);
            if (!$targetVendor || !$this->visibility->isVendorPubliclyVisible($targetVendor, 'enquiry')) {
                $msg = 'The selected supplier is not currently accepting direct enquiries.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->withInput()->with('error', $msg);
            }
            if (auth()->check() && (int) $targetVendor->user_id === (int) auth()->id()) {
                $msg = 'You cannot send a direct RFQ to your own store.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->withInput()->with('error', $msg);
            }
        }

        if (!empty($validated['product_id'])) {
            $targetProduct = Product::withoutGlobalScopes()->with('vendor')->find($validated['product_id']);
            if (!$targetProduct || !$this->visibility->isProductPubliclyVisible($targetProduct)) {
                $msg = 'The selected product is currently unavailable for direct enquiries.';
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->withInput()->with('error', $msg);
            }
        }

        // Prevent duplicate accidental re-POSTs within 60 seconds
        $recentDuplicate = Enquiry::where('buyer_phone', $validated['buyer_phone'])
            ->where('title', trim($validated['title']))
            ->where('created_at', '>=', now()->subMinutes(1))
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('enquiry.show', $recentDuplicate->enquiry_number)
                ->with('info', 'This requirement was just submitted and is active for supplier quotes.');
        }

        $enquiryNumber = 'ENQ-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $enquiry = Enquiry::create([
            'enquiry_number' => $enquiryNumber,
            'user_id' => auth()->id(),
            'vendor_id' => $validated['vendor_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'quantity' => trim($validated['quantity']),
            'target_budget' => isset($validated['target_budget']) && is_numeric($validated['target_budget'])
                ? (int) ($validated['target_budget'] * 100)
                : null,
            'city' => trim($validated['city']),
            'pincode' => $validated['pincode'] ?? null,
            'urgency' => $validated['urgency'] ?? 'flexible',
            'buyer_name' => trim($validated['buyer_name']),
            'buyer_phone' => trim($validated['buyer_phone']),
            'buyer_email' => $validated['buyer_email'] ?? auth()->user()?->email,
            'status' => 'open',
            'quotes_count' => 0,
        ]);

        session()->put("guest_enquiry_{$enquiry->id}", true);

        if (!empty($enquiry->vendor_id)) {
            $targetVendorUserId = Vendor::whereKey($enquiry->vendor_id)->value('user_id');
            $this->notifications->notifyUser(
                $targetVendorUserId,
                '📋',
                "New Direct RFQ #{$enquiry->enquiry_number}",
                "{$enquiry->title} ({$enquiry->quantity}) from {$enquiry->city}.",
                route('vendor.enquiries.index', ['tab' => 'direct']),
                'b2b_enquiry'
            );
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Requirement posted successfully! RFQ Number: ' . $enquiry->enquiry_number,
                'enquiry_number' => $enquiry->enquiry_number,
                'enquiry' => $enquiry,
                'redirect' => route('enquiry.show', $enquiry->enquiry_number),
            ]);
        }

        return redirect()->route('enquiry.show', $enquiry->enquiry_number)
            ->with('success', 'Your requirement has been posted successfully! Verified vendors will review and submit quotations.');
    }

    /**
     * Show requirement details, submitted quotes, and contact card.
     */
    public function show(string $enquiry_number): View
    {
        $enquiry = Enquiry::where('enquiry_number', $enquiry_number)
            ->with(['category', 'vendor', 'product', 'quotes.vendor.settings', 'user'])
            ->firstOrFail();

        $user = auth()->user();
        $isOwner = $user && $enquiry->user_id && (int) $user->id === (int) $enquiry->user_id;
        $isAdmin = $user && $user->isAdmin();
        $isVerifiedMerchant = $user && $user->vendor && in_array($user->vendor->status, ['approved', 'active'], true);
        $hasGuestOwnerSession = (bool) session("guest_enquiry_{$enquiry->id}");

        $canViewBuyerContact = $isOwner || $isAdmin || $isVerifiedMerchant || $hasGuestOwnerSession;
        $canAcceptQuotes = $enquiry->user_id !== null
            ? ($isOwner || $isAdmin)
            : ($isAdmin || $hasGuestOwnerSession);

        return view('enquiry.show', compact('enquiry', 'canViewBuyerContact', 'canAcceptQuotes'));
    }

    /**
     * Vendor enquiries inbox & leads board (/vendor/enquiries).
     */
    public function vendorIndex(Request $request): View|RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Unauthorized vendor account.');
        }

        $sectorKey = $vendor->isServices() ? 'services' : 'enquiry';
        $featureKey = $sectorKey === 'services' ? 'allow_enquiry_management' : 'allow_rfq_responses';
        $authCheck = $this->entitlements->authorizeSectorWrite($vendor, $sectorKey, $featureKey);
        if (!$authCheck['allowed']) {
            return redirect()->route('vendor.subscription.index')->with('error', $authCheck['reason']);
        }

        $tab = $request->query('tab', $vendor->isServices() ? 'direct' : 'all');

        $counts = [
            'all' => Enquiry::where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor->id);
            })->whereIn('status', ['open', 'quoted'])->count(),

            'direct' => Enquiry::where('vendor_id', $vendor->id)->count(),

            'my_quotes' => Quote::where('vendor_id', $vendor->id)->count(),

            'closed' => Enquiry::where('status', 'closed')->where(function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)->orWhereHas('quotes', fn($sq) => $sq->where('vendor_id', $vendor->id));
            })->count(),
        ];

        $query = Enquiry::with(['category', 'quotes' => fn($q) => $q->where('vendor_id', $vendor->id)]);

        if ($tab === 'direct') {
            $query->where('vendor_id', $vendor->id);
        } elseif ($tab === 'my_quotes') {
            $query->whereHas('quotes', fn($q) => $q->where('vendor_id', $vendor->id));
        } elseif ($tab === 'closed') {
            $query->where('status', 'closed')->where(function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)->orWhereHas('quotes', fn($sq) => $sq->where('vendor_id', $vendor->id));
            });
        } else {
            $query->where(function($q) use ($vendor) {
                $q->whereNull('vendor_id')->orWhere('vendor_id', $vendor->id);
            })->whereIn('status', ['open', 'quoted']);
        }

        $enquiries = $query->latest()->paginate(15)->withQueryString();

        return view('vendor.enquiries.index', compact('enquiries', 'counts', 'tab', 'vendor'));
    }

    /**
     * Vendor submits or updates a quote on an enquiry.
     */
    public function vendorQuote(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Unauthorized vendor account.');
        }

        $sectorKey = $vendor->isServices() ? 'services' : 'enquiry';
        $featureKey = $sectorKey === 'services' ? 'allow_enquiry_management' : 'allow_rfq_responses';
        $authCheck = $this->entitlements->authorizeSectorWrite($vendor, $sectorKey, $featureKey);
        if (!$authCheck['allowed']) {
            return back()->with('error', $authCheck['reason']);
        }

        if ($enquiry->user_id && (int) $enquiry->user_id === (int) auth()->id()) {
            return back()->with('error', 'You cannot submit a quotation on your own requirement.');
        }

        if ($enquiry->isClosed()) {
            return back()->with('error', 'This enquiry is closed or has expired and is no longer accepting quotations.');
        }

        if ($enquiry->vendor_id && (int) $enquiry->vendor_id !== (int) $vendor->id) {
            abort(403, 'This enquiry is directed to another vendor.');
        }

        $validated = $request->validate([
            'quote_amount' => 'required|numeric|min:1',
            'lead_time_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $quote = DB::transaction(function () use ($enquiry, $vendor, $validated) {
            $lockedEnquiry = Enquiry::whereKey($enquiry->id)->lockForUpdate()->firstOrFail();
            if ($lockedEnquiry->isClosed()) {
                throw new \RuntimeException('This enquiry is closed or has expired and is no longer accepting quotations.');
            }

            $existingQuote = Quote::where('enquiry_id', $lockedEnquiry->id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if ($existingQuote && $existingQuote->isAccepted()) {
                throw new \RuntimeException('This quotation has already been accepted and cannot be modified.');
            }

            $quoteNumber = $existingQuote?->quote_number ?: ('QT-' . date('Ymd') . '-' . strtoupper(Str::random(6)));

            $quote = Quote::updateOrCreate(
                [
                    'enquiry_id' => $lockedEnquiry->id,
                    'vendor_id' => $vendor->id,
                ],
                [
                    'quote_number' => $quoteNumber,
                    'quote_amount' => (int) round((float) $validated['quote_amount'] * 100),
                    'lead_time_days' => $validated['lead_time_days'],
                    'notes' => $validated['notes'] ?? null,
                    'valid_until' => $validated['valid_until'] ?? null,
                    'status' => 'sent',
                ]
            );

            $lockedEnquiry->update([
                'status' => 'quoted',
                'quotes_count' => $lockedEnquiry->quotes()->where('status', '!=', 'withdrawn')->count(),
            ]);

            return $quote;
        });

        $this->notifications->notifyUser(
            $enquiry->user_id,
            '💼',
            "New Quotation Received — #{$enquiry->enquiry_number}",
            "{$vendor->shop_name} quoted ₹" . number_format($quote->quote_amount / 100, 2) . " ({$quote->lead_time_days} days lead time).",
            route('enquiry.show', $enquiry->enquiry_number),
            'b2b_quote'
        );

        return back()->with('success', "Your quotation of ₹" . number_format($quote->quote_amount / 100, 2) . " has been submitted to the buyer.");
    }

    /**
     * Buyer accepts a quotation.
     */
    public function acceptQuote(Request $request, Quote $quote): RedirectResponse
    {
        $enquiry = $quote->enquiry;
        if (!$enquiry || (int) $quote->enquiry_id !== (int) $enquiry->id) {
            abort(404);
        }

        $user = auth()->user();
        $isAdmin = $user && $user->isAdmin();
        $hasGuestOwnerSession = (bool) session("guest_enquiry_{$enquiry->id}");

        // Strict IDOR protection for both logged-in and guest-owned enquiries
        if ($enquiry->user_id !== null) {
            if (!$user || ((int) $user->id !== (int) $enquiry->user_id && !$isAdmin)) {
                abort(403, 'Only the owner of this enquiry can accept a quotation.');
            }
        } elseif (!$isAdmin && !$hasGuestOwnerSession) {
            abort(403, 'Only the creator session or an administrator can accept a quotation on this guest enquiry.');
        }

        if ($enquiry->isClosed()) {
            return back()->with('error', 'This enquiry is already closed.');
        }

        if (!$quote->isSent()) {
            return back()->with('error', 'Only active sent quotations can be accepted.');
        }

        if ($quote->valid_until && $quote->valid_until->lt(now()->startOfDay())) {
            return back()->with('error', 'This quotation has expired and can no longer be accepted.');
        }

        try {
            DB::transaction(function () use ($quote, $enquiry, $request) {
                $lockedEnquiry = Enquiry::whereKey($enquiry->id)->lockForUpdate()->firstOrFail();
                $lockedQuote = Quote::whereKey($quote->id)->lockForUpdate()->firstOrFail();

                if ($lockedEnquiry->isClosed() || !$lockedQuote->isSent()) {
                    throw new \RuntimeException('This quotation is no longer eligible for acceptance.');
                }

                if ($lockedQuote->valid_until && $lockedQuote->valid_until->lt(now()->startOfDay())) {
                    throw new \RuntimeException('This quotation has expired and can no longer be accepted.');
                }

                $lockedQuote->update([
                    'status' => 'accepted',
                    'accepted_at' => now(),
                ]);

                Quote::where('enquiry_id', $lockedEnquiry->id)
                    ->where('id', '!=', $lockedQuote->id)
                    ->where('status', 'sent')
                    ->update(['status' => 'rejected']);

                $lockedEnquiry->update(['status' => 'closed']);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'auditable_type' => Quote::class,
                    'auditable_id' => $lockedQuote->id,
                    'action' => 'enquiry_quote_accepted',
                    'new_values' => [
                        'enquiry_id' => $lockedEnquiry->id,
                        'vendor_id' => $lockedQuote->vendor_id,
                        'quote_amount' => $lockedQuote->quote_amount,
                    ],
                    'ip_address' => $request->ip(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->notifications->notifyUser(
            $quote->vendor?->user_id,
            '🎉',
            "Quotation Accepted — #{$enquiry->enquiry_number}",
            "Your quote #{$quote->quote_number} (₹" . number_format($quote->quote_amount / 100, 2) . ") was accepted by the buyer.",
            route('vendor.enquiries.index', ['tab' => 'closed']),
            'b2b_quote'
        );

        return back()->with('success', "You have accepted the quotation from {$quote->vendor->shop_name}. You can now coordinate final fulfillment via WhatsApp or direct call.");
    }

    /**
     * Buyer closes their own open/quoted enquiry when requirement is no longer needed.
     */
    public function close(Request $request, string $enquiry_number): RedirectResponse
    {
        $enquiry = Enquiry::where('enquiry_number', $enquiry_number)->firstOrFail();
        $user = auth()->user();
        $isAdmin = $user && $user->isAdmin();
        $hasGuestOwnerSession = (bool) session("guest_enquiry_{$enquiry->id}");

        if ($enquiry->user_id !== null) {
            if (!$user || ((int) $enquiry->user_id !== (int) $user->id && !$isAdmin)) {
                abort(403, 'Only the enquiry owner can close this requirement.');
            }
        } elseif (!$isAdmin && !$hasGuestOwnerSession) {
            abort(403, 'Only the enquiry owner can close this requirement.');
        }

        if ($enquiry->isClosed()) {
            return back()->with('info', 'This enquiry is already closed.');
        }

        DB::transaction(function () use ($enquiry, $user, $request) {
            $locked = Enquiry::whereKey($enquiry->id)->lockForUpdate()->firstOrFail();
            if ($locked->isClosed()) {
                return;
            }
            $locked->update(['status' => 'closed']);
            Quote::where('enquiry_id', $locked->id)
                ->where('status', 'sent')
                ->update(['status' => 'rejected']);

            AuditLog::create([
                'user_id' => $user?->id,
                'auditable_type' => Enquiry::class,
                'auditable_id' => $locked->id,
                'action' => 'enquiry_closed_by_owner',
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', "Requirement #{$enquiry->enquiry_number} has been closed.");
    }

    /**
     * Vendor withdraws an active sent quote before it is accepted.
     */
    public function withdrawQuote(Request $request, Quote $quote): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor || (int) $quote->vendor_id !== (int) $vendor->id) {
            abort(403, 'Unauthorized to withdraw this quotation.');
        }

        try {
            $enquiry = DB::transaction(function () use ($quote, $request) {
                $lockedQuote = Quote::whereKey($quote->id)->lockForUpdate()->firstOrFail();
                $lockedEnquiry = Enquiry::whereKey($lockedQuote->enquiry_id)->lockForUpdate()->firstOrFail();

                if (!$lockedQuote->isSent()) {
                    throw new \RuntimeException('Only pending sent quotations can be withdrawn.');
                }

                if ($lockedEnquiry->isClosed()) {
                    throw new \RuntimeException('This enquiry is already closed.');
                }

                $lockedQuote->update(['status' => 'withdrawn']);

                $activeCount = Quote::where('enquiry_id', $lockedEnquiry->id)
                    ->where('status', '!=', 'withdrawn')
                    ->count();

                $lockedEnquiry->update([
                    'quotes_count' => $activeCount,
                    'status' => $activeCount > 0 ? 'quoted' : 'open',
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'auditable_type' => Quote::class,
                    'auditable_id' => $lockedQuote->id,
                    'action' => 'enquiry_quote_withdrawn',
                    'ip_address' => $request->ip(),
                ]);

                return $lockedEnquiry;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($enquiry->user_id) {
            $this->notifications->notifyUser(
                $enquiry->user_id,
                '↩️',
                "Quotation Withdrawn — #{$enquiry->enquiry_number}",
                "{$vendor->shop_name} withdrew quote #{$quote->quote_number}.",
                route('enquiry.show', $enquiry->enquiry_number),
                'b2b_quote'
            );
        }

        return back()->with('success', "Quotation #{$quote->quote_number} withdrawn.");
    }

    /**
     * Submit a commercial RFQ inquiry directly from a B2B product page.
     */
    public function submitRfq(Request $request, Product $product): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!$this->visibility->isProductPubliclyVisible($product)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'This product is currently unavailable for RFQ submissions.'], 404)
                : back()->with('error', 'This product is currently unavailable for RFQ submissions.');
        }

        if (auth()->check() && $product->vendor && (int) $product->vendor->user_id === (int) auth()->id()) {
            $msg = 'You cannot submit a commercial RFQ on your own product.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'quantity' => 'required|string|max:100',
            'destination' => 'required|string|max:150',
            'company_name' => 'required|string|max:120',
            'phone' => 'required|string|max:20',
            'requirements' => 'nullable|string|max:2000',
        ]);

        $enquiryNumber = 'RFQ-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $enquiry = Enquiry::create([
            'enquiry_number' => $enquiryNumber,
            'user_id' => auth()->id(),
            'vendor_id' => $product->vendor_id,
            'category_id' => $product->category_id,
            'product_id' => $product->id,
            'title' => 'Commercial RFQ: ' . $product->title . ' (' . $validated['quantity'] . ' units)',
            'description' => "Commercial RFQ for {$product->title}\nQuantity: {$validated['quantity']}\nDestination: {$validated['destination']}\n" . ($validated['requirements'] ? "Requirements: {$validated['requirements']}" : ''),
            'quantity' => (string) $validated['quantity'],
            'target_budget' => null,
            'city' => $validated['destination'],
            'pincode' => is_numeric($validated['destination']) ? $validated['destination'] : null,
            'urgency' => 'immediate',
            'buyer_name' => trim($validated['company_name']),
            'buyer_phone' => trim($validated['phone']),
            'buyer_email' => auth()->user()?->email,
            'status' => 'open',
            'quotes_count' => 0,
        ]);

        session()->put("guest_enquiry_{$enquiry->id}", true);

        $this->notifications->notifyUser(
            $product->vendor?->user_id,
            '📦',
            "New Commercial RFQ #{$enquiryNumber}",
            "{$validated['company_name']} requested {$validated['quantity']} units of {$product->title}.",
            route('vendor.enquiries.index', ['tab' => 'direct']),
            'b2b_rfq'
        );

        $msg = "Your commercial RFQ ({$enquiryNumber}) for {$product->title} has been submitted to the seller!";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'enquiry_number' => $enquiryNumber,
                'enquiry_id' => $enquiry->id,
            ]);
        }

        return back()->with('success', $msg);
    }
}
