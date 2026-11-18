# K71 Storefront Theme System — Industry Design Intelligence Matrix & 50-Theme Reconstruction Tracker

## 1. Forensic Diagnosis of Previous Implementations

Prior iterations produced visually repetitive storefronts for five structural reasons:
1. **Shared Universal Page Flow**: All themes initially rendered inside one centered `max-w-7xl` vertical grid (`Header -> Hero -> Categories -> Grid -> Footer`), so removing color made them structurally indistinguishable.
2. **Uniform Product Detail Pages**: Every theme opened the same 12-column `gallery-left / purchase-box-right` PDP regardless of vertical (couture vs. B2B specs vs. gallery sculpture).
3. **Flat Placeholder Imagery**: Empty-store previews used a single grey `K71 PRODUCT PREVIEW` box or flat vector clipart shapes, undermining perceived craft even when grid layouts differed.
4. **Global CSS Cascade Leakage**: Global `app.css` heading colors bled onto dark themes until `.k71-sf-theme` explicitly enforced `color: inherit;` scoping.
5. **Missing Interactive Commerce Micro-Architecture**: Adding to cart showed only a transient toast rather than a theme-styled slide-out mini-cart drawer, and cards lacked Quick View & Shop-the-Look hotspot interactions standard in flagship Shopify/WooCommerce stores.

---

## 2. Industry Research & Design Intelligence Matrix

Research conducted across official Shopify Theme Store leaders, WooCommerce flagship themes, and Awwwards e-commerce patterns. Principles are extracted and transformed into original K71 custom-PHP compositions (zero proprietary code or assets copied).

| Reference Ecosystem & Theme | Verified Reference URL | Industry / Direction | Key Structural & UX Decisions Extracted | Original K71 Transformation |
|---|---|---|---|---|
| **Shopify · Prestige** (Maestrooo) | https://themes.shopify.com/themes/prestige | High-fashion luxury editorial | Print-magazine asymmetric spreads, integrated transparent header over hero photography, **Shop-the-Look interactive hotspots**, sticky PDP buy box with tall editorial stack. | Rebuilt into K71 `editorial-runway` + `editorial-fullbleed` PDP + clickable `[data-k71-quickview]` lookbook hotspots (`maison-elite`, `jewelle`, `velvet-beauty`). |
| **Shopify · Stiletto** (Fluorescent) | https://themes.shopify.com/themes/stiletto | Curated boutique storytelling | Overlapping text-on-media editorial drops, generous negative space, slide-out drawer interactions, asymmetric gallery spans. | Rebuilt into K71 `typography-as-architecture` + `split-sticky-atelier` PDP + theme-matched slide-out AJAX Mini-Cart Drawer (`runway-01`, `modest-luxe`). |
| **Shopify · Pipeline** (Group Sixteen) | https://themes.shopify.com/themes/pipeline | Minimalist modular grid | Linear breathing rhythm, subtle scroll parallax, dual-column sticky PDP with clean swatch/pill variant selectors. | Rebuilt into K71 `split-screen-showroom` + `product-first-index` with GSAP ScrollTrigger subtle parallax (`casa-moderna`, `nordic-living`, `studio-label`). |
| **Shopify · Motion** (Archetype) | https://themes.shopify.com/themes/motion | Kinetic visual storytelling | Choreographed scroll-triggered reveals, dynamic banner grids, mobile-first drawer ergonomics, accordion PDP specs. | Rebuilt into K71 12-profile GSAP 3 + ScrollTrigger engine (`resources/js/k71-theme-motion.js`) with `gsap.matchMedia()` mobile adaptation. |
| **Shopify · Focal** (Maestrooo) | https://themes.shopify.com/themes/focal | Bold grid & color-block editorial | Magazine-cover color fields, asymmetrical grids, conversion-centric sticky bars, mixed aspect-ratio galleries. | Rebuilt into K71 `color-block-editorial` + `bento-modular-flagship` with bespoke chromatic `band_colors` (`pop-form`, `color-theory`, `maison-gourmet`). |
| **WooCommerce · WoodMart** | https://themeforest.net/category/wordpress/ecommerce/woocommerce | High-density versatile commerce | Native AJAX Quick View modal, off-canvas cart drawer, deep department filtering, sticky add-to-cart, spec clarity. | Rebuilt into K71 `pro-commerce-catalog` + `#k71-quickview-modal` + `#k71-cart-drawer` (`fresh-market`, `market-studio`). |
| **WooCommerce · Flatsome** | https://themeforest.net/category/wordpress/ecommerce/woocommerce | High-speed retail & agency UX | Lightweight quick-view lightbox preserving grid scroll position, fluid responsive grids, crisp typographic hierarchy. | Rebuilt into fast-commerce light choreography (`fast-commerce-light`) + instant lightbox Quick View on cards. |
| **WooCommerce · Savoy** | https://themeforest.net/category/wordpress/ecommerce/woocommerce | Minimalist image-first boutique | Product-first immediate grid above the fold, ultra-clean chrome, zero visual clutter, borderless image framing. | Rebuilt into K71 `product-first-index` (Zero-Hero 5-column product wall immediately above the fold: `studio-label`, `minimal-sole`, `nordic-living`). |
| **WooCommerce · Electro** | https://themeforest.net/category/wordpress/ecommerce/woocommerce | Electronics & B2B high-density | Data-dense SKU/specification rows, 3-column engineering PDP spec tables, inline quantity procurement controls. | Rebuilt into K71 `b2b-specimen-ledger` + `technical-spec-lab` 3-column PDP (`the-specialist`, `circuit`, `pixel-foundry`, `mono-tech`). |
| **Awwwards E-Commerce** | https://www.awwwards.com/websites/e-commerce/ | Experimental & luxury flagships | Broken/asymmetric grids, kinetic GSAP `ScrollTrigger` scrubbed horizontal reels, oversized architectural type, subtle WebGL light shaders, left exhibition rails. | Rebuilt into K71 `left-rail-gallery`, `cinematic-dark-stage`, `horizontal-gallery-wall`, `brutalist-poster`, `spatial-glass-studio`, `sculptural-arch-sanctuary`, plus lightweight procedural WebGL shader stage & precision cursor. |

