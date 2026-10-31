<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class FeastoController extends Controller
{
    /**
     * Feasto 10-Minute Hyperlocal Quick-Commerce Hub (Blinkit Style)
     */
    public function index(Request $request)
    {
        $categories = [
            ['id' => 'all', 'name' => 'All Quick Items', 'icon' => '⚡', 'tag' => 'Instant'],
            ['id' => 'dairy', 'name' => 'Dairy & Milk', 'icon' => '🥛', 'tag' => 'Fresh Daily'],
            ['id' => 'munchies', 'name' => 'Munchies & Chips', 'icon' => '🍿', 'tag' => 'Crispy'],
            ['id' => 'sweets', 'name' => 'Sweets & Desserts', 'icon' => 'Gulab Jamun', 'tag' => 'Rajasthani'],
            ['id' => 'drinks', 'name' => 'Cold Drinks & Juices', 'icon' => '🥤', 'tag' => 'Chilled'],
            ['id' => 'instant', 'name' => 'Instant Noodles & Meals', 'icon' => '🍜', 'tag' => '2 Mins'],
            ['id' => 'bakery', 'name' => 'Breads & Buns', 'icon' => '🍞', 'tag' => 'Baked Fresh'],
            ['id' => 'kitchen', 'name' => 'Kitchen & Cleaning', 'icon' => '🧼', 'tag' => 'Essentials'],
        ];

        // Fetch products in food / grocery verticals
        $products = Product::whereIn('vertical', ['food', 'groceries', 'main'])
            ->where('status', 'active')
            ->latest()
            ->take(30)
            ->get();

        // Seed rich fallback demo items if empty
        if ($products->isEmpty()) {
            $products = collect([
                (object)[
                    'id' => 901,
                    'title' => 'Amul Taaza Homogenised Toned Milk (1L Pouch)',
                    'slug' => 'amul-taaza-toned-milk-1l',
                    'base_price' => 54.00,
                    'compare_at_price' => 56.00,
                    'featured_image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&auto=format&fit=crop&q=80',
                    'brand' => 'Amul',
                    'category_slug' => 'dairy',
                    'rating' => 4.9,
                    'delivery_minutes' => '10-12 mins',
                ],
                (object)[
                    'id' => 902,
                    'title' => 'Bikanervala Special Kaju Katli (Pure Ghee 500g)',
                    'slug' => 'bikanervala-kaju-katli-500g',
                    'base_price' => 480.00,
                    'compare_at_price' => 550.00,
                    'featured_image' => 'https://images.unsplash.com/photo-1599785209707-a456fc1337bb?w=500&auto=format&fit=crop&q=80',
                    'brand' => 'Bikanervala',
                    'category_slug' => 'sweets',
                    'rating' => 4.95,
                    'delivery_minutes' => '12-15 mins',
                ],
                (object)[
                    'id' => 903,
                    'title' => 'Thums Up Charged Cola (750ml Pet Bottle)',
                    'slug' => 'thums-up-charged-cola-750ml',
                    'base_price' => 40.00,
                    'compare_at_price' => 45.00,
                    'featured_image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=500&auto=format&fit=crop&q=80',
                    'brand' => 'Coca-Cola',
                    'category_slug' => 'drinks',
                    'rating' => 4.8,
                    'delivery_minutes' => '8-10 mins',
                ],
                (object)[
                    'id' => 904,
                    'title' => 'Maggi 2-Minute Special Masala Noodles (Pack of 4)',
                    'slug' => 'maggi-special-masala-noodles-4pack',
                    'base_price' => 68.00,
                    'compare_at_price' => 72.00,
                    'featured_image' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?w=500&auto=format&fit=crop&q=80',
                    'brand' => 'Nestle',
                    'category_slug' => 'instant',
                    'rating' => 4.85,
                    'delivery_minutes' => '10-12 mins',
                ],
                (object)[
                    'id' => 905,
                    'title' => 'Lay\'s India\'s Magic Masala Chips (Party Size 115g)',
                    'slug' => 'lays-magic-masala-chips-115g',
                    'base_price' => 50.00,
                    'compare_at_price' => 50.00,
                    'featured_image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=500&auto=format&fit=crop&q=80',
                    'brand' => 'Lay\'s',
                    'category_slug' => 'munchies',
                    'rating' => 4.9,
                    'delivery_minutes' => '9-11 mins',
                ],
                (object)[
                    'id' => 906,
                    'title' => 'Fresh Garlic Naan & Dal Makhani Combo (Hot Cloud Kitchen)',
                    'slug' => 'dal-makhani-garlic-naan-combo',
                    'base_price' => 249.00,
                    'compare_at_price' => 299.00,
                    'featured_image' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=500&auto=format&fit=crop&q=80',
                    'brand' => 'Rajasthan Royal Rasoi',
                    'category_slug' => 'food',
                    'rating' => 4.92,
                    'delivery_minutes' => '15-18 mins',
                ],
            ]);
        }

        return view('feasto.index', compact('categories', 'products'));
    }
}

