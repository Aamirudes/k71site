# K71 — Shorts Parity Documentation
> Mapping WordPress Zilla Shorts Ultra v11 (ZSU) to Laravel K71 Shorts System (`shorts.k71.in`)

---

## 1. Architectural Overview

The WordPress plugin `legacy/shorts-plugin/` ("Zilla Shorts Ultra v11") is ported into a native Laravel implementation within K71 while keeping:
1. **Frontend Experience & Assets:** Exact high-performance JS/CSS assets (`zsu-frontend.js`, `zsu-frontend.css`, `zsu-home-rail.js`, `zsu-home-rail.css`, `zsu-studio.js`, `zsu-studio.css`) isolated in `public/shorts-assets/` to prevent Tailwind preflight conflicts (Decision D12).
2. **AJAX Action Compatibility:** Dedicated dispatcher `/shorts/ajax` and `/admin-ajax.php` fallback supporting all `zsu_*` public, private, and admin actions, backed by native Laravel Eloquent controllers and services.
3. **Dedicated Subdomain & Host Isolation:** Canonical home at `shorts.k71.in` with 301-redirects from `k71.in/shorts` (Decision D6).
4. **Shared Authentication & Session:** Native auth via `SESSION_DOMAIN=.k71.in` (Decision D15), allowing seamless switching between marketplace storefront, vendor studio, and Shorts feed.

---

## 2. Database Schema & Data Mapping

| WordPress ZSU Table / Meta | Laravel Migration / Model | Columns & Type Mappings | Parity Notes |
|-----------------------------|---------------------------|-------------------------|--------------|
| `wp_posts` (`post_type='zsu_short'`) + `wp_postmeta` | `shorts` (`App\Models\Short`) | `id` (bigint), `vendor_id` (foreignId), `title` (string), `slug` (string unique), `description` (text null), `video_url` (string 500), `video_path` (string 500 null), `thumb_url` (string 500 null), `thumb_b_url` (string 500 null), `ratio` (string 10 default '9:16'), `duration` (int default 0), `is_featured` (bool), `is_boosted` (bool), `is_manual_boost` (bool), `boost_type` (string null), `views` (bigint), `likes` (bigint), `shares` (bigint), `clicks` (bigint), `comments_count` (int), `trending_score` (decimal 10,2), `status` (string 'published','pending','rejected'), `approved_at` (timestamp null), `rejection_reason` (text null), `timestamps` | Replaces EAV postmeta with indexed relational columns |
| `_zsu_product_ids` meta | `short_products` (Pivot) | `short_id`, `product_id`, `sort_order` (int) | BelongsToMany relationship between `Short` and `Product` |
| `wp_zsu_events` | `short_events` (`App\Models\ShortEvent`) | `id`, `short_id`, `event_type` ('view','like','share','click','comment','watch_pct'), `user_id`, `session_id`, `device`, `browser`, `country`, `referrer`, `ip_hash`, `watch_pct`, `created_at` | High-frequency analytics and trending score feed |
| `wp_zsu_boosts` | `short_boosts` (`App\Models\ShortBoost`) | `id`, `short_id`, `vendor_id`, `boost_type` ('standard','prime','elite'), `coins_spent`, `starts_at`, `ends_at`, `status` ('pending','active','expired','cancelled'), `impressions`, `clicks`, `timestamps` | Boost scheduling with hourly/daily cron auto-expiry |
| `wp_zsu_coins` | `short_coins` (`App\Models\ShortCoin`) | `id`, `user_id`, `delta`, `balance`, `reason`, `ref_id`, `meta` (json/text), `timestamps` | Ledger for seller coins; coin top-ups integrate with PhonePe (Decision D7) |
| `wp_zsu_comments` | `short_comments` (`App\Models\ShortComment`) | `id`, `short_id`, `user_id`, `parent_id`, `content`, `status`, `likes`, `timestamps` | Interactive comments on reels with reply hierarchy |
| `wp_zsu_follows` | `short_follows` (`App\Models\ShortFollow`) | `id`, `follower_id`, `vendor_id`, `created_at` | Vendor store following directly from reel overlay |
| `wp_zsu_shares` | `short_shares` (`App\Models\ShortShare`) | `id`, `short_id`, `platform`, `user_id`, `session_id`, `created_at` | Social share tracking |
| `zsu_spin_wheel_prizes` option | `spin_prizes` (`App\Models\SpinPrize`) | `id`, `label`, `color`, `type` ('coupon','coins','free_shipping'), `value`, `coupon_id` (foreignId to `coupons`), `probability`, `is_active`, `timestamps` | Maps directly to real coupons in `coupons` table (Decision D8) |

