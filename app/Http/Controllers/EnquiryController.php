<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Display the public B2B & Custom Requirements Marketplace Hub (enquiry.k71.in).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('cat');
        $city = $request->query('city');
        $urgency = $request->query('urgency');
        $minBudget = $request->query('min_budget');
        $maxBudget = $request->query('max_budget');

        // Fetch active enquiry categories
        $enquiryCategories = Category::where('vertical', 'enquiry')
            ->where('is_active', true)
            ->withCount(['enquiries' => fn($q) => $q->where('status', 'open')])
            ->orderBy('sort_order')
            ->get();

        // Available distinct cities from open enquiries
        $availableCities = Enquiry::where('status', 'open')
            ->whereNotNull('city')
            ->pluck('city')
            ->unique()
            ->values();

        // Main RFQs / Enquiries Query
        $query = Enquiry::with(['category', 'vendor', 'quotes'])
            ->whereIn('status', ['open', 'quoted']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('buyer_name', 'like', "%{$search}%");
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

        $enquiries = $query->latest()->paginate(15)->withQueryString();

        return view('enquiry.index', compact(
            'enquiryCategories',
            'availableCities',
            'enquiries',
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
            $targetVendor = Vendor::find($request->query('vendor_id'));
        }

        $targetProduct = null;
        if ($request->has('product_id')) {
            $targetProduct = Product::find($request->query('product_id'));
        }

        return view('enquiry.create', compact('categories', 'targetVendor', 'targetProduct'));
    }

    /**
     * Store new buyer requirement / RFQ.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category_id' => 'nullable|exists:categories,id',
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

        $enquiryNumber = 'ENQ-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $enquiry = Enquiry::create([
            'enquiry_number' => $enquiryNumber,
            'user_id' => auth()->id(),
            'vendor_id' => $validated['vendor_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'target_budget' => isset($validated['target_budget']) && is_numeric($validated['target_budget'])
                ? (int) ($validated['target_budget'] * 100)
                : null,
            'city' => $validated['city'],
            'pincode' => $validated['pincode'] ?? null,
            'urgency' => $validated['urgency'] ?? 'flexible',
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['buyer_phone'],
            'buyer_email' => $validated['buyer_email'] ?? auth()->user()?->email,
            'status' => 'open',
            'quotes_count' => 0,
        ]);

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

        return view('enquiry.show', compact('enquiry'));
    }

    /**
     * Vendor enquiries inbox & leads board (/vendor/enquiries).
     */
    public function vendorIndex(Request $request): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Unauthorized vendor account.');
        }

        $tab = $request->query('tab', 'all');

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
     * Vendor submits a quote on an enquiry.
     */
    public function vendorQuote(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Unauthorized vendor account.');
        }

        if ($enquiry->isClosed()) {
            return back()->with('error', 'This enquiry is closed and is no longer accepting quotations.');
        }

        if ($enquiry->vendor_id && $enquiry->vendor_id !== $vendor->id) {
            abort(403, 'This enquiry is directed to another vendor.');
        }

        $validated = $request->validate([
            'quote_amount' => 'required|numeric|min:1',
            'lead_time_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $quoteNumber = 'QT-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        // Create or update vendor's quote
        $quote = Quote::updateOrCreate(
            [
                'enquiry_id' => $enquiry->id,
                'vendor_id' => $vendor->id,
            ],
            [
                'quote_number' => $quoteNumber,
                'quote_amount' => (int) ($validated['quote_amount'] * 100),
                'lead_time_days' => $validated['lead_time_days'],
                'notes' => $validated['notes'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
                'status' => 'sent',
            ]
        );

        $enquiry->update([
            'status' => 'quoted',
            'quotes_count' => $enquiry->quotes()->count(),
        ]);

        return back()->with('success', "Your quotation of ₹" . number_format($quote->quote_amount / 100, 2) . " has been submitted to the buyer.");
    }

    /**
     * Buyer accepts a quotation.
     */
    public function acceptQuote(Request $request, Quote $quote): RedirectResponse
    {
        $enquiry = $quote->enquiry;

        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Mark other quotes on same enquiry as rejected
        Quote::where('enquiry_id', $enquiry->id)
            ->where('id', '!=', $quote->id)
            ->update(['status' => 'rejected']);

        $enquiry->update(['status' => 'closed']);

        return back()->with('success', "You have accepted the quotation from {$quote->vendor->shop_name}. You can now coordinate final fulfillment via WhatsApp or direct call.");
    }
}
