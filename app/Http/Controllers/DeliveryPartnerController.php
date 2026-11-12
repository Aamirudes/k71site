<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\NotificationCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $needsCredentials = !auth()->check() || auth()->user()->isAdmin() || auth()->user()->isVendor();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => [$needsCredentials ? 'required' : 'nullable', 'email', 'max:100'],
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
            $fleetRole = \App\Models\Role::firstOrCreate(
                ['name' => 'fleet'],
                ['display_name' => 'Fleet Partner', 'guard_name' => 'web']
            );

            if (!empty($validated['email'])) {
                $normalizedEmail = strtolower(trim($validated['email']));
                $user = \App\Models\User::withTrashed()->where('email', $normalizedEmail)->first();
                if ($user && $user->trashed()) {
                    return back()->withInput()->withErrors([
                        'email' => 'This email address is associated with a deactivated account. Please use another email.',
                    ]);
                }
                if ($user) {
                    if ($user->is_blocked) {
                        return back()->withInput()->withErrors([
                            'email' => 'This account has been suspended. Please contact K71 support.',
                        ]);
                    }
                    if ($user->isAdmin() || $user->isVendor() || $user->vendor()->exists()) {
                        return back()->withInput()->withErrors([
                            'email' => 'This email belongs to a store or admin account. Please register with a separate email for your Fleet account.',
                        ]);
                    }
                    $rawPassword = (string) $request->input('password', '');
                    if ($rawPassword === '' || !\Illuminate\Support\Facades\Hash::check($rawPassword, $user->password)) {
                        return back()->withInput()->withErrors([
                            'email' => 'An account with this email already exists. Sign in at /login or enter the matching password.',
                        ]);
                    }
                } else {
                    $nameParts = explode(' ', trim($validated['name']), 2);
                    $firstName = \Illuminate\Support\Str::limit(trim($nameParts[0] ?? '') ?: 'Rider', 50, '');
                    $lastName = \Illuminate\Support\Str::limit(trim($nameParts[1] ?? ''), 50, '');

                    $riderUsername = 'r_' . substr(\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(10)), 0, 14);
                    while (\App\Models\User::withTrashed()->where('username', $riderUsername)->exists()) {
                        $riderUsername = 'r_' . substr(md5($normalizedEmail . \Illuminate\Support\Str::random(6)), 0, 14);
                    }

                    $rawNewPassword = (string) $request->input('password', '');
                    $user = \App\Models\User::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $riderUsername,
                        'email' => $normalizedEmail,
                        'password' => \Illuminate\Support\Facades\Hash::make($rawNewPassword !== '' ? $rawNewPassword : \Illuminate\Support\Str::random(24)),
                        'phone' => $userPhone ?: null,
                        'email_verified_at' => now(),
                        'is_blocked' => false,
                    ]);
                }

                $user->roles()->syncWithoutDetaching([$fleetRole->id]);
                $user->unsetRelation('roles');
                \Illuminate\Support\Facades\Auth::login($user, true);
                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }
            } elseif (auth()->check()) {
                $currentUser = auth()->user();
                if ($currentUser->is_blocked) {
                    return back()->withInput()->withErrors([
                        'email' => 'Your account has been suspended. Please contact K71 support.',
                    ]);
                }
                if ($currentUser->isAdmin() || $currentUser->isVendor()) {
                    return back()->withInput()->withErrors([
                        'email' => 'Store and Admin accounts must register a separate email for a Fleet Partner profile.',
                    ]);
                }
                $user = $currentUser;
                $user->roles()->syncWithoutDetaching([$fleetRole->id]);
                $user->unsetRelation('roles');
            }

            if (!$user) {
                return back()->withInput()->withErrors([
                    'email' => 'Please provide a valid email and password to create your Fleet account.',
                ]);
            }

            $partner = DeliveryPartner::where('user_id', $user->id)->first();

            $partnerData = array_merge($validated, [
                'user_id' => $user->id,
                'email' => $user->email,
                'vehicle_number' => !empty($validated['vehicle_number']) ? strtoupper(trim($validated['vehicle_number'])) : null,
                'is_online' => true,
                'is_verified' => true,
                'last_active_at' => now(),
            ]);
            unset($partnerData['password']);

            if (!$partner) {
                $partnerData['rating'] = 5.0;
                $partnerData['total_deliveries'] = 0;
                $partner = DeliveryPartner::create($partnerData);
            } else {
                $partner->update($partnerData);
            }

            session(['delivery_partner_id' => $partner->id]);

            return redirect()->route('delivery.dashboard')->with('success', 'Welcome to K71 Hyperlocal Fleet! Your Fleet Partner account is now active.');
        } catch (\Throwable $e) {
            \Log::error('Delivery partner registration error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Registration encountered an issue: ' . $e->getMessage());
        }
    }

    /**
     * Get active partner from session or auth safely (never hijacking admin or vendor, never inventing fake pilots)
     */
    protected function getActivePartner()
    {
        $partnerId = session('delivery_partner_id');
        if ($partnerId) {
            $partner = DeliveryPartner::with('user')->realFleet()->find($partnerId);
            if ($partner) {
                $riderBlocked = (bool) ($partner->user?->is_blocked ?? false);
                if ($riderBlocked || (auth()->check() && !auth()->user()->isAdmin() && $partner->user_id !== auth()->id())) {
                    session()->forget('delivery_partner_id');
                } else {
                    return $partner;
                }
            } else {
                session()->forget('delivery_partner_id');
            }
        }

        if (auth()->check()) {
            $user = auth()->user();

            // 1. Admin can inspect the fleet dashboard in administrative oversight mode (real registered riders only)
            if ($user->isAdmin()) {
                return DeliveryPartner::realFleet()->latest('id')->first();
            }

            if ($user->is_blocked) {
                return null;
            }

            // 2. Dedicated Fleet Partner user account
            if ($user->isFleet()) {
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
        $adminOversight = auth()->check() && auth()->user()->isAdmin();
        $fleetRidersCount = DeliveryPartner::realFleet()->count();

        if (!$partner) {
            if ($adminOversight) {
                return view('delivery.dashboard', [
                    'partner' => null,
                    'adminOversight' => true,
                    'fleetRidersCount' => $fleetRidersCount,
                    'activeAssignments' => collect(),
                    'completedAssignments' => collect(),
                    'totalEarningsToday' => 0,
                ]);
            }

            return redirect()->route('delivery.onboarding')
                ->with('error', 'Please register or sign in with your Fleet Partner account to access the Fleet Dashboard.');
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

        return view('delivery.dashboard', compact('partner', 'adminOversight', 'fleetRidersCount', 'activeAssignments', 'completedAssignments', 'totalEarningsToday'));
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

        $updateData = ['last_active_at' => now()];
        if ($lat !== null && is_numeric($lat)) {
            $latVal = (float) $lat;
            if ($latVal >= -90 && $latVal <= 90) {
                $updateData['current_latitude'] = $latVal;
            }
        }
        if ($lng !== null && is_numeric($lng)) {
            $lngVal = (float) $lng;
            if ($lngVal >= -180 && $lngVal <= 180) {
                $updateData['current_longitude'] = $lngVal;
            }
        }
        if ($request->has('is_online') && $request->input('is_online') !== null) {
            $updateData['is_online'] = $request->boolean('is_online');
        }

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
                'pickup_address' => $newAssigned->vendor ? ($newAssigned->vendor->pickup_address['line_1'] ?? $newAssigned->vendor->shop_name) : 'Vendor Store',
                'payout' => '₹' . number_format($newAssigned->payout_amount, 2),
                'distance' => $newAssigned->pickup_distance_km !== null ? ($newAssigned->pickup_distance_km . ' km') : 'Distance pending',
            ] : null,
        ]);
    }

    /**
     * Accept Order
     */
    public function acceptAssignment(DeliveryAssignment $assignment)
    {
        $partner = $this->getActivePartner();
        if (!$partner || ($assignment->delivery_partner_id && (int) $assignment->delivery_partner_id !== (int) $partner->id && !auth()->user()?->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $order = DB::transaction(function () use ($assignment, $partner) {
                $locked = DeliveryAssignment::whereKey($assignment->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'assigned') {
                    throw new \RuntimeException("Assignment is currently '{$locked->status}' and cannot be accepted again.");
                }

                $lockedOrder = $locked->order_id ? Order::whereKey($locked->order_id)->lockForUpdate()->first() : null;
                if ($lockedOrder && in_array($lockedOrder->status, ['cancelled', 'delivered'], true)) {
                    $locked->update(['status' => 'cancelled']);
                    throw new \RuntimeException("Order is already {$lockedOrder->status} and can no longer be picked up.");
                }

                $locked->update([
                    'delivery_partner_id' => $locked->delivery_partner_id ?: $partner->id,
                    'status' => 'accepted',
                    'accepted_at' => now(),
                ]);

                if ($lockedOrder) {
                    $fromStatus = $lockedOrder->status;
                    $orderUpdate = [
                        'status' => 'processing',
                        'packed_at' => $lockedOrder->packed_at ?: now(),
                    ];
                    if ($lockedOrder->isFoodOrder()) {
                        $orderUpdate['accepted_at'] = $lockedOrder->accepted_at ?: now();
                        $orderUpdate['preparing_at'] = $lockedOrder->preparing_at ?: now();
                        $orderUpdate['food_fulfillment_stage'] = 'preparing';
                    }
                    $lockedOrder->update($orderUpdate);

                    OrderStatusLog::create([
                        'order_id' => $lockedOrder->id,
                        'from_status' => $fromStatus,
                        'to_status' => 'processing',
                        'actor_type' => 'fleet',
                        'actor_id' => auth()->id(),
                        'note' => "Delivery partner {$partner->name} accepted assignment and is heading to pickup.",
                    ]);
                }

                return $lockedOrder;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        if ($order && $order->user_id) {
            app(NotificationCenterService::class)->notifyUser(
                (int) $order->user_id,
                '🛵',
                "Rider Assigned — #{$order->order_number}",
                "{$partner->name} accepted your delivery and is heading for pickup.",
                route('orders.show', $order),
                'delivery_tracking'
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Order accepted! Proceed to pickup.']);
    }

    /**
     * Pickup Order
     */
    public function pickupAssignment(DeliveryAssignment $assignment)
    {
        $partner = $this->getActivePartner();
        if (!$partner || ($assignment->delivery_partner_id && (int) $assignment->delivery_partner_id !== (int) $partner->id && !auth()->user()?->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $order = DB::transaction(function () use ($assignment, $partner) {
                $locked = DeliveryAssignment::whereKey($assignment->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'accepted') {
                    throw new \RuntimeException("Order must be 'accepted' before marking picked up (currently '{$locked->status}').");
                }

                $lockedOrder = $locked->order_id ? Order::whereKey($locked->order_id)->lockForUpdate()->first() : null;
                if ($lockedOrder && in_array($lockedOrder->status, ['cancelled', 'delivered'], true)) {
                    $locked->update(['status' => 'cancelled']);
                    throw new \RuntimeException("Order is already {$lockedOrder->status} and cannot be dispatched.");
                }

                $locked->update([
                    'status' => 'picked_up',
                    'picked_up_at' => now(),
                ]);

                if ($lockedOrder) {
                    $fromStatus = $lockedOrder->status;
                    $orderUpdate = [
                        'status' => 'shipped',
                        'shipped_at' => $lockedOrder->shipped_at ?: now(),
                    ];
                    if ($lockedOrder->isFoodOrder()) {
                        $orderUpdate['out_for_delivery_at'] = $lockedOrder->out_for_delivery_at ?: now();
                        $orderUpdate['food_fulfillment_stage'] = 'out_for_delivery';
                    }
                    $lockedOrder->update($orderUpdate);

                    OrderStatusLog::create([
                        'order_id' => $lockedOrder->id,
                        'from_status' => $fromStatus,
                        'to_status' => 'shipped',
                        'actor_type' => 'fleet',
                        'actor_id' => auth()->id(),
                        'note' => "Order picked up by {$partner->name} and is out for delivery.",
                    ]);
                }

                return $lockedOrder;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        if ($order && $order->user_id) {
            app(NotificationCenterService::class)->notifyUser(
                (int) $order->user_id,
                '🚚',
                "Out for Delivery — #{$order->order_number}",
                "{$partner->name} picked up your order and is on the way.",
                route('orders.show', $order),
                'delivery_tracking'
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Order picked up! Now delivering.']);
    }

    /**
     * Deliver Order
     */
    public function deliverAssignment(DeliveryAssignment $assignment)
    {
        $partner = $this->getActivePartner();
        if (!$partner || ($assignment->delivery_partner_id && (int) $assignment->delivery_partner_id !== (int) $partner->id && !auth()->user()?->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $order = DB::transaction(function () use ($assignment, $partner) {
                $locked = DeliveryAssignment::whereKey($assignment->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'picked_up') {
                    throw new \RuntimeException("Order must be 'picked_up' before marking delivered (currently '{$locked->status}').");
                }

                $lockedOrder = $locked->order_id ? Order::whereKey($locked->order_id)->lockForUpdate()->first() : null;
                if ($lockedOrder && $lockedOrder->status === 'cancelled') {
                    $locked->update(['status' => 'cancelled']);
                    throw new \RuntimeException('Cancelled orders cannot be marked as delivered.');
                }

                $locked->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);

                if ($locked->delivery_partner_id) {
                    DeliveryPartner::whereKey($locked->delivery_partner_id)->increment('total_deliveries');
                }

                if ($lockedOrder) {
                    $fromStatus = $lockedOrder->status;
                    $orderUpdate = [
                        'status' => 'delivered',
                        'delivered_at' => $lockedOrder->delivered_at ?: now(),
                    ];
                    if ($lockedOrder->isFoodOrder()) {
                        $orderUpdate['food_fulfillment_stage'] = 'delivered';
                    }
                    $lockedOrder->update($orderUpdate);

                    OrderStatusLog::create([
                        'order_id' => $lockedOrder->id,
                        'from_status' => $fromStatus,
                        'to_status' => 'delivered',
                        'actor_type' => 'fleet',
                        'actor_id' => auth()->id(),
                        'note' => "Order delivered by {$partner->name}.",
                    ]);
                }

                return $lockedOrder;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        if ($order) {
            $notify = app(NotificationCenterService::class);
            if ($order->user_id) {
                $notify->notifyUser(
                    (int) $order->user_id,
                    '🎉',
                    "Order Delivered — #{$order->order_number}",
                    "Your order has been delivered by {$partner->name}. Enjoy!",
                    route('orders.show', $order),
                    'delivery_tracking'
                );
            }
            if ($order->vendor?->user_id) {
                $notify->notifyUser(
                    (int) $order->vendor->user_id,
                    '✅',
                    "Order Delivered — #{$order->order_number}",
                    "Fleet rider {$partner->name} completed delivery.",
                    route('vendor.orders.show', $order),
                    'delivery_tracking'
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Delivered successfully! Payout credited.']);
    }
}

