<?php

namespace App\Services\Storefront;

/**
 * Maps all 50 bespoke K71 themes to genuinely distinct Page Architectures,
 * GSAP/ScrollTrigger Motion Choreographies, 3D Depth Treatments, and Signature Moments.
 *
 * Preserves all 50 existing theme_id slugs so vendor entitlements, drafts, and live stores stay intact.
 */
class ThemeArchitectureMap
{
    public const ARCHITECTURES = [
        'editorial-runway' => 'Editorial Runway & Offset Lookbook',
        'left-rail-gallery' => 'Left Vertical Rail Museum Gallery',
        'cinematic-dark-stage' => 'Cinematic Dark Stage & Cinema Reel',
        'swiss-manifesto' => 'Swiss Typographic Grid Manifesto',
        'brutalist-poster' => 'Brutalist Poster & Scrubbed Marquee',
        'spatial-glass-studio' => 'Spatial Liquid Glass 3D Studio',
        'split-screen-showroom' => '50/50 Sticky Split-Screen Showroom',
        'bento-modular-flagship' => '12-Col Bento Modular Flagship',
        'color-block-editorial' => 'Full-Bleed Color-Block Art Gallery',
        'pro-commerce-catalog' => 'Pro-Commerce Filter Rail & Fast Matrix',
    ];

    public const MOTION_CHOREOGRAPHIES = [
        'quiet-editorial',
        'cinematic-curtain',
        'swiss-grid-snap',
        'japanese-ma',
        'architectural-3d',
        'spatial-glass-float',
        'brutalist-kinetic',
        'horizontal-cinema',
        'editorial-asymmetry',
        'sneaker-vault-3d',
        'tech-precision',
        'fast-commerce-light',
    ];

