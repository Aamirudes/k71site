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

        $outcome = DB::transaction(function () use ($transaction, $user, $request) {
            $locked = UsedTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->isCompleted()) {
                return ['status' => 'already_completed', 'tx' => $locked];
            }
            if ($locked->isCancelled()) {
                return ['status' => 'cancelled', 'tx' => $locked];
            }

            $locked->update([
                'status' => 'completed',
                'completed_at' => now(),
                'seller_notes' => $request->input('seller_notes', $locked->seller_notes),
            ]);

            $product = $locked->product()->lockForUpdate()->first();
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
                'auditable_id' => $locked->id,
                'action' => 'used_transaction_completed',
                'new_values' => ['status' => 'completed', 'agreed_price' => $locked->agreed_price],
                'ip_address' => $request->ip(),
            ]);

            return ['status' => 'ok', 'tx' => $locked];
        });

        if ($outcome['status'] === 'already_completed') {
            return back()->with('success', 'This transaction is already completed.');
        }
        if ($outcome['status'] === 'cancelled') {
            return back()->with('error', 'Cannot complete a cancelled reservation.');
        }

        $locked = $outcome['tx'];
        $this->usedService->recalculateSellerStats($locked->seller_id);
        $this->usedService->notifyUser(
            $locked->buyer_id,
            '⭐',
            'Handover Completed — Leave a Review',
            "Your purchase of \"{$locked->product?->title}\" has been marked completed. You can now rate the seller.",
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

        $outcome = DB::transaction(function () use ($transaction, $user, $request) {
            $locked = UsedTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->isCompleted()) {
                return ['status' => 'completed', 'tx' => $locked];
            }
            if ($locked->isCancelled()) {
                return ['status' => 'already_cancelled', 'tx' => $locked];
            }

            $locked->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $product = $locked->product()->lockForUpdate()->first();
            if ($product && !$product->isSold()) {
                $otherReserved = UsedTransaction::where('product_id', $product->id)
                    ->where('id', '!=', $locked->id)
                    ->where('status', 'reserved')
                    ->exists();
                if (!$otherReserved) {
                    $product->markAvailable();
                }
            }

            AuditLog::create([
                'user_id' => $user->id,
                'auditable_type' => UsedTransaction::class,
                'auditable_id' => $locked->id,
                'action' => 'used_transaction_cancelled',
                'new_values' => ['status' => 'cancelled'],
                'ip_address' => $request->ip(),
            ]);

            return ['status' => 'ok', 'tx' => $locked];
        });

        if ($outcome['status'] === 'completed') {
            return back()->with('error', 'Completed transactions cannot be cancelled.');
        }
        if ($outcome['status'] === 'already_cancelled') {
            return back()->with('info', 'This reservation is already cancelled.');
        }

        $locked = $outcome['tx'];
        $peerId = (int) $user->id === (int) $locked->seller_id ? $locked->buyer_id : $locked->seller_id;
        $this->usedService->notifyUser(
            $peerId,
            '↩️',
            'Reservation Cancelled',
            "Reservation #{$locked->transaction_number} for \"{$locked->product?->title}\" was cancelled.",
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

        $review = DB::transaction(function () use ($transaction, $user, $validated) {
            $lockedTx = UsedTransaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            if (!$lockedTx->isCompleted()) {
                return ['ok' => false, 'msg' => 'Reviews are unlocked once the handover transaction is marked completed.'];
            }

            $alreadyReviewed = Review::where('product_id', $lockedTx->product_id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyReviewed) {
                return ['ok' => false, 'msg' => 'You have already submitted a review for this transaction.'];
            }

            Review::create([
                'product_id' => $lockedTx->product_id,
                'user_id' => $user->id,
                'vendor_id' => $lockedTx->product?->vendor_id,
                'used_transaction_id' => $lockedTx->id,
                'reviewed_user_id' => $lockedTx->seller_id,
                'rating' => (int) $validated['rating'],
                'title' => $validated['title'] ?? null,
                'body' => trim($validated['body']),
                'is_verified_purchase' => true,
                'is_approved' => true,
            ]);

            return ['ok' => true];
        });

        if (!$review['ok']) {
            return back()->with('error', $review['msg']);
        }

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

