<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $vendor = \Illuminate\Support\Facades\Auth::user()->vendor;
        $coupons = Coupon::where('vendor_id', $vendor->id)->latest()->paginate(15);
        return view('vendor.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('vendor.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:coupons,code',
            'type' => 'required|in:percentage,flat',
            'value' => 'required|numeric|min:1',
            'max_discount' => 'nullable|numeric|min:1',
            'min_cart_value' => 'nullable|numeric|min:1',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'total_limit' => 'nullable|integer|min:1',
            'per_customer_limit' => 'required|integer|min:1',
            'first_order_only' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $vendor = \Illuminate\Support\Facades\Auth::user()->vendor;
        
        $coupon = new Coupon($validated);
        $coupon->vendor_id = $vendor->id;
        
        // Convert to cents for flat types
        if ($coupon->type === 'flat') {
            $coupon->value = $validated['value'] * 100;
        }
        
        if ($coupon->max_discount) $coupon->max_discount *= 100;
        if ($coupon->min_cart_value) $coupon->min_cart_value *= 100;
        
        $coupon->first_order_only = $request->has('first_order_only');
        $coupon->is_active = $request->has('is_active');
        $coupon->save();

        return redirect()->route('vendor.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        $vendor = \Illuminate\Support\Facades\Auth::user()->vendor;
        if ($coupon->vendor_id !== $vendor->id) {
            abort(403);
        }
        return view('vendor.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $vendor = \Illuminate\Support\Facades\Auth::user()->vendor;
        if ($coupon->vendor_id !== $vendor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percentage,flat',
            'value' => 'required|numeric|min:1',
            'max_discount' => 'nullable|numeric|min:1',
            'min_cart_value' => 'nullable|numeric|min:1',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'total_limit' => 'nullable|integer|min:1',
            'per_customer_limit' => 'required|integer|min:1',
        ]);
        
        // Convert to cents for flat types
        if ($validated['type'] === 'flat') {
            $validated['value'] *= 100;
        }
        if (!empty($validated['max_discount'])) $validated['max_discount'] *= 100;
        if (!empty($validated['min_cart_value'])) $validated['min_cart_value'] *= 100;
        
        $validated['first_order_only'] = $request->has('first_order_only');
        $validated['is_active'] = $request->has('is_active');

        $coupon->update($validated);

        return redirect()->route('vendor.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $vendor = \Illuminate\Support\Facades\Auth::user()->vendor;
        if ($coupon->vendor_id !== $vendor->id) {
            abort(403);
        }
        
        $coupon->delete();
        return redirect()->route('vendor.coupons.index')->with('success', 'Coupon deleted successfully.');
    }
}