    public static function all(): array
    {
        return [
            // ── CATEGORY A: LUXURY & FASHION (01-12) ─────────────────────────────────
            'maison-elite' => [
                'page_architecture' => 'editorial-runway',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Full-height couture runway frame with masked curtain photography reveal and offset editorial lookbook pairs',
                'band_colors' => ['#f7f4ef', '#1a1816', '#ede6dc'],
            ],
            'atelier-blanc' => [
                'page_architecture' => 'left-rail-gallery',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'signature_moment' => 'Fixed architectural left gallery rail beside a whisper-white museum wall of unframed plinth products',
                'band_colors' => ['#ffffff', '#f8f8f6', '#111111'],
            ],
            'obsidian' => [
                'page_architecture' => 'cinematic-dark-stage',
                'motion_choreography' => 'cinematic-curtain',
                'depth_3d' => 'architectural-depth',
                'signature_moment' => 'Nocturnal full-viewport spotlight stage with GSAP zoom-out hero and horizontal dark cinema lookbook reel',
                'band_colors' => ['#070709', '#111115', '#18181f'],
            ],
            'monogram' => [
                'page_architecture' => 'pro-commerce-catalog',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Heritage-framed luxury house directory with gilded border rules and structured department ledger',
                'band_colors' => ['#f6f1e7', '#231b14', '#ebe2d2'],
            ],
            'the-curator' => [
                'page_architecture' => 'swiss-manifesto',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'Sticky curatorial index sidebar beside numbered [01] editorial issue plates with counter-parallax scroll',
                'band_colors' => ['#f5f3ef', '#141414', '#e8e3da'],
            ],
            'runway-01' => [
                'page_architecture' => 'editorial-runway',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'none',
                'signature_moment' => 'High-contrast asymmetric runway campaign with scroll-scrubbed collection ticker and oversized editorial diptychs',
                'band_colors' => ['#ffffff', '#09090b', '#f4f4f5'],
            ],
            'studio-label' => [
                'page_architecture' => 'spatial-glass-studio',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'signature_moment' => 'Floating frosted studio navigation pill over an asymmetric 3D-tilted lookbook stage',
                'band_colors' => ['#f6f7f9', '#e8ecf2', '#0f172a'],
            ],
            'soft-form' => [
                'page_architecture' => 'spatial-glass-studio',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'floating-glass',
                'signature_moment' => 'Sculptural organic arch stage with tactile floating surfaces and gentle spring transitions',
                'band_colors' => ['#f8f3ef', '#efe5dd', '#3d2f28'],
            ],
            'street-culture' => [
                'page_architecture' => 'brutalist-poster',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'perspective-tilt',
                'signature_moment' => 'Raw graphic street-poster wall with thick 2px borders, GSAP scroll-scrubbed marquee, and stark hover inversions',
                'band_colors' => ['#f4f4f0', '#111111', '#e6ff00'],
            ],
            'modest-luxe' => [
                'page_architecture' => 'editorial-runway',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'signature_moment' => 'Poised warm-alabaster editorial sanctuary with tranquil vertical lookbook drapery and slow opacity cadence',
                'band_colors' => ['#faf7f2', '#2c241d', '#efe7dc'],
            ],
            'denim-district' => [
                'page_architecture' => 'brutalist-poster',
                'motion_choreography' => 'swiss-grid-snap',
                'depth_3d' => 'none',
                'signature_moment' => 'Raw indigo workwear poster ledger with stitched hairline rules, lot-numbered cards, and crisp grid snaps',
                'band_colors' => ['#f2f4f7', '#132238', '#dce3ec'],
            ],
            'sartorial' => [
                'page_architecture' => 'swiss-manifesto',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Savile-inspired bespoke ledger with sticky tailoring index and disciplined numbered garment showcase',
                'band_colors' => ['#f7f5f0', '#1c2420', '#eae4d8'],
            ],

            // ── CATEGORY B: FOOTWEAR & PERFORMANCE (13-17) ───────────────────────────
            'sneaker-vault' => [
                'page_architecture' => 'brutalist-poster',
                'motion_choreography' => 'sneaker-vault-3d',
                'depth_3d' => 'specular-vault',
                'signature_moment' => '3D perspective collector vault showcase with cursor-tracked specular sheen and rapid size-matrix PDP',
                'band_colors' => ['#0c0d10', '#16181e', '#ff4d00'],
            ],
            'motion-lab' => [
                'page_architecture' => 'brutalist-poster',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'perspective-tilt',
                'signature_moment' => 'Aerodynamic performance stage with diagonal kinetics and horizontal scroll-linked athlete release rail',
                'band_colors' => ['#0d1117', '#161f2c', '#00e5a0'],
            ],
            'sole-archive' => [
                'page_architecture' => 'left-rail-gallery',
                'motion_choreography' => 'swiss-grid-snap',
                'depth_3d' => 'none',
                'signature_moment' => 'Archival footwear museum index with persistent left year/category rail and clean side-profile plinths',
                'band_colors' => ['#f7f7f5', '#ecece8', '#141414'],
            ],
            'velocity' => [
                'page_architecture' => 'cinematic-dark-stage',
                'motion_choreography' => 'sneaker-vault-3d',
                'depth_3d' => 'specular-vault',
                'signature_moment' => 'Dark high-velocity sprint theater with 3D specular footwear stage and scrubbed horizontal release reel',
                'band_colors' => ['#08090c', '#12151c', '#38bdf8'],
            ],
            'minimal-sole' => [
                'page_architecture' => 'left-rail-gallery',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'signature_moment' => 'Scandinavian footwear studio rail with serene silhouette plinths and gentle tactile hover elevation',
                'band_colors' => ['#fcfcfb', '#f1f0ec', '#1c1b19'],
            ],

            // ── CATEGORY C: WATCHES, JEWELRY & OBJECTS (18-22) ───────────────────────
            'chronograph' => [
                'page_architecture' => 'cinematic-dark-stage',
                'motion_choreography' => 'tech-precision',
                'depth_3d' => 'specular-vault',
                'signature_moment' => 'Precision horology dark vault with calibrated spec lines, specular dial tilt, and mechanical reveal timing',
                'band_colors' => ['#090b0f', '#131720', '#c8a45d'],
            ],
            'jewelle' => [
                'page_architecture' => 'editorial-runway',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'Luminous champagne couture showcase with delicate macro frames and silk-smooth editorial transitions',
                'band_colors' => ['#fbf8f4', '#261e1a', '#f2e8dc'],
            ],
            'goldline' => [
                'page_architecture' => 'pro-commerce-catalog',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Neo-classical gold-hairline salon with formal symmetrical framing and high-trust hallmark presentation',
                'band_colors' => ['#fcfaf5', '#1d1914', '#f3ead8'],
            ],
            'artifact' => [
                'page_architecture' => 'left-rail-gallery',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'architectural-depth',
                'signature_moment' => 'Museum sculpture gallery rail with architectural perspective plinth reveals and exhibition catalog cards',
                'band_colors' => ['#f6f4f0', '#eae6de', '#181715'],
            ],
            'luxe-objects' => [
                'page_architecture' => 'cinematic-dark-stage',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'Nocturnal objet-d-art showroom with counter-parallax gallery plates and dramatic warm rim lighting',
                'band_colors' => ['#0e0d0c', '#1a1815', '#d6b47c'],
            ],

            // ── CATEGORY D: ELECTRONICS, TECH & AUDIO (23-27) ────────────────────────
            'future-form' => [
                'page_architecture' => 'spatial-glass-studio',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'signature_moment' => 'Spatial translucent glass command pill + floating 3D hardware stage with magnetic CTA feedback',
                'band_colors' => ['#0b101b', '#131c2e', '#60a5fa'],
            ],
            'circuit' => [
                'page_architecture' => 'bento-modular-flagship',
                'motion_choreography' => 'tech-precision',
                'depth_3d' => 'specular-vault',
                'signature_moment' => '12-column precision engineering Bento Grid with live telemetry specs and dark circuit blueprint framing',
                'band_colors' => ['#090c10', '#121821', '#22d3ee'],
            ],
            'mono-tech' => [
                'page_architecture' => 'swiss-manifesto',
                'motion_choreography' => 'swiss-grid-snap',
                'depth_3d' => 'none',
                'signature_moment' => 'Dieter Rams–inspired industrial Swiss grid with crisp monochrome specs and zero decorative noise',
                'band_colors' => ['#f4f4f2', '#111111', '#ff4f00'],
            ],
            'pixel-foundry' => [
                'page_architecture' => 'bento-modular-flagship',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'perspective-tilt',
                'signature_moment' => 'Modular creator Bento flagship combining interactive product cards, spec tiles, and tactile tilt response',
                'band_colors' => ['#f8fafc', '#0f172a', '#6366f1'],
            ],
            'audio-atelier' => [
                'page_architecture' => 'cinematic-dark-stage',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'architectural-depth',
                'signature_moment' => 'Acoustic hi-fi dark listening room with horizontal product cinema reel and brushed-metal precision specs',
                'band_colors' => ['#0d0c0b', '#191715', '#e59a47'],
            ],

            // ── CATEGORY E: FURNITURE, HOME & ARCHITECTURE (28-32) ───────────────────
            'casa-moderna' => [
                'page_architecture' => 'split-screen-showroom',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'architectural-depth',
                'signature_moment' => '50/50 sticky interior showroom split-screen paired with wide 16:10 spatial room photography and dimension specs',
                'band_colors' => ['#f6f3ee', '#24201b', '#e8e0d5'],
            ],
            'nordic-living' => [
                'page_architecture' => 'split-screen-showroom',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'signature_moment' => 'Calm birch-and-linen split showroom with airy 3-column daylight furniture cards and tranquil transitions',
                'band_colors' => ['#faf9f6', '#2b2d2f', '#eeece6'],
            ],
            'forma-gallery' => [
                'page_architecture' => 'left-rail-gallery',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'Architectural furniture & marble slab gallery rail with counter-parallax monumental material photography',
                'band_colors' => ['#f4f2ee', '#e6e1d9', '#1a1917'],
            ],
            'earth-and-grain' => [
                'page_architecture' => 'split-screen-showroom',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Organic timber-and-stoneware split showcase highlighting tactile raw textures and artisan provenance',
                'band_colors' => ['#f5efe6', '#33251c', '#e6dac8'],
            ],
            'habitat-studio' => [
                'page_architecture' => 'bento-modular-flagship',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'perspective-tilt',
                'signature_moment' => 'Spatial room-by-room Bento architecture combining hero interior canvas with modular category launch tiles',
                'band_colors' => ['#f7f6f2', '#1f2937', '#ebe7de'],
            ],

            // ── CATEGORY F: BEAUTY, SKINCARE & WELLNESS (33-37) ──────────────────────
            'botanical-lab' => [
                'page_architecture' => 'split-screen-showroom',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'signature_moment' => 'Apothecary split-screen ritual canvas with botanical formulation notes and calm organic scroll reveals',
                'band_colors' => ['#f4f6f1', '#1e2b22', '#e4eadc'],
            ],
            'skin-ritual' => [
                'page_architecture' => 'spatial-glass-studio',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'signature_moment' => 'Luminous soft-sculptural beauty sanctuary with frosted glass capsules and delicate macro imagery',
                'band_colors' => ['#fdf7f4', '#f7e9e3', '#3b2824'],
            ],
            'pure-science' => [
                'page_architecture' => 'swiss-manifesto',
                'motion_choreography' => 'tech-precision',
                'depth_3d' => 'none',
                'signature_moment' => 'Clinical dermatology Swiss ledger with numbered [01] formulation index and high-clarity lab typography',
                'band_colors' => ['#ffffff', '#0f172a', '#f1f5f9'],
            ],
            'velvet-beauty' => [
                'page_architecture' => 'editorial-runway',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'High-glam editorial cosmetics runway with velvet plum contrast bands and counter-parallax beauty spreads',
                'band_colors' => ['#1a0f16', '#fdf4f7', '#d9468f'],
            ],
            'daily-ritual' => [
                'page_architecture' => 'spatial-glass-studio',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Warm minimalist daily-care studio with floating capsule navigation and effortless clean conversion flow',
                'band_colors' => ['#faf7f2', '#efe8dd', '#2c2520'],
            ],

            // ── CATEGORY G: FOOD, GOURMET & CAFE (38-42) ─────────────────────────────
            'maison-gourmet' => [
                'page_architecture' => 'color-block-editorial',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Epicurean burgundy-and-parchment full-bleed color-block menu showcase with rich tasting hierarchy',
                'band_colors' => ['#fbf6ee', '#3b1019', '#efe2cd'],
            ],
            'fresh-market' => [
                'page_architecture' => 'pro-commerce-catalog',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'signature_moment' => 'High-speed grocery & produce storefront with sticky left department rail and instant quick-add matrix',
                'band_colors' => ['#f8faf7', '#14532d', '#ecfdf3'],
            ],
            'artisan-pantry' => [
                'page_architecture' => 'pro-commerce-catalog',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'signature_moment' => 'Craft larder & small-batch pantry directory with warm editorial framing and rapid category filtering',
                'band_colors' => ['#faf6ee', '#452717', '#f2e8d5'],
            ],
            'coffee-culture' => [
                'page_architecture' => 'color-block-editorial',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'Specialty roastery espresso-and-crema color blocks with roast-note cards and tactile scroll depth',
                'band_colors' => ['#f7f1e8', '#1f1510', '#d97736'],
            ],
            'sweet-atelier' => [
                'page_architecture' => 'color-block-editorial',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'signature_moment' => 'Patisserie pastel color-block gallery with playful spring reveals and luminous confection framing',
                'band_colors' => ['#fff7f8', '#4a1d2f', '#fde2e8'],
            ],

            // ── CATEGORY H: CONTEMPORARY & NICHE CONCEPTS (43-50) ────────────────────
            'neo-commerce' => [
                'page_architecture' => 'bento-modular-flagship',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'perspective-tilt',
                'signature_moment' => 'Signature D2C modular Bento flagship with horizontal featured reel and magnetic conversion actions',
                'band_colors' => ['#ffffff', '#09090b', '#f4f4f5'],
            ],
            'the-drop' => [
                'page_architecture' => 'brutalist-poster',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'specular-vault',
                'signature_moment' => 'Limited-edition drop bunker with scroll-scrubbed release marquee, real countdown support, and 3D vault cards',
                'band_colors' => ['#0a0a0c', '#18181b', '#f97316'],
            ],
            'market-studio' => [
                'page_architecture' => 'bento-modular-flagship',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'signature_moment' => 'Multi-department modern flagship Bento with instant category jump tiles and structured clean catalog',
                'band_colors' => ['#f9fafb', '#111827', '#eef2ff'],
            ],
            'pop-form' => [
                'page_architecture' => 'color-block-editorial',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'perspective-tilt',
                'signature_moment' => 'Y2K / Pop-art graphic color-block gallery with bold cobalt/citron fields and energetic kinetic snaps',
                'band_colors' => ['#fffdf5', '#1d4ed8', '#facc15'],
            ],
            'color-theory' => [
                'page_architecture' => 'color-block-editorial',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'none',
                'signature_moment' => 'Curated chromatic exhibition alternating dramatic terracotta, obsidian, and bone full-bleed bands',
                'band_colors' => ['#f9f5ef', '#9a3412', '#1c1917'],
            ],
            'heritage-house' => [
                'page_architecture' => 'pro-commerce-catalog',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'signature_moment' => 'Storied flagship house with double-ruled architectural borders and refined department showcase',
                'band_colors' => ['#f6f2ea', '#1b2b24', '#e8dfd0'],
            ],
            'field-and-found' => [
                'page_architecture' => 'split-screen-showroom',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'layered-parallax',
                'signature_moment' => 'Expedition outdoor split-screen showroom with rugged canvas framing and technical gear specifications',
                'band_colors' => ['#f3f1ea', '#253326', '#e2dfd4'],
            ],
            'the-specialist' => [
                'page_architecture' => 'swiss-manifesto',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'signature_moment' => 'High-trust B2B & industrial trade manifesto with strict SKU/spec table-grid clarity and zero friction',
                'band_colors' => ['#f8fafc', '#0f172a', '#e2e8f0'],
            ],
        ];
    }

    public static function forTheme(string $themeId): array
    {
        return self::all()[$themeId] ?? [
            'page_architecture' => 'editorial-runway',
            'motion_choreography' => 'quiet-editorial',
            'depth_3d' => 'none',
            'signature_moment' => 'Curated editorial storefront composition',
            'band_colors' => ['#ffffff', '#111111', '#f4f4f5'],
        ];
    }
}
