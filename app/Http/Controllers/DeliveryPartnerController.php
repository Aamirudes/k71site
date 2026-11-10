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
            'password' => ['nullable', 'string', 'min:6'],
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
            $validated['phone'] = substr($cleanPhone ?: $validated['phone'], 0, 25);
            $userPhone = substr($cleanPhone ?: $validated['phone'], 0, 15);

            // Resolve or create dedicated fleet rider account
            $user = null;
            $fleetRole = \App\Models\Role::firstOrCreate(['name' => 'fleet'], ['guard_name' => 'web']);

            if (!empty($validated['email'])) {
                $normalizedEmail = strtolower(trim($validated['email']));
                $user = \App\Models\User::withTrashed()->where('email', $normalizedEmail)->first();
                if ($user && $user->trashed()) {
                    return back()->withInput()->withErrors([
                        'email' => 'This email address is associated with a deactivated account. Please use another email.',
                    ]);
                }
                if (!$user) {
                    $nameParts = explode(' ', trim($validated['name']), 2);
                    $firstName = \Illuminate\Support\Str::limit(trim($nameParts[0] ?? '') ?: 'Rider', 50, '');
                    $lastName = \Illuminate\Support\Str::limit(trim($nameParts[1] ?? ''), 50, '');

                    $riderUsername = 'r_' . substr(\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10)), 0, 14);
                    while (\App\Models\User::withTrashed()->where('username', $riderUsername)->exists()) {
                        $riderUsername = 'r_' . substr(md5($normalizedEmail . \Illuminate\Support\Str::random(6)), 0, 14);
                    }

                    $user = \App\Models\User::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $riderUsername,
                        'email' => $normalizedEmail,
                        'password' => \Illuminate\Support\Facades\Hash::make($request->input('password', \Illuminate\Support\Str::random(16))),
                        'phone' => $userPhone ?: null,
                        'email_verified_at' => now(),
                        'is_blocked' => false,
                    ]);
                }
                // Attach fleet role to this user account (distinct from vendor/customer/admin)
                $user->roles()->syncWithoutDetaching([$fleetRole->id]);
                $user->unsetRelation('roles');
                \Illuminate\Support\Facades\Auth::login($user);
            } elseif (auth()->check()) {
                $currentUser = auth()->user();
                // Never hijack an admin or vendor account into a fleet account!
                if (!$currentUser->isAdmin() && !$currentUser->isVendor()) {
                    $user = $currentUser;
                    $user->roles()->syncWithoutDetaching([$fleetRole->id]);
                    $user->unsetRelation('roles');
                }
            }

            $partner = null;
            if ($user) {
                $partner = DeliveryPartner::where('user_id', $user->id)->first();
            }
            if (!$partner) {
                $partner = DeliveryPartner::where('phone', $validated['phone'])->first();
            }

            $partnerData = array_merge($validated, [
                'user_id' => $user?->id,
                'is_online' => true,
                'is_verified' => true,
                'last_active_at' => now(),
            ]);
            unset($partnerData['password']);

            if (!$partner) {
                $partner = DeliveryPartner::create($partnerData);
            } else {
                $partner->update($partnerData);
            }

            session(['delivery_partner_id' => $partner->id]);

            return redirect()->route('delivery.dashboard')->with('success', 'Welcome to K71 Hyperlocal Fleet! Your Fleet Delivery Boy account is now active.');
        } catch (\Throwable $e) {
            \Log::error('Delivery partner registration error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Registration encountered an issue: ' . $e->getMessage());
        }
    }

    /**
     * Get active partner from session or auth safely (never hijacking admin or vendor)
     */
    protected function getActivePartner()
    {
        $partnerId = session('delivery_partner_id');
        if ($partnerId) {
            $partner = DeliveryPartner::find($partnerId);
            if ($partner) return $partner;
        }

        if (auth()->check()) {
            $user = auth()->user();

            // 1. Admin can inspect the fleet dashboard in administrative oversight mode
            if ($user->isAdmin()) {
                // Return an active fleet pilot without tying the record to the admin user
                $partner = DeliveryPartner::where('user_id', '!=', $user->id)->first() ?? DeliveryPartner::first();
                if (!$partner) {
                    $partner = DeliveryPartner::create([
                        'name' => 'K71 Fleet Pilot',
                        'phone' => '9829012345',
                        'vehicle_type' => 'bike',
                        'vehicle_number' => 'RJ-01-AB-1234',
                        'city' => 'Kishangarh',
                        'state' => 'Rajasthan',
                        'is_online' => true,
                        'is_verified' => true,
                        'current_latitude' => 26.5744,
                        'current_longitude' => 74.8654,
                        'payout_upi_id' => 'k71fleet@okhdfcbank',
                        'total_deliveries' => 25,
                        'rating' => 4.95,
                    ]);
                }
                return $partner;
            }

            // 2. Dedicated Fleet Delivery Boy user account
            if ($user->hasRole('fleet')) {
                $partner = DeliveryPartner::where('user_id', $user->id)->first();
                if ($partner) {
                    session(['delivery_partner_id' => $partner->id]);
                    return $partner;
                }
            }

            // 3. Normal customer or vendor: DO NOT auto-create a delivery partner!
            return null;
        }

        return null;
    }

    /**
     * Delivery Boy Dashboard
     */
    public function dashboard()
    {
        $partner = $this->getActivePartner();
        if (!$partner) {
            return redirect()->route('delivery.onboarding')
                ->with('error', 'You must register or log into your dedicated Fleet Delivery Boy account to access the pilot dashboard.');
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
        if (!$partner) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
     * Real-time distance and delivery fee pricing configuration (Blinkit style)
     */
    public function updatePricing(Request $request)
    {
        $partner = $this->getActivePartner();
        if (!$partner) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'base_delivery_fee' => 'required|numeric|min:5|max:500',
            'per_km_rate' => 'required|numeric|min:2|max:100',
            'max_delivery_radius_km' => 'nullable|numeric|min:1|max:50',
        ]);

        $partner->update([
            'base_delivery_fee' => (float) $validated['base_delivery_fee'],
            'per_km_rate' => (float) $validated['per_km_rate'],
            'max_delivery_radius_km' => (float) ($validated['max_delivery_radius_km'] ?? 15.0),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Real-time delivery rates updated successfully!',
                'base_delivery_fee' => $partner->base_delivery_fee,
                'per_km_rate' => $partner->per_km_rate,
                'max_delivery_radius_km' => $partner->max_delivery_radius_km,
            ]);
        }

        return back()->with('success', 'Real-time delivery rates updated successfully!');
    }

    /**
     * Active Alert Polling API - Triggers audio ringtone on delivery boy dashboard!
     */
    public function checkNewAssignments()
    {
        $partner = $this->getActivePartner();
        if (!$partner) {
            return response()->json(['has_new' => false, 'assignment' => null, 'error' => 'Unauthorized'], 401);
        }

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
                'pickup_address' => $newAssigned->vendor ? ($newAssigned->vendor->pickup_address['line_1'] ?? 'Kishangarh Center') : 'Vendor Store',
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
        $partner = $this->getActivePartner();
        if (!$partner || ($assignment->delivery_partner_id && $assignment->delivery_partner_id !== $partner->id && !auth()->user()?->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
        $partner = $this->getActivePartner();
        if (!$partner || ($assignment->delivery_partner_id && $assignment->delivery_partner_id !== $partner->id && !auth()->user()?->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
        $partner = $this->getActivePartner();
        if (!$partner || ($assignment->delivery_partner_id && $assignment->delivery_partner_id !== $partner->id && !auth()->user()?->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assignment->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $assignment->deliveryPartner?->increment('total_deliveries');

        if ($assignment->order) {
            $assignment->order->update(['status' => 'delivered']);
        }

        return response()->json(['status' => 'success', 'message' => 'Delivered successfully! Payout credited.']);
    }
}

