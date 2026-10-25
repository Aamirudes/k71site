<?php

use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\CouponController;
use App\Http\Controllers\Vendor\SettingsController;

Route::middleware(['auth', 'vendor.own'])->prefix('vendor')->name('vendor.')->group(function () {
    // Routes that any vendor can access (even if pending)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Routes that require an approved store
    Route::middleware(['vendor.approved'])->group(function () {
        Route::get('/products/category-fields', [ProductController::class, 'getCategoryFields'])->name('products.category_fields');
        Route::resource('products', ProductController::class)->except(['show']);
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle_status');
        Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::resource('coupons', CouponController::class)->except(['show']);
        
        Route::get('/orders/check-new', [\App\Http\Controllers\Vendor\OrderController::class, 'checkNewOrders'])->name('orders.check_new');
        Route::get('/orders', [\App\Http\Controllers\Vendor\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Vendor\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/shipping-label', [\App\Http\Controllers\Vendor\OrderController::class, 'shippingLabel'])->name('orders.shipping_label');
        Route::post('/orders/{order}/ship-automated', [\App\Http\Controllers\Vendor\OrderController::class, 'shipAutomated'])->name('orders.ship_automated');
        Route::post('/orders/{order}/accept', [\App\Http\Controllers\Vendor\OrderController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{order}/reject', [\App\Http\Controllers\Vendor\OrderController::class, 'rejectOrder'])->name('orders.reject');
        if (app()->environment('local', 'testing')) {
            Route::post('/orders/{order}/simulate-shipping', [\App\Http\Controllers\Vendor\OrderController::class, 'simulateShipping'])->name('orders.simulate_shipping');
            Route::post('/orders/{order}/simulate-settlement', [\App\Http\Controllers\Vendor\OrderController::class, 'simulateSettlement'])->name('orders.simulate_settlement');
        }
        Route::get('/orders/{order}/status', fn(\App\Models\Order $order) => redirect()->route('vendor.orders.show', $order));
        Route::put('/orders/{order}/status', [\App\Http\Controllers\Vendor\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Vendor\OrderController::class, 'invoice'])->name('orders.invoice');

        // Vendor Shorts Studio
        Route::get('/shorts', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'index'])->name('shorts.index');
        Route::match(['get', 'post'], '/shorts/ajax', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'ajax'])->name('shorts.ajax');
        Route::post('/shorts/upload', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'upload'])->name('shorts.upload');
        Route::post('/shorts/save', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'save'])->name('shorts.save');
        Route::delete('/shorts/{short}', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'deleteShort'])->name('shorts.delete');
        Route::post('/shorts/boost', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'requestBoost'])->name('shorts.boost');

        // Vendor Offers Management & Sold Status (Phase 6)
        Route::get('/offers', [\App\Http\Controllers\OfferController::class, 'vendorIndex'])->name('offers.index');
        Route::put('/offers/{offer}/respond', [\App\Http\Controllers\OfferController::class, 'vendorRespond'])->name('offers.respond');
        Route::post('/products/{product}/mark-sold', [\App\Http\Controllers\OfferController::class, 'markSold'])->name('products.mark_sold');

        // Vendor Bookings Management (Phase 7)
        Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'vendorIndex'])->name('bookings.index');
        Route::put('/bookings/{booking}/status', [\App\Http\Controllers\BookingController::class, 'vendorUpdateStatus'])->name('bookings.status');

        // Vendor Enquiries & Quotes (Phase 8)
        Route::get('/enquiries', [\App\Http\Controllers\EnquiryController::class, 'vendorIndex'])->name('enquiries.index');
        Route::post('/enquiries/{enquiry}/quote', [\App\Http\Controllers\EnquiryController::class, 'vendorQuote'])->name('enquiries.quote');

        // Vendor Messages & Direct Inquiries (Phase 9)
        Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'vendorIndex'])->name('messages.index');
        Route::get('/messages/{conversation}', [\App\Http\Controllers\MessageController::class, 'vendorShow'])->name('messages.show');
        Route::post('/messages/{conversation}/reply', [\App\Http\Controllers\MessageController::class, 'vendorReply'])->name('messages.reply');

        // Vendor Review Replies (Phase 10)
        Route::post('/reviews/{review}/reply', [\App\Http\Controllers\ReviewController::class, 'vendorReply'])->name('reviews.reply');

        // Vendor Freelance Services & Project Studio
        Route::get('/services', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'index'])->name('services.index');
        Route::get('/services/create', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'create'])->name('services.create');
        Route::post('/services', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'store'])->name('services.store');
        Route::get('/services/{product}/edit', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'edit'])->name('services.edit');
        Route::put('/services/{product}', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'update'])->name('services.update');
        Route::delete('/services/{product}', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'destroy'])->name('services.destroy');
        Route::get('/service-orders', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'orders'])->name('service_orders.index');
    });
});
