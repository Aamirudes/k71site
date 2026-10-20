# K71 — Phase 0 Security & Codebase Audit

> Generated: 2026-10-19
> Auditor: Antigravity (autonomous mode)

---

## 1. Environment & Credentials (CRITICAL)

### Finding 1.1 — .env with live credentials is in the zip/repo
- **Severity:** CRITICAL
- **Details:** Previously contained production DB credentials, legacy SMTP credentials, PhonePe API keys, and APP_KEY.
- **Fix:** `.env` IS in `.gitignore` ✅. Ensure no `.env` or credentials are ever committed to Git.
- **Action Required by Owner:** Rotate ALL credentials after deployment:
  - [ ] MySQL password
  - [ ] Gmail app password
  - [ ] PhonePe client_secret (contact PhonePe)
  - [ ] PhonePe salt_key  
  - [ ] APP_KEY (re-encrypt any encrypted data first)

### Finding 1.2 — PhonePeService has hardcoded production credentials as fallbacks
- **Severity:** CRITICAL
- **File:** `app/Services/PhonePeService.php` lines 24-36
- **Details:** If env vars are missing or set to test values, the service falls back to hardcoded production credentials:
  - Merchant ID: `M23TKU0R0MJ8I`
  - Client ID: `SU2610161656542399718703`
  - Client Secret: `8967e108-d791-45f6-a1b2-fb168beed1ee`
  - Salt Key: `8967e108-d791-45f6-a1b2-fb168beed1ee`
- **Fix:** Remove all hardcoded credentials; throw an exception if env vars are not set.

### Finding 1.3 — APP_DEBUG is false ✅
- **File:** `.env` line 4
- **Status:** `APP_DEBUG=false` — correct for production.

---

## 2. Dangerous Public Routes (CRITICAL)

### Finding 2.1 — `/setup-database` route with hardcoded secret
- **Severity:** CRITICAL
- **File:** `routes/web.php` lines 149-171
- **Details:** Public GET route that runs `artisan migrate` and `db:seed` with a query-string secret `shopdost2026`. Anyone who guesses this URL can wipe/reseed the database.
- **Fix:** DELETE this route entirely. Use `php artisan migrate --force` via CLI.

### Finding 2.2 — Public helper PHP scripts in `public/`
- **Severity:** HIGH
- **Files:**
  - `public/fix.php` (17KB — likely runs fixes/migrations)
  - `public/fix_storage.php` (5KB — filesystem operations)
  - `public/setup.php` (9KB — setup wizard)
  - `public/deploy_pull.php` (2KB — GIT PULL FROM WEB!)
  - `public/check_log.php` (349B — reads log files)
  - `public/create_test_product.php` (8KB — creates DB records)
  - `public/optimize_existing_images.php` (4KB — filesystem operations)
  - `public/version_check.php` (497B — exposes version info)
- **Fix:** Delete ALL. Convert useful ones to artisan commands.

---

## 3. CSRF Exemptions (MEDIUM)

### Finding 3.1 — cart/* and wishlist/* CSRF exempted
- **Severity:** MEDIUM
- **File:** `bootstrap/app.php` lines 27-36
- **Details:** `cart/*` and `wishlist/*` are CSRF-exempt. These POST/PUT/DELETE routes should verify CSRF. The JS should send `X-XSRF-TOKEN`.
- **Fix:** Remove `cart/*` and `wishlist/*` from CSRF exceptions. Ensure frontend JS sends the CSRF token.

### Finding 3.2 — Webhook exemptions (correct)
- **Status:** PhonePe and Shiprocket webhook exemptions are appropriate — webhooks come from external servers.
- **Concern:** PhonePe webhook signature verification appears incomplete — see Finding 4.1.

---

## 4. Webhook Security (HIGH)

### Finding 4.1 — PhonePe webhook lacks signature verification
- **Severity:** HIGH
- **File:** `app/Http/Controllers/Webhook/PhonePeWebhookController.php`
- **Details:** The webhook handler does NOT verify the `X-VERIFY` signature from PhonePe. An attacker could forge webhook payloads to mark orders as paid.
- **Fix:** Verify the SHA256 HMAC signature of the response payload against the salt key before processing.

### Finding 4.2 — PhonePe callback auto-logs in user without auth check
- **Severity:** MEDIUM
- **File:** `app/Http/Controllers/PaymentController.php` line 42-44
- **Details:** `Auth::login($order->user, true)` — if a user is not logged in during callback, it auto-logs them in based solely on the order association. An attacker with a valid order ID could potentially exploit this.
- **Fix:** Only auto-login if the callback contains a valid PhonePe-signed response.

---

## 5. Vendor Simulation Routes (MEDIUM)

### Finding 5.1 — simulate-shipping and simulate-settlement routes in production
- **Severity:** MEDIUM
- **File:** `routes/vendor.php` lines 28, 31
- **Details:** Routes `vendor.orders.simulate_shipping` and `vendor.orders.simulate_settlement` are registered without environment guard. These should only work in `local`/`testing` environments.
- **Fix:** Wrap in `if (app()->environment('local', 'testing'))` or add middleware that 404s in production.

---

## 6. Authorisation Gaps

### Finding 6.1 — EnsureVendorOwnership middleware (needs verification)
- **File:** `app/Http/Middleware/EnsureVendorOwnership.php`
- **Status:** Need to verify it properly isolates vendor A from vendor B's resources (products, orders, coupons, analytics).

### Finding 6.2 — Customer order access (needs tests)
- **File:** `app/Http/Controllers/OrderController.php`
- **Status:** Payment page checks `$order->user_id !== Auth::id()` ✅. Need to verify all order/invoice routes enforce this.

