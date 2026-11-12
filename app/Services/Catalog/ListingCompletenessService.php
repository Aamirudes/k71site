<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Collection;

class ListingCompletenessService
{
    /**
     * Evaluate genuine listing completeness from real DB columns & relations.
     *
     * @return array{score:int,grade:string,badge_class:string,passed:array<int,string>,missing:array<int,string>}
     */
    public function evaluate(Product $product): array
    {
        $passed = [];
        $missing = [];
        $earned = 0;
        $totalWeight = 0;

        $addCheck = static function (bool $condition, int $weight, string $passLabel, string $missLabel) use (&$earned, &$totalWeight, &$passed, &$missing): void {
            $totalWeight += $weight;
            if ($condition) {
                $earned += $weight;
                $passed[] = $passLabel;
            } else {
                $missing[] = $missLabel;
            }
        };

        $addCheck(
            mb_strlen(trim((string) $product->title)) >= 8,
            15,
            'Clear title',
            'Use a descriptive title (8+ chars)'
        );

        $addCheck(
            mb_strlen(trim(strip_tags((string) $product->description))) >= 40,
            15,
            'Detailed description',
            'Add a detailed description (40+ chars)'
        );

        $imagesCount = $product->relationLoaded('images') ? $product->images->count() : $product->images()->count();
        $addCheck(
            $imagesCount >= 1,
            25,
            'Product media',
            'Upload at least 1 product image'
        );

        $addCheck(
            !empty($product->category_id),
            10,
            'Category assigned',
            'Assign a category'
        );

        $addCheck(
            ((int) $product->base_price) > 0,
            15,
            'Valid pricing',
            'Set a valid price > ₹0'
        );

        $vertical = strtolower((string) ($product->vertical ?: 'marketplace'));
        if ($vertical === 'food') {
            $addCheck(
                !empty($product->dietary_label) || !empty($product->custom_fields['dietary_type']),
                10,
                'Dietary tag',
                'Set veg / non-veg dietary tag'
            );
            $addCheck(
                !empty($product->food_menu_category_id) || !empty($product->prep_time_mins),
                10,
                'Menu section / prep time',
                'Assign menu section or prep time'
            );
        } elseif ($vertical === 'services') {
            $gigCount = $product->relationLoaded('gigPackages') ? $product->gigPackages->count() : $product->gigPackages()->count();
            $addCheck(
                $gigCount > 0 || !empty($product->service_config_json),
                20,
                'Service deliverables configured',
                'Configure service packages or delivery window'
            );
        } elseif ($vertical === 'enquiry' || $product->listing_type === 'enquiry') {
            $specs = is_array($product->specifications_json) ? count($product->specifications_json) : 0;
            $addCheck(
                $specs > 0 || !empty($product->custom_fields),
                20,
                'Technical specs provided',
                'Add technical specs or MOQ details'
            );
        } else {
            $variantsCount = $product->relationLoaded('variants') ? $product->variants->count() : $product->variants()->count();
            $addCheck(
                ((int) $product->total_stock) > 0 || $variantsCount > 0,
                20,
                'Inventory / variants ready',
                'Add stock quantity or variants'
            );
        }

        $score = $totalWeight > 0 ? (int) round(($earned / $totalWeight) * 100) : 100;

        if ($score >= 85) {
            $grade = 'Ready';
            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } elseif ($score >= 60) {
            $grade = 'Good';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
        } else {
            $grade = 'Incomplete';
            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
        }

        return [
            'score' => $score,
            'grade' => $grade,
            'badge_class' => $badgeClass,
            'passed' => $passed,
            'missing' => $missing,
        ];
    }

    /**
     * Evaluate a collection/paginator of products keyed by product ID.
     *
     * @param iterable<int, Product> $products
     * @return array<int, array>
     */
    public function evaluateMany(iterable $products): array
    {
        $map = [];
        foreach ($products as $product) {
            if ($product instanceof Product) {
                $map[$product->id] = $this->evaluate($product);
            }
        }
        return $map;
    }
}

