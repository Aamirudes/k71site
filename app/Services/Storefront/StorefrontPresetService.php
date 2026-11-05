<?php

namespace App\Services\Storefront;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Support\Str;

class StorefrontPresetService
{
    /**
     * Supported Signature Presets definitions.
     */
    public function getPresets(): array
    {
        return [
            'velora' => [
                'id' => 'velora',
                'name' => 'Velora',
                'tagline' => 'Liquid-Glass Luxury · Refined Editorial Typography',
                'description' => 'A liquid-glass luxury shopping experience with refined editorial serif typography, elegant floating navigation capsule, champagne gold tones, and seamless boutique catalog discovery.',
                'best_for' => 'Luxury fashion, couture, jewellery, footwear, designer apparel, and lifestyle brands.',
                'preview_accent' => '#c59b27',
                'preview_bg' => '#fafaf9',
                'preview_card_bg' => '#ffffff',
                'preview_text' => '#18181b',
                'display_font' => 'Playfair Display',
                'body_font' => 'Inter',
                'badge' => 'Liquid Luxury',
                'recommended_for_types' => ['clothing', 'mens', 'fashion', 'footwear', 'beauty'],
            ],
            'noire' => [
                'id' => 'noire',
                'name' => 'Noiré',
                'tagline' => 'Premium Dark Commerce · Charcoal Surfaces',
                'description' => 'A dramatic product-first storefront featuring deep charcoal surfaces, high-contrast product presentation, razor-sharp typography, and focused dark commerce.',
                'best_for' => 'Contemporary fashion, footwear, electronics, luxury accessories, and modern design goods.',
                'preview_accent' => '#f4f4f5',
                'preview_bg' => '#121214',
                'preview_card_bg' => '#18181b',
                'preview_text' => '#f4f4f5',
                'display_font' => 'Space Grotesk',
                'body_font' => 'Inter',
                'badge' => 'Dark Commerce',
                'recommended_for_types' => ['streetwear', 'sneakers', 'sports', 'footwear', 'electronics'],
            ],
            'aurel' => [
                'id' => 'aurel',
                'name' => 'Aurel',
                'tagline' => 'Refined Light Minimalism · Soft Surfaces',
                'description' => 'A beautifully restrained, product-first storefront with soft neutral surfaces, precise product grids, architectural typography balance, and exceptional layout clarity.',
                'best_for' => 'Everyday premium goods, modern lifestyle, home decor, furniture, and minimal apparel.',
                'preview_accent' => '#0f172a',
                'preview_bg' => '#f9fafb',
                'preview_card_bg' => '#ffffff',
                'preview_text' => '#0f172a',
                'display_font' => 'Plus Jakarta Sans',
                'body_font' => 'Inter',
                'badge' => 'Light Minimal',
                'recommended_for_types' => ['electronics', 'home_living', 'furniture', 'machinery', 'b2b'],
            ],
            'chroma' => [
                'id' => 'chroma',
                'name' => 'Chroma',
                'tagline' => 'Bold Campaign Composition · Distinctive Typography',
                'description' => 'A bold, campaign-led shopping composition with distinctive display typography, confident product arrangements, expressive visual hierarchy, and high conversion flow.',
                'best_for' => 'Independent brands, creators, streetwear, sportswear, and distinctive collections.',
                'preview_accent' => '#2563eb',
                'preview_bg' => '#f8fafc',
                'preview_card_bg' => '#ffffff',
                'preview_text' => '#09090b',
                'display_font' => 'Syne',
                'body_font' => 'Space Grotesk',
                'badge' => 'Bold Campaign',
                'recommended_for_types' => ['creator', 'streetwear', 'crafts', 'organic', 'clothing'],
            ],
            'orbit' => [
                'id' => 'orbit',
                'name' => 'Orbit',
                'tagline' => 'Immersive Modern Shopping · Tactile Glass Controls',
                'description' => 'A high-performance modern shop featuring tactile frosted glass controls, subtle dimensional depth, fast product category discovery, and instant cart responsiveness.',
                'best_for' => 'General retail, multi-category stores, footwear, electronics, and fast-moving lifestyle items.',
                'preview_accent' => '#0284c7',
                'preview_bg' => '#ffffff',
                'preview_card_bg' => '#f8fafc',
                'preview_text' => '#0f172a',
                'display_font' => 'Outfit',
                'body_font' => 'Inter',
                'badge' => 'Tactile Modern',
                'recommended_for_types' => ['groceries', 'toys_sports', 'footwear', 'general', 'automotive'],
            ],
        ];
    }

    /**
     * Map legacy preset key to modern preset key safely.
     */
    public function normalizePresetKey(string $key): string
    {
        $k = strtolower(trim($key));
        $aliases = [
            'atelier' => 'velora',
            'signature' => 'noire',
            'object' => 'aurel',
            'commerce' => 'orbit',
            'market' => 'chroma',
        ];

        return $aliases[$k] ?? (isset($this->getPresets()[$k]) ? $k : 'velora');
    }

    /**
     * Get specific preset by key, falling back safely to 'velora'.
     */
    public function getPreset(string $key): array
    {
        $key = $this->normalizePresetKey($key);
        $presets = $this->getPresets();
        return $presets[$key] ?? $presets['velora'];
    }

    /**
     * Recommended preset for a vendor based on vertical or category.
     */
    public function getRecommendedPresetForVendor(Vendor $vendor): string
    {
        if ($vendor->layout_preset) {
            $norm = $this->normalizePresetKey($vendor->layout_preset);
            if (isset($this->getPresets()[$norm])) {
                return $norm;
            }
        }

        $type = $vendor->vendor_type ?? 'clothing';
        foreach ($this->getPresets() as $key => $preset) {
            if (in_array($type, $preset['recommended_for_types'])) {
                return $key;
            }
        }

        return 'velora';
    }

