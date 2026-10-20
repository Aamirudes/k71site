<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    /**
     * Get dynamic attributes, vertical_type, and layout blueprints for a given category.
     * Accessible via GET /api/vendor/category-attributes?category_id={id}
     * or GET /api/vendor/category-attributes.php?category_id={id}
     */
    public function show(Request $request)
    {
        $categoryId = $request->input('category_id');
        
        $category = null;
        if ($categoryId) {
            $category = Category::find($categoryId);
        }

        if (!$category) {
            // Fallback to first active category or standard_retail
            $category = Category::where('is_active', true)->first();
        }

        $verticalType = $category?->vertical_type ?: 'standard_retail';

        // Load blueprint attributes for this category or parent category or fallback by vertical_type
        $attributes = CategoryAttribute::where('category_id', $category?->id)
            ->orderBy('sort_order')
            ->get();

        if ($attributes->isEmpty() && $category?->parent_id) {
            $attributes = CategoryAttribute::where('category_id', $category->parent_id)
                ->orderBy('sort_order')
                ->get();
        }

        // If still empty, load any attributes matching the category's vertical_type
        if ($attributes->isEmpty()) {
            $matchingCatId = Category::where('vertical_type', $verticalType)
                ->whereHas('attributes')
                ->value('id');

            if ($matchingCatId) {
                $attributes = CategoryAttribute::where('category_id', $matchingCatId)
                    ->orderBy('sort_order')
                    ->get();
            }
        }

        $driverAttributes = $attributes->where('is_variant_driver', true)->values();
        $specAttributes = $attributes->where('is_variant_driver', false)->values();

        // Preset defaults for specific vertical types
        $defaultPackages = [
            [
                'tier' => 'basic',
                'title' => 'Basic Essential',
                'description' => 'Core deliverable, single concept with 1 revision.',
                'delivery_days' => 2,
                'revisions' => 1,
                'price' => 499.00,
                'features' => [
                    'source_file' => false,
                    'high_resolution' => true,
                    'commercial_rights' => false,
                ]
            ],
            [
                'tier' => 'standard',
                'title' => 'Standard Professional',
                'description' => 'Complete package with editable source files and commercial license.',
                'delivery_days' => 4,
                'revisions' => 3,
                'price' => 1499.00,
                'features' => [
                    'source_file' => true,
                    'high_resolution' => true,
                    'commercial_rights' => true,
                ]
            ],
            [
                'tier' => 'premium',
                'title' => 'Premium Enterprise',
                'description' => 'VIP priority delivery, unlimited revisions, full source and copyright buyout.',
                'delivery_days' => 7,
                'revisions' => 9,
                'price' => 3499.00,
                'features' => [
                    'source_file' => true,
                    'high_resolution' => true,
                    'commercial_rights' => true,
                    'vip_support' => true,
                ]
            ],
        ];

        $defaultModifiers = [
            ['group_name' => 'Add-ons', 'modifier_name' => 'Extra Bhatura / Roti', 'price_delta' => 35.00, 'is_required' => false],
            ['group_name' => 'Add-ons', 'modifier_name' => 'Special Chutney / Dip', 'price_delta' => 25.00, 'is_required' => false],
            ['group_name' => 'Add-ons', 'modifier_name' => 'Sweet Lassi (300ml)', 'price_delta' => 40.00, 'is_required' => false],
            ['group_name' => 'Add-ons', 'modifier_name' => 'Gulab Jamun (2 Pcs)', 'price_delta' => 50.00, 'is_required' => false],
        ];

        return response()->json([
            'success' => true,
            'category_id' => $category?->id,
            'category_name' => $category?->name,
            'vertical_type' => $verticalType,
            'vertical' => $category?->vertical ?? 'marketplace',
            'attributes' => $attributes,
            'driver_attributes' => $driverAttributes,
            'specification_attributes' => $specAttributes,
            'default_packages' => $defaultPackages,
            'default_modifiers' => $defaultModifiers,
        ]);
    }
}
