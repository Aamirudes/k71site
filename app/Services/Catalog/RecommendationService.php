<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Monetization\VisibilityService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public const SESSION_RECENTLY_VIEWED_KEY = 'k71_recently_viewed';
    public const MAX_RECENTLY_VIEWED = 12;

    public function __construct(
        protected VisibilityService $visibility
    ) {}

    /**
     * Track a publicly visible product ID in the current visitor's session-only history.
     */
    public function trackRecentlyViewed(Product $product): void
    {
        if (!$product->id || $product->status !== 'active') {
            return;
        }

        if (!$this->visibility->isProductPubliclyVisible($product)) {
            return;
        }

        $ids = collect(session()->get(self::SESSION_RECENTLY_VIEWED_KEY, []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== (int) $product->id)
            ->prepend((int) $product->id)
            ->unique()
            ->take(self::MAX_RECENTLY_VIEWED)
            ->values()
            ->all();

        session()->put(self::SESSION_RECENTLY_VIEWED_KEY, $ids);
    }

    /**
     * Retrieve privacy-safe recently viewed visible products in chronological order (newest first).
     */
    public function getRecentlyViewed(?int $excludeProductId = null, int $limit = 6, ?string $vertical = null): Collection
    {
        $ids = collect(session()->get(self::SESSION_RECENTLY_VIEWED_KEY, []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && ($excludeProductId === null || $id !== $excludeProductId))
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $query = Product::with(['images', 'vendor', 'category'])
            ->whereIn('id', $ids->all())
            ->where('status', 'active');

        if ($vertical !== null) {
            $query->where('vertical', $vertical);
        } else {
            $query->excludeUsed();
        }

        $this->visibility->scopeVisibleProducts($query, $vertical, allowNullVendor: $vertical === 'used');

        $products = $query->get()->keyBy('id');

        return $ids
            ->map(fn (int $id) => $products->get($id))
            ->filter()
            ->take($limit)
            ->values();
    }

    /**
     * Retrieve genuine co-purchased items based on real historical non-cancelled orders.
     */
    public function getFrequentlyBoughtTogether(Product $product, int $limit = 4): Collection
    {
        if (!$product->id) {
            return collect();
        }

        $coProductIds = DB::table('order_items as oi1')
            ->join('order_items as oi2', function ($join) {
                $join->on('oi1.order_id', '=', 'oi2.order_id')
                     ->whereColumn('oi1.product_id', '!=', 'oi2.product_id');
            })
            ->join('orders', 'orders.id', '=', 'oi1.order_id')
            ->where('oi1.product_id', $product->id)
            ->whereNotIn('orders.status', ['cancelled', 'refunded', 'failed'])
            ->selectRaw('oi2.product_id, COUNT(*) as pair_count')
            ->groupBy('oi2.product_id')
            ->orderByDesc('pair_count')
            ->limit($limit * 2)
            ->pluck('oi2.product_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if (empty($coProductIds)) {
            return collect();
        }

        $query = Product::with(['images', 'vendor', 'category'])
            ->whereIn('id', $coProductIds)
            ->where('status', 'active')
            ->excludeUsed();

        $this->visibility->scopeVisibleProducts($query);

        $map = $query->get()->keyBy('id');

        return collect($coProductIds)
            ->map(fn (int $id) => $map->get($id))
            ->filter()
            ->take($limit)
            ->values();
    }

    /**
     * Retrieve similar / complementary visible products based on category hierarchy and vertical.
     */
    public function getRelatedProducts(Product $product, int $limit = 8): Collection
    {
        $collected = collect();
        $excludeIds = [(int) $product->id];

        // 1. Same category (plus parent / sibling categories)
        if ($product->category_id) {
            $categoryIds = [$product->category_id];
            $cat = $product->relationLoaded('category') ? $product->category : Category::find($product->category_id);
            if ($cat && $cat->parent_id) {
                $siblingIds = Category::where('parent_id', $cat->parent_id)->pluck('id')->all();
                $categoryIds = array_unique(array_merge($categoryIds, [$cat->parent_id], $siblingIds));
            } elseif ($cat) {
                $childIds = Category::where('parent_id', $cat->id)->pluck('id')->all();
                $categoryIds = array_unique(array_merge($categoryIds, $childIds));
            }

            $q = Product::with(['images', 'vendor', 'category'])
                ->whereNotIn('id', $excludeIds)
                ->whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->excludeUsed();

            if ($product->vertical) {
                $q->where('vertical', $product->vertical);
            }

            $this->visibility->scopeVisibleProducts($q);

            $sameCat = $q->orderByDesc('is_featured')
                ->orderByDesc('order_count')
                ->orderByDesc('avg_rating')
                ->take($limit)
                ->get();

            $collected = $collected->merge($sameCat);
            $excludeIds = array_merge($excludeIds, $sameCat->pluck('id')->all());
        }

        // 2. Fallback to popular items in the same vertical if not full
        if ($collected->count() < $limit) {
            $remaining = $limit - $collected->count();
            $fallback = $this->getPopularFallback($remaining, $excludeIds, $product->vertical ?: null);
            $collected = $collected->merge($fallback);
        }

        return $collected->unique('id')->take($limit)->values();
    }

    /**
     * Genuine popular visible products fallback (sorted by real orders/sales/views).
     */
    public function getPopularFallback(int $limit = 8, array $excludeIds = [], ?string $vertical = null): Collection
    {
        $query = Product::with(['images', 'vendor', 'category'])
            ->where('status', 'active')
            ->excludeUsed();

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        if ($vertical) {
            $query->where('vertical', $vertical);
        }

        $this->visibility->scopeVisibleProducts($query, $vertical);

        return $query->orderByDesc('order_count')
            ->orderByDesc('sales_count')
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->take($limit)
            ->get();
    }
}