    /**
     * Build default configuration for a given preset and vendor.
     */
    public function getDefaultConfig(string $presetKey, Vendor $vendor): array
    {
        $preset = $this->getPreset($presetKey);
        $social = is_array($vendor->social_links) ? $vendor->social_links : (json_decode($vendor->social_links, true) ?: []);

        $vendorLogo = $vendor->logo ? (str_starts_with($vendor->logo, 'http') ? $vendor->logo : asset('storage/' . $vendor->logo)) : null;
        $vendorBanner = $vendor->banner ? (str_starts_with($vendor->banner, 'http') ? $vendor->banner : asset('storage/' . $vendor->banner)) : null;
        $shopName = $vendor->shop_name ?: 'Boutique Store';
        $tagline = $vendor->tagline ?: ($social['tagline'] ?? 'Curated authentic collections handcrafted with distinction');
        $description = $vendor->description ?: 'Welcome to our official flagship studio. Explore verified creations directly from our workshop with insured doorstep delivery across India.';

        $normalizedKey = $this->normalizePresetKey($presetKey);

        // Preset-specific defaults
        $themes = [
            'velora' => [
                'font_heading' => 'Playfair Display',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#18181b',
                'secondary_color' => '#71717a',
                'accent_color' => '#c59b27',
                'bg_color' => '#fafaf9',
                'surface_color' => '#ffffff',
                'text_color' => '#18181b',
                'border_radius' => 'rounded-sm',
                'content_width' => 'normal',
                'dark_mode' => false,
            ],
            'noire' => [
                'font_heading' => 'Space Grotesk',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#ffffff',
                'secondary_color' => '#a1a1aa',
                'accent_color' => '#f4f4f5',
                'bg_color' => '#121214',
                'surface_color' => '#18181b',
                'text_color' => '#f4f4f5',
                'border_radius' => 'rounded-xl',
                'content_width' => 'normal',
                'dark_mode' => true,
            ],
            'aurel' => [
                'font_heading' => 'Plus Jakarta Sans',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#0f172a',
                'secondary_color' => '#64748b',
                'accent_color' => '#0f172a',
                'bg_color' => '#f9fafb',
                'surface_color' => '#ffffff',
                'text_color' => '#0f172a',
                'border_radius' => 'rounded-lg',
                'content_width' => 'normal',
                'dark_mode' => false,
            ],
            'chroma' => [
                'font_heading' => 'Syne',
                'font_body' => 'Space Grotesk',
                'font_scale' => 'large',
                'primary_color' => '#09090b',
                'secondary_color' => '#64748b',
                'accent_color' => '#2563eb',
                'bg_color' => '#f8fafc',
                'surface_color' => '#ffffff',
                'text_color' => '#09090b',
                'border_radius' => 'rounded-2xl',
                'content_width' => 'wide',
                'dark_mode' => false,
            ],
            'orbit' => [
                'font_heading' => 'Outfit',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#0f172a',
                'secondary_color' => '#475569',
                'accent_color' => '#0284c7',
                'bg_color' => '#ffffff',
                'surface_color' => '#f8fafc',
                'text_color' => '#0f172a',
                'border_radius' => 'rounded-2xl',
                'content_width' => 'wide',
                'dark_mode' => false,
            ],
            // Legacy aliases for backward compatibility
            'atelier' => [
                'font_heading' => 'Playfair Display',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#18181b',
                'secondary_color' => '#71717a',
                'accent_color' => '#c59b27',
                'bg_color' => '#fafaf9',
                'surface_color' => '#ffffff',
                'text_color' => '#18181b',
                'border_radius' => 'rounded-sm',
                'content_width' => 'normal',
                'dark_mode' => false,
            ],
            'object' => [
                'font_heading' => 'Plus Jakarta Sans',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#0f172a',
                'secondary_color' => '#64748b',
                'accent_color' => '#0284c7',
                'bg_color' => '#f8fafc',
                'surface_color' => '#ffffff',
                'text_color' => '#0f172a',
                'border_radius' => 'rounded-xl',
                'content_width' => 'normal',
                'dark_mode' => false,
            ],
            'commerce' => [
                'font_heading' => 'Inter',
                'font_body' => 'Inter',
                'font_scale' => 'normal',
                'primary_color' => '#1e40af',
                'secondary_color' => '#475569',
                'accent_color' => '#f59e0b',
                'bg_color' => '#ffffff',
                'surface_color' => '#f8fafc',
                'text_color' => '#0f172a',
                'border_radius' => 'rounded-2xl',
                'content_width' => 'wide',
                'dark_mode' => false,
            ],
            'market' => [
                'font_heading' => 'Fraunces',
                'font_body' => 'Outfit',
                'font_scale' => 'normal',
                'primary_color' => '#2d4a3e',
                'secondary_color' => '#78716c',
                'accent_color' => '#c85a32',
                'bg_color' => '#fdfbf7',
                'surface_color' => '#ffffff',
                'text_color' => '#27272a',
                'border_radius' => 'rounded-2xl',
                'content_width' => 'normal',
                'dark_mode' => false,
            ],
            'signature' => [
                'font_heading' => 'Syne',
                'font_body' => 'Space Grotesk',
                'font_scale' => 'large',
                'primary_color' => '#000000',
                'secondary_color' => '#71717a',
                'accent_color' => '#22c55e',
                'bg_color' => '#09090b',
                'surface_color' => '#18181b',
                'text_color' => '#ffffff',
                'border_radius' => 'rounded-none',
                'content_width' => 'wide',
                'dark_mode' => true,
            ],
        ];

        $theme = $themes[$presetKey] ?? $themes[$normalizedKey] ?? $themes['velora'];

        // Header configuration
        $header = [
            'layout' => $presetKey === 'commerce' ? 'search_prominent' : ($presetKey === 'object' ? 'minimal' : ($presetKey === 'signature' ? 'stacked' : 'split')),
            'logo_height' => 38,
            'sticky' => true,
            'show_search' => true,
            'show_cart' => true,
            'show_wishlist' => true,
            'show_account' => true,
            'announcement_enabled' => true,
            'announcement_text' => $social['announcement_bar'] ?? '✨ Complimentary pan-India express dispatch on orders above ₹1,999',
            'announcement_link' => $social['announcement_link'] ?? '',
            'announcement_bg' => $presetKey === 'signature' ? '#22c55e' : ($presetKey === 'market' ? '#2d4a3e' : '#18181b'),
            'announcement_text_color' => $presetKey === 'signature' ? '#000000' : '#ffffff',
        ];

        // Default sections by preset
        $sections = $this->buildDefaultSectionsForPreset($presetKey, $vendor, $shopName, $tagline, $description, $vendorBanner);

        // Footer configuration
        $footer = [
            'layout' => 'columns',
            'about_title' => $shopName,
            'about_text' => Str::limit($description, 160),
            'show_social' => true,
            'show_policies' => true,
            'show_contact' => true,
            'show_newsletter' => true,
            'newsletter_title' => 'Receive Private Atelier Dispatches',
            'newsletter_subtext' => 'Direct notifications of new studio drops, fabric weaves, and seasonal reductions.',
            'copyright_text' => "© " . date('Y') . " {$shopName}. Verified K71 Merchant.",
            'bg_color' => $presetKey === 'signature' ? '#000000' : ($presetKey === 'market' ? '#1c2826' : '#0c1310'),
            'text_color' => '#fafaf9',
        ];

        return [
            'preset' => $presetKey,
            'schema_version' => 2,
            'theme' => $theme,
            'brand' => [
                'store_name' => $shopName,
                'tagline' => $tagline,
                'description' => $description,
                'logo_url' => $vendorLogo,
                'banner_url' => $vendorBanner,
                'city' => $vendor->city ?? 'Direct Studio',
                'state' => $vendor->state ?? 'India',
                'established_year' => $social['established_year'] ?? $vendor->created_at?->format('Y') ?? date('Y'),
                'whatsapp' => $social['whatsapp'] ?? $vendor->contact_phone,
                'instagram' => $social['instagram'] ?? null,
                'phone' => $vendor->contact_phone,
                'email' => $vendor->contact_email,
            ],
            'header' => $header,
            'sections' => $sections,
            'footer' => $footer,
        ];
    }

