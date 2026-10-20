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
            'opening_time' => ['nullable', 'string', 'max:10'],
            'closing_time' => ['nullable', 'string', 'max:10'],
            'avg_prep_time_mins' => ['nullable', 'integer', 'min:5', 'max:180'],
            'food_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'min_food_order_amount' => ['nullable', 'numeric', 'min:0'],
            'food_delivery_mode' => ['nullable', 'string', 'max:50'],
            'cuisine_types' => ['nullable', 'string'],
            // Services specific settings
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:200'],
            'service_experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'service_working_hours' => ['nullable', 'string', 'max:100'],
        ]);

        if ($vendor->isFood()) {
            $validated['is_restaurant'] = 1;
            $validated['is_pure_veg'] = $request->boolean('is_pure_veg');
            if ($request->filled('food_delivery_fee')) {
                $validated['food_delivery_fee'] = (int) ($request->input('food_delivery_fee') * 100);
            }
            if ($request->filled('min_food_order_amount')) {
                $validated['min_food_order_amount'] = (int) ($request->input('min_food_order_amount') * 100);
            }
            if ($request->filled('cuisine_types')) {
                $validated['cuisine_types'] = array_map('trim', explode(',', $request->input('cuisine_types')));
            }
            $validated['shipping_type'] = 'flat';
            $validated['processing_days'] = 1;
            $validated['return_window_days'] = 0;
        }

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

        if ($request->hasFile('upi_qr_image')) {
            if ($settings->upi_qr_image) Storage::disk('public')->delete($settings->upi_qr_image);
            
            $filename = $request->file('upi_qr_image')->store('vendors/' . $vendor->id, 'public');
            $validated['upi_qr_image'] = $filename;
        }

        $validated['shipping_flat_rate'] = (isset($validated['shipping_flat_rate']) && $validated['shipping_flat_rate'] !== null && $validated['shipping_flat_rate'] !== '') ? (int) ($validated['shipping_flat_rate'] * 100) : 5000;
        $validated['shipping_free_above'] = (isset($validated['shipping_free_above']) && $validated['shipping_free_above'] !== null && $validated['shipping_free_above'] !== '') ? (int) ($validated['shipping_free_above'] * 100) : null;
        $validated['allow_customer_cancellation'] = $request->has('allow_customer_cancellation');
        $validated['cancellation_window_hours'] = $request->input('cancellation_window_hours', 24);

        if ($request->filled('serviceable_pincodes')) {
            $pincodes = array_filter(array_map('trim', explode(',', $request->input('serviceable_pincodes'))));
            $validated['serviceable_pincodes'] = !empty($pincodes) ? json_encode(array_values($pincodes)) : null;
        } else {
            $validated['serviceable_pincodes'] = null;
        }

        if ($settings->upi_id !== $validated['upi_id'] && !empty($validated['upi_id'])) {
            $validated['upi_id_previous'] = $settings->upi_id;
            $validated['upi_id_changed_at'] = now();
        }

        unset($validated['payment_mode'], $validated['pickup_address']);
        $settings->update($validated);

        return back()->with('success', 'Operations & shipping pickup address updated successfully.');
    }
}
