<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Customer notifications list.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => (bool) $notification,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ], $notification ? 200 : 404);
        }

        $redirectTo = $request->query('redirect');
        if (is_string($redirectTo) && str_starts_with($redirectTo, '/') && !str_starts_with($redirectTo, '//')) {
            return redirect($redirectTo);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get real-time unread notifications count for nav bell badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }
}