    /**
     * Build appropriate default sections tailored for each preset style.
     */
    protected function buildDefaultSectionsForPreset(string $presetKey, Vendor $vendor, string $shopName, string $tagline, string $description, ?string $vendorBanner): array
    {
        $commonBadges = [
            [
                'icon' => '✦',
                'title' => 'Direct Workshop Dispatch',
                'desc' => 'Every item is packaged and authenticated straight from our studio.',
            ],
            [
                'icon' => '◈',
                'title' => 'Verified Quality Seal',
                'desc' => '100% genuine materials and rigorous pre-dispatch craftsmanship inspection.',
            ],
            [
                'icon' => '⚡',
                'title' => 'Express Insured Transit',
                'desc' => 'Real-time trackable courier shipping across India with safe delivery guarantee.',
            ],
            [
                'icon' => '↺',
                'title' => '7-Day Smooth Exchange',
                'desc' => 'Hassle-free size and product exchange support directly from seller.',
            ],
        ];

        switch ($presetKey) {
            case 'object':
                return [
                    [
                        'id' => 'sec_hero_' . uniqid(),
                        'type' => 'hero_split',
                        'visible' => true,
                        'title' => 'Designed with Purpose',
                        'subtitle' => $tagline,
                        'description' => $description,
                        'badge' => 'New Season Catalog',
                        'cta_primary_text' => 'Explore Collection',
                        'cta_primary_link' => '#catalog',
                        'cta_secondary_text' => 'Specifications',
                        'cta_secondary_link' => '#story',
                        'image_url' => $vendorBanner,
                        'alignment' => 'left',
                    ],
                    [
                        'id' => 'sec_cat_grid_' . uniqid(),
                        'type' => 'category_grid',
                        'visible' => true,
                        'title' => 'Categories & Systems',
                        'subtitle' => 'Browse by product architecture',
                        'layout' => 'cards',
                    ],
                    [
                        'id' => 'sec_featured_' . uniqid(),
                        'type' => 'featured_products',
                        'visible' => true,
                        'title' => 'Selected Releases',
                        'subtitle' => 'Precision crafted items in stock and ready to ship',
                        'source' => 'featured',
                        'limit' => 8,
                        'columns' => 4,
                        'aspect_ratio' => '1:1',
                        'show_quick_buy' => true,
                        'show_specs' => true,
                    ],
                    [
                        'id' => 'sec_badges_' . uniqid(),
                        'type' => 'trust_badges',
                        'visible' => true,
                        'title' => 'Engineering Standards',
                        'items' => $commonBadges,
                    ],
                    [
                        'id' => 'sec_brand_story_' . uniqid(),
                        'type' => 'brand_story',
                        'visible' => true,
                        'title' => 'Our Design Philosophy',
                        'quote' => 'Form follows precision. We eliminate noise so that quality and utility speak.',
                        'text' => $description,
                        'image_url' => $vendorBanner,
                    ],
                    [
                        'id' => 'sec_faq_' . uniqid(),
                        'type' => 'faq_accordion',
                        'visible' => true,
                        'title' => 'Frequently Asked Questions',
                        'items' => [
                            ['q' => 'How long does dispatch take?', 'a' => 'Orders are packaged and dispatched within 24 to 48 hours with real-time tracking.'],
                            ['q' => 'Are products covered by seller warranty?', 'a' => 'Yes, every product carries our authentic manufacturer guarantee and 7-day exchange window.'],
                            ['q' => 'Can I order custom specifications?', 'a' => 'Directly reach out via WhatsApp button to discuss bespoke customization.'],
                        ],
                    ],
                ];

            case 'commerce':
                return [
                    [
                        'id' => 'sec_hero_' . uniqid(),
                        'type' => 'hero_editorial',
                        'visible' => true,
                        'title' => $shopName,
                        'subtitle' => $tagline,
                        'description' => 'Direct savings, fresh inventory, and authentic guaranteed merchandise.',
                        'badge' => '⚡ Limited Edition Drop',
                        'cta_primary_text' => 'Shop All Products',
                        'cta_primary_link' => '#catalog',
                        'cta_secondary_text' => 'Trending Deals',
                        'cta_secondary_link' => '#catalog',
                        'image_url' => $vendorBanner,
                        'alignment' => 'center',
                    ],
                    [
                        'id' => 'sec_cat_bubbles_' . uniqid(),
                        'type' => 'category_bubbles',
                        'visible' => true,
                        'title' => 'Explore Categories',
                        'subtitle' => 'Tap any department to filter catalog',
                    ],
                    [
                        'id' => 'sec_featured_' . uniqid(),
                        'type' => 'featured_products',
                        'visible' => true,
                        'title' => 'Best Sellers & Trending',
                        'subtitle' => 'Most desired products curated by customer demand',
                        'source' => 'all',
                        'limit' => 12,
                        'columns' => 4,
                        'aspect_ratio' => '3:4',
                        'show_quick_buy' => true,
                        'show_specs' => false,
                    ],
                    [
                        'id' => 'sec_badges_' . uniqid(),
                        'type' => 'trust_badges',
                        'visible' => true,
                        'title' => 'Why Buy Direct',
                        'items' => $commonBadges,
                    ],
                    [
                        'id' => 'sec_video_' . uniqid(),
                        'type' => 'video_showcase',
                        'visible' => true,
                        'title' => 'Live Studio Shorts & Reels',
                        'subtitle' => 'See product unboxings, craftsmanship tests, and customer try-ons',
                    ],
                ];

            case 'market':
                return [
                    [
                        'id' => 'sec_hero_' . uniqid(),
                        'type' => 'hero_split',
                        'visible' => true,
                        'title' => 'Authentic Heritage, Hand-Crafted For You',
                        'subtitle' => $tagline,
                        'description' => $description,
                        'badge' => '🌿 Certified Artisan Workshop',
                        'cta_primary_text' => 'Discover the Craft',
                        'cta_primary_link' => '#catalog',
                        'cta_secondary_text' => 'Our Maker Story',
                        'cta_secondary_link' => '#story',
                        'image_url' => $vendorBanner,
                        'alignment' => 'left',
                    ],
                    [
                        'id' => 'sec_brand_story_' . uniqid(),
                        'type' => 'brand_story',
                        'visible' => true,
                        'title' => 'Meet the Artisan Behind the Brand',
                        'quote' => 'Preserving regional Indian craftsmanship with sustainable ethics and timeless beauty.',
                        'text' => $description,
                        'image_url' => $vendorBanner,
                    ],
                    [
                        'id' => 'sec_cat_bubbles_' . uniqid(),
                        'type' => 'category_bubbles',
                        'visible' => true,
                        'title' => 'Artisanal Collections',
                        'subtitle' => 'Handpicked by master weavers and creators',
                    ],
                    [
                        'id' => 'sec_featured_' . uniqid(),
                        'type' => 'featured_products',
                        'visible' => true,
                        'title' => 'Handmade Specialties',
                        'subtitle' => 'Crafted in limited batches with natural materials',
                        'source' => 'featured',
                        'limit' => 8,
                        'columns' => 3,
                        'aspect_ratio' => '3:4',
                        'show_quick_buy' => true,
                        'show_specs' => true,
                    ],
                    [
                        'id' => 'sec_badges_' . uniqid(),
                        'type' => 'trust_badges',
                        'visible' => true,
                        'title' => 'Our Honest Artisan Promise',
                        'items' => $commonBadges,
                    ],
                    [
                        'id' => 'sec_contact_' . uniqid(),
                        'type' => 'contact_policies',
                        'visible' => true,
                        'title' => 'Visit Our Workshop or Message Us',
                        'show_whatsapp' => true,
                        'show_pickup' => true,
                    ],
                ];

            case 'signature':
                return [
                    [
                        'id' => 'sec_hero_' . uniqid(),
                        'type' => 'hero_editorial',
                        'visible' => true,
                        'title' => strtoupper($shopName),
                        'subtitle' => $tagline,
                        'description' => 'Uncompromising aesthetic. High-velocity releases. Worldwide standard.',
                        'badge' => '⚡ EXCLUSIVE DROP LIVE',
                        'cta_primary_text' => 'COP THE DROP',
                        'cta_primary_link' => '#catalog',
                        'cta_secondary_text' => 'LOOKBOOK 2026',
                        'cta_secondary_link' => '#lookbook',
                        'image_url' => $vendorBanner,
                        'alignment' => 'left',
                    ],
                    [
                        'id' => 'sec_featured_' . uniqid(),
                        'type' => 'featured_products',
                        'visible' => true,
                        'title' => 'CURRENT DROP',
                        'subtitle' => 'Limited edition units. Once gone, archived permanently.',
                        'source' => 'featured',
                        'limit' => 6,
                        'columns' => 3,
                        'aspect_ratio' => '3:4',
                        'show_quick_buy' => true,
                        'show_specs' => false,
                    ],
                    [
                        'id' => 'sec_lookbook_' . uniqid(),
                        'type' => 'editorial_lookbook',
                        'visible' => true,
                        'title' => 'CAMPAIGN VOL. 02',
                        'subtitle' => 'Visual narrative captured on 35mm',
                        'quote' => 'Distinctive presence for those who define the culture.',
                    ],
                    [
                        'id' => 'sec_cat_grid_' . uniqid(),
                        'type' => 'category_grid',
                        'visible' => true,
                        'title' => 'PRODUCT ARCHIVE',
                        'subtitle' => 'Explore by category',
                        'layout' => 'minimal_grid',
                    ],
                    [
                        'id' => 'sec_video_' . uniqid(),
                        'type' => 'video_showcase',
                        'visible' => true,
                        'title' => 'MOTION SESSIONS',
                        'subtitle' => 'Lookbooks in action',
                    ],
                ];

            case 'atelier':
            default:
                return [
                    [
                        'id' => 'sec_hero_' . uniqid(),
                        'type' => 'hero_editorial',
                        'visible' => true,
                        'title' => $shopName,
                        'subtitle' => $tagline,
                        'description' => $description,
                        'badge' => 'Atelier Collection 2026',
                        'cta_primary_text' => 'Explore Boutique',
                        'cta_primary_link' => '#catalog',
                        'cta_secondary_text' => 'Atelier Heritage',
                        'cta_secondary_link' => '#story',
                        'image_url' => $vendorBanner,
                        'alignment' => 'center',
                    ],
                    [
                        'id' => 'sec_cat_bubbles_' . uniqid(),
                        'type' => 'category_bubbles',
                        'visible' => true,
                        'title' => 'Couture Editions',
                        'subtitle' => 'Curated by weave & silhouette',
                    ],
                    [
                        'id' => 'sec_lookbook_' . uniqid(),
                        'type' => 'editorial_lookbook',
                        'visible' => true,
                        'title' => 'The Seasonal Lookbook',
                        'subtitle' => 'An editorial exploration of silhouette, texture, and drapery',
                        'quote' => 'True couture lives in the thread count, the patient hand-weave, and the timeless elegance of unhurried design.',
                    ],
                    [
                        'id' => 'sec_featured_' . uniqid(),
                        'type' => 'featured_products',
                        'visible' => true,
                        'title' => 'Spotlight Creations',
                        'subtitle' => 'Signature pieces ready for immediate studio dispatch',
                        'source' => 'featured',
                        'limit' => 8,
                        'columns' => 4,
                        'aspect_ratio' => '3:4',
                        'show_quick_buy' => true,
                        'show_specs' => true,
                    ],
                    [
                        'id' => 'sec_brand_story_' . uniqid(),
                        'type' => 'brand_story',
                        'visible' => true,
                        'title' => 'The Atelier Story',
                        'quote' => 'From our looms directly to your wardrobe. Authentic luxury without intermediaries.',
                        'text' => $description,
                        'image_url' => $vendorBanner,
                    ],
                    [
                        'id' => 'sec_badges_' . uniqid(),
                        'type' => 'trust_badges',
                        'visible' => true,
                        'title' => 'The Atelier Promise',
                        'items' => $commonBadges,
                    ],
                    [
                        'id' => 'sec_contact_' . uniqid(),
                        'type' => 'contact_policies',
                        'visible' => true,
                        'title' => 'Atelier Client Services',
                        'show_whatsapp' => true,
                        'show_pickup' => true,
                    ],
                ];
        }
    }

