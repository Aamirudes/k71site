<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShiprocketService
{
    protected string $baseUrl;
    protected string $email;
    protected string $password;
    protected string $mode;
    protected int $markupPaise;

    public function __construct()
    {
        $this->baseUrl = config('shipping.shiprocket.base_url', 'https://apiv2.shiprocket.in/v1/external');
        $this->email = config('shipping.shiprocket.email', 'crab.hashir@gmail.com');
        $this->password = config('shipping.shiprocket.password', 'UsM&FWf&%DlqMi7GB!2UpMj2Ag#mZUIB');
        $this->mode = config('shipping.mode', 'live');
        $this->markupPaise = config('shipping.platform_markup_paise', 1800);
    }

    /**
     * Get or refresh active JWT Bearer token from Shiprocket.
     */
    public function getToken(): string
    {
        $cacheKey = 'shiprocket_token_' . md5($this->email);

        return Cache::remember($cacheKey, now()->addDays(9), function () {
            $response = Http::post("{$this->baseUrl}/auth/login", [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if (!$response->successful()) {
                Log::error('Shiprocket Login Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to authenticate with Shiprocket API: ' . ($response->json('message') ?? 'Invalid credentials'));
            }

            return $response->json('token');
        });
    }

    /**
     * Fetch all pickup locations configured in Shiprocket.
     */
    public function getPickupLocations(): array
    {
        $token = $this->getToken();
        $response = Http::withToken($token)->get("{$this->baseUrl}/settings/company/pickup");

        if ($response->successful()) {
            return $response->json('data.shipping_address') ?? [];
        }

        return [];
    }

    /**
     * Ensure the vendor's physical shop is registered as an approved pickup location in Shiprocket.
     */
    public function ensurePickupLocation(Vendor $vendor): string
    {
        // 1. Check existing pickup locations
        $existing = $this->getPickupLocations();
        $targetCode = 'BUY_V_' . $vendor->id;

        foreach ($existing as $loc) {
            if (($loc['pickup_location'] ?? '') === $targetCode) {
                return $targetCode;
            }
        }

        // Parse vendor pickup address or fallback
        $rawAddr = $vendor->pickup_address;
        $addr = is_array($rawAddr) ? $rawAddr : (json_decode($rawAddr, true) ?: []);

        $name = !empty($addr['contact_name']) ? $addr['contact_name'] : ($vendor->shop_name ?? $vendor->user->name ?? 'Vendor Boutique');
        $email = $vendor->contact_email ?? $vendor->user->email ?? $this->email;
        $rawPhone = !empty($addr['contact_phone']) ? $addr['contact_phone'] : ($vendor->contact_phone ?? $vendor->user->phone ?? '9549311580');
        $phone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (strlen($phone) > 10) $phone = substr($phone, -10);
        if (strlen($phone) < 10) $phone = '9549311580';

        $addressLine = $addr['line_1'] ?? $addr['address_line1'] ?? $addr['address'] ?? ($vendor->shop_name . ' Street');
        $addressLine2 = $addr['line_2'] ?? $addr['address_line2'] ?? '';
        $city = $addr['city'] ?? 'Ajmer';
        $state = $addr['state'] ?? 'Rajasthan';
        $pincode = $addr['pincode'] ?? $addr['pin_code'] ?? '305801';

        // Register new pickup address with Shiprocket
        $token = $this->getToken();
        $response = Http::withToken($token)->post("{$this->baseUrl}/settings/company/addpickup", [
            'pickup_location' => $targetCode,
            'name' => substr($name, 0, 30),
            'email' => $email,
            'phone' => $phone,
            'address' => substr($addressLine, 0, 80),
            'address_2' => substr($addressLine2, 0, 80),
            'city' => $city,
            'state' => $state,
            'country' => 'India',
            'pin_code' => $pincode,
        ]);

        if ($response->successful() && ($response->json('success') || $response->json('pickup_id'))) {
            Log::info("Created Shiprocket Pickup Location for Vendor {$vendor->id}", ['code' => $targetCode]);
            return $targetCode;
        }

        // If location already exists or was accepted
        if ($response->json('message') && str_contains(strtolower($response->json('message')), 'already exists')) {
            return $targetCode;
        }

        // If creation failed or already exists under Primary, return first available or Primary
        if (!empty($existing)) {
            return $existing[0]['pickup_location'] ?? 'Primary';
        }

        return 'Primary';
    }

    /**
     * Check courier serviceability & wholesale shipping rates.
     */
    public function checkRates(string $pickupPincode, string $deliveryPincode, float $weightKg, bool $isCod = false): array
    {
        if ($this->mode === 'sandbox') {
            $markup = (float) ($this->markupPaise / 100);
            return [
                'success' => true,
                'available_couriers' => [
                    [
                        'courier_company_id' => 101,
                        'courier_name' => 'Delhivery Surface (Sandbox)',
                        'rate' => 42.00,
                        'customer_rate' => round(42.00 + $markup, 2),
                        'estimated_delivery_days' => 3,
                        'etd' => now()->addDays(3)->format('d M'),
                    ],
                    [
                        'courier_company_id' => 102,
                        'courier_name' => 'Blue Dart Express (Sandbox)',
                        'rate' => 58.00,
                        'customer_rate' => round(58.00 + $markup, 2),
                        'estimated_delivery_days' => 2,
                        'etd' => now()->addDays(2)->format('d M'),
                    ],
                ],
                'recommended' => [
                    'courier_company_id' => 101,
                    'courier_name' => 'Delhivery Surface (Sandbox)',
                    'rate' => 42.00,
                    'customer_rate' => round(42.00 + $markup, 2),
                    'estimated_delivery_days' => 3,
                ]
            ];
        }

        $token = $this->getToken();
        $response = Http::withToken($token)->get("{$this->baseUrl}/courier/serviceability/", [
            'pickup_postcode' => $pickupPincode,
            'delivery_postcode' => $deliveryPincode,
            'weight' => max(0.2, $weightKg),
            'cod' => $isCod ? '1' : '0',
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'message' => $response->json('message') ?? 'Pincode not serviceable'];
        }

        $couriers = $response->json('data.available_courier_companies') ?? [];
        $recommended = null;

        $formatted = array_map(function ($c) use (&$recommended) {
            $wholesaleRate = (float) ($c['rate'] ?? 50);
            // Clean round figure: round up to nearest whole rupee (e.g. 159.72 -> 160.00)
            $customerRate = (float) ceil($wholesaleRate + ($this->markupPaise / 100));
            $item = [
                'courier_company_id' => $c['courier_company_id'],
                'courier_name' => $c['courier_name'],
                'rate' => $wholesaleRate,
                'customer_rate' => $customerRate,
                'estimated_delivery_days' => (int) ($c['estimated_delivery_days'] ?? 4),
                'etd' => $c['etd'] ?? now()->addDays(4)->format('d M'),
            ];
            if (!$recommended || $wholesaleRate < $recommended['rate']) {
                $recommended = $item;
            }
            return $item;
        }, $couriers);

        return [
            'success' => true,
            'available_couriers' => $formatted,
            'recommended' => $recommended ?? ($formatted[0] ?? null),
        ];
    }

    /**
     * Create Shipment order, assign courier, generate AWB, schedule pickup & create label.
     */
    public function createAndBookShipment(Order $order, ?int $preferredCourierId = null, array $customPackage = []): Shipment
    {
        // 1. Calculate Package Weight & Dimensions
        $totalWeightGrams = 0;
        foreach ($order->items as $item) {
            $w = $item->variant->product->weight_grams ?? $item->product->weight_grams ?? 450;
            $totalWeightGrams += ($w * $item->quantity);
        }
        $calculatedWeightKg = round(max(0.250, $totalWeightGrams / 1000), 3);

        $weightKg = !empty($customPackage['weight']) ? max(0.1, (float) $customPackage['weight']) : $calculatedWeightKg;
        $length = !empty($customPackage['length']) ? max(1, (int) $customPackage['length']) : 22;
        $breadth = !empty($customPackage['breadth']) ? max(1, (int) $customPackage['breadth']) : 18;
        $height = !empty($customPackage['height']) ? max(1, (int) $customPackage['height']) : 8;

        $customerShippingCollected = (int) ($order->shipping_amount ?? 6000);

        // 2. SANDBOX MODE
        if ($this->mode === 'sandbox') {
            $awb = 'SRTEST' . strtoupper(Str::random(10));
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'vendor_id' => $order->vendor_id,
                'provider' => 'shiprocket_sandbox',
                'shiprocket_order_id' => 'SANDBOX_ORD_' . $order->order_number,
                'shiprocket_shipment_id' => 'SANDBOX_SHP_' . $order->id,
                'awb_code' => $awb,
                'courier_company_id' => $preferredCourierId ?? 101,
                'courier_name' => 'Delhivery Surface (Sandbox)',
                'status' => 'pickup_scheduled',
                'pickup_location' => 'BUY_V_' . $order->vendor_id,
                'pickup_scheduled_at' => now()->addDay(),
                'label_url' => route('vendor.orders.shipping_label', $order),
                'tracking_url' => "https://www.delhivery.com/track/package/{$awb}",
                'package_weight_kg' => $weightKg,
                'actual_cost_paise' => 4200,
                'customer_shipping_paise' => $customerShippingCollected,
                'platform_margin_paise' => max(0, $customerShippingCollected - 4200),
                'metadata' => ['sandbox' => true],
            ]);

            $order->update([
                'status' => 'shipped',
                'shipped_at' => now(),
                'tracking_number' => $awb,
                'courier_name' => 'Delhivery Surface',
                'tracking_url' => "https://www.delhivery.com/track/package/{$awb}",
            ]);

            return $shipment;
        }

        // 3. LIVE SHIPROCKET API WORKFLOW
        $token = $this->getToken();
        $vendor = $order->vendor;
        $pickupLocation = $this->ensurePickupLocation($vendor);

        $shippingAddr = is_array($order->address_snapshot) ? $order->address_snapshot : (json_decode($order->address_snapshot, true) ?: []);
        $fullName = trim($shippingAddr['name'] ?? $order->user->name ?? 'Customer');
        $nameParts = explode(' ', $fullName, 2);
        $customerFirstName = $nameParts[0] ?: 'Customer';
        $customerLastName = $nameParts[1] ?? 'User';

        $customerPhone = preg_replace('/[^0-9]/', '', $shippingAddr['phone'] ?? $order->user->phone ?? '9549311580');
        if (strlen($customerPhone) > 10) $customerPhone = substr($customerPhone, -10);
        if (strlen($customerPhone) < 10) $customerPhone = '9549311580';

        $customerEmail = $shippingAddr['email'] ?? $order->user->email ?? 'order@k71.in';
        $address1 = substr($shippingAddr['line_1'] ?? $shippingAddr['address_line1'] ?? $shippingAddr['address'] ?? 'Main Road', 0, 80);
        $address2 = substr($shippingAddr['line_2'] ?? $shippingAddr['address_line2'] ?? '', 0, 80);
        $city = $shippingAddr['city'] ?? 'Ajmer';
        $state = $shippingAddr['state'] ?? 'Rajasthan';
        $pincode = $shippingAddr['pincode'] ?? $shippingAddr['pin_code'] ?? '305801';

        // Build line items
        $orderItems = [];
        foreach ($order->items as $item) {
            $title = $item->snapshot['product_title'] ?? $item->product->title ?? 'Garment Item';
            $sku = $item->snapshot['sku'] ?? $item->variant->sku ?? ('SKU-' . $item->id);
            $unitPriceRupees = ($item->unit_price ?? 50000) / 100;
            $hsn = (int) ($item->hsn_code ?? $item->product->hsn_code ?? 6204);

            $orderItems[] = [
                'name' => substr($title, 0, 50),
                'sku' => substr($sku, 0, 30),
                'units' => (int) $item->quantity,
                'selling_price' => $unitPriceRupees,
                'discount' => 0,
                'tax' => 0,
                'hsn' => $hsn,
            ];
        }

        $isCod = ($order->payment_method === 'cod');
        $subtotalRupees = ($order->subtotal ?? 100000) / 100;

        // Step A: Create Adhoc Order in Shiprocket
        $createPayload = [
            'order_id' => $order->order_number . '_' . $order->id,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => $pickupLocation,
            'channel_id' => '',
            'comment' => "Order #{$order->order_number} via K71 Marketplace",
            'billing_customer_name' => $customerFirstName,
            'billing_last_name' => $customerLastName,
            'billing_address' => $address1,
            'billing_address_2' => $address2,
            'billing_city' => $city,
            'billing_pincode' => $pincode,
            'billing_state' => $state,
            'billing_country' => 'India',
            'billing_email' => $customerEmail,
            'billing_phone' => $customerPhone,
            'shipping_is_billing' => true,
            'order_items' => $orderItems,
            'payment_method' => $isCod ? 'COD' : 'Prepaid',
            'sub_total' => $subtotalRupees,
            'length' => max(1, $length),
            'breadth' => max(1, $breadth),
            'height' => max(1, $height),
            'weight' => max(0.1, $weightKg),
        ];

        $orderRes = Http::withToken($token)->post("{$this->baseUrl}/orders/create/adhoc", $createPayload);

        if (!$orderRes->successful()) {
            Log::error('Shiprocket Create Order Failed', ['res' => $orderRes->body()]);
            throw new \Exception('Shiprocket order creation error: ' . ($orderRes->json('message') ?? 'Unknown error'));
        }

        $srOrderId = $orderRes->json('order_id');
        $srShipmentId = $orderRes->json('shipment_id');

        // Step B: Auto-assign courier & generate AWB
        $assignPayload = ['shipment_id' => $srShipmentId];
        if ($preferredCourierId) {
            $assignPayload['courier_id'] = $preferredCourierId;
        }

        $awbRes = Http::withToken($token)->post("{$this->baseUrl}/couriers/assign/awb", $assignPayload);

        $awbCode = null;
        $courierName = 'Shiprocket Courier';
        $courierCompanyId = $preferredCourierId;
        $actualCostPaise = 4500;

        if ($awbRes->successful()) {
            $awbData = $awbRes->json('response.data') ?? [];
            $awbCode = $awbData['awb_code'] ?? null;
            $courierName = $awbData['courier_name'] ?? 'Shiprocket Courier';
            $courierCompanyId = $awbData['courier_company_id'] ?? $preferredCourierId;
            $rate = (float) ($awbData['rate'] ?? 45.00);
            $actualCostPaise = (int) ($rate * 100);
        } else {
            // AWB generation might take a few moments; assign fallback
            Log::warning('Shiprocket AWB Assignment Deferred', ['res' => $awbRes->body()]);
            $awbCode = 'SR' . $srShipmentId;
        }

        // Step C: Request Doorstep Pickup
        Http::withToken($token)->post("{$this->baseUrl}/couriers/generate/pickup", [
            'shipment_id' => [$srShipmentId],
        ]);

        // Step D: Generate Shipping Label
        $labelRes = Http::withToken($token)->post("{$this->baseUrl}/couriers/generate/label", [
            'shipment_id' => [$srShipmentId],
        ]);
        $labelUrl = $labelRes->json('label_url') ?? null;

        $trackingUrl = "https://shiprocket.co/tracking/{$awbCode}";

        // Calculate margin
        $marginPaise = max(0, $customerShippingCollected - $actualCostPaise);

        // Step E: Save to Database
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'provider' => 'shiprocket',
            'shiprocket_order_id' => (string) $srOrderId,
            'shiprocket_shipment_id' => (string) $srShipmentId,
            'awb_code' => $awbCode,
            'courier_company_id' => $courierCompanyId,
            'courier_name' => $courierName,
            'status' => 'pickup_scheduled',
            'pickup_location' => $pickupLocation,
            'pickup_scheduled_at' => now()->addDay(),
            'label_url' => $labelUrl,
            'tracking_url' => $trackingUrl,
            'package_weight_kg' => $weightKg,
            'actual_cost_paise' => $actualCostPaise,
            'customer_shipping_paise' => $customerShippingCollected,
            'platform_margin_paise' => $marginPaise,
            'metadata' => [
                'sr_order_id' => $srOrderId,
                'sr_shipment_id' => $srShipmentId,
            ],
        ]);

        // Update Order
        $order->update([
            'status' => 'shipped',
            'tracking_number' => $awbCode,
            'courier_name' => $courierName,
            'tracking_url' => $trackingUrl,
        ]);

        return $shipment;
    }

    /**
     * Cancel an existing shipment order on Shiprocket.
     */
    public function cancelShipment(Shipment $shipment): bool
    {
        if ($this->mode === 'sandbox' || empty($shipment->shiprocket_order_id)) {
            $shipment->update(['status' => 'cancelled']);
            return true;
        }

        try {
            $token = $this->getToken();
            $res = Http::withToken($token)->post("{$this->baseUrl}/orders/cancel", [
                'ids' => [(int) $shipment->shiprocket_order_id],
            ]);

            if ($res->successful()) {
                $shipment->update(['status' => 'cancelled']);
                Log::info("Shiprocket order cancelled successfully for Shipment #{$shipment->id}");
                return true;
            } else {
                Log::warning("Shiprocket cancel order failed: " . $res->body());
            }
        } catch (\Throwable $e) {
            Log::error("Shiprocket cancel shipment exception: " . $e->getMessage());
        }

        $shipment->update(['status' => 'cancelled']);
        return false;
    }

    /**
     * Simulate shipment status transitions for Sandbox & testing.
     */
    public function simulateState(Order $order, string $targetState): Shipment
    {
        $shipment = $order->shipment ?: Shipment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'vendor_id' => $order->vendor_id,
                'provider' => 'simulator',
                'awb_code' => 'SIMULATE_' . strtoupper(Str::random(8)),
                'courier_name' => 'Delhivery Express',
                'status' => 'pickup_scheduled',
                'package_weight_kg' => 0.500,
                'actual_cost_paise' => 4200,
                'customer_shipping_paise' => 6000,
                'platform_margin_paise' => 1800,
            ]
        );

        switch ($targetState) {
            case 'in_transit':
                $shipment->update(['status' => 'in_transit']);
                $order->update(['status' => 'shipped']);
                break;

            case 'out_for_delivery':
                $shipment->update(['status' => 'out_for_delivery']);
                break;

            case 'delivered':
                $shipment->update(['status' => 'delivered']);
                $order->update(['status' => 'delivered']);
                break;

            case 'pickup_scheduled':
            default:
                $shipment->update(['status' => 'pickup_scheduled']);
                $order->update(['status' => 'shipped']);
                break;
        }

        return $shipment;
    }
}
