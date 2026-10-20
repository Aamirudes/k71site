<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\SizeChart;
use App\Models\Coupon;
use App\Models\Order;
use App\Scopes\VendorScope;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '']) 
            || str_contains($host, 'localhost') 
            || str_contains($host, '127.0.0.1')
            || str_starts_with($host, '192.168.')
            || str_starts_with($host, '10.')
            || str_starts_with($host, '172.');
        if (app()->environment('production') && !$isLocalHost && !str_starts_with(config('app.url'), 'http://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        Product::addGlobalScope(new VendorScope);
        SizeChart::addGlobalScope(new VendorScope);
        Coupon::addGlobalScope(new VendorScope);
        Order::addGlobalScope(new VendorScope);

        // Security: Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('checkout', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
