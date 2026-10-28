<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Models\ShortCoin;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShortsStudioController extends Controller
{
    /**
     * Get the authenticated vendor model.
     */
    protected function getVendor(): ?Vendor
    {
        $user = auth()->user();
        if (!$user) return null;
        return $user->vendor ?? Vendor::where('user_id', $user->id)->first();
    }

    /**
     * Display the Vendor Shorts Studio dashboard.
     */
    public function index(): View
    {
        $vendor = $this->getVendor();
        $user = auth()->user();

        // Calculate coin balance from ledger
        $coinBalance = 0;
        if ($user) {
            $latestCoin = ShortCoin::where('user_id', $user->id)->orderByDesc('id')->first();
            $coinBalance = $latestCoin?->balance ?? 100; // Seed 100 starter coins for testing/vendor onboarding
        }

        $categories = Category::select('id', 'name', 'slug')->get()->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
        ])->toArray();

        $stats = [
            'total_shorts' => $vendor ? $vendor->shorts()->count() : 0,
            'total_views' => $vendor ? $vendor->shorts()->sum('views') : 0,
            'total_likes' => $vendor ? $vendor->shorts()->sum('likes') : 0,
            'total_shares' => $vendor ? $vendor->shorts()->sum('shares') : 0,
            'coins_balance' => $coinBalance,
        ];

        $studioConfig = [
            'ajax_url' => route('vendor.shorts.ajax'),
            'nonce' => csrf_token(),
            'vendor_id' => $vendor?->id ?? 0,
            'vendor_name' => $vendor?->shop_name ?? $user?->name ?? 'Vendor',
            'coins' => $coinBalance,
            'upload_count' => $stats['total_shorts'],
            'max_uploads' => 100,
            'max_mb' => 100,
            'categories' => $categories,
            'auto_approve' => 1,
            'boost_enabled' => 1,
            'boost_prices' => [
                'standard' => 199,
                'prime' => 499,
                'elite' => 999,
            ],
            'shorts_url' => route('shorts.index'),
        ];

        return view('vendor.shorts.studio', [
            'vendor' => $vendor,
            'stats' => $stats,
            'categories' => $categories,
            'studioConfig' => $studioConfig,
        ]);
    }

    /**
     * Dispatcher for Vendor Studio AJAX actions.
     */
    public function ajax(Request $request): JsonResponse
    {
        $action = $request->input('action', '');

        return match ($action) {
            'zsu_upload_short' => $this->upload($request),
            'zsu_save_short' => $this->save($request),
            'zsu_vendor_get_shorts' => $this->getShorts($request),
            'zsu_vendor_delete_short' => $this->deleteShort($request),
            'zsu_vendor_analytics' => $this->analytics($request),
            'zsu_request_boost' => $this->requestBoost($request),
            'zsu_vendor_get_boosts' => $this->getBoosts($request),
            'zsu_get_coins_balance' => $this->coinsBalance($request),
            'zsu_coins_history' => $this->coinsHistory($request),
            'zsu_vendor_search_products' => $this->searchProducts($request),
            default => response()->json(['success' => false, 'message' => "Unknown action '{$action}'"], 400),
        };
    }

    /**
     * Handle video file upload.
     */
    public function upload(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found. Please log in as a vendor.'], 403);
        }

        if (!$request->hasFile('video')) {
            $err = 'No video file was received. Please check if file exceeds maximum size or retry.';
            if (isset($_FILES['video']['error'])) {
                $code = $_FILES['video']['error'];
                $err = match($code) {
                    UPLOAD_ERR_INI_SIZE => 'Video file exceeds the server upload_max_filesize limit.',
                    UPLOAD_ERR_FORM_SIZE => 'Video file exceeds the form maximum file size.',
                    UPLOAD_ERR_PARTIAL => 'The uploaded video file was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write video file to disk.',
                    default => 'Upload error code: ' . $code,
                };
            }
            return response()->json(['success' => false, 'message' => $err], 422);
        }

        $file = $request->file('video');
        if (!$file->isValid()) {
            return response()->json(['success' => false, 'message' => 'Uploaded file is not valid: ' . $file->getErrorMessage()], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'mp4');
        $allowedExts = ['mp4', 'webm', 'mov', 'quicktime', 'm4v', 'mkv', 'avi', '3gp'];
        if (!in_array($ext, $allowedExts)) {
            return response()->json([
                'success' => false,
                'message' => "Unsupported video format (.{$ext}). Please upload an MP4, WebM, or MOV video reel."
            ], 422);
        }

        if ($file->getSize() > 200 * 1024 * 1024) { // 200MB limit
            return response()->json([
                'success' => false,
                'message' => 'Video size exceeds 200MB. Please trim or compress your reel before uploading.'
            ], 422);
        }

        $filename = 'short_' . time() . '_' . Str::random(8) . '.' . $ext;
        $path = $file->storeAs('shorts/videos', $filename, 'public');

        // Ensure public copy exists for instant direct access on Hostinger
        $publicDir = public_path('storage/shorts/videos');
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }
        try {
            $destFile = $publicDir . '/' . $filename;
            @copy(storage_path('app/public/' . $path), $destFile);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'video_path' => $path,
            'filename' => $filename,
        ]);
    }

    /**
     * Save new or edited short.
     */
    public function save(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $shortId = (int) $request->input('short_id', 0);
        $title = trim($request->input('title', ''));
        $description = trim($request->input('description', ''));
        $videoUrl = $request->input('video_url', '');
        $videoPath = $request->input('video_path', '');
        $catId = (int) $request->input('category_id', 0);
        $ratio = $request->input('ratio', '9:16');
        $thumbDataUri = $request->input('thumb_datauri', '');
        $thumbUrl = $request->input('thumb_url', '');
        $rawProductIds = $request->input('product_ids', []);

        if (empty($title) || empty($videoUrl)) {
            return response()->json(['success' => false, 'message' => 'Title and video URL are required.'], 422);
        }

        // Process base64 frame thumbnail if provided
        if (!empty($thumbDataUri) && str_starts_with($thumbDataUri, 'data:image')) {
            $thumbUrl = $this->saveBase64Thumbnail($thumbDataUri);
        }

        $slug = Str::slug($title) . '-' . Str::random(5);

        if ($shortId > 0) {
            $short = Short::where('vendor_id', $vendor->id)->findOrFail($shortId);
            $short->update([
                'title' => $title,
                'description' => $description,
                'category_id' => $catId ?: null,
                'ratio' => $ratio,
                'thumb_url' => $thumbUrl ?: $short->thumb_url,
                'video_url' => $videoUrl,
                'video_path' => $videoPath ?: $short->video_path,
            ]);
        } else {
            $short = Short::create([
                'vendor_id' => $vendor->id,
                'category_id' => $catId ?: null,
                'title' => $title,
                'slug' => $slug,
                'description' => $description,
                'video_url' => $videoUrl,
                'video_path' => $videoPath,
                'thumb_url' => $thumbUrl ?: Short::placeholderThumb(rand(1, 99), $title),
                'ratio' => $ratio,
                'duration' => (int) $request->input('duration', 30),
                'status' => 'published',
            ]);
        }

        // Sync tagged products
        if (is_string($rawProductIds)) {
            $productIds = array_filter(array_map('intval', explode(',', $rawProductIds)));
        } else {
            $productIds = array_filter(array_map('intval', (array) $rawProductIds));
        }

        if (!empty($productIds)) {
            $syncData = [];
            foreach ($productIds as $idx => $pId) {
                $syncData[$pId] = ['sort_order' => $idx];
            }
            $short->products()->sync($syncData);
        } else {
            $short->products()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Short saved and published successfully!',
            'short_id' => $short->id,
            'short' => $short->toZsuArray(),
        ]);
    }

    /**
     * Get vendor's uploaded shorts list.
     */
    public function getShorts(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return response()->json(['success' => false, 'shorts' => []]);
        }

        $shorts = $vendor->shorts()
            ->with('products')
            ->orderByDesc('id')
            ->get()
            ->map(fn(Short $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'video_url' => $s->video_url,
                'thumb_url' => $s->thumb_url ?: Short::placeholderThumb($s->id, $s->title),
                'views' => Short::formatNumber($s->views),
                'views_raw' => $s->views,
                'likes' => Short::formatNumber($s->likes),
                'shares' => Short::formatNumber($s->shares),
                'is_boosted' => $s->is_boosted,
                'boost_type' => $s->boost_type,
                'status' => $s->status,
                'created_at' => $s->created_at?->format('M j, Y') ?? '',
                'tagged_products_count' => $s->products->count(),
            ]);

        return response()->json(['success' => true, 'shorts' => $shorts]);
    }

    /**
     * Delete vendor's short.
     */
    public function deleteShort(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        $shortId = (int) $request->input('short_id', 0);

        if (!$vendor || !$shortId) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $short = Short::where('vendor_id', $vendor->id)->find($shortId);
        if ($short) {
            // Delete video and thumbnail if local
            if ($short->video_path) {
                Storage::disk('public')->delete($short->video_path);
            }
            $short->products()->detach();
            $short->delete();
        }

        return response()->json(['success' => true, 'message' => 'Short deleted successfully.']);
    }

    /**
     * Get vendor analytics data.
     */
    public function analytics(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        if (!$vendor) {
            return response()->json(['success' => false, 'stats' => []]);
        }

        $shorts = $vendor->shorts;
        $totalViews = $shorts->sum('views');
        $totalLikes = $shorts->sum('likes');
        $totalShares = $shorts->sum('shares');
        $totalClicks = $shorts->sum('clicks');

        return response()->json([
            'success' => true,
            'stats' => [
                'total_views' => $totalViews,
                'total_likes' => $totalLikes,
                'total_shares' => $totalShares,
                'total_clicks' => $totalClicks,
                'total_shorts' => $shorts->count(),
                'engagement_rate' => $totalViews > 0 ? round((($totalLikes + $totalShares + $totalClicks) / $totalViews) * 100, 1) . '%' : '0%',
            ],
        ]);
    }

    /**
     * Request boost for a short using coins.
     */
    public function requestBoost(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        $user = auth()->user();
        $shortId = (int) $request->input('short_id', 0);
        $tier = $request->input('tier', 'standard'); // standard, prime, elite

        $tierCosts = [
            'standard' => ['coins' => 199, 'days' => 1],
            'prime' => ['coins' => 499, 'days' => 3],
            'elite' => ['coins' => 999, 'days' => 7],
        ];

        $selectedTier = $tierCosts[$tier] ?? $tierCosts['standard'];
        $coinsRequired = $selectedTier['coins'];
        $durationDays = $selectedTier['days'];

        $short = Short::where('vendor_id', $vendor->id)->find($shortId);
        if (!$short) {
            return response()->json(['success' => false, 'message' => 'Short not found.'], 404);
        }

        // Check user coin balance
        $latestCoin = ShortCoin::where('user_id', $user->id)->orderByDesc('id')->first();
        $currentBalance = $latestCoin?->balance ?? 100; // Starter coins

        if ($currentBalance < $coinsRequired) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient coin balance. You need {$coinsRequired} coins for {$tier} boost, but have {$currentBalance}.",
            ], 422);
        }

        DB::transaction(function () use ($user, $vendor, $short, $tier, $coinsRequired, $durationDays, $currentBalance) {
            $newBalance = $currentBalance - $coinsRequired;

            // Deduct coins in ledger
            ShortCoin::create([
                'user_id' => $user->id,
                'delta' => -$coinsRequired,
                'balance' => $newBalance,
                'reason' => "Short Boost: {$tier} tier for Short #{$short->id}",
                'ref_id' => $short->id,
            ]);

            // Create or update boost entry
            ShortBoost::create([
                'short_id' => $short->id,
                'vendor_id' => $vendor->id,
                'boost_type' => $tier,
                'coins_spent' => $coinsRequired,
                'starts_at' => now(),
                'ends_at' => now()->addDays($durationDays),
                'status' => 'active',
                'impressions' => 0,
                'clicks' => 0,
            ]);

            // Update short flags
            $short->update([
                'is_boosted' => true,
                'boost_type' => $tier,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Boost activated successfully for {$durationDays} day(s)!",
            'is_boosted' => true,
            'boost_type' => $tier,
        ]);
    }

    /**
     * Get active/past boosts for vendor.
     */
    public function getBoosts(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        if (!$vendor) return response()->json(['success' => true, 'boosts' => []]);

        $boosts = ShortBoost::with('short')
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn(ShortBoost $b) => [
                'id' => $b->id,
                'short_title' => $b->short?->title ?? 'Short #' . $b->short_id,
                'boost_type' => ucfirst($b->boost_type),
                'coins_spent' => $b->coins_spent,
                'status' => $b->status,
                'impressions' => $b->impressions,
                'clicks' => $b->clicks,
                'starts_at' => $b->starts_at?->format('M j, Y H:i') ?? '',
                'ends_at' => $b->ends_at?->format('M j, Y H:i') ?? '',
                'is_active' => $b->status === 'active' && $b->ends_at?->isFuture(),
            ]);

        return response()->json(['success' => true, 'boosts' => $boosts]);
    }

    /**
     * Get current coin balance.
     */
    public function coinsBalance(Request $request): JsonResponse
    {
        $user = auth()->user();
        $latest = ShortCoin::where('user_id', $user->id)->orderByDesc('id')->first();
        $balance = $latest?->balance ?? 100;

        return response()->json(['success' => true, 'balance' => $balance]);
    }

    /**
     * Get coins history ledger.
     */
    public function coinsHistory(Request $request): JsonResponse
    {
        $user = auth()->user();
        $history = ShortCoin::where('user_id', $user->id)
            ->orderByDesc('id')
            ->take(50)
            ->get()
            ->map(fn(ShortCoin $c) => [
                'id' => $c->id,
                'delta' => $c->delta > 0 ? "+{$c->delta}" : (string) $c->delta,
                'balance' => $c->balance,
                'reason' => $c->reason,
                'created_at' => $c->created_at?->format('M j, Y H:i') ?? '',
            ]);

        return response()->json(['success' => true, 'history' => $history]);
    }

    /**
     * Search vendor's own products to tag in a short.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $vendor = $this->getVendor();
        $term = trim($request->input('term', $request->input('q', '')));

        if (!$vendor) {
            return response()->json(['success' => true, 'products' => []]);
        }

        $query = $vendor->products()->with('images');

        if (!empty($term)) {
            $query->where('title', 'like', "%{$term}%");
        }

        $products = $query->take(20)->get()->map(fn(Product $p) => [
            'id' => $p->id,
            'title' => $p->title,
            'price' => '₹' . number_format($p->base_price / 100, 0),
            'image' => $p->images->first() ? asset('storage/' . $p->images->first()->path) : asset('images/product-placeholder.svg'),
        ]);

        return response()->json(['success' => true, 'products' => $products]);
    }

    /**
     * Helper to save base64 frame thumbnail.
     */
    protected function saveBase64Thumbnail(string $dataUri): string
    {
        $parts = explode(',', $dataUri, 2);
        if (count($parts) !== 2) return '';

        $decoded = base64_decode($parts[1]);
        if (!$decoded) return '';

        $filename = 'thumb_' . time() . '_' . Str::random(8) . '.jpg';
        $path = 'shorts/thumbnails/' . $filename;

        Storage::disk('public')->put($path, $decoded);

        $publicDir = public_path('storage/shorts/thumbnails');
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }
        try {
            @file_put_contents($publicDir . '/' . $filename, $decoded);
        } catch (\Throwable $e) {}

        return asset('storage/' . $path);
    }

    /**
     * Delete short permanently by vendor.
     */
    public function deleteShort(Short $short)
    {
        $user = auth()->user();
        $vendor = $user?->vendor;
        if (!$vendor || $short->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to short.');
        }

        $id = $short->id;
        $title = $short->title;

        // Clean up video and thumb files if stored locally
        if ($short->video_url && str_contains($short->video_url, 'storage/shorts/')) {
            $rel = str_replace(asset('storage/'), '', $short->video_url);
            @unlink(storage_path('app/public/' . $rel));
            @unlink(public_path('storage/' . $rel));
        }
        if ($short->thumb_url && str_contains($short->thumb_url, 'storage/shorts/')) {
            $rel = str_replace(asset('storage/'), '', $short->thumb_url);
            @unlink(storage_path('app/public/' . $rel));
            @unlink(public_path('storage/' . $rel));
        }

        $short->forceDelete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Short #{$id} ('{$title}') permanently removed.",
            ]);
        }

        return back()->with('success', "Short #{$id} deleted successfully.");
    }
}
