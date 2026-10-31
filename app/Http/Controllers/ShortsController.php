<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Short;
use App\Models\SpinPrize;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortsController extends Controller
{
    /**
     * Display the main Shorts feed or handle AJAX action.
     */
    public function index(Request $request)
    {
        if ($request->has('action')) {
            return app(ShortsAjaxController::class)->dispatch($request);
        }

        $openId = (int) $request->query('v', 0);
        $openCat = $request->query('cat', '');

        return $this->renderShortsView($openId, $openCat);
    }

    /**
     * Deep-link view for a single short.
     */
    public function show(Request $request, $id)
    {
        return $this->renderShortsView((int) $id, '');
    }

    /**
     * Category view for shorts.
     */
    public function category(Request $request, $slug)
    {
        return $this->renderShortsView(0, (string) $slug);
    }

    /**
     * Render the unified Shorts Blade view with localized ZSU configuration.
     */
    protected function renderShortsView(int $openId = 0, string $openCat = ''): View
    {
        $categories = Category::select('id', 'name', 'slug')->get()->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
        ])->toArray();

        // Spin prizes from database (Decision D8)
        $spinPrizes = SpinPrize::with('coupon')
            ->where('is_active', true)
            ->get()
            ->map(fn($p) => [
                'label' => $p->label,
                'color' => $p->color,
                'type' => $p->type,
                'value' => $p->value,
            ])->toArray();

        // Build ZSU config matching legacy plugin expectations
        $zsuConfig = [
            'ajax_url' => route('shorts.ajax'),
            'nonce' => csrf_token(),
            'logged_in' => auth()->check() ? 1 : 0,
            'user_id' => auth()->id() ?? 0,
            'autoplay' => (int) (Setting::get('zsu_autoplay', 1)),
            'muted_start' => (int) (Setting::get('zsu_muted_start', 1)),
            'loop' => (int) (Setting::get('zsu_loop', 1)),
            'lazy_load' => 1,
            'preload_next' => 1,
            'per_page' => (int) (Setting::get('zsu_videos_per_page', 24)),
            'grid_cols' => (int) (Setting::get('zsu_grid_columns', 2)),
            'show_follow' => 1,
            'show_share' => 1,
            'show_like' => 1,
            'show_featured' => (int) (Setting::get('zsu_show_featured', 1)),
            'show_comments' => 1,
            'particle_bg' => 0,
            'tilt_cards' => 0,
            'confetti_like' => 0,
            'neon_glow' => 0,
            'glassmorphism' => 1,
            'animation_level' => 'minimal',
            'heatmap' => 0,
            'ab_testing' => 0,
            'enable_comments' => 1,
            'enable_ratings' => 0,
            'enable_wishlists' => 1,
            'categories' => $categories,
            'home_url' => url('/'),
            'shorts_url' => route('shorts.index'),
            'open_id' => $openId,
            'open_cat' => $openCat,
            'primary_color' => '#09090b',
            'accent_color' => '#ffffff',
            'spin_wheel_enabled' => (int) (Setting::get('zsu_spin_wheel_enabled', 0)),
            'spin_wheel_prizes' => $spinPrizes,
        ];

        // Fetch SEO short if openId is provided
        $seoShort = $openId > 0 ? Short::with(['vendor', 'category', 'products.images'])->find($openId) : null;

        $featuredShorts = Short::with(['vendor', 'category', 'products.images'])
            ->whereIn('status', ['published', 'approved', 'active'])
            ->where('is_featured', true)
            ->orderByDesc('is_boosted')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $trendingShorts = Short::with(['vendor', 'category', 'products.images'])
            ->whereIn('status', ['published', 'approved', 'active'])
            ->orderByDesc('trending_score')
            ->orderByDesc('views')
            ->take(12)
            ->get();

        $initialQuery = Short::with(['vendor', 'category', 'products.images'])
            ->whereIn('status', ['published', 'approved', 'active']);

        if (!empty($openCat)) {
            $cat = Category::where('slug', $openCat)->first();
            if ($cat) {
                $initialQuery->where('category_id', $cat->id);
            }
        }

        $initialShorts = $initialQuery->orderByDesc('id')->take(24)->get();

        // Spotlight / Hero short
        $heroShort = ($openId > 0 && $seoShort) ? $seoShort : ($featuredShorts->first() ?? $trendingShorts->first() ?? $initialShorts->first());

        // Extract genuine tagged products for direct "Shop What You See" rail
        $allTaggedIds = collect();
        foreach ([$trendingShorts, $featuredShorts, $initialShorts] as $collection) {
            foreach ($collection as $short) {
                $allTaggedIds = $allTaggedIds->merge($short->products->pluck('id'));
            }
        }
        $allTaggedIds = $allTaggedIds->unique()->take(12)->values()->all();

        $shoppableProducts = \App\Models\Product::with(['images', 'vendor', 'category', 'variants'])
            ->whereIn('id', $allTaggedIds)
            ->where('status', 'active')
            ->take(12)
            ->get();

        // Featured boutique creators & stores
        $creators = \App\Models\Vendor::whereHas('shorts', fn($q) => $q->whereIn('status', ['published', 'approved', 'active']))
            ->withCount(['shorts' => fn($q) => $q->whereIn('status', ['published', 'approved', 'active'])])
            ->take(8)
            ->get();

        // Build client-ready shorts array for instant vertical swipe player
        $clientShorts = $initialShorts->map(function ($s) {
            $arr = $s->toZsuArray();
            $arr['products_detail'] = $s->products->map(function ($p) {
                $priceRupees = $p->base_price / 100;
                $compareRupees = $p->compare_at_price ? ($p->compare_at_price / 100) : null;
                $isOnSale = $compareRupees && ($compareRupees > $priceRupees);
                $discountPct = $isOnSale ? (int) round((1 - ($priceRupees / $compareRupees)) * 100) : 0;
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'price' => '₹' . number_format($priceRupees, 0),
                    'price_raw' => $priceRupees,
                    'original_price' => $compareRupees ? '₹' . number_format($compareRupees, 0) : null,
                    'discount_pct' => $discountPct,
                    'image' => $p->primary_image_url,
                    'in_stock' => $p->total_stock > 0,
                    'rating' => (float) ($p->avg_rating ?? 4.8),
                    'reviews_count' => (int) ($p->review_count ?? 18),
                    'url' => route('product.show', $p->slug),
                    'vendor_name' => $p->vendor?->shop_name ?? 'K71 Verified Partner',
                ];
            })->values()->all();
            return $arr;
        })->values()->all();

        return view('shorts.index', [
            'zsuConfig' => $zsuConfig,
            'openId' => $openId,
            'openCat' => $openCat,
            'seoShort' => $seoShort,
            'categories' => $categories,
            'spinPrizes' => $spinPrizes,
            'featuredShorts' => $featuredShorts,
            'trendingShorts' => $trendingShorts,
            'initialShorts' => $initialShorts,
            'heroShort' => $heroShort,
            'shoppableProducts' => $shoppableProducts,
            'creators' => $creators,
            'clientShorts' => $clientShorts,
        ]);
    }
}
