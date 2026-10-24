<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'shop_name',
        'vendor_type',
        'description',
        'logo',
        'banner',
        'contact_email',
        'contact_phone',
        'city',
        'state',
        'pincode',
        'address',
        'phone',
        'email',
        'pickup_address',
        'return_address',
        'gstin',
        'social_links',
        'store_policies',
        'seo_title',
        'seo_description',
        'og_image',
        'colour_palette',
        'layout_preset',
        'product_preset',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'vacation_mode',
        'vacation_message',
        'slug_reserved_at',
        'slug_released_at',
    ];

    protected $casts = [
        'pickup_address' => 'json',
        'return_address' => 'json',
        'social_links' => 'json',
        'approved_at' => 'datetime',
        'slug_reserved_at' => 'datetime',
        'slug_released_at' => 'datetime',
        'vacation_mode' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(VendorSetting::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shorts(): HasMany
    {
        return $this->hasMany(Short::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Product::class)->where('vertical', 'services')->where('status', 'active');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function isServiceProvider(): bool
    {
        return $this->products()->where('vertical', 'services')->exists();
    }

    public function shortBoosts(): HasMany
    {
        return $this->hasMany(ShortBoost::class);
    }

    public function shortFollowers(): HasMany
    {
        return $this->hasMany(ShortFollow::class, 'vendor_id');
    }

    /**
     * Check vendor vertical type.
     */
    public function isClothing(): bool
    {
        return empty($this->vendor_type) || in_array($this->vendor_type, ['clothing', 'footwear']);
    }

    public function isFood(): bool
    {
        return $this->vendor_type === 'food';
    }

    public function isServices(): bool
    {
        return in_array($this->vendor_type, ['services', 'local_services']);
    }

    public function isUsed(): bool
    {
        return $this->vendor_type === 'used';
    }

    public function isB2B(): bool
    {
        return in_array($this->vendor_type, ['b2b', 'machinery']);
    }

    public function isElectronics(): bool
    {
        return $this->vendor_type === 'electronics';
    }

    public function isAutomotive(): bool
    {
        return $this->vendor_type === 'automotive';
    }

    public function isBeauty(): bool
    {
        return $this->vendor_type === 'beauty';
    }

    public function isHomeLiving(): bool
    {
        return $this->vendor_type === 'home_living';
    }

    public function isGroceries(): bool
    {
        return $this->vendor_type === 'groceries';
    }

    public function isToysSports(): bool
    {
        return $this->vendor_type === 'toys_sports';
    }

    public function isDigitalGigs(): bool
    {
        return $this->vendor_type === 'digital_services';
    }

    public function isRealEstate(): bool
    {
        return $this->vendor_type === 'real_estate';
    }

    public function getSectorConfig(): array
    {
        $type = $this->vendor_type ?: 'clothing';

        $sectors = [
            'electronics' => [
                'key' => 'electronics',
                'label' => 'Electronics & IT',
                'icon' => '📱',
                'color' => 'from-blue-600 to-cyan-600',
                'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                'catalog_name' => 'Devices & Electronics Catalog',
                'item_singular' => 'Device / IT Product',
                'add_btn' => 'Add Tech Product',
                'specs_label' => 'Technical Specs (RAM, Storage, Processor, Warranty)',
                'title_placeholder' => 'e.g. Apple iPhone 15 Pro 256GB Natural Titanium',
                'brand_placeholder' => 'e.g. Apple, Samsung, Sony, HP, Asus, Lenovo',
                'brand_label' => 'Brand / Manufacturer',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '29999.00',
                'unit_label' => 'Package Weight (Grams)',
                'stock_label' => 'Available Stock Units',
                'gst_default' => '18',
                'desc_placeholder' => 'Detailed technical overview, features, inside the box, warranty terms...',
                'default_specs' => [
                    ['key' => 'RAM & Storage', 'value' => '8GB RAM + 128GB Storage'],
                    ['key' => 'Processor', 'value' => 'Octa-Core High-Speed Chipset'],
                    ['key' => 'Warranty', 'value' => '1 Year Brand Manufacturer Warranty'],
                    ['key' => 'Condition', 'value' => 'Brand New (Sealed Pack)'],
                ],
            ],
            'automotive' => [
                'key' => 'automotive',
                'label' => 'Automotive & Spares',
                'icon' => '🚗',
                'color' => 'from-red-600 to-amber-600',
                'badge' => 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
                'catalog_name' => 'Auto Parts & Spares Catalog',
                'item_singular' => 'Auto Part / Accessory',
                'add_btn' => 'Add Spare Part',
                'specs_label' => 'Vehicle Compatibility & Fitment Specs',
                'title_placeholder' => 'e.g. Synthetic Engine Oil 5W-40 (4 Litres)',
                'brand_placeholder' => 'e.g. Bosch, Castrol, Motul, Brembo, MRF, CEAT',
                'brand_label' => 'Manufacturer / Brand',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '2499.00',
                'unit_label' => 'Weight / Pack Volume (Grams/ml)',
                'stock_label' => 'Available Stock Units',
                'gst_default' => '18',
                'desc_placeholder' => 'Vehicle compatibility list, installation instructions, performance certification...',
                'default_specs' => [
                    ['key' => 'Vehicle Compatibility', 'value' => 'Universal / Multi-Brand Compatible'],
                    ['key' => 'Fitment Position', 'value' => 'Engine / Front Axle'],
                    ['key' => 'Warranty', 'value' => '1 Year / 20,000 KM Replacement Warranty'],
                ],
            ],
            'clothing' => [
                'key' => 'clothing',
                'label' => 'Fashion & Apparel',
                'icon' => '👗',
                'color' => 'from-rose-500 to-pink-600',
                'badge' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                'catalog_name' => 'Fashion & Apparel Catalog',
                'item_singular' => 'Garment / Footwear',
                'add_btn' => 'Add Fashion Product',
                'specs_label' => 'Fabric, Fit, Sizes & Care Instructions',
                'title_placeholder' => 'e.g. Handloom Pure Banarasi Katan Silk Saree with Blouse',
                'brand_placeholder' => 'e.g. Zara, FabIndia, Manyavar, Raymond, UrbanCraft',
                'brand_label' => 'Brand / Designer Label',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '1499.00',
                'unit_label' => 'Package Weight (Grams)',
                'stock_label' => 'Available Stock Units',
                'gst_default' => '5',
                'desc_placeholder' => 'Fabric weave details, style advice, blouse piece details, wash care instructions...',
                'default_specs' => [
                    ['key' => 'Fabric Material', 'value' => '100% Pure Combed Cotton / Silk Blend'],
                    ['key' => 'Fit / Cut', 'value' => 'Regular / Relaxed Fit'],
                    ['key' => 'Pattern & Work', 'value' => 'Woven Zari / Solid Handcrafted'],
                    ['key' => 'Wash Care', 'value' => 'Dry clean only or Machine wash cold gentle'],
                ],
            ],
            'beauty' => [
                'key' => 'beauty',
                'label' => 'Beauty & Personal Care',
                'icon' => '💄',
                'color' => 'from-pink-500 to-rose-600',
                'badge' => 'bg-pink-500/10 text-pink-600 dark:text-pink-400 border-pink-500/20',
                'catalog_name' => 'Beauty & Wellness Catalog',
                'item_singular' => 'Beauty Product',
                'add_btn' => 'Add Beauty Item',
                'specs_label' => 'Skin Type, Ingredients & Volume',
                'title_placeholder' => 'e.g. 10% Niacinamide & Zinc Clarifying Face Serum 30ml',
                'brand_placeholder' => 'e.g. Minimalist, Plum, Mamaearth, Biotique, Forest Essentials',
                'brand_label' => 'Brand / Cosmetic House',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '599.00',
                'unit_label' => 'Net Volume / Weight (ml/g)',
                'stock_label' => 'Available Stock Units',
                'gst_default' => '18',
                'desc_placeholder' => 'Active ingredients, skin/hair suitability, dermatologically tested, how to apply...',
                'default_specs' => [
                    ['key' => 'Suitable Skin Type', 'value' => 'All Skin Types (Dermatologically Tested)'],
                    ['key' => 'Key Active Ingredients', 'value' => 'Pure Actives with Botanical Extracts'],
                    ['key' => 'Net Volume', 'value' => '30 ml / 50 g'],
                    ['key' => 'Shelf Life', 'value' => '24 Months from Manufacturing'],
                ],
            ],
            'home_living' => [
                'key' => 'home_living',
                'label' => 'Home & Living',
                'icon' => '🏠',
                'color' => 'from-amber-600 to-yellow-600',
                'badge' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                'catalog_name' => 'Furniture & Home Decor',
                'item_singular' => 'Furniture / Decor Item',
                'add_btn' => 'Add Home Product',
                'specs_label' => 'Wood / Material, Dimensions & Assembly',
                'title_placeholder' => 'e.g. Solid Sheesham Wood 6-Seater Dining Table Set',
                'brand_placeholder' => 'e.g. Urban Ladder, Pepperfry, Godrej Interio, Local Craft',
                'brand_label' => 'Brand / Wood Workshop',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '18500.00',
                'unit_label' => 'Item Weight (Kg)',
                'stock_label' => 'Available Units',
                'gst_default' => '18',
                'desc_placeholder' => 'Wood finish, dimensions, seating capacity, assembly instructions, warranty details...',
                'default_specs' => [
                    ['key' => 'Primary Material', 'value' => '100% Grade-A Seasoned Sheesham Wood'],
                    ['key' => 'Finish', 'value' => 'Natural Teak Walnut Matte Polish'],
                    ['key' => 'Assembly Required', 'value' => 'Free Expert Assembly Provided at Delivery'],
                    ['key' => 'Warranty', 'value' => '3 Years Termite & Structural Warranty'],
                ],
            ],
            'groceries' => [
                'key' => 'groceries',
                'label' => 'Groceries & FMCG',
                'icon' => '🛒',
                'color' => 'from-lime-600 to-emerald-600',
                'badge' => 'bg-lime-500/10 text-lime-600 dark:text-lime-400 border-lime-500/20',
                'catalog_name' => 'Staples & FMCG Inventory',
                'item_singular' => 'Grocery Item',
                'add_btn' => 'Add Grocery Item',
                'specs_label' => 'Net Weight, Shelf Life & Organic Cert',
                'title_placeholder' => 'e.g. Royal Aged Dum Basmati Rice (5kg Bag)',
                'brand_placeholder' => 'e.g. Fortune, India Gate, Aashirvaad, Tata Sampann, 24 Mantra',
                'brand_label' => 'Brand / Miller',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '650.00',
                'unit_label' => 'Net Quantity (Kg/Grams)',
                'stock_label' => 'Available Packets/Bags',
                'gst_default' => '5',
                'desc_placeholder' => 'Grain quality, harvest batch, FSSAI certified, storage instructions...',
                'default_specs' => [
                    ['key' => 'Net Weight', 'value' => '5 Kg Vacuum Sealed'],
                    ['key' => 'Food Preference', 'value' => '100% Vegetarian (FSSAI Approved)'],
                    ['key' => 'Shelf Life', 'value' => '12 Months from Packaging'],
                    ['key' => 'Country of Origin', 'value' => 'India'],
                ],
            ],
            'toys_sports' => [
                'key' => 'toys_sports',
                'label' => 'Toys, Books & Sports',
                'icon' => '🧸',
                'color' => 'from-cyan-600 to-teal-600',
                'badge' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20',
                'catalog_name' => 'Toys, Sports & Books Catalog',
                'item_singular' => 'Book / Gear / Toy',
                'add_btn' => 'Add Item',
                'specs_label' => 'Age Group, Material & Weight',
                'title_placeholder' => 'e.g. Handcrafted English Willow Cricket Bat Grade 1+',
                'brand_placeholder' => 'e.g. SS, SG, Cosco, Lego, Nivia, Oxford',
                'brand_label' => 'Brand / Publisher',
                'price_label' => 'Selling Price (₹)',
                'price_placeholder' => '2499.00',
                'unit_label' => 'Weight (Grams)',
                'stock_label' => 'Available Stock Units',
                'gst_default' => '12',
                'desc_placeholder' => 'Specifications, age suitability, safety certifications, material quality...',
                'default_specs' => [
                    ['key' => 'Recommended Age Group', 'value' => '8 Years and Above / Professional'],
                    ['key' => 'Material Composition', 'value' => 'High-Grade Durable Non-Toxic Material'],
                    ['key' => 'Warranty', 'value' => '6 Months Manufacturer Guarantee'],
                ],
            ],
            'digital_services' => [
                'key' => 'digital_services',
                'label' => 'Digital Gigs & Freelance',
                'icon' => '💼',
                'color' => 'from-violet-600 to-indigo-600',
                'badge' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20',
                'catalog_name' => 'Digital Gigs & Deliverables',
                'item_singular' => 'Gig / Service Package',
                'add_btn' => 'Add Gig Package',
                'specs_label' => 'Turnaround Days, Deliverables & Revisions',
                'title_placeholder' => 'e.g. High-Converting Figma UI/UX Landing Page Design',
                'brand_placeholder' => 'e.g. PixelCraft Studio, Solo Creator Agency',
                'brand_label' => 'Agency / Freelancer Studio',
                'price_label' => 'Package Starting Rate (₹)',
                'price_placeholder' => '4999.00',
                'unit_label' => 'Turnaround Time (Days)',
                'stock_label' => 'Active Project Slots Available',
                'gst_default' => '18',
                'desc_placeholder' => 'Scope of work, milestone deliverables, source files included, revision terms...',
                'default_specs' => [
                    ['key' => 'Delivery Turnaround', 'value' => '3 - 5 Business Days'],
                    ['key' => 'Source Files Included', 'value' => 'Yes (Figma / Vector AI / Full GitHub Code)'],
                    ['key' => 'Revisions', 'value' => '3 Rounds of Revisions Included'],
                    ['key' => 'Commercial License', 'value' => '100% Full Ownership Transfer'],
                ],
            ],
            'real_estate' => [
                'key' => 'real_estate',
                'label' => 'Real Estate & Assets',
                'icon' => '🏡',
                'color' => 'from-emerald-700 to-teal-800',
                'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                'catalog_name' => 'Property & Asset Listings',
                'item_singular' => 'Property / Apartment / Land',
                'add_btn' => 'Add Property Listing',
                'specs_label' => 'Carpet Area, BHK, Location & RERA Reg',
                'title_placeholder' => 'e.g. 3 BHK Luxury Gated High-Rise Apartment with Balcony',
                'brand_placeholder' => 'e.g. DLF, Godrej Properties, Prestige, Local Builder',
                'brand_label' => 'Builder / Developer / Project Name',
                'price_label' => 'Total Price / Expected Rent (₹)',
                'price_placeholder' => '8500000.00',
                'unit_label' => 'Carpet Area (Sq. Ft.)',
                'stock_label' => 'Available Units / Floors',
                'gst_default' => '5',
                'desc_placeholder' => 'Detailed property layout, society amenities, nearby metro/landmarks, RERA registration, possession timeline...',
                'default_specs' => [
                    ['key' => 'Carpet Area', 'value' => '1,650 Sq.Ft (Super Built-up 2,100 Sq.Ft)'],
                    ['key' => 'Configuration', 'value' => '3 BHK (3 Bedrooms + 3 Bathrooms + 2 Balconies)'],
                    ['key' => 'Furnishing Status', 'value' => 'Semi-Furnished with Modular Kitchen & Wardrobes'],
                    ['key' => 'Facing & Floor', 'value' => 'North-East Facing (Vastu Compliant) • 8th Floor of 14'],
                    ['key' => 'Possession Status', 'value' => 'Ready to Move In (OC & CC Received)'],
                    ['key' => 'RERA Registration No', 'value' => 'RAJ/RERA/P/2023/1089'],
                    ['key' => 'Reserved Parking', 'value' => '2 Covered Basement Car Parking Slots'],
                ],
            ],
            'machinery' => [
                'key' => 'machinery',
                'label' => 'Machinery & B2B RFQ',
                'icon' => '🛠️',
                'color' => 'from-purple-600 to-indigo-700',
                'badge' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                'catalog_name' => 'Industrial Machinery & Materials',
                'item_singular' => 'Industrial Machine / Slabs',
                'add_btn' => 'Add Industrial Product',
                'specs_label' => 'Technical Capacity, Voltage & MOQ',
                'title_placeholder' => 'e.g. Automatic Marble & Granite Bridge Cutting Machine 15HP',
                'brand_placeholder' => 'e.g. K71 Heavy Machines, Kirloskar, Siemens, Makrana Tools',
                'brand_label' => 'Manufacturer / Foundry',
                'price_label' => 'Ex-Factory Price / MOQ Rate (₹)',
                'price_placeholder' => '485000.00',
                'unit_label' => 'Machine Weight (Kg / Tons)',
                'stock_label' => 'Available Units in Stock',
                'gst_default' => '18',
                'desc_placeholder' => 'Technical specifications, cutting speed, motor power, power consumption, installation support...',
                'default_specs' => [
                    ['key' => 'Motor Power', 'value' => '15 HP Main Spindle Motor (Pure Copper)'],
                    ['key' => 'Operating Voltage', 'value' => '415V Three Phase 50Hz (Industrial)'],
                    ['key' => 'Blade / Working Capacity', 'value' => '350mm - 600mm Blade (3200x2000mm Bed)'],
                    ['key' => 'Automation Grade', 'value' => 'Semi-Automatic with Microcomputer PLC Control'],
                    ['key' => 'Warranty Coverage', 'value' => '1 Year Comprehensive On-Site Warranty'],
                ],
            ],
            'food' => [
                'key' => 'food',
                'label' => 'Cloud Kitchen & Menu',
                'icon' => '🍽️',
                'color' => 'from-amber-500 to-orange-600',
                'badge' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
                'catalog_name' => 'Digital Menu & Kitchen Dishes',
                'item_singular' => 'Dish / Meal',
                'add_btn' => 'Add Dish',
                'specs_label' => 'Portion Size, Dietary & Prep Time',
                'title_placeholder' => 'e.g. Royal Hyderabadi Dum Gosht Biryani (Handi)',
                'brand_placeholder' => 'e.g. Biryani Blues, Bawarchi Kitchen, The Oven Story',
                'brand_label' => 'Kitchen / Restaurant Brand',
                'price_label' => 'Dish Selling Price (₹)',
                'price_placeholder' => '380.00',
                'unit_label' => 'Portion Weight (Grams)',
                'stock_label' => 'Daily Kitchen Inventory Portions',
                'gst_default' => '5',
                'desc_placeholder' => 'Ingredients, culinary style, accompaniments included, allergen details...',
                'default_specs' => [
                    ['key' => 'Portion Size', 'value' => '1 - 2 Persons (650g Servings)'],
                    ['key' => 'Dietary Classification', 'value' => 'Pure Vegetarian / Halal Certified'],
                    ['key' => 'Spice Level', 'value' => 'Authentic Desi Medium Spicy'],
                    ['key' => 'Preparation Time', 'value' => '15 - 20 Minutes Freshly Cooked'],
                ],
            ],
            'services' => [
                'key' => 'services',
                'label' => 'Doorstep & Pro Services',
                'icon' => '🔧',
                'color' => 'from-blue-500 to-indigo-600',
                'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                'catalog_name' => 'Service Packages & Bookings',
                'item_singular' => 'Service Package',
                'add_btn' => 'Add Service',
                'specs_label' => 'Service Duration, Tools & Radius',
                'title_placeholder' => 'e.g. Complete Deep Home Cleaning & Sanitization (2/3 BHK)',
                'brand_placeholder' => 'e.g. Urban Pro Care, CleanMaster Experts',
                'brand_label' => 'Service Brand / Provider',
                'price_label' => 'Service Fee / Visit Charge (₹)',
                'price_placeholder' => '1499.00',
                'unit_label' => 'Estimated Duration (Hours)',
                'stock_label' => 'Daily Booking Slots Capacity',
                'gst_default' => '18',
                'desc_placeholder' => 'What is included in the package, tools deployed, client requirements, service guarantee...',
                'default_specs' => [
                    ['key' => 'Service Duration', 'value' => '3 - 4 Hours (2 Verified Technicians)'],
                    ['key' => 'Delivery Mode', 'value' => 'Doorstep / Client Location with All Tools'],
                    ['key' => 'Equipment Used', 'value' => 'Industrial Single Disc Machine & Eco-Friendly Chemicals'],
                    ['key' => 'Service Guarantee', 'value' => '15 Days Free Revisit & Re-Work Guarantee'],
                ],
            ],
            'used' => [
                'key' => 'used',
                'label' => 'Pre-Owned & Refurbished',
                'icon' => '♻️',
                'color' => 'from-emerald-500 to-teal-600',
                'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                'catalog_name' => 'Pre-Owned Inventory & Bids',
                'item_singular' => 'Pre-Owned Item',
                'add_btn' => 'Add Pre-Owned Item',
                'specs_label' => 'Item Condition, Warranty & Age',
                'title_placeholder' => 'e.g. iPhone 14 Pro Max 256GB Space Black (Mint 9/10)',
                'brand_placeholder' => 'e.g. Apple, OnePlus, Samsung, Sony, Dell',
                'brand_label' => 'Original Brand',
                'price_label' => 'Asking Price (₹)',
                'price_placeholder' => '72000.00',
                'unit_label' => 'Item Weight (Grams)',
                'stock_label' => 'Available Quantity',
                'gst_default' => '0',
                'desc_placeholder' => 'Condition description, usage duration, cosmetic defects if any, included accessories...',
                'default_specs' => [
                    ['key' => 'Cosmetic Condition', 'value' => 'Mint 9.5/10 (Spotless Screen & Body)'],
                    ['key' => 'Usage / Purchase Year', 'value' => '14 Months Used (Single Owner)'],
                    ['key' => 'Invoice & Box', 'value' => 'Original Bill & Box Included with Charging Cable'],
                    ['key' => 'Testing Status', 'value' => '100% Tested & Fully Functional (Clean IMEI)'],
                ],
            ],
        ];

        $config = $sectors[$type] ?? $sectors['clothing'];
        $config['name'] = $config['name'] ?? ($config['label'] ?? 'Marketplace Store');
        $config['orders_name'] = match($config['key'] ?? '') {
            'food' => 'Kitchen Orders & Live Delivery Stream',
            'services' => 'Service Appointments & Bookings',
            'real_estate' => 'Client Inquiries & Property Visits',
            'digital_services' => 'Project Briefs & Milestones',
            default => 'Customer Orders & Shipments',
        };

        return $config;
    }

    public function getVerticalKey(): string
    {
        if ($this->vendor_type) {
            return match($this->vendor_type) {
                'food' => 'food',
                'services', 'local_services', 'digital_services' => 'services',
                'used' => 'used',
                'machinery', 'b2b', 'real_estate' => 'enquiry',
                default => 'marketplace',
            };
        }
        if ($this->isRestaurant()) {
            return 'food';
        }
        return 'marketplace';
    }

    /**
     * Check if vendor operates as a food restaurant or cloud kitchen.
     */
    public function isRestaurant(): bool
    {
        return $this->vendor_type === 'food' 
            || (bool) ($this->settings?->is_restaurant || $this->products()->where('vertical', 'food')->exists());
    }

    /**
     * Check if restaurant is certified Pure Vegetarian.
     */
    public function isPureVeg(): bool
    {
        return (bool) ($this->settings?->is_pure_veg ?? false);
    }

    /**
     * Active food menu items for this restaurant.
     */
    public function foodMenu(): HasMany
    {
        return $this->hasMany(Product::class)->where('vertical', 'food')->where('status', 'active');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }
        return asset('storage/' . ltrim($this->logo, '/'));
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->banner)) {
            return null;
        }
        if (str_starts_with($this->banner, 'http://') || str_starts_with($this->banner, 'https://')) {
            return $this->banner;
        }
        return asset('storage/' . ltrim($this->banner, '/'));
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->banner_url;
    }

    public function getCityAttribute($value): ?string
    {
        return $value ?: ($this->pickup_address['city'] ?? null);
    }

    public function getStorefrontUrlAttribute(): string
    {
        $requestHost = request()->getHost();
        $isIp = in_array($requestHost, ['127.0.0.1', '::1']) || filter_var($requestHost, FILTER_VALIDATE_IP);

        if (config('marketplace.use_subdomains', true) && !$isIp) {
            $rootDomain = k71_root_domain($requestHost);
            $scheme = (app()->environment('production') || request()->secure()) ? 'https' : 'http';
            $port = request()->getPort();
            $portSuffix = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
            return "{$scheme}://{$this->slug}.{$rootDomain}{$portSuffix}";
        }

        return route('storefront.show', $this->slug);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
}
