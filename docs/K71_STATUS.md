# K71 — Status Tracker

> Tracks implementation status of every spec section, feature, and phase.
> Status: ✅ DONE-VERIFIED | 🟡 PARTIAL | 🔴 BLOCKED | ⬜ NOT STARTED

---

## Phase 0 — Audit & Security Hardening

| Item | Status | Evidence |
|------|--------|----------|
| Dependency install & migrate | ✅ DONE-VERIFIED | Composer & npm installed, 17 migrations verified |
| Route audit (works/broken/dead) | ✅ DONE-VERIFIED | Catalogued in `docs/K71_AUDIT.md` |
| .env security (git-ignored, creds noted) | ✅ DONE-VERIFIED | `.env` git-ignored, placeholders in `.env.example`, hardcoded secrets purged from PhonePeService |
| Delete /setup-database route | ✅ DONE-VERIFIED | Removed from `routes/web.php`, verified by test |
| Remove public helper scripts | ✅ DONE-VERIFIED | 8 scripts deleted from `public/` directory |
| Restrict simulate-shipping/settlement | ✅ DONE-VERIFIED | Guarded by `app()->environment('local', 'testing')` in `routes/vendor.php` and `OrderController.php` |
| CSRF for cart/wishlist | ✅ DONE-VERIFIED | Removed exemptions from `bootstrap/app.php`, enforced via middleware |
| Authorisation tests | ✅ DONE-VERIFIED | `TenantIsolationTest.php` passes (vendor isolation on products & orders) |
| Security headers | ✅ DONE-VERIFIED | `SecurityHeaders.php` middleware attached to web group, tested |
| Rate limits | ✅ DONE-VERIFIED | `RateLimiter` configured for api (60/m), auth (6/m), checkout (10/m) |
| Upload validation | ✅ DONE-VERIFIED | Mime & size restrictions in `ProductController` and `ProfileController` |
| Shared-hosting .htaccess | ✅ DONE-VERIFIED | Directory protection, `Options -Indexes`, sensitive file rules added |
| Baseline feature tests | ✅ DONE-VERIFIED | 10 tests passing in `php artisan test` |

## Phase 1 — Foundation, Multi-Host, Design System, Mobile Nav

| Item | Status | Evidence |
|------|--------|----------|
| config/k71.php + rebrand | ✅ DONE-VERIFIED | `config/k71.php` created; APP_NAME="K71", APP_URL="https://k71.in" |
| ResolveVertical middleware | ✅ DONE-VERIFIED | `ResolveVertical.php` routes 5 subdomains & main marketplace; 6 tests pass in `MultiHostVerticalTest` |
| Session domain .k71.in | ✅ DONE-VERIFIED | `SESSION_DOMAIN=.k71.in` in `.env` and `config/session.php` |
| Canonical URLs & sitemaps per host | ✅ DONE-VERIFIED | Canonical tags and host-aware sitemaps in `SitemapController` |
| Design system (CSS tokens, components) | ✅ DONE-VERIFIED | Glass tokens, shorts glow animations, and responsive utilities in `app.css`, built via Vite |
| Mobile nav (top bar, bottom tabs) | ✅ DONE-VERIFIED | Top bar (`k71-nav.blade.php`) & Bottom tabs (`k71-bottom-nav.blade.php`) with red centre Shorts button |
| Glass hamburger menu | ✅ DONE-VERIFIED | `k71-hamburger.blade.php` with 5-verticals switcher, account links, and seller hub |
| Mini-cart sheet | ✅ DONE-VERIFIED | `k71-mini-cart.blade.php` matching reference card/pic, urgency timer, stepper, and `CartController::data()` API |
| Desktop mega-menu | ✅ DONE-VERIFIED | Integrated verticals mega-menu in `k71-nav.blade.php` |

## Phase 2 — Payment Choice + 3×3×3 Design Presets

