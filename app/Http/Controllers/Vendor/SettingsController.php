<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        $vendor = auth()->user()->vendor;
        $settings = VendorSetting::firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['shipping_type' => 'flat', 'shipping_flat_rate' => 5000, 'processing_days' => 3, 'return_window_days' => 7]
        );
        return view('vendor.settings', compact('settings', 'vendor'));
    }

    public function update(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $settings = VendorSetting::firstOrCreate(['vendor_id' => $vendor->id]);

        $validated = $request->validate([
            'shipping_type' => ['nullable', 'in:flat,free_above,weight_based'],
            'shipping_flat_rate' => ['nullable', 'numeric', 'min:0'],
            'shipping_free_above' => ['nullable', 'numeric', 'min:0'],
            'processing_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'return_window_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'serviceable_pincodes' => ['nullable', 'string'],
            'pickup_address' => ['nullable', 'array'],
            'pickup_address.line_1' => ['nullable', 'string', 'max:200'],
            'pickup_address.line_2' => ['nullable', 'string', 'max:200'],
            'pickup_address.city' => ['nullable', 'string', 'max:100'],
            'pickup_address.state' => ['nullable', 'string', 'max:100'],
            'pickup_address.pincode' => ['nullable', 'string', 'size:6'],
            'pickup_address.contact_name' => ['nullable', 'string', 'max:100'],
            'pickup_address.contact_phone' => ['nullable', 'string', 'max:15'],
            'payment_mode' => ['nullable', 'in:both,upi_only,cod_only'],
            'upi_enabled' => ['nullable', 'boolean'],
            'cod_enabled' => ['nullable', 'boolean'],
            'allow_customer_cancellation' => ['boolean'],
            'cancellation_window_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'upi_payee_name' => ['nullable', 'string', 'max:100'],
            'upi_qr_image' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'payout_method' => ['nullable', 'in:upi,bank'],
            // Food specific settings
            'is_pure_veg' => ['nullable', 'boolean'],
            'is_accepting_food_orders' => ['nullable', 'boolean'],
            'paused_reason' => ['nullable', 'string', 'max:255'],
            'enforce_operating_hours' => ['nullable', 'boolean'],
            'weekly_hours_json' => ['nullable', 'array'],
            'service_cities' => ['nullable', 'string', 'max:500'],
            'opening_time' => ['nullable', 'string', 'max:10'],
            'closing_time' => ['nullable', 'string', 'max:10'],
            'avg_prep_time_mins' => ['nullable', 'integer', 'min:5', 'max:180'],
            'food_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'food_packaging_fee' => ['nullable', 'numeric', 'min:0'],
            'food_free_delivery_above' => ['nullable', 'numeric', 'min:0'],
            'min_food_order_amount' => ['nullable', 'numeric', 'min:0'],
            'food_delivery_mode' => ['nullable', 'in:both,delivery,takeaway'],
            'cuisine_types' => ['nullable', 'string'],
            'fssai_license_number' => ['nullable', 'string', 'max:30'],
            'food_tax_percent' => ['nullable', 'numeric', 'min:0', 'max:28'],
            'food_settings_submitted' => ['nullable', 'boolean'],
            // Services specific settings
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:200'],
            'service_experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'service_working_hours' => ['nullable', 'string', 'max:100'],
        ]);

        if ($vendor->isFood()) {
            $validated['is_restaurant'] = 1;
            $validated['is_pure_veg'] = $request->has('food_settings_submitted')
                ? $request->boolean('is_pure_veg')
                : ($request->has('is_pure_veg') ? $request->boolean('is_pure_veg') : (bool) ($settings->is_pure_veg ?? false));

            if ($request->has('food_settings_submitted') || $request->has('is_accepting_food_orders')) {
                $accepting = $request->boolean('is_accepting_food_orders');
                $validated['is_accepting_food_orders'] = $accepting;
                if ($accepting) {
                    $validated['paused_reason'] = null;
                }
            }

            if ($request->has('food_settings_submitted') || $request->has('enforce_operating_hours')) {
                $validated['enforce_operating_hours'] = $request->boolean('enforce_operating_hours');
            }

            if ($request->has('food_delivery_fee')) {
                $rawDelFee = $request->input('food_delivery_fee');
                $validated['food_delivery_fee'] = ($rawDelFee !== null && $rawDelFee !== '')
                    ? (int) round(((float) $rawDelFee) * 100)
                    : 0;
            }
            if ($request->has('food_packaging_fee')) {
                $rawPack = $request->input('food_packaging_fee');
                $validated['food_packaging_fee'] = ($rawPack !== null && $rawPack !== '') ? (int) round(((float) $rawPack) * 100) : 0;
            }
            if ($request->has('food_free_delivery_above')) {
                $rawFree = $request->input('food_free_delivery_above');
                $validated['food_free_delivery_above'] = ($rawFree !== null && $rawFree !== '' && (float) $rawFree > 0)
                    ? (int) round(((float) $rawFree) * 100)
                    : null;
            }
            if ($request->has('min_food_order_amount')) {
                $rawMin = $request->input('min_food_order_amount');
                $validated['min_food_order_amount'] = ($rawMin !== null && $rawMin !== '' && (float) $rawMin > 0)
                    ? (int) round(((float) $rawMin) * 100)
                    : 0;
            }
            if ($request->filled('cuisine_types')) {
                $validated['cuisine_types'] = array_values(array_filter(array_map('trim', explode(',', $request->input('cuisine_types')))));
            }
            if ($request->has('service_cities')) {
                $citiesRaw = trim((string) $request->input('service_cities', ''));
                $validated['service_cities_json'] = $citiesRaw !== ''
                    ? array_values(array_filter(array_map('trim', explode(',', $citiesRaw))))
                    : null;
            }
            if ($request->has('fssai_license_number')) {
                $newFssai = trim((string) $request->input('fssai_license_number', '')) ?: null;
                $validated['fssai_license_number'] = $newFssai;
                if ($newFssai !== $settings->fssai_license_number) {
                    // Require fresh admin review whenever FSSAI number changes
                    $validated['fssai_verified'] = false;
                    $validated['fssai_verified_at'] = null;
                }
            }
            $validated['shipping_type'] = 'flat';
            $validated['processing_days'] = 1;
            $validated['return_window_days'] = 0;
        }
        unset($validated['service_cities'], $validated['food_settings_submitted']);

        // Resolve vendor payment choice (UPI, COD, or Both)
        if ($request->filled('payment_mode')) {
            $mode = $request->input('payment_mode');
            $upiEnabled = in_array($mode, ['both', 'upi_only']);
            $codEnabled = in_array($mode, ['both', 'cod_only']);
        } else {
            $upiEnabled = $request->has('upi_enabled') ? $request->boolean('upi_enabled') : (bool) ($settings->upi_enabled ?? true);
            $codEnabled = $request->has('cod_enabled') ? $request->boolean('cod_enabled') : (bool) ($settings->cod_enabled ?? false);
        }

        // Server-side enforcement: At least one payment method must remain active
        if (!$upiEnabled && !$codEnabled) {
            return back()
                ->withInput()
                ->withErrors(['payment_mode' => 'At least one payment method (UPI or Cash on Delivery) must remain enabled for customer checkout.']);
        }

        $validated['upi_enabled'] = $upiEnabled;
        $validated['cod_enabled'] = $codEnabled;

        if ($request->has('pickup_address')) {
            $pickupData = $request->input('pickup_address');
            $vendor->update(['pickup_address' => $pickupData]);

            // If valid shop address & pincode provided, register pickup location in Shiprocket
            if (!empty($pickupData['line_1']) && !empty($pickupData['pincode']) && strlen($pickupData['pincode']) === 6) {
                try {
                    app(\App\Services\Shipping\ShiprocketService::class)->ensurePickupLocation($vendor);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Shiprocket pickup registration for vendor {$vendor->id} deferred: " . $e->getMessage());
                }
            }
        }

        $oldUpiQr = $settings->upi_qr_image;
        $newUpiQr = null;

        try {
            if ($request->hasFile('upi_qr_image')) {
                $newUpiQr = $request->file('upi_qr_image')->store('vendors/' . $vendor->id, 'public');
                k71_sync_public_storage($newUpiQr);
                $validated['upi_qr_image'] = $newUpiQr;
            }

            if (!empty($validated['bank_ifsc'])) {
                $validated['bank_ifsc'] = strtoupper(trim((string) $validated['bank_ifsc']));
            }
            if (array_key_exists('upi_id', $validated) && $validated['upi_id'] !== null) {
                $validated['upi_id'] = trim((string) $validated['upi_id']) ?: null;
            }

            $validated['shipping_flat_rate'] = (isset($validated['shipping_flat_rate']) && $validated['shipping_flat_rate'] !== null && $validated['shipping_flat_rate'] !== '') ? (int) ($validated['shipping_flat_rate'] * 100) : 5000;
            $validated['shipping_free_above'] = (isset($validated['shipping_free_above']) && $validated['shipping_free_above'] !== null && $validated['shipping_free_above'] !== '') ? (int) ($validated['shipping_free_above'] * 100) : null;
            $validated['allow_customer_cancellation'] = $request->has('allow_customer_cancellation');
            $validated['cancellation_window_hours'] = $request->input('cancellation_window_hours', 24);

            if ($request->filled('serviceable_pincodes')) {
                $rawPincodes = $request->input('serviceable_pincodes');
                $decoded = is_string($rawPincodes) && str_starts_with(trim($rawPincodes), '[')
                    ? json_decode($rawPincodes, true)
                    : explode(',', (string) $rawPincodes);
                $pincodes = array_values(array_unique(array_filter(array_map(
                    static fn ($p) => preg_replace('/\s+/', '', (string) $p),
                    is_array($decoded) ? $decoded : []
                ), static fn ($p) => $p !== '')));
                $validated['serviceable_pincodes'] = !empty($pincodes) ? json_encode($pincodes) : null;
            } else {
                $validated['serviceable_pincodes'] = null;
            }

            $newUpiId = $validated['upi_id'] ?? null;
            if (!empty($newUpiId) && $settings->upi_id !== $newUpiId) {
                $validated['upi_id_previous'] = $settings->upi_id;
                $validated['upi_id_changed_at'] = now();
            }

            unset($validated['payment_mode'], $validated['pickup_address']);
            $settings->update($validated);
        } catch (\Throwable $e) {
            if ($newUpiQr) {
                k71_delete_public_storage($newUpiQr);
            }
            throw $e;
        }

        if ($newUpiQr && $oldUpiQr && $oldUpiQr !== $newUpiQr) {
            k71_delete_public_storage($oldUpiQr);
        }
        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Operations & shipping pickup address updated successfully.');
    }
}
