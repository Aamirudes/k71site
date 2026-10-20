# K71 — Complete Platform Vision, Features & Functional Requirements

## 1. Platform Overview

K71 is a fully custom, modern, multi-category online marketplace. It should feel and function like a purpose-built marketplace platform, not a basic WordPress website or a standard online shop.

K71 brings together product shopping, restaurants and food, professional services, used-item classifieds, enquiries, bidding, vendor stores and short-form videos in one connected online platform.

The entire platform must have a premium, modern, responsive UI/UX, with complete functionality across desktop, tablet and mobile.

The goal is to let customers discover, buy, enquire about, book, bid on and sell different kinds of products and services, while allowing vendors to create their own online stores and manage their businesses.

The platform should support future expansion without requiring its entire experience to be redesigned.

## 2. Domains and Main Navigation

The main website is:

- `k71.in` — the main K71 homepage and marketplace.

The platform has five dedicated category experiences:

1. `food.k71.in` — Food & Restaurants.
2. `services.k71.in` — Services.
3. `used.k71.in` — Used Items.
4. `enquiry.k71.in` — Enquiry marketplace.
5. `shorts.k71.in` — Short-form videos.

The existing Shorts functionality has already been built and must be preserved rather than unnecessarily recreated.

The main navigation should provide access to Home, Food & Restaurants, Services, Used Items, Enquiry and Shorts.

Every category must have its own identity, theme, page layouts, listing cards, filters, browsing experience and relevant actions. However, the entire platform should still feel like one unified K71 brand.

The category pages must display relevant listings automatically. When a vendor publishes a listing in a particular category, it should appear in the appropriate category experience according to its publication status, category, location and eligibility.

## 3. Complete Admin Control

The admin must have extensive control over the entire K71 platform.

The admin should be able to manage:

- The complete visual appearance and user experience.
- Homepage sections, layouts, navigation, headers and footers.
- Category pages, listing pages, store pages and product-detail pages.
- Colours, typography, spacing, images, banners, cards, buttons and page sections.
- Category names, subcategories, attributes, filters and listing rules.
- Users, vendors, stores, products, services, restaurants and other listings.
- Orders, enquiries, bids, bookings, reviews, reports and transactions.
- Vendor permissions, account status, approvals and restrictions.
- Commission rates, payment settings, promotions and monetisation options.
- Featured content, homepage placement and promotional sections.
- Platform policies, notifications and general settings.

The admin should be able to manage these functions through a clear, powerful control panel and visual editing experience wherever practical.

The goal is to control the platform as comprehensively as a custom-coded marketplace, rather than being limited to the basic settings of a standard WordPress theme.

## 4. Customer Accounts and Profiles

Users should be able to register, log in, log out, reset passwords and manage their accounts.

Each user should have a personal profile and an account area containing relevant information, such as:

- Profile photo and personal details.
- Contact details and saved addresses where needed.
- Favourites and saved listings.
- Orders, payments and purchase history.
- Enquiries and quotations.
- Bookings and appointments.
- Auction bids and results.
- Messages and notifications.
- Reviews and account preferences.
- Their own listings and selling activity, where applicable.

Users should be able to use the same account throughout K71 and its category experiences, without needing to create a separate account for every category.

Account information and private activity must remain protected from other users.

## 5. Vendor Registration and Store Creation

Vendors should be able to access their account through the My Account page and create their own online store.

The vendor onboarding experience should allow them to:

1. Register or log in.
2. Start the store-creation process.
3. Choose a unique store name and store URL.
4. Select the type of business and categories they want to operate in.
5. Provide the required business, contact and location details.
6. Add their store logo, banner, description and other relevant information.
7. Configure their store presentation and available design presets.
8. Add products, services, food items or other permitted listings.
9. Submit the store for approval if required.
10. Publish and manage their store according to K71's rules.

Store URLs should be unique and easy to share. The platform should support the intended wildcard-style store URL experience, subject to the final domain configuration.

Vendors should be able to manage their own business without needing the admin to manually place each listing on a category page.

The platform should automatically display eligible listings in their appropriate locations.

## 6. Vendor Dashboard

Every vendor should have a professional dashboard for managing their business.

The dashboard should include the relevant tools for:

- Store profile and appearance.
- Product and listing management.
- Adding, editing, publishing, pausing and removing listings.
- Images, videos, prices, stock and availability.
- Categories and listing attributes.
- Orders, fulfilment and delivery where applicable.
- Enquiries, quotes and customer messages.
- Bookings and appointments where applicable.
- Auction listings and bids where applicable.
- Reviews, ratings and customer feedback.
- Store analytics and listing performance.
- Promotions and featured listings where enabled.
- Payment records, commissions and payout information where applicable.
- Notifications, account settings and support.

