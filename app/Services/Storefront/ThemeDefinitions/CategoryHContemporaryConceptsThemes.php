<?php

namespace App\Services\Storefront\ThemeDefinitions;

/**
 * Category H (Themes 43–50): Contemporary Commerce & Special Concepts.
 */
class CategoryHContemporaryConceptsThemes
{
    public static function definitions(): array
    {
        return [
            'neo-commerce' => [
                'id' => 'neo-commerce',
                'slug' => 'neo-commerce',
                'number' => 43,
                'name' => 'Neo Commerce',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['general', 'fashion', 'electronics', 'home_living'],
                'design_attributes' => ['Modular Bento Hero', 'Clean Spatial Cards', 'Refined Micro-Interactions'],
                'tagline' => 'Modern modular premium retail with architectural bento discovery',
                'description' => 'A next-generation modular retail flagship featuring a multi-cell bento hero, floating capsule navigation, structured product discovery, and crisp tactile micro-interactions.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f6f7f9', 'surface' => '#ffffff', 'surface_alt' => '#eaedf3',
                    'text' => '#11151f', 'muted' => '#5b6475', 'accent' => '#4338ca',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(17, 21, 31, 0.10)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Outfit', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.025em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'crisp-modern', 'duration_ms' => 380, 'easing' => 'cubic-bezier(0.16, 1, 0.3, 1)', 'enable_parallax' => false, 'stagger_ms' => 40],
                'layout' => [
                    'header_template' => 'floating-pill-nav', 'hero_template' => 'bento-modular-hero',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'specialist-modular',
                    'footer_template' => 'studio-compact', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '16px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Modular Collection'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'the-drop' => [
                'id' => 'the-drop',
                'slug' => 'the-drop',
                'number' => 44,
                'name' => 'The Drop',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['streetwear', 'footwear', 'fashion', 'collectibles'],
                'design_attributes' => ['Release-Led Hero', 'Authentic Timer Support', 'Noir Drop Poster Cards'],
                'tagline' => 'Limited-release and collection-drop flagship with authentic launch framing',
                'description' => 'A stark release-oriented flagship in deep obsidian and acid-emerald status indicators, featuring real configured countdown timers (never fabricated), bold drop headers, and horizontal rails.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#0a0a0c', 'surface' => '#141418', 'surface_alt' => '#1e1e24',
                    'text' => '#f8f8fa', 'muted' => '#9a9ca8', 'accent' => '#22c55e',
                    'accent_contrast' => '#051f0e', 'border' => 'rgba(248, 248, 250, 0.14)', 'is_dark' => true,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.035em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'kinetic-snap', 'duration_ms' => 300, 'easing' => 'cubic-bezier(0.16, 1, 0.3, 1)', 'enable_parallax' => false, 'stagger_ms' => 35],
                'layout' => [
                    'header_template' => 'brutalist-bar', 'hero_template' => 'drop-launch-hero',
                    'card_template' => 'drop-kinetic-poster', 'pdp_template' => 'obsidian-showcase',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'dark-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '4px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'horizontal-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'CURRENT RELEASE'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'monolith-tabs'],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'market-studio' => [
                'id' => 'market-studio',
                'slug' => 'market-studio',
                'number' => 45,
                'name' => 'Market Studio',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['general', 'fashion', 'home_living', 'gifting'],
                'design_attributes' => ['Department Hierarchy', 'Editorial + Catalog Balance', 'Multi-Category Tiles'],
                'tagline' => 'Curated multi-category department boutique with balanced hierarchy',
                'description' => 'A curated multi-category department storefront combining a practical search header, editorial campaign panels, clear category tile hierarchy, and balanced 4-column product discovery.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#faf9f7', 'surface' => '#ffffff', 'surface_alt' => '#efede8',
                    'text' => '#18181b', 'muted' => '#5c5c66', 'accent' => '#0f766e',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(24, 24, 27, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'DM Sans', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.02em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'crisp-modern', 'duration_ms' => 340, 'easing' => 'cubic-bezier(0.2, 0, 0, 1)', 'enable_parallax' => false, 'stagger_ms' => 35],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'bento-modular-hero',
                    'card_template' => 'fresh-dense-market', 'pdp_template' => 'specialist-modular',
                    'footer_template' => 'utility-directory', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'All Departments'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'pop-form' => [
                'id' => 'pop-form',
                'slug' => 'pop-form',
                'number' => 46,
                'name' => 'Pop Form',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['accessories', 'crafts', 'gifting', 'fashion'],
                'design_attributes' => ['Expressive Rounded Geometry', 'Tactile Offset Borders', 'Controlled Warm Accent'],
                'tagline' => 'Playful contemporary design retail with expressive geometry and discipline',
                'description' => 'An upbeat design-object and lifestyle storefront combining warm cream backdrops, tangerine-and-ink graphic framing, friendly Outfit display typography, and high-contrast purchase controls.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#fff9f0', 'surface' => '#ffffff', 'surface_alt' => '#ffecd1',
                    'text' => '#1c192b', 'muted' => '#5e5873', 'accent' => '#ea580c',
                    'accent_contrast' => '#ffffff', 'border' => '#1c192b', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Outfit', 'body_font' => 'DM Sans',
                    'heading_weight' => '700', 'heading_tracking' => '-0.02em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'pop-elastic', 'duration_ms' => 380, 'easing' => 'cubic-bezier(0.34, 1.35, 0.64, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'floating-pill-nav', 'hero_template' => 'color-block-duet',
                    'card_template' => 'drop-kinetic-poster', 'pdp_template' => 'skincare-routine',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '1/1', 'default_columns' => 4, 'radius' => '20px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Fresh Drops & Goods'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'color-theory' => [
                'id' => 'color-theory',
                'slug' => 'color-theory',
                'number' => 47,
                'name' => 'Color Theory',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['fashion', 'accessories', 'home_living', 'art'],
                'design_attributes' => ['Intentional Color Blocking', 'Swatch-Forward Cards', 'Graphic Composition'],
                'tagline' => 'Color-led designer retail with structured architectural color blocks',
                'description' => 'A bold color-blocked studio storefront pairing crisp gallery alabaster with deep cobalt architectural panels, clear variant swatches, graphic shadows, and accessible contrast.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f7f5ef', 'surface' => '#ffffff', 'surface_alt' => '#1e3a8a',
                    'text' => '#131620', 'muted' => '#4f5566', 'accent' => '#1d4ed8',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(19, 22, 32, 0.16)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Syne', 'body_font' => 'DM Sans',
                    'heading_weight' => '800', 'heading_tracking' => '-0.025em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'editorial-stagger', 'duration_ms' => 380, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'color-block-duet',
                    'card_template' => 'editorial-spread', 'pdp_template' => 'gallery-minimal',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'asymmetric-mixed',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'oversized-marquee'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Color Studies'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'heritage-house' => [
                'id' => 'heritage-house',
                'slug' => 'heritage-house',
                'number' => 48,
                'name' => 'Heritage House',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['luxury', 'home_living', 'crafts', 'fashion'],
                'design_attributes' => ['Classic Serif Columns', 'Authentic Craft Trust Bar', 'Structured Heirloom Grid'],
                'tagline' => 'Established heritage retailer with classical structure and authentic trust',
                'description' => 'A timeless heritage house storefront pairing warm parchment, forest-pine accents, classical serif headlines, formal catalog symmetry, and factual merchant trust blocks.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f5f2ea', 'surface' => '#fdfbf6', 'surface_alt' => '#e8e1d2',
                    'text' => '#1e211b', 'muted' => '#63665c', 'accent' => '#264636',
                    'accent_contrast' => '#fdfbf6', 'border' => 'rgba(30, 33, 27, 0.14)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Libre Baskerville', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '0em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'measured-heritage', 'duration_ms' => 540, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 50],
                'layout' => [
                    'header_template' => 'heritage-framed', 'hero_template' => 'heritage-framed-banner',
                    'card_template' => 'interior-architectural', 'pdp_template' => 'horology-precision',
                    'footer_template' => 'heritage-columns', 'collection_layout' => 'formal-3col',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '2px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'House Selections'],
                    ['type' => 'editorial_feature', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'field-and-found' => [
                'id' => 'field-and-found',
                'slug' => 'field-and-found',
                'number' => 49,
                'name' => 'Field & Found',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['outdoor', 'travel', 'sports', 'accessories'],
                'design_attributes' => ['Environmental Wide Hero', 'Field Spec Metadata', 'Rugged Utility Palette'],
                'tagline' => 'Outdoor, travel and expedition goods with clear materials and specs',
                'description' => 'An expedition and travel-gear storefront featuring wide environmental campaign photography, warm khaki and pine-charcoal surfaces, practical specification cards, and rugged clarity.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f4f2ec', 'surface' => '#ffffff', 'surface_alt' => '#e3dfd4',
                    'text' => '#1e211c', 'muted' => '#5c6157', 'accent' => '#9a4d1c',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(30, 33, 28, 0.15)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Barlow Condensed', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '700', 'heading_tracking' => '0.02em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'runway-directional', 'duration_ms' => 420, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => true, 'stagger_ms' => 40],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'fullbleed-runway',
                    'card_template' => 'tech-spec-matrix', 'pdp_template' => 'furniture-architectural',
                    'footer_template' => 'heritage-columns', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/3', 'default_columns' => 3, 'radius' => '6px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Field Equipment'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'horizontal-rail'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'the-specialist' => [
                'id' => 'the-specialist',
                'slug' => 'the-specialist',
                'number' => 50,
                'name' => 'The Specialist',
                'category' => 'contemporary_concepts',
                'compatible_categories' => ['machinery', 'b2b', 'automotive', 'electronics', 'general'],
                'design_attributes' => ['Expert Spec Dossier', 'Structured Category Sidebar', 'High-Trust Precision'],
                'tagline' => 'Expert-led specialist retailer with structured specs and authority',
                'description' => 'An authoritative specialist retailer storefront built around clear technical product documentation, structured department indexing, specification-forward cards, and transparent seller credentials.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f5f6f8', 'surface' => '#ffffff', 'surface_alt' => '#e8ebf0',
                    'text' => '#121826', 'muted' => '#4b5568', 'accent' => '#0f4c81',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(18, 24, 38, 0.14)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Inter',
                    'heading_weight' => '700', 'heading_tracking' => '-0.025em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'utility-instant', 'duration_ms' => 220, 'easing' => 'ease-out', 'enable_parallax' => false, 'stagger_ms' => 25],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'studio-split-offset',
                    'card_template' => 'horology-spec', 'pdp_template' => 'specialist-modular',
                    'footer_template' => 'utility-directory', 'collection_layout' => 'dense-4col',
                    'card_aspect' => '4/3', 'default_columns' => 4, 'radius' => '4px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'hairline-index'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Specialist Inventory'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],
        ];
    }
}
