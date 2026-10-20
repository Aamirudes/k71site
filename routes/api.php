<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorValidationController;
use App\Http\Controllers\WishlistController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/validate-slug', [VendorValidationController::class, 'check'])->name('api.validate.slug');
Route::get('/validate-username', [VendorValidationController::class, 'checkUsername'])->name('api.validate.username');

// Logistics Tracking Webhook (clean URL without prohibited keywords)
Route::post('/delivery-status', [\App\Http\Controllers\Webhook\ShiprocketWebhookController::class, 'handle'])->name('api.delivery.status');
Route::post('/webhook/shiprocket', [\App\Http\Controllers\Webhook\ShiprocketWebhookController::class, 'handle'])->name('api.webhook.shiprocket');

// PhonePe Payment & Settlement Webhook
Route::post('/webhooks/phonepe', [\App\Http\Controllers\Webhook\PhonePeWebhookController::class, 'handle'])->name('api.webhook.phonepe');