Vendors must only be able to access and manage their own data and the functions they are authorised to use.

## 7. Vendor Store and Product Design Presets

Vendors should be able to customise the appearance of their own stores without needing to write code.

Provide three presets for each supported design area.

### Storefront presets

Vendors can choose between three professionally designed store layouts, with different arrangements of banners, featured listings, categories, product grids and store information.

### Product-detail presets

Vendors can choose between three product-page layouts, with different arrangements of product images, pricing, specifications, descriptions, seller details and relevant action buttons.

### Vendor-dashboard presets

Vendors can choose between three dashboard presentation styles, such as a visual card-based dashboard, a business overview with metrics, or a compact management interface.

The selected presets should affect the relevant vendor experience without changing the entire K71 platform or other vendors' stores.

Each preset must be responsive and usable on mobile devices.

Vendors should be able to preview a design before applying it and change presets later without losing their listings or business data.

The admin should control which presets are available, establish default designs and enforce platform-wide design rules.

Category-specific options should be appropriate for the business. For example, a restaurant should have relevant menu and restaurant presentation options, while a used-item seller should have layouts suitable for second-hand goods.

## 8. Main Homepage — k71.in

The homepage should act as the central discovery destination for K71.

It should provide:

- A premium homepage design.
- Prominent search and category navigation.
- Entry points to all five category experiences.
- Featured products, stores and vendors.
- Relevant food and restaurant listings.
- Recommended services.
- Used-item listings.
- Featured enquiries or buying opportunities.
- Integrated Shorts content.
- Trending, popular or recently added listings.
- Location-based discovery where relevant.
- Promotional sections controlled by the admin.
- Clear calls to action for shopping, selling and exploring.

The homepage layout and its sections should be configurable by the admin.

The platform should support meaningful discovery rather than displaying the same generic product grid for every category.

## 9. Food & Restaurants — food.k71.in

This section should provide a dedicated experience for food businesses, restaurants and food listings.

It should support:

- Restaurant profiles and store pages.
- Restaurant names, descriptions, logos, banners and locations.
- Food menus and menu categories.
- Food images, descriptions, prices and availability.
- Relevant dietary or product attributes where appropriate.
- Restaurant search and filters.
- Opening hours and service areas.
- Food ordering where enabled.
- Pickup and delivery options where supported.
- Orders, order statuses and customer notifications.
- Restaurant-specific vendor management.

A restaurant should be able to manage its own permitted listings and business information from its vendor dashboard.

Food ordering, delivery and payment features must be fully functional if offered. They should not be presented as available merely because a restaurant listing exists.

## 10. Services — services.k71.in

This section should support service providers and businesses offering services instead of physical products.

It should include:

- Service-provider profiles and business pages.
- Service descriptions, categories, images and prices or rates.
- Location and service-area information.
- Availability and working hours where relevant.
- Customer enquiries and quote requests.
- Booking and appointment options where enabled.
- Provider contact information and communication.
- Reviews and ratings.
- Search, sorting and service-specific filters.
- Vendor management for service listings.
- Booking and enquiry history.

Different service categories may require different fields. The platform should support category-specific information instead of forcing every service into the same product form.

## 11. Used Items — used.k71.in

This section should provide a dedicated second-hand marketplace for used and pre-owned goods.

It should support:

- Used-item listings and seller profiles.
- Item photos, descriptions and specifications.
- Item condition, age and relevant history.
- Asking prices and location.
- Negotiation or offer functionality where enabled.
- Direct seller contact or enquiry.
- Favourites and saved listings.
- Listing expiry, renewal and sold status.
- Reporting suspicious or prohibited listings.
- Relevant categories and search filters.

Where bidding is enabled, eligible used items should also support the auction functionality described later.

The interface should clearly distinguish used items from new products and show relevant condition information.

## 12. Enquiry Marketplace — enquiry.k71.in

The Enquiry section should allow buyers to describe what they need and receive relevant responses from vendors.

It should support:

- Buyer-submitted product or service requirements.
- Quantity, budget, location and specifications where applicable.
- Photos, documents or other relevant attachments.
- Enquiry submission and tracking.
- Vendor discovery and relevant lead distribution.
- Vendor quotations and responses.
- Buyer-vendor communication.
- Multiple quotes for a request where appropriate.
- Enquiry status, history and notifications.
- Closing or cancelling requests.
- Reporting spam and inappropriate responses.

