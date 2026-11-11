<?php

namespace App\Services\Food;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductModifier;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FoodMarketplaceService
{
    public const SESSION_LOCATION_KEY = 'k71_food_location';

    /**
     * Collect genuine distinct service cities across active/approved food kitchens.
     *
     * @return Collection<int, string>
     */
    public function getAvailableServiceCities(): Collection
    {
        $vendors = Vendor::whereIn('status', ['approved', 'active'])
            ->where(function ($q) {
                $q->where('vendor_type', 'food')
                  ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true));
            })
            ->with('settings')
            ->get(['id', 'city', 'status', 'vendor_type']);

        $cities = [];
        foreach ($vendors as $vendor) {
            if (!empty(trim((string) $vendor->city))) {
                $city = trim((string) $vendor->city);
                $cities[strtolower($city)] = $city;
            }
            foreach ($vendor->service_cities_list as $svcCity) {
                $clean = trim((string) $svcCity);
                if ($clean !== '' && !isset($cities[strtolower($clean)])) {
                    $cities[strtolower($clean)] = $clean;
                }
            }
        }

        return collect(array_values($cities))
            ->sort(fn (string $a, string $b) => strcasecmp($a, $b))
            ->values();
    }

    /**
     * Resolve customer selected location from query, session, or saved default address.
     *
     * @return array{label: ?string, city: ?string, pincode: ?string, state: ?string, address_id: ?int}
     */
    public function getUserSelectedLocation(?Request $request = null): array
    {
        $request = $request ?: request();

        $queryCity = trim((string) $request->query('city', ''));
        $queryPincode = trim((string) $request->query('pincode', ''));
        if ($queryCity !== '' || $queryPincode !== '') {
            return [
                'label' => trim(collect([$queryCity, $queryPincode])->filter()->implode(' ')),
                'city' => $queryCity ?: null,
                'pincode' => $queryPincode ?: null,
                'state' => null,
                'address_id' => null,
            ];
        }

        $sessionLoc = session(self::SESSION_LOCATION_KEY);
        if (is_array($sessionLoc) && (!empty($sessionLoc['label']) || !empty($sessionLoc['city']) || !empty($sessionLoc['pincode']))) {
            return [
                'label' => $sessionLoc['label'] ?? trim(collect([$sessionLoc['city'] ?? null, $sessionLoc['pincode'] ?? null])->filter()->implode(' ')),
                'city' => $sessionLoc['city'] ?? null,
                'pincode' => $sessionLoc['pincode'] ?? null,
                'state' => $sessionLoc['state'] ?? null,
                'address_id' => $sessionLoc['address_id'] ?? null,
            ];
        }

        if (Auth::check() && Schema::hasTable('addresses')) {
            $defaultAddress = Address::where('user_id', Auth::id())
                ->orderByDesc('is_default')
                ->latest()
                ->first();

            if ($defaultAddress) {
                return [
                    'label' => $defaultAddress->shortLabel(),
                    'city' => $defaultAddress->city,
                    'pincode' => $defaultAddress->pincode,
                    'state' => $defaultAddress->state,
                    'address_id' => $defaultAddress->id,
                ];
            }
        }

        return [
            'label' => null,
            'city' => null,
            'pincode' => null,
            'state' => null,
            'address_id' => null,
        ];
    }

    /**
     * Compute real operational state of a restaurant/cloud kitchen.
     *
     * Note: Outside-hours blocking is only enforced when `enforce_operating_hours === true`,
     * while `is_accepting_food_orders === false` or `vacation_mode === true` always pauses new orders.
     */
    public function getRestaurantOperationalStatus(Vendor $vendor): array
    {
        $vendor->loadMissing('settings');
        $settings = $vendor->settings;

        if (!$vendor->isApprovedOrActive()) {
            return [
                'is_open' => false,
                'accepting_orders' => false,
                'status_key' => 'inactive',
                'label' => 'Store Unavailable',
                'reason' => 'This kitchen is currently not active on K71 Food.',
                'hours_text' => ($settings?->opening_time ?? '10:00') . ' - ' . ($settings?->closing_time ?? '23:00'),
            ];
        }

        if ($vendor->vacation_mode) {
            return [
                'is_open' => false,
                'accepting_orders' => false,
                'status_key' => 'paused',
                'label' => 'Paused',
                'reason' => $vendor->vacation_message ?: 'Kitchen is temporarily closed by owner.',
                'hours_text' => ($settings?->opening_time ?? '10:00') . ' - ' . ($settings?->closing_time ?? '23:00'),
            ];
        }

        if ($settings && isset($settings->is_accepting_food_orders) && !$settings->is_accepting_food_orders) {
            return [
                'is_open' => false,
                'accepting_orders' => false,
                'status_key' => 'paused',
                'label' => 'Orders Paused',
                'reason' => $settings->paused_reason ?: 'Kitchen has temporarily paused accepting new orders.',
                'hours_text' => ($settings->opening_time ?? '10:00') . ' - ' . ($settings->closing_time ?? '23:00'),
            ];
        }

        $withinHours = $vendor->isWithinOperatingHours();
        $enforceHours = (bool) ($settings?->enforce_operating_hours ?? false);
        $hoursText = ($settings?->opening_time ?? '10:00') . ' - ' . ($settings?->closing_time ?? '23:00');

        if ($enforceHours && !$withinHours) {
            return [
                'is_open' => false,
                'accepting_orders' => false,
                'status_key' => 'closed',
                'label' => 'Closed Now',
                'reason' => "Kitchen accepts orders during operating hours ({$hoursText}).",
                'hours_text' => $hoursText,
            ];
        }

        if (!$withinHours) {
            return [
                'is_open' => false,
                'accepting_orders' => true,
                'status_key' => 'closed_soft',
                'label' => 'Outside Regular Hours',
                'reason' => "Regular hours: {$hoursText}",
                'hours_text' => $hoursText,
            ];
        }

        return [
            'is_open' => true,
            'accepting_orders' => true,
            'status_key' => 'open',
            'label' => 'Open & Accepting Orders',
            'reason' => null,
            'hours_text' => $hoursText,
        ];
    }

    /**
     * Determine whether a restaurant serves a given city and/or pincode when restrictions are configured.
     */
    public function isServiceableForLocation(Vendor $vendor, ?string $city, ?string $pincode): bool
    {
        $vendor->loadMissing('settings');
        $servicePincodes = $vendor->service_pincodes_list;
        $serviceCities = $vendor->service_cities_list;

        $normPin = preg_replace('/\D/', '', (string) $pincode);
        $normCity = strtolower(trim((string) $city));

        if (!empty($servicePincodes) && $normPin !== '') {
            return in_array($normPin, $servicePincodes, true);
        }

        if (!empty($serviceCities) && $normCity !== '') {
            foreach ($serviceCities as $sc) {
                $scLower = strtolower(trim($sc));
                if ($scLower === $normCity || str_contains($normCity, $scLower) || str_contains($scLower, $normCity)) {
                    return true;
                }
            }
            return false;
        }

        // If restaurant didn't explicitly restrict pincodes/cities, check vendor city when customer explicitly chose a city
        if ($normCity !== '' && !empty($vendor->city) && $vendor->city !== null) {
            $vCity = strtolower(trim((string) $vendor->city));
            return $vCity === $normCity || str_contains($vCity, $normCity) || str_contains($normCity, $vCity);
        }

        return true;
    }

    /**
     * Strictly verify dish availability (active status, flag, stock, and time window if set).
     */
    public function isDishAvailableForOrder(Product $dish): bool
    {
        if ($dish->status !== 'active') {
            return false;
        }

        if ($dish->is_dish_available === false) {
            return false;
        }

        if ($dish->total_stock !== null && (int) $dish->total_stock <= 0) {
            return false;
        }

        if (!empty($dish->available_from) && !empty($dish->available_until)) {
            $now = now()->format('H:i');
            if ($dish->available_until < $dish->available_from) {
                if (!($now >= $dish->available_from || $now <= $dish->available_until)) {
                    return false;
                }
            } elseif ($now < $dish->available_from || $now > $dish->available_until) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate option groups & modifiers strictly server-side and compute authoritative unit price & snapshots.
     *
     * @return array{unit_price_paise: int, addon_paise: int, modifiers_snapshot: array, option_selections_snapshot: array}
     */
    public function validateAndCalculateCustomizations(
        Product $dish,
        ?ProductVariant $variant,
        array $selectedModifierIds = []
    ): array {
        $requestedIds = collect($selectedModifierIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $dish->loadMissing(['optionGroups.modifiers', 'modifiers']);

        // Load requested modifiers strictly scoped to THIS dish
        $selectedModifiers = collect();
        if ($requestedIds->isNotEmpty()) {
            $selectedModifiers = ProductModifier::where('product_id', $dish->id)
                ->whereIn('id', $requestedIds)
                ->get();

            if ($selectedModifiers->count() !== $requestedIds->count()) {
                throw ValidationException::withMessages([
                    'selected_modifiers' => ['One or more selected add-ons do not belong to this dish.'],
                ]);
            }

            $unavailable = $selectedModifiers->first(fn ($m) => $m->is_available === false);
            if ($unavailable) {
                throw ValidationException::withMessages([
                    'selected_modifiers' => ["The add-on '{$unavailable->modifier_name}' is currently out of stock."],
                ]);
            }
        }

        $optionSelectionsSnapshot = [];
        // Enforce structured Option Groups if configured on this dish
        $activeGroups = $dish->optionGroups->where('is_active', true);
        foreach ($activeGroups as $group) {
            $groupModIds = $group->modifiers->pluck('id')->all();
            $pickedInGroup = $selectedModifiers->filter(
                fn ($m) => ((int) $m->option_group_id === (int) $group->id) || in_array($m->id, $groupModIds, true)
            )->values();

            $count = $pickedInGroup->count();
            $min = $group->effectiveMinSelections();
            $max = $group->effectiveMaxSelections();

            if ($group->selection_type === 'single' && $count > 1) {
                throw ValidationException::withMessages([
                    'selected_modifiers' => ["Please select only one option for '{$group->name}'."],
                ]);
            }
            if ($count < $min) {
                throw ValidationException::withMessages([
                    'selected_modifiers' => ["Please select at least {$min} option(s) for '{$group->name}'."],
                ]);
            }
            if ($max !== null && $count > $max) {
                throw ValidationException::withMessages([
                    'selected_modifiers' => ["You can select at most {$max} option(s) for '{$group->name}'."],
                ]);
            }

            if ($pickedInGroup->isNotEmpty()) {
                $optionSelectionsSnapshot[] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'selection_type' => $group->selection_type,
                    'options' => $pickedInGroup->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->modifier_name,
                        'price_delta' => (float) $m->price_delta,
                    ])->values()->all(),
                ];
            }
        }

        // Legacy ungrouped required modifiers safeguard
        if ($activeGroups->isEmpty()) {
            $requiredLegacyGroups = $dish->modifiers->where('is_required', true)->groupBy('group_name');
            foreach ($requiredLegacyGroups as $groupName => $modsInGroup) {
                $picked = $selectedModifiers->whereIn('id', $modsInGroup->pluck('id'))->count();
                if ($picked === 0) {
                    throw ValidationException::withMessages([
                        'selected_modifiers' => ["Please select a required option from '{$groupName}'."],
                    ]);
                }
            }
        }

        // Authoritative Base / Portion price
        $basePricePaise = $variant?->price_override
            ?? ($variant && (float) $variant->price > 0 ? (int) round(((float) $variant->price) * 100) : null)
            ?? (int) $dish->base_price;

        $addonPaise = (int) round(((float) $selectedModifiers->sum('price_delta')) * 100);
        $unitPricePaise = $basePricePaise + $addonPaise;

        $modifiersSnapshot = $selectedModifiers->map(fn ($m) => [
            'id' => $m->id,
            'option_group_id' => $m->option_group_id,
            'name' => $m->modifier_name,
            'group' => $m->optionGroup?->name ?: $m->group_name,
            'price_delta' => (float) $m->price_delta,
        ])->values()->all();

        return [
            'unit_price_paise' => $unitPricePaise,
            'addon_paise' => $addonPaise,
            'modifiers_snapshot' => $modifiersSnapshot,
            'option_selections_snapshot' => $optionSelectionsSnapshot,
        ];
    }

    /**
     * Authoritative food fee calculation (delivery fee, free delivery threshold, packaging fee).
     */
    public function calculateFoodFees(Vendor $vendor, int $subtotalPaise, string $orderType = 'food_delivery'): array
    {
        $vendor->loadMissing('settings');
        $settings = $vendor->settings;

        $packagingFee = (int) ($settings?->food_packaging_fee ?? 0);
        $baseDeliveryFee = ($settings && isset($settings->food_delivery_fee))
            ? (int) $settings->food_delivery_fee
            : 0;
        $freeDeliveryAbove = $settings?->food_free_delivery_above;

        if ($orderType === 'food_takeaway') {
            $deliveryFee = 0;
        } elseif ($freeDeliveryAbove && $subtotalPaise >= (int) $freeDeliveryAbove) {
            $deliveryFee = 0;
        } else {
            $deliveryFee = $baseDeliveryFee;
        }

        return [
            'delivery_fee' => $deliveryFee,
            'packaging_fee' => $packagingFee,
            'base_delivery_fee' => $baseDeliveryFee,
            'free_delivery_above' => $freeDeliveryAbove,
            'min_order_amount' => (int) ($settings?->min_food_order_amount ?? 0),
        ];
    }

    /**
     * Validate allowed food order status transitions while preserving valid direct forward transitions.
     */
    public function canTransitionFoodOrderStatus(string $currentStatus, string $targetStatus): bool
    {
        if ($currentStatus === $targetStatus) {
            return true;
        }

        $allowed = [
            'pending' => ['processing', 'ready_for_pickup', 'shipped', 'delivered', 'cancelled'],
            'processing' => ['ready_for_pickup', 'shipped', 'delivered', 'cancelled'],
            'ready_for_pickup' => ['shipped', 'delivered', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'out_for_delivery' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
            'returned' => [],
            'refunded' => [],
        ];

        return in_array($targetStatus, $allowed[$currentStatus] ?? [], true);
    }

    /**
     * Recompute restaurant real food rating & review count from approved reviews.
     */
    public function refreshRestaurantRatingCache(Vendor $vendor): void
    {
        $vendor->loadMissing('settings');

        $productIds = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'food')
            ->pluck('id');

        $stats = Review::query()
            ->where('is_approved', true)
            ->where(function ($q) use ($vendor, $productIds) {
                $q->where('vendor_id', $vendor->id);
                if ($productIds->isNotEmpty()) {
                    $q->orWhereIn('product_id', $productIds);
                }
            })
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        $count = (int) ($stats->cnt ?? 0);
        $avg = $count > 0 && $stats->avg_rating !== null ? round((float) $stats->avg_rating, 2) : null;

        if ($vendor->settings) {
            $vendor->settings->update([
                'food_avg_rating' => $avg,
                'food_review_count' => $count,
            ]);
        }

        if ($avg !== null) {
            $vendor->forceFill(['rating' => $avg])->saveQuietly();
        }
    }
}

