<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Resolve Base URL for sitemap generation
     */
    protected function getBaseUrl(): string
    {
        $host = request()->getHost();
        $isLocal = in_array($host, ['localhost', '127.0.0.1', '']) || app()->environment('local');
        
        $rootDomain = config('k71.root_domain', 'marblemandi.org');
        $domain = (!$isLocal && $host) ? $host : (config('marketplace.domain') !== 'localhost' ? config('marketplace.domain') : $rootDomain);
        $scheme = (app()->environment('production') || request()->secure() || !str_contains($domain, 'localhost')) ? 'https' : 'http';
        
        return $scheme . '://' . $domain;
    }

    /**
     * Master Sitemap Index
     */
    public function index(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = now()->toAtomString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $sitemaps = [
            $baseUrl . '/sitemap-products.xml',
            $baseUrl . '/sitemap-categories.xml',
            $baseUrl . '/sitemap-stores.xml',
            $baseUrl . '/sitemap-pages.xml',
            $baseUrl . '/sitemap-shorts.xml',
        ];

        foreach ($sitemaps as $url) {
            $xml .= "    <sitemap>\n";
            $xml .= "        <loc>" . htmlspecialchars($url) . "</loc>\n";
            $xml .= "        <lastmod>{$now}</lastmod>\n";
            $xml .= "    </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Dynamic Products XML Sitemap with Google Image Extension
     */
    public function products(): Response
    {
        $baseUrl = $this->getBaseUrl();

        $xml = Cache::remember('sitemap_products_xml', 3600, function () use ($baseUrl) {
            $products = Product::with(['images' => fn($q) => $q->orderBy('sort_order')])
                ->where('status', 'active')
                ->latest('updated_at')
                ->take(10000)
                ->get();

            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            foreach ($products as $product) {
                $loc = $baseUrl . '/product/' . $product->slug;
                $lastmod = ($product->updated_at ?? now())->toAtomString();

                $out .= "    <url>\n";
                $out .= "        <loc>" . htmlspecialchars($loc) . "</loc>\n";
                $out .= "        <lastmod>{$lastmod}</lastmod>\n";
                $out .= "        <changefreq>daily</changefreq>\n";
                $out .= "        <priority>0.8</priority>\n";

                // Add up to 3 product images for Google Image Search indexing
                foreach ($product->images->take(3) as $img) {
                    $imgUrl = str_starts_with($img->path, 'http') ? $img->path : $baseUrl . '/storage/' . $img->path;
                    $out .= "        <image:image>\n";
                    $out .= "            <image:loc>" . htmlspecialchars($imgUrl) . "</image:loc>\n";
                    $out .= "            <image:title>" . htmlspecialchars($product->title) . "</image:title>\n";
                    $out .= "        </image:image>\n";
                }

                $out .= "    </url>\n";
            }

            $out .= '</urlset>';
            return $out;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Categories XML Sitemap
     */
    public function categories(): Response
    {
        $baseUrl = $this->getBaseUrl();

        $xml = Cache::remember('sitemap_categories_xml', 7200, function () use ($baseUrl) {
            $categories = Category::where('is_active', true)
                ->latest('updated_at')
                ->get();

            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($categories as $cat) {
                $loc = $baseUrl . '/category/' . $cat->slug;
                $lastmod = ($cat->updated_at ?? now())->toAtomString();

                $out .= "    <url>\n";
                $out .= "        <loc>" . htmlspecialchars($loc) . "</loc>\n";
                $out .= "        <lastmod>{$lastmod}</lastmod>\n";
                $out .= "        <changefreq>weekly</changefreq>\n";
                $out .= "        <priority>0.8</priority>\n";
                $out .= "    </url>\n";
            }

            $out .= '</urlset>';
            return $out;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=7200',
        ]);
    }

    /**
     * Vendor Storefronts XML Sitemap (subdomains & path storefronts)
     */
    public function stores(): Response
    {
        $xml = Cache::remember('sitemap_stores_xml', 3600, function () {
            $vendors = Vendor::where('status', 'approved')
                ->latest('updated_at')
                ->get();

            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($vendors as $vendor) {
                if (config('marketplace.use_subdomains')) {
                    $domain = (config('marketplace.domain') && config('marketplace.domain') !== 'localhost') ? config('marketplace.domain') : 'marblemandi.org';
                    $scheme = (app()->environment('production') || !str_contains($domain, 'localhost')) ? 'https' : 'http';
                    $loc = $scheme . '://' . $vendor->slug . '.' . $domain;
                } else {
                    $loc = $vendor->storefront_url;
                }
                $lastmod = ($vendor->updated_at ?? now())->toAtomString();

                $out .= "    <url>\n";
                $out .= "        <loc>" . htmlspecialchars($loc) . "</loc>\n";
                $out .= "        <lastmod>{$lastmod}</lastmod>\n";
                $out .= "        <changefreq>daily</changefreq>\n";
                $out .= "        <priority>0.9</priority>\n";
                $out .= "    </url>\n";
            }

            $out .= '</urlset>';
            return $out;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Essential Static & Legal Pages Sitemap
     */
    public function pages(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = now()->toAtomString();

        $pages = [
            ['path' => '/', 'freq' => 'daily', 'priority' => '1.0'],
            ['path' => '/catalog', 'freq' => 'daily', 'priority' => '0.9'],
            ['path' => '/shorts', 'freq' => 'daily', 'priority' => '0.9'],
            ['path' => '/food', 'freq' => 'daily', 'priority' => '0.9'],
            ['path' => '/services', 'freq' => 'daily', 'priority' => '0.9'],
            ['path' => '/used', 'freq' => 'daily', 'priority' => '0.9'],
            ['path' => '/enquiry', 'freq' => 'daily', 'priority' => '0.9'],
            ['path' => '/sell', 'freq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/about-us', 'freq' => 'monthly', 'priority' => '0.5'],
            ['path' => '/contact', 'freq' => 'monthly', 'priority' => '0.5'],
            ['path' => '/terms-and-conditions', 'freq' => 'monthly', 'priority' => '0.3'],
            ['path' => '/privacy-policy', 'freq' => 'monthly', 'priority' => '0.3'],
            ['path' => '/refund-policy', 'freq' => 'monthly', 'priority' => '0.3'],
            ['path' => '/shipping-policy', 'freq' => 'monthly', 'priority' => '0.3'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $p) {
            $loc = $baseUrl . $p['path'];
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($loc) . "</loc>\n";
            $xml .= "        <lastmod>{$now}</lastmod>\n";
            $xml .= "        <changefreq>{$p['freq']}</changefreq>\n";
            $xml .= "        <priority>{$p['priority']}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Google Merchant Center Automated Product Feed (RSS 2.0 / XML)
     * Powers Google Shopping Free Product Listings, Google Lens, and Image Shopping Cards.
     */
    public function googleMerchantFeed(): Response
    {
        $baseUrl = $this->getBaseUrl();

        $xml = Cache::remember('google_merchant_feed_xml', 3600, function () use ($baseUrl) {
            $products = Product::with(['images' => fn($q) => $q->orderBy('sort_order'), 'vendor', 'category'])
                ->where('status', 'active')
                ->where('total_stock', '>', 0)
                ->latest('updated_at')
                ->take(5000)
                ->get();

            $appName = config('marketplace.name', 'K71 Marketplace');

            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
            $out .= "  <channel>\n";
            $out .= "    <title>" . htmlspecialchars($appName) . " - Official Google Merchant Feed</title>\n";
            $out .= "    <link>" . htmlspecialchars($baseUrl) . "</link>\n";
            $out .= "    <description>Curated fashion, footwear, ethnic wear, and modern collections directly from independent Indian boutiques.</description>\n";

            foreach ($products as $prod) {
                $priceRupees = number_format($prod->base_price / 100, 2, '.', '');
                $prodUrl = $baseUrl . '/product/' . $prod->slug;
                $brand = $prod->brand ?: ($prod->vendor->shop_name ?? 'K71 Verified Seller');
                $categoryName = $prod->category?->name ?? 'Apparel & Accessories';
                $description = strip_tags($prod->short_description ?: $prod->description ?: "Handcrafted {$prod->title} by {$brand} on K71.");
                $description = mb_substr(trim($description), 0, 5000);

                $mainImage = $prod->images->first();
                $mainImageUrl = $mainImage 
                    ? (str_starts_with($mainImage->path, 'http') ? $mainImage->path : $baseUrl . '/storage/' . $mainImage->path)
                    : $baseUrl . '/images/product-placeholder.svg';

                $out .= "    <item>\n";
                $out .= "      <g:id>" . htmlspecialchars($prod->sku ?: 'PROD_' . $prod->id) . "</g:id>\n";
                $out .= "      <g:title>" . htmlspecialchars(mb_substr($prod->title, 0, 150)) . "</g:title>\n";
                $out .= "      <g:description>" . htmlspecialchars($description) . "</g:description>\n";
                $out .= "      <g:link>" . htmlspecialchars($prodUrl) . "</g:link>\n";
                $out .= "      <g:image_link>" . htmlspecialchars($mainImageUrl) . "</g:image_link>\n";

                // Additional images
                foreach ($prod->images->slice(1, 5) as $addImg) {
                    $addUrl = str_starts_with($addImg->path, 'http') ? $addImg->path : $baseUrl . '/storage/' . $addImg->path;
                    $out .= "      <g:additional_image_link>" . htmlspecialchars($addUrl) . "</g:additional_image_link>\n";
                }

                $out .= "      <g:availability>" . ($prod->total_stock > 0 ? 'in_stock' : 'out_of_stock') . "</g:availability>\n";
                $out .= "      <g:price>{$priceRupees} INR</g:price>\n";

                if ($prod->compare_at_price > $prod->base_price) {
                    $out .= "      <g:sale_price>{$priceRupees} INR</g:sale_price>\n";
                }

                $out .= "      <g:brand>" . htmlspecialchars($brand) . "</g:brand>\n";
                $out .= "      <g:condition>new</g:condition>\n";
                $out .= "      <g:google_product_category>Apparel &amp; Accessories</g:google_product_category>\n";
                $out .= "      <g:product_type>" . htmlspecialchars($categoryName) . "</g:product_type>\n";
                $out .= "      <g:shipping>\n";
                $out .= "        <g:country>IN</g:country>\n";
                $out .= "        <g:service>Standard Doorstep Delivery</g:service>\n";
                $out .= "        <g:price>0.00 INR</g:price>\n";
                $out .= "      </g:shipping>\n";
                $out .= "    </item>\n";
            }

            $out .= "  </channel>\n";
            $out .= '</rss>';
            return $out;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Shorts Video Sitemap for Googlebot & Video Search Indexing
     */
    public function shorts(): Response
    {
        $baseUrl = $this->getBaseUrl();

        $xml = Cache::remember('sitemap_shorts_' . md5($baseUrl), 3600, function () use ($baseUrl) {
            $shorts = \App\Models\Short::with('vendor')
                ->where('status', 'published')
                ->latest()
                ->take(500)
                ->get();

            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

            foreach ($shorts as $short) {
                $loc = $baseUrl . '/shorts/v/' . $short->id;
                $lastmod = ($short->updated_at ?? $short->created_at ?? now())->toAtomString();
                $thumb = $short->thumb_url ?: $baseUrl . '/images/product-placeholder.svg';
                $title = htmlspecialchars($short->title);
                $desc = htmlspecialchars(mb_substr(strip_tags($short->description ?: $short->title), 0, 2048));
                $uploader = htmlspecialchars($short->vendor?->shop_name ?? 'K71 Creator');

                $out .= "  <url>\n";
                $out .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
                $out .= "    <lastmod>{$lastmod}</lastmod>\n";
                $out .= "    <changefreq>daily</changefreq>\n";
                $out .= "    <priority>0.8</priority>\n";
                $out .= "    <video:video>\n";
                $out .= "      <video:thumbnail_loc>" . htmlspecialchars($thumb) . "</video:thumbnail_loc>\n";
                $out .= "      <video:title>{$title}</video:title>\n";
                $out .= "      <video:description>{$desc}</video:description>\n";
                $out .= "      <video:content_loc>" . htmlspecialchars($short->video_url) . "</video:content_loc>\n";
                $out .= "      <video:publication_date>" . ($short->created_at ?? now())->toIso8601String() . "</video:publication_date>\n";
                $out .= "      <video:uploader>" . $uploader . "</video:uploader>\n";
                $out .= "    </video:video>\n";
                $out .= "  </url>\n";
            }

            $out .= '</urlset>';
            return $out;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
