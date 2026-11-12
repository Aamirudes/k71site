<?php

use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\CouponController;
use App\Http\Controllers\Vendor\SettingsController;

Route::middleware(['auth', 'vendor.own'])->prefix('vendor')->name('vendor.')->group(function () {
    // Routes that any vendor can access (even if pending or expired)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/subscription', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/request-renewal', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'requestRenewal'])->name('subscription.request_renewal');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/preview-ajax', [ProfileController::class, 'previewAjax'])->name('profile.preview_ajax');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Store Design 2.0 Visual Storefront Builder & Presets
    Route::get('/design', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'index'])->name('design.index');
    Route::get('/design/editor', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'editor'])->name('design.editor');
    Route::get('/design/preview', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'preview'])->name('design.preview');
    Route::post('/design/activate', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'activatePreset'])->name('design.activate');
    Route::post('/design/quick-settings', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'saveQuickSettings'])->name('design.quick_settings');
    Route::post('/design/save', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'saveDraft'])->name('design.save');
    Route::post('/design/publish', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'publish'])->name('design.publish');
    Route::post('/design/preset', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'applyPreset'])->name('design.preset');
    Route::post('/design/reset', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'resetPreset'])->name('design.reset');
    Route::get('/design/revisions', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'revisions'])->name('design.revisions');
    Route::post('/design/restore', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'restoreRevision'])->name('design.restore');
    Route::post('/design/upload', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'uploadMedia'])->name('design.upload');
    Route::post('/design/scroll-frames/batch', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'uploadScrollFramesBatch'])->name('design.scroll_frames.batch');
    Route::post('/design/scroll-frames/video', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'uploadScrollVideo'])->name('design.scroll_frames.video');
    Route::delete('/design/scroll-frames', [\App\Http\Controllers\Vendor\StorefrontDesignController::class, 'clearScrollFrames'])->name('design.scroll_frames.clear');

    // Provider Personal Website Hub (Identity, Privacy, Domain, Sections, Publish)
    Route::get('/website', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'index'])->name('website.index');
    Route::post('/website/identity', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'updateIdentity'])->name('website.identity');
    Route::post('/website/sections', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'updateSections'])->name('website.sections');
    Route::post('/website/privacy', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'updateContactPrivacy'])->name('website.privacy');
    Route::post('/website/subdomain', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'updateSubdomain'])->name('website.subdomain');
    Route::post('/website/publish', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'togglePublish'])->name('website.publish');
    Route::post('/website/preset', [\App\Http\Controllers\Vendor\WebsiteHubController::class, 'switchPreset'])->name('website.preset');

    // Routes that require an approved store
    Route::middleware(['vendor.approved'])->group(function () {
        Route::get('/products/category-fields', [ProductController::class, 'getCategoryFields'])->name('products.category_fields');
        Route::resource('products', ProductController::class)->except(['show']);
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle_status');
        Route::patch('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle_availability');
        Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');

        // K71 Food Kitchen & Menu Builder
        Route::get('/food/menu', [\App\Http\Controllers\Vendor\FoodMenuController::class, 'index'])->name('food.menu');
        Route::post('/food/categories', [\App\Http\Controllers\Vendor\FoodMenuController::class, 'storeCategory'])->name('food.categories.store');
        Route::put('/food/categories/{category}', [\App\Http\Controllers\Vendor\FoodMenuController::class, 'updateCategory'])->name('food.categories.update');
        Route::delete('/food/categories/{category}', [\App\Http\Controllers\Vendor\FoodMenuController::class, 'destroyCategory'])->name('food.categories.destroy');
        Route::post('/food/categories/reorder', [\App\Http\Controllers\Vendor\FoodMenuController::class, 'reorderCategories'])->name('food.categories.reorder');
        Route::post('/food/toggle-orders', [\App\Http\Controllers\Vendor\FoodMenuController::class, 'toggleAcceptingOrders'])->name('food.toggle_orders');

        Route::resource('coupons', CouponController::class)->except(['show']);
        
        Route::get('/orders/check-new', [\App\Http\Controllers\Vendor\OrderController::class, 'checkNewOrders'])->name('orders.check_new');
        Route::get('/orders', [\App\Http\Controllers\Vendor\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Vendor\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/shipping-label', [\App\Http\Controllers\Vendor\OrderController::class, 'shippingLabel'])->name('orders.shipping_label');
        Route::post('/orders/{order}/ship-automated', [\App\Http\Controllers\Vendor\OrderController::class, 'shipAutomated'])->name('orders.ship_automated');
        Route::post('/orders/{order}/accept', [\App\Http\Controllers\Vendor\OrderController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{order}/reject', [\App\Http\Controllers\Vendor\OrderController::class, 'rejectOrder'])->name('orders.reject');
        Route::post('/orders/{order}/approve-payment', [\App\Http\Controllers\Vendor\OrderController::class, 'approvePaymentProof'])->name('orders.approve_payment');
        Route::post('/orders/{order}/reject-payment', [\App\Http\Controllers\Vendor\OrderController::class, 'rejectPaymentProof'])->name('orders.reject_payment');
        Route::get('/orders/{order}/nearby-partners', [\App\Http\Controllers\Vendor\OrderController::class, 'getNearbyDeliveryPartners'])->name('orders.nearby_partners');
        Route::post('/orders/{order}/assign-partner', [\App\Http\Controllers\Vendor\OrderController::class, 'assignDeliveryPartner'])->name('orders.assign_partner');
        Route::get('/delivery-partners', [\App\Http\Controllers\Vendor\OrderController::class, 'deliveryPartnersIndex'])->name('delivery_partners.index');
        Route::post('/orders/{order}/simulate-shipping', [\App\Http\Controllers\Vendor\OrderController::class, 'simulateShipping'])->name('orders.simulate_shipping');
        Route::post('/orders/{order}/simulate-settlement', [\App\Http\Controllers\Vendor\OrderController::class, 'simulateSettlement'])->name('orders.simulate_settlement');
        Route::get('/orders/{order}/status', fn(\App\Models\Order $order) => redirect()->route('vendor.orders.show', $order));
        Route::put('/orders/{order}/status', [\App\Http\Controllers\Vendor\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Vendor\OrderController::class, 'invoice'])->name('orders.invoice');

        // Vendor Shorts Studio
        Route::get('/shorts', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'index'])->name('shorts.index');
        Route::get('/shorts/studio', [\App\Http\Controllers\Vendor\ShortsStudioController::class, 'index'])->name('shorts.studio');
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

        // Vendor Review Replies & Reputation Hub
        Route::get('/reviews', [\App\Http\Controllers\Vendor\ReviewHubController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/contract/{contract}/reply', [\App\Http\Controllers\Vendor\ReviewHubController::class, 'replyContractReview'])->name('reviews.contract_reply');
        Route::post('/reviews/{review}/reply', [\App\Http\Controllers\ReviewController::class, 'vendorReply'])->name('reviews.reply');

        // Portfolio Case Studies CRUD
        Route::resource('portfolio', \App\Http\Controllers\Vendor\PortfolioController::class)->except(['show']);
        Route::patch('/portfolio/{portfolio}/toggle-status', [\App\Http\Controllers\Vendor\PortfolioController::class, 'toggleStatus'])->name('portfolio.toggle_status');
        Route::patch('/portfolio/{portfolio}/toggle-featured', [\App\Http\Controllers\Vendor\PortfolioController::class, 'toggleFeatured'])->name('portfolio.toggle_featured');

        // Client Projects, Proposals, Contracts & Website Analytics
        Route::get('/projects', [\App\Http\Controllers\Vendor\ProjectHubController::class, 'projectsIndex'])->name('projects.index');
        Route::get('/contracts', [\App\Http\Controllers\Vendor\ProjectHubController::class, 'contractsIndex'])->name('contracts.index');
        Route::get('/analytics', [\App\Http\Controllers\Vendor\AnalyticsController::class, 'index'])->name('analytics.index');

        // Vendor Freelance Services & Project Studio
        Route::get('/services', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'index'])->name('services.index');
        Route::get('/services/create', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'create'])->name('services.create');
        Route::post('/services', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'store'])->name('services.store');
        Route::get('/services/{product}/edit', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'edit'])->name('services.edit');
        Route::put('/services/{product}', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'update'])->name('services.update');
        Route::patch('/services/{product}/toggle-status', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'toggleStatus'])->name('services.toggle_status');
        Route::patch('/services/{product}/toggle-website', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'toggleWebsiteVisibility'])->name('services.toggle_website');
        Route::post('/services/{product}/duplicate', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'duplicate'])->name('services.duplicate');
        Route::delete('/services/{product}', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'destroy'])->name('services.destroy');
        Route::get('/service-orders', [\App\Http\Controllers\Vendor\ServiceStudioController::class, 'orders'])->name('service_orders.index');
    });
});