| Item | Status | Evidence |
|------|--------|----------|
| Vendor payment settings (UPI/COD/Both) | ✅ DONE-VERIFIED | `payment_mode` radio & boolean fallback in `vendor/settings.blade.php` and `SettingsController.php`; server-side enforcement ensures at least one method remains enabled |
| Admin COD master switch | ✅ DONE-VERIFIED | Seeded in `settings` table (`cod_marketplace_enabled=1`, `cod_max_amount=500000`, `cod_fee=0`) and config fallback |
| Checkout payment enforcement | ✅ DONE-VERIFIED | Server-side validation against dynamic allowed methods in `CheckoutController.php`; blocks COD over ₹5,000 threshold or when vendor/admin disallowed |
| COD accounting & order flow | ✅ DONE-VERIFIED | COD orders save with `payment_method='cod'`, bypass PhonePe settlement clearance in `OrderController::acceptOrder`, and show doorstep cash payment instructions |
| store_presets table + migration | ✅ DONE-VERIFIED | Migration `2026_10_19_060000_add_k71_phase2_payment_and_presets.php` seeded with 9 presets |
| 3 store layouts | ✅ DONE-VERIFIED | `classic`, `editorial`, `minimal` implemented in `storefront/show.blade.php` |
| 3 colour presets | ✅ DONE-VERIFIED | `sage` (Earthy Sage), `midnight` (Midnight Indigo), `sunset` (Warm Terracotta) implemented with CSS tokens |
| 3 product-page layouts | ✅ DONE-VERIFIED | `gallery` (Thumbnail strip), `immersive` (Full-bleed media & floating quick-add dock), `showcase` (Specification & fabric care table) in `catalog/show.blade.php` |
| Vendor preset selection UI | ✅ DONE-VERIFIED | Visual 3×3×3 cards in `vendor/profile.blade.php` and validated in `ProfileController.php` |
| Automated feature tests | ✅ DONE-VERIFIED | 5/5 tests passing in `tests/Feature/PaymentChoiceAndPresetsTest.php` (41 assertions) |

## Phase 3 — Shorts Port

| Item | Status | Evidence |
|------|--------|----------|
| SHORTS_PARITY.md created | ✅ DONE-VERIFIED | Complete mapping created in `docs/SHORTS_PARITY.md` |
| Data mapping (migrations) | ✅ DONE-VERIFIED | `2026_10_19_070000_add_k71_phase3_shorts_tables.php` creates shorts, short_products, short_events, short_boosts, short_coins, short_comments, short_follows, short_shares, spin_prizes |
| AJAX compatibility layer | ✅ DONE-VERIFIED | `ShortsAjaxController.php` dispatches `zsu_*` actions for 1:1 WP AJAX parity |
| Feed page | ✅ DONE-VERIFIED | `resources/views/shorts/index.blade.php` at `shorts.k71.in` or `/shorts` |
| Lightbox reel player | ✅ DONE-VERIFIED | Fullscreen snap reel, sidebar, mute/fullscreen/like/share, progress bar, shoppable bottom drawer |
| Home rail + Spin wheel | ✅ DONE-VERIFIED | `components/k71-shorts-rail.blade.php`, `ShortsSpinController.php` with verified coupons (Decision D8) |
| Vendor Studio | ✅ DONE-VERIFIED | `ShortsStudioController.php` & `vendor/shorts/studio.blade.php` (frame grabber, product tagger, boost selector) |
| Admin pages | ✅ DONE-VERIFIED | `ShortsAdminController.php` & `admin/shorts/index.blade.php` (moderation queue, approval/rejection, pin/unpin) |
| Store tab | ✅ DONE-VERIFIED | Reels section integrated into `storefront/show.blade.php` |
| Boost expiry system | ✅ DONE-VERIFIED | `shorts:expire-boosts` command in `ExpireShortsBoosts.php` & scheduled hourly |
| Coins ledger | ✅ DONE-VERIFIED | `ShortCoin.php` ledger table and transactions recording |
| Trending score | ✅ DONE-VERIFIED | `shorts:recalculate-trending` command with decay weighting & scheduled daily |
| Upload pipeline | ✅ DONE-VERIFIED | Chunked/standard MP4/WebM validation, storage to `public/storage/shorts`, auto poster generator |
| SEO (VideoObject, sitemap) | ✅ DONE-VERIFIED | `VideoObject` Schema JSON-LD on shorts deep-link + Google Video XML sitemap in `SitemapController::shorts` |
| Feature tests | ✅ DONE-VERIFIED | 9/9 tests pass in `tests/Feature/ShortsFeatureTest.php` (40 assertions) |

