<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class FeastoController extends Controller
{
    /**
     * Feasto 10-Minute Hyperlocal Quick-Commerce Hub (Blinkit Style Architecture)
     */
    public function index(Request $request)
    {
        $categories = [
            ['id' => 'all', 'name' => 'All Quick Items', 'icon' => '⚡', 'tag' => 'Instant', 'count' => 36],
            ['id' => 'dairy', 'name' => 'Dairy, Bread & Eggs', 'icon' => '🥛', 'tag' => 'Fresh Daily', 'count' => 12],
            ['id' => 'veggies', 'name' => 'Vegetables & Fruits', 'icon' => '🍅', 'tag' => 'Farm Fresh', 'count' => 14],
            ['id' => 'munchies', 'name' => 'Munchies & Chips', 'icon' => '🍿', 'tag' => 'Crispy', 'count' => 10],
            ['id' => 'drinks', 'name' => 'Cold Drinks & Juices', 'icon' => '🥤', 'tag' => 'Chilled', 'count' => 8],
            ['id' => 'instant', 'name' => 'Instant Noodles & Meals', 'icon' => '🍜', 'tag' => '2 Mins', 'count' => 9],
            ['id' => 'sweets', 'name' => 'Sweets & Mithai', 'icon' => '🍬', 'tag' => 'Artisan Pure Ghee', 'count' => 7],
            ['id' => 'tea_coffee', 'name' => 'Tea, Coffee & Drinks', 'icon' => '☕', 'tag' => 'Daily Sip', 'count' => 6],
            ['id' => 'cleaning', 'name' => 'Cleaning & Home Essentials', 'icon' => '🧼', 'tag' => 'Essentials', 'count' => 8],
            ['id' => 'personal_care', 'name' => 'Personal Care & Hygiene', 'icon' => '🧴', 'tag' => 'Wellness', 'count' => 6],
        ];

        // Fetch real active products
        $dbProducts = Product::whereIn('vertical', ['food', 'groceries', 'main'])
            ->where('status', 'active')
            ->latest()
            ->take(60)
            ->get();

        // Standard Blinkit catalog items with verified pricing, unit weights, and 10-min delivery
        $curatedItems = collect([
            // Dairy & Milk
            (object)[
                'id' => 901,
                'title' => 'Amul Taaza Homogenised Toned Milk',
                'weight' => '1 L Pouch',
                'slug' => 'amul-taaza-toned-milk-1l',
                'base_price' => 54.00,
                'compare_at_price' => 56.00,
                'featured_image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Amul',
                'category_slug' => 'dairy',
                'rating' => 4.9,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 907,
                'title' => 'Mother Dairy Classic Curd / Dahi',
                'weight' => '400 g Tub',
                'slug' => 'mother-dairy-classic-curd-400g',
                'base_price' => 35.00,
                'compare_at_price' => 38.00,
                'featured_image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Mother Dairy',
                'category_slug' => 'dairy',
                'rating' => 4.88,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 908,
                'title' => 'Harvest Gold 100% Whole Wheat Bread',
                'weight' => '400 g Pack',
                'slug' => 'harvest-gold-whole-wheat-bread-400g',
                'base_price' => 50.00,
                'compare_at_price' => 55.00,
                'featured_image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Harvest Gold',
                'category_slug' => 'dairy',
                'rating' => 4.85,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 909,
                'title' => 'Farm Fresh White Table Eggs (Pack of 6)',
                'weight' => '6 Pieces Tray',
                'slug' => 'farm-fresh-eggs-6pack',
                'base_price' => 48.00,
                'compare_at_price' => 54.00,
                'featured_image' => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=500&auto=format&fit=crop&q=80',
                'brand' => 'FreshFarm',
                'category_slug' => 'dairy',
                'rating' => 4.91,
                'delivery_minutes' => '10 MINS',
            ],

            // Veggies & Fruits
            (object)[
                'id' => 910,
                'title' => 'Fresh Hybrid Red Tomatoes',
                'weight' => '500 g Net',
                'slug' => 'fresh-hybrid-red-tomatoes-500g',
                'base_price' => 24.00,
                'compare_at_price' => 30.00,
                'featured_image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Kisan Fresh',
                'category_slug' => 'veggies',
                'rating' => 4.82,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 911,
                'title' => 'Fresh Jyoti Potatoes (Aloo)',
                'weight' => '1 kg Pouch',
                'slug' => 'fresh-jyoti-potatoes-1kg',
                'base_price' => 32.00,
                'compare_at_price' => 40.00,
                'featured_image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Kisan Fresh',
                'category_slug' => 'veggies',
                'rating' => 4.89,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 912,
                'title' => 'Fresh Green Coriander Leaves (Dhaniya)',
                'weight' => '100 g Bunch',
                'slug' => 'fresh-coriander-dhaniya-100g',
                'base_price' => 12.00,
                'compare_at_price' => 15.00,
                'featured_image' => 'https://images.unsplash.com/photo-1590452366127-6f8d387607a9?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Kisan Fresh',
                'category_slug' => 'veggies',
                'rating' => 4.95,
                'delivery_minutes' => '10 MINS',
            ],

            // Munchies & Chips
            (object)[
                'id' => 905,
                'title' => 'Lay\'s India\'s Magic Masala Potato Chips',
                'weight' => '115 g Party Pack',
                'slug' => 'lays-magic-masala-chips-115g',
                'base_price' => 50.00,
                'compare_at_price' => 50.00,
                'featured_image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Lay\'s',
                'category_slug' => 'munchies',
                'rating' => 4.9,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 913,
                'title' => 'Kurkure Masala Munch Crunchy Crisps',
                'weight' => '82 g Pack',
                'slug' => 'kurkure-masala-munch-82g',
                'base_price' => 20.00,
                'compare_at_price' => 20.00,
                'featured_image' => 'https://images.unsplash.com/photo-1582293041079-7814c2f12063?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Kurkure',
                'category_slug' => 'munchies',
                'rating' => 4.87,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 914,
                'title' => 'Haldiram\'s Nagpur Aloo Bhujia Namkeen',
                'weight' => '400 g Pack',
                'slug' => 'haldirams-aloo-bhujia-400g',
                'base_price' => 110.00,
                'compare_at_price' => 125.00,
                'featured_image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Haldiram\'s',
                'category_slug' => 'munchies',
                'rating' => 4.96,
                'delivery_minutes' => '10 MINS',
            ],

            // Drinks & Juices
            (object)[
                'id' => 903,
                'title' => 'Thums Up Charged Carbonated Cola',
                'weight' => '750 ml Bottle',
                'slug' => 'thums-up-charged-cola-750ml',
                'base_price' => 40.00,
                'compare_at_price' => 45.00,
                'featured_image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Coca-Cola',
                'category_slug' => 'drinks',
                'rating' => 4.85,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 915,
                'title' => 'Real Fruit Power Alphonso Mango Nectar',
                'weight' => '1 L Tetra Pack',
                'slug' => 'real-fruit-power-alphonso-mango-1l',
                'base_price' => 115.00,
                'compare_at_price' => 130.00,
                'featured_image' => 'https://images.unsplash.com/photo-1546173159-315724a31696?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Dabur Real',
                'category_slug' => 'drinks',
                'rating' => 4.9,
                'delivery_minutes' => '10 MINS',
            ],

            // Instant Food & Noodles
            (object)[
                'id' => 904,
                'title' => 'Maggi 2-Minute Masala Instant Noodles',
                'weight' => '280 g (Pack of 4)',
                'slug' => 'maggi-masala-noodles-4pack',
                'base_price' => 68.00,
                'compare_at_price' => 72.00,
                'featured_image' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Nestle Maggi',
                'category_slug' => 'instant',
                'rating' => 4.94,
                'delivery_minutes' => '10 MINS',
            ],

            // Sweets & Mithai
            (object)[
                'id' => 902,
                'title' => 'Bikanervala Special Kaju Katli (Pure Desi Ghee)',
                'weight' => '500 g Gift Box',
                'slug' => 'bikanervala-kaju-katli-500g',
                'base_price' => 480.00,
                'compare_at_price' => 550.00,
                'featured_image' => 'https://images.unsplash.com/photo-1599785209707-a456fc1337bb?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Bikanervala',
                'category_slug' => 'sweets',
                'rating' => 4.98,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 916,
                'title' => 'Haldiram\'s Gulab Jamun (Soft & Juicy In Syrup)',
                'weight' => '1 kg Tin',
                'slug' => 'haldirams-gulab-jamun-1kg',
                'base_price' => 240.00,
                'compare_at_price' => 275.00,
                'featured_image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Haldiram\'s',
                'category_slug' => 'sweets',
                'rating' => 4.93,
                'delivery_minutes' => '10 MINS',
            ],

            // Tea & Coffee
            (object)[
                'id' => 917,
                'title' => 'Tata Tea Premium Desh Ki Chai',
                'weight' => '500 g Pouch',
                'slug' => 'tata-tea-premium-500g',
                'base_price' => 260.00,
                'compare_at_price' => 290.00,
                'featured_image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Tata Tea',
                'category_slug' => 'tea_coffee',
                'rating' => 4.9,
                'delivery_minutes' => '10 MINS',
            ],

            // Cleaning & Home
            (object)[
                'id' => 918,
                'title' => 'Vim Dishwash Gel Lemon Flavor',
                'weight' => '750 ml Bottle',
                'slug' => 'vim-dishwash-gel-lemon-750ml',
                'base_price' => 175.00,
                'compare_at_price' => 199.00,
                'featured_image' => 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Vim',
                'category_slug' => 'cleaning',
                'rating' => 4.89,
                'delivery_minutes' => '10 MINS',
            ],
            (object)[
                'id' => 919,
                'title' => 'Surf Excel Quick Wash Detergent Powder',
                'weight' => '1 kg Pouch',
                'slug' => 'surf-excel-quick-wash-1kg',
                'base_price' => 140.00,
                'compare_at_price' => 155.00,
                'featured_image' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=500&auto=format&fit=crop&q=80',
                'brand' => 'Surf Excel',
                'category_slug' => 'cleaning',
                'rating' => 4.92,
                'delivery_minutes' => '10 MINS',
            ],
        ]);

        // Merge DB products into catalog if any exist
        foreach ($dbProducts as $dp) {
            $curatedItems->push((object)[
                'id' => $dp->id,
                'title' => $dp->title,
                'weight' => $dp->weight_grams ? ($dp->weight_grams >= 1000 ? round($dp->weight_grams / 1000, 1) . ' kg' : $dp->weight_grams . ' g') : 'Standard Unit',
                'slug' => $dp->slug,
                'base_price' => (float) ($dp->base_price / 100),
                'compare_at_price' => $dp->compare_at_price ? (float) ($dp->compare_at_price / 100) : (float) (($dp->base_price * 1.1) / 100),
                'featured_image' => $dp->primary_image_url ?: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&auto=format&fit=crop&q=80',
                'brand' => $dp->vendor?->shop_name ?? 'K71 Verified',
                'category_slug' => $dp->vertical === 'food' ? 'sweets' : 'dairy',
                'rating' => 4.88,
                'delivery_minutes' => '10 MINS',
            ]);
        }

        $allProducts = $curatedItems->unique('id');

        return view('feasto.index', compact('categories', 'allProducts'));
    }
}
