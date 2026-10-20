<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Store a new service booking appointment.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        if ($product->status !== 'active') {
            return back()->with('error', 'This service is currently not available for bookings.');
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

        $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $booking = Booking::create([
            'booking_number' => $bookingNumber,
            'product_id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'user_id' => auth()->id(),
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? auth()->user()?->email,
            'service_address' => $validated['service_address'],
            'booking_date' => $validated['booking_date'],
            'booking_slot' => $validated['booking_slot'],
            'total_amount' => $product->base_price,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('bookings.show', $booking->booking_number)
            ->with('success', 'Your appointment request has been submitted successfully! The professional will confirm shortly.');
    }

    /**
     * Display a booking confirmation / tracker screen.
     */
    public function show(string $booking_number): View
    {
        $booking = Booking::where('booking_number', $booking_number)
            ->with(['product.images', 'vendor.settings'])
            ->firstOrFail();

        return view('services.show', compact('booking'));
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

        if ($status && in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
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
        if (!$vendor || $booking->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized action on this booking.');
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled',
            'vendor_notes' => 'nullable|string|max:500',
            'cancelled_reason' => 'nullable|string|max:500',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'vendor_notes' => $validated['vendor_notes'] ?? $booking->vendor_notes,
        ];

        if ($validated['status'] === 'confirmed') {
            $updateData['confirmed_at'] = now();
        } elseif ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
        } elseif ($validated['status'] === 'cancelled') {
            $updateData['cancelled_at'] = now();
            $updateData['cancelled_reason'] = $validated['cancelled_reason'] ?? 'Cancelled by service provider';
        }

        $booking->update($updateData);

        return back()->with('success', "Booking #{$booking->booking_number} status updated to " . ucfirst($validated['status']) . ".");
    }
}
