<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Product;
use App\Services\Monetization\EntitlementService;
use App\Services\Monetization\VisibilityService;
use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected VisibilityService $visibility,
        protected EntitlementService $entitlements,
        protected NotificationCenterService $notifications
    ) {}

    /**
     * Store a new service booking appointment.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        if ($product->status !== 'active' || !$this->visibility->isProductPubliclyVisible($product)) {
            return back()->with('error', 'This service is currently not available for bookings.');
        }

        if ($product->vertical !== 'services' && !in_array($product->listing_type, ['booking', 'service', 'gig'], true)) {
            return back()->with('error', 'Appointments can only be scheduled on eligible service listings.');
        }

        if (auth()->check() && $product->vendor && (int) $product->vendor->user_id === (int) auth()->id()) {
            return back()->with('error', 'You cannot book an appointment for your own service.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:150',
            'service_address' => 'required|string|max:500',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_slot' => 'required|string|max:60',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking = DB::transaction(function () use ($product, $validated) {
                $lockedProduct = Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->firstOrFail();

                if ($lockedProduct->status !== 'active') {
                    throw new \RuntimeException('This service is currently not available for bookings.');
                }

                // Prevent double-booking active time slots for the same service atomically
                $slotTaken = Booking::where('product_id', $lockedProduct->id)
                    ->whereDate('booking_date', $validated['booking_date'])
                    ->where('booking_slot', trim($validated['booking_slot']))
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->lockForUpdate()
                    ->exists();

                if ($slotTaken) {
                    throw new \RuntimeException('That time slot has already been requested or confirmed. Please choose another slot.');
                }

                $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(6));

                return Booking::create([
                    'booking_number' => $bookingNumber,
                    'product_id' => $lockedProduct->id,
                    'vendor_id' => $lockedProduct->vendor_id,
                    'user_id' => auth()->id(),
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'] ?? auth()->user()?->email,
                    'service_address' => $validated['service_address'],
                    'booking_date' => $validated['booking_date'],
                    'booking_slot' => trim($validated['booking_slot']),
                    'total_amount' => $lockedProduct->base_price,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        session()->put("guest_booking_{$booking->id}", true);

        $this->notifications->notifyUser(
            $product->vendor?->user_id,
            '📅',
            "New Booking Request #{$booking->booking_number}",
            "{$validated['customer_name']} requested {$product->title} on {$booking->booking_date} ({$booking->booking_slot}).",
            route('vendor.bookings.index'),
            'service_booking'
        );

        return redirect()->route('bookings.show', $booking->booking_number)
            ->with('success', 'Your appointment request has been submitted successfully! The professional will confirm shortly.');
    }

    /**
     * Display a booking confirmation / tracker screen.
     */
    public function show(string $booking_number): View
    {
        $booking = Booking::where('booking_number', $booking_number)
            ->with(['product.images', 'product.category', 'vendor.settings'])
            ->firstOrFail();

        $user = auth()->user();
        $isOwner = $user && $booking->user_id && (int) $user->id === (int) $booking->user_id;
        $isVendorOwner = $user && $booking->vendor && (int) $booking->vendor->user_id === (int) $user->id;
        $isAdmin = $user && $user->isAdmin();
        $hasGuestSession = (bool) session("guest_booking_{$booking->id}");

        // Block signed-in unrelated third parties from inspecting another customer's booking
        if ($user && !$isOwner && !$isVendorOwner && !$isAdmin && ($booking->user_id !== null || !$hasGuestSession)) {
            abort(403, 'Unauthorized access to this booking record.');
        }

        $canViewSensitiveContact = $isOwner
            || $isVendorOwner
            || $isAdmin
            || ($hasGuestSession && (!$user || $booking->user_id === null));

        return view('services.booking-show', compact('booking', 'canViewSensitiveContact'));
    }

    /**
     * Vendor bookings management console (/vendor/bookings).
     */
    public function vendorIndex(Request $request): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Unauthorized vendor account.');
        }

        $status = $request->query('status');

        $counts = [
            'all' => Booking::where('vendor_id', $vendor->id)->count(),
            'pending' => Booking::where('vendor_id', $vendor->id)->where('status', 'pending')->count(),
            'confirmed' => Booking::where('vendor_id', $vendor->id)->where('status', 'confirmed')->count(),
            'completed' => Booking::where('vendor_id', $vendor->id)->where('status', 'completed')->count(),
            'cancelled' => Booking::where('vendor_id', $vendor->id)->where('status', 'cancelled')->count(),
        ];

        $query = Booking::with(['product.images', 'user'])
            ->where('vendor_id', $vendor->id);

        if ($status && in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return view('vendor.bookings.index', compact('bookings', 'counts', 'status'));
    }

    /**
     * Vendor updates booking status (confirm, complete, cancel).
     */
    public function vendorUpdateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor || (int) $booking->vendor_id !== (int) $vendor->id) {
            abort(403, 'Unauthorized action on this booking.');
        }

        $authCheck = $this->entitlements->authorizeSectorWrite($vendor, 'services');
        if (!$authCheck['allowed']) {
            return back()->with('error', $authCheck['reason']);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled',
            'vendor_notes' => 'nullable|string|max:500',
            'cancelled_reason' => 'nullable|string|max:500',
        ]);

        $targetStatus = $validated['status'];

        try {
            DB::transaction(function () use ($booking, $validated, $targetStatus) {
                $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

                $allowedTransitions = [
                    'pending' => ['confirmed', 'completed', 'cancelled'],
                    'confirmed' => ['completed', 'cancelled'],
                    'completed' => [],
                    'cancelled' => [],
                ];

                if (!in_array($targetStatus, $allowedTransitions[$locked->status] ?? [], true)) {
                    throw new \RuntimeException("Booking #{$locked->booking_number} cannot transition from '{$locked->status}' to '{$targetStatus}'.");
                }

                $updateData = [
                    'status' => $targetStatus,
                    'vendor_notes' => $validated['vendor_notes'] ?? $locked->vendor_notes,
                ];

                if ($targetStatus === 'confirmed') {
                    $updateData['confirmed_at'] = now();
                } elseif ($targetStatus === 'completed') {
                    $updateData['completed_at'] = now();
                } elseif ($targetStatus === 'cancelled') {
                    $updateData['cancelled_at'] = now();
                    $updateData['cancelled_reason'] = $validated['cancelled_reason'] ?? 'Cancelled by service provider';
                }

                $locked->update($updateData);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->notifications->notifyUser(
            $booking->user_id,
            '📅',
            "Booking #{$booking->booking_number} " . ucfirst($targetStatus),
            "{$vendor->shop_name} marked your booking for " . ($booking->product?->title ?? 'service') . " as {$targetStatus}.",
            route('bookings.show', $booking->booking_number),
            'service_booking'
        );

        return back()->with('success', "Booking #{$booking->booking_number} status updated to " . ucfirst($targetStatus) . '.');
    }

    /**
     * Customer cancels their own pending/confirmed appointment booking.
     */
    public function cancel(Request $request, string $booking_number): RedirectResponse
    {
        $booking = Booking::where('booking_number', $booking_number)->with(['vendor', 'product'])->firstOrFail();

        $user = auth()->user();
        $isOwner = $user && $booking->user_id && (int) $user->id === (int) $booking->user_id;
        $isAdmin = $user && $user->isAdmin();
        $hasGuestSession = (bool) session("guest_booking_{$booking->id}") && $booking->user_id === null;

        if (!$isOwner && !$isAdmin && !$hasGuestSession) {
            abort(403, 'Only the booking customer can cancel this appointment.');
        }

        $validated = $request->validate([
            'cancelled_reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($booking, $validated) {
                $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
                if (!in_array($locked->status, ['pending', 'confirmed'], true)) {
                    throw new \RuntimeException("Booking #{$locked->booking_number} cannot be cancelled because it is already {$locked->status}.");
                }

                $locked->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_reason' => trim((string) ($validated['cancelled_reason'] ?? '')) ?: 'Cancelled by customer',
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->notifications->notifyUser(
            $booking->vendor?->user_id,
            '🚫',
            "Booking #{$booking->booking_number} Cancelled",
            "{$booking->customer_name} cancelled the appointment for " . ($booking->product?->title ?? 'service') . " on {$booking->booking_date}.",
            route('vendor.bookings.index'),
            'service_booking'
        );

        return back()->with('success', "Appointment #{$booking->booking_number} has been cancelled and the slot is now released.");
    }
}