## Phase 4 — Listing Types + Category Fields

| Item | Status | Evidence |
|------|--------|----------|
| products.vertical column | ✅ DONE-VERIFIED | Added via additive migration `2026_10_19_080000_add_k71_phase4_listing_types_and_fields.php`, indexed, fillable & scoped in `Product.php` |
| products.listing_type column | ✅ DONE-VERIFIED | Added via additive migration, defaults to 'purchase', supports 'enquiry', 'booking', 'auction', 'contact_seller' |
| category_fields system | ✅ DONE-VERIFIED | `category_fields` table & `CategoryField.php` model seeded with standard attributes across food, services, used, enquiry |
| Listing type enforcement | ✅ DONE-VERIFIED | Server-side validation in `CartController::add()` (rejects non-purchase with 422) & `CheckoutController::index()`/`process()` (redirects with error) |
| Dynamic vendor listing form | ✅ DONE-VERIFIED | Integrated into `vendor/products/create.blade.php` & `edit.blade.php` with vertical cards, listing type selectors, reactive custom_fields, and PDP badges/CTAs |
| Feature tests | ✅ DONE-VERIFIED | 8/8 tests passing in `tests/Feature/ListingTypesAndFieldsTest.php` (48 assertions, 38/38 suite-wide) |

## Phase 5 — Food & Restaurants

| Item | Status | Evidence |
|------|--------|----------|
| Restaurant profiles | ✅ DONE-VERIFIED | `2026_10_19_090000_add_k71_phase5_food_and_restaurants.php` adds `is_restaurant`, `is_pure_veg`, `cuisine_types`, `opening_time`, `closing_time`, `avg_prep_time_mins`, `min_food_order_amount`, `food_delivery_fee`, `food_delivery_mode` to `vendor_settings`; `Vendor::isRestaurant()`, `isPureVeg()` |
| Menu system | ✅ DONE-VERIFIED | `FoodController.php` (`index`, `restaurant`), `resources/views/food/index.blade.php`, `resources/views/food/restaurant.blade.php` with categorized menus, dietary indicators (Veg/Non-Veg badges), spice levels, and serving sizes |
| Food ordering flow | ✅ DONE-VERIFIED | `CheckoutController.php` supports food local delivery fee (default ₹40 or vendor setting), takeaway (₹0 shipping), `kitchen_notes`, and `estimated_delivery_time` |
| Order lifecycle | ✅ DONE-VERIFIED | Direct kitchen lifecycle states (`pending`, `processing` cooking, `ready_for_pickup` takeaway, `shipped` out for delivery, `delivered`) with timestamp tracking (`packed_at`, `shipped_at`, `delivered_at`) |
| Vendor order board | ✅ DONE-VERIFIED | `resources/views/vendor/orders/show.blade.php` displays dedicated Food Order & Kitchen Dispatch Console bypassing Shiprocket per Decision D13; 1-click status transitions and customer kitchen notes |
| Feature tests | ✅ DONE-VERIFIED | 6/6 tests passing in `tests/Feature/FoodAndRestaurantTest.php` (33 assertions, 44/44 suite-wide) |

## Phase 6 — Used Items & Make-an-Offer

