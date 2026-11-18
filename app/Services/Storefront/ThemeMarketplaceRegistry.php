<?php

namespace App\Services\Storefront;

use App\Services\Storefront\ThemeDefinitions\CategoryALuxuryFashionThemes;
use App\Services\Storefront\ThemeDefinitions\CategoryBCFootwearJewelryThemes;
use App\Services\Storefront\ThemeDefinitions\CategoryDETechFurnitureThemes;
use App\Services\Storefront\ThemeDefinitions\CategoryFGBeautyFoodThemes;
use App\Services\Storefront\ThemeDefinitions\CategoryHContemporaryConceptsThemes;

class ThemeMarketplaceRegistry
{
    public const CATEGORY_LABELS = [
        'legacy' => 'Classic K71 (Original 11)',
        'luxury_fashion' => 'A · Luxury, Fashion & Editorial',
        'footwear_performance' => 'B · Sneakers, Footwear & Performance',
        'watches_jewelry' => 'C · Watches, Jewelry & Objects',
        'tech_electronics' => 'D · Technology & Electronics',
        'furniture_home' => 'E · Furniture, Home & Interiors',
        'beauty_skincare' => 'F · Beauty, Skincare & Personal Care',
        'food_artisan' => 'G · Food, Specialty & Artisan',
        'contemporary_concepts' => 'H · Contemporary Commerce & Concepts',
    ];

    public const ALLOWED_HEADING_FONTS = [
        'Cormorant Garamond',
        'Playfair Display',
        'Space Grotesk',
        'Fraunces',
        'Libre Baskerville',
        'Outfit',
        'Syne',
        'Bodoni Moda',
        'Barlow Condensed',
        'Plus Jakarta Sans',
        'DM Sans',
        'Inter',
    ];

    public const ALLOWED_BODY_FONTS = [
        'Inter',
        'Plus Jakarta Sans',
        'DM Sans',
        'Space Grotesk',
        'Outfit',
    ];

    public const ALLOWED_SECTION_TYPES = [
        'hero',
        'category_nav',
        'featured_showcase',
        'editorial_feature',
        'product_grid',
        'reviews_social',
        'trust_policies',
    ];

    protected ?array $cachedNewThemes = null;

    /**
     * All 50 bespoke individually art-directed storefront themes (Categories A–H).
     */
    public function getNewThemes(): array
    {
        if ($this->cachedNewThemes !== null) {
            return $this->cachedNewThemes;
        }

        $this->cachedNewThemes = array_merge(
            CategoryALuxuryFashionThemes::definitions(),
            CategoryBCFootwearJewelryThemes::definitions(),
            CategoryDETechFurnitureThemes::definitions(),
            CategoryFGBeautyFoodThemes::definitions(),
            CategoryHContemporaryConceptsThemes::definitions()
        );

        return $this->cachedNewThemes;
    }

    /**
     * Original 11 legacy presets from StorefrontPresetService (strictly preserved).
     */
    public function getLegacyThemes(): array
    {
        $presets = app(StorefrontPresetService::class)->getPresets(true);
        $mapped = [];
        $num = 1;
        foreach ($presets as $key => $preset) {
            $mapped[$key] = [
                'id' => $key,
                'slug' => $key,
                'number' => $num++,
                'name' => $preset['name'],
                'category' => 'legacy',
                'compatible_categories' => $preset['recommended_for_types'] ?? ['general'],
                'design_attributes' => ['Preserved K71 Classic', $preset['badge'] ?? 'Classic Preset'],
                'tagline' => $preset['tagline'] ?? '',
                'description' => $preset['description'] ?? '',
                'version' => '2.0.0',
                'is_legacy' => true,
                'is_free' => $key !== 'kinetic',
                'is_featured' => false,
                'palette' => [
                    'bg' => $preset['preview_bg'] ?? '#fafaf9',
                    'surface' => $preset['preview_card_bg'] ?? '#ffffff',
                    'surface_alt' => $preset['preview_card_bg'] ?? '#f5f5f4',
                    'text' => $preset['preview_text'] ?? '#18181b',
                    'muted' => '#71717a',
                    'accent' => $preset['preview_accent'] ?? '#c59b27',
                    'accent_contrast' => '#ffffff',
                    'border' => 'rgba(24, 24, 27, 0.12)',
                    'is_dark' => in_array($key, ['noire', 'orbit', 'nexus', 'elysium', 'kinetic'], true),
                ],
                'typography' => [
                    'heading_font' => $preset['display_font'] ?? 'Playfair Display',
                    'body_font' => $preset['body_font'] ?? 'Inter',
                    'heading_weight' => '700',
                    'heading_tracking' => '-0.02em',
                    'label_style' => 'uppercase-spaced',
                ],
                'motion' => ['profile' => 'quiet-gallery', 'duration_ms' => 450, 'easing' => 'ease-out', 'enable_parallax' => false, 'stagger_ms' => 40],
                'layout' => [
                    'header_template' => 'legacy',
                    'hero_template' => 'legacy',
                    'card_template' => 'legacy',
                    'pdp_template' => 'luxury-lookbook',
                    'footer_template' => 'legacy',
                    'collection_layout' => 'clean-4col',
                    'card_aspect' => '3/4',
                    'default_columns' => 4,
                    'radius' => '12px',
                ],
                'default_sections' => [],
            ];
        }
        return $mapped;
    }

