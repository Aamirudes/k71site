# K71 — Decisions Log

> Every ambiguity is resolved here. Each entry explains the decision, why, and how to change it later.

---

## Pre-Made Decisions (from project brief)

### D1 — Payment method choice per vendor
**Decision:** Vendor picks in Dashboard → Settings → Payments: **UPI only**, **COD only**, or **Both**.
- "UPI" means existing PhonePe gateway + vendor direct UPI/QR claim flows (unchanged).
- Storage: reuse `vendor_settings.cod_enabled`, add `upi_enabled` (boolean); at least one must be true.
- Backfill: existing vendors get `upi_enabled = true`, `cod_enabled = false`.
**Why:** Gives vendors control without breaking existing UPI flows.
**Change later:** Edit `vendor_settings` migration and the `VendorPaymentMethodsController`.

### D2 — COD re-enabled marketplace-wide
**Decision:** Admin master switch `cod_marketplace_enabled` in `settings` table (default ON). Additional settings: max COD order value (default ₹5,000), optional COD fee (default ₹0), "COD allowed only if Shiprocket returns COD-capable courier".
**Why:** COD is essential for Indian marketplace adoption; was disabled due to earlier issues.
**Change later:** Admin → Settings → Payments panel.

### D3 — Server-side enforcement of payment methods
**Decision:** `CheckoutController` validates `payment_method` against vendor settings AND admin master switch AND serviceability AND COD limit, at both render and submit. Forged requests rejected.
**Why:** Client-side hiding is not security; everything must be validated server-side.
**Change later:** Edit `CheckoutController::process()` validation rules.

### D4 — COD accounting
**Decision:** COD order → placed → accepted → shipped (COD flag) → delivered → `payment_status = paid` only on courier delivery confirmation webhook. Commission recorded as receivable, netted against vendor's next UPI payouts. Refund/RTO for COD must not create a payout.
**Why:** Prevents paying out money that was never received.
**Change later:** Edit `ShiprocketWebhookController` and `SettlementService`.

### D5 — Vendor design presets (3×3×3)
**Decision:** Three choices: (a) Store layout (classic/editorial/minimal), (b) Colour preset (sage/midnight/sunset), (c) Product-page layout (gallery/immersive/showcase). Dashboard presets from spec section 7.3 are **dropped for now**.
**Why:** Dashboard presets are low-value compared to customer-facing presets. Simplifies scope.
**Change later:** Add `dashboard_preset` to `vendor_settings` and create 3 dashboard layout templates.

### D6 — Shorts lives at `shorts.k71.in`
**Decision:** `shorts.k71.in` is the canonical home. `k71.in/shorts` 301-redirects. Old routes `/shorts/cat/{slug}` and `/shorts/v/{id}` work on the new host.
**Why:** Consistent with the five-subdomain architecture.
**Change later:** Edit `ResolveVertical` middleware reserved subdomain list and Shorts route group.

### D7 — Boost/coins via PhonePe
**Decision:** Buying coins uses existing PhonePe flow; credited only after verified payment. Admin can adjust manually.
**Why:** Reuses existing payment infrastructure; prevents fraudulent coin grants.
**Change later:** Could add additional payment methods to coin purchase later.

### D8 — Spin wheel prizes → real coupons
**Decision:** Every spin prize maps to a real coupon in the `coupons` table. Prizes never show codes that don't exist.
**Why:** No dead prizes; users must be able to actually use what they win.
**Change later:** Edit `ShortsSpinController` and the `spin_prizes` seed data.

### D9 — Food COD and Used items behaviour
**Decision:** Food: COD allowed if vendor enables it. Delivery via vendor's own or pickup, NOT Shiprocket. Used items: direct contact + offers, no cart.
**Why:** Food needs rapid delivery (vendor-managed); used items are negotiation-based.
**Change later:** Edit listing type policies in `ListingTypePolicy`.

### D10 — Search: MySQL FULLTEXT
**Decision:** Use MySQL FULLTEXT behind a `SearchService` interface. No Elasticsearch/Meilisearch yet.
**Why:** Minimises infrastructure requirements; good enough for initial scale.
**Change later:** Implement `ElasticsearchSearchService` and swap binding in `AppServiceProvider`.

---

## Runtime Decisions (made during implementation)

### D11 — Database: additive migrations only
**Decision:** Never edit existing migration files. All changes via new additive migrations. Old COD-disable migration stays.
**Why:** Prevents breaking existing deployed databases.

### D12 — Shorts CSS/JS: loaded separately, not through Tailwind pipeline
**Decision:** Shorts assets copied to `public/shorts-assets/` with version hash, loaded only on Shorts pages. Tailwind preflight scoped away from Shorts.
**Why:** Prevents Tailwind CSS resets from breaking the plugin's hand-crafted UI.

### D13 — Food delivery: not routed through Shiprocket
**Decision:** Food orders use vendor's own delivery or customer pickup. Shiprocket is for product shipping only.
**Why:** Food delivery is time-sensitive and local; Shiprocket is for e-commerce parcels.

### D14 — Auctions: no buy-now by default
**Decision:** Auction listings do not have a buy-now option unless explicitly enabled per listing.
**Why:** Auctions should resolve through bidding; buy-now undermines the auction mechanism.

### D15 — Session domain
**Decision:** `SESSION_DOMAIN=.k71.in` — one login across all subdomains.
**Why:** Users should not need to re-login when switching between k71.in, food.k71.in, shorts.k71.in, etc.