---

## 7. Shared Hosting Layout

### Finding 7.1 — Root .htaccess exists with security rules ✅
- **File:** `.htaccess`
- **Details:** Blocks `.env`, `.git`, `composer.json`, `artisan`. Rewrites to `public/` directory. This is the correct pattern for shared hosting where `public_html` is the document root.

### Finding 7.2 — Root index.php exists as proxy ✅ 
- **File:** `index.php`
- **Details:** Delegates to `public/index.php`. Standard for shared hosting.

### Finding 7.3 — Storage and vendor directories need blocking
- **Status:** `.htaccess` blocks `.env` and `.git` but does NOT explicitly block `/storage/`, `/vendor/`, `/database/`, `/config/`, `/resources/`, `/bootstrap/`. While the rewrite rules should prevent direct access, an explicit deny is safer.
- **Fix:** Add explicit deny rules for sensitive directories.

---

## 8. Route Audit Summary

### Working Routes (confirmed by code inspection):
| Route | Controller | Status |
|-------|-----------|--------|
| `GET /` | HomeController@index | ✅ Works |
| `GET /catalog` | CatalogController@index | ✅ Works |
| `GET /category/{slug}` | CatalogController@category | ✅ Works |
| `GET /product/{slug}` | ProductViewController@show | ✅ Works |
| `GET /cart` | CartController@index | ✅ Works |
| `POST /cart/add` | CartController@add | ✅ Works |
| `GET /checkout/{vendor}` | CheckoutController@index | ✅ Works |
| `POST /checkout/{vendor}` | CheckoutController@process | ✅ Works |
| `GET /login` | LoginController@show | ✅ Works |
| `POST /login` | LoginController@login | ✅ Works |
| `GET /register` | RegisterController@show | ✅ Works |
| `GET /sell` | RegisterController@vendorShow | ✅ Works |
| `GET /vendor/dashboard` | DashboardController@index | ✅ Works |
| `GET /admin/dashboard` | AdminController@dashboard | ✅ Works |
| Sitemaps | SitemapController | ✅ Works |
| Payment flows | PaymentController | ✅ Works |
| Webhooks | PhonePe/Shiprocket | ⚠️ Works but see security findings |

### Dead/Dangerous Routes (to remove):
| Route | Issue |
|-------|-------|
| `GET /setup-database` | Runs migrations with hardcoded secret |
| `POST vendor/orders/{order}/simulate-shipping` | Test-only, no env guard |
| `POST vendor/orders/{order}/simulate-settlement` | Test-only, no env guard |

### Missing Routes (to add in K71):
| Route | Purpose |
|-------|---------|
| `shorts.k71.in/*` | Shorts vertical |
| `food.k71.in/*` | Food vertical |
| `services.k71.in/*` | Services vertical |
| `used.k71.in/*` | Used items vertical |
| `enquiry.k71.in/*` | Enquiry vertical |

---

## 9. Spec vs Reality Matrix

| Spec Section | Exists? | Status | Notes |
|-------------|---------|--------|-------|
| 1. Platform Overview | Partial | Code is "Buyz" marketplace | Needs K71 rebrand |
| 2. Domains / Multi-host | ❌ | No vertical subdomains | Only vendor subdomains exist |
| 3. Admin Control | Partial | Custom admin panel exists | Needs expansion |
| 4. Customer Accounts | ✅ | OTP + Google login | Working |
| 5. Vendor Registration | ✅ | Onboarding flow exists | Working |
| 6. Vendor Dashboard | ✅ | Full dashboard exists | Needs design presets |
| 7. Design Presets | ❌ | `layout_preset` column exists but only `classic` works | Need 3×3×3 presets |
| 8. Homepage | ✅ | 46KB home.blade.php | Needs K71 sections |
| 9. Food & Restaurants | ❌ | No food vertical | Build from scratch |
| 10. Services | ❌ | No services vertical | Build from scratch |
| 11. Used Items | ❌ | No used-items vertical | Build from scratch |
| 12. Enquiry | ❌ | No enquiry vertical | Build from scratch |
| 13. Shorts | ❌ | Not in Laravel | Port from WP plugin |
| 14. Listing Types | ❌ | Only `purchase` type | Need all types |
| 15. Shopping/Orders | ✅ | Full cart→checkout→payment | Working |
| 16. Auctions | ❌ | No auction system | Build from scratch |
| 17. Search/Filters | Partial | Basic catalog filters | Need FULLTEXT search |
| 18. Messaging | ❌ | No messaging system | Build from scratch |
| 19. Reviews | ❌ | Feature flag `false` | Build reviews |
| 20. Promotions | ❌ | No promotions | Build from scratch |
| 21. Moderation | Partial | Basic admin approval | Need full moderation |
| 22. Analytics | Partial | Dashboard stats exist | Need event-based |
| 23. SEO/Performance | ✅ | Sitemaps, image optimizer | Working |
| 24. Security/Privacy | ⚠️ | Multiple issues found | See findings above |
| 25. Design/UX | Partial | Basic Tailwind UI | Need glassmorphism overhaul |

---

## 10. Immediate Actions (Phase 0 Fixes)

1. ✅ Remove `/setup-database` route
2. ✅ Delete all public helper scripts
3. ✅ Remove hardcoded credentials from PhonePeService
4. ✅ Add PhonePe webhook signature verification
5. ✅ Re-enable CSRF for cart/wishlist
6. ✅ Guard simulate routes to local env only
7. ✅ Enhance .htaccess security
8. ✅ Rewrite `.env.example` with placeholders
9. ✅ Add baseline feature tests
10. ✅ Add security headers middleware
11. ✅ Add rate limiting
