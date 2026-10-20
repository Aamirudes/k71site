-- K71 Universal Dynamic Variant, Attribute & UX Engine Schema Migration
-- Database Engine: MySQL / MariaDB (Hostinger & Production Compatible)

-- 1. Classify Category Vertical Types
ALTER TABLE `categories` 
ADD COLUMN IF NOT EXISTS `vertical_type` ENUM(
  'fashion_matrix',       -- Apparel, Footwear, Jewelry
  'food_portion',         -- Food, Cloud Kitchen, Bakery
  'service_booking',      -- On-demand trades, Cleaning, Repairs
  'b2b_machinery',        -- Machinery, Industrial, B2B RFQ
  'digital_gig',          -- Digital Gigs, Freelance
  'classified_asset',     -- Pre-Owned, Vehicles, Real Estate
  'standard_retail'       -- Groceries, Electronics, Books, Beauty
) NOT NULL DEFAULT 'standard_retail' AFTER `name`;

-- 2. Dynamic Attribute Blueprints
CREATE TABLE IF NOT EXISTS `category_attributes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `attribute_code` VARCHAR(64) NOT NULL,
  `label` VARCHAR(128) NOT NULL,
  `input_type` ENUM(
    'text', 'number', 'select', 'multiselect', 
    'color_swatch', 'image_swatch', 'boolean', 'radio_pill'
  ) NOT NULL,
  `is_variant_driver` TINYINT(1) DEFAULT 0,  -- 1 generates SKU/price combinations; 0 is pure static spec
  `is_filterable` TINYINT(1) DEFAULT 0,
  `is_required` TINYINT(1) DEFAULT 0,
  `options_json` JSON NULL,                 -- e.g., ["S", "M", "L", "XL"] or [{"name": "Red", "hex": "#FF0000"}]
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Base Products Table Updates
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `has_variants` TINYINT(1) DEFAULT 0 AFTER `base_price`,
ADD COLUMN IF NOT EXISTS `specifications_json` JSON NULL AFTER `has_variants`,
ADD COLUMN IF NOT EXISTS `b2b_tiers_json` JSON NULL AFTER `specifications_json`,
ADD COLUMN IF NOT EXISTS `service_config_json` JSON NULL AFTER `b2b_tiers_json`;

-- 4. Variant Combination Matrix (Child SKUs)
ALTER TABLE `product_variants`
ADD COLUMN IF NOT EXISTS `attributes_hash` JSON NULL AFTER `sku`,
ADD COLUMN IF NOT EXISTS `price` DECIMAL(12,2) NULL AFTER `attributes_hash`,
ADD COLUMN IF NOT EXISTS `compare_at_price` DECIMAL(12,2) NULL AFTER `price`,
ADD COLUMN IF NOT EXISTS `stock_quantity` INT NOT NULL DEFAULT 0 AFTER `compare_at_price`,
ADD COLUMN IF NOT EXISTS `image_url` VARCHAR(255) NULL AFTER `stock_quantity`,
ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'inactive') DEFAULT 'active' AFTER `image_url`;

-- 5. Product Modifiers & Add-ons (Food Portions & Customizations)
CREATE TABLE IF NOT EXISTS `product_modifiers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `group_name` VARCHAR(100) NOT NULL,      -- e.g., "Add-ons", "Portion Upgrades"
  `modifier_name` VARCHAR(100) NOT NULL,   -- e.g., "Extra Bhatura", "Special Chutney"
  `price_delta` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `is_required` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tiered Service / Freelance Packages (Fiverr Model)
CREATE TABLE IF NOT EXISTS `product_gig_packages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `tier` ENUM('basic', 'standard', 'premium') NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `delivery_days` SMALLINT UNSIGNED NOT NULL,
  `revisions` INT NOT NULL DEFAULT 0,
  `price` DECIMAL(12,2) NOT NULL,
  `features_json` JSON NOT NULL,            -- e.g., {"source_file": true, "commercial_rights": true}
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_product_tier` (`product_id`, `tier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Universal Cart Table Upgrades
ALTER TABLE `cart_items`
ADD COLUMN IF NOT EXISTS `gig_package_id` INT UNSIGNED NULL AFTER `variant_id`,
ADD COLUMN IF NOT EXISTS `selected_modifiers_json` JSON NULL AFTER `gig_package_id`,
ADD COLUMN IF NOT EXISTS `booking_date_time` DATETIME NULL AFTER `selected_modifiers_json`;

-- 8. Universal Order Items Upgrades (Frozen snapshot of selected attributes / modifiers)
ALTER TABLE `order_items`
ADD COLUMN IF NOT EXISTS `meta_json` JSON NULL AFTER `snapshot`,
ADD COLUMN IF NOT EXISTS `gig_package_id` INT UNSIGNED NULL AFTER `meta_json`,
ADD COLUMN IF NOT EXISTS `selected_modifiers_json` JSON NULL AFTER `gig_package_id`,
ADD COLUMN IF NOT EXISTS `booking_date_time` DATETIME NULL AFTER `selected_modifiers_json`;

-- 8. Relax cart items unique constraint to allow multi-modifier and gig items
ALTER TABLE `cart_items` DROP INDEX `cart_items_cart_id_variant_id_unique`;

