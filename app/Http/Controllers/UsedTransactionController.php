<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Review;
use App\Models\UsedThread;
use App\Models\UsedTransaction;
use App\Services\Used\UsedMarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsedTransactionController extends Controller
{
    public function __construct(private UsedMarketplaceService $usedService)
    {
    }

    /**
     * Seller marks handover completed & closes listing as Sold.
     */
    public function complete(Request $request, UsedTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        if ((int) $transaction->seller_id !== (int) $user->id && !$user->isAdmin()) {
            abort(403, 'Only the seller can mark this handover complete.');
        }

        if ($transaction->isCompleted()) {
            return back()->with('success', 'This transaction is already completed.');
        }
        if ($transaction->isCancelled()) {
            return back()->with('error', 'Cannot complete a cancelled reservation.');
        }

        DB::transaction(function () use ($transaction, $user, $request) {
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'seller_notes' => $request->input('seller_notes', $transaction->seller_notes),
            ]);

            $product = $transaction->product()->lockForUpdate()->first();
            if ($product) {
                $product->markAsSold();
                UsedThread::where('product_id', $product->id)->update(['status' => 'locked']);

                Offer::where('product_id', $product->id)
                    ->whereIn('status', ['pending', 'countered'])
                    ->update(['status' => 'rejected', 'rejected_at' => now(), 'responded_at' => now()]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'auditable_type' => UsedTransaction::class,
                'auditable_id' => $transaction->id,
                'action' => 'used_transaction_completed',
                'new_values' => ['status' => 'completed', 'agreed_price' => $transaction->agreed_price],
                'ip_address' => $request->ip(),
            ]);
        });

        $this->usedService->recalculateSellerStats($transaction->seller_id);
        $this->usedService->notifyUser(
            $transaction->buyer_id,
            '⭐',
            'Handover Completed — Leave a Review',
            "Your purchase of \"{$transaction->product?->title}\" has been marked completed. You can now rate the seller.",
            route('used.dashboard', ['tab' => 'buyer'])
        );

        return back()->with('success', 'Handover marked complete! Listing is now SOLD and buyer can leave a verified review.');
    }

    /**
     * Seller or Buyer cancels a reservation so item returns to Active.
     */
    public function cancel(Request $request, UsedTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        $isParticipant = (int) $transaction->seller_id === (int) $user->id
            || (int) $transaction->buyer_id === (int) $user->id
            || $user->isAdmin();

        if (!$isParticipant) {
            abort(403, 'Unauthorized to cancel this reservation.');
        }

        if ($transaction->isCompleted()) {
            return back()->with('error', 'Completed transactions cannot be cancelled.');
        }

        DB::transaction(function () use ($transaction, $user, $request) {
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $product = $transaction->product()->lockForUpdate()->first();
            if ($product && !$product->isSold()) {
                $otherReserved = UsedTransaction::where('product_id', $product->id)
                    ->where('id', '!=', $transaction->id)
                    ->where('status', 'reserved')
                    ->exists();
                if (!$otherReserved) {
                    $product->markAvailable();
                }
            }

            AuditLog::create([
                'user_id' => $user->id,
                'auditable_type' => UsedTransaction::class,
                'auditable_id' => $transaction->id,
                'action' => 'used_transaction_cancelled',
                'new_values' => ['status' => 'cancelled'],
                'ip_address' => $request->ip(),
            ]);
        });

        $peerId = (int) $user->id === (int) $transaction->seller_id ? $transaction->buyer_id : $transaction->seller_id;
        $this->usedService->notifyUser(
            $peerId,
            '↩️',
            'Reservation Cancelled',
            "Reservation #{$transaction->transaction_number} for \"{$transaction->product?->title}\" was cancelled.",
            route('used.dashboard', ['tab' => 'transactions'])
        );

        return back()->with('success', 'Reservation cancelled. Listing returned to active availability.');
    }

    /**
     * Transaction-gated genuine buyer review for a completed Used handover.
     */
    public function storeReview(Request $request, UsedTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        if ((int) $transaction->buyer_id !== (int) $user->id) {
            abort(403, 'Only the buyer of a completed transaction can leave a review.');
        }

        if (!$transaction->isCompleted()) {
            return back()->with('error', 'Reviews are unlocked once the handover transaction is marked completed.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:160',
            'body' => 'required|string|min:5|max:1500',
        ]);

        $alreadyReviewed = Review::where('product_id', $transaction->product_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'You have already submitted a review for this transaction.');
        }

        Review::create([
            'product_id' => $transaction->product_id,
            'user_id' => $user->id,
            'vendor_id' => $transaction->product?->vendor_id,
            'used_transaction_id' => $transaction->id,
            'reviewed_user_id' => $transaction->seller_id,
            'rating' => (int) $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => trim($validated['body']),
            'is_verified_purchase' => true,
            'is_approved' => true,
        ]);

        $this->usedService->recalculateSellerStats($transaction->seller_id);

        $this->usedService->notifyUser(
            $transaction->seller_id,
            '⭐',
            'New Seller Rating Received',
            "{$user->name} rated your transaction {$validated['rating']}/5 stars.",
            route('used.dashboard', ['tab' => 'overview'])
        );

        return back()->with('success', 'Thank you! Your verified buyer review has been published on the seller profile.');
    }
}

