<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Short;
use App\Models\ShortBoost;
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
        $short->update([
            'status' => 'published',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

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
        $reason = $request->input('reason', 'Content violates platform policy.');

        $short->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

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
