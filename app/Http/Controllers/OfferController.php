<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Offer;
use App\Models\Product;
use App\Models\UsedThread;
use App\Models\UsedTransaction;
use App\Services\Used\UsedMarketplaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(private UsedMarketplaceService $usedService)
    {
    }

    /**
     * Submit a buyer offer for a used / pre-owned listing.
     */
    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            $msg = 'Please log in to submit an offer.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }
            return redirect()->route('login')->with('error', $msg);
        }

        if ($product->isSold()) {
            $msg = 'This item has already been marked as sold and cannot receive new offers.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($product->isPaused() || $product->isDraft() || !$product->isPubliclyVisible()) {
            $msg = 'This listing is currently unavailable for offers.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($product->isReserved()) {
            $msg = 'This item is currently reserved pending handover with another buyer.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($product->isOwnedBy($user)) {
            $msg = 'You cannot make an offer on your own listing.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if (!$product->isNegotiable()) {
            $msg = 'Seller has listed this item at a fixed price (offers disabled). Use Contact Seller to enquire.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $rateKey = 'used-offer-' . $user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $msg = 'Too many offers submitted recently. Please wait a minute.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 429)
                : back()->with('error', $msg);
        }
        RateLimiter::hit($rateKey, 60);

        $validated = $request->validate([
            'buyer_name' => 'nullable|string|max:120',
            'buyer_phone' => 'nullable|string|min:7|max:20',
            'buyer_email' => 'nullable|email|max:150',
            'offer_amount' => 'required|numeric|min:1|max:500000000',
            'message' => 'nullable|string|max:500',
            'handoff_preference' => 'nullable|string|in:local_pickup,public_meetup,seller_shipping',
        ]);

        $offerPaise = (int) round(((float) $validated['offer_amount']) * 100);
        if ($product->min_offer_amount && $offerPaise < (int) $product->min_offer_amount) {
            $minRupees = number_format($product->min_offer_amount / 100, 0);
            $msg = "Minimum offer accepted for this item is ₹{$minRupees}.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withInput()->with('error', $msg);
        }

        $sellerId = $product->ownerUserId();
        $buyerName = trim((string) (($validated['buyer_name'] ?? null) ?: $user->name));
        $buyerPhone = trim((string) (($validated['buyer_phone'] ?? null) ?: ($user->phone ?? 'Not shared')));

        try {
            $offer = DB::transaction(function () use ($product, $user, $sellerId, $buyerName, $buyerPhone, $validated, $offerPaise) {
                $lockedProduct = Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->firstOrFail();
                if ($lockedProduct->isSold() || $lockedProduct->isReserved()) {
                    throw new \RuntimeException('This item is no longer available for new offers.');
                }

                $activeOfferExists = Offer::where('product_id', $lockedProduct->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'countered'])
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->exists();

                if ($activeOfferExists) {
                    throw new \RuntimeException('You already have an active offer on this item. Wait for the seller or withdraw it first before sending another offer.');
                }

                return Offer::create([
                    'product_id' => $lockedProduct->id,
                    'vendor_id' => $lockedProduct->vendor_id,
                    'seller_id' => $sellerId,
                    'user_id' => $user->id,
                    'buyer_name' => $buyerName,
                    'buyer_phone' => $buyerPhone,
                    'buyer_email' => $validated['buyer_email'] ?? $user->email,
                    'offer_amount' => $offerPaise,
                    'message' => $validated['message'] ?? null,
                    'handoff_preference' => $validated['handoff_preference'] ?? 'local_pickup',
                    'status' => 'pending',
                    'last_action_by' => 'buyer',
                    'expires_at' => now()->addDays((int) config('used_marketplace.offer_expiry_days', 7)),
                ]);
            });
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($sellerId) {
            $note = "Submitted an offer of " . $offer->formattedOfferAmount()
                . (!empty($validated['message']) ? ": \"{$validated['message']}\"" : '.');
            try {
                $this->usedService->startOrSendEnquiry($user, $product, $note, 'offer_event');
            } catch (\Throwable $e) {}

            $this->usedService->notifyUser(
                $sellerId,
                '🤝',
                'New Buyer Offer Received',
                "{$buyerName} offered {$offer->formattedOfferAmount()} for \"{$product->title}\".",
                route('used.dashboard', ['tab' => 'offers'])
            );
        }

        $msg = "Your offer of ₹" . number_format((float) $validated['offer_amount'], 2) . " has been submitted to the seller!";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'offer_id' => $offer->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Vendor / Seller inbox for received offers (/vendor/offers).
     */
    public function vendorIndex(Request $request): View
    {
        $user = auth()->user();
        $vendor = $user->vendor;
        if (!$vendor) {
            abort(403);
        }

        $status = $request->query('status', 'all');

        $baseScope = Offer::where(function ($q) use ($vendor, $user) {
            $q->where('vendor_id', $vendor->id)
              ->orWhere('seller_id', $user->id);
        });

        $counts = [
            'all' => (clone $baseScope)->count(),
            'pending' => (clone $baseScope)->where('status', 'pending')->count(),
            'accepted' => (clone $baseScope)->where('status', 'accepted')->count(),
            'countered' => (clone $baseScope)->where('status', 'countered')->count(),
            'rejected' => (clone $baseScope)->where('status', 'rejected')->count(),
        ];

        $query = (clone $baseScope)->with(['product.images', 'user']);

        if ($status !== 'all' && array_key_exists($status, $counts)) {
            $query->where('status', $status);
        }

        $offers = $query->latest()->paginate(20)->withQueryString();

        return view('vendor.offers.index', compact('offers', 'counts', 'status'));
    }

    /**
     * Seller responds to an offer (accept, reject, or counter) with atomic conflict protection.
     */
    public function vendorRespond(Request $request, Offer $offer): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$offer->isOwnedBySeller($user)) {
            abort(403, 'Unauthorized access to offer.');
        }

        if ($user->vendor) {
            $offerAuth = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($user->vendor, 'used', 'allow_offer_negotiation');
            if (!$offerAuth['allowed']) {
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $offerAuth['reason']], 403)
                    : back()->with('error', $offerAuth['reason']);
            }
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,reject,counter',
            'counter_amount' => 'required_if:action,counter|nullable|numeric|min:1|max:500000000',
            'vendor_notes' => 'nullable|string|max:500',
        ]);

        $result = DB::transaction(function () use ($offer, $validated, $user) {
            $lockedOffer = Offer::where('id', $offer->id)->lockForUpdate()->firstOrFail();
            $product = Product::withoutGlobalScopes()->where('id', $lockedOffer->product_id)->lockForUpdate()->firstOrFail();

            if ($lockedOffer->isWithdrawn()) {
                return ['ok' => false, 'msg' => 'This offer was withdrawn by the buyer.'];
            }
            if ($lockedOffer->isExpired() && $validated['action'] !== 'reject') {
                $lockedOffer->update(['status' => 'expired']);
                return ['ok' => false, 'msg' => 'This offer has expired.'];
            }
            if (!in_array($lockedOffer->status, ['pending', 'countered'], true)) {
                return ['ok' => false, 'msg' => "Offer has already been finalized ({$lockedOffer->status})."];
            }

            $action = $validated['action'];

            if ($action === 'accept') {
                if ($product->isSold()) {
                    return ['ok' => false, 'msg' => 'Listing is already marked as sold.'];
                }

                $existingReservation = UsedTransaction::where('product_id', $product->id)
                    ->whereIn('status', ['reserved', 'completed'])
                    ->exists();

                if ($product->isReserved() || $existingReservation) {
                    return ['ok' => false, 'msg' => 'Listing is already reserved for a handover. Cancel existing reservation first.'];
                }

                $lockedOffer->update([
                    'status' => 'accepted',
                    'vendor_notes' => $validated['vendor_notes'] ?? null,
                    'responded_at' => now(),
                    'accepted_at' => now(),
                    'last_action_by' => 'seller',
                ]);

                if ($lockedOffer->user_id) {
                    $sellerId = $product->ownerUserId() ?? $user->id;
                    UsedTransaction::create([
                        'transaction_number' => 'K71U-' . strtoupper(Str::random(7)),
                        'product_id' => $product->id,
                        'seller_id' => $sellerId,
                        'buyer_id' => $lockedOffer->user_id,
                        'offer_id' => $lockedOffer->id,
                        'agreed_price' => $lockedOffer->offer_amount,
                        'quantity' => 1,
                        'fulfillment_method' => $lockedOffer->handoff_preference ?: 'local_pickup',
                        'status' => 'reserved',
                        'private_pickup_location' => $product->private_pickup_area,
                        'reserved_at' => now(),
                    ]);

                    $product->markReserved($lockedOffer->user_id);

                    // Reveal private pickup area inside their listing thread if configured
                    if ($product->private_pickup_area) {
                        $thread = UsedThread::firstOrCreate(
                            ['product_id' => $product->id, 'buyer_id' => $lockedOffer->user_id],
                            ['seller_id' => $sellerId, 'status' => 'open', 'last_message_at' => now()]
                        );
                        $thread->update([
                            'shared_pickup_location' => $product->private_pickup_area,
                            'pickup_shared_at' => now(),
                        ]);
                    }
                }

                return [
                    'ok' => true,
                    'actionWord' => 'accepted',
                    'status' => 'accepted',
                ];
            }

            if ($action === 'counter') {
                $counterPaise = (int) round(((float) $validated['counter_amount']) * 100);
                if ($product->base_price && $counterPaise > (int) $product->base_price) {
                    return ['ok' => false, 'msg' => 'Counter-offer cannot exceed the listing asking price of ₹' . number_format($product->base_price / 100, 2) . '.'];
                }
                if ($counterPaise <= (int) $lockedOffer->offer_amount) {
                    return ['ok' => false, 'msg' => 'Counter-offer must be higher than the buyer\'s original offer of ₹' . number_format($lockedOffer->offer_amount / 100, 2) . '.'];
                }

                $lockedOffer->update([
                    'status' => 'countered',
                    'counter_amount' => $counterPaise,
                    'vendor_notes' => $validated['vendor_notes'] ?? null,
                    'responded_at' => now(),
                    'expires_at' => now()->addDays((int) config('used_marketplace.offer_expiry_days', 7)),
                    'last_action_by' => 'seller',
                ]);

                return [
                    'ok' => true,
                    'actionWord' => 'countered with ₹' . number_format((float) $validated['counter_amount'], 2),
                    'status' => 'countered',
                ];
            }

            $lockedOffer->update([
                'status' => 'rejected',
                'vendor_notes' => $validated['vendor_notes'] ?? null,
                'responded_at' => now(),
                'rejected_at' => now(),
                'last_action_by' => 'seller',
            ]);

            return [
                'ok' => true,
                'actionWord' => 'rejected',
                'status' => 'rejected',
            ];
        });

        if (!$result['ok']) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $result['msg']], 422)
                : back()->with('error', $result['msg']);
        }

        $offer->refresh();
        if ($offer->user_id) {
            $this->usedService->notifyUser(
                $offer->user_id,
                $result['status'] === 'accepted' ? '✅' : ($result['status'] === 'countered' ? '⚡' : '✕'),
                'Seller Responded to Your Offer',
                "Your offer on \"{$offer->product?->title}\" was {$result['actionWord']}.",
                route('used.dashboard', ['tab' => 'buyer'])
            );
        }

        AuditLog::create([
            'user_id' => $user->id,
            'auditable_type' => Offer::class,
            'auditable_id' => $offer->id,
            'action' => 'offer_' . $validated['action'],
            'new_values' => ['status' => $offer->status, 'counter_amount' => $offer->counter_amount],
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Offer has been {$result['actionWord']} successfully."]);
        }

        return back()->with('success', "Offer has been {$result['actionWord']} successfully.");
    }

    /**
     * Buyer withdraws their pending or countered offer.
     */
    public function withdraw(Request $request, Offer $offer): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$offer->isOwnedByBuyer($user)) {
            abort(403, 'Unauthorized to withdraw this offer.');
        }

        if (!in_array($offer->status, ['pending', 'countered'], true)) {
            $msg = 'Only pending or countered offers can be withdrawn.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $offer->update([
            'status' => 'withdrawn',
            'withdrawn_at' => now(),
            'last_action_by' => 'buyer',
        ]);

        if ($offer->seller_id) {
            $this->usedService->notifyUser(
                $offer->seller_id,
                '↩️',
                'Offer Withdrawn by Buyer',
                "{$offer->buyer_name} withdrew their offer of {$offer->formattedOfferAmount()} on \"{$offer->product?->title}\".",
                route('used.dashboard', ['tab' => 'offers'])
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Offer withdrawn successfully.']);
        }

        return back()->with('success', 'Your offer has been withdrawn.');
    }

    /**
     * Buyer accepts or declines a seller's counteroffer.
     */
    public function buyerRespondCounter(Request $request, Offer $offer): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$offer->isOwnedByBuyer($user)) {
            abort(403, 'Unauthorized to respond to this counteroffer.');
        }

        $validated = $request->validate([
            'action' => 'required|in:accept_counter,reject_counter',
        ]);

        $result = DB::transaction(function () use ($offer, $validated, $user) {
            $lockedOffer = Offer::where('id', $offer->id)->lockForUpdate()->firstOrFail();
            $product = Product::withoutGlobalScopes()->where('id', $lockedOffer->product_id)->lockForUpdate()->firstOrFail();

            if (!$lockedOffer->isCountered()) {
                return ['ok' => false, 'msg' => 'This offer does not have an active counteroffer to respond to.'];
            }
            if ($lockedOffer->isExpired()) {
                $lockedOffer->update(['status' => 'expired']);
                return ['ok' => false, 'msg' => 'This counteroffer has expired.'];
            }

            if ($validated['action'] === 'accept_counter') {
                if ($product->isSold()) {
                    return ['ok' => false, 'msg' => 'Listing has already been marked sold.'];
                }

                $existingReservation = UsedTransaction::where('product_id', $product->id)
                    ->whereIn('status', ['reserved', 'completed'])
                    ->exists();
                if ($product->isReserved() || $existingReservation) {
                    return ['ok' => false, 'msg' => 'Item is currently reserved for a handover.'];
                }

                $agreedPaise = (int) ($lockedOffer->counter_amount ?: $lockedOffer->offer_amount);

                $lockedOffer->update([
                    'status' => 'accepted',
                    'accepted_at' => now(),
                    'responded_at' => now(),
                    'last_action_by' => 'buyer_accepted_counter',
                ]);

                $sellerId = $product->ownerUserId() ?? $lockedOffer->seller_id;
                UsedTransaction::create([
                    'transaction_number' => 'K71U-' . strtoupper(Str::random(7)),
                    'product_id' => $product->id,
                    'seller_id' => $sellerId,
                    'buyer_id' => $user->id,
                    'offer_id' => $lockedOffer->id,
                    'agreed_price' => $agreedPaise,
                    'quantity' => 1,
                    'fulfillment_method' => $lockedOffer->handoff_preference ?: 'local_pickup',
                    'status' => 'reserved',
                    'private_pickup_location' => $product->private_pickup_area,
                    'reserved_at' => now(),
                ]);

                $product->markReserved($user->id);

                // Reveal private pickup area inside their listing thread if configured
                if ($product->private_pickup_area && $sellerId) {
                    $thread = UsedThread::firstOrCreate(
                        ['product_id' => $product->id, 'buyer_id' => $user->id],
                        ['seller_id' => $sellerId, 'status' => 'open', 'last_message_at' => now()]
                    );
                    $thread->update([
                        'shared_pickup_location' => $product->private_pickup_area,
                        'pickup_shared_at' => now(),
                    ]);
                }

                return ['ok' => true, 'accepted' => true, 'agreed' => $agreedPaise];
            }

            $lockedOffer->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'responded_at' => now(),
                'last_action_by' => 'buyer',
            ]);

            return ['ok' => true, 'accepted' => false];
        });

        if (!$result['ok']) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $result['msg']], 422)
                : back()->with('error', $result['msg']);
        }

        $offer->refresh();
        AuditLog::create([
            'user_id' => $user->id,
            'auditable_type' => Offer::class,
            'auditable_id' => $offer->id,
            'action' => 'offer_' . $validated['action'],
            'new_values' => ['status' => $offer->status, 'counter_amount' => $offer->counter_amount],
            'ip_address' => $request->ip(),
        ]);

        if ($offer->seller_id) {
            $statusMsg = $result['accepted']
                ? "{$user->name} accepted your counteroffer on \"{$offer->product?->title}\". Item is now reserved for handover!"
                : "{$user->name} declined your counteroffer on \"{$offer->product?->title}\".";

            $this->usedService->notifyUser(
                $offer->seller_id,
                $result['accepted'] ? '🎉' : '✕',
                $result['accepted'] ? 'Counteroffer Accepted!' : 'Counteroffer Declined',
                $statusMsg,
                route('used.dashboard', ['tab' => $result['accepted'] ? 'transactions' : 'offers'])
            );
        }

        $msg = $result['accepted']
            ? 'Counteroffer accepted! This item is now reserved for your handover.'
            : 'Counteroffer declined.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Mark a used item as Sold (supports both merchant vendor & individual seller).
     */
    public function markSold(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$product->isOwnedBy($user)) {
            abort(403, 'Unauthorized action.');
        }

        $request->merge(['action' => 'mark_sold']);
        return app(UsedListingController::class)->updateStatus($request, $product);
    }
}