    /**
     * Complete trusted allowlisted registry: 11 legacy + 50 bespoke = 61 total themes.
     */
    public function getAllThemes(): array
    {
        $all = [];
        foreach ($this->getLegacyThemes() as $id => $def) {
            $all[$id] = $def;
        }
        foreach ($this->getNewThemes() as $id => $def) {
            $def['is_legacy'] = false;
            $def['is_free'] = false;
            $all[$id] = $def;
        }
        return $all;
    }

    public function isValidThemeId(string $themeId): bool
    {
        $clean = strtolower(trim($themeId));
        return isset($this->getAllThemes()[$clean]);
    }

    public function isNewTheme(string $themeId): bool
    {
        $clean = strtolower(trim($themeId));
        return isset($this->getNewThemes()[$clean]);
    }

    public function isLegacyTheme(string $themeId): bool
    {
        $clean = strtolower(trim($themeId));
        return isset($this->getLegacyThemes()[$clean]);
    }

    public function getManifest(string $themeId): ?array
    {
        $clean = strtolower(trim($themeId));
        return $this->getAllThemes()[$clean] ?? null;
    }

    /**
     * Build a compositional uniqueness matrix across all 50 new themes.
     * Proves no two themes share an identical structural layout + typography + section order.
     */
    public function getThemeUniquenessMatrix(): array
    {
        $matrix = [];
        foreach ($this->getNewThemes() as $id => $m) {
            $layout = $m['layout'];
            $typo = $m['typography'];
            $sectionOrder = implode('>', array_map(fn ($s) => ($s['type'] ?? '') . ':' . ($s['style'] ?? 'std'), $m['default_sections'] ?? []));
            $signature = implode('|', [
                $layout['header_template'],
                $layout['hero_template'],
                $layout['card_template'],
                $layout['pdp_template'],
                $layout['footer_template'],
                $typo['heading_font'],
                $sectionOrder,
            ]);
            $matrix[$id] = [
                'id' => $id,
                'name' => $m['name'],
                'category' => $m['category'],
                'header' => $layout['header_template'],
                'hero' => $layout['hero_template'],
                'card' => $layout['card_template'],
                'pdp' => $layout['pdp_template'],
                'footer' => $layout['footer_template'],
                'font' => $typo['heading_font'] . ' / ' . $typo['body_font'],
                'motion' => $m['motion']['profile'],
                'section_order' => $sectionOrder,
                'signature' => hash('sha256', $signature),
            ];
        }
        return $matrix;
    }

