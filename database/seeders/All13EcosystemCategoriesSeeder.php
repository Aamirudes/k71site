<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class All13EcosystemCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            // 1. 🎥 Shoppable Shorts & Live Commerce
            [
                'name' => 'Shoppable Shorts & Live Commerce',
                'slug' => 'shoppable-shorts-live-commerce',
                'vertical' => 'shorts',
                'icon' => '🎥',
                'description' => 'Discovery feed layer across all physical/digital goods and services with live shoppable tags.',
                'subcategories' => [
                    'Fashion & Outfit Inspo' => 'OOTD, Try-On Hauls, Styling Guides',
                    'Tech & Gadget Unboxing' => 'Hands-on reviews, Quick specs & teardowns',
                    'Auto & Speed Shorts' => 'Vehicle walkthroughs, Exhaust sounds, Restoration clips',
                    'Machinery & Industrial Demos' => 'Live cutting/welding demos, Factory tours, Tool tests',
                    'Home & Interior DIY' => 'Transformation videos, Decor ideas & Room makeovers',
                    'Beauty & Glam Tutorials' => 'Makeup, Skincare routines & product swatches',
                    'Food & Recipe Quick-Clips' => 'Snack reviews, Local chef reels & Dum cooking',
                    'Service Showcases' => 'Before/After cleaning, Design time-lapses, Skilled labor demos',
                    'Unusual & High-Ticket Showcases' => 'Supercars, Luxury watches, Estates & Heavy machinery',
                ]
            ],

            // 2. 👗 Clothing, Fashion & Accessories
            [
                'name' => 'Clothing, Fashion & Accessories',
                'slug' => 'clothing-fashion-accessories',
                'vertical' => 'marketplace',
                'icon' => '👗',
                'description' => 'Retail, wholesale & pre-loved apparel, footwear, watches and luxury jewelry.',
                'subcategories' => [
                    'Men’s Fashion' => 'Topwear, Bottomwear, Ethnic & Traditional, Activewear, Innerwear, Sleepwear, Suits & Blazers',
                    'Women’s Fashion' => 'Western Wear, Ethnic & Fusion Wear, Sarees & Lehengas, Lingerie & Lounge, Maternity Wear, Party Wear',
                    'Kids & Baby Apparel' => 'Boys, Girls, Newborn / Toddler, School Uniforms, Baby Footwear',
                    'Footwear' => 'Casual Shoes, Formal Shoes, Sneakers, Sandals & Floaters, Sports Shoes, Heels & Boots, Traditional Footwear',
                    'Watches & Fine Jewelry' => 'Smartwatches, Analog/Luxury Watches, Precious Jewelry (Gold/Diamond), Fashion/Imitation Jewelry',
                    'Bags & Luggage' => 'Backpacks, Handbags & Totes, Trolleys & Suitcases, Wallets & Clutches, Laptop Bags',
                    'Fashion Accessories' => 'Sunglasses & Frames, Belts, Ties & Cufflinks, Scarves, Hats & Caps',
                ]
            ],

            // 3. 📱 Electronics, Appliances & IT
            [
                'name' => 'Electronics, Appliances & IT',
                'slug' => 'electronics-appliances-it',
                'vertical' => 'marketplace',
                'icon' => '📱',
                'description' => 'Retail, refurbished & bulk B2B electronics, gaming rigs, appliances and commercial displays.',
                'subcategories' => [
                    'Mobiles & Accessories' => 'Smartphones, Feature Phones, Refurbished Mobiles, Cases, Chargers, Power Banks, Screen Protectors',
                    'Computers & IT' => 'Laptops, Desktop PCs, Gaming Rigs, Components (GPUs, CPUs, RAM), Peripherals, Networking Gear',
                    'Consumer Electronics' => 'Audio (Earbuds, Headphones, Bluetooth Speakers), Cameras & Drones, Smart Home Devices, Wearables',
                    'TV & Entertainment' => 'Smart TVs, Projectors, Home Theater Systems, Streaming Devices, Gaming Consoles & Video Games',
                    'Home & Kitchen Appliances' => 'Refrigerators, Washing Machines, Air Conditioners, Microwave Ovens, Water Purifiers, Vacuum Cleaners, Chimneys',
                    'Commercial Electronics' => 'POS Machines, CCTV & Security Systems, Digital Signage, Commercial Displays, Printers & Copiers',
                ]
            ],

            // 4. 🛠️ Heavy Industrial, Machinery & Construction
            [
                'name' => 'Heavy Industrial, Machinery & Construction',
                'slug' => 'heavy-industrial-machinery-construction',
                'vertical' => 'enquiry',
                'icon' => '🛠️',
                'description' => 'IndiaMART-style RFQ engine: Marble machinery, construction equipment, CNC machines, steel & raw materials.',
                'subcategories' => [
                    'Stone & Marble Machinery' => 'Marble/Granite Cutting Machines, Edge Polishing Machines, CNC Engraving Machines, Block Cutters, Bridge Saws',
                    'Construction Machinery' => 'Excavators, Concrete Mixers, Cranes, Earthmoving Equipment, Scaffolding & Formwork, Road Rollers',
                    'Manufacturing & Factory Equipment' => 'CNC Machines, Lathe Machines, Hydraulic Presses, Laser Cutting Machines, Packaging Machines, Molding Equipment',
                    'Industrial Raw Materials' => 'Steel & Metals, Chemical Compounds, Polymers & Plastics, Rubber, Textile Fabrics & Yarns',
                    'Electrical & Power Supplies' => 'Industrial Transformers, Solar Panels & Inverters, Diesel Generators, Electric Motors, Switchgear & Panels',
                    'Tools & Hardware' => 'Power Tools, Hand Tools, Cutting Tools, Safety & PPE Gear, Fasteners, Measuring Instruments',
                ]
            ],

            // 5. 🚗 Automotive, Vehicles & Transport
            [
                'name' => 'Automotive, Vehicles & Transport',
                'slug' => 'automotive-vehicles-transport',
                'vertical' => 'marketplace',
                'icon' => '🚗',
                'description' => 'Cars, four-wheelers, motorcycles, commercial fleets, genuine spare parts, and towing/detailing services.',
                'subcategories' => [
                    'Cars & Four-Wheelers' => 'Hatchbacks, Sedans, SUVs, Electric Vehicles (EVs), Luxury Cars, Commercial Vans',
                    'Bikes & Two-Wheelers' => 'Motorcycles, Scooters, Electric Scooters, Superbikes, Vintage Bikes, Cycles & E-Bikes',
                    'Commercial & Heavy Vehicles' => 'Trucks, Buses, Auto Rickshaws, Tractors & Farm Vehicles, Trailers',
                    'Spare Parts & Accessories' => 'Engine Components, Tyres & Alloys, Car Electronics, Helmets, Riding Gear, Vehicle Oils & Fluids',
                    'Vehicle Services' => 'Used Car Inspection Services, Vehicle Detailing, Transport & Towing, Auto Insurance Leads',
                ]
            ],

            // 6. 💼 Digital Services, Freelancing & Gigs
            [
                'name' => 'Digital Services, Freelancing & Gigs',
                'slug' => 'digital-services-freelancing-gigs',
                'vertical' => 'services',
                'icon' => '💼',
                'description' => 'Fiverr-style tiered milestone gigs: Programming, AI/ML, UI/UX, 3D modeling, copywriting, and marketing.',
                'subcategories' => [
                    'Programming & Tech' => 'Web Development, Mobile App Development, AI/Machine Learning, Software Engineering, DevOps & Cloud, Cybersecurity',
                    'Graphics & Design' => 'Logo Design, UI/UX Design, 3D Modeling, Brand Identity, Video Editing, Motion Graphics, CAD/Architectural Design',
                    'Digital Marketing' => 'SEO, Social Media Management, PPC Advertising, Influencer Marketing, Content Marketing',
                    'Writing & Translation' => 'Content Writing, Copywriting, Technical Writing, Translation, Resume Writing',
                    'Business & Professional' => 'Legal Consulting, Accounting & Bookkeeping, Virtual Assistants, Business Plans, HR & Recruiting',
                    'Audio, Music & Voiceover' => 'Voiceovers, Audio Editing, Music Production, Jingles, Mixing & Mastering',
                ]
            ],

            // 7. 🧹 On-Demand Local & On-Site Services
            [
                'name' => 'On-Demand Local & On-Site Services',
                'slug' => 'on-demand-local-on-site-services',
                'vertical' => 'services',
                'icon' => '🧹',
                'description' => 'Verified home technicians, slot booking: Electricians, plumbers, deep cleaning, salon & event catering.',
                'subcategories' => [
                    'Home Maintenance & Repair' => 'Electricians, Plumbers, Carpenters, AC Repair & Servicing, Appliance Technicians',
                    'Cleaning & Pest Control' => 'Deep Home Cleaning, Sofa/Carpet Cleaning, Commercial Cleaning, Pest Control Services',
                    'Construction & Interior Work' => 'Interior Designers, Civil Contractors, Painters, Flooring & Marble Installers, Electricians',
                    'Events & Catering' => 'Event Planners, Wedding Photographers, Caterers, DJs & Performers, Decorators',
                    'Personal Care & Wellness' => 'At-home Salon & Spa, Personal Trainers, Yoga Instructors, Physiotherapy',
                ]
            ],

            // 8. 🔄 Pre-Loved, Used Goods & Thrift
            [
                'name' => 'Pre-Loved, Used Goods & Thrift',
                'slug' => 'pre-loved-used-goods-thrift',
                'vertical' => 'used',
                'icon' => '🔄',
                'description' => 'OLX/C2C classifieds with direct negotiation, inspected condition grades, and verified peer-to-peer offers.',
                'subcategories' => [
                    'Second-Hand Electronics' => 'Used Mobiles, Pre-Owned Laptops, Second-Hand TVs, Used Cameras',
                    'Used Furniture & Decor' => 'Used Sofas, Bed Frames, Dining Sets, Office Chairs & Desks',
                    'Used Vehicles' => 'Pre-Owned Cars, Second-Hand Bikes, Used Cycles',
                    'Thrift & Vintage Fashion' => 'Pre-Loved Designer Bags, Vintage Clothing, Antique Watches',
                    'Used Books & Hobbies' => 'Books & Textbooks, Musical Instruments, Fitness & Gym Equipment, Board Games',
                    'Scrap & Industrial Recyclables' => 'E-waste, Scrap Metal, Used Pallets & Drums',
                ]
            ],

            // 9. 🏡 Real Estate, Properties & High-Ticket Assets
            [
                'name' => 'Real Estate, Properties & High-Ticket Assets',
                'slug' => 'real-estate-properties-high-ticket-assets',
                'vertical' => 'enquiry',
                'icon' => '🏡',
                'description' => 'High-ticket brokerage & lead capture: Residential villas, office showrooms, warehouses, and industrial sheds.',
                'subcategories' => [
                    'Residential Properties' => 'Flats & Apartments, Independent Houses/Villas, Residential Plots/Land',
                    'Commercial Properties' => 'Office Spaces, Shops & Showrooms, Warehouses & Godowns, Commercial Land',
                    'Property Rentals' => 'Apartments for Rent, PG & Co-Living, Office Space Rentals',
                    'Industrial Real Estate' => 'Factory Land, Industrial Sheds, Manufacturing Units',
                ]
            ],

            // 10. 🏠 Home, Living, Furniture & Hardware
            [
                'name' => 'Home, Living, Furniture & Hardware',
                'slug' => 'home-living-furniture-hardware',
                'vertical' => 'marketplace',
                'icon' => '🏠',
                'description' => 'Retail and B2B furniture, ambient home lighting, granite slabs, faucets, and sanitaryware.',
                'subcategories' => [
                    'Furniture' => 'Sofas, Beds & Mattresses, Dining Sets, Wardrobes, Office Furniture, Outdoor Furniture',
                    'Home Decor' => 'Lighting & Lamps, Wall Art & Mirrors, Rugs & Carpets, Curtains, Clocks',
                    'Kitchenware & Dining' => 'Cookware, Tableware, Kitchen Storage, Bakeware, Cutlery',
                    'Building & Renovation Materials' => 'Tiles, Marble & Granite Slabs, Sanitaryware & Faucets, Paints & Wallpapers, Cement & Bricks',
                ]
            ],

            // 11. 💄 Beauty, Personal Care, Health & Wellness
            [
                'name' => 'Beauty, Personal Care, Health & Wellness',
                'slug' => 'beauty-personal-care-health-wellness',
                'vertical' => 'marketplace',
                'icon' => '💄',
                'description' => 'Dermatologist skincare, luxury fragrances, grooming trimmers, and certified health supplements.',
                'subcategories' => [
                    'Skincare & Haircare' => 'Cleansers, Serums, Sunscreen, Shampoos, Hair Oils',
                    'Makeup & Cosmetics' => 'Face, Eye, Lip, Nail Products, Makeup Kits',
                    'Fragrances' => 'Perfumes, Deodorants, Body Mists, Attar',
                    'Personal Care Appliances' => 'Hair Dryers, Straighteners, Trimmers & Shavers',
                    'Health & Wellness' => 'Health Supplements, Protein Powders, Fitness Equipment, Ayurvedic Products, Medical Supplies',
                ]
            ],

            // 12. 🛒 Groceries, Daily Essentials & FMCG
            [
                'name' => 'Groceries, Daily Essentials & FMCG',
                'slug' => 'groceries-daily-essentials-fmcg',
                'vertical' => 'food',
                'icon' => '🛒',
                'description' => 'Instant delivery: Fresh farm produce, organic staples, packaged beverages, and household cleaning supplies.',
                'subcategories' => [
                    'Fresh Produce' => 'Fruits, Vegetables, Organic Farm Produce',
                    'Staples & Cooking Essentials' => 'Rice, Wheat & Atta, Pulses/Dals, Cooking Oils, Spices',
                    'Snacks & Beverages' => 'Tea & Coffee, Packaged Foods, Dairy Products, Cold Drinks & Juices',
                    'Household Supplies' => 'Detergents, Cleaning Liquids, Paper Products, Trash Bags',
                ]
            ],

            // 13. 🧸 Baby, Toys, Hobbies, Books & Sports
            [
                'name' => 'Baby, Toys, Hobbies, Books & Sports',
                'slug' => 'baby-toys-hobbies-books-sports',
                'vertical' => 'marketplace',
                'icon' => '🧸',
                'description' => 'Educational toys, bestselling books, exam guides, infant strollers, and outdoor sports equipment.',
                'subcategories' => [
                    'Baby Care & Gear' => 'Diapers, Strollers, Baby Food, Nursing Gear',
                    'Toys & Games' => 'Educational Toys, Action Figures, Board Games, Remote Control Toys',
                    'Books & Media' => 'Fiction & Non-Fiction, School/College Textbooks, Competitive Exam Books, Audiobooks/E-Books',
                    'Sports & Fitness' => 'Cricket/Soccer/Racket Gear, Gym Equipment, Cycles, Camping & Outdoor Gear',
                ]
            ],
        ];

        $sortOrder = 10;
        foreach ($sectors as $s) {
            $parent = Category::updateOrCreate(
                ['slug' => $s['slug']],
                [
                    'name' => $s['name'],
                    'vertical' => $s['vertical'],
                    'icon' => $s['icon'],
                    'description' => $s['description'],
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                    'seo_title' => Str::limit($s['name'] . ' — K71', 60, ''),
                    'seo_description' => Str::limit($s['description'], 150, ''),
                ]
            );

            $subOrder = 1;
            foreach ($s['subcategories'] as $subName => $subDesc) {
                $subSlug = Str::slug($parent->slug . '-' . $subName);
                Category::updateOrCreate(
                    ['slug' => $subSlug],
                    [
                        'parent_id' => $parent->id,
                        'name' => $subName,
                        'vertical' => $s['vertical'],
                        'icon' => $s['icon'],
                        'description' => $subDesc,
                        'sort_order' => $subOrder++,
                        'is_active' => true,
                        'seo_title' => Str::limit($subName . ' — ' . $parent->name, 60, ''),
                        'seo_description' => Str::limit($subDesc, 150, ''),
                    ]
                );
            }
        }
    }
}