For example, a buyer who needs a large quantity of marble should be able to submit a requirement and receive suitable vendor quotations rather than being forced to use a normal shopping cart.

The system should allow the admin to control which vendors can receive or respond to particular enquiries.

Private enquiry information must only be shared with authorised participants.

## 13. Shorts — shorts.k71.in

The existing Shorts feature must be preserved and integrated with the rest of K71.

The experience should support the functionality already present in the existing plugin and any additional features verified to be compatible.

Potential integrations include:

- Shared K71 accounts and profiles.
- Vendor identity and store links.
- Links from videos to relevant products, services or stores.
- Navigation between Shorts and other K71 sections.
- Relevant engagement and moderation features, if already supported or subsequently added.

Do not remove or rebuild working Shorts functionality without first auditing the existing implementation.

## 14. Product Listings and Listing Types

The platform should support different kinds of listings and different permitted actions.

Each listing should have a clear type that determines its available workflow.

### Purchase listings

Customers can view product details, add eligible products to a cart, complete checkout and track orders.

### Enquiry listings

Customers can submit questions or requirements and request quotations rather than purchasing directly.

### Booking listings

Customers can select available appointments, dates or service slots where supported.

### Auction listings

Eligible users can place bids according to the auction rules.

### Contact-seller listings

Customers can contact the seller without requiring a normal checkout.

The platform must enforce the permitted actions for each listing type. For example, hiding a purchase button is not enough to prevent an enquiry-only listing from being purchased through another route.

The appropriate category fields, forms, filters and page designs should change according to the listing type.

## 15. Shopping, Orders and Payments

Where direct purchases are enabled, K71 should support:

- Product details and pricing.
- Shopping cart.
- Checkout.
- Delivery and address information where relevant.
- Payment gateway integration.
- Order confirmation.
- Order history and status tracking.
- Vendor order management.
- Fulfilment and shipping workflows where applicable.
- Cancellation and refund workflows.
- Transaction records.
- Customer notifications.
- Relevant receipts and order details.

The platform should support vendor commissions and appropriate accounting for payments, refunds and payouts if the chosen marketplace payment provider supports the required model.

Payment processing must be implemented securely. The system must not claim an order is paid until the payment provider has confirmed the payment.

Actual gateway support, commission collection and vendor payouts must be verified before launch.

## 16. Auction and Bidding

Where auctions are enabled, K71 should support:

- Auction start and end times.
- Starting price and minimum bid increment.
- Auction status and eligibility rules.
- Valid bid submission and bid history.
- Notifications about relevant bid events.
- Automatic closing at the defined end time.
- Winner determination according to the rules.
- Winner notification and the appropriate next-step workflow.
- Protection against invalid or unauthorised bids.

The platform must handle simultaneous bids, expired auctions and other edge cases consistently.

Auction rules must be visible to participants and enforceable by the system.

## 17. Search, Filters and Discovery

Users should be able to find relevant content across K71.

Search and discovery should support:

- Keyword search.
- Category and subcategory filters.
- Location filters where relevant.
- Price ranges.
- Listing-type filters.
- Availability and condition filters where applicable.
- Sorting by relevance, newest, price or other suitable criteria.
- Vendor and store discovery.
- Category-specific search experiences.
- Pagination or efficient incremental loading.

Search results should respect listing visibility, approval status, permissions and category rules.

## 18. Messaging and Notifications

The platform should provide appropriate communication between customers, vendors and the admin.

Features should include:

- Enquiry-related conversations.
- Vendor responses and quotations.
- Relevant order or booking communication.
- In-platform notifications.
- Email notifications where configured.
- Other supported notification channels where configured.
- Message and enquiry history.
- Spam and abuse reporting.

Private communications must not be visible to unrelated users or vendors.

## 19. Reviews and Reputation

K71 should support trust and reputation features appropriate to each category.

These may include:

- Customer ratings and reviews.
- Vendor responses to reviews.
- Review moderation.
- Reporting fraudulent or abusive reviews.
- Verified-purchase indicators where appropriate.
- Relevant service or booking feedback.
- Vendor reputation summaries.

Review rules should distinguish between verified transactions and general feedback where the platform supports both.

## 20. Promotions and Monetisation

The platform should allow the admin to manage revenue-generating features, such as:

- Featured products and listings.
- Promoted stores.
- Sponsored placement.
- Promoted Shorts, where appropriate.
- Optional vendor subscriptions or premium plans.
- Commission rules.
- Paid visibility and promotion periods.
- Promotion status and expiry.

These features should be configurable and should not require the admin to manually edit source code for every routine campaign.

