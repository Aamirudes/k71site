<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Models\ShortComment;
use App\Models\ShortEvent;
use App\Models\ShortFollow;
use App\Models\ShortShare;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShortsAjaxController extends Controller
{
    /**
     * Universal dispatcher compatible with WP AJAX actions (`zsu_*`).
     */
    public function dispatch(Request $request): JsonResponse
    {
        $action = $request->input('action', $request->query('action', ''));

        return match ($action) {
            'zsu_get_shorts' => $this->getShorts($request),
            'zsu_get_featured' => $this->getFeatured($request),
            'zsu_get_cat_rail' => $this->getCatRail($request),
            'zsu_record_view' => $this->recordView($request),
            'zsu_record_like' => $this->recordLike($request),
            'zsu_record_share' => $this->recordShare($request),
            'zsu_record_watch_pct' => $this->recordWatchPct($request),
            'zsu_get_products' => $this->getProducts($request),
            'zsu_get_short_data' => $this->getShortData($request),
            'zsu_follow_vendor' => $this->followVendor($request),
            'zsu_unfollow_vendor' => $this->unfollowVendor($request),
            'zsu_check_follow' => $this->checkFollow($request),
            'zsu_get_comments' => $this->getComments($request),
            'zsu_post_comment' => $this->postComment($request),
            'zsu_wishlist_toggle' => $this->wishlistToggle($request),
            'zsu_search_shorts' => $this->searchShorts($request),
            'zsu_home_spin_check' => app(ShortsSpinController::class)->check($request),
            'zsu_home_spin_result' => app(ShortsSpinController::class)->result($request),
            default => response()->json(['success' => false, 'message' => "Unknown action '{$action}'"], 400),
        };
    }

    /**
     * Get paginated shorts feed with filtering.
     */
    public function getShorts(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, (int) $request->input('per_page', 24));
        $cat = $request->input('cat', '');
        $filter = $request->input('filter', 'latest');
        $vendorId = (int) $request->input('vendor_id', 0);
        $search = $request->input('search', '');
        $exclude = (array) $request->input('exclude', []);

        $query = Short::with(['vendor', 'category'])
            ->whereIn('status', ['published', 'approved', 'active']);

        if (!empty($exclude)) {
            $query->whereNotIn('id', array_filter(array_map('intval', $exclude)));
        }

        if ($vendorId > 0) {
            $query->where('vendor_id', $vendorId);
        }

        if (!empty($cat)) {
            $category = Category::where('slug', $cat)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordering based on filter
        match ($filter) {
            'trending' => $query->orderByDesc('trending_score')->orderByDesc('id'),
            'popular' => $query->orderByDesc('views')->orderByDesc('id'),
            'featured' => $query->where('is_featured', true)->orderByDesc('id'),
            'boosted' => $query->orderByDesc('is_boosted')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $shorts = [];

        foreach ($paginator->items() as $short) {
            $shorts[] = $short->toZsuArray();
        }

        // Promote boosted / manual-boosted shorts on page 1 for non-latest feeds
        if ($page === 1 && $filter !== 'latest' && $filter !== 'featured') {
            $promoted = Short::with(['vendor', 'category'])
                ->whereIn('status', ['published', 'approved', 'active'])
                ->where(function ($q) {
                    $q->where('is_manual_boost', true)
                      ->orWhere('is_boosted', true);
                })
                ->orderByDesc('is_manual_boost')
                ->orderByDesc('id')
                ->take(3)
                ->get();

            if ($promoted->isNotEmpty()) {
                $promotedArray = [];
                $promotedIds = [];
                foreach ($promoted as $p) {
                    $arr = $p->toZsuArray();
                    $arr['is_promoted'] = true;
                    $promotedArray[] = $arr;
                    $promotedIds[] = $p->id;

                    // Increment impressions on active boosts
                    ShortBoost::where('short_id', $p->id)
                        ->where('status', 'active')
                        ->increment('impressions');
                }

                // Filter out duplicates and merge
                $shorts = array_values(array_filter($shorts, fn($s) => !in_array($s['id'], $promotedIds, true)));
                $shorts = array_merge($promotedArray, $shorts);
            }
        }

        return response()->json([
            'success' => true,
            'shorts' => $shorts,
            'total' => $paginator->total(),
            'pages' => $paginator->lastPage(),
            'page' => $page,
        ]);
    }

    /**
     * Get featured / boosted shorts for the top featured rail.
     */
    public function getFeatured(Request $request): JsonResponse
    {
        $count = (int) $request->input('count', 8);

        $shorts = Short::with(['vendor', 'category'])
            ->whereIn('status', ['published', 'approved', 'active'])
            ->where(function ($q) {
                $q->where('is_boosted', true)
                  ->orWhere('is_featured', true);
            })
            ->orderByDesc('is_boosted')
            ->orderByDesc('id')
            ->take($count)
            ->get()
            ->map(fn(Short $s) => $s->toZsuArray());

        return response()->json([
            'success' => true,
            'shorts' => $shorts,
        ]);
    }

    /**
     * Get category-specific rail shorts.
     */
    public function getCatRail(Request $request): JsonResponse
    {
        $catSlug = $request->input('cat', '');
        $limit = (int) $request->input('limit', 10);

        $query = Short::with(['vendor', 'category'])
            ->whereIn('status', ['published', 'approved', 'active']);

        if ($catSlug) {
            $cat = Category::where('slug', $catSlug)->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        $shorts = $query->orderByDesc('views')
            ->take($limit)
            ->get()
            ->map(fn(Short $s) => $s->toZsuArray());

        return response()->json([
            'success' => true,
            'shorts' => $shorts,
        ]);
    }

    /**
     * Record a video view.
     */
    public function recordView(Request $request): JsonResponse
    {
        $shortId = (int) $request->input('short_id', 0);
        $short = Short::find($shortId);

        if (!$short) {
            return response()->json(['success' => false, 'message' => 'Short not found'], 404);
        }

        $short->increment('views');

        ShortEvent::create([
            'short_id' => $short->id,
            'event_type' => 'view',
            'user_id' => auth()->id() ?? 0,
            'session_id' => session()->getId() ?: '',
            'device' => $this->detectDevice($request),
            'browser' => $this->detectBrowser($request),
            'country' => 'IN',
            'referrer' => Str::limit($request->header('referer', ''), 500),
            'ip_hash' => hash('sha256', $request->ip() ?: '127.0.0.1'),
            'watch_pct' => (int) $request->input('watch_pct', 0),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'recorded' => true,
            'views' => Short::formatNumber($short->views),
            'raw' => $short->views,
        ]);
    }

    /**
     * Record or toggle a like on a short.
     */
    public function recordLike(Request $request): JsonResponse
    {
        $shortId = (int) $request->input('short_id', 0);
        $short = Short::find($shortId);

        if (!$short) {
            return response()->json(['success' => false, 'message' => 'Short not found'], 404);
        }

        $userId = auth()->id();
        $sessionKey = "liked_short_{$shortId}";
        $isLiked = false;

        if ($userId) {
            $existing = ShortEvent::where('short_id', $shortId)
                ->where('user_id', $userId)
                ->where('event_type', 'like')
                ->first();
            $isLiked = (bool) $existing;
        } else {
            $isLiked = session()->has($sessionKey);
        }

        if ($isLiked) {
            // Unlike
            if ($userId) {
                ShortEvent::where('short_id', $shortId)
                    ->where('user_id', $userId)
                    ->where('event_type', 'like')
                    ->delete();
            } else {
                session()->forget($sessionKey);
            }

            $short->likes = max(0, $short->likes - 1);
            $short->save();

            return response()->json([
                'success' => true,
                'action' => 'unliked',
                'likes' => Short::formatNumber($short->likes),
                'raw' => $short->likes,
            ]);
        }

        // Like
        if ($userId) {
            ShortEvent::create([
                'short_id' => $short->id,
                'event_type' => 'like',
                'user_id' => $userId,
                'session_id' => session()->getId() ?: '',
                'device' => $this->detectDevice($request),
                'browser' => $this->detectBrowser($request),
                'ip_hash' => hash('sha256', $request->ip() ?: '127.0.0.1'),
                'created_at' => now(),
            ]);
        } else {
            session()->put($sessionKey, true);
        }

        $short->increment('likes');

        return response()->json([
            'success' => true,
            'action' => 'liked',
            'likes' => Short::formatNumber($short->likes),
            'raw' => $short->likes,
        ]);
    }

    /**
     * Record a video share.
     */
    public function recordShare(Request $request): JsonResponse
    {
        $shortId = (int) $request->input('short_id', 0);
        $platform = $request->input('platform', 'generic');
        $short = Short::find($shortId);

        if (!$short) {
            return response()->json(['success' => false, 'message' => 'Short not found'], 404);
        }

        $short->increment('shares');

        ShortShare::create([
            'short_id' => $short->id,
            'platform' => $platform,
            'user_id' => auth()->id() ?? 0,
            'session_id' => session()->getId() ?: '',
            'created_at' => now(),
        ]);

        ShortEvent::create([
            'short_id' => $short->id,
            'event_type' => 'share',
            'user_id' => auth()->id() ?? 0,
            'session_id' => session()->getId() ?: '',
            'device' => $this->detectDevice($request),
            'browser' => $this->detectBrowser($request),
            'ip_hash' => hash('sha256', $request->ip() ?: '127.0.0.1'),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'shares' => Short::formatNumber($short->shares),
            'platform' => $platform,
        ]);
    }

    /**
     * Record watch percentage for analytics.
     */
    public function recordWatchPct(Request $request): JsonResponse
    {
        $shortId = (int) $request->input('short_id', 0);
        $pct = (int) $request->input('pct', 0);

        if ($shortId > 0 && $pct > 0) {
            ShortEvent::create([
                'short_id' => $shortId,
                'event_type' => 'watch_pct',
                'user_id' => auth()->id() ?? 0,
                'session_id' => session()->getId() ?: '',
                'watch_pct' => min(100, $pct),
                'device' => $this->detectDevice($request),
                'browser' => $this->detectBrowser($request),
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get tagged products for shoppable bottom tray.
     */
    public function getProducts(Request $request): JsonResponse
    {
        $rawIds = $request->input('ids', []);
        $shortId = (int) $request->input('short_id', 0);

        if (is_string($rawIds)) {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        } else {
            $ids = array_filter(array_map('intval', (array) $rawIds));
        }

        if (empty($ids) && $shortId > 0) {
            $short = Short::find($shortId);
            if ($short) {
                $ids = $short->products()->pluck('products.id')->toArray();
            }
        }

        if (empty($ids)) {
            return response()->json(['success' => true, 'products' => []]);
        }

        $products = Product::with(['images', 'vendor'])
            ->whereIn('id', array_slice($ids, 0, 8))
            ->where('status', 'active')
            ->get();

        $trayItems = [];
        foreach ($products as $p) {
            $priceRupees = $p->base_price / 100;
            $compareRupees = $p->compare_at_price ? ($p->compare_at_price / 100) : null;
            $isOnSale = $compareRupees && ($compareRupees > $priceRupees);
            $discountPct = $isOnSale ? (int) round((1 - ($priceRupees / $compareRupees)) * 100) : 0;

            $imageUrl = $p->primary_image_url;

            $trayItems[] = [
                'id' => $p->id,
                'name' => $p->title,
                'price' => '₹' . number_format($priceRupees, 0),
                'price_raw' => $priceRupees,
                'sale_price' => $isOnSale ? $priceRupees : 0,
                'regular_price' => $compareRupees ?: $priceRupees,
                'on_sale' => $isOnSale,
                'discount_pct' => $discountPct,
                'image' => $imageUrl,
                'gallery' => $p->images->map(fn($img) => $img->url)->toArray(),
                'url' => route('product.show', $p->slug),
                'add_to_cart' => route('cart.add', ['product' => $p->id]),
                'in_stock' => $p->total_stock > 0,
                'stock_qty' => $p->total_stock,
                'rating' => (float) ($p->avg_rating ?? 5.0),
                'review_count' => (int) ($p->review_count ?? 0),
                'sku' => $p->sku ?? '',
                'vendor_name' => $p->vendor?->shop_name ?? '',
                'badge' => $discountPct > 0 ? "-{$discountPct}%" : ($p->is_featured ? 'Featured' : ''),
            ];
        }

        // Record click event on short if requested
        if ($shortId > 0) {
            Short::where('id', $shortId)->increment('clicks');
            ShortEvent::create([
                'short_id' => $shortId,
                'event_type' => 'click',
                'user_id' => auth()->id() ?? 0,
                'session_id' => session()->getId() ?: '',
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'products' => $trayItems]);
    }

    /**
     * Get single short data payload.
     */
    public function getShortData(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', $request->input('short_id', 0));
        $short = Short::with(['vendor', 'category'])->find($id);

        if (!$short) {
            return response()->json(['success' => false, 'message' => 'Short not found'], 404);
        }

        return response()->json(['success' => true, 'short' => $short->toZsuArray()]);
    }

    /**
     * Follow vendor from reel action bar.
     */
    public function followVendor(Request $request): JsonResponse
    {
        $vendorId = (int) $request->input('vendor_id', 0);
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Please login to follow creators.'], 401);
        }

        ShortFollow::firstOrCreate([
            'follower_id' => $userId,
            'vendor_id' => $vendorId,
        ]);

        return response()->json(['success' => true, 'followed' => true]);
    }

    /**
     * Unfollow vendor.
     */
    public function unfollowVendor(Request $request): JsonResponse
    {
        $vendorId = (int) $request->input('vendor_id', 0);
        $userId = auth()->id();

        if ($userId && $vendorId) {
            ShortFollow::where('follower_id', $userId)
                ->where('vendor_id', $vendorId)
                ->delete();
        }

        return response()->json(['success' => true, 'followed' => false]);
    }

    /**
     * Check if user follows a vendor.
     */
    public function checkFollow(Request $request): JsonResponse
    {
        $vendorId = (int) $request->input('vendor_id', 0);
        $userId = auth()->id();

        $isFollowed = false;
        if ($userId && $vendorId) {
            $isFollowed = ShortFollow::where('follower_id', $userId)
                ->where('vendor_id', $vendorId)
                ->exists();
        }

        return response()->json(['success' => true, 'followed' => $isFollowed]);
    }

    /**
     * Get comments for a short.
     */
    public function getComments(Request $request): JsonResponse
    {
        $shortId = (int) $request->input('short_id', 0);
        $comments = ShortComment::with('user')
            ->where('short_id', $shortId)
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->take(50)
            ->get()
            ->map(fn(ShortComment $c) => [
                'id' => $c->id,
                'author' => $c->user?->name ?? 'Shopper',
                'avatar' => asset('images/default-avatar.png'),
                'content' => $c->content,
                'likes' => $c->likes,
                'created_at' => $c->created_at?->diffForHumans() ?? 'Just now',
            ]);

        return response()->json(['success' => true, 'comments' => $comments]);
    }

    /**
     * Post comment on a short.
     */
    public function postComment(Request $request): JsonResponse
    {
        $shortId = (int) $request->input('short_id', 0);
        $content = trim($request->input('content', ''));
        $short = Short::find($shortId);

        if (!$short || empty($content)) {
            return response()->json(['success' => false, 'message' => 'Invalid content'], 422);
        }

        $userId = auth()->id() ?? 1; // Fallback to guest user ID

        $comment = ShortComment::create([
            'short_id' => $shortId,
            'user_id' => $userId,
            'content' => strip_tags($content),
            'status' => 'approved',
            'likes' => 0,
        ]);

        $short->increment('comments_count');

        ShortEvent::create([
            'short_id' => $shortId,
            'event_type' => 'comment',
            'user_id' => $userId,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'author' => auth()->user()?->name ?? 'Shopper',
                'avatar' => asset('images/default-avatar.png'),
                'content' => $comment->content,
                'likes' => 0,
                'created_at' => 'Just now',
            ],
            'count' => $short->comments_count,
        ]);
    }

    /**
     * Wishlist toggle on a product from reel.
     */
    public function wishlistToggle(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id', 0);
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Please login to save to wishlist.'], 401);
        }

        // Toggle user wishlist
        $exists = DB::table('wishlists')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($exists) {
            DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();
            return response()->json(['success' => true, 'saved' => false]);
        }

        DB::table('wishlists')->insert([
            'user_id' => $userId,
            'product_id' => $productId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'saved' => true]);
    }

    /**
     * Search shorts by keyword.
     */
    public function searchShorts(Request $request): JsonResponse
    {
        $term = trim($request->input('search', ''));
        if (strlen($term) < 2) {
            return response()->json(['success' => true, 'shorts' => []]);
        }

        $shorts = Short::with(['vendor', 'category'])
            ->whereIn('status', ['published', 'approved', 'active'])
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            })
            ->take(12)
            ->get()
            ->map(fn(Short $s) => $s->toZsuArray());

        return response()->json(['success' => true, 'shorts' => $shorts]);
    }

    protected function detectDevice(Request $request): string
    {
        $ua = $request->header('User-Agent', '');
        if (preg_match('/Mobile|Android|iPhone/i', $ua)) {
            return 'mobile';
        }
        if (preg_match('/Tablet|iPad/i', $ua)) {
            return 'tablet';
        }
        return 'desktop';
    }

    protected function detectBrowser(Request $request): string
    {
        $ua = $request->header('User-Agent', '');
        if (str_contains($ua, 'Chrome')) return 'chrome';
        if (str_contains($ua, 'Firefox')) return 'firefox';
        if (str_contains($ua, 'Safari')) return 'safari';
        if (str_contains($ua, 'Edge')) return 'edge';
        return 'browser';
    }
}
