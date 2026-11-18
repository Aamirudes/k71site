<?php

namespace App\Services\Storefront\ThemeDefinitions;

/**
 * Category F (33–37): Beauty, Skincare & Personal Care.
 * Category G (38–42): Food, Specialty Retail & Artisan Products.
 */
class CategoryFGBeautyFoodThemes
{
    public static function definitions(): array
    {
        return [
            // --- CATEGORY F: BEAUTY, SKINCARE & PERSONAL CARE ---
            'botanical-lab' => [
                'id' => 'botanical-lab',
                'slug' => 'botanical-lab',
                'number' => 33,
                'name' => 'Botanical Lab',
                'category' => 'beauty_skincare',
                'compatible_categories' => ['beauty', 'organic', 'wellness'],
                'design_attributes' => ['Sage Apothecary Palette', 'Ingredient Accordion PDP', 'Calm Botanical Framing'],
                'tagline' => 'Botanical beauty and natural care with calm apothecary typography',
                'description' => 'A refined botanical care storefront featuring muted sage and cream surfaces, restrained apothecary typography, clean formulation cards, and structured ingredient/usage panels.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f4f5ef', 'surface' => '#fcfdf9', 'surface_alt' => '#e5eadb',
                    'text' => '#1c241d', 'muted' => '#59665b', 'accent' => '#3b6143',
                    'accent_contrast' => '#fcfdf9', 'border' => 'rgba(28, 36, 29, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Fraunces', 'body_font' => 'DM Sans',
                    'heading_weight' => '500', 'heading_tracking' => '-0.015em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 560, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 50],
                'layout' => [
                    'header_template' => 'floating-pill-nav', 'hero_template' => 'apothecary-split',
                    'card_template' => 'apothecary-ritual', 'pdp_template' => 'skincare-routine',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '14px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Botanical Formulations'],
                    ['type' => 'editorial_feature', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'skin-ritual' => [
                'id' => 'skin-ritual',
                'slug' => 'skin-ritual',
                'number' => 34,
                'name' => 'Skin Ritual',
                'category' => 'beauty_skincare',
                'compatible_categories' => ['beauty', 'skincare', 'wellness'],
                'design_attributes' => ['Editorial Skincare Surfaces', 'Routine Category Bar', 'Mobile-First Purchase Flow'],
                'tagline' => 'Premium skincare brand built around luminous calm and routine discovery',
                'description' => 'An editorial skincare flagship combining clean alabaster and warm stone planes, serif ritual headings, routine-oriented category navigation, and crisp mobile purchasing.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#fbf9f7', 'surface' => '#ffffff', 'surface_alt' => '#f2ece6',
                    'text' => '#211d1a', 'muted' => '#736a62', 'accent' => '#9b6b56',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(33, 29, 26, 0.10)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Playfair Display', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '500', 'heading_tracking' => '-0.01em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 520, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'couture-centered', 'hero_template' => 'apothecary-split',
                    'card_template' => 'apothecary-ritual', 'pdp_template' => 'skincare-routine',
                    'footer_template' => 'maison-minimal', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '16px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Daily Skincare'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'pure-science' => [
                'id' => 'pure-science',
                'slug' => 'pure-science',
                'number' => 35,
                'name' => 'Pure Science',
                'category' => 'beauty_skincare',
                'compatible_categories' => ['skincare', 'beauty', 'wellness', 'pharmacy'],
                'design_attributes' => ['Clinical Clarity', 'Structured Spec Panels', 'High-Contrast Lab Grid'],
                'tagline' => 'Science-led skincare and personal care with factual ingredient transparency',
                'description' => 'A clinical-clarity personal care storefront pairing pure white and cool slate lab framing, monospace formulation indexes, factual specification tables, and zero marketing hype.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#ffffff', 'surface' => '#f8fafc', 'surface_alt' => '#edf2f7',
                    'text' => '#0f172a', 'muted' => '#475569', 'accent' => '#0f766e',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(15, 23, 42, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Space Grotesk', 'body_font' => 'Inter',
                    'heading_weight' => '600', 'heading_tracking' => '-0.025em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'utility-instant', 'duration_ms' => 240, 'easing' => 'ease-out', 'enable_parallax' => false, 'stagger_ms' => 25],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'compact-catalog-banner',
                    'card_template' => 'tech-spec-matrix', 'pdp_template' => 'skincare-routine',
                    'footer_template' => 'utility-directory', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '4px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'hairline-index'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Clinical Catalogue'],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'velvet-beauty' => [
                'id' => 'velvet-beauty',
                'slug' => 'velvet-beauty',
                'number' => 36,
                'name' => 'Velvet Beauty',
                'category' => 'beauty_skincare',
                'compatible_categories' => ['beauty', 'cosmetics', 'fragrance'],
                'design_attributes' => ['Rich Plum & Ivory Editorial', 'Shade-Forward Cards', 'Campaign Hero'],
                'tagline' => 'Sophisticated makeup and beauty retail with rich shade presentation',
                'description' => 'A sophisticated cosmetics storefront balancing deep velvet-plum editorial accents against warm ivory planes, prominent shade/variant selectors, and refined gallery transitions.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#faf6f7', 'surface' => '#ffffff', 'surface_alt' => '#f2e6ea',
                    'text' => '#26141d', 'muted' => '#725663', 'accent' => '#7a1f4b',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(38, 20, 29, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Bodoni Moda', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.015em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'cinematic-editorial', 'duration_ms' => 540, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 50],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'fullbleed-runway',
                    'card_template' => 'apothecary-ritual', 'pdp_template' => 'luxury-lookbook',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '4/5', 'default_columns' => 4, 'radius' => '12px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'asymmetric-editorial'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Color & Complexion'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'daily-ritual' => [
                'id' => 'daily-ritual',
                'slug' => 'daily-ritual',
                'number' => 37,
                'name' => 'Daily Ritual',
                'category' => 'beauty_skincare',
                'compatible_categories' => ['beauty', 'wellness', 'groceries', 'general'],
                'design_attributes' => ['Fast Everyday Clarity', 'Direct Add-to-Bag Cards', 'Calm Neutral Palette'],
                'tagline' => 'Everyday personal care focused on speed, clarity and repeat purchasing',
                'description' => 'A frictionless everyday personal-care store prioritizing immediate product discovery, clear pack-size pricing, 1-tap bag additions, and clean mobile navigation.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f8f9f8', 'surface' => '#ffffff', 'surface_alt' => '#edf1ee',
                    'text' => '#18211d', 'muted' => '#586660', 'accent' => '#2f6f55',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(24, 33, 29, 0.10)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Outfit', 'body_font' => 'Inter',
                    'heading_weight' => '600', 'heading_tracking' => '-0.02em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'utility-instant', 'duration_ms' => 240, 'easing' => 'ease-out', 'enable_parallax' => false, 'stagger_ms' => 25],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'apothecary-split',
                    'card_template' => 'gourmet-pantry', 'pdp_template' => 'skincare-routine',
                    'footer_template' => 'studio-compact', 'collection_layout' => 'clean-4col',
                    'card_aspect' => '1/1', 'default_columns' => 4, 'radius' => '12px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Everyday Essentials'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            // --- CATEGORY G: FOOD, SPECIALTY RETAIL & ARTISAN PRODUCTS ---
            'maison-gourmet' => [
                'id' => 'maison-gourmet',
                'slug' => 'maison-gourmet',
                'number' => 38,
                'name' => 'Maison Gourmet',
                'category' => 'food_artisan',
                'compatible_categories' => ['food', 'groceries', 'organic', 'gifting'],
                'design_attributes' => ['Fine Culinary Editorial', 'Pack & Ingredient PDP', 'Warm Ivory & Olive'],
                'tagline' => 'Premium gourmet food store with refined culinary editorial presentation',
                'description' => 'A fine-food emporium combining rich culinary photography, classic editorial serif titles, warm parchment and deep forest accents, pack-size clarity, and direct ordering.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f9f5ee', 'surface' => '#ffffff', 'surface_alt' => '#eee6d8',
                    'text' => '#211c16', 'muted' => '#6b6052', 'accent' => '#2e4a38',
                    'accent_contrast' => '#f9f5ee', 'border' => 'rgba(33, 28, 22, 0.13)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Playfair Display', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.01em', 'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'measured-heritage', 'duration_ms' => 500, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'heritage-framed', 'hero_template' => 'culinary-table-hero',
                    'card_template' => 'gourmet-pantry', 'pdp_template' => 'gourmet-culinary',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '4/3', 'default_columns' => 3, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'alternating-plinth'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Fine Provisions'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'fresh-market' => [
                'id' => 'fresh-market',
                'slug' => 'fresh-market',
                'number' => 39,
                'name' => 'Fresh Market',
                'category' => 'food_artisan',
                'compatible_categories' => ['groceries', 'food', 'feasto', 'organic'],
                'design_attributes' => ['High-Density Instant Grid', '1-Tap Cart Add', 'Search-First Utility'],
                'tagline' => 'Fast, product-dense fresh market and daily essentials storefront',
                'description' => 'An ultra-practical daily market storefront built for speed: search-first header, instant category strip, high-density cards with bold prices and 1-tap bag controls, and zero animation lag.',
                'version' => '2.0.0',
                'is_featured' => true,
                'palette' => [
                    'bg' => '#f5f7f5', 'surface' => '#ffffff', 'surface_alt' => '#e8f0ea',
                    'text' => '#111c16', 'muted' => '#4a5c52', 'accent' => '#15803d',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(17, 28, 22, 0.12)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Plus Jakarta Sans', 'body_font' => 'Inter',
                    'heading_weight' => '800', 'heading_tracking' => '-0.02em', 'label_style' => 'clean',
                ],
                'motion' => ['profile' => 'utility-instant', 'duration_ms' => 150, 'easing' => 'ease-out', 'enable_parallax' => false, 'stagger_ms' => 15],
                'layout' => [
                    'header_template' => 'utility-catalog', 'hero_template' => 'compact-catalog-banner',
                    'card_template' => 'fresh-dense-market', 'pdp_template' => 'gourmet-culinary',
                    'footer_template' => 'utility-directory', 'collection_layout' => 'dense-4col',
                    'card_aspect' => '1/1', 'default_columns' => 4, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Fresh & Daily Essentials'],
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                ],
            ],

            'artisan-pantry' => [
                'id' => 'artisan-pantry',
                'slug' => 'artisan-pantry',
                'number' => 40,
                'name' => 'Artisan Pantry',
                'category' => 'food_artisan',
                'compatible_categories' => ['food', 'organic', 'groceries', 'crafts'],
                'design_attributes' => ['Warm Editorial Pantry', 'Ingredient & Pack Cards', 'Curated Shelf Grid'],
                'tagline' => 'Small-batch specialty pantry with warm packaging photography and clear specs',
                'description' => 'A warm specialty food storefront pairing kraft-cream backdrops, deep paprika-rust accents, expressive Fraunces headings, clear pack details, and inviting shelf curation.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#f7f1e6', 'surface' => '#fffdf8', 'surface_alt' => '#ece1cf',
                    'text' => '#2b2118', 'muted' => '#6f5e4f', 'accent' => '#b44823',
                    'accent_contrast' => '#fffdf8', 'border' => 'rgba(43, 33, 24, 0.14)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Fraunces', 'body_font' => 'DM Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.015em', 'label_style' => 'editorial-italic',
                ],
                'motion' => ['profile' => 'editorial-stagger', 'duration_ms' => 460, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'magazine-masthead', 'hero_template' => 'culinary-table-hero',
                    'card_template' => 'gourmet-pantry', 'pdp_template' => 'gourmet-culinary',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '10px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'magazine-spread'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'pill-rail'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Pantry Staples'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'coffee-culture' => [
                'id' => 'coffee-culture',
                'slug' => 'coffee-culture',
                'number' => 41,
                'name' => 'Coffee Culture',
                'category' => 'food_artisan',
                'compatible_categories' => ['food', 'beverages', 'appliances', 'crafts'],
                'design_attributes' => ['Roastery Espresso Palette', 'Roast & Grind Details', 'Editorial Split'],
                'tagline' => 'Specialty coffee, tea and brewing equipment with roastery typography',
                'description' => 'A craft roastery storefront in deep espresso-brown, crema ivory, and roasted amber highlights with bold editorial typography and grind/equipment variant clarity.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#17120e', 'surface' => '#221a15', 'surface_alt' => '#2f241d',
                    'text' => '#f6efe6', 'muted' => '#b0a092', 'accent' => '#d48c46',
                    'accent_contrast' => '#17120e', 'border' => 'rgba(246, 239, 230, 0.14)', 'is_dark' => true,
                ],
                'typography' => [
                    'heading_font' => 'Fraunces', 'body_font' => 'Plus Jakarta Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.02em', 'label_style' => 'mono',
                ],
                'motion' => ['profile' => 'obsidian-curtain', 'duration_ms' => 500, 'easing' => 'cubic-bezier(0.22, 1, 0.36, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'split-studio', 'hero_template' => 'culinary-table-hero',
                    'card_template' => 'gourmet-pantry', 'pdp_template' => 'gourmet-culinary',
                    'footer_template' => 'obsidian-architectural', 'collection_layout' => 'dark-4col',
                    'card_aspect' => '4/5', 'default_columns' => 3, 'radius' => '8px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'monolith-tabs'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Roasts & Brew Gear'],
                    ['type' => 'editorial_feature', 'enabled' => true],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],

            'sweet-atelier' => [
                'id' => 'sweet-atelier',
                'slug' => 'sweet-atelier',
                'number' => 42,
                'name' => 'Sweet Atelier',
                'category' => 'food_artisan',
                'compatible_categories' => ['food', 'bakery', 'gifting'],
                'design_attributes' => ['Patisserie Cream & Cocoa', 'Gifting Box Framing', 'Flavor/Size Selection'],
                'tagline' => 'Premium patisserie, confectionery and gifting boutique',
                'description' => 'An elegant patisserie and gifting boutique crafted in warm vanilla cream, cocoa ink, and raspberry-rose highlights with inviting square box photography and effortless ordering.',
                'version' => '2.0.0',
                'palette' => [
                    'bg' => '#fdf8f5', 'surface' => '#ffffff', 'surface_alt' => '#f5e8e2',
                    'text' => '#2e1c18', 'muted' => '#7a605a', 'accent' => '#9f3548',
                    'accent_contrast' => '#ffffff', 'border' => 'rgba(46, 28, 24, 0.11)', 'is_dark' => false,
                ],
                'typography' => [
                    'heading_font' => 'Playfair Display', 'body_font' => 'DM Sans',
                    'heading_weight' => '600', 'heading_tracking' => '-0.01em', 'label_style' => 'editorial-italic',
                ],
                'motion' => ['profile' => 'soft-drift', 'duration_ms' => 480, 'easing' => 'cubic-bezier(0.25, 0.1, 0.25, 1)', 'enable_parallax' => false, 'stagger_ms' => 45],
                'layout' => [
                    'header_template' => 'floating-pill-nav', 'hero_template' => 'lifestyle-editorial-arch',
                    'card_template' => 'gourmet-pantry', 'pdp_template' => 'gourmet-culinary',
                    'footer_template' => 'warm-editorial', 'collection_layout' => 'spacious-3col',
                    'card_aspect' => '1/1', 'default_columns' => 3, 'radius' => '16px',
                ],
                'default_sections' => [
                    ['type' => 'hero', 'enabled' => true],
                    ['type' => 'featured_showcase', 'enabled' => true, 'style' => 'spotlight-row'],
                    ['type' => 'category_nav', 'enabled' => true, 'style' => 'framed-tiles'],
                    ['type' => 'product_grid', 'enabled' => true, 'title' => 'Fresh from the Atelier'],
                    ['type' => 'reviews_social', 'enabled' => true],
                    ['type' => 'trust_policies', 'enabled' => true],
                ],
            ],
        ];
    }
}
