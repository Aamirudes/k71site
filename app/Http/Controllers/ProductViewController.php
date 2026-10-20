<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductViewController extends Controller
{
    public function show($slug)
    {
        $product = Product::with(['vendor.settings', 'images', 'variants', 'category', 'reviews.user'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // High-CTR SEO Data (Google SERP optimized)
        $priceRupees = number_format($product->base_price / 100, 0);
        $brandName = $product->brand ?: $product->vendor->shop_name;
        $defaultSeoTitle = "Buy {$product->title} Online at ₹{$priceRupees} | {$brandName} - K71 India";
        $seoTitle = $product->seo_title ?: $defaultSeoTitle;

        $defaultSeoDesc = "Shop authentic {$product->title} by {$brandName} on K71 at ₹{$priceRupees}. Verified quality, 100% genuine seller, pan-India express doorstep delivery.";
        $seoDescription = $product->seo_description ?: ($product->short_description ?: $defaultSeoDesc);
        $ogImage = $product->og_image ?? ($product->images->first()->path ?? null);

        // Canonical URL
        $canonicalUrl = route('product.show', $product->slug);

        // Images for Schema
        $imageUrls = $product->images->map(fn($img) => str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path))->values()->all();
        if (empty($imageUrls)) {
            $imageUrls = [asset('images/product-placeholder.svg')];
        }

        // Inventory & Logistics
        $vendorSettings = $product->vendor->settings;
        $processingDays = $vendorSettings ? (int) ($vendorSettings->processing_days ?: 2) : 2;
        $returnDays = $product->return_window_days !== null ? (int) $product->return_window_days : ($vendorSettings ? (int) ($vendorSettings->return_window_days ?: 7) : 7);
        $isReturnable = $product->is_returnable ?? ($returnDays > 0);
        $totalStock = $product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->total_stock;
        $inStock = $totalStock > 0;
        $price = number_format($product->base_price / 100, 2, '.', '');

        // 1. Schema.org Product & Offer Graph for Google Rich Snippets
        $productSchema = [
            '@type' => 'Product',
            'name' => $product->title,
            'image' => $imageUrls,
            'description' => $seoDescription,
            'sku' => $product->sku ?: 'SKU-' . $product->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand ?: $product->vendor->shop_name,
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'INR',
                'price' => $price,
                'priceValidUntil' => now()->addYear()->format('Y-m-d'),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $product->vendor->shop_name,
                    'url' => $product->vendor->storefront_url,
                ],
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => [
                        '@type' => 'MonetaryAmount',
                        'value' => '0',
                        'currency' => 'INR',
                    ],
                    'shippingDestination' => [
                        '@type' => 'DefinedRegion',
                        'addressCountry' => 'IN',
                    ],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'transitTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 2,
                            'maxValue' => max(4, $processingDays + 3),
                            'unitCode' => 'DAY',
                        ],
                    ],
                ],
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => 'IN',
                    'returnPolicyCategory' => $isReturnable 
                        ? 'https://schema.org/MerchantReturnFiniteReturnWindow' 
                        : 'https://schema.org/MerchantReturnNotPermitted',
                    'merchantReturnDays' => $isReturnable ? $returnDays : 0,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                    'returnFees' => 'https://schema.org/FreeReturn',
                ],
            ],
        ];

        // 2. Schema.org BreadcrumbList for SERP navigation trail
        $breadcrumbSchema = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $product->category ? $product->category->name : 'Catalog',
                    'item' => $product->category ? route('category.show', $product->category->slug) : route('catalog.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $product->title,
                    'item' => $canonicalUrl,
                ],
            ],
        ];

        $schemaJson = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $productSchema,
                $breadcrumbSchema,
            ],
        ];

        // More products from same vendor
        $vendorProducts = Product::with(['vendor', 'images'])
            ->where('vendor_id', $product->vendor_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('catalog.show', compact(
            'product', 
            'seoTitle', 
            'seoDescription', 
            'ogImage', 
            'canonicalUrl',
            'schemaJson',
            'vendorProducts'
        ));
    }
}
