<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\FoodMenuCategory;
use App\Models\Product;
use App\Models\VendorSetting;
use App\Services\Food\FoodMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FoodMenuController extends Controller
{
    public function index(FoodMarketplaceService $foodService)
    {
        $vendor = Auth::user()->vendor()->with('settings')->firstOrFail();

        $menuCategories = FoodMenuCategory::where('vendor_id', $vendor->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with([
                'products' => function ($q) {
                    $q->withoutGlobalScopes()
                        ->where('vertical', 'food')
                        ->with(['images', 'variants', 'optionGroups.modifiers', 'category'])
                        ->orderBy('menu_sort_order')
                        ->orderBy('id');
                },
            ])
            ->get();

        $uncategorizedDishes = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'food')
            ->whereNull('food_menu_category_id')
            ->with(['images', 'variants', 'optionGroups.modifiers', 'category'])
            ->orderBy('menu_sort_order')
            ->latest()
            ->get();

        $allDishesCount = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'food')
            ->count();

        $availableDishesCount = Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('vertical', 'food')
            ->where('status', 'active')
            ->where('is_dish_available', true)
            ->count();

        $operationalStatus = $foodService->getRestaurantOperationalStatus($vendor);
        $settings = $vendor->settings ?? VendorSetting::firstOrCreate(['vendor_id' => $vendor->id]);

        return view('vendor.food.menu', compact(
            'vendor',
            'settings',
            'menuCategories',
            'uncategorizedDishes',
            'allDishesCount',
            'availableDishesCount',
            'operationalStatus'
        ));
    }

    public function storeCategory(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $authCheck = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($vendor, 'food');
        if (!$authCheck['allowed']) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $authCheck['reason']], 403)
                : back()->with('error', $authCheck['reason']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'menu-section';
        $slug = $baseSlug;
        $counter = 1;
        while (FoodMenuCategory::where('vendor_id', $vendor->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $maxOrder = (int) FoodMenuCategory::where('vendor_id', $vendor->id)->max('sort_order');

        $category = FoodMenuCategory::create([
            'vendor_id' => $vendor->id,
            'name' => trim($validated['name']),
            'slug' => $slug,
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category], 201);
        }

        return back()->with('success', "Menu category '{$category->name}' added.");
    }

    public function updateCategory(Request $request, FoodMenuCategory $category)
    {
        $vendor = Auth::user()->vendor;
        if ((int) $category->vendor_id !== (int) $vendor->id) {
            abort(403, 'Unauthorized menu category.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $category->is_active,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category->fresh()]);
        }

        return back()->with('success', "Menu category '{$category->name}' updated.");
    }

    public function destroyCategory(Request $request, FoodMenuCategory $category)
    {
        $vendor = Auth::user()->vendor;
        if ((int) $category->vendor_id !== (int) $vendor->id) {
            abort(403, 'Unauthorized menu category.');
        }

        Product::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->where('food_menu_category_id', $category->id)
            ->update(['food_menu_category_id' => null]);

        $name = $category->name;
        $category->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Menu category '{$name}' deleted. Dishes remain in uncategorized.");
    }

    public function reorderCategories(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $validated = $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'integer',
        ]);

        foreach ($validated['order'] as $index => $categoryId) {
            FoodMenuCategory::where('vendor_id', $vendor->id)
                ->where('id', (int) $categoryId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleAcceptingOrders(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $authCheck = app(\App\Services\Monetization\EntitlementService::class)->authorizeSectorWrite($vendor, 'food', 'allow_food_orders');
        if (!$authCheck['allowed']) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $authCheck['reason']], 403)
                : back()->with('error', $authCheck['reason']);
        }
        $settings = VendorSetting::firstOrCreate(['vendor_id' => $vendor->id]);

        $validated = $request->validate([
            'is_accepting_food_orders' => 'nullable|boolean',
            'paused_reason' => 'nullable|string|max:255',
        ]);

        $newState = array_key_exists('is_accepting_food_orders', $validated) && $validated['is_accepting_food_orders'] !== null
            ? (bool) $validated['is_accepting_food_orders']
            : !((bool) ($settings->is_accepting_food_orders ?? true));

        $settings->update([
            'is_accepting_food_orders' => $newState,
            'paused_reason' => $newState ? null : ($validated['paused_reason'] ?? $settings->paused_reason ?: 'Kitchen temporarily paused accepting new orders.'),
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_accepting_food_orders' => $newState,
                'paused_reason' => $settings->paused_reason,
            ]);
        }

        return back()->with('success', $newState
            ? 'Kitchen is now LIVE and accepting customer orders!'
            : 'Kitchen orders paused. Customers cannot add new dishes until resumed.');
    }
}