    /**
     * Build a safe default customization configuration for any of the 50 new themes.
     */
    public function getDefaultConfig(string $themeId, ?\App\Models\Vendor $vendor = null): array
    {
        $manifest = $this->getManifest($themeId);
        if (!$manifest) {
            $manifest = $this->getNewThemes()['maison-elite'];
        }

        if (!empty($manifest['is_legacy'])) {
            return app(StorefrontPresetService::class)->getDefaultConfig($manifest['id'], $vendor);
        }

        $palette = $manifest['palette'];
        $typo = $manifest['typography'];
        $layout = $manifest['layout'];

        return [
            'theme_id' => $manifest['id'],
            'preset' => $manifest['id'],
            'theme_version' => $manifest['version'],
            'palette' => [
                'accent' => $palette['accent'],
                'bg' => $palette['bg'],
                'surface' => $palette['surface'],
                'text' => $palette['text'],
            ],
            'typography' => [
                'heading_font' => $typo['heading_font'],
                'body_font' => $typo['body_font'],
            ],
            'header' => [
                'logo_size' => 'md',
                'sticky' => true,
                'show_search' => true,
                'announcement_enabled' => false,
                'announcement_text' => '',
                'announcement_link' => '',
            ],
            'hero' => [
                'eyebrow' => '',
                'headline' => $vendor?->shop_name ?? 'Featured Collection',
                'subheadline' => $vendor?->description ? \Illuminate\Support\Str::limit(strip_tags((string) $vendor->description), 160) : '',
                'cta_label' => 'Explore Collection',
                'cta_url' => '#collection',
                'secondary_cta_label' => '',
                'secondary_cta_url' => '',
                'image_url' => $vendor?->banner_url ?? '',
                'release_label' => '',
                'release_deadline' => '',
            ],
            'editorial' => [
                'eyebrow' => '',
                'heading' => '',
                'body' => '',
                'image_url' => '',
            ],
            'grid' => [
                'columns' => (int) ($layout['default_columns'] ?? 4),
                'card_aspect' => (string) ($layout['card_aspect'] ?? '3/4'),
                'spacing' => 'normal',
                'content_align' => 'left',
            ],
            'motion' => [
                'intensity' => 'normal',
            ],
            'popup' => [
                'enabled' => false,
                'heading' => '',
                'body' => '',
                'cta_label' => '',
                'cta_url' => '',
                'delay_seconds' => 8,
            ],
            'footer' => [
                'about_text' => '',
                'show_policies' => true,
                'show_contact' => true,
                'copyright_text' => '',
            ],
            'sections' => array_values($manifest['default_sections']),
        ];
    }

