<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FreelanceMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            [
                'name' => 'Graphic Design & Branding',
                'slug' => 'graphic-design-branding',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Logo design, brand identity kits, UI/UX mockups, social media kits and packaging.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Website & Web Development',
                'slug' => 'website-web-development',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Custom Laravel apps, WordPress sites, Shopify e-commerce, Next.js and Tailwind web design.',
                'sort_order' => 2,
            ],
            [
                'name' => 'App Development',
                'slug' => 'app-development',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Native and cross-platform iOS and Android apps with Flutter and React Native.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Video Editing & Animation',
                'slug' => 'video-editing-animation',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Viral short-form reels, YouTube edits, 3D motion graphics, and corporate video ads.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Digital Marketing & Ads',
                'slug' => 'digital-marketing-ads',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Meta Facebook & Instagram ads, Google PPC, growth hacking and conversion funnels.',
                'sort_order' => 5,
            ],
            [
                'name' => 'SEO & Content Writing',
                'slug' => 'seo-content-writing',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Technical SEO audits, high-ranking blog posts, copywriting and sales landing page copy.',
                'sort_order' => 6,
            ],
            [
                'name' => 'Business & Consulting',
                'slug' => 'business-consulting',
                'vertical' => 'services',
                'vertical_type' => 'digital_gig',
                'description' => 'Pitch deck design, financial model spreadsheets, business plans and company incorporation.',
                'sort_order' => 7,
            ],
            [
                'name' => 'Marble & Home Craftsmanship',
                'slug' => 'marble-home-craftsmanship',
                'vertical' => 'services',
                'vertical_type' => 'service_booking',
                'description' => 'Italian marble polishing, granite cutting, diamond floor crystallization and stonework.',
                'sort_order' => 8,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cData) {
            $cat = Category::firstOrCreate(
                ['slug' => $cData['slug']],
                array_merge($cData, ['is_active' => true])
            );
            $categories[$cData['slug']] = $cat;
        }

        // 2. Freelance Providers (Vendors)
        $freelancers = [
            [
                'email' => 'priya.designs@k71.in',
                'name' => 'Priya Sharma',
                'shop_name' => 'Apex Design Studio',
                'tagline' => 'Award-Winning UI/UX Designer & Brand Identity Strategist',
                'city' => 'Bengaluru',
                'phone' => '+91 98765 43211',
                'hourly_rate' => 1800,
                'response_time' => '1 Hour',
                'rating' => 4.95,
                'completed_projects' => 84,
                'is_verified_pro' => true,
                'skills' => ['Figma', 'UI/UX Design', 'Brand Identity', 'Logo Design', 'Design Systems', 'Mobile App UI'],
                'languages' => ['English (Fluent)', 'Hindi (Native)'],
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
                'portfolio' => [
                    ['title' => 'FinTech Mobile Banking Experience', 'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80'],
                    ['title' => 'Luxury Perfume Packaging & 3D Brand', 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80'],
                    ['title' => 'SaaS Analytics Dashboard UI', 'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80'],
                ],
            ],
            [
                'email' => 'rohit.tech@k71.in',
                'name' => 'Rohit Verma',
                'shop_name' => 'NextGen Tech Labs',
                'tagline' => 'Senior Full-Stack Laravel & Next.js Engineer (Ex-Swiggy)',
                'city' => 'Hyderabad',
                'phone' => '+91 98765 43212',
                'hourly_rate' => 2500,
                'response_time' => '30 Mins',
                'rating' => 4.98,
                'completed_projects' => 128,
                'is_verified_pro' => true,
                'skills' => ['Laravel 12', 'Vue.js', 'Next.js', 'PostgreSQL', 'TailwindCSS', 'AWS Architecture', 'REST APIs'],
                'languages' => ['English (Fluent)', 'Hindi (Fluent)', 'Telugu (Native)'],
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80',
                'portfolio' => [
                    ['title' => 'High-Frequency B2B Marketplace Portal', 'image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80'],
                    ['title' => 'Real-Time Logistics Fleet Dashboard', 'image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80'],
                ],
            ],
            [
                'email' => 'arjun.motion@k71.in',
                'name' => 'Arjun Nair',
                'shop_name' => 'MotionPulse Creative',
                'tagline' => 'Cinematic Video Editor & 3D Motion Graphics Specialist',
                'city' => 'Mumbai',
                'phone' => '+91 98765 43213',
                'hourly_rate' => 1500,
                'response_time' => '2 Hours',
                'rating' => 4.90,
                'completed_projects' => 96,
                'is_verified_pro' => false,
                'skills' => ['Adobe Premiere Pro', 'After Effects', 'DaVinci Resolve', 'Reels Editing', 'Sound Design', 'Color Grading'],
                'languages' => ['English (Fluent)', 'Hindi (Fluent)', 'Malayalam (Native)'],
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=1200&q=80',
                'portfolio' => [
                    ['title' => 'Viral D2C E-commerce Commercial Reel', 'image' => 'https://images.unsplash.com/photo-1536240478700-b869070f9279?auto=format&fit=crop&w=800&q=80'],
                ],
            ],
            [
                'email' => 'vikram.marble@k71.in',
                'name' => 'Vikram Rathore',
                'shop_name' => 'Artisan Marble & Flooring',
                'tagline' => 'Master Stone Artisan • Italian Marble Diamond Mirror Polishing',
                'city' => 'Udaipur / Jaipur',
                'phone' => '+91 98765 43214',
                'hourly_rate' => 950,
                'response_time' => '1 Hour',
                'rating' => 4.96,
                'completed_projects' => 210,
                'is_verified_pro' => true,
                'skills' => ['Italian Marble Polishing', 'Silicate Crystallization', 'Epoxy Joint Filling', 'Granite Restoration', 'Stone Sealing'],
                'languages' => ['Hindi (Native)', 'English (Conversational)'],
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'portfolio' => [
                    ['title' => '5-Star Hotel Lobby Statuario Marble Restoration', 'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=80'],
                ],
            ],
        ];

        $vendorModels = [];

        foreach ($freelancers as $f) {
            $parts = explode(' ', $f['name']);
            $firstName = $parts[0] ?? $f['name'];
            $lastName = $parts[1] ?? 'Pro';
            $username = Str::slug($f['name']) . rand(10, 99);

            $user = User::firstOrCreate(
                ['email' => $f['email']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'password' => Hash::make('Secret123!'),
                    'email_verified_at' => now(),
                    'phone' => $f['phone'],
                ]
            );

            $slug = Str::slug($f['shop_name']);
            $vendor = Vendor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name' => $f['shop_name'],
                    'slug' => $slug,
                    'vendor_type' => 'service',
                    'tagline' => $f['tagline'],
                    'description' => "Welcome to {$f['shop_name']}! We deliver world-class service with verified milestones and high attention to detail. 100% money-back guarantee if not satisfied.",
                    'city' => $f['city'],
                    'phone' => $f['phone'],
                    'contact_phone' => $f['phone'],
                    'email' => $f['email'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'hourly_rate' => $f['hourly_rate'],
                    'response_time' => $f['response_time'],
                    'rating' => $f['rating'],
                    'completed_projects' => $f['completed_projects'],
                    'is_verified_pro' => $f['is_verified_pro'],
                    'skills_json' => $f['skills'],
                    'languages_json' => $f['languages'],
                    'portfolio_items' => $f['portfolio'],
                    'logo' => $f['avatar'],
                    'banner' => $f['banner'],
                ]
            );

            $vendorModels[$slug] = $vendor;
        }

        // 3. Rich Gigs & Services
        $gigs = [
            [
                'vendor_slug' => 'apex-design-studio',
                'category_slug' => 'graphic-design-branding',
                'title' => 'I will design a modern minimalist luxury logo and complete brand identity system',
                'slug' => 'modern-minimalist-luxury-logo-brand-identity',
                'description' => "Are you looking for a modern, memorable, and timeless brand identity? Welcome to Apex Design Studio! We have helped over 80+ startups and luxury brands stand out in saturated markets.\n\n### What You Will Receive:\n- Custom original logo concepts tailored to your brand personality\n- Comprehensive vector master files (.AI, .EPS, .SVG, .PDF, .PNG transparent)\n- Brand color palettes with HEX, RGB, and CMYK color codes\n- Typography hierarchy pairings and usage guidelines\n- Realistic 3D mockups for business cards, stationary, and signage\n- Full commercial copyright ownership transfer",
                'base_price' => 199900, // ₹1,999 Basic
                'images' => [
                    'https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600132806370-bf17e65e942f?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=1000&q=80',
                ],
                'packages' => [
                    [
                        'tier' => 'basic',
                        'title' => 'Essential Starter Logo',
                        'description' => '2 Original Logo Concepts + High Resolution PNG/JPG + Vector SVG + 2 Free Revisions',
                        'price' => 1999.00,
                        'delivery_days' => 2,
                        'revisions' => 2,
                        'features' => ['2 Logo Concepts', 'Printable Resolution File', 'Vector SVG File', 'Commercial Use License'],
                    ],
                    [
                        'tier' => 'standard',
                        'title' => 'Standard Brand Pack (Recommended)',
                        'description' => '3 Premium Logo Concepts + Editable Vector AI/EPS Source Files + Color Palette & Typography Guide + Social Media Kit',
                        'price' => 4999.00,
                        'delivery_days' => 3,
                        'revisions' => 5,
                        'features' => ['3 Premium Concepts', 'All Editable Source Files (AI, EPS, SVG)', 'Social Media Banners & Avatars', 'Brand Typography & Color Guide', 'Priority VIP Support'],
                    ],
                    [
                        'tier' => 'premium',
                        'title' => 'Complete Corporate Identity Suite',
                        'description' => '5 Top-Tier Concepts + Full 24-Page Brand Style Book + Stationery Kit (Business Cards, Letterhead) + 3D Mockups + Unlimited Revisions',
                        'price' => 9999.00,
                        'delivery_days' => 5,
                        'revisions' => 99,
                        'features' => ['5 Logo Concepts', '24-Page Brand Identity Book', 'Stationery Designs (Cards, Letterheads, Envelopes)', 'Social Media Kit (All Platforms)', 'Unlimited Revisions', 'Copyright Transfer Document'],
                    ],
                ],
                'addons' => [
                    ['name' => 'Fast 24-Hour Express Delivery', 'price' => 999, 'description' => 'Receive your first draft concepts within 24 hours.'],
                    ['name' => 'Copyright Ownership Certificate', 'price' => 499, 'description' => 'Signed legal intellectual property transfer documentation.'],
                    ['name' => 'Stationery Pack Add-on', 'price' => 1499, 'description' => 'Print-ready double-sided business card and corporate letterhead.'],
                ],
                'faqs' => [
                    ['q' => 'What information is needed to start?', 'a' => 'Simply share your business name, tagline, target audience, preferred style (minimalist, bold, vintage), and any references or color preferences.'],
                    ['q' => 'What if I don’t like the initial designs?', 'a' => 'We include free revisions in all packages! We will rework the design until you are completely satisfied with the result.'],
                    ['q' => 'Will I own full commercial rights?', 'a' => 'Yes, 100%! Upon order completion, you own full commercial rights to use the logo anywhere.'],
                ],
            ],
            [
                'vendor_slug' => 'nextgen-tech-labs',
                'category_slug' => 'website-web-development',
                'title' => 'I will build a high-performance modern web application or marketplace with Laravel and Tailwind',
                'slug' => 'modern-web-application-marketplace-laravel-tailwind',
                'description' => "Get a custom, lightning-fast web application built by a seasoned full-stack engineer. Whether you need a SaaS portal, custom booking system, or e-commerce marketplace, we build production-grade code that scales effortlessly.\n\n### Tech Stack & Features:\n- Robust backend powered by Laravel 12 & clean MVC architecture\n- Modern, responsive frontend with TailwindCSS and Alpine.js\n- Secure database design and RESTful API endpoints\n- Payment gateway integration (Razorpay, PhonePe, Stripe)\n- Automated email alerts, notifications, and PDF invoice generation\n- Edge caching and sub-second page loading speed",
                'base_price' => 499900, // ₹4,999 Basic
                'images' => [
                    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1000&q=80',
                ],
                'packages' => [
                    [
                        'tier' => 'basic',
                        'title' => 'Landing Page / MVP Screen',
                        'description' => 'Custom responsive 1-page application with modern animations, contact form, and mobile optimization.',
                        'price' => 4999.00,
                        'delivery_days' => 3,
                        'revisions' => 2,
                        'features' => ['1 Fully Responsive Page', 'Speed & SEO Optimization', 'Contact Form to Email', 'Source Code Access'],
                    ],
                    [
                        'tier' => 'standard',
                        'title' => 'Multi-Page Business Web App',
                        'description' => 'Up to 5 Pages + User Authentication (Login/Register) + Database CRUD + Admin Dashboard.',
                        'price' => 14999.00,
                        'delivery_days' => 7,
                        'revisions' => 4,
                        'features' => ['5 Dynamic Web Pages', 'User Auth (Email & Google)', 'Admin Management Dashboard', 'Payment Gateway Integration', '1 Month Free Maintenance'],
                    ],
                    [
                        'tier' => 'premium',
                        'title' => 'Full-Fledged Marketplace / SaaS Platform',
                        'description' => 'Enterprise web system with vendor management, wallet/escrow payments, real-time notifications, and automated workflows.',
                        'price' => 29999.00,
                        'delivery_days' => 14,
                        'revisions' => 10,
                        'features' => ['Unlimited Database Models & Pages', 'Vendor Multi-Store Architecture', 'S2S Webhook Payment Integration', 'Live Chat / Messaging System', 'Cloud Server Deployment (AWS/VPS)', '3 Months Dedicated Support'],
                    ],
                ],
                'addons' => [
                    ['name' => 'Speed Optimization to 95+ PageSpeed', 'price' => 1999, 'description' => 'Complete asset minification, image WebP conversion, and cache configuration.'],
                    ['name' => 'Cloud VPS Server Setup', 'price' => 2999, 'description' => 'Configure Nginx, PHP-FPM, SSL certificates, and CI/CD deployment on DigitalOcean or AWS.'],
                ],
                'faqs' => [
                    ['q' => 'Can you work with existing codebases?', 'a' => 'Yes! We frequently refactor and modernize existing Laravel, PHP, and JavaScript projects.'],
                    ['q' => 'Do you provide maintenance after launch?', 'a' => 'Yes, our Standard and Premium packages include complimentary post-launch support and bug fixes.'],
                ],
            ],
            [
                'vendor_slug' => 'motionpulse-creative',
                'category_slug' => 'video-editing-animation',
                'title' => 'I will edit viral Instagram reels, TikToks, and YouTube shorts with high retention motion graphics',
                'slug' => 'viral-instagram-reels-youtube-shorts-video-editing',
                'description' => "Transform raw footage into viral, addictive short-form content like Alex Hormozi and Ali Abdaal. High retention editing designed specifically to maximize watch time and viewer conversion.\n\n### What We Provide:\n- Dynamic motion text and kinetic typography with emojis\n- Seamless B-roll stock footage overlays\n- High impact sound effects (whooshes, pops, impacts) and trend-matching music\n- Strategic zoom-ins and jump-cut pacing to eliminate dead air\n- Professional color correction and audio noise reduction",
                'base_price' => 99900, // ₹999 Basic
                'images' => [
                    'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1536240478700-b869070f9279?auto=format&fit=crop&w=1000&q=80',
                ],
                'packages' => [
                    [
                        'tier' => 'basic',
                        'title' => '1 Viral Reel (Up to 60s)',
                        'description' => '1 Short-form video + Dynamic Captions + Sound Effects + 1080x1920 Full HD Export',
                        'price' => 999.00,
                        'delivery_days' => 1,
                        'revisions' => 2,
                        'features' => ['1 Video (60 Seconds)', 'Animated Captions & Emojis', 'Sound Design & Royalty-Free Music', '24h Fast Delivery'],
                    ],
                    [
                        'tier' => 'standard',
                        'title' => 'Batch of 5 Viral Reels (Popular)',
                        'description' => '5 Short-form videos edited to perfection with custom brand font styling and stock B-roll.',
                        'price' => 3999.00,
                        'delivery_days' => 3,
                        'revisions' => 4,
                        'features' => ['5 High-Retention Videos', 'Stock Video & Image B-Roll Insertion', 'Custom Brand Typography & Colors', 'Sound Effects Library', 'Thumbnail Covers Included'],
                    ],
                    [
                        'tier' => 'premium',
                        'title' => 'Monthly Content Domination (15 Reels)',
                        'description' => '15 Viral Videos for a full month of daily posting + Hook optimization coaching + Dedicated Slack/WhatsApp channel.',
                        'price' => 9999.00,
                        'delivery_days' => 7,
                        'revisions' => 10,
                        'features' => ['15 High-Impact Videos', 'Hook Script Polish & Retention Review', 'Trending Audio Research', 'Custom Animated Intro/Outro', 'Full Project Source Files'],
                    ],
                ],
                'addons' => [
                    ['name' => 'Custom Click-Worthy Thumbnail', 'price' => 299, 'description' => '1 Custom designed cover thumbnail with 3D text.'],
                    ['name' => '4K Ultra HD Export', 'price' => 499, 'description' => 'Master render in 4K resolution.'],
                ],
                'faqs' => [
                    ['q' => 'How do I share large video files?', 'a' => 'You can share Google Drive, Dropbox, or WeTransfer links during the requirements submission.'],
                ],
            ],
            [
                'vendor_slug' => 'artisan-marble-flooring',
                'category_slug' => 'marble-home-craftsmanship',
                'title' => 'Professional Italian marble crystallization, diamond disc polishing and stone restoration',
                'slug' => 'italian-marble-crystallization-diamond-polishing-stone-restoration',
                'description' => "Restore the brilliant mirror shine of your Italian marble, Makrana, or granite floors. Using 8-step Italian diamond grit discs and silicate crystallization compounds, we eliminate scratches, stains, and dullness without toxic acid washes.\n\n### Process Breakdown:\n- Floor inspection and deep grout cleaning\n- Diamond grinding using grit pads #100 up to #3000 to eliminate lippage and micro-scratches\n- Epoxy color matching for hairline cracks\n- Silicate crystallization and buffing with 3M pads for lasting gloss\n- Hydrophobic stain protector sealant application",
                'base_price' => 249900, // ₹2,499 Basic
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1000&q=80',
                ],
                'packages' => [
                    [
                        'tier' => 'basic',
                        'title' => 'Room Polish (Up to 250 Sq Ft)',
                        'description' => 'Deep cleaning + 4-disc diamond buffing + High-gloss crystallization for 1 living room or bedroom.',
                        'price' => 2499.00,
                        'delivery_days' => 1,
                        'revisions' => 1,
                        'features' => ['Up to 250 Sq Ft Area', '4-Step Diamond Discs', 'Mirror Crystallization Coat', 'Chemical-Free Odorless Finish'],
                    ],
                    [
                        'tier' => 'standard',
                        'title' => 'Apartment Restoration (Up to 800 Sq Ft)',
                        'description' => 'Complete 2BHK/3BHK floor restoration + Grout filling + 8-step diamond mirror polishing.',
                        'price' => 6999.00,
                        'delivery_days' => 2,
                        'revisions' => 2,
                        'features' => ['Up to 800 Sq Ft Area', '8-Step Italian Diamond Discs', 'Joint Epoxy Filling', 'Hydrophobic Sealant Protection', '6-Month Gloss Warranty'],
                    ],
                    [
                        'tier' => 'premium',
                        'title' => 'Villa & Penthouse Complete Suite (Up to 2000 Sq Ft)',
                        'description' => 'Comprehensive stone restoration for luxury villas, stairs, and countertops with nano-coating.',
                        'price' => 15999.00,
                        'delivery_days' => 4,
                        'revisions' => 3,
                        'features' => ['Up to 2000 Sq Ft Area', 'Countertops & Stairs Included', 'Nano Stain Guard Coating', 'Crack Epoxy Inlay', '1-Year Warranty & Touch-up Session'],
                    ],
                ],
                'addons' => [
                    ['name' => 'Italian Marble Countertop Polishing', 'price' => 999, 'description' => 'Restore 1 kitchen island or bathroom vanity.'],
                ],
                'faqs' => [
                    ['q' => 'Will the polishing create dust in the house?', 'a' => 'No! We use wet grinding machines equipped with industrial vacuum slurry collectors, creating zero airborne dust.'],
                ],
            ],
        ];

        foreach ($gigs as $gigData) {
            $vendor = $vendorModels[$gigData['vendor_slug']] ?? null;
            $category = $categories[$gigData['category_slug']] ?? null;

            if (!$vendor || !$category) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['slug' => $gigData['slug']],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'title' => $gigData['title'],
                    'description' => $gigData['description'],
                    'vertical' => 'services',
                    'listing_type' => 'gig',
                    'base_price' => $gigData['base_price'],
                    'gst_rate' => 18.00,
                    'status' => 'active',
                    'faqs_json' => $gigData['faqs'],
                    'addons_json' => $gigData['addons'],
                    'requirements_prompt' => 'Please provide details about your project, target goals, and attach any relevant files.',
                ]
            );

            // Seed Images
            $product->images()->delete();
            $sort = 0;
            foreach ($gigData['images'] as $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $imgUrl,
                    'alt_text' => $gigData['title'],
                    'sort_order' => $sort++,
                ]);
            }

            // Seed 3-Tier Packages
            $product->gigPackages()->delete();
            foreach ($gigData['packages'] as $pkg) {
                ProductGigPackage::create([
                    'product_id' => $product->id,
                    'tier' => $pkg['tier'],
                    'title' => $pkg['title'],
                    'description' => $pkg['description'],
                    'price' => $pkg['price'],
                    'delivery_days' => $pkg['delivery_days'],
                    'revisions' => $pkg['revisions'],
                    'features_json' => $pkg['features'],
                ]);
            }
        }
    }
}