Paid placement must be distinguishable from organic results where required.

## 21. Admin Moderation and Platform Trust

The admin should be able to oversee the entire platform through a central control experience.

It should include:

- User and vendor management.
- Store and listing approval where required.
- Listing editing, suspension and removal.
- User reports and abuse complaints.
- Vendor restrictions and suspensions.
- Reviews and content moderation.
- Auction and enquiry oversight.
- Suspicious activity reporting.
- Platform logs and relevant audit history.
- Category-level rules and publishing controls.

Actions that affect users or vendors should be recorded where appropriate.

## 22. Analytics and Reports

The admin should have a useful overview of the marketplace, including relevant measures such as:

- Registered users and active vendors.
- Stores and published listings.
- Product views and listing engagement.
- Orders and sales.
- Enquiries and quotations.
- Booking activity.
- Auction activity.
- Promotion performance.
- Revenue, commissions and payout status where available.

Vendors should see statistics for their own stores and listings, without accessing another vendor's private data.

Reports must be based on actual recorded activity rather than fabricated or estimated values presented as exact figures.

## 23. SEO, Performance and Accessibility

The platform should be designed to perform well and remain discoverable.

Requirements include:

- Search-engine-friendly public pages.
- Appropriate page titles and metadata.
- Canonical URLs and structured data where applicable.
- Indexable category and listing pages.
- Efficient image handling and responsive images.
- Fast loading and minimal unnecessary scripts.
- Accessible navigation and forms.
- Keyboard-friendly interactions.
- Readable typography and appropriate contrast.
- Responsive layouts across mobile, tablet and desktop.

The five category experiences should have clear URLs and coherent navigation without creating unnecessary duplicate pages or conflicting search indexing.

## 24. Security, Privacy, Reliability and Backups

The entire platform must protect its users, vendors and business data.

Requirements include:

- Secure login and account recovery.
- Strong permission checks.
- Protection against unauthorised data access.
- Secure handling of uploaded files.
- Validation of user-submitted data.
- Protection against common web vulnerabilities.
- Safe payment and transaction handling.
- Privacy-conscious treatment of personal information.
- Account data export or deletion where required.
- Error logging and meaningful failure messages.
- Reliable backups and tested restoration.
- Safe updates that preserve existing data.
- Appropriate monitoring of critical workflows.

No vendor should be able to access another vendor's private dashboard information, listings that are not theirs to manage, payment details or private customer communications.

## 25. Design and User Experience

The entire K71 platform must have a premium and consistent visual identity while allowing each category to feel appropriate to its purpose.

The design should be:

- Modern and professional.
- Visually distinctive without being cluttered.
- Responsive on mobile and desktop.
- Smooth and intuitive to navigate.
- Consistent in typography, spacing, icons and interactions.
- Suitable for a large marketplace with many listings.
- Clear about prices, listing conditions and available actions.
- Accessible and readable.
- Configurable by the admin wherever practical.

Category-specific design should be purposeful: food listings should look appropriate for restaurants, services should communicate provider details and availability, used items should emphasise condition and seller information, and enquiry pages should make requirements and quotations easy to understand.

The platform should avoid generic templates that make every category look identical.

## 26. Existing Website and Development Requirements

K71 already has an existing WordPress-based website on `marblemandi.org`, with marketplace functionality and a Shorts plugin reported to be working.

The intended final main domain is `k71.in`.

Before modifying or replacing anything, review the current website and establish which features already work, which need improvement and which are missing.

Preserve working functionality and existing data unless a deliberate migration or replacement is approved.

The development and testing process should verify the actual customer journey, vendor journey and admin controls, not just whether a page renders or a plugin installs.

All essential features must be tested before the platform is considered ready for production.

## 27. Definition of Success

K71 is successful when:

- Customers can discover and use the correct category experiences.
- Vendors can create stores, publish listings and manage their businesses.
- Listings automatically appear in the appropriate category.
- Purchases, enquiries, bookings and bids follow their intended workflows.
- Admins can control the entire platform and its policies.
- Vendors can customise their permitted store and dashboard designs.
- The existing Shorts feature continues to work.
- Accounts and data remain consistent across the platform.
- Payments, permissions and private information are handled securely.
- The experience works reliably on mobile and desktop.
- The platform can be improved without repeatedly breaking existing features.

**Important instruction:** Treat this document as the functional vision and requirements for K71, not as proof that any feature already exists or is working. Inspect the existing implementation before recommending changes. Do not unnecessarily rebuild working features. Identify missing functionality, clarify any important ambiguity, and verify each implemented workflow before declaring it complete.