    /**
     * Validate and sanitize vendor customization payload for a theme.
     */
    public function sanitizeConfig(string $themeId, array $input, ?\App\Models\Vendor $vendor = null): array
    {
        $manifest = $this->getManifest($themeId);
        if (!$manifest) {
            throw new \InvalidArgumentException("Unknown theme ID: {$themeId}");
        }

        if (!empty($manifest['is_legacy'])) {
            $input['preset'] = $manifest['id'];
            return app(StorefrontPresetService::class)->sanitizeConfig($input, $vendor);
        }

        $defaults = $this->getDefaultConfig($themeId, $vendor);
        $clean = $defaults;

        if (isset($input['palette']) && is_array($input['palette'])) {
            foreach (['accent', 'bg', 'surface', 'text'] as $colorKey) {
                $val = trim((string) ($input['palette'][$colorKey] ?? ''));
                if (preg_match('/^#[0-9a-fA-F]{6}$/', $val)) {
                    $clean['palette'][$colorKey] = strtolower($val);
                }
            }
        }

        if (isset($input['typography']) && is_array($input['typography'])) {
            $hf = trim((string) ($input['typography']['heading_font'] ?? ''));
            if (in_array($hf, self::ALLOWED_HEADING_FONTS, true)) {
                $clean['typography']['heading_font'] = $hf;
            }
            $bf = trim((string) ($input['typography']['body_font'] ?? ''));
            if (in_array($bf, self::ALLOWED_BODY_FONTS, true)) {
                $clean['typography']['body_font'] = $bf;
            }
        }

        if (isset($input['header']) && is_array($input['header'])) {
            $logoSize = (string) ($input['header']['logo_size'] ?? 'md');
            $clean['header']['logo_size'] = in_array($logoSize, ['sm', 'md', 'lg'], true) ? $logoSize : 'md';
            $clean['header']['sticky'] = !empty($input['header']['sticky']);
            $clean['header']['show_search'] = !empty($input['header']['show_search']);
            $clean['header']['announcement_enabled'] = !empty($input['header']['announcement_enabled']);
            $clean['header']['announcement_text'] = mb_substr(trim(strip_tags((string) ($input['header']['announcement_text'] ?? ''))), 0, 140);
            $clean['header']['announcement_link'] = $this->sanitizeSafeUrl((string) ($input['header']['announcement_link'] ?? ''));
        }

        if (isset($input['hero']) && is_array($input['hero'])) {
            $clean['hero']['eyebrow'] = mb_substr(trim(strip_tags((string) ($input['hero']['eyebrow'] ?? ''))), 0, 60);
            $headline = mb_substr(trim(strip_tags((string) ($input['hero']['headline'] ?? ''))), 0, 120);
            $clean['hero']['headline'] = $headline !== '' ? $headline : $defaults['hero']['headline'];
            $clean['hero']['subheadline'] = mb_substr(trim(strip_tags((string) ($input['hero']['subheadline'] ?? ''))), 0, 260);
            $ctaLabel = mb_substr(trim(strip_tags((string) ($input['hero']['cta_label'] ?? ''))), 0, 40);
            $clean['hero']['cta_label'] = $ctaLabel !== '' ? $ctaLabel : 'Explore Collection';
            $clean['hero']['cta_url'] = $this->sanitizeSafeUrl((string) ($input['hero']['cta_url'] ?? '#collection')) ?: '#collection';
            $clean['hero']['secondary_cta_label'] = mb_substr(trim(strip_tags((string) ($input['hero']['secondary_cta_label'] ?? ''))), 0, 40);
            $clean['hero']['secondary_cta_url'] = $this->sanitizeSafeUrl((string) ($input['hero']['secondary_cta_url'] ?? ''));
            $clean['hero']['image_url'] = $this->sanitizeSafeMediaUrl((string) ($input['hero']['image_url'] ?? $defaults['hero']['image_url']));
            $clean['hero']['release_label'] = mb_substr(trim(strip_tags((string) ($input['hero']['release_label'] ?? ''))), 0, 60);
            $deadline = trim((string) ($input['hero']['release_deadline'] ?? ''));
            $clean['hero']['release_deadline'] = ($deadline !== '' && strtotime($deadline) !== false) ? date('c', strtotime($deadline)) : '';
        }

        if (isset($input['editorial']) && is_array($input['editorial'])) {
            $clean['editorial']['eyebrow'] = mb_substr(trim(strip_tags((string) ($input['editorial']['eyebrow'] ?? ''))), 0, 60);
            $clean['editorial']['heading'] = mb_substr(trim(strip_tags((string) ($input['editorial']['heading'] ?? ''))), 0, 120);
            $clean['editorial']['body'] = mb_substr(trim(strip_tags((string) ($input['editorial']['body'] ?? ''))), 0, 600);
            $clean['editorial']['image_url'] = $this->sanitizeSafeMediaUrl((string) ($input['editorial']['image_url'] ?? ''));
        }

        if (isset($input['grid']) && is_array($input['grid'])) {
            $cols = (int) ($input['grid']['columns'] ?? $defaults['grid']['columns']);
            $clean['grid']['columns'] = in_array($cols, [2, 3, 4], true) ? $cols : $defaults['grid']['columns'];
            $aspect = (string) ($input['grid']['card_aspect'] ?? $defaults['grid']['card_aspect']);
            $clean['grid']['card_aspect'] = in_array($aspect, ['3/4', '4/5', '1/1', '4/3'], true) ? $aspect : $defaults['grid']['card_aspect'];
            $spacing = (string) ($input['grid']['spacing'] ?? 'normal');
            $clean['grid']['spacing'] = in_array($spacing, ['compact', 'normal', 'spacious'], true) ? $spacing : 'normal';
            $align = (string) ($input['grid']['content_align'] ?? 'left');
            $clean['grid']['content_align'] = in_array($align, ['left', 'center'], true) ? $align : 'left';
        }

        if (isset($input['motion']) && is_array($input['motion'])) {
            $intensity = (string) ($input['motion']['intensity'] ?? 'normal');
            $clean['motion']['intensity'] = in_array($intensity, ['normal', 'subtle', 'reduced'], true) ? $intensity : 'normal';
        }

        if (isset($input['popup']) && is_array($input['popup'])) {
            $clean['popup']['enabled'] = !empty($input['popup']['enabled']);
            $clean['popup']['heading'] = mb_substr(trim(strip_tags((string) ($input['popup']['heading'] ?? ''))), 0, 100);
            $clean['popup']['body'] = mb_substr(trim(strip_tags((string) ($input['popup']['body'] ?? ''))), 0, 260);
            $clean['popup']['cta_label'] = mb_substr(trim(strip_tags((string) ($input['popup']['cta_label'] ?? ''))), 0, 40);
            $clean['popup']['cta_url'] = $this->sanitizeSafeUrl((string) ($input['popup']['cta_url'] ?? ''));
            $clean['popup']['delay_seconds'] = max(4, min(60, (int) ($input['popup']['delay_seconds'] ?? 8)));
        }

        if (isset($input['footer']) && is_array($input['footer'])) {
            $clean['footer']['about_text'] = mb_substr(trim(strip_tags((string) ($input['footer']['about_text'] ?? ''))), 0, 320);
            $clean['footer']['show_policies'] = array_key_exists('show_policies', $input['footer']) ? (bool) $input['footer']['show_policies'] : true;
            $clean['footer']['show_contact'] = array_key_exists('show_contact', $input['footer']) ? (bool) $input['footer']['show_contact'] : true;
            $clean['footer']['copyright_text'] = mb_substr(trim(strip_tags((string) ($input['footer']['copyright_text'] ?? ''))), 0, 160);
        }

        if (isset($input['sections']) && is_array($input['sections'])) {
            $validatedSections = [];
            $seenTypes = [];
            foreach ($input['sections'] as $sec) {
                if (!is_array($sec)) {
                    continue;
                }
                $type = (string) ($sec['type'] ?? '');
                if (!in_array($type, self::ALLOWED_SECTION_TYPES, true) || in_array($type, $seenTypes, true)) {
                    continue;
                }
                $seenTypes[] = $type;
                $entry = [
                    'type' => $type,
                    'enabled' => array_key_exists('enabled', $sec) ? (bool) $sec['enabled'] : true,
                ];
                if (!empty($sec['title'])) {
                    $entry['title'] = mb_substr(trim(strip_tags((string) $sec['title'])), 0, 80);
                }
                if (!empty($sec['style']) && preg_match('/^[a-z0-9\-]+$/', (string) $sec['style'])) {
                    $entry['style'] = (string) $sec['style'];
                }
                $validatedSections[] = $entry;
            }
            if (!empty($validatedSections)) {
                $clean['sections'] = $validatedSections;
            }
        }

        $clean['theme_id'] = $manifest['id'];
        $clean['preset'] = $manifest['id'];
        $clean['theme_version'] = $manifest['version'];

        return $clean;
    }

    public function resolveEffectiveConfig(string $themeId, ?array $savedConfig, ?\App\Models\Vendor $vendor = null): array
    {
        $defaults = $this->getDefaultConfig($themeId, $vendor);
        if (empty($savedConfig)) {
            return $defaults;
        }
        if (($savedConfig['theme_id'] ?? $savedConfig['preset'] ?? null) !== $themeId) {
            return $defaults;
        }
        return $this->sanitizeConfig($themeId, array_replace_recursive($defaults, $savedConfig), $vendor);
    }

    protected function sanitizeSafeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return str_starts_with($url, '//') ? '' : mb_substr($url, 0, 255);
        }
        if (preg_match('#^https?://#i', $url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return mb_substr($url, 0, 255);
        }
        return '';
    }

    protected function sanitizeSafeMediaUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '/storage/') || str_starts_with($url, '/images/') || str_starts_with($url, 'storage/')) {
            if (str_contains($url, '..') || str_contains($url, "\0")) {
                return '';
            }
            return str_starts_with($url, '/') ? $url : '/' . $url;
        }
        if (preg_match('#^https?://#i', $url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return mb_substr($url, 0, 300);
        }
        return '';
    }
}
