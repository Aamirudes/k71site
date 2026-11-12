<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\Review;
use App\Models\UsedSellerProfile;
use App\Models\UsedThreadMessage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ReportController extends Controller
{
    /**
     * Submit a trust & safety report against a listing, seller, vendor, review, user, or thread message.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            $msg = 'Please sign in to submit a report.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 401)
                : redirect()->route('login')->with('error', $msg);
        }

        $rateKey = 'trust-report-' . $user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 15)) {
            $msg = 'Too many reports submitted in a short period. Please wait a minute.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 429)
                : back()->with('error', $msg);
        }
        RateLimiter::hit($rateKey, 60);

        $validated = $request->validate([
            'reportable_type' => 'required|in:product,vendor,review,user,used_seller,used_thread_message',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|max:80',
            'details' => 'nullable|string|max:1000',
        ]);

        $typeMap = [
            'product' => Product::class,
            'vendor' => Vendor::class,
            'review' => Review::class,
            'user' => User::class,
            'used_seller' => UsedSellerProfile::class,
            'used_thread_message' => UsedThreadMessage::class,
        ];

        $modelClass = $typeMap[$validated['reportable_type']];
        $model = $modelClass === Product::class
            ? Product::withoutGlobalScopes()->whereNull('deleted_at')->findOrFail($validated['reportable_id'])
            : $modelClass::findOrFail($validated['reportable_id']);

        $isSelfTarget = ($model instanceof User && (int) $model->id === (int) $user->id)
            || ($model instanceof Vendor && (int) $model->user_id === (int) $user->id)
            || ($model instanceof Product && ($model->isOwnedBy($user) || ((int) ($model->vendor?->user_id ?? 0) === (int) $user->id)))
            || ($model instanceof Review && (int) $model->user_id === (int) $user->id)
            || ($model instanceof UsedSellerProfile && (int) $model->user_id === (int) $user->id)
            || ($model instanceof UsedThreadMessage && (int) $model->sender_id === (int) $user->id);

        if ($isSelfTarget) {
            $msg = 'You cannot report your own account or content.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $alreadyPending = Report::where('user_id', $user->id)
            ->where('reportable_type', $modelClass)
            ->where('reportable_id', $model->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            $msg = 'You already have an open report for this item. Our moderation team is reviewing it.';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'already_reported' => true, 'message' => $msg])
                : back()->with('info', $msg);
        }

        $report = Report::create([
            'user_id' => $user->id,
            'reportable_type' => $modelClass,
            'reportable_id' => $model->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        $msg = 'Thank you for your report. Our trust & safety team will review it promptly.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'report_id' => $report->id,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
