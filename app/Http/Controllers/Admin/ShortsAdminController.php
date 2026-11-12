<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Services\NotificationCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortsAdminController extends Controller
{
    /**
     * Admin Shorts list & moderation overview.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = Short::with(['vendor', 'category'])
            ->orderByDesc('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $shorts = $query->paginate(20);

        $counts = [
            'all' => Short::count(),
            'published' => Short::where('status', 'published')->count(),
            'pending' => Short::where('status', 'pending')->count(),
            'rejected' => Short::where('status', 'rejected')->count(),
            'boosted' => Short::where('is_boosted', true)->count(),
        ];

        return view('admin.shorts.index', [
            'shorts' => $shorts,
            'counts' => $counts,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Approve a pending short.
     */
    public function approve(Short $short): JsonResponse
    {
        $short->loadMissing('vendor');
        $short->update([
            'status' => 'published',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        if ($short->vendor?->user_id) {
            app(NotificationCenterService::class)->notifyUser(
                (int) $short->vendor->user_id,
                '🎬',
                'Short Approved & Published',
                "Your Short '{$short->title}' is now live on K71 Shorts.",
                route('vendor.shorts.studio'),
                'shorts'
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Short #{$short->id} approved and published.",
        ]);
    }

    /**
     * Reject a short.
     */
    public function reject(Request $request, Short $short): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $reason = trim((string) ($validated['reason'] ?? ''));
        if ($reason === '') {
            $reason = 'Content violates platform policy.';
        }

        $short->loadMissing('vendor');
        $short->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        if ($short->vendor?->user_id) {
            app(NotificationCenterService::class)->notifyUser(
                (int) $short->vendor->user_id,
                '⚠️',
                'Short Rejected',
                "Your Short '{$short->title}' was rejected: {$reason}",
                route('vendor.shorts.studio'),
                'shorts'
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Short #{$short->id} rejected.",
        ]);
    }

    /**
     * Toggle manual admin boost pin.
     */
    public function manualBoost(Short $short): JsonResponse
    {
        $short->is_manual_boost = !$short->is_manual_boost;
        $short->save();

        return response()->json([
            'success' => true,
            'is_manual_boost' => $short->is_manual_boost,
            'message' => $short->is_manual_boost ? 'Short pinned to top of feeds!' : 'Manual pin removed.',
        ]);
    }

    /**
     * Delete a short permanently by Admin.
     */
    public function destroy(Short $short)
    {
        $id = $short->id;
        $title = $short->title;

        foreach ([$short->video_path, $short->video_url, $short->thumb_url, $short->thumb_b_url] as $asset) {
            if (!empty($asset) && is_string($asset)) {
                $rel = ltrim(str_replace(['/storage/', 'storage/'], '', parse_url($asset, PHP_URL_PATH) ?: $asset), '/');
                if ($rel !== '' && str_starts_with($rel, 'shorts')) {
                    k71_delete_public_storage($rel);
                } elseif (!empty($short->video_path) && $asset === $short->video_path) {
                    k71_delete_public_storage($short->video_path);
                }
            }
        }

        $short->products()->detach();
        $short->forceDelete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Short #{$id} '{$title}' permanently removed.",
            ]);
        }

        return back()->with('success', "Short #{$id} ('{$title}') deleted successfully.");
    }
}
