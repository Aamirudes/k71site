<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'addresses' => $addresses,
            ]);
        }

        return view('account.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'recipient_name' => $request->input('recipient_name', $request->input('full_name')),
            'address_line_1' => $request->input('address_line_1', $request->input('line_1')),
            'address_line_2' => $request->input('address_line_2', $request->input('line_2')),
        ]);

        $validated = $request->validate([
            'label' => 'required|string|max:30',
            'recipient_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'regex:/^[0-9+ -]{10,15}$/'],
            'address_line_1' => 'required|string|max:200',
            'address_line_2' => 'nullable|string|max:200',
            'landmark' => 'nullable|string|max:200',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'pincode' => ['required', 'string', 'regex:/^[1-9][0-9]{5}$/'],
            'is_default' => 'nullable|boolean',
        ]);

        $userId = Auth::id();
        $hasExisting = Address::where('user_id', $userId)->exists();
        $makeDefault = $request->boolean('is_default') || !$hasExisting;

        $address = DB::transaction(function () use ($validated, $userId, $makeDefault) {
            if ($makeDefault) {
                Address::where('user_id', $userId)->update(['is_default' => false]);
            }

            return Address::create([
                'user_id' => $userId,
                'label' => trim($validated['label']),
                'full_name' => trim($validated['recipient_name']),
                'phone' => trim($validated['phone']),
                'line_1' => trim($validated['address_line_1']),
                'line_2' => !empty($validated['address_line_2']) ? trim($validated['address_line_2']) : null,
                'landmark' => !empty($validated['landmark']) ? trim($validated['landmark']) : null,
                'city' => trim($validated['city']),
                'state' => trim($validated['state']),
                'pincode' => trim($validated['pincode']),
                'is_default' => $makeDefault,
            ]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Delivery address saved.',
                'address' => $address,
            ], 201);
        }

        return back()->with('success', 'Delivery address saved.');
    }

    public function update(Request $request, Address $address)
    {
        if ((int) $address->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized address access.');
        }

        $request->merge([
            'recipient_name' => $request->input('recipient_name', $request->input('full_name')),
            'address_line_1' => $request->input('address_line_1', $request->input('line_1')),
            'address_line_2' => $request->input('address_line_2', $request->input('line_2')),
        ]);

        $validated = $request->validate([
            'label' => 'required|string|max:30',
            'recipient_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'regex:/^[0-9+ -]{10,15}$/'],
            'address_line_1' => 'required|string|max:200',
            'address_line_2' => 'nullable|string|max:200',
            'landmark' => 'nullable|string|max:200',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'pincode' => ['required', 'string', 'regex:/^[1-9][0-9]{5}$/'],
            'is_default' => 'nullable|boolean',
        ]);

        $makeDefault = $request->boolean('is_default');

        DB::transaction(function () use ($address, $validated, $makeDefault) {
            if ($makeDefault) {
                Address::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                'label' => trim($validated['label']),
                'full_name' => trim($validated['recipient_name']),
                'phone' => trim($validated['phone']),
                'line_1' => trim($validated['address_line_1']),
                'line_2' => !empty($validated['address_line_2']) ? trim($validated['address_line_2']) : null,
                'landmark' => !empty($validated['landmark']) ? trim($validated['landmark']) : null,
                'city' => trim($validated['city']),
                'state' => trim($validated['state']),
                'pincode' => trim($validated['pincode']),
                'is_default' => $makeDefault ? true : $address->is_default,
            ]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated.',
                'address' => $address->fresh(),
            ]);
        }

        return back()->with('success', 'Delivery address updated.');
    }

    public function setDefault(Request $request, Address $address)
    {
        if ((int) $address->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized address access.');
        }

        DB::transaction(function () use ($address) {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Default delivery address updated.',
                'address' => $address->fresh(),
            ]);
        }

        return back()->with('success', 'Default delivery address updated.');
    }

    public function destroy(Request $request, Address $address)
    {
        if ((int) $address->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized address access.');
        }

        DB::transaction(function () use ($address) {
            $wasDefault = (bool) $address->is_default;
            $userId = $address->user_id;
            $address->delete();

            if ($wasDefault) {
                $next = Address::where('user_id', $userId)->latest()->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Delivery address removed.',
            ]);
        }

        return back()->with('success', 'Delivery address removed.');
    }
}