| Item | Status | Evidence |
|------|--------|----------|
| Used item listings | ✅ DONE-VERIFIED | `UsedController.php`, `resources/views/used/index.blade.php` with condition pills (Like New, Gently Used, etc.), location/city selector, price range, negotiable filter, and sold badges |
| Offers/negotiation | ✅ DONE-VERIFIED | `2026_10_19_100000_add_k71_phase6_used_items_and_offers.php`, `Offer.php` model, `OfferController.php`, buyer "Make an Offer" modal on PDP, and vendor offers inbox with Accept/Counter/Reject |
| Contact seller | ✅ DONE-VERIFIED | Direct WhatsApp & call seller CTAs bypassing cart checkout per Decision D9; mark as sold functionality (`is_sold=1`) |
| Feature tests | ✅ DONE-VERIFIED | 9/9 tests passing in `tests/Feature/UsedItemsAndOffersTest.php` (29 assertions, 53/53 suite-wide) |

## Phase 7 — Services + Bookings

| Item | Status | Evidence |
|------|--------|----------|
| Service provider pages | ✅ DONE-VERIFIED | `ServicesController::provider()`, `resources/views/services/provider.blade.php` with verified badges, experience, service radius, working hours, and services catalog |
| Booking system | ✅ DONE-VERIFIED | `2026_10_19_110000_add_k71_phase7_services_and_bookings.php`, `Booking.php` model, `BookingController.php` with appointment creation, booking confirmation/tracker view (`resources/views/services/show.blade.php`), and PDP booking modal |
| Slot locking & vendor console | ✅ DONE-VERIFIED | Interactive slot selection ('09:00 AM - 11:00 AM', etc.), vendor dashboard bookings inbox (`resources/views/vendor/bookings/index.blade.php`) with 1-click status transitions (`confirmed`, `completed`, `cancelled`), and direct WhatsApp communication |
| Feature tests | ✅ DONE-VERIFIED | 11/11 tests passing in `tests/Feature/ServicesAndBookingsTest.php` (54 assertions, 64/64 suite-wide) |

## Phase 8 — Enquiry Marketplace

| Item | Status | Evidence |
|------|--------|----------|
| Buyer requirement form | ✅ DONE-VERIFIED | `EnquiryController::create()` / `store()`, `resources/views/enquiry/create.blade.php` with title, category, quantity, target budget, delivery city, timeline urgency, and contact capture |
| Vendor lead inbox | ✅ DONE-VERIFIED | `resources/views/vendor/enquiries/index.blade.php` with leads filter tabs (`all`, `direct`, `my_quotes`, `closed`), inline quote submission drawer, and direct WhatsApp buyer CTA |
| Quote system | ✅ DONE-VERIFIED | `2026_10_19_120000_add_k71_phase8_enquiries_and_quotes.php`, `Quote.php` model, `EnquiryController::vendorQuote()`, `acceptQuote()`, and quotation comparison section on `resources/views/enquiry/show.blade.php` |
| Feature tests | ✅ DONE-VERIFIED | 9/9 tests passing in `tests/Feature/EnquiryMarketplaceTest.php` (49 assertions, 73/73 suite-wide) |

## Phase 9 — Auctions + Messaging

| Item | Status | Evidence |
|------|--------|----------|
| Bidding system | ✅ DONE-VERIFIED | `2026_10_19_130000_add_k71_phase9_auctions_and_messaging.php`, `Bid.php` model, `AuctionController.php`, live bidding with anti-sniping 5-minute auto-extension (Decision D14), min next bid enforcement, and outbid state management |
| Conversations | ✅ DONE-VERIFIED | `Conversation.php` and `Message.php` models, `MessageController.php`, customer inbox & chat view (`resources/views/messages/index.blade.php`, `show.blade.php`), vendor inquiries inbox (`resources/views/vendor/messages/index.blade.php`, `show.blade.php`), and PDP messaging modals |
| Notification centre | ✅ DONE-VERIFIED | `NotificationController.php`, `resources/views/notifications/index.blade.php`, single/all mark-as-read actions, and header user dropdown integration |
| Feature tests | ✅ DONE-VERIFIED | 11/11 tests passing in `tests/Feature/AuctionsAndMessagingTest.php` (43 assertions, 84/84 suite-wide) |