---

## 3. AJAX Actions Compatibility Table

| Action Name | WP Handler in `class-zsu-ajax.php` | Laravel Route / Method in `ShortsAjaxController` | Access Level |
|-------------|------------------------------------|-------------------------------------------------|--------------|
| `zsu_get_shorts` | `handle_zsu_get_shorts()` | `ShortsAjaxController::getShorts()` | Public |
| `zsu_get_featured` | `handle_zsu_get_featured()` | `ShortsAjaxController::getFeatured()` | Public |
| `zsu_get_cat_rail` | `handle_zsu_get_cat_rail()` | `ShortsAjaxController::getCatRail()` | Public |
| `zsu_record_view` | `handle_zsu_record_view()` | `ShortsAjaxController::recordView()` | Public |
| `zsu_record_like` | `handle_zsu_record_like()` | `ShortsAjaxController::recordLike()` | Public |
| `zsu_record_share` | `handle_zsu_record_share()` | `ShortsAjaxController::recordShare()` | Public |
| `zsu_record_watch_pct` | `handle_zsu_record_watch_pct()` | `ShortsAjaxController::recordWatchPct()` | Public |
| `zsu_get_products` | `handle_zsu_get_products()` | `ShortsAjaxController::getProducts()` | Public |
| `zsu_get_short_data` | `handle_zsu_get_short_data()` | `ShortsAjaxController::getShortData()` | Public |
| `zsu_follow_vendor` | `handle_zsu_follow_vendor()` | `ShortsAjaxController::followVendor()` | Public (Auth-aware) |
| `zsu_unfollow_vendor` | `handle_zsu_unfollow_vendor()` | `ShortsAjaxController::unfollowVendor()` | Public (Auth-aware) |
| `zsu_check_follow` | `handle_zsu_check_follow()` | `ShortsAjaxController::checkFollow()` | Public |
| `zsu_get_comments` | `handle_zsu_get_comments()` | `ShortsAjaxController::getComments()` | Public |
| `zsu_post_comment` | `handle_zsu_post_comment()` | `ShortsAjaxController::postComment()` | Public (Auth-aware) |
| `zsu_wishlist_toggle` | `handle_zsu_wishlist_toggle()` | `ShortsAjaxController::wishlistToggle()` | Public (Auth-aware) |
| `zsu_search_shorts` | `handle_zsu_search_shorts()` | `ShortsAjaxController::searchShorts()` | Public |
| `zsu_home_spin_check` | `ZSU_Home_Rail::ajax_spin_check()` | `ShortsSpinController::check()` | Public |
| `zsu_home_spin_result` | `ZSU_Home_Rail::ajax_spin_result()` | `ShortsSpinController::result()` | Public |
| `zsu_upload_short` | `handle_zsu_upload_short()` | `Vendor\ShortsStudioController::upload()` | Vendor Only |
| `zsu_save_short` | `handle_zsu_save_short()` | `Vendor\ShortsStudioController::save()` | Vendor Only |
| `zsu_vendor_get_shorts` | `handle_zsu_vendor_get_shorts()` | `Vendor\ShortsStudioController::getShorts()` | Vendor Only |
| `zsu_vendor_delete_short` | `handle_zsu_vendor_delete_short()` | `Vendor\ShortsStudioController::deleteShort()` | Vendor Only |
| `zsu_request_boost` | `handle_zsu_request_boost()` | `Vendor\ShortsStudioController::requestBoost()` | Vendor Only |
| `zsu_get_coins_balance` | `handle_zsu_get_coins_balance()` | `Vendor\ShortsStudioController::coinsBalance()` | Vendor Only |
| `zsu_coins_history` | `handle_zsu_coins_history()` | `Vendor\ShortsStudioController::coinsHistory()` | Vendor Only |
| `zsu_vendor_search_products` | `handle_zsu_vendor_search_products()` | `Vendor\ShortsStudioController::searchProducts()` | Vendor Only |
| `zsu_admin_get_stats` | `handle_zsu_admin_get_stats()` | `Admin\ShortsAdminController::stats()` | Admin Only |
| `zsu_admin_approve_short` | `handle_zsu_admin_approve_short()` | `Admin\ShortsAdminController::approve()` | Admin Only |
| `zsu_admin_reject_short` | `handle_zsu_admin_reject_short()` | `Admin\ShortsAdminController::reject()` | Admin Only |
| `zsu_admin_manual_boost` | `handle_zsu_admin_manual_boost()` | `Admin\ShortsAdminController::manualBoost()` | Admin Only |