---

## 3. Complete 50-Theme Architectural & Motion Matrix

All 11 existing K71 classic themes (`velora`, `noire`, `aurel`, `chroma`, `orbit`, `zenith`, `solaris`, `nexus`, `elysium`, `vogue`, `kinetic`) are strictly preserved alongside all 50 individually art-directed themes below:

- **15 Distinct Page Architectures**: `editorial-runway`, `left-rail-gallery`, `cinematic-dark-stage`, `swiss-manifesto`, `brutalist-poster`, `spatial-glass-studio`, `split-screen-showroom`, `bento-modular-flagship`, `color-block-editorial`, `pro-commerce-catalog`, `typography-as-architecture`, `product-first-index`, `b2b-specimen-ledger`, `horizontal-gallery-wall`, `sculptural-arch-sanctuary`.
- **6 Distinct PDP Architectures**: `editorial-fullbleed`, `split-sticky-atelier`, `technical-spec-lab`, `gallery-plinth-museum`, `brutalist-poster-sheet`, `dense-trade-catalog`.
- **12 GSAP + ScrollTrigger Motion Choreographies**: `quiet-editorial`, `cinematic-curtain`, `swiss-grid-snap`, `japanese-ma`, `architectural-3d`, `spatial-glass-float`, `brutalist-kinetic`, `horizontal-cinema`, `editorial-asymmetry`, `sneaker-vault-3d`, `tech-precision`, `fast-commerce-light`.
- **12 Bespoke Studio Editorial Photography Plates**: `public/images/theme-editorial/*.png` (strictly sample preview art when a vendor store has no uploaded photos; real vendor photography always takes precedence).
- **Procedural WebGL Shader Stage**: Active on dark/spatial/cinematic flagships (`obsidian`, `chronograph`, `future-form`, `audio-atelier`, `velocity`, `luxe-objects`, `circuit`, `the-drop`) on desktop fine pointers with `prefers-reduced-motion` and mobile auto-disable.
