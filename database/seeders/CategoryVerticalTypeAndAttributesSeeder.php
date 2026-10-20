<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryVerticalTypeAndAttributesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define vertical_type classification map for root categories & their descendants
        $verticalTypeMap = [
            'fashion_matrix' => [
                'men', 'women', 'kids', 'accessories', 'footwear', 'handloom-sarees',
                'clothing-fashion-accessories', 'women-ethnic', 'men-casual', 'western-wear'
            ],
            'food_portion' => [
                'food-dining', 'artisan-biryanis', 'food-north-indian', 'food-south-indian',
                'food-biryani-rice', 'food-fast-food-snacks', 'food-desserts-sweets',
                'food-beverages-juices', 'food-bakery-cakes'
            ],
            'service_booking' => [
                'professional-services', 'services-home-repairs', 'services-electricians-plumbers',
                'services-appliance-repair', 'services-cleaning-pest', 'services-tutors-lessons',
                'on-demand-local-on-site-services', 'services-home-repairs-electrician',
                'services-cleaning-deep-sanitization', 'services-salon-grooming',
                'services-tailoring-alterations', 'services-photography-events'
            ],
            'b2b_machinery' => [
                'bulk-custom-enquiries', 'enquiry-wholesale-fabrics', 'enquiry-marble-granite',
                'enquiry-industrial-hardware', 'enquiry-corporate-gifts', 'enquiry-electronic-components',
                'heavy-industrial-machinery-construction', 'enquiry-wholesale-fabrics-apparel',
                'enquiry-marble-granite-tiles', 'enquiry-industrial-hardware-tools',
                'enquiry-packaging-materials', 'enquiry-custom-corporate-gifts'
            ],
            'digital_gig' => [
                'digital-services-freelancing-gigs', 'digital-services-freelancing-gigs-programming-tech',
                'digital-services-freelancing-gigs-graphics-design', 'digital-services-freelancing-gigs-digital-marketing',
                'digital-services-freelancing-gigs-writing-translation', 'digital-services-freelancing-gigs-business-professional',
                'digital-services-freelancing-gigs-audio-music-voiceover', 'services-it-digital-work'
            ],
            'classified_asset' => [
                'pre-owned-used-goods', 'used-mobiles-tablets', 'used-laptops-electronics',
                'used-vehicles-bikes', 'used-furniture', 'used-vintage-collectibles', 'used-appliances',
                'pre-owned-watches', 'automotive-vehicles-transport', 'pre-loved-used-goods-thrift',
                'real-estate-properties-high-ticket-assets', 'used-used-mobiles-tablets',
                'used-pre-owned-laptops-pcs', 'used-second-hand-furniture', 'used-pre-owned-bikes-scooters',
                'used-home-appliances', 'used-sports-fitness'
            ],
            'standard_retail' => [
                'electronics-appliances-it', 'home-living-furniture-hardware',
                'beauty-personal-care-health-wellness', 'groceries-daily-essentials-fmcg',
                'baby-toys-hobbies-books-sports', 'shoppable-shorts-live-commerce'
            ],
        ];

        // Update category vertical_types
        foreach ($verticalTypeMap as $verticalType => $slugs) {
            foreach ($slugs as $slug) {
                $category = Category::where('slug', $slug)->first();
                if ($category) {
                    $category->update(['vertical_type' => $verticalType]);
                    // Update children recursively
                    $this->updateDescendants($category, $verticalType);
                }
            }
        }

        // Also ensure any leftover categories with vertical string match the vertical_type
        Category::where('vertical', 'food')->where('vertical_type', 'standard_retail')->update(['vertical_type' => 'food_portion']);
        Category::where('vertical', 'used')->where('vertical_type', 'standard_retail')->update(['vertical_type' => 'classified_asset']);
        Category::where('vertical', 'enquiry')->where('vertical_type', 'standard_retail')->update(['vertical_type' => 'b2b_machinery']);

        // 2. Blueprint Attributes definitions per vertical type
        $blueprints = [
            'fashion_matrix' => [
                [
                    'attribute_code' => 'color',
                    'label' => 'Color Swatch',
                    'input_type' => 'color_swatch',
                    'is_variant_driver' => true,
                    'is_filterable' => true,
                    'is_required' => true,
                    'options_json' => [
                        ['name' => 'Jet Black', 'hex' => '#111827'],
                        ['name' => 'Navy Blue', 'hex' => '#1e3a8a'],
                        ['name' => 'Ruby Red', 'hex' => '#dc2626'],
                        ['name' => 'Emerald Green', 'hex' => '#059669'],
                        ['name' => 'Ivory White', 'hex' => '#f9fafb'],
                        ['name' => 'Royal Maroon', 'hex' => '#881337'],
                        ['name' => 'Mustard Gold', 'hex' => '#d97706'],
                        ['name' => 'Slate Grey', 'hex' => '#64748b'],
                    ],
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'size',
                    'label' => 'Apparel / Footwear Size',
                    'input_type' => 'radio_pill',
                    'is_variant_driver' => true,
                    'is_filterable' => true,
                    'is_required' => true,
                    'options_json' => ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'],
                    'sort_order' => 2,
                ],
                [
                    'attribute_code' => 'fabric',
                    'label' => 'Fabric / Material',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['100% Pure Cotton', 'Mulmul Cotton', 'Chanderi Silk', 'Banarasi Silk', 'Georgette', 'Rayon', 'Linen', 'Denim', 'Velvet'],
                    'sort_order' => 3,
                ],
                [
                    'attribute_code' => 'fit',
                    'label' => 'Fit Type',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['Regular Fit', 'Slim Fit', 'Relaxed / Oversized', 'Comfort Fit'],
                    'sort_order' => 4,
                ],
            ],

            'food_portion' => [
                [
                    'attribute_code' => 'portion',
                    'label' => 'Portion / Serving Size',
                    'input_type' => 'radio_pill',
                    'is_variant_driver' => true,
                    'is_filterable' => false,
                    'is_required' => true,
                    'options_json' => ['1 Plate (Single)', '2 Plates (Couple)', 'Half Handi', 'Full Handi', 'Family Pack (4 Pax)'],
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'dietary',
                    'label' => 'Dietary Classification',
                    'input_type' => 'radio_pill',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => true,
                    'options_json' => ['Pure Veg', 'Non-Veg', 'Contains Egg', 'Jain Friendly', 'Vegan'],
                    'sort_order' => 2,
                ],
                [
                    'attribute_code' => 'spice_level',
                    'label' => 'Spice Level',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['Mild / Zero Spice', 'Medium Spicy (Balanced)', 'Authentic Desi Spicy', 'Extra Hot Fire'],
                    'sort_order' => 3,
                ],
            ],

            'b2b_machinery' => [
                [
                    'attribute_code' => 'motor_hp',
                    'label' => 'Motor Power (HP / kW)',
                    'input_type' => 'text',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => null,
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'cutting_thickness',
                    'label' => 'Max Cutting / Working Thickness (mm)',
                    'input_type' => 'text',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => null,
                    'sort_order' => 2,
                ],
                [
                    'attribute_code' => 'voltage',
                    'label' => 'Operating Voltage',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['220V Single Phase', '415V Three Phase (Industrial)', 'Dual Voltage 220V/440V', 'Solar DC Operated'],
                    'sort_order' => 3,
                ],
                [
                    'attribute_code' => 'automation_grade',
                    'label' => 'Automation Grade',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['Fully Automatic (CNC / PLC)', 'Semi-Automatic', 'Manual Heavy Duty'],
                    'sort_order' => 4,
                ],
                [
                    'attribute_code' => 'warranty_period',
                    'label' => 'Warranty Coverage',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => false,
                    'is_required' => false,
                    'options_json' => ['1 Year Manufacturer Warranty', '2 Years Comprehensive', '3 Years Heavy Machine Cover'],
                    'sort_order' => 5,
                ],
            ],

            'digital_gig' => [
                [
                    'attribute_code' => 'deliverable_format',
                    'label' => 'Delivered File Formats',
                    'input_type' => 'multiselect',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['Figma / Design Source', 'Vector AI / SVG / EPS', 'High-Res PDF (300 DPI)', 'Full Code Repository (Git/ZIP)', 'MP4 4K Video Render'],
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'commercial_use',
                    'label' => 'Commercial Rights',
                    'input_type' => 'boolean',
                    'is_variant_driver' => false,
                    'is_filterable' => false,
                    'is_required' => false,
                    'options_json' => null,
                    'sort_order' => 2,
                ],
            ],

            'service_booking' => [
                [
                    'attribute_code' => 'duration_minutes',
                    'label' => 'Typical Service Duration',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['30 Minutes', '60 Minutes (1 Hour)', '90 Minutes', '2 - 3 Hours', 'Full Day (6-8 Hours)'],
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'service_mode',
                    'label' => 'Service Delivery Mode',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => true,
                    'options_json' => ['Doorstep / Customer Location', 'In-Store / Provider Studio', 'Remote Video / Consultation'],
                    'sort_order' => 2,
                ],
                [
                    'attribute_code' => 'rework_guarantee',
                    'label' => 'Free Rework Guarantee',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => false,
                    'is_required' => false,
                    'options_json' => ['7 Days Free Revisit', '15 Days Service Warranty', '30 Days Complete Guarantee', 'No Revisit Cover'],
                    'sort_order' => 3,
                ],
            ],

            'classified_asset' => [
                [
                    'attribute_code' => 'condition',
                    'label' => 'Item Condition',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => true,
                    'options_json' => [
                        'Brand New (Box Sealed / Unused)',
                        'Like New (Mint 10/10 Condition)',
                        'Gently Used (Minor Signs of Wear)',
                        'Used (Fully Working)',
                        'For Parts / Needs Repair'
                    ],
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'purchase_year',
                    'label' => 'Year of Purchase / Manufacturing',
                    'input_type' => 'number',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => null,
                    'sort_order' => 2,
                ],
                [
                    'attribute_code' => 'usage_metric',
                    'label' => 'Usage / KM Driven / Battery Cycles',
                    'input_type' => 'text',
                    'is_variant_driver' => false,
                    'is_filterable' => false,
                    'is_required' => false,
                    'options_json' => null,
                    'sort_order' => 3,
                ],
                [
                    'attribute_code' => 'original_bill_box',
                    'label' => 'Original Invoice & Box Available',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['Original Bill & Box Included', 'Bill Available Only', 'Box Available Only', 'Item Only (No Box/Bill)'],
                    'sort_order' => 4,
                ],
            ],

            'standard_retail' => [
                [
                    'attribute_code' => 'model_number',
                    'label' => 'Model / Part Number',
                    'input_type' => 'text',
                    'is_variant_driver' => false,
                    'is_filterable' => false,
                    'is_required' => false,
                    'options_json' => null,
                    'sort_order' => 1,
                ],
                [
                    'attribute_code' => 'warranty',
                    'label' => 'Warranty',
                    'input_type' => 'select',
                    'is_variant_driver' => false,
                    'is_filterable' => true,
                    'is_required' => false,
                    'options_json' => ['No Warranty', '6 Months Manufacturer', '1 Year National Warranty', '2 Years Extended'],
                    'sort_order' => 2,
                ],
            ],
        ];

        // Seed CategoryAttribute blueprints for each vertical's categories
        foreach ($blueprints as $verticalType => $attributes) {
            $matchingCategories = Category::where('vertical_type', $verticalType)->get();

            foreach ($matchingCategories as $category) {
                foreach ($attributes as $attr) {
                    CategoryAttribute::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'attribute_code' => $attr['attribute_code'],
                        ],
                        [
                            'label' => $attr['label'],
                            'input_type' => $attr['input_type'],
                            'is_variant_driver' => $attr['is_variant_driver'],
                            'is_filterable' => $attr['is_filterable'],
                            'is_required' => $attr['is_required'],
                            'options_json' => $attr['options_json'],
                            'sort_order' => $attr['sort_order'],
                        ]
                    );
                }
            }
        }
    }

    private function updateDescendants(Category $category, string $verticalType): void
    {
        $children = Category::where('parent_id', $category->id)->get();
        foreach ($children as $child) {
            $child->update(['vertical_type' => $verticalType]);
            $this->updateDescendants($child, $verticalType);
        }
    }
}
