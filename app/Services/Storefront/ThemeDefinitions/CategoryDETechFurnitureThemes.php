<?php

namespace App\Services\Storefront\ThemeDefinitions;

/**
 * Category D (23–27): Technology & Electronics.
 * Category E (28–32): Furniture, Home & Interiors.
 */
class CategoryDETechFurnitureThemes
{
    public static function definitions(): array
    {
        return [
            // --- CATEGORY D: TECHNOLOGY & ELECTRONICS ---
            'future-form' => [
                'id' => 'future-form',
                'slug' => 'future-form',
                'number' => 23,
                'name' => 'Future Form',
                'category' => 'tech_electronics',
                'compatible_categories' => ['electronics', 'gadgets', 'smart_home'],
                'design_attributes' => ['Product-Led Geometric Hero', 'Crisp Spec Hierarchy', 'Precision Hardware PDP'],
                'tagline' => 'Premium modern technology retailer with clean geometric staging',
                'description' => 'A modern hardware flagship defined by centered product-led hero staging, surgical geometric typography, readable specification blocks, and calm precision interactions without neon clutter.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#ffffff', 'surface' => '#f5f6f8', 'surface_alt' => '#eaedf2',
                    'text' => '#111318', 'muted' => '#596070', 'accent' => '#0066cc',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(17, 19, 24, 0.10)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Plus Jakarta Sans', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.03em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'crisp-modern', 'duration_ms' => 380, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 40],
                'layout' => [
                    'header_template' => 'floating-pill-nav', 'hero_template' => 'tech-product-centerpiece',
                    'card_template' => 'tech-spec-matrix', 'pdp_template' => 'tech-comparison',
                    'footer_template' => 'studio-compact', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '1/1', 'default_columns' => 4, 'radius' => '14px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Hardware Lineup'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'circuit' => [
                'id' => 'circuit',
                'slug' => 'circuit',
                'number' => 24,
                'name' => 'Circuit',
                'category' => 'tech_electronics',
                'compatible_categories' => ['electronics', 'machinery', 'b2b', 'automotive'],
                'design_attributes' => ['Search-First Utility Header', 'Spec-Dense Cards', 'Comparison PDP'],
                'tagline' => 'Information-dense electronics specialist built for fast filtering and specs',
                'description' => 'An engineered technical electronics catalog featuring a prominent search header, structured category filters, spec-dense SKU cards, and comparison-ready product specification tables.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f3f5f8', 'surface' => '#ffffff', 'surface_alt' => '#e5eaf0',
                    'text' => '#0f172a', 'muted' => '#475569', 'accent' => '#2563eb',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(15, 23, 42, 0.13)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.02em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'utility-instant', 'duration_ms' => 180, 'easing' => 'ease-out', 'enable_parallax' => false, 'stagger_ms' => 20],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'compact-catalog-banner',
                    'card_template' => 'tech-spec-matrix', 'pdp_template' => 'tech-comparison',
                    'footer_template' => 'utility-directory', 'collection_layout' => 'dense-4col',
                    'card_aspect' => '4/3', 'default_columns' => 4, 'radius' => '6px',
                ],
                'default_sections' => [
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'monolith-tabs'],
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Technical Catalogue'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'mono-tech' => [
                'id' => 'mono-tech',
                'slug' => 'mono-tech',
                'number' => 25,
                'name' => 'Mono Tech',
                'category' => 'tech_electronics',
                'compatible_categories' => ['electronics', 'accessories', 'gadgets'],
                'design_attributes' => ['Strict Monochrome System', 'Swiss Grid Discipline', 'Zero Decoration'],
                'tagline' => 'Minimal monochrome electronics store with Swiss grid precision',
                'description' => 'A stark monochrome hardware store defined by Swiss grid discipline, crisp black-and-white contrast, oversized hardware photography, and clear tabular specifications.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#ffffff', 'surface' => '#ffffff', 'surface_alt' => '#f2f2f2',
                    'text' => '#0a0a0a', 'muted' => '#525252', 'accent' => '#0a0a0a',
                    'accent_contrast' => '#ffffff', 'border' => '#0a0a0a', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Inter',
                    'heading_weight' => '600', 'heading_tracking' => '-0.03em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'crisp-modern', 'duration_ms' => 240, 'easing' => 'cubic-bezier(0.2, 0, 0, 1)', 'enable_parallax' => false, 'stagger_ms' => 25],
                'layout' => [
                    'header_template' => 'brutalist-bar', 'hero_template' => 'tech-product-centerpiece',
                    'card_template' => 'gallery-plinth', 'pdp_template' => 'specialist-modular',
                    'footer_template' => 'colophon-hairline', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '1/1', 'default_columns' => 4, 'radius' => '0px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'INDEX // DEVICES'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'hairline-index'],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'pixel-foundry' => [
                'id' => 'pixel-foundry',
                'slug' => 'pixel-foundry',
                'number' => 26,
                'name' => 'Pixel Foundry',
                'category' => 'tech_electronics',
                'compatible_categories' => ['electronics', 'gaming', 'gadgets'],
                'design_attributes' => ['Dark Matte Hardware Bay', 'Geometric HUD Borders', 'Compatibility Specs'],
                'tagline' => 'Gaming hardware and digital accessories without cheap RGB clutter',
                'description' => 'A serious hardware storefront for gaming peripherals and digital gear in matte carbon-slate with restrained cobalt-cyan highlights, sharp geometric framing, and crisp compatibility specs.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#0d0f14', 'surface' => '#161a24', 'surface_alt' => '#202636',
                    'text' => '#f1f5f9', 'muted' => '#94a3b8', 'accent' => '#38bdf8',
                    'accent_contrast' => '#082032', 'border' => 'rgba(56, 189, 248, 0.22)', 'is_dark' => true,
                ],
                'typography' => [
                    'heading_font' => 'Syne', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.025em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'kinetic-snap', 'duration_ms' => 300, 'easing' => 'cubic-bezier(0.16, 1, 0.3, 1)', 'enable_parallax' => false, 'stagger_ms' => 35],
                'layout' => [
                    'header_template' => 'obsidian-overlay', 'hero_template' => 'obsidian-monolith',
                    'card_template' => 'tech-spec-matrix', 'pdp_template' => 'obsidian-showcase',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'dark-4col',
                    'card_aspect' => '4/3', 'default_columns' => 4, 'radius' => '6px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'monolith-tabs'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'monolith-trio'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Foundry Hardware'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'audio-atelier' => [
                'id' => 'audio-atelier',
                'slug' => 'audio-atelier',
                'number' => 27,
                'name' => 'Audio Atelier',
                'category' => 'tech_electronics',
                'compatible_categories' => ['electronics', 'audio', 'gadgets'],
                'design_attributes' => ['Acoustic Studio Palette', 'Editorial Hardware Split', 'Acoustic Spec Panel'],
                'tagline' => 'Premium headphones, speakers and acoustic gear with studio refinement',
                'description' => 'An audiophile equipment storefront pairing warm matte charcoal, brushed-copper highlights, studio editorial photography, and structured connectivity/acoustic specification sheets.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#121214', 'surface' => '#1b1b1f', 'surface_alt' => '#26262c',
                    'text' => '#f4f4f6', 'muted' => '#a1a1aa', 'accent' => '#d97706',
                    'accent_contrast' => '#121214', 'border' => 'rgba(244, 244, 246, 0.14)', 'is_dark' => true,
                ],
                'typography' => [
                    'heading_font' => 'Outfit', 'body_font' => 'DM Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.025em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'obsidian-curtain', 'duration_ms' => 480, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'horology-macro-stage',
                    'card_template' => 'obsidian-monolith', 'pdp_template' => 'horology-precision',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'dark-4col',
                    'card_aspect' => '1/1', 'default_columns' => 3, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Listening Equipment'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            // --- CATEGORY E: FURNITURE, HOME & INTERIORS ---
            'casa-moderna' => [
                'id' => 'casa-moderna',
                'slug' => 'casa-moderna',
                'number' => 28,
                'name' => 'Casa Moderna',
                'category' => 'furniture_home',
                'compatible_categories' => ['furniture', 'home_living', 'real_estate'],
                'design_attributes' => ['Room-Scale 16:10 Hero', 'Dimensions-First PDP', 'Warm Architectural Grid'],
                'tagline' => 'Contemporary furniture and interiors with room-scale photography',
                'description' => 'An Italian-inspired contemporary furniture showroom showcasing sweeping interior photography, wide architectural product cards, prominent dimensions and materials, and calm spatial balance.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f8f5f0', 'surface' => '#ffffff', 'surface_alt' => '#ece6dc',
                    'text' => '#221e1a', 'muted' => '#6e655c', 'accent' => '#8c5332',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(34, 30, 26, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Playfair Display', 'body_font' => 'DM Sans',
                    'heading_weight' => '500', 'heading_tracking' => '-0.015em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 600, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => true, 'stagger_ms' => 55],
                'layout' => [
                    'header_template' => 'couture-centered', 'hero_template' => 'interior-room-canvas',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '4/3', 'default_columns' => 3, 'radius' => '6px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'alternating-plinth'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Living & Dining Pieces'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'nordic-living' => [
                'id' => 'nordic-living',
                'slug' => 'nordic-living',
                'number' => 29,
                'name' => 'Nordic Living',
                'category' => 'furniture_home',
                'compatible_categories' => ['home_living', 'furniture', 'crafts'],
                'design_attributes' => ['Scandinavian Birch & White', 'Airy Product Grid', 'Practical Material Details'],
                'tagline' => 'Scandinavian home and lifestyle retail in warm white and pale wood neutrals',
                'description' => 'A calm Scandinavian home store emphasizing generous daylight whitespace, warm birch-neutral surfaces, practical dimensions and materials, and clean uncluttered product grids.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#fafaf7', 'surface' => '#ffffff', 'surface_alt' => '#f0ede5',
                    'text' => '#1e2124', 'muted' => '#666c73', 'accent' => '#385263',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(30, 33, 36, 0.09)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'DM Sans', 'body_font' => 'Inter',
                    'heading_weight' => '500', 'heading_tracking' => '-0.02em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'quiet-gallery', 'duration_ms' => 450, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'gallery-minimal', 'hero_template' => 'studio-split-offset',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'studio-compact', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/3', 'default_columns' => 3, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Nordic Home'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'forma-gallery' => [
                'id' => 'forma-gallery',
                'slug' => 'forma-gallery',
                'number' => 30,
                'name' => 'Forma Gallery',
                'category' => 'furniture_home',
                'compatible_categories' => ['furniture', 'home_living', 'art'],
                'design_attributes' => ['Architectural Grid Lines', 'Asymmetric Showroom', 'Monumental Scale'],
                'tagline' => 'Architectural furniture gallery with monumental imagery and asymmetric grids',
                'description' => 'An architectural showroom combining stark concrete-white framing, structural hairline grids, monumental furniture photography, asymmetric curation, and exact configuration details.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f2f2ef', 'surface' => '#ffffff', 'surface_alt' => '#e5e5e0',
                    'text' => '#141413', 'muted' => '#5e5e5a', 'accent' => '#2b2b28',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(20, 20, 19, 0.14)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Inter',
                    'heading_weight' => '500', 'heading_tracking' => '-0.03em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'quiet-gallery', 'duration_ms' => 540, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 55],
                'layout' => [
                    'header_template' => 'brutalist-bar', 'hero_template' => 'interior-room-canvas',
                    'card_template' => 'gallery-plinth', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'colophon-hairline', 'collection_layout' => 'asymmetric-mixed',
                    'card_aspect' => '4/3', 'default_columns' => 3, 'radius' => '0px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'hairline-index'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Architectural Forms'],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'earth-and-grain' => [
                'id' => 'earth-and-grain',
                'slug' => 'earth-and-grain',
                'number' => 31,
                'name' => 'Earth & Grain',
                'category' => 'furniture_home',
                'compatible_categories' => ['crafts', 'home_living', 'furniture', 'organic'],
                'design_attributes' => ['Terracotta & Clay Neutrals', 'Texture-Led Framing', 'Craft Story Block'],
                'tagline' => 'Natural materials and handcrafted home goods in warm clay and olive',
                'description' => 'An earthy artisan homeware storefront defined by clay, warm linen, muted olive accents, organic serif typography, material craft specs, and calm collection cadence.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f3ece2', 'surface' => '#fbf7f1', 'surface_alt' => '#e5d8c6',
                    'text' => '#2b1f16', 'muted' => '#6d594b', 'accent' => '#964b2c',
                    'accent_contrast' => '#fbf7f1', 'border' => 'rgba(43, 31, 22, 0.13)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Fraunces', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '500', 'heading_tracking' => '-0.015em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 580, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 55],
                'layout' => [
                    'header_template' => 'heritage-framed', 'hero_template' => 'lifestyle-editorial-arch',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'editorial_feature', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Handcrafted Goods'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'habitat-studio' => [
                'id' => 'habitat-studio',
                'slug' => 'habitat-studio',
                'number' => 32,
                'name' => 'Habitat Studio',
                'category' => 'furniture_home',
                'compatible_categories' => ['furniture', 'home_living', 'general'],
                'design_attributes' => ['Room Category Nav', 'Balanced Catalog & Editorial', 'Utility Search'],
                'tagline' => 'Practical premium home furnishing with room-based discovery and filtering',
                'description' => 'A practical home furnishing destination balancing warm architectural photography with organized room navigation, clear dimensions, accessible filters, and fast browsing.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f7f7f5', 'surface' => '#ffffff', 'surface_alt' => '#ebece8',
                    'text' => '#1c201e', 'muted' => '#5b635f', 'accent' => '#3f624c',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(28, 32, 30, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Plus Jakarta Sans', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.02em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'crisp-modern', 'duration_ms' => 340, 'easing' => 'cubic-bezier(0.2, 0, 0, 1)', 'enable_parallax' => false, 'stagger_ms' => 35],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'interior-room-canvas',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'utility-directory', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/3', 'default_columns' => 4, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Shop By Room'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],
        ];
    }
}
