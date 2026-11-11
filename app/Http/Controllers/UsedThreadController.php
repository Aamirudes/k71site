<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\UsedThread;
use App\Services\Used\UsedMarketplaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class UsedThreadController extends Controller
{
    public function __construct(private UsedMarketplaceService $usedService)
    {
    }

    /**
     * Buyer starts or appends to a listing-attached enquiry thread.
     */
    public function enquire(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please sign in to contact the seller.'], 401);
            }
            return redirect()->route('login');
        }

        if ($product->isOwnedBy($user)) {
            $msg = 'You cannot start an enquiry on your own listing.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($product->isSold()) {
            $msg = 'This listing has already been marked as sold; new enquiries are closed.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if (!$product->isPubliclyVisible()) {
            abort(404);
        }

        $rateKey = 'used-enquire-' . $user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $msg = 'Message rate limit reached. Please wait briefly.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 429)
                : back()->with('error', $msg);
        }
        RateLimiter::hit($rateKey, 60);

        $validated = $request->validate([
            'body' => 'required|string|min:2|max:2000',
        ]);

        $thread = $this->usedService->startOrSendEnquiry($user, $product, $validated['body']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'thread_id' => $thread->id,
                'redirect_url' => route('used.threads.show', $thread),
                'message' => 'Message sent to seller!',
            ]);
        }

        return redirect()->route('used.threads.show', $thread)
            ->with('success', 'Enquiry sent to the seller!');
    }

    /**
     * Participant-only view for a listing enquiry thread.
     */
    public function show(UsedThread $thread): View
    {
        $user = auth()->user();
        if (!$thread->isParticipant($user) && !$user->isAdmin()) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $thread->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $thread->load(['product.images', 'seller.usedSellerProfile', 'buyer', 'messages.sender']);

        $isSeller = (int) $thread->seller_id === (int) $user->id;

        return view('used.thread-show', compact('thread', 'isSeller', 'user'));
    }

    /**
     * Send a message in an active listing enquiry thread.
     */
    public function reply(Request $request, UsedThread $thread): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$thread->isParticipant($user)) {
            abort(403, 'Unauthorized to reply in this conversation.');
        }

        if ($thread->isLocked()) {
            $msg = 'This listing has been sold; new enquiry messages are closed.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:2000',
        ]);

        $msg = $this->usedService->appendThreadMessage($thread, $user, $validated['body']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $msg,
            ]);
        }

        return back()->with('success', 'Message sent.');
    }

    /**
     * Seller shares private pickup locality/instructions ONLY within this buyer thread.
     */
    public function sharePickupLocation(Request $request, UsedThread $thread): RedirectResponse
    {
        $user = auth()->user();
        if ((int) $thread->seller_id !== (int) $user->id) {
            abort(403, 'Only the seller can share pickup details for this listing.');
        }

        if ($thread->isLocked()) {
            return back()->with('error', 'Listing is already sold.');
        }

        $validated = $request->validate([
            'pickup_location' => 'nullable|string|max:500',
        ]);

        $location = trim((string) (($validated['pickup_location'] ?? null) ?: $thread->product?->private_pickup_area ?: $thread->product?->city_label));
        if ($location === '') {
            return back()->with('error', 'Please enter pickup area / meetup spot details.');
        }

        $thread->update([
            'shared_pickup_location' => $location,
            'pickup_shared_at' => now(),
        ]);

        $this->usedService->appendThreadMessage(
            $thread,
            $user,
            "Seller shared pickup / meetup details: {$location}",
            'location_share'
        );

        return back()->with('success', 'Pickup area shared privately with this buyer.');
    }
}

