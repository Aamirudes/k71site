# K71 Storefront Theme Marketplace — 50-Theme Architectural & Visual QA Tracker

- **Total Storefront Themes**: 61 (11 preserved legacy classic themes + 50 bespoke themes)
- **Unique Structural DOM Compositions**: **50 / 50** unique `(page_architecture : structural_variant)` pairs across 15 dedicated Blade architectures & 6 dedicated PDP architectures
- **Real Browser Playwright Verification**: **50 / 50 PASSED** (0 console errors, 0 desktop `1440×900` horizontal overflow, 0 mobile `390×844` horizontal overflow, 50/50 GSAP 3 active, 50/50 HTTP 200 Homepage & PDP)
- **Visual Proof Sheets**:
  - Color Contact Sheet: `docs/theme-contact-sheet-50.png`
  - Grayscale Silhouette Proof: `docs/theme-contact-sheet-50-grayscale.png`

| # | Theme ID | Theme Name | Category | Page Architecture : Variant | PDP Architecture | Motion Choreography | Desktop 1440px | Mobile 390px | PDP 200 | GSAP | Console Errors | Status |
|---:|---|---|---|---|---|---|---|---|---|---|---|---|
| 01 | `maison-elite` | Maison Élite | luxury_fashion | `editorial-runway:A` | `editorial-fullbleed` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 02 | `atelier-blanc` | Atelier Blanc | luxury_fashion | `left-rail-gallery:A` | `gallery-plinth-museum` | `japanese-ma` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 03 | `obsidian` | Obsidian | luxury_fashion | `cinematic-dark-stage:A` | `split-sticky-atelier` | `cinematic-curtain` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 04 | `monogram` | Monogram | luxury_fashion | `horizontal-gallery-wall:A` | `gallery-plinth-museum` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 05 | `the-curator` | The Curator | luxury_fashion | `swiss-manifesto:A` | `technical-spec-lab` | `editorial-asymmetry` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 06 | `runway-01` | Runway 01 | luxury_fashion | `typography-as-architecture:A` | `editorial-fullbleed` | `brutalist-kinetic` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 07 | `studio-label` | Studio Label | luxury_fashion | `product-first-index:A` | `split-sticky-atelier` | `japanese-ma` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 08 | `soft-form` | Soft Form | luxury_fashion | `sculptural-arch-sanctuary:A` | `gallery-plinth-museum` | `spatial-glass-float` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 09 | `street-culture` | Street Culture | luxury_fashion | `brutalist-poster:A` | `brutalist-poster-sheet` | `brutalist-kinetic` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 10 | `modest-luxe` | Modest Luxe | luxury_fashion | `split-screen-showroom:A` | `split-sticky-atelier` | `japanese-ma` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 11 | `denim-district` | Denim District | luxury_fashion | `b2b-specimen-ledger:A` | `brutalist-poster-sheet` | `swiss-grid-snap` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 12 | `sartorial` | Sartorial | luxury_fashion | `swiss-manifesto:B` | `editorial-fullbleed` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 13 | `sneaker-vault` | Sneaker Vault | footwear_performance | `brutalist-poster:B` | `brutalist-poster-sheet` | `sneaker-vault-3d` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 14 | `motion-lab` | Motion Lab | footwear_performance | `typography-as-architecture:B` | `technical-spec-lab` | `horizontal-cinema` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 15 | `sole-archive` | Sole Archive | footwear_performance | `left-rail-gallery:B` | `gallery-plinth-museum` | `swiss-grid-snap` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 16 | `velocity` | Velocity | footwear_performance | `cinematic-dark-stage:B` | `technical-spec-lab` | `sneaker-vault-3d` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 17 | `minimal-sole` | Minimal Sole | footwear_performance | `product-first-index:B` | `split-sticky-atelier` | `japanese-ma` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 18 | `chronograph` | Chronograph | watches_jewelry | `cinematic-dark-stage:C` | `technical-spec-lab` | `tech-precision` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 19 | `jewelle` | Jewelle | watches_jewelry | `sculptural-arch-sanctuary:B` | `editorial-fullbleed` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 20 | `goldline` | Goldline | watches_jewelry | `horizontal-gallery-wall:B` | `gallery-plinth-museum` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 21 | `artifact` | Artifact | watches_jewelry | `left-rail-gallery:C` | `gallery-plinth-museum` | `architectural-3d` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 22 | `luxe-objects` | Luxe Objects | watches_jewelry | `editorial-runway:B` | `split-sticky-atelier` | `editorial-asymmetry` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 23 | `future-form` | Future Form | tech_electronics | `spatial-glass-studio:A` | `split-sticky-atelier` | `spatial-glass-float` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 24 | `circuit` | Circuit | tech_electronics | `bento-modular-flagship:A` | `technical-spec-lab` | `tech-precision` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 25 | `mono-tech` | Mono Tech | tech_electronics | `swiss-manifesto:C` | `technical-spec-lab` | `swiss-grid-snap` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 26 | `pixel-foundry` | Pixel Foundry | tech_electronics | `b2b-specimen-ledger:B` | `dense-trade-catalog` | `spatial-glass-float` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 27 | `audio-atelier` | Audio Atelier | tech_electronics | `cinematic-dark-stage:D` | `editorial-fullbleed` | `horizontal-cinema` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 28 | `casa-moderna` | Casa Moderna | furniture_home | `split-screen-showroom:B` | `split-sticky-atelier` | `architectural-3d` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 29 | `nordic-living` | Nordic Living | furniture_home | `product-first-index:C` | `gallery-plinth-museum` | `japanese-ma` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 30 | `forma-gallery` | Forma Gallery | furniture_home | `horizontal-gallery-wall:C` | `editorial-fullbleed` | `editorial-asymmetry` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 31 | `earth-and-grain` | Earth & Grain | furniture_home | `sculptural-arch-sanctuary:C` | `split-sticky-atelier` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 32 | `habitat-studio` | Habitat Studio | furniture_home | `bento-modular-flagship:B` | `technical-spec-lab` | `architectural-3d` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 33 | `botanical-lab` | Botanical Lab | beauty_skincare | `split-screen-showroom:C` | `split-sticky-atelier` | `japanese-ma` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 34 | `skin-ritual` | Skin Ritual | beauty_skincare | `sculptural-arch-sanctuary:D` | `gallery-plinth-museum` | `spatial-glass-float` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 35 | `pure-science` | Pure Science | beauty_skincare | `swiss-manifesto:D` | `technical-spec-lab` | `tech-precision` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 36 | `velvet-beauty` | Velvet Beauty | beauty_skincare | `editorial-runway:C` | `editorial-fullbleed` | `editorial-asymmetry` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 37 | `daily-ritual` | Daily Ritual | beauty_skincare | `spatial-glass-studio:B` | `dense-trade-catalog` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 38 | `maison-gourmet` | Maison Gourmet | food_artisan | `color-block-editorial:A` | `editorial-fullbleed` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 39 | `fresh-market` | Fresh Market | food_artisan | `pro-commerce-catalog:A` | `dense-trade-catalog` | `fast-commerce-light` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 40 | `artisan-pantry` | Artisan Pantry | food_artisan | `horizontal-gallery-wall:D` | `dense-trade-catalog` | `fast-commerce-light` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 41 | `coffee-culture` | Coffee Culture | food_artisan | `typography-as-architecture:C` | `split-sticky-atelier` | `editorial-asymmetry` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 42 | `sweet-atelier` | Sweet Atelier | food_artisan | `color-block-editorial:B` | `gallery-plinth-museum` | `spatial-glass-float` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 43 | `neo-commerce` | Neo Commerce | contemporary_concepts | `bento-modular-flagship:C` | `split-sticky-atelier` | `horizontal-cinema` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 44 | `the-drop` | The Drop | contemporary_concepts | `brutalist-poster:C` | `brutalist-poster-sheet` | `brutalist-kinetic` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 45 | `market-studio` | Market Studio | contemporary_concepts | `pro-commerce-catalog:B` | `dense-trade-catalog` | `fast-commerce-light` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 46 | `pop-form` | Pop Form | contemporary_concepts | `color-block-editorial:C` | `brutalist-poster-sheet` | `brutalist-kinetic` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 47 | `color-theory` | Color Theory | contemporary_concepts | `color-block-editorial:D` | `editorial-fullbleed` | `horizontal-cinema` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 48 | `heritage-house` | Heritage House | contemporary_concepts | `left-rail-gallery:D` | `editorial-fullbleed` | `quiet-editorial` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 49 | `field-and-found` | Field & Found | contemporary_concepts | `split-screen-showroom:D` | `technical-spec-lab` | `architectural-3d` | 200 / 0px | 0px | 200 | Active | 0 | PASS |
| 50 | `the-specialist` | The Specialist | contemporary_concepts | `b2b-specimen-ledger:C` | `dense-trade-catalog` | `fast-commerce-light` | 200 / 0px | 0px | 200 | Active | 0 | PASS |

## Visual QA Findings Fixed During Audit
1. **Dark-Theme Heading Inheritance (`chronograph` & dark obsidian stages)**: Added `color: inherit` to `.k71-sf-theme h1..h4, .k71-heading` in `public/css/k71-theme-engine.css` so global app heading styles never darken headings on obsidian surfaces.
2. **Chromatic Accent Contrast (`color-theory` Band 2 Index)**: Computed luminance of `$bandAccent` in `color-block-editorial.blade.php` so dark accent bands (`#1a1715`) automatically use crisp `#ffffff` ink and inverted white/charcoal department pills.
3. **50/50 Silhouette De-duplication**: Upgraded all 15 architecture Blade files with structural variants (`A`/`B`/`C`/`D`) enforced by `ThemeArchitectureMap::forTheme()` and locked by `ThemeMarketplaceTest`.