    /**
     * Library of all supported sections with their metadata for the Add Section modal.
     */
    public function getAvailableSections(): array
    {
        return [
            'hero_editorial' => [
                'type' => 'hero_editorial',
                'name' => 'Editorial Hero Banner',
                'icon' => '🖼️',
                'category' => 'Heroes & Headers',
                'description' => 'Full-width cinematic hero with large headline, subhead, badges, and dual call-to-actions.',
                'default_settings' => [
                    'title' => 'Editorial Showcase',
                    'subtitle' => 'New Season Highlights',
                    'description' => 'Crafted with distinction and unhurried patience.',
                    'badge' => 'Exclusive Edition',
                    'cta_primary_text' => 'Shop Now',
                    'cta_primary_link' => '#catalog',
                    'cta_secondary_text' => 'Learn More',
                    'cta_secondary_link' => '#story',
                    'image_url' => null,
                    'alignment' => 'center',
                ],
            ],
            'hero_split' => [
                'type' => 'hero_split',
                'name' => 'Split Campaign Hero',
                'icon' => '🌓',
                'category' => 'Heroes & Headers',
                'description' => 'Modern two-column layout with bold brand narrative on one side and campaign imagery on the other.',
                'default_settings' => [
                    'title' => 'Pure Form & Function',
                    'subtitle' => 'Curated for Contemporary Living',
                    'description' => 'Explore thoughtfully designed objects built to last a lifetime.',
                    'badge' => 'New Arrivals',
                    'cta_primary_text' => 'Discover Collection',
                    'cta_primary_link' => '#catalog',
                    'cta_secondary_text' => 'Brand Manifesto',
                    'cta_secondary_link' => '#story',
                    'image_url' => null,
                    'alignment' => 'left',
                ],
            ],
            'category_bubbles' => [
                'type' => 'category_bubbles',
                'name' => 'Story Category Bubbles',
                'icon' => '🫧',
                'category' => 'Discovery',
                'description' => 'Interactive circular story bubbles showing real vendor categories with live product counts.',
                'default_settings' => [
                    'title' => 'Curated Departments',
                    'subtitle' => 'Tap any circle to filter the store catalog',
                ],
            ],
            'category_grid' => [
                'type' => 'category_grid',
                'name' => 'Category Card Grid',
                'icon' => '🔲',
                'category' => 'Discovery',
                'description' => 'Architectural grid of category visual cards with image backgrounds and item counts.',
                'default_settings' => [
                    'title' => 'Explore by Category',
                    'subtitle' => 'Browse our distinct product disciplines',
                    'layout' => 'cards',
                ],
            ],
            'featured_products' => [
                'type' => 'featured_products',
                'name' => 'Featured Product Grid',
                'icon' => '🛍️',
                'category' => 'Commerce',
                'description' => 'Responsive showcase of vendor products with instant add-to-cart, badges, and variant pills.',
                'default_settings' => [
                    'title' => 'Featured Products',
                    'subtitle' => 'Handpicked selections from our workshop',
                    'source' => 'featured',
                    'limit' => 8,
                    'columns' => 4,
                    'aspect_ratio' => '3:4',
                    'show_quick_buy' => true,
                    'show_specs' => true,
                ],
            ],
            'editorial_lookbook' => [
                'type' => 'editorial_lookbook',
                'name' => 'Editorial Lookbook',
                'icon' => '📖',
                'category' => 'Storytelling',
                'description' => 'Magazine-style asymmetric layout with campaign photo arrangements and pull quotes.',
                'default_settings' => [
                    'title' => 'The Seasonal Lookbook',
                    'subtitle' => 'An editorial exploration of texture, craft, and silhouette',
                    'quote' => 'True craftsmanship lives in the fine weave, the disciplined cut, and the longevity of honest materials.',
                ],
            ],
            'brand_story' => [
                'type' => 'brand_story',
                'name' => 'Brand Story & Maker Note',
                'icon' => '🖋️',
                'category' => 'Storytelling',
                'description' => 'Emotional founder introduction, workshop heritage, and brand philosophy.',
                'default_settings' => [
                    'title' => 'The Artisan Story',
                    'quote' => 'Preserving authentic tradition with modern ethics and uncompromising standards.',
                    'text' => 'Welcome to our studio. Every piece in our collection represents generations of regional craftsmanship.',
                    'image_url' => null,
                ],
            ],
            'trust_badges' => [
                'type' => 'trust_badges',
                'name' => 'Trust & Service Guarantees',
                'icon' => '🛡️',
                'category' => 'Trust & Conversion',
                'description' => '4 reassuring service seals (Direct Dispatch, Safe Courier, Verified Quality, Smooth Returns).',
                'default_settings' => [
                    'title' => 'Our Buyer Guarantees',
                    'items' => [
                        ['icon' => '✦', 'title' => 'Direct Studio Dispatch', 'desc' => 'Direct from the source without retail markups.'],
                        ['icon' => '◈', 'title' => '100% Genuine Quality', 'desc' => 'Hand-inspected before sealing.'],
                        ['icon' => '⚡', 'title' => 'Fast Express Shipping', 'desc' => 'Trackable insured transit across India.'],
                        ['icon' => '↺', 'title' => 'Easy 7-Day Exchange', 'desc' => 'Quick size exchange with seller support.'],
                    ],
                ],
            ],
            'faq_accordion' => [
                'type' => 'faq_accordion',
                'name' => 'FAQ Accordion',
                'icon' => '❓',
                'category' => 'Trust & Conversion',
                'description' => 'Expandable answers to common questions about sizing, delivery, and custom orders.',
                'default_settings' => [
                    'title' => 'Frequently Asked Questions',
                    'items' => [
                        ['q' => 'How soon will my order be dispatched?', 'a' => 'Orders are processed and dispatched within 24-48 business hours with tracking details.'],
                        ['q' => 'Can I exchange if the size does not fit?', 'a' => 'Yes, we offer smooth 7-day exchanges for unworn products with original tags.'],
                        ['q' => 'Are all products authentic?', 'a' => 'All items are 100% genuine and produced directly by our verified studio.'],
                    ],
                ],
            ],
            'video_showcase' => [
                'type' => 'video_showcase',
                'name' => 'Video & Shorts Showcase',
                'icon' => '🎬',
                'category' => 'Media',
                'description' => 'Engaging reel rail featuring the vendor’s published video shorts.',
                'default_settings' => [
                    'title' => 'Studio in Motion',
                    'subtitle' => 'Watch unboxings, craftsmanship reels, and behind-the-scenes shorts',
                ],
            ],
            'contact_policies' => [
                'type' => 'contact_policies',
                'name' => 'Contact, Studio & Policies',
                'icon' => '📍',
                'category' => 'Information',
                'description' => 'Workshop address, WhatsApp direct chat, contact phone, and store policies.',
                'default_settings' => [
                    'title' => 'Get in Touch with our Studio',
                    'show_whatsapp' => true,
                    'show_pickup' => true,
                ],
            ],
        ];
    }