## Phase 10 — Trust, Promotions, Analytics, Admin

| Item | Status | Evidence |
|------|--------|----------|
| Reports & moderation | ✅ DONE-VERIFIED | `2026_10_19_140000_add_k71_phase10_trust_promotions_and_admin.php`, `Report.php` model, `ReportController.php` (product/vendor/review flagging), `AdminController::reports()` & `resolveReport()` moderation queue (`resources/views/admin/reports/index.blade.php`) |
| Reviews with vendor reply | ✅ DONE-VERIFIED | `Review.php` model with `vendor_reply` & `vendor_replied_at`, `ReviewController::store()` & `vendorReply()`, verified purchase detection, and official boutique reply display on PDP (`catalog/show.blade.php`) |
| Promotions system | ✅ DONE-VERIFIED | `Promotion.php` model, `AdminController::promotions()`, `storePromotion()`, and `togglePromotion()` flash deals hub (`resources/views/admin/promotions/index.blade.php`) |
| Admin panels | ✅ DONE-VERIFIED | Command Center dashboard with GMV, PhonePe settlements, treasury ledger, vendor onboarding queue, and fulfillment telemetry |
| Homepage section builder | ✅ DONE-VERIFIED | `Admin\HomepageController.php` section switches, SEO meta studio, announcement marquee, hero customization, and category banner image manager |
| Audit log | ✅ DONE-VERIFIED | `AuditLog.php` model, `Auditable` trait on core models, and Admin audit browser (`resources/views/admin/audit-logs/index.blade.php`) with mutation diffs |
| Feature tests | ✅ DONE-VERIFIED | 9/9 tests passing in `tests/Feature/TrustPromotionsAndAdminTest.php` (41 assertions, 93/93 suite-wide) |

## Phase 11 — Discovery, SEO, Performance, Privacy

| Item | Status | Evidence |
|------|--------|----------|
| Homepage rails | ✅ DONE-VERIFIED | Flash sale promotions banner (`Promotion::where('is_active', true)`), 5 dedicated verticals switcher grid (Food, Services, Used, Enquiry, Shorts), and Shorts video commerce rail (`<x-k71-shorts-rail />`) in `resources/views/home.blade.php` |
| Search & Filter Discovery | ✅ DONE-VERIFIED | Cross-vertical query handling (`q`/`search`, `vertical`, `city`, `listing_type`, `price_min`/`price_max`, sort aliases) and instant autocomplete API (`/catalog/suggest` & `/api/search/suggest`) in `CatalogController.php` |
| JSON-LD structured data | ✅ DONE-VERIFIED | Full Schema.org graph for Product, Offer, MerchantReturnPolicy, DeliveryTime, BreadcrumbList, CollectionPage, and VideoObject across PDP, catalog, and shorts |
| Data export/delete | ✅ DONE-VERIFIED | DPDP Act (2023) compliant customer personal data export (`GET /account/export` JSON package) & permanent right to erasure (`POST /account/delete` with identifier anonymization and soft deletion) in `AccountController.php` & `resources/views/account/index.blade.php` |
| Sitemaps & robots.txt | ✅ DONE-VERIFIED | `SitemapController.php` with master index, products (with Google image extension), categories, stores, pages, shorts; `public/robots.txt` updated with `k71.in/sitemap.xml` and vertical crawl directives |
| Backup/restore runbook | ✅ DONE-VERIFIED | Complete production SRE runbook documented in `docs/BACKUP_RUNBOOK.md` covering zero-downtime MySQL dumps, media rsync snapshots, and point-in-time recovery |
| Automated feature tests | ✅ DONE-VERIFIED | 9/9 tests passing in `tests/Feature/DiscoverySeoPrivacyTest.php` (57 assertions); **102/102 tests passing suite-wide with 468 assertions** |