---

## 4. Frontend Component Breakdown

1. **Shorts Full Page Feed (`shorts/index.blade.php`):**
   - Clean HTML structure hosting `#zsuContainer`, filter pills (`Trending`, `Latest`, `Popular`, `Boosted`), category horizontal selector, and `#zsuGrid`.
   - Embeds `#zsuLightbox` with vertical swipe-reel player, video controls, right action rail (Like, Comment, Share, Wishlist, Vendor Avatar), and bottom expandable shoppable product drawer (`#zsuProductTray`).
   - Dynamic CSS variable theme engine injects `--zsu-primary`, `--zsu-accent`, `--zsu-grad-start`, `--zsu-grad-end`, `--zsu-radius`, and glow effects.
2. **Shorts Home Rail (`components/k71-shorts-rail.blade.php`):**
   - Horizontal video reel preview on `k71.in` homepage and category pages.
   - Circular glowing vendor avatars, live view badges, autoplay preview on hover/intersect.
   - Includes Spin-to-Win floating action button (`#zsuWheelFab`) and modal overlay (`#zsuWheelOverlay`).
3. **Vendor Store Tab (`storefront/partials/shorts-tab.blade.php`):**
   - Integrated tab on vendor stores (`/store/{slug}`) showing vendor's published shorts.
4. **Vendor Shorts Studio (`vendor/shorts/studio.blade.php`):**
   - Direct video drag-and-drop / file upload with progress indicator.
   - Client-side and server-side video duration validation (15s to 90s standard ratio 9:16).
   - Video canvas frame capture for thumbnail selection or custom thumbnail upload.
   - Tagging store products from vendor's catalog with drag-to-reorder shoppable tray items.
   - Direct coin boost trigger: Standard (199 coins / 24h), Prime (499 coins / 72h), Elite (999 coins / 7 days).

---

## 5. Background Services & Cron Tasks

1. **Hourly Boost Expiry Task (`app/Console/Commands/ExpireShortsBoosts.php`):**
   - Queries `short_boosts` where `status = 'active'` and `ends_at <= now()`.
   - Marks boost as `expired`.
   - Clears `is_boosted` and `boost_type` on `shorts` table if no other active boost exists (mirrors WP `zsu_sync_boost_meta()`).
2. **Daily Trending Recalculation (`app/Console/Commands/RecalculateShortsTrending.php`):**
   - Formula: `(Views × 1) + (Likes × 4) + (Shares × 6) + (Clicks × 5) + (Comments × 3)` within a rolling 7-day window from `short_events`.
   - Updates `shorts.trending_score`.
3. **SEO Integration (`ShortsSeoService.php`):**
   - Injects Google-compliant `VideoObject` Schema.org JSON-LD with `name`, `description`, `thumbnailUrl`, `uploadDate`, `contentUrl`, `embedUrl`.
   - Feeds `/sitemap-shorts.xml` into marketplace sitemap index.
