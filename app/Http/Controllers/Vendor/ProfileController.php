<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $vendor = auth()->user()->vendor;
        $presets = \App\Models\StorePreset::where('is_active', true)->get()->groupBy('type');
        return view('vendor.profile', compact('vendor', 'presets'));
    }

    public function update(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:100'],
            'vendor_type' => ['nullable', 'string', 'in:clothing,food,services,used,machinery,electronics,automotive,digital_services,local_services,real_estate,home_living,beauty,groceries,toys_sports,shorts,b2b'],
            'description' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string'],
            'gstin' => ['nullable', 'string', 'size:15'],
            'pickup_address' => ['nullable', 'array'],
            'pickup_address.line_1' => ['nullable', 'string', 'max:200'],
            'pickup_address.line_2' => ['nullable', 'string', 'max:200'],
            'pickup_address.city' => ['nullable', 'string', 'max:100'],
            'pickup_address.state' => ['nullable', 'string', 'max:100'],
            'pickup_address.pincode' => ['nullable', 'string', 'size:6'],
            'pickup_address.contact_name' => ['nullable', 'string', 'max:100'],
            'pickup_address.contact_phone' => ['nullable', 'string', 'max:15'],
            'logo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,webp'],
            'banner' => ['nullable', 'image', 'max:4096', 'mimes:jpeg,png,jpg,webp'],
            'colour_palette' => ['nullable', 'string', 'in:default,linen,emerald,rose,noir,terracotta,sage,midnight,sunset'],
            'layout_preset' => ['nullable', 'string', 'in:classic,boutique,editorial,minimal'],
            'product_preset' => ['nullable', 'string', 'in:gallery,immersive,showcase'],
            'text_preset' => ['nullable', 'string', 'in:default,obsidian,deep_navy,espresso,monochrome'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'custom_badge' => ['nullable', 'string', 'max:50'],
            'announcement_bar' => ['nullable', 'string', 'max:200'],
            'announcement_bar_enabled' => ['nullable', 'boolean'],
            'announcement_emoji' => ['nullable', 'string', 'max:10'],
            'announcement_link' => ['nullable', 'string', 'max:255'],
            'established_year' => ['nullable', 'integer', 'min:1900', 'max:2099'],
            'craft_specialty' => ['nullable', 'string', 'max:100'],
            'craft_highlight_1' => ['nullable', 'string', 'max:100'],
            'craft_highlight_2' => ['nullable', 'string', 'max:100'],
            'craft_highlight_3' => ['nullable', 'string', 'max:100'],
            'show_stats_strip' => ['nullable', 'boolean'],
            'show_whatsapp_button' => ['nullable', 'boolean'],
            'show_spotlight' => ['nullable', 'boolean'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'string', 'max:255'],
            'store_policies' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($request->hasFile('logo')) {
            if ($vendor->logo) {
                Storage::disk('public')->delete($vendor->logo);
                @unlink(public_path('storage/' . $vendor->logo));
            }
            $validated['logo'] = $request->file('logo')->store('vendors/' . $vendor->id, 'public');
            $target = public_path('storage/' . $validated['logo']);
            @mkdir(dirname($target), 0755, true);
            @copy(storage_path('app/public/' . $validated['logo']), $target);
        }

        if ($request->hasFile('banner')) {
            if ($vendor->banner) {
                Storage::disk('public')->delete($vendor->banner);
                @unlink(public_path('storage/' . $vendor->banner));
            }
            $validated['banner'] = $request->file('banner')->store('vendors/' . $vendor->id, 'public');
            $target = public_path('storage/' . $validated['banner']);
            @mkdir(dirname($target), 0755, true);
            @copy(storage_path('app/public/' . $validated['banner']), $target);
        }

        // Merge social links & storefront customizations into social_links json
        $social = is_array($vendor->social_links) ? $vendor->social_links : (json_decode($vendor->social_links, true) ?: []);
        $social['tagline'] = $request->input('tagline');
        $social['custom_badge'] = $request->input('custom_badge');
        $social['announcement_bar'] = $request->input('announcement_bar');
        $social['announcement_bar_enabled'] = $request->has('announcement_bar_enabled');
        $social['announcement_emoji'] = $request->input('announcement_emoji', '✨');
        $social['announcement_link'] = $request->input('announcement_link');
        $social['established_year'] = $request->input('established_year');
        $social['craft_specialty'] = $request->input('craft_specialty');
        $social['craft_highlight_1'] = $request->input('craft_highlight_1');
        $social['craft_highlight_2'] = $request->input('craft_highlight_2');
        $social['craft_highlight_3'] = $request->input('craft_highlight_3');
        $social['show_stats_strip'] = $request->has('show_stats_strip');
        $social['show_whatsapp_button'] = $request->has('show_whatsapp_button');
        $social['show_spotlight'] = $request->has('show_spotlight');
        $social['text_preset'] = $request->input('text_preset', 'obsidian');
        $social['instagram'] = $request->input('instagram');
        $social['whatsapp'] = $request->input('whatsapp');
        $social['website'] = $request->input('website');
        $validated['social_links'] = $social;

        $vendor->update($validated);

        // If valid shop address & pincode provided, register pickup location in Shiprocket
        if (!empty($validated['pickup_address']['line_1']) && !empty($validated['pickup_address']['pincode']) && strlen($validated['pickup_address']['pincode']) === 6) {
            try {
                app(\App\Services\Shipping\ShiprocketService::class)->ensurePickupLocation($vendor);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Shiprocket pickup registration for vendor {$vendor->id} deferred: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Boutique storefront updated successfully.');
    }

    public function previewAjax(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $palette = $request->input('palette', $vendor->colour_palette ?? 'sage');
        $layout = $request->input('layout', $vendor->layout_preset ?? 'classic');
        $productPreset = $request->input('product_preset', $vendor->product_preset ?? 'gallery');
        $textPreset = $request->input('text_preset', 'obsidian');

        return response()->json([
            'status' => 'success',
            'palette' => $palette,
            'layout' => $layout,
            'product_preset' => $productPreset,
            'text_preset' => $textPreset,
            'shop_name' => $vendor->shop_name,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
