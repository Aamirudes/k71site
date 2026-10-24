<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Short;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealShoppableShortsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shorts = [
            [
                'title' => 'Pure Katan Silk Banarasi Saree Weaving & Draping Hack',
                'slug' => 'pure-katan-silk-banarasi-saree-draping',
                'description' => 'Real handloom Kadwa pure Katan silk saree with gold zari border. Watch the drape test and pallu pleating guide.',
                'vendor_slug' => 'kavita-couture-banarasi-atelier',
                'category_slug' => 'clothing-fashion-accessories',
                'product_ids' => [3],
                'video_url' => '/videos/shorts/short1.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80',
                'duration' => 28,
                'views' => 34200,
                'likes' => 2890,
                'shares' => 540,
                'clicks' => 1240,
                'trending_score' => 195.5,
                'is_featured' => true,
                'is_boosted' => true,
            ],
            [
                'title' => 'Dum Biryani Clay Handi Seal Breaking | 4-Hour Slow Cook',
                'slug' => 'dum-biryani-clay-handi-seal-breaking',
                'description' => 'Unsealing our signature Awadhi Dum Mutton Biryani handi. Fresh aroma of pure saffron, long-grain basmati, and tender marinated lamb.',
                'vendor_slug' => 'nawabi-dastarkhwan-kitchen',
                'category_slug' => 'biryani-rice',
                'product_ids' => [15, 16],
                'video_url' => '/videos/shorts/short2.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=600&q=80',
                'duration' => 35,
                'views' => 52100,
                'likes' => 4780,
                'shares' => 890,
                'clicks' => 2180,
                'trending_score' => 280.0,
                'is_featured' => true,
                'is_boosted' => true,
            ],
            [
                'title' => 'Handcrafted Goodyear-Welted Oxford Shoes | Full Grain Leather',
                'slug' => 'goodyear-welted-oxford-shoes-craft',
                'description' => 'Close-up craftsmanship inspection of bespoke leather Oxford formal shoes. Hand-burnished finish with Goodyear welt stitching.',
                'vendor_slug' => 'kavita-couture-banarasi-atelier',
                'category_slug' => 'clothing-fashion-accessories',
                'product_ids' => [4],
                'video_url' => '/videos/shorts/short3.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=600&q=80',
                'duration' => 24,
                'views' => 19400,
                'likes' => 1420,
                'shares' => 320,
                'clicks' => 890,
                'trending_score' => 140.0,
                'is_featured' => false,
                'is_boosted' => false,
            ],
            [
                'title' => 'Kumkumadi Tailam Miracle Night Glow Routine with Pure Saffron',
                'slug' => 'kumkumadi-tailam-night-glow-routine',
                'description' => 'How to apply 100% pure Ayurvedic Kumkumadi Tailam for glowing skin and even skin tone. Made with authentic Kashmiri saffron.',
                'vendor_slug' => 'kavita-couture-banarasi-atelier',
                'category_slug' => 'beauty-personal-care-health-wellness',
                'product_ids' => [13],
                'video_url' => '/videos/shorts/short4.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=600&q=80',
                'duration' => 26,
                'views' => 41800,
                'likes' => 3610,
                'shares' => 740,
                'clicks' => 1650,
                'trending_score' => 230.5,
                'is_featured' => true,
                'is_boosted' => true,
            ],
            [
                'title' => 'Pro Grade-1 English Willow Cricket Bat Ping & Knocking Test',
                'slug' => 'grade-1-english-willow-cricket-bat-test',
                'description' => 'Listen to the sound and rebound ping on this custom Grade-1 English Willow bat. Hand-pressed with 9 straight grains.',
                'vendor_slug' => 'kavita-couture-banarasi-atelier',
                'category_slug' => 'baby-toys-hobbies-books-sports',
                'product_ids' => [14],
                'video_url' => '/videos/shorts/short5.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1624526267942-ab0ff8a3e972?auto=format&fit=crop&w=600&q=80',
                'duration' => 30,
                'views' => 67500,
                'likes' => 5920,
                'shares' => 1240,
                'clicks' => 3100,
                'trending_score' => 310.0,
                'is_featured' => true,
                'is_boosted' => true,
            ],
            [
                'title' => 'Diamond-Cut Makrana Pure White Marble Slab Mirror Polish',
                'slug' => 'makrana-white-marble-slab-polish-test',
                'description' => 'Direct from Kishangarh marble mandi: Diamond gang-saw sliced Makrana White Marble slabs undergoing mirror finish crystallization.',
                'vendor_slug' => 'royal-makrana-marbles-slabs',
                'category_slug' => 'home-kitchen-furniture-living',
                'product_ids' => [7, 8],
                'video_url' => '/videos/shorts/short6.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80',
                'duration' => 32,
                'views' => 89300,
                'likes' => 8110,
                'shares' => 1890,
                'clicks' => 4500,
                'trending_score' => 415.0,
                'is_featured' => true,
                'is_boosted' => true,
            ],
            [
                'title' => 'Wood-Fired Margherita Di Bufala Pizza Fresh Out of Oven',
                'slug' => 'wood-fired-margherita-di-bufala-pizza',
                'description' => 'Hand-stretched sourdough pizza with San Marzano tomato sauce, fresh mozzarella di bufala, and extra virgin olive oil.',
                'vendor_slug' => 'ovenfresh-artisan-pizza-co',
                'category_slug' => 'pizzas-italian-bistro',
                'product_ids' => [27, 29],
                'video_url' => '/videos/shorts/short7.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?auto=format&fit=crop&w=600&q=80',
                'duration' => 22,
                'views' => 28900,
                'likes' => 2650,
                'shares' => 490,
                'clicks' => 1120,
                'trending_score' => 175.0,
                'is_featured' => false,
                'is_boosted' => false,
            ],
            [
                'title' => 'Double Smash Truffle Cheeseburger Melt & Sizzle',
                'slug' => 'double-smash-truffle-cheeseburger-sizzle',
                'description' => 'Watch the crust develop on our twin smash patties, double cheddar melt, and house truffle mayo on toasted brioche.',
                'vendor_slug' => 'the-burger-club-crisp-grills',
                'category_slug' => 'burgers-fast-food',
                'product_ids' => [31, 34],
                'video_url' => '/videos/shorts/short8.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80',
                'duration' => 25,
                'views' => 39400,
                'likes' => 3420,
                'shares' => 680,
                'clicks' => 1580,
                'trending_score' => 210.0,
                'is_featured' => true,
                'is_boosted' => false,
            ],
            [
                'title' => 'Crispy Ghee Mysore Masala Dosa with Authentic Degree Filter Coffee',
                'slug' => 'crispy-ghee-mysore-masala-dosa-coffee',
                'description' => 'Crisp golden crepe smeared with fiery red garlic chutney, potato masala, and a dollop of pure desi ghee. Served piping hot.',
                'vendor_slug' => 'dosa-kingdom-south-indian-tiffin',
                'category_slug' => 'south-indian',
                'product_ids' => [35, 37],
                'video_url' => '/videos/shorts/short9.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1668236543090-82eba5ee5976?auto=format&fit=crop&w=600&q=80',
                'duration' => 20,
                'views' => 31500,
                'likes' => 2870,
                'shares' => 510,
                'clicks' => 1290,
                'trending_score' => 185.0,
                'is_featured' => false,
                'is_boosted' => false,
            ],
            [
                'title' => 'Warm Belgian Chocolate Liege Waffle & Dark Chocolate Sizzler',
                'slug' => 'belgian-chocolate-liege-waffle-sizzler',
                'description' => 'Caramelized pearl sugar Belgian waffle loaded with warm dark chocolate ganache and vanilla gelato. The ultimate dessert craving.',
                'vendor_slug' => 'sweet-nirvana-bakery-desserts',
                'category_slug' => 'desserts-cakes-bakery',
                'product_ids' => [41, 43],
                'video_url' => '/videos/shorts/short10.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=600&q=80',
                'duration' => 27,
                'views' => 44200,
                'likes' => 4120,
                'shares' => 820,
                'clicks' => 1980,
                'trending_score' => 245.0,
                'is_featured' => true,
                'is_boosted' => true,
            ],
            [
                'title' => '5W-40 Fully Synthetic Engine Oil Drain & Refill Test',
                'slug' => 'synthetic-5w40-engine-oil-drain-refill',
                'description' => 'Protect your engine under extreme Indian summer heat. Watch the viscosity shear test and anti-friction flow demonstration.',
                'vendor_slug' => 'kavita-couture-banarasi-atelier',
                'category_slug' => 'automotive-vehicles-transport',
                'product_ids' => [12],
                'video_url' => '/videos/shorts/short11.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=600&q=80',
                'duration' => 34,
                'views' => 22600,
                'likes' => 1840,
                'shares' => 390,
                'clicks' => 960,
                'trending_score' => 155.0,
                'is_featured' => false,
                'is_boosted' => false,
            ],
            [
                'title' => 'Amritsari 24-Hr Slow Simmered Dal Makhani & Chur-Chur Kulcha',
                'slug' => 'amritsari-dal-makhani-chur-chur-kulcha',
                'description' => 'Simmered overnight on slow charcoal coals with white butter and pure cream. Pair it with crisp layered chur-chur aaloo kulcha.',
                'vendor_slug' => 'punjab-grill-pure-veg-rasoi',
                'category_slug' => 'north-indian',
                'product_ids' => [23, 25],
                'video_url' => '/videos/shorts/short12.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=600&q=80',
                'duration' => 29,
                'views' => 37800,
                'likes' => 3210,
                'shares' => 610,
                'clicks' => 1490,
                'trending_score' => 215.0,
                'is_featured' => true,
                'is_boosted' => false,
            ],
        ];

        $fallbackVendor = Vendor::first();
        $fallbackCategory = Category::first();

        foreach ($shorts as $data) {
            $vendor = Vendor::where('slug', $data['vendor_slug'])->first() ?: $fallbackVendor;
            $category = Category::where('slug', $data['category_slug'])->first() ?: $fallbackCategory;

            if (!$vendor) continue;

            $short = Short::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $category?->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'video_url' => $data['video_url'],
                    'thumb_url' => $data['thumb_url'],
                    'status' => 'published',
                    'is_featured' => $data['is_featured'],
                    'is_boosted' => $data['is_boosted'],
                    'views' => $data['views'],
                    'likes' => $data['likes'],
                    'shares' => $data['shares'],
                    'clicks' => $data['clicks'],
                    'duration' => $data['duration'],
                    'trending_score' => $data['trending_score'],
                    'ratio' => '9:16',
                ]
            );

            // Sync tagged real products
            $validProductIds = Product::whereIn('id', $data['product_ids'])->pluck('id')->toArray();
            if (!empty($validProductIds)) {
                $short->products()->sync($validProductIds);
            }
        }
    }
}
