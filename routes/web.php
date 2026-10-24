<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\GoogleController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\ProductViewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ShortsController;
use App\Http\Controllers\ShortsAjaxController;
use App\Http\Controllers\ShortsSpinController;
use App\Http\Controllers\Admin\ShortsAdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/vendor/login', fn() => redirect()->route('login'))->name('vendor.login');

// Dynamic Multi-Index XML Sitemaps for Googlebot
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-products.xml', [SitemapController::class, 'products'])->name('sitemap.products');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories'])->name('sitemap.categories');
Route::get('/sitemap-stores.xml', [SitemapController::class, 'stores'])->name('sitemap.stores');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-shorts.xml', [SitemapController::class, 'shorts'])->name('sitemap.shorts');

// Google Merchant Center Automated RSS 2.0 Product Feed (Free Google Shopping & Image Search Listings)
Route::get('/feeds/google-merchant.xml', [SitemapController::class, 'googleMerchantFeed'])->name('feeds.google_merchant');
Route::get('/google-merchant-feed.xml', [SitemapController::class, 'googleMerchantFeed']);

// High-Performance Dynamic Image Optimization (WebP Resizer & Edge Cache)
Route::get('/img-optimized/{width}/{path}', [\App\Http\Controllers\ImageOptimizerController::class, 'render'])
    ->where('path', '.*')
    ->name('image.optimized');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/suggest', [CatalogController::class, 'suggest'])->name('catalog.suggest');
Route::get('/api/search/suggest', [CatalogController::class, 'suggest'])->name('api.search.suggest');
Route::get('/category/{slug}', [CatalogController::class, 'category'])->name('category.show');
Route::get('/product/{slug}', [ProductViewController::class, 'show'])->name('product.show');
Route::get('/catalog/product/{slug}', [ProductViewController::class, 'show'])->name('catalog.product');