    /**
     * Sanitize and validate incoming configuration input to prevent injection or corruption.
     */
    public function sanitizeConfig(array $input, Vendor $vendor): array
    {
        $presetKey = in_array($input['preset'] ?? '', ['atelier', 'object', 'commerce', 'market', 'signature']) 
            ? $input['preset'] 
            : 'atelier';

        $safeHex = function ($color, $default) {
            return (is_string($color) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color)) ? $color : $default;
        };

        $allowedFonts = [
            'Playfair Display', 'Cormorant Garamond', 'Cinzel', 'Fraunces',
            'Inter', 'Plus Jakarta Sans', 'Outfit', 'Space Grotesk', 'Syne',
            'DM Sans', 'Manrope', 'Montserrat', 'system-ui'
        ];

        $safeFont = function ($font, $default) use ($allowedFonts) {
            return in_array($font, $allowedFonts) ? $font : $default;
        };

        $safeText = function ($str, $max = 300) {
            return Str::limit(strip_tags((string) $str), $max);
        };

        $safeUrl = function ($url) {
            $url = trim((string) $url);
            if (empty($url)) return '';
            if (str_starts_with($url, '#') || str_starts_with($url, '/') || filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
            return '';
        };

        // 1. Theme Sanitization
        $rawTheme = $input['theme'] ?? [];
        $theme = [
            'font_heading' => $safeFont($rawTheme['font_heading'] ?? null, 'Playfair Display'),
            'font_body' => $safeFont($rawTheme['font_body'] ?? null, 'Inter'),
            'font_scale' => in_array($rawTheme['font_scale'] ?? '', ['compact', 'normal', 'large']) ? $rawTheme['font_scale'] : 'normal',
            'primary_color' => $safeHex($rawTheme['primary_color'] ?? null, '#18181b'),
            'secondary_color' => $safeHex($rawTheme['secondary_color'] ?? null, '#71717a'),
            'accent_color' => $safeHex($rawTheme['accent_color'] ?? null, '#c59b27'),
            'bg_color' => $safeHex($rawTheme['bg_color'] ?? null, '#fafaf9'),
            'surface_color' => $safeHex($rawTheme['surface_color'] ?? null, '#ffffff'),
            'text_color' => $safeHex($rawTheme['text_color'] ?? null, '#18181b'),
            'border_radius' => in_array($rawTheme['border_radius'] ?? '', ['rounded-none', 'rounded-sm', 'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-full']) ? $rawTheme['border_radius'] : 'rounded-lg',
            'content_width' => in_array($rawTheme['content_width'] ?? '', ['narrow', 'normal', 'wide', 'full']) ? $rawTheme['content_width'] : 'normal',
            'dark_mode' => (bool) ($rawTheme['dark_mode'] ?? false),
        ];

        // 2. Brand Identity Sanitization
        $rawBrand = $input['brand'] ?? [];
        $brand = [
            'store_name' => $safeText($rawBrand['store_name'] ?? $vendor->shop_name, 100),
            'tagline' => $safeText($rawBrand['tagline'] ?? $vendor->tagline, 200),
            'description' => $safeText($rawBrand['description'] ?? $vendor->description, 1000),
            'logo_url' => $safeUrl($rawBrand['logo_url'] ?? $vendor->logo_url),
            'banner_url' => $safeUrl($rawBrand['banner_url'] ?? $vendor->banner_url),
            'city' => $safeText($rawBrand['city'] ?? $vendor->city, 60),
            'state' => $safeText($rawBrand['state'] ?? $vendor->state, 60),
            'established_year' => (int) ($rawBrand['established_year'] ?? $vendor->created_at?->format('Y') ?? date('Y')),
            'whatsapp' => preg_replace('/[^0-9]/', '', (string) ($rawBrand['whatsapp'] ?? $vendor->contact_phone)),
            'instagram' => $safeText($rawBrand['instagram'] ?? '', 80),
            'phone' => $safeText($rawBrand['phone'] ?? $vendor->contact_phone, 30),
            'email' => filter_var($rawBrand['email'] ?? $vendor->contact_email, FILTER_VALIDATE_EMAIL) ?: $vendor->contact_email,
        ];

        // 3. Header Sanitization
        $rawHeader = $input['header'] ?? [];
        $header = [
            'layout' => in_array($rawHeader['layout'] ?? '', ['minimal', 'split', 'centered', 'stacked', 'search_prominent']) ? $rawHeader['layout'] : 'split',
            'logo_height' => max(20, min(100, (int) ($rawHeader['logo_height'] ?? 38))),
            'sticky' => (bool) ($rawHeader['sticky'] ?? true),
            'show_search' => (bool) ($rawHeader['show_search'] ?? true),
            'show_cart' => (bool) ($rawHeader['show_cart'] ?? true),
            'show_wishlist' => (bool) ($rawHeader['show_wishlist'] ?? true),
            'show_account' => (bool) ($rawHeader['show_account'] ?? true),
            'announcement_enabled' => (bool) ($rawHeader['announcement_enabled'] ?? true),
            'announcement_text' => $safeText($rawHeader['announcement_text'] ?? '', 200),
            'announcement_link' => $safeUrl($rawHeader['announcement_link'] ?? ''),
            'announcement_bg' => $safeHex($rawHeader['announcement_bg'] ?? null, '#18181b'),
            'announcement_text_color' => $safeHex($rawHeader['announcement_text_color'] ?? null, '#ffffff'),
        ];

        // 4. Sections Sanitization
        $rawSections = is_array($input['sections'] ?? null) ? $input['sections'] : [];
        $availableSections = $this->getAvailableSections();
        $sections = [];

        foreach ($rawSections as $sec) {
            if (!is_array($sec) || empty($sec['type']) || !isset($availableSections[$sec['type']])) {
                continue;
            }

            $type = $sec['type'];
            $secId = !empty($sec['id']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $sec['id']) : 'sec_' . uniqid();
            $isVisible = isset($sec['visible']) ? (bool) $sec['visible'] : true;

            $cleanSec = [
                'id' => $secId,
                'type' => $type,
                'visible' => $isVisible,
            ];

            // Clean common fields
            if (isset($sec['title'])) $cleanSec['title'] = $safeText($sec['title'], 150);
            if (isset($sec['subtitle'])) $cleanSec['subtitle'] = $safeText($sec['subtitle'], 250);
            if (isset($sec['description'])) $cleanSec['description'] = $safeText($sec['description'], 1000);
            if (isset($sec['badge'])) $cleanSec['badge'] = $safeText($sec['badge'], 60);
            if (isset($sec['quote'])) $cleanSec['quote'] = $safeText($sec['quote'], 400);
            if (isset($sec['text'])) $cleanSec['text'] = $safeText($sec['text'], 1500);
            if (isset($sec['cta_primary_text'])) $cleanSec['cta_primary_text'] = $safeText($sec['cta_primary_text'], 50);
            if (isset($sec['cta_primary_link'])) $cleanSec['cta_primary_link'] = $safeUrl($sec['cta_primary_link']);
            if (isset($sec['cta_secondary_text'])) $cleanSec['cta_secondary_text'] = $safeText($sec['cta_secondary_text'], 50);
            if (isset($sec['cta_secondary_link'])) $cleanSec['cta_secondary_link'] = $safeUrl($sec['cta_secondary_link']);
            if (isset($sec['image_url'])) $cleanSec['image_url'] = $safeUrl($sec['image_url']);
            if (isset($sec['alignment'])) $cleanSec['alignment'] = in_array($sec['alignment'], ['left', 'center', 'right']) ? $sec['alignment'] : 'center';

            // Clean section-specific controls
            if ($type === 'featured_products') {
                $cleanSec['source'] = in_array($sec['source'] ?? '', ['all', 'featured', 'newest', 'sale']) ? $sec['source'] : 'featured';
                $cleanSec['limit'] = max(3, min(24, (int) ($sec['limit'] ?? 8)));
                $cleanSec['columns'] = in_array((int) ($sec['columns'] ?? 4), [2, 3, 4]) ? (int) $sec['columns'] : 4;
                $cleanSec['aspect_ratio'] = in_array($sec['aspect_ratio'] ?? '', ['1:1', '3:4', '4:5', 'natural']) ? $sec['aspect_ratio'] : '3:4';
                $cleanSec['show_quick_buy'] = (bool) ($sec['show_quick_buy'] ?? true);
                $cleanSec['show_specs'] = (bool) ($sec['show_specs'] ?? true);

                // If specific product IDs selected, ensure they belong to this vendor!
                if (!empty($sec['selected_product_ids']) && is_array($sec['selected_product_ids'])) {
                    $validIds = Product::where('vendor_id', $vendor->id)
                        ->whereIn('id', $sec['selected_product_ids'])
                        ->pluck('id')
                        ->all();
                    $cleanSec['selected_product_ids'] = $validIds;
                }
            } elseif ($type === 'trust_badges' && !empty($sec['items']) && is_array($sec['items'])) {
                $cleanItems = [];
                foreach (array_slice($sec['items'], 0, 6) as $item) {
                    if (is_array($item)) {
                        $cleanItems[] = [
                            'icon' => $safeText($item['icon'] ?? '✦', 10),
                            'title' => $safeText($item['title'] ?? '', 60),
                            'desc' => $safeText($item['desc'] ?? '', 180),
                        ];
                    }
                }
                $cleanSec['items'] = $cleanItems;
            } elseif ($type === 'faq_accordion' && !empty($sec['items']) && is_array($sec['items'])) {
                $cleanItems = [];
                foreach (array_slice($sec['items'], 0, 10) as $item) {
                    if (is_array($item)) {
                        $cleanItems[] = [
                            'q' => $safeText($item['q'] ?? '', 150),
                            'a' => $safeText($item['a'] ?? '', 500),
                        ];
                    }
                }
                $cleanSec['items'] = $cleanItems;
            } elseif ($type === 'contact_policies') {
                $cleanSec['show_whatsapp'] = (bool) ($sec['show_whatsapp'] ?? true);
                $cleanSec['show_pickup'] = (bool) ($sec['show_pickup'] ?? true);
            }

            $sections[] = $cleanSec;
        }

        // If all sections removed, ensure at least one featured products section exists
        if (empty($sections)) {
            $sections = $this->buildDefaultSectionsForPreset($presetKey, $vendor, $brand['store_name'], $brand['tagline'], $brand['description'], $brand['banner_url']);
        }

        // 5. Footer Sanitization
        $rawFooter = $input['footer'] ?? [];
        $footer = [
            'layout' => in_array($rawFooter['layout'] ?? '', ['columns', 'minimal', 'centered']) ? $rawFooter['layout'] : 'columns',
            'about_title' => $safeText($rawFooter['about_title'] ?? $brand['store_name'], 100),
            'about_text' => $safeText($rawFooter['about_text'] ?? $brand['description'], 300),
            'show_social' => (bool) ($rawFooter['show_social'] ?? true),
            'show_policies' => (bool) ($rawFooter['show_policies'] ?? true),
            'show_contact' => (bool) ($rawFooter['show_contact'] ?? true),
            'show_newsletter' => (bool) ($rawFooter['show_newsletter'] ?? true),
            'newsletter_title' => $safeText($rawFooter['newsletter_title'] ?? 'Private Studio Dispatches', 100),
            'newsletter_subtext' => $safeText($rawFooter['newsletter_subtext'] ?? 'Direct updates on new arrivals and seasonal reductions.', 200),
            'copyright_text' => $safeText($rawFooter['copyright_text'] ?? "© " . date('Y') . " {$brand['store_name']}. Verified K71 Merchant.", 200),
            'bg_color' => $safeHex($rawFooter['bg_color'] ?? null, '#0c1310'),
            'text_color' => $safeHex($rawFooter['text_color'] ?? null, '#fafaf9'),
        ];

        return [
            'preset' => $presetKey,
            'schema_version' => 2,
            'theme' => $theme,
            'brand' => $brand,
            'header' => $header,
            'sections' => $sections,
            'footer' => $footer,
        ];
    }
}

