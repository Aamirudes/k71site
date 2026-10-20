<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/vendor.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'storefront' => \App\Http\Middleware\ResolveStorefront::class,
            'vendor.own' => \App\Http\Middleware\EnsureVendorOwnership::class,
            'vendor.approved' => \App\Http\Middleware\EnsureVendorApproved::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'payment/phonepe/callback*',
            'webhooks/phonepe*',
            'api/webhooks/phonepe*',
            'webhook/shiprocket*',
            'delivery-status*',
            'api/delivery-status*',
            'shorts/ajax*',
            'admin-ajax.php*',
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ResolveVertical::class,
            \App\Http\Middleware\ResolveStorefront::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
