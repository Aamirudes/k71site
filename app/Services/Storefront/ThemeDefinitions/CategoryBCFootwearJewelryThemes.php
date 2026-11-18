<?php

namespace App\Services\Storefront\ThemeDefinitions;

/**
 * Category B (13–17): Sneakers, Footwear & Performance.
 * Category C (18–22): Watches, Jewelry & Premium Objects.
 */
class CategoryBCFootwearJewelryThemes
{
    public static function definitions(): array
    {
        return [
            // --- CATEGORY B: SNEAKERS, FOOTWEAR & PERFORMANCE ---
            'sneaker-vault' => [
                'id' => 'sneaker-vault',
                'slug' => 'sneaker-vault',
                'number' => 13,
                'name' => 'Sneaker Vault',
                'category' => 'footwear_performance',
                'compatible_categories' => ['footwear', 'streetwear', 'sports'],
                'design_attributes' => ['Isolated Shoe Stage', 'Size Matrix Cards', 'Collector Zoom PDP'],
                'tagline' => 'Premium sneaker collector vault with isolated studio staging and size matrix',
                'description' => 'A collector-grade sneaker storefront built around dramatic isolated shoe framing, instant size-availability matrices on cards, zoomable galleries, and razor-clear stock status.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f3f3f5', 'surface' => '#ffffff', 'surface_alt' => '#14151a',
                    'text' => '#111217', 'muted' => '#5b5e6b', 'accent' => '#ea580c',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(17, 18, 23, 0.12)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Barlow Condensed', 'body_font' => 'Inter',
                    'heading_weight' => '800', 'heading_tracking' => '0.01em', 'label_style' => 'bold-condensed',
                ],
                'motion' => ['profile' => 'kinetic-snap', 'duration_ms' => 300, 'easing' => 'cubic-bezier(0.16, 1, 0.3, 1)', 'enable_parallax' => false, 'stagger_ms' => 35],
                'layout' => [
                    'header_template' => 'brutalist-bar', 'hero_template' => 'sneaker-vault-stage',
                    'card_template' => 'sneaker-vault', 'pdp_template' => 'sneaker-collector',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '1/1', 'default_columns' => 4, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'horizontal-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Vault Releases'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'motion-lab' => [
                'id' => 'motion-lab',
                'slug' => 'motion-lab',
                'number' => 14,
                'name' => 'Motion Lab',
                'category' => 'footwear_performance',
                'compatible_categories' => ['sports', 'footwear', 'clothing'],
                'design_attributes' => ['Diagonal Athletic Geometry', 'Spec-Forward Cards', 'Energetic Transitions'],
                'tagline' => 'Performance sportswear with controlled diagonal geometry and spec clarity',
                'description' => 'A kinetic sportswear and training flagship pairing crisp white and slate surfaces with cobalt performance accents, selective diagonal section cuts, and prominent technical specifications.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f6f8fb', 'surface' => '#ffffff', 'surface_alt' => '#0f172a',
                    'text' => '#0f172a', 'muted' => '#475569', 'accent' => '#0284c7',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(15, 23, 42, 0.12)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '700', 'heading_tracking' => '-0.03em', 'label_style' => 'bold-condensed',
                ],
                'motion' => ['profile' => 'runway-directional', 'duration_ms' => 340, 'easing' => 'cubic-bezier(0.16, 1, 0.3, 1)', 'enable_parallax' => true, 'stagger_ms' => 40],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'diagonal-performance',
                    'card_template' => 'sneaker-vault', 'pdp_template' => 'tech-comparison',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'monolith-tabs'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Performance Kit'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'sole-archive' => [
                'id' => 'sole-archive',
                'slug' => 'sole-archive',
                'number' => 15,
                'name' => 'Sole Archive',
                'category' => 'footwear_performance',
                'compatible_categories' => ['footwear', 'fashion', 'accessories'],
                'design_attributes' => ['Archival Numbering', 'Editorial Silhouette Index', 'Structured Metadata'],
                'tagline' => 'Curated footwear archive with catalog indexing and silhouette galleries',
                'description' => 'An archival footwear catalog balancing quiet editorial stone surfaces, numbered collection indexes, silhouette-led galleries, and refined technical metadata.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f5f4f0', 'surface' => '#ffffff', 'surface_alt' => '#e8e6de',
                    'text' => '#181816', 'muted' => '#66655e', 'accent' => '#3f4a3c',
                    'accent_contrast' => '#f5f4f0', 'border' => 'rgba(24, 24, 22, 0.13)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'DM Sans', 'body_font' => 'Inter',
                    'heading_weight' => '600', 'heading_tracking' => '-0.025em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'quiet-gallery', 'duration_ms' => 460, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'gallery-minimal', 'hero_template' => 'magazine-cover-spread',
                    'card_template' => 'gallery-plinth', 'pdp_template' => 'sneaker-collector',
                    'footer_template' => 'colophon-hairline', 'collection_layout' => 'gallery-3col',
                    'card_aspect' => '1/1', 'default_columns' => 3, 'radius' => '4px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'hairline-index'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Footwear Archive'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'velocity' => [
                'id' => 'velocity',
                'slug' => 'velocity',
                'number' => 16,
                'name' => 'Velocity',
                'category' => 'footwear_performance',
                'compatible_categories' => ['sports', 'footwear', 'outdoor'],
                'design_attributes' => ['Speed Hierarchy', 'Utility Spec Matrix', 'Dark Track Accent'],
                'tagline' => 'Running, training and performance gear built for rapid specification scanning',
                'description' => 'An athletic equipment storefront pairing stark obsidian-graphite headers with crisp technical comparison cards, prominent fit guidance, and ultra-responsive filter discovery.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#0e1116', 'surface' => '#161b23', 'surface_alt' => '#202733',
                    'text' => '#f1f5f9', 'muted' => '#94a3b8', 'accent' => '#10b981',
                    'accent_contrast' => '#052016', 'border' => 'rgba(148, 163, 184, 0.16)', 'is_dark' => true,
                ],
                'typography' => [
                    'heading_font' => 'Barlow Condensed', 'body_font' => 'Inter',
                    'heading_weight' => '800', 'heading_tracking' => '0.02em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'kinetic-snap', 'duration_ms' => 260, 'easing' => 'cubic-bezier(0.2, 0, 0, 1)', 'enable_parallax' => false, 'stagger_ms' => 30],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'diagonal-performance',
                    'card_template' => 'tech-spec-matrix', 'pdp_template' => 'tech-comparison',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'dark-4col',
                    'card_aspect' => '4/3', 'default_columns' => 4, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Training & Run Gear'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'minimal-sole' => [
                'id' => 'minimal-sole',
                'slug' => 'minimal-sole',
                'number' => 17,
                'name' => 'Minimal Sole',
                'category' => 'footwear_performance',
                'compatible_categories' => ['footwear', 'fashion', 'luxury'],
                'design_attributes' => ['Pure Monochrome Framing', 'Material Craft Focus', 'Borderless Plinths'],
                'tagline' => 'Minimal premium footwear boutique focused on silhouette and craftsmanship',
                'description' => 'A stripped-back luxury shoe gallery using quiet alabaster planes, generous margins, clean square shoe framing, and uncluttered variant selectors.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#fafaf9', 'surface' => '#ffffff', 'surface_alt' => '#f0efec',
                    'text' => '#171717', 'muted' => '#737373', 'accent' => '#171717',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(23, 23, 23, 0.08)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Outfit', 'body_font' => 'Inter',
                    'heading_weight' => '400', 'heading_tracking' => '-0.025em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'quiet-gallery', 'duration_ms' => 480, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'gallery-minimal', 'hero_template' => 'white-plinth-gallery',
                    'card_template' => 'sneaker-vault', 'pdp_template' => 'gallery-minimal',
                    'footer_template' => 'maison-minimal', 'collection_layout' => 'gallery-3col',
                    'card_aspect' => '1/1', 'default_columns' => 3, 'radius' => '4px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Footwear Collection'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'hairline-index'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            // --- CATEGORY C: WATCHES, JEWELRY & PREMIUM OBJECTS ---
            'chronograph' => [
                'id' => 'chronograph',
                'slug' => 'chronograph',
                'number' => 18,
                'name' => 'Chronograph',
                'category' => 'watches_jewelry',
                'compatible_categories' => ['jewelry', 'watches', 'luxury', 'accessories'],
                'design_attributes' => ['Horology Spec Table', 'Deep Slate Dial Palette', 'Macro Zoom Gallery'],
                'tagline' => 'Luxury watch retailer with precision specification tables and macro galleries',
                'description' => 'A horology flagship crafted in deep slate-ebony and brushed silver accents, pairing macro-zoom watch galleries with structured technical specification dossiers and warranty trust panels.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#0b0e13', 'surface' => '#131820', 'surface_alt' => '#1c232e',
                    'text' => '#f2f4f7', 'muted' => '#97a1b0', 'accent' => '#c5a467',
                    'accent_contrast' => '#0b0e13', 'border' => 'rgba(197, 164, 103, 0.22)', 'is_dark' => true,
                ],
                'typography' => [
                    'heading_font' => 'Cormorant Garamond', 'body_font' => 'Inter',
                    'heading_weight' => '600', 'heading_tracking' => '0.01em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'obsidian-curtain', 'duration_ms' => 620, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 55],
                'layout' => [
                    'header_template' => 'heritage-framed', 'hero_template' => 'horology-macro-stage',
                    'card_template' => 'horology-spec', 'pdp_template' => 'horology-precision',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'dark-4col',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '2px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'monolith-trio'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Timepieces'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'jewelle' => [
                'id' => 'jewelle',
                'slug' => 'jewelle',
                'number' => 19,
                'name' => 'Jewelle',
                'category' => 'watches_jewelry',
                'compatible_categories' => ['jewelry', 'luxury', 'accessories'],
                'design_attributes' => ['Delicate Serif Poise', 'Macro Detail Focus', 'Spacious Ivory Gallery'],
                'tagline' => 'Contemporary fine jewelry boutique with luminous spacing and detail zoom',
                'description' => 'An airy contemporary jewelry boutique uniting delicate Playfair serif headers, pearl-white surfaces, muted champagne borders, unobstructed macro photography, and smooth zoom interactions.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#fcfaf7', 'surface' => '#ffffff', 'surface_alt' => '#f4eee6',
                    'text' => '#1e1b18', 'muted' => '#78716a', 'accent' => '#9a7b4f',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(154, 123, 79, 0.18)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Playfair Display', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '500', 'heading_tracking' => '0em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 600, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 55],
                'layout' => [
                    'header_template' => 'couture-centered', 'hero_template' => 'white-plinth-gallery',
                    'card_template' => 'horology-spec', 'pdp_template' => 'gallery-minimal',
                    'footer_template' => 'maison-minimal', 'collection_layout' => 'gallery-3col',
                    'card_aspect' => '1/1', 'default_columns' => 3, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'alternating-plinth'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Fine Pieces'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'goldline' => [
                'id' => 'goldline',
                'slug' => 'goldline',
                'number' => 20,
                'name' => 'Goldline',
                'category' => 'watches_jewelry',
                'compatible_categories' => ['jewelry', 'luxury', 'bridal', 'gifting'],
                'design_attributes' => ['Warm Heirloom Palette', 'Weight & Material Clarity', 'Regal Editorial Hero'],
                'tagline' => 'Premium gold and occasion jewelry with warm high-contrast editorial elegance',
                'description' => 'An heirloom gold and occasion jewelry storefront featuring warm ivory and deep espresso contrast, refined gilded borders, prominent weight/material attributes, and regal campaign composition.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f8f2e7', 'surface' => '#fffcf6', 'surface_alt' => '#ecdfc8',
                    'text' => '#261b10', 'muted' => '#6b5642', 'accent' => '#8f651d',
                    'accent_contrast' => '#fffcf6', 'border' => 'rgba(143, 101, 29, 0.22)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Cormorant Garamond', 'body_font' => 'DM Sans',
                    'heading_weight' => '700', 'heading_tracking' => '0em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'measured-heritage', 'duration_ms' => 640, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 55],
                'layout' => [
                    'header_template' => 'heritage-framed', 'hero_template' => 'heritage-framed-banner',
                    'card_template' => 'horology-spec', 'pdp_template' => 'horology-precision',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'formal-3col',
                    'card_aspect' => '1/1', 'default_columns' => 3, 'radius' => '4px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Occasion & Gold Collection'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'artifact' => [
                'id' => 'artifact',
                'slug' => 'artifact',
                'number' => 21,
                'name' => 'Artifact',
                'category' => 'watches_jewelry',
                'compatible_categories' => ['crafts', 'home_living', 'collectibles', 'art'],
                'design_attributes' => ['Museum Object Plinths', 'Asymmetric Curated Grid', 'Maker Provenance'],
                'tagline' => 'Collectibles, design objects and artisan pieces presented like a museum archive',
                'description' => 'An object-focused gallery storefront emphasizing sculptural product silhouettes, asymmetric curated placement, calm clay-stone surfaces, and maker/material storytelling.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f2f0eb', 'surface' => '#faf9f6', 'surface_alt' => '#e4e0d8',
                    'text' => '#1c1b18', 'muted' => '#66635b', 'accent' => '#594a3c',
                    'accent_contrast' => '#faf9f6', 'border' => 'rgba(28, 27, 24, 0.14)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Fraunces', 'body_font' => 'Inter',
                    'heading_weight' => '500', 'heading_tracking' => '-0.02em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'quiet-gallery', 'duration_ms' => 580, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 60],
                'layout' => [
                    'header_template' => 'gallery-minimal', 'hero_template' => 'asymmetric-campaign',
                    'card_template' => 'gallery-plinth', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'colophon-hairline', 'collection_layout' => 'asymmetric-mixed',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '2px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'editorial_feature', 'enabled' => true],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Collected Works'],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'luxe-objects' => [
                'id' => 'luxe-objects',
                'slug' => 'luxe-objects',
                'number' => 22,
                'name' => 'Luxe Objects',
                'category' => 'watches_jewelry',
                'compatible_categories' => ['accessories', 'home_living', 'luxury', 'gifting'],
                'design_attributes' => ['Sculptural Proportions', 'Floating Pill Navigation', 'Refined Glass Accents'],
                'tagline' => 'Premium lifestyle accessories and designer objects with spacious curation',
                'description' => 'A contemporary luxury objects gallery pairing cool alabaster surfaces, sculptural product proportions, refined translucent navigation pills, and structured object detail sheets.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f5f5f7', 'surface' => '#ffffff', 'surface_alt' => '#e8e8ed',
                    'text' => '#141418', 'muted' => '#63636e', 'accent' => '#3730a3',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(20, 20, 24, 0.10)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Outfit', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '500', 'heading_tracking' => '-0.025em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 500, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 50],
                'layout' => [
                    'header_template' => 'floating-pill-nav', 'hero_template' => 'studio-split-offset',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'gallery-minimal',
                    'footer_template' => 'studio-compact', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '4/3', 'default_columns' => 3, 'radius' => '14px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'alternating-plinth'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Designer Objects'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],
        ];
    }
}