// Robots.txt Handler
Route::get('/robots.txt', function () {
    $robots = file_exists(public_path('robots.txt')) ? file_get_contents(public_path('robots.txt')) : ("User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml') . "\n");
    return response($robots, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

// Session Heartbeat & CSRF Token Refresher (Guarantees Zero 419 Page Expired)
Route::get('/api/heartbeat', function() {
    return response()->json([
        'status' => 'ok',
        'csrf_token' => csrf_token(),
        'authenticated' => auth()->check(),
    ]);
})->name('api.heartbeat');

// Mandatory Legal, Compliance & Information Pages (PhonePe & Banking Audited)
Route::get('/about-us', [\App\Http\Controllers\PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [\App\Http\Controllers\PageController::class, 'contact'])->name('pages.contact');
Route::get('/terms-and-conditions', [\App\Http\Controllers\PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy-policy', [\App\Http\Controllers\PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/refund-policy', [\App\Http\Controllers\PageController::class, 'refund'])->name('pages.refund');
Route::get('/return-policy', [\App\Http\Controllers\PageController::class, 'refund']);
Route::get('/shipping-policy', [\App\Http\Controllers\PageController::class, 'shipping'])->name('pages.shipping');

Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [\App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Shorts (Short-form video commerce)
Route::get('/shorts', [ShortsController::class, 'index'])->name('shorts.index');
Route::match(['get', 'post'], '/shorts/ajax', [ShortsAjaxController::class, 'dispatch'])->name('shorts.ajax');
Route::match(['get', 'post'], '/admin-ajax.php', [ShortsAjaxController::class, 'dispatch']);
Route::get('/shorts/v/{id}', [ShortsController::class, 'show'])->name('shorts.show');
Route::get('/shorts/cat/{slug}', [ShortsController::class, 'category'])->name('shorts.category');
Route::get('/shorts/spin/check', [ShortsSpinController::class, 'check'])->name('shorts.spin.check');
Route::post('/shorts/spin/result', [ShortsSpinController::class, 'result'])->name('shorts.spin.result');

// Food & Restaurants Vertical Routes
Route::get('/food', [FoodController::class, 'index'])->name('food.index');
Route::get('/food/restaurant/{slug}', [FoodController::class, 'restaurant'])->name('food.restaurant');

// Used Items Marketplace & Offers Routes
Route::get('/used', [\App\Http\Controllers\UsedController::class, 'index'])->name('used.index');
Route::post('/used/post-ad', [\App\Http\Controllers\UsedController::class, 'storeAd'])->name('used.post-ad');
Route::get('/used/{slug}', [\App\Http\Controllers\UsedController::class, 'show'])->name('used.show');
Route::post('/offers/product/{product}', [\App\Http\Controllers\OfferController::class, 'store'])->name('offers.store');
Route::post('/classifieds/offer/{product}', [\App\Http\Controllers\OfferController::class, 'store'])->name('classifieds.offer');

// Services Vertical & Bookings Routes (Phase 7)
Route::get('/services', [\App\Http\Controllers\ServicesController::class, 'index'])->name('services.index');
Route::get('/services/provider/{slug}', [\App\Http\Controllers\ServicesController::class, 'provider'])->name('services.provider');
Route::post('/services/book/{product}', [\App\Http\Controllers\BookingController::class, 'store'])->name('services.book');
Route::get('/bookings/{booking_number}', [\App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');

// Enquiry Marketplace & Quotes Routes (Phase 8)
Route::get('/enquiry', [\App\Http\Controllers\EnquiryController::class, 'index'])->name('enquiry.index');
Route::get('/b2b', [\App\Http\Controllers\EnquiryController::class, 'index'])->name('b2b.index');
Route::get('/enquiry/create', [\App\Http\Controllers\EnquiryController::class, 'create'])->name('enquiry.create');
Route::get('/b2b/create', [\App\Http\Controllers\EnquiryController::class, 'create'])->name('b2b.create');
Route::post('/enquiry', [\App\Http\Controllers\EnquiryController::class, 'store'])->name('enquiry.store');
Route::post('/rfq/submit/{product}', [\App\Http\Controllers\EnquiryController::class, 'submitRfq'])->name('rfq.submit');
Route::get('/enquiry/{enquiry_number}', [\App\Http\Controllers\EnquiryController::class, 'show'])->name('enquiry.show');
Route::post('/quotes/{quote}/accept', [\App\Http\Controllers\EnquiryController::class, 'acceptQuote'])->name('quotes.accept');

// Auctions & Bidding Routes (Phase 9)
Route::get('/auctions/{product:slug}', [\App\Http\Controllers\AuctionController::class, 'show'])->name('auctions.show');
Route::post('/auctions/{product}/bid', [\App\Http\Controllers\AuctionController::class, 'placeBid'])->name('auctions.bid');

// Customer Direct Messaging & Notifications Routes (Phase 9)
Route::middleware('auth')->group(function () {
    Route::get('/direct', fn() => redirect()->route('messages.index'))->name('messages.instagram_direct');
    Route::get('/dm', fn() => redirect()->route('messages.index'))->name('messages.dm');
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/{conversation}/reply', [\App\Http\Controllers\MessageController::class, 'reply'])->name('messages.reply');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Customer Reviews & Community Trust Reports (Phase 10)
    Route::post('/products/{product}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reports', [\App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');
});


// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

// Vendor Onboarding (Guest or Authenticated Customer)
Route::get('/sell', [RegisterController::class, 'vendorShow'])->name('vendor.register');
Route::post('/sell', [RegisterController::class, 'vendorStore']);
Route::get('/vendor/register', [RegisterController::class, 'vendorShow']);
Route::post('/vendor/register', [RegisterController::class, 'vendorStore']);

// OTP Routes (Needs session, but not necessarily fully authenticated)
Route::get('/verify-otp', [OtpController::class, 'show'])->name('otp.show');
Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
Route::post('/resend-otp', [OtpController::class, 'resend'])->name('otp.resend');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/account', [\App\Http\Controllers\AccountController::class, 'index'])->name('account');
    Route::get('/account/export', [\App\Http\Controllers\AccountController::class, 'export'])->name('account.export');
    Route::post('/account/delete', [\App\Http\Controllers\AccountController::class, 'delete'])->name('account.delete');
});

// Public Catalog & Products Route
Route::get('/products', function() { return redirect()->route('catalog.index'); })->name('products.index');
// Vertical Storefront Proxies (handles subdomains routed via Cloudflare/Hostinger proxying as /s/{vertical})
Route::get('/s/food', [FoodController::class, 'index'])->name('storefront.food');
Route::get('/s/food/restaurant/{slug}', [FoodController::class, 'restaurant'])->name('storefront.food.restaurant');

Route::get('/s/services', [\App\Http\Controllers\ServicesController::class, 'index'])->name('storefront.services');
Route::get('/s/services/provider/{slug}', [\App\Http\Controllers\ServicesController::class, 'provider'])->name('storefront.services.provider');

Route::get('/s/used', [\App\Http\Controllers\UsedController::class, 'index'])->name('storefront.used');
Route::get('/s/used/{slug}', [\App\Http\Controllers\UsedController::class, 'show'])->name('storefront.used.show');
Route::get('/s/enquiry', [\App\Http\Controllers\EnquiryController::class, 'index'])->name('storefront.enquiry');
Route::get('/s/shorts', [ShortsController::class, 'index'])->name('storefront.shorts');
Route::get('/s/vendor', fn() => redirect()->route('vendor.register'))->name('storefront.vendor');

// Public Vendor Storefronts
Route::get('/s/{slug}', [\App\Http\Controllers\StorefrontController::class, 'show'])->name('storefront.show');

// Cart Routes
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::get('/cart/data', [\App\Http\Controllers\CartController::class, 'data'])->name('cart.data');
Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
Route::get('/cart/count', [\App\Http\Controllers\CartController::class, 'getCount'])->name('cart.count');
Route::put('/cart/{item}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');

// Checkout and Orders
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{vendor}', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.vendor');
    Route::get('/checkout', fn() => redirect()->route('cart.index'))->name('checkout.index');
    Route::post('/checkout/{vendor}', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/{vendor}/coupon', [\App\Http\Controllers\CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    Route::delete('/checkout/{vendor}/coupon', [\App\Http\Controllers\CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout/{vendor}/shipping-rate', [\App\Http\Controllers\CheckoutController::class, 'calculateShippingRate'])->name('checkout.shipping_rate');
    
    Route::get('/checkout/{order}/payment', [\App\Http\Controllers\CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/{order}/payment', [\App\Http\Controllers\CheckoutController::class, 'processPayment'])->name('checkout.payment.process');
    
    // PhonePe Automated Payment Initiation (Requires Auth)
    Route::get('/payment/phonepe/initiate/{order}', [\App\Http\Controllers\PaymentController::class, 'initiate'])->name('payment.phonepe.initiate');
    
    Route::get('/orders/success/{order}', [\App\Http\Controllers\OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/cancel', fn(\App\Models\Order $order) => redirect()->route('orders.show', $order));
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    
});

// Master Admin Authentication Routes (Guest accessible)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.submit');
    Route::get('/verify-otp', [\App\Http\Controllers\Admin\AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/verify-otp', [\App\Http\Controllers\Admin\AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/resend-otp', [\App\Http\Controllers\Admin\AuthController::class, 'resendOtp'])->name('otp.resend');
    Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
});

// Protected Master Admin Panel Routes (Guarded by IsAdmin & 2FA)
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/vendors', [\App\Http\Controllers\AdminController::class, 'vendors'])->name('vendors');
    Route::put('/vendors/{vendor}/status', [\App\Http\Controllers\AdminController::class, 'updateVendorStatus'])->name('vendors.status');
    Route::get('/products', [\App\Http\Controllers\AdminController::class, 'products'])->name('products');
    Route::get('/orders', [\App\Http\Controllers\AdminController::class, 'orders'])->name('orders');
    Route::post('/orders/batch-settlement', [\App\Http\Controllers\AdminController::class, 'processBatchSettlements'])->name('orders.batch_settlement');
    Route::get('/orders/{order}', [\App\Http\Controllers\AdminController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/mark-settled', [\App\Http\Controllers\AdminController::class, 'markSettled'])->name('orders.mark_settled');
    Route::post('/orders/{order}/disburse-payout', [\App\Http\Controllers\AdminController::class, 'disbursePayout'])->name('orders.disburse_payout');
    Route::post('/orders/{order}/add-note', [\App\Http\Controllers\AdminController::class, 'addOrderNote'])->name('orders.add_note');
    Route::get('/homepage', [\App\Http\Controllers\Admin\HomepageController::class, 'edit'])->name('homepage');
    Route::put('/homepage', [\App\Http\Controllers\Admin\HomepageController::class, 'update'])->name('homepage.update');
    Route::get('/settlements', [\App\Http\Controllers\Admin\SettlementController::class, 'index'])->name('settlements');
    Route::get('/shorts', [ShortsAdminController::class, 'index'])->name('shorts.index');
    Route::post('/shorts/{short}/approve', [ShortsAdminController::class, 'approve'])->name('shorts.approve');
    Route::post('/shorts/{short}/reject', [ShortsAdminController::class, 'reject'])->name('shorts.reject');
    Route::post('/shorts/{short}/manual-boost', [ShortsAdminController::class, 'manualBoost'])->name('shorts.manual_boost');

    // Trust Moderation, Promotions & Audit Logs (Phase 10)
    Route::get('/reports', [\App\Http\Controllers\AdminController::class, 'reports'])->name('reports.index');
    Route::post('/reports/{report}/resolve', [\App\Http\Controllers\AdminController::class, 'resolveReport'])->name('reports.resolve');
    Route::get('/promotions', [\App\Http\Controllers\AdminController::class, 'promotions'])->name('promotions.index');
    Route::post('/promotions', [\App\Http\Controllers\AdminController::class, 'storePromotion'])->name('promotions.store');
    Route::post('/promotions/{promotion}/toggle', [\App\Http\Controllers\AdminController::class, 'togglePromotion'])->name('promotions.toggle');
    Route::get('/audit-logs', [\App\Http\Controllers\AdminController::class, 'auditLogs'])->name('audit_logs.index');

    // Navigation & Mobile App Drawer Studio
    Route::get('/navigation', [\App\Http\Controllers\Admin\NavigationController::class, 'index'])->name('navigation.index');
    Route::get('/navigation/{item}', fn() => redirect()->route('admin.navigation.index'));
    Route::post('/navigation', [\App\Http\Controllers\Admin\NavigationController::class, 'store'])->name('navigation.store');
    Route::put('/navigation/{item}', [\App\Http\Controllers\Admin\NavigationController::class, 'update'])->name('navigation.update');
    Route::delete('/navigation/{item}', [\App\Http\Controllers\Admin\NavigationController::class, 'destroy'])->name('navigation.destroy');
    Route::post('/navigation/reorder', [\App\Http\Controllers\Admin\NavigationController::class, 'reorder'])->name('navigation.reorder');
    Route::post('/navigation/reset-defaults', [\App\Http\Controllers\Admin\NavigationController::class, 'resetDefaults'])->name('navigation.reset');
});

// PhonePe Gateway Callback & S2S Webhook
Route::match(['get', 'post'], '/payment/phonepe/callback/{order}', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.phonepe.callback');
Route::post('/webhooks/phonepe', [\App\Http\Controllers\Webhook\PhonePeWebhookController::class, 'handle'])->name('webhook.phonepe');

// Dynamic Category Attributes & Vertical Blueprints API
Route::get('/api/vendor/category-attributes', [\App\Http\Controllers\Api\CategoryAttributeController::class, 'show']);
Route::get('/api/vendor/category-attributes.php', [\App\Http\Controllers\Api\CategoryAttributeController::class, 'show']);

// Logistics Tracking Webhook (clean URL without prohibited keywords)
Route::post('/delivery-status', [\App\Http\Controllers\Webhook\ShiprocketWebhookController::class, 'handle'])->name('delivery.status');
Route::post('/webhook/shiprocket', [\App\Http\Controllers\Webhook\ShiprocketWebhookController::class, 'handle'])->name('webhook.shiprocket');

// REMOVED: /setup-database route (security risk — use: php artisan migrate --force && php artisan db:seed --force)

// Dynamic Fallback Storage Route for environments where symlink() is disabled
Route::get('/storage/{path}', function ($path) {
    // 1. Check if the file exists in storage/app/public
    $source = storage_path('app/public/' . $path);
    if (file_exists($source) && is_file($source)) {
        $target = public_path('storage/' . $path);
        @mkdir(dirname($target), 0755, true);
        @copy($source, $target);
        return response()->file($source);
    }

    // 2. Return product placeholder SVG if file is missing
    $placeholder = public_path('images/product-placeholder.svg');
    if (file_exists($placeholder)) {
        return response()->file($placeholder, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    abort(404);
})->where('path', '.*');


