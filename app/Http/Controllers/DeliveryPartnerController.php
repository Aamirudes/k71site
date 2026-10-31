<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryPartnerController extends Controller
{
    /**
     * Delivery Partner Onboarding Landing Page
     */
    public function showOnboarding()
    {
        return view('delivery.onboarding');
    }

    /**
     * Submit Partner Registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
            'vehicle_type' => ['required', 'string', 'in:bike,scooter,ev,bicycle'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'aadhaar_number' => ['nullable', 'string', 'max:20'],
            'driving_license' => ['nullable', 'string', 'max:30'],
            'payout_upi_id' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            // Clean phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
            if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
                $cleanPhone = substr($cleanPhone, 2);
            }
            $validated['phone'] = $cleanPhone ?: $validated['phone'];

            $partner = null;
            if (auth()->check()) {
                $partner = DeliveryPartner::where('user_id', auth()->id())->first();
            }
            if (!$partner) {
                $partner = DeliveryPartner::where('phone', $validated['phone'])->first();
            }

            if (!$partner) {
                $partner = DeliveryPartner::create(array_merge($validated, [
                    'user_id' => auth()->id(),
                    'is_online' => true,
                    'is_verified' => true,
                    'last_active_at' => now(),
                ]));
            } else {
                $partner->update(array_merge($validated, [
                    'user_id' => auth()->id() ?: $partner->user_id,
                    'is_online' => true,
                    'last_active_at' => now(),
                ]));
            }

            // Assign role if authenticated user
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->role !== 'admin') {
                    $user->role = 'delivery_partner';
                    $user->save();
                }
            }

            session(['delivery_partner_id' => $partner->id]);

            return redirect()->route('delivery.dashboard')->with('success', 'Welcome to K71 Hyperlocal Fleet! You are now online.');
        } catch (\Throwable $e) {
            \Log::error('Delivery partner registration error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Registration encountered an issue: ' . $e->getMessage());
        }
    }

    /**
     * Get active partner from session or auth
     */
    protected function getActivePartner()
    {
        $partnerId = session('delivery_partner_id');
        if ($partnerId) {
            $partner = DeliveryPartner::find($partnerId);
            if ($partner) return $partner;
        }

        if (auth()->check()) {
            $partner = DeliveryPartner::where('user_id', auth()->id())->first();
            if ($partner) {
                session(['delivery_partner_id' => $partner->id]);
                return $partner;
            }
        }

        // Fallback demo/active partner for testing
        $partner = DeliveryPartner::first();
        if (!$partner) {
            $partner = DeliveryPartner::create([
                'name' => 'Ramesh Kumar (K71 Fleet Pilot)',
                'phone' => '9829012345',
                'vehicle_type' => 'bike',
                'vehicle_number' => 'RJ-21-SD-4091',
                'city' => 'Makrana',
                'state' => 'Rajasthan',
                'is_online' => true,
                'is_verified' => true,
                'current_latitude' => 27.0425,
                'current_longitude' => 74.7214,
                'payout_upi_id' => 'ramesh.k71@okhdfcbank',
                'total_deliveries' => 142,
                'rating' => 4.95,
            ]);
        }
        session(['delivery_partner_id' => $partner->id]);
        return $partner;
    }

    /**
     * Delivery Boy Dashboard
     */
    public function dashboard()
    {
        $partner = $this->getActivePartner();
        if (!$partner) {
            return redirect()->route('delivery.onboarding');
        }

        $activeAssignments = DeliveryAssignment::with(['order', 'vendor'])
            ->where('delivery_partner_id', $partner->id)
            ->whereIn('status', ['assigned', 'accepted', 'picked_up'])
            ->latest()
            ->get();

        $completedAssignments = DeliveryAssignment::with(['order', 'vendor'])
            ->where('delivery_partner_id', $partner->id)
            ->where('status', 'delivered')
            ->latest()
            ->take(15)
            ->get();

        $totalEarningsToday = DeliveryAssignment::where('delivery_partner_id', $partner->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->sum('payout_amount');

        return view('delivery.dashboard', compact('partner', 'activeAssignments', 'completedAssignments', 'totalEarningsToday'));
    }

    /**
     * Location & Online Toggle API
     */
    public function updateLocation(Request $request)
    {
        $partner = $this->getActivePartner();

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $isOnline = $request->input('is_online');

        $updateData = ['last_active_at' => now()];
        if ($lat !== null) $updateData['current_latitude'] = (float) $lat;
        if ($lng !== null) $updateData['current_longitude'] = (float) $lng;
        if ($isOnline !== null) $updateData['is_online'] = (bool) $isOnline;

        $partner->update($updateData);

        return response()->json([
            'status' => 'success',
            'is_online' => $partner->is_online,
            'latitude' => $partner->current_latitude,
            'longitude' => $partner->current_longitude,
        ]);
    }

    /**
     * Active Alert Polling API - Triggers audio ringtone on delivery boy dashboard!
     */
    public function checkNewAssignments()
    {
        $partner = $this->getActivePartner();

        $newAssigned = DeliveryAssignment::with(['order.items', 'vendor'])
            ->where('delivery_partner_id', $partner->id)
            ->where('status', 'assigned')
            ->latest()
            ->first();

        return response()->json([
            'has_new' => (bool) $newAssigned,
            'assignment' => $newAssigned ? [
                'id' => $newAssigned->id,
                'order_id' => $newAssigned->order_id,
                'order_number' => $newAssigned->order ? $newAssigned->order->order_number : ('#ORD-' . $newAssigned->order_id),
                'vendor_name' => $newAssigned->vendor ? $newAssigned->vendor->shop_name : 'K71 Merchant',
                'pickup_address' => $newAssigned->vendor ? ($newAssigned->vendor->pickup_address['line_1'] ?? 'Makrana Center') : 'Vendor Store',
                'payout' => '₹' . number_format($newAssigned->payout_amount, 2),
                'distance' => $newAssigned->pickup_distance_km ? ($newAssigned->pickup_distance_km . ' km') : '0.8 km',
            ] : null,
        ]);
    }

    /**
     * Accept Order
     */
    public function acceptAssignment(DeliveryAssignment $assignment)
    {
        $assignment->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        if ($assignment->order) {
            $assignment->order->update(['status' => 'processing']);
        }

        return response()->json(['status' => 'success', 'message' => 'Order accepted! Proceed to pickup.']);
    }

    /**
     * Pickup Order
     */
    public function pickupAssignment(DeliveryAssignment $assignment)
    {
        $assignment->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        if ($assignment->order) {
            $assignment->order->update(['status' => 'shipped']);
        }

        return response()->json(['status' => 'success', 'message' => 'Order picked up! Now delivering.']);
    }

    /**
     * Deliver Order
     */
    public function deliverAssignment(DeliveryAssignment $assignment)
    {
        $assignment->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $assignment->deliveryPartner()->increment('total_deliveries');

        if ($assignment->order) {
            $assignment->order->update(['status' => 'delivered']);
        }

        return response()->json(['status' => 'success', 'message' => 'Delivered successfully! Payout credited.']);
    }
}

