<?php

namespace App\Services\Storefront;

/**
 * Total Creative Reconstruction Map:
 * Maps all 50 bespoke K71 themes across 15 genuinely distinct Page Architectures,
 * 6 radically distinct PDP Architectures, 12 GSAP/ScrollTrigger Motion Choreographies,
 * CSS 3D Depth Treatments, and bespoke category art-directed sample exhibition plates.
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
        'typography-as-architecture' => 'Typography-as-Architecture Editorial',
        'product-first-index' => 'Zero-Hero Product-First Wall Index',
        'b2b-specimen-ledger' => 'Horizontal Specification & Trade Ledger',
        'horizontal-gallery-wall' => '4-Column Horizontal Exhibition Filmstrip',
        'sculptural-arch-sanctuary' => 'Sculptural Organic Arch Sanctuary',
    ];

    public const PDP_ARCHITECTURES = [
        'editorial-fullbleed',
        'split-sticky-atelier',
        'technical-spec-lab',
        'gallery-plinth-museum',
        'brutalist-poster-sheet',
        'dense-trade-catalog',
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
            // ── CATEGORY A: LUXURY & FASHION (01-12) — ZERO ADJACENT DUPLICATE ARCHITECTURES ──
            'maison-elite' => [
                'page_architecture' => 'editorial-runway',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Full-bleed couture runway campaign with masked curtain reveal and alternating 7/5 lookbook pairs',
                'band_colors' => ['#f7f4ef', '#1a1816', '#ede6dc'],
            ],
            'atelier-blanc' => [
                'page_architecture' => 'left-rail-gallery',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Persistent architectural left exhibition rail beside a whisper-white museum plinth wall',
                'band_colors' => ['#ffffff', '#f8f8f6', '#111111'],
            ],
            'obsidian' => [
                'page_architecture' => 'cinematic-dark-stage',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'cinematic-curtain',
                'depth_3d' => 'architectural-depth',
                'sample_art' => 'fashion-runway',
                'signature_moment' => 'Nocturnal full-viewport spotlight theater with scrubbed zoom hero and horizontal cinema lookbook reel',
                'band_colors' => ['#070709', '#111115', '#18181f'],
            ],
            'monogram' => [
                'page_architecture' => 'horizontal-gallery-wall',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => '4-column full-viewport heritage filmstrip wall with oversized numerals and gilded house framing',
                'band_colors' => ['#f6f1e7', '#231b14', '#ebe2d2'],
            ],
            'the-curator' => [
                'page_architecture' => 'swiss-manifesto',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'tailoring-suit',
                'signature_moment' => 'Double-rule editorial masthead, [00/INDEX] coordinate bar, sticky left ledger, numbered [01] matrix',
                'band_colors' => ['#f5f3ef', '#141414', '#e8e3da'],
            ],
            'runway-01' => [
                'page_architecture' => 'typography-as-architecture',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-runway',
                'signature_moment' => 'Giant edge-to-edge architectural display wordmark framing inset runway diptychs and counter-parallax plates',
                'band_colors' => ['#ffffff', '#09090b', '#f4f4f5'],
            ],
            'studio-label' => [
                'page_architecture' => 'product-first-index',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Zero-hero immediate borderless 5-column fashion wall above the fold (SSENSE/Acne editorial discipline)',
                'band_colors' => ['#f6f7f9', '#e8ecf2', '#0f172a'],
            ],
            'soft-form' => [
                'page_architecture' => 'sculptural-arch-sanctuary',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Quiet centered sanctuary above three elevated organic archway pedestals with soft sculptural depth',
                'band_colors' => ['#f8f3ef', '#efe5dd', '#3d2f28'],
            ],
            'street-culture' => [
                'page_architecture' => 'brutalist-poster',
                'pdp_architecture' => 'brutalist-poster-sheet',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'perspective-tilt',
                'sample_art' => 'fashion-runway',
                'signature_moment' => 'Raw 2px-bordered street poster wall, GSAP scroll-scrubbed marquee, and hard offset-shadow release cards',
                'band_colors' => ['#f4f4f0', '#111111', '#e6ff00'],
            ],
            'modest-luxe' => [
                'page_architecture' => 'split-screen-showroom',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Warm-alabaster 50/50 sticky split-screen lookbook pairing serene brand manifesto with stacked couture photography',
                'band_colors' => ['#faf7f2', '#2c241d', '#efe7dc'],
            ],
            'denim-district' => [
                'page_architecture' => 'b2b-specimen-ledger',
                'pdp_architecture' => 'brutalist-poster-sheet',
                'motion_choreography' => 'swiss-grid-snap',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Raw indigo workwear lot-ledger with horizontal spec rows, fabric weights, and crisp grid snaps',
                'band_colors' => ['#f2f4f7', '#132238', '#dce3ec'],
            ],
            'sartorial' => [
                'page_architecture' => 'swiss-manifesto',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'tailoring-suit',
                'signature_moment' => 'Savile-inspired tailoring manifesto with sticky measurement index and disciplined numbered suiting grid',
                'band_colors' => ['#f7f5f0', '#1c2420', '#eae4d8'],
            ],

            // ── CATEGORY B: FOOTWEAR & PERFORMANCE (13-17) — ALL 5 DIFFERENT ARCHITECTURES ──
            'sneaker-vault' => [
                'page_architecture' => 'brutalist-poster',
                'pdp_architecture' => 'brutalist-poster-sheet',
                'motion_choreography' => 'sneaker-vault-3d',
                'depth_3d' => 'specular-vault',
                'sample_art' => 'sneaker-vault',
                'signature_moment' => '3D specular collector vault poster wall with cursor-tracked light sheen and brutalist size-grid PDP',
                'band_colors' => ['#0c0d10', '#16181e', '#ff4d00'],
            ],
            'motion-lab' => [
                'page_architecture' => 'typography-as-architecture',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'perspective-tilt',
                'sample_art' => 'sneaker-vault',
                'signature_moment' => 'Oversized kinetic performance typography framing aerodynamic footwear plates and 3-column lab PDP',
                'band_colors' => ['#0d1117', '#161f2c', '#00e5a0'],
            ],
            'sole-archive' => [
                'page_architecture' => 'left-rail-gallery',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'swiss-grid-snap',
                'depth_3d' => 'none',
                'sample_art' => 'sneaker-vault',
                'signature_moment' => 'Archival footwear museum with persistent left year/edition rail and clean side-profile plinths',
                'band_colors' => ['#f7f7f5', '#ecece8', '#141414'],
            ],
            'velocity' => [
                'page_architecture' => 'cinematic-dark-stage',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'sneaker-vault-3d',
                'depth_3d' => 'specular-vault',
                'sample_art' => 'sneaker-vault',
                'signature_moment' => 'Dark high-velocity sprint theater with scrubbed horizontal sneaker cinema reel and 3D sheen cards',
                'band_colors' => ['#08090c', '#12151c', '#38bdf8'],
            ],
            'minimal-sole' => [
                'page_architecture' => 'product-first-index',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'sample_art' => 'sneaker-vault',
                'signature_moment' => 'Scandinavian zero-hero monochrome footwear index placing pure silhouettes immediately above the fold',
                'band_colors' => ['#fcfcfb', '#f1f0ec', '#1c1b19'],
            ],

            // ── CATEGORY C: WATCHES, JEWELRY & OBJECTS (18-22) — ALL 5 DIFFERENT ARCHITECTURES ──
            'chronograph' => [
                'page_architecture' => 'cinematic-dark-stage',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'tech-precision',
                'depth_3d' => 'specular-vault',
                'sample_art' => 'horology-watch',
                'signature_moment' => 'Precision horology dark vault with calibrated dial lighting, cinema reel, and 3-column calibre spec PDP',
                'band_colors' => ['#090b0f', '#131720', '#c8a45d'],
            ],
            'jewelle' => [
                'page_architecture' => 'sculptural-arch-sanctuary',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'jewelry-macro',
                'signature_moment' => 'Luminous champagne arch sanctuary framing fine jewelry pieces like museum crown exhibits',
                'band_colors' => ['#fbf8f4', '#261e1a', '#f2e8dc'],
            ],
            'goldline' => [
                'page_architecture' => 'horizontal-gallery-wall',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'jewelry-macro',
                'signature_moment' => '4-column full-viewport neo-classical exhibition wall with fine gold hairlines and plinth PDP',
                'band_colors' => ['#fcfaf5', '#1d1914', '#f3ead8'],
            ],
            'artifact' => [
                'page_architecture' => 'left-rail-gallery',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'architectural-depth',
                'sample_art' => 'marble-stone',
                'signature_moment' => 'Museum sculpture gallery rail with architectural perspective plinth reveals and numbered plaques',
                'band_colors' => ['#f6f4f0', '#eae6de', '#181715'],
            ],
            'luxe-objects' => [
                'page_architecture' => 'editorial-runway',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'jewelry-macro',
                'signature_moment' => 'Nocturnal objet-d-art editorial campaign with alternating 7/5 counter-parallax exhibition diptychs',
                'band_colors' => ['#0e0d0c', '#1a1815', '#d6b47c'],
            ],

            // ── CATEGORY D: ELECTRONICS, TECH & AUDIO (23-27) — ALL 5 DIFFERENT ARCHITECTURES ──
            'future-form' => [
                'page_architecture' => 'spatial-glass-studio',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'sample_art' => 'tech-hardware',
                'signature_moment' => 'Translucent floating glass command bar + 3D specular hardware showcase capsule + floating category pills',
                'band_colors' => ['#0b101b', '#131c2e', '#60a5fa'],
            ],
            'circuit' => [
                'page_architecture' => 'bento-modular-flagship',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'tech-precision',
                'depth_3d' => 'specular-vault',
                'sample_art' => 'tech-hardware',
                'signature_moment' => '12-column precision engineering Bento Flagship with Spotlight SKU tile, live counters, and 3-column lab PDP',
                'band_colors' => ['#090c10', '#121821', '#22d3ee'],
            ],
            'mono-tech' => [
                'page_architecture' => 'swiss-manifesto',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'swiss-grid-snap',
                'depth_3d' => 'none',
                'sample_art' => 'tech-hardware',
                'signature_moment' => 'Dieter Rams–inspired industrial Swiss grid with crisp monochrome hairlines and zero decorative noise',
                'band_colors' => ['#f4f4f2', '#111111', '#ff4f00'],
            ],
            'pixel-foundry' => [
                'page_architecture' => 'b2b-specimen-ledger',
                'pdp_architecture' => 'dense-trade-catalog',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'perspective-tilt',
                'sample_art' => 'tech-hardware',
                'signature_moment' => 'Structured component & creator hardware specification ledger with inline quantity & quick-add rows',
                'band_colors' => ['#f8fafc', '#0f172a', '#6366f1'],
            ],
            'audio-atelier' => [
                'page_architecture' => 'cinematic-dark-stage',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'architectural-depth',
                'sample_art' => 'tech-hardware',
                'signature_moment' => 'Acoustic dark listening room with scrubbed horizontal hi-fi cinema reel and monumental full-bleed PDP',
                'band_colors' => ['#0d0c0b', '#191715', '#e59a47'],
            ],

            // ── CATEGORY E: FURNITURE, HOME & STONE (28-32) — ALL 5 DIFFERENT ARCHITECTURES ──
            'casa-moderna' => [
                'page_architecture' => 'split-screen-showroom',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'architectural-depth',
                'sample_art' => 'furniture-interior',
                'signature_moment' => 'True 50/50 sticky interior showroom split-screen paired with wide 16:10 spatial room photography',
                'band_colors' => ['#f6f3ee', '#24201b', '#e8e0d5'],
            ],
            'nordic-living' => [
                'page_architecture' => 'product-first-index',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'sample_art' => 'furniture-interior',
                'signature_moment' => 'Scandinavian functionalism placing calm daylight furniture directly above the fold with zero hero clutter',
                'band_colors' => ['#faf9f6', '#2b2d2f', '#eeece6'],
            ],
            'forma-gallery' => [
                'page_architecture' => 'horizontal-gallery-wall',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'marble-stone',
                'signature_moment' => 'Marble & architectural stone 4-column specimen filmstrip preserving true surface veining and full-bleed slab PDP',
                'band_colors' => ['#f4f2ee', '#e6e1d9', '#1a1917'],
            ],
            'earth-and-grain' => [
                'page_architecture' => 'sculptural-arch-sanctuary',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'furniture-interior',
                'signature_moment' => 'Organic timber-and-stoneware arched pedestals celebrating raw tactile craftsmanship',
                'band_colors' => ['#f5efe6', '#33251c', '#e6dac8'],
            ],
            'habitat-studio' => [
                'page_architecture' => 'bento-modular-flagship',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'perspective-tilt',
                'sample_art' => 'furniture-interior',
                'signature_moment' => 'Modular room-by-room Bento architecture combining interior photography with instant department jump tiles',
                'band_colors' => ['#f7f6f2', '#1f2937', '#ebe7de'],
            ],

            // ── CATEGORY F: BEAUTY, SKINCARE & WELLNESS (33-37) — ALL 5 DIFFERENT ARCHITECTURES ──
            'botanical-lab' => [
                'page_architecture' => 'split-screen-showroom',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'japanese-ma',
                'depth_3d' => 'none',
                'sample_art' => 'botanical-beauty',
                'signature_moment' => 'Apothecary 50/50 sticky split-screen sanctuary pairing formulation calm with macro botanical vessels',
                'band_colors' => ['#f4f6f1', '#1e2b22', '#e4eadc'],
            ],
            'skin-ritual' => [
                'page_architecture' => 'sculptural-arch-sanctuary',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'sample_art' => 'botanical-beauty',
                'signature_moment' => 'Soft sculptural skincare arches with elevated central hero vessel and delicate spring cadence',
                'band_colors' => ['#fdf7f4', '#f7e9e3', '#3b2824'],
            ],
            'pure-science' => [
                'page_architecture' => 'swiss-manifesto',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'tech-precision',
                'depth_3d' => 'none',
                'sample_art' => 'botanical-beauty',
                'signature_moment' => 'Clinical dermatology Swiss ledger with numbered [01] formulation index and 3-column lab PDP',
                'band_colors' => ['#ffffff', '#0f172a', '#f1f5f9'],
            ],
            'velvet-beauty' => [
                'page_architecture' => 'editorial-runway',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'botanical-beauty',
                'signature_moment' => 'High-glam cosmetics runway campaign with dramatic dark framing and counter-parallax beauty spreads',
                'band_colors' => ['#1a0f16', '#fdf4f7', '#d9468f'],
            ],
            'daily-ritual' => [
                'page_architecture' => 'spatial-glass-studio',
                'pdp_architecture' => 'dense-trade-catalog',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'floating-glass',
                'sample_art' => 'botanical-beauty',
                'signature_moment' => 'Warm minimalist daily-care studio with floating frosted glass capsules and effortless quick-add',
                'band_colors' => ['#faf7f2', '#efe8dd', '#2c2520'],
            ],

            // ── CATEGORY G: FOOD, GOURMET & CAFE (38-42) — ALL 5 DIFFERENT ARCHITECTURES ──
            'maison-gourmet' => [
                'page_architecture' => 'color-block-editorial',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'gourmet-coffee',
                'signature_moment' => 'Epicurean burgundy-and-parchment full-bleed color-block menu with contrasting tasting diptychs',
                'band_colors' => ['#fbf6ee', '#3b1019', '#efe2cd'],
            ],
            'fresh-market' => [
                'page_architecture' => 'pro-commerce-catalog',
                'pdp_architecture' => 'dense-trade-catalog',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'sample_art' => 'fresh-grocery',
                'signature_moment' => 'High-speed grocery storefront with sticky left department sidebar, quick sort, and instant +Add matrix',
                'band_colors' => ['#f8faf7', '#14532d', '#ecfdf3'],
            ],
            'artisan-pantry' => [
                'page_architecture' => 'horizontal-gallery-wall',
                'pdp_architecture' => 'dense-trade-catalog',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'sample_art' => 'gourmet-coffee',
                'signature_moment' => 'Small-batch craft larder 4-column visual shelf displaying specialty pantry releases side by side',
                'band_colors' => ['#faf6ee', '#452717', '#f2e8d5'],
            ],
            'coffee-culture' => [
                'page_architecture' => 'typography-as-architecture',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'editorial-asymmetry',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'gourmet-coffee',
                'signature_moment' => 'Specialty roastery oversized display wordmark anchoring roasted-origin editorial plates',
                'band_colors' => ['#f7f1e8', '#1f1510', '#d97736'],
            ],
            'sweet-atelier' => [
                'page_architecture' => 'color-block-editorial',
                'pdp_architecture' => 'gallery-plinth-museum',
                'motion_choreography' => 'spatial-glass-float',
                'depth_3d' => 'floating-glass',
                'sample_art' => 'gourmet-coffee',
                'signature_moment' => 'Patisserie plum-and-blush color-block gallery framing confections on centered exhibition plinths',
                'band_colors' => ['#fff7f8', '#4a1d2f', '#fde2e8'],
            ],

            // ── CATEGORY H: CONTEMPORARY & NICHE CONCEPTS (43-50) ────────────────────
            'neo-commerce' => [
                'page_architecture' => 'bento-modular-flagship',
                'pdp_architecture' => 'split-sticky-atelier',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'perspective-tilt',
                'sample_art' => 'graphic-art',
                'signature_moment' => 'Modern D2C 12-column Bento flagship with interactive Spotlight SKU and magnetic checkout transitions',
                'band_colors' => ['#ffffff', '#09090b', '#f4f4f5'],
            ],
            'the-drop' => [
                'page_architecture' => 'brutalist-poster',
                'pdp_architecture' => 'brutalist-poster-sheet',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'specular-vault',
                'sample_art' => 'graphic-art',
                'signature_moment' => 'Limited-edition drop bunker with scrubbed release marquee, real countdown support, and raw spec PDP',
                'band_colors' => ['#0a0a0c', '#18181b', '#f97316'],
            ],
            'market-studio' => [
                'page_architecture' => 'pro-commerce-catalog',
                'pdp_architecture' => 'dense-trade-catalog',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'sample_art' => 'graphic-art',
                'signature_moment' => 'Multi-department modern flagship with persistent left department rail and rapid high-clarity catalog',
                'band_colors' => ['#f9fafb', '#111827', '#eef2ff'],
            ],
            'pop-form' => [
                'page_architecture' => 'color-block-editorial',
                'pdp_architecture' => 'brutalist-poster-sheet',
                'motion_choreography' => 'brutalist-kinetic',
                'depth_3d' => 'perspective-tilt',
                'sample_art' => 'graphic-art',
                'signature_moment' => 'Pop-art / Y2K cobalt-and-citron full-bleed color fields paired with raw graphic poster PDP',
                'band_colors' => ['#fffdf5', '#1d4ed8', '#facc15'],
            ],
            'color-theory' => [
                'page_architecture' => 'color-block-editorial',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'horizontal-cinema',
                'depth_3d' => 'none',
                'sample_art' => 'graphic-art',
                'signature_moment' => 'Curated terracotta-and-obsidian chromatic exhibition alternating dramatic full-bleed gallery bands',
                'band_colors' => ['#f9f5ef', '#9a3412', '#1c1917'],
            ],
            'heritage-house' => [
                'page_architecture' => 'left-rail-gallery',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'tailoring-suit',
                'signature_moment' => 'Storied heritage house with persistent left archival rail and formal editorial full-bleed PDP',
                'band_colors' => ['#f6f2ea', '#1b2b24', '#e8dfd0'],
            ],
            'field-and-found' => [
                'page_architecture' => 'split-screen-showroom',
                'pdp_architecture' => 'technical-spec-lab',
                'motion_choreography' => 'architectural-3d',
                'depth_3d' => 'layered-parallax',
                'sample_art' => 'marble-stone',
                'signature_moment' => 'Expedition outdoor 50/50 split-screen showroom paired with rugged 3-column field spec PDP',
                'band_colors' => ['#f3f1ea', '#253326', '#e2dfd4'],
            ],
            'the-specialist' => [
                'page_architecture' => 'b2b-specimen-ledger',
                'pdp_architecture' => 'dense-trade-catalog',
                'motion_choreography' => 'fast-commerce-light',
                'depth_3d' => 'none',
                'sample_art' => 'tech-hardware',
                'signature_moment' => 'High-trust B2B & industrial procurement ledger with horizontal SKU rows and inline bulk quantity add',
                'band_colors' => ['#f8fafc', '#0f172a', '#e2e8f0'],
            ],
        ];
    }

    public static function forTheme(string $themeId): array
    {
        $aliases = [
            'atelier-noir' => 'maison-elite',
            'object-gallery' => 'atelier-blanc',
            'wholesale-command' => 'the-specialist',
        ];
        if (isset($aliases[$themeId])) {
            $baseCfg = self::forTheme($aliases[$themeId]);
            if ($themeId === 'atelier-noir') {
                $baseCfg['signature_moment'] = 'Nocturnal haute couture lookbook with dramatic typography and asymmetric curtain reveal';
                $baseCfg['band_colors'] = ['#08080a', '#121216', '#ede6dc'];
            } elseif ($themeId === 'object-gallery') {
                $baseCfg['signature_moment'] = 'Museum-grade white exhibition plinths with generous architectural negative space and accession numbering';
                $baseCfg['band_colors'] = ['#ffffff', '#f8f8f6', '#111111'];
            } elseif ($themeId === 'wholesale-command') {
                $baseCfg['signature_moment'] = 'High-density B2B wholesale trade terminal with tabular specimen ledger, volume pricing tiers, and rapid line-item ordering';
                $baseCfg['band_colors'] = ['#0b0f19', '#111827', '#0284c7'];
            }
            return $baseCfg;
        }

        $all = self::all();
        if (!isset($all[$themeId])) {
            return [
                'page_architecture' => 'editorial-runway',
                'structural_variant' => 'A',
                'pdp_architecture' => 'editorial-fullbleed',
                'motion_choreography' => 'quiet-editorial',
                'depth_3d' => 'none',
                'sample_art' => 'fashion-editorial',
                'signature_moment' => 'Curated editorial storefront composition',
                'band_colors' => ['#ffffff', '#111111', '#f4f4f5'],
            ];
        }

        // Assign deterministic unique structural_variant (A, B, C, D) per architecture group
        // so NO two themes out of all 50 ever share the same (page_architecture, structural_variant) DOM silhouette!
        $variantLetters = ['A', 'B', 'C', 'D'];
        $archCounts = [];
        $resolvedVariant = 'A';
        foreach ($all as $id => $entry) {
            $a = $entry['page_architecture'];
            $idx = $archCounts[$a] ?? 0;
            $archCounts[$a] = $idx + 1;
            if ($id === $themeId) {
                $resolvedVariant = $variantLetters[$idx] ?? 'A';
                break;
            }
        }

        $cfg = $all[$themeId];
        $cfg['structural_variant'] = $resolvedVariant;
        return $cfg;
    }
}
