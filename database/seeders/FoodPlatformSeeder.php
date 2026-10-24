<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductModifier;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FoodPlatformSeeder extends Seeder
{
    /**
     * Seed comprehensive, production-ready restaurants, cloud kitchens, and gourmet menus.
     */
    public function run(): void
    {
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);

        // 1. Ensure Food Categories Exist
        $foodCats = [
            'food-dining' => ['name' => 'Food & Dining', 'icon' => '🍕'],
            'food-biryani-rice' => ['name' => 'Dum Biryani & Rice Bowls', 'icon' => '🍲'],
            'food-pizzas-italian' => ['name' => 'Pizzas & Italian Bistro', 'icon' => '🍕'],
            'food-burgers-fast-food' => ['name' => 'Burgers & Fast Food', 'icon' => '🍔'],
            'food-north-indian' => ['name' => 'North Indian & Mughlai Curries', 'icon' => '🍛'],
            'food-south-indian' => ['name' => 'South Indian Tiffin & Dosas', 'icon' => '🥞'],
            'food-pan-asian-chinese' => ['name' => 'Pan-Asian & Chinese Wok', 'icon' => '🍜'],
            'food-bakery-desserts' => ['name' => 'Desserts, Cakes & Bakery', 'icon' => '🍰'],
            'food-beverages-juices' => ['name' => 'Chai, Coffee & Shakes', 'icon' => '☕'],
        ];

        $categoryModels = [];
        $sort = 1;
        foreach ($foodCats as $slug => $cData) {
            $categoryModels[$slug] = Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $cData['name'],
                    'vertical' => 'food',
                    'vertical_type' => 'food_portion',
                    'icon' => $cData['icon'],
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );
        }

        // 2. Define 8 Authentic Restaurants and Cloud Kitchens
        $restaurantsData = [
            [
                'user' => ['name' => 'Chef Zubair Qureshi', 'email' => 'nawabi@k71food.test', 'phone' => '9820011111'],
                'vendor' => [
                    'shop_name' => 'Nawabi Dastarkhwan Kitchen',
                    'slug' => 'nawabi-dastarkhwan',
                    'vendor_type' => 'food',
                    'description' => 'Authentic royal Awadhi and Mughlai delicacies. Slow-cooked in sealed clay handis with secret hand-pounded spices.',
                    'logo' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302001',
                    'address' => 'Shop 14, Heritage Square, MI Road, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => false,
                    'cuisine_types' => ['Awadhi', 'Mughlai', 'Dum Biryani', 'Kebabs'],
                    'opening_time' => '11:00',
                    'closing_time' => '23:30',
                    'avg_prep_time_mins' => 25,
                    'min_food_order_amount' => 15000,
                    'food_delivery_fee' => 4000,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Slow-Cooked Awadhi Dum Mutton Biryani (Handi)',
                        'cat' => 'food-biryani-rice',
                        'price' => 44900,
                        'compare' => 49900,
                        'desc' => 'Tender goat meat marinated in yoghurt and royal spices, layered with aged basmati rice and saffron milk, slow-cooked in a sealed clay pot.',
                        'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Biryani',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['Regular Handi (500g)' => 44900, 'Jumbo Royal Handi (1kg)' => 84900],
                        'modifiers' => ['Extra Burani Raita' => 40, 'Mutton Shami Kebab (2 Pcs)' => 150, 'Extra Salan Gravy' => 30],
                    ],
                    [
                        'title' => 'Galouti Kebab Melt Platter (4 Pcs)',
                        'cat' => 'food-north-indian',
                        'price' => 38000,
                        'compare' => 42000,
                        'desc' => 'Finely ground minced meat kebabs with raw papaya and 16 aromatic spices that literally melt on your tongue. Served with mini ulte tawe ke parathe.',
                        'image' => 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Mughlai',
                        'spice' => 'Mild',
                        'serves' => '1-2 People',
                        'portions' => ['Standard Platter (4 Pcs)' => 38000, 'Party Platter (8 Pcs)' => 72000],
                        'modifiers' => ['Extra Ulte Tawe Ka Paratha' => 45, 'Mint Coriander Dip' => 25],
                    ],
                    [
                        'title' => 'Murgh Shahi Korma Desi Ghee Gravy',
                        'cat' => 'food-north-indian',
                        'price' => 36000,
                        'compare' => 39900,
                        'desc' => 'Chicken simmered gently in rich cashew paste, fried brown onions, cardamom, and saffron. Pairs heavenly with Khamiri Roti.',
                        'image' => 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'North Indian',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['Half Portion' => 36000, 'Full Portion' => 64000],
                        'modifiers' => ['Butter Khamiri Roti (2 Pcs)' => 60, 'Garlic Naan' => 70],
                    ],
                    [
                        'title' => 'Royal Shahi Tukda with Rabdi & Pistachio',
                        'cat' => 'food-bakery-desserts',
                        'price' => 18000,
                        'compare' => 22000,
                        'desc' => 'Ghee-fried golden bread steeped in fragrant saffron sugar syrup, layered with thick simmered rabdi and roasted slivered almonds.',
                        'image' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Dessert',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Single Serve' => 18000, 'Double Delight' => 32000],
                        'modifiers' => ['Extra Thick Rabdi' => 50, 'Silver Vark Topping' => 20],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'Mirza Farooq Baig', 'email' => 'royalbiryani@k71food.test', 'phone' => '9820022222'],
                'vendor' => [
                    'shop_name' => 'Royal Biryani & Thali Kitchen',
                    'slug' => 'royal-biryani-kitchen',
                    'vendor_type' => 'food',
                    'description' => 'Original Hyderabadi Kachhi Dum Biryani and grand festival feast thalis prepared with pure ghee and secret masalas.',
                    'logo' => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302001',
                    'address' => '12 Station Road, Sindhi Camp, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => false,
                    'cuisine_types' => ['Biryani', 'Hyderabadi', 'Thalis', 'South Indian'],
                    'opening_time' => '11:00',
                    'closing_time' => '23:59',
                    'avg_prep_time_mins' => 20,
                    'min_food_order_amount' => 12000,
                    'food_delivery_fee' => 3500,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Hyderabadi Dum Biryani Handi',
                        'cat' => 'food-biryani-rice',
                        'price' => 32000,
                        'compare' => 36000,
                        'desc' => 'Long grain Daawat basmati rice slow cooked in dough-sealed handi with fresh chicken, caramelized onions, mint, and pure ghee.',
                        'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Biryani',
                        'spice' => 'Medium',
                        'serves' => '1-2 People',
                        'portions' => ['Regular (1 Pc Chicken + Egg)' => 32000, 'Special (2 Pcs Chicken + Egg)' => 42000],
                        'modifiers' => ['Extra Boiled Egg' => 25, 'Spicy Mirchi Ka Salan' => 35, 'Cucumber Mint Raita' => 30],
                    ],
                    [
                        'title' => 'Nawabi Grand Feast Maharaja Thali',
                        'cat' => 'food-north-indian',
                        'price' => 49900,
                        'compare' => 59900,
                        'desc' => 'Grand banquet platter featuring Paneer Butter Masala, Dal Makhani, Dum Biryani, 2 Butter Naan, Gulab Jamun, Papad, and Raita.',
                        'image' => 'https://images.unsplash.com/photo-1610057099443-fde8c4d50f91?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'North Indian',
                        'spice' => 'Medium',
                        'serves' => '1-2 People',
                        'portions' => ['Standard Maharaja Thali' => 49900],
                        'modifiers' => ['Sweet Lassi Glass' => 60, 'Extra Butter Naan' => 50],
                    ],
                    [
                        'title' => 'Crispy Chicken 65 Curry Leaf Toss',
                        'cat' => 'food-north-indian',
                        'price' => 28000,
                        'compare' => 32000,
                        'desc' => 'South Indian style spiced batter fried boneless chicken bites tempered with green chillies, curry leaves, and ginger garlic.',
                        'image' => 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'South Indian',
                        'spice' => 'Extra Spicy',
                        'serves' => '2 People',
                        'portions' => ['250 Grams' => 28000, '500 Grams' => 52000],
                        'modifiers' => ['Extra Lemon & Onion Rings' => 15, 'Mint Dip' => 20],
                    ],
                    [
                        'title' => 'Hyderabadi Double Ka Meetha',
                        'cat' => 'food-bakery-desserts',
                        'price' => 14000,
                        'compare' => 18000,
                        'desc' => 'Crispy fried bread baked with sweetened thickened condensed milk, mawa, cardamom, and golden cashew nuts.',
                        'image' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Dessert',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Standard Bowl' => 14000],
                        'modifiers' => ['Extra Cashew Nut Topping' => 30],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'Sardar Harpreet Singh', 'email' => 'punjabgrill@k71food.test', 'phone' => '9820033333'],
                'vendor' => [
                    'shop_name' => 'Punjab Grill & Pure Veg Rasoi',
                    'slug' => 'punjab-grill-pure-veg',
                    'vendor_type' => 'food',
                    'description' => '100% Pure Vegetarian Dhaba & Kitchen. Rich buttery Dal Makhani, tandoori paneer tikkas, and stuffed Amritsari kulchas.',
                    'logo' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302015',
                    'address' => 'Plot 45, Tonk Road, Near Gopalpura Flyover, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => true,
                    'cuisine_types' => ['North Indian', 'Punjabi', 'Pure Veg', 'Tandoor'],
                    'opening_time' => '10:30',
                    'closing_time' => '23:30',
                    'avg_prep_time_mins' => 20,
                    'min_food_order_amount' => 10000,
                    'food_delivery_fee' => 3000,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Amritsari 24-Hr Slow Simmered Dal Makhani',
                        'cat' => 'food-north-indian',
                        'price' => 26000,
                        'compare' => 29900,
                        'desc' => 'Whole black lentils and kidney beans simmered overnight with tomatoes, white butter, and fresh cream. The legendary recipe.',
                        'image' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'North Indian',
                        'spice' => 'Mild',
                        'serves' => '2 People',
                        'portions' => ['Regular Bowl (400ml)' => 26000, 'Family Pot (800ml)' => 48000],
                        'modifiers' => ['Extra White Butter Dollop' => 30, 'Garlic Butter Naan (2 Pcs)' => 80],
                    ],
                    [
                        'title' => 'Paneer Butter Masala Velvet Handi',
                        'cat' => 'food-north-indian',
                        'price' => 29000,
                        'compare' => 34000,
                        'desc' => 'Fresh Malai paneer cubes simmered in a luscious tomato, cashew and honey gravy flavored with kasoori methi.',
                        'image' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'North Indian',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['Medium Handi' => 29000, 'Large Handi' => 52000],
                        'modifiers' => ['Lachha Paratha (2 Pcs)' => 60, 'Jeera Rice Bowl' => 110],
                    ],
                    [
                        'title' => 'Amritsari Chur-Chur Aloo Pyaaz Kulcha Platter',
                        'cat' => 'food-north-indian',
                        'price' => 22000,
                        'compare' => 26000,
                        'desc' => 'Crisp flaky tandoor baked kulcha crushed with butter, served with Pindi Chhole, tangy imli chutney, and pickled onions.',
                        'image' => 'https://images.unsplash.com/photo-1626074353765-517a681e40be?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Punjabi',
                        'spice' => 'Medium',
                        'serves' => '1 Person',
                        'portions' => ['Double Kulcha Meal' => 22000],
                        'modifiers' => ['Sweet Creamy Punjabi Lassi' => 70, 'Extra Pindi Chhole Bowl' => 60],
                    ],
                    [
                        'title' => 'Tandoori Saffron Paneer Tikka (6 Pcs)',
                        'cat' => 'food-north-indian',
                        'price' => 28000,
                        'compare' => 32000,
                        'desc' => 'Diced cottage cheese marinated in hung curd, yellow mustard, ajwain and saffron, char-grilled on clay skewers.',
                        'image' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'North Indian',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['Standard Skewer (6 Pcs)' => 28000],
                        'modifiers' => ['Extra Mint Yogurt Dip' => 25],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'Chef Marco D’Souza', 'email' => 'ovenfresh@k71food.test', 'phone' => '9820044444'],
                'vendor' => [
                    'shop_name' => 'OvenFresh Artisan Pizza Co.',
                    'slug' => 'ovenfresh-artisan-pizza',
                    'vendor_type' => 'food',
                    'description' => '48-hour fermented sourdough Neapolitan pizzas baked at 450°C. San Marzano tomatoes, fresh buffalo mozzarella, and aromatic basil.',
                    'logo' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302004',
                    'address' => 'G-3, Urban Square Mall, C-Scheme, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => false,
                    'cuisine_types' => ['Pizza', 'Italian', 'Pasta', 'Continental'],
                    'opening_time' => '11:30',
                    'closing_time' => '01:00',
                    'avg_prep_time_mins' => 22,
                    'min_food_order_amount' => 18000,
                    'food_delivery_fee' => 4000,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Classic Margherita Di Bufala Pizza (11-inch)',
                        'cat' => 'food-pizzas-italian',
                        'price' => 38000,
                        'compare' => 44000,
                        'desc' => 'Crushed San Marzano Italian plum tomatoes, fresh Fior Di Latte mozzarella pearls, extra virgin olive oil, and fresh garden basil.',
                        'image' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Pizza',
                        'spice' => 'Mild',
                        'serves' => '2 People',
                        'portions' => ['11-inch Neapolitan' => 38000, '14-inch Party Size' => 59000],
                        'modifiers' => ['Cheese Burst Crust' => 90, 'Extra Buffalo Mozzarella' => 70, 'Jalapeno & Olive Topping' => 45],
                    ],
                    [
                        'title' => 'Fire-Roasted Peri-Peri Chicken Pizza',
                        'cat' => 'food-pizzas-italian',
                        'price' => 46000,
                        'compare' => 52000,
                        'desc' => 'Spicy grilled chicken chunks, roasted red bell peppers, smoked paprika, caramelized onions, and gooey mozzarella.',
                        'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Pizza',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['11-inch' => 46000, '14-inch' => 69000],
                        'modifiers' => ['Cheese Burst Crust' => 90, 'Extra Smoked Bacon' => 80, 'Chilli Flakes & Garlic Dip' => 30],
                    ],
                    [
                        'title' => 'Truffle Butter Cheesy Pull-Apart Garlic Bread',
                        'cat' => 'food-pizzas-italian',
                        'price' => 22000,
                        'compare' => 26000,
                        'desc' => 'Artisan sourdough baguette stuffed with melted herb butter, confit garlic, cheddar, and mozzarella with a touch of white truffle oil.',
                        'image' => 'https://images.unsplash.com/photo-1619895092538-128341789043?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Italian',
                        'spice' => 'Mild',
                        'serves' => '2 People',
                        'portions' => ['Standard Loaf (6 Slices)' => 22000],
                        'modifiers' => ['Creamy Jalapeno Dip' => 35, 'Extra Garlic Marinara Dip' => 30],
                    ],
                    [
                        'title' => 'Classic Italian Espresso Tiramisu Pot',
                        'cat' => 'food-bakery-desserts',
                        'price' => 24000,
                        'compare' => 29000,
                        'desc' => 'Savoiardi ladyfingers soaked in dark roasted Italian espresso, layered with whipped mascarpone cream and dusted with bitter cocoa.',
                        'image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Dessert',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Single Pot (150g)' => 24000],
                        'modifiers' => ['Extra Espresso Shot On Side' => 40],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'Vikramaditya Roy', 'email' => 'burgerclub@k71food.test', 'phone' => '9820055555'],
                'vendor' => [
                    'shop_name' => 'The Burger Club & Crisp Grills',
                    'slug' => 'the-burger-club',
                    'vendor_type' => 'food',
                    'description' => 'Gourmet smashed patties, golden brioche buns, melted cheddar, and hand-cut double fried crispy fries.',
                    'logo' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302017',
                    'address' => 'Corner Bistro 5, Malviya Nagar Central Market, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => false,
                    'cuisine_types' => ['Burgers', 'American', 'Fries', 'Fast Food'],
                    'opening_time' => '11:00',
                    'closing_time' => '02:00',
                    'avg_prep_time_mins' => 18,
                    'min_food_order_amount' => 12000,
                    'food_delivery_fee' => 3500,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'OG Truffle Double Smash Cheese Burger',
                        'cat' => 'food-burgers-fast-food',
                        'price' => 34000,
                        'compare' => 39900,
                        'desc' => 'Two crispy-edged smash patties, melted English cheddar, caramelized onions, house burger sauce in a butter toasted brioche bun.',
                        'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Burger',
                        'spice' => 'Medium',
                        'serves' => '1 Person',
                        'portions' => ['Single Burger' => 34000, 'Meal Combo (With Fries + Coke)' => 46000],
                        'modifiers' => ['Extra Crispy Bacon' => 70, 'Extra Melted Cheddar Slice' => 35, 'Truffle Mayo Dip' => 30],
                    ],
                    [
                        'title' => 'Crispy Nashville Hot Chicken Burger',
                        'cat' => 'food-burgers-fast-food',
                        'price' => 31000,
                        'compare' => 35000,
                        'desc' => 'Buttermilk brined chicken thigh fried to loud crunch, tossed in spicy Nashville oil, dill pickles, and creamy coleslaw.',
                        'image' => 'https://images.unsplash.com/photo-1625813506062-0aeb1d7a094b?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Burger',
                        'spice' => 'Extra Spicy',
                        'serves' => '1 Person',
                        'portions' => ['Single Burger' => 31000, 'Meal Combo (With Peri Fries + Coke)' => 43000],
                        'modifiers' => ['Cheese Dip Cup' => 35, 'Extra Gherkin Pickles' => 20],
                    ],
                    [
                        'title' => 'Crisp Paneer Crunch Burger (Veggie Special)',
                        'cat' => 'food-burgers-fast-food',
                        'price' => 24000,
                        'compare' => 28000,
                        'desc' => 'Golden panko breaded cottage cheese slab, mint mayo, crunchy lettuce, and sliced tomatoes in a soft sesame bun.',
                        'image' => 'https://images.unsplash.com/photo-1585238342024-78d387f4a707?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Burger',
                        'spice' => 'Medium',
                        'serves' => '1 Person',
                        'portions' => ['Single Burger' => 24000, 'Combo (With Fries + Shake)' => 38000],
                        'modifiers' => ['Extra Cheese Slice' => 30, 'Spicy Peri Peri Dip' => 25],
                    ],
                    [
                        'title' => 'Loaded Truffle & Parmesan Crinkle Fries',
                        'cat' => 'food-burgers-fast-food',
                        'price' => 19000,
                        'compare' => 23000,
                        'desc' => 'Crinkle cut Idaho potato fries tossed in aromatic white truffle oil, coarse sea salt, and aged parmesan shavings.',
                        'image' => 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Fast Food',
                        'spice' => 'Mild',
                        'serves' => '1-2 People',
                        'portions' => ['Regular Tub' => 19000, 'Monster Bucket' => 32000],
                        'modifiers' => ['Cheese Sauce Topping' => 40, 'Bacon Crumbs' => 50],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'K. Ramanathan Iyer', 'email' => 'dosakingdom@k71food.test', 'phone' => '9820066666'],
                'vendor' => [
                    'shop_name' => 'Dosa Kingdom & South Indian Tiffin',
                    'slug' => 'dosa-kingdom-tiffin',
                    'vendor_type' => 'food',
                    'description' => 'Crisp paper thin golden dosas, melt-in-mouth steamed ghee podi idlis, and traditional Kumbakonam Degree filter coffee.',
                    'logo' => 'https://images.unsplash.com/photo-1630383249896-424e482df921?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302005',
                    'address' => '22 Bapu Nagar, University Road, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => true,
                    'cuisine_types' => ['South Indian', 'Pure Veg', 'Breakfast', 'Tiffin'],
                    'opening_time' => '07:30',
                    'closing_time' => '22:30',
                    'avg_prep_time_mins' => 15,
                    'min_food_order_amount' => 8000,
                    'food_delivery_fee' => 2500,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Crispy Mysore Butter Masala Dosa',
                        'cat' => 'food-south-indian',
                        'price' => 19000,
                        'compare' => 22000,
                        'desc' => 'Thin crisp fermented crepe smeared inside with spicy red garlic chutney, filled with potato onion bhaji, and finished with fresh butter.',
                        'image' => 'https://images.unsplash.com/photo-1668236543090-82eba5ee5976?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'South Indian',
                        'spice' => 'Medium',
                        'serves' => '1 Person',
                        'portions' => ['Single Dosa' => 19000],
                        'modifiers' => ['Extra Coconut Chutney' => 20, 'Extra Sambar Bowl' => 25, 'Gunpowder Podi Ghee' => 35],
                    ],
                    [
                        'title' => 'Ghee Thatte Podi Idli (2 Pcs)',
                        'cat' => 'food-south-indian',
                        'price' => 15000,
                        'compare' => 18000,
                        'desc' => 'Soft fluffy plate-sized steamed rice idlis coated generously with spicy roasted lentil gunpowder and bubbling hot desi ghee.',
                        'image' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'South Indian',
                        'spice' => 'Medium',
                        'serves' => '1 Person',
                        'portions' => ['Plate of 2' => 15000, 'Plate of 4' => 27000],
                        'modifiers' => ['Extra Desi Ghee Drizzle' => 25, 'Tomato Onion Chutney' => 20],
                    ],
                    [
                        'title' => 'Authentic Degree Filter Coffee (Hot)',
                        'cat' => 'food-beverages-juices',
                        'price' => 8000,
                        'compare' => 10000,
                        'desc' => 'Freshly brewed chicory-coffee decoction frothy frothed with thick buffalo milk in brass davarah-tumbler style.',
                        'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Beverage',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Single Cup' => 8000, 'Flask (Serves 3)' => 21000],
                        'modifiers' => ['Jaggery Sweetener Option' => 15],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'Master Wok Chef Lee Chen', 'email' => 'dragonwok@k71food.test', 'phone' => '9820077777'],
                'vendor' => [
                    'shop_name' => 'Dragon Wok Pan-Asian Kitchen',
                    'slug' => 'dragon-wok-pan-asian',
                    'vendor_type' => 'food',
                    'description' => 'Sizzling cast-iron wok tossed noodles, delicate crystal dimsums, and spicy authentic Indo-Chinese favorites.',
                    'logo' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302001',
                    'address' => '3rd Floor Food Hub, Crystal Palm Mall, Sahkar Marg, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => false,
                    'cuisine_types' => ['Chinese', 'Pan-Asian', 'Noodles', 'Dimsum'],
                    'opening_time' => '12:00',
                    'closing_time' => '23:30',
                    'avg_prep_time_mins' => 20,
                    'min_food_order_amount' => 15000,
                    'food_delivery_fee' => 4000,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Chilli Garlic Hakka Noodles with Bok Choy',
                        'cat' => 'food-pan-asian-chinese',
                        'price' => 24000,
                        'compare' => 28000,
                        'desc' => 'High flame wok tossed noodles with sliced peppers, spring onions, crunchy bok choy, burnt garlic, and dark soy.',
                        'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Chinese',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['Regular Box' => 24000, 'Large Family Box' => 39000],
                        'modifiers' => ['Add Fried Egg' => 30, 'Add Shredded Chicken' => 70, 'Schezwan Dip' => 25],
                    ],
                    [
                        'title' => 'Steamed Chicken Crystal Dimsums (6 Pcs)',
                        'cat' => 'food-pan-asian-chinese',
                        'price' => 28000,
                        'compare' => 32000,
                        'desc' => 'Translucent hand-pinched dumplings packed with seasoned chicken mince, scallions, and water chestnuts. Served with red chilli oil dip.',
                        'image' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Non-Veg',
                        'cuisine' => 'Chinese',
                        'spice' => 'Medium',
                        'serves' => '1-2 People',
                        'portions' => ['Basket of 6' => 28000, 'Basket of 12' => 52000],
                        'modifiers' => ['Hot Garlic Sauce Dip' => 25, 'Roasted Chilli Oil Crunch' => 30],
                    ],
                    [
                        'title' => 'Wok-Tossed Cottage Cheese Chilli Paneer Gravy',
                        'cat' => 'food-pan-asian-chinese',
                        'price' => 27000,
                        'compare' => 31000,
                        'desc' => 'Golden batter-coated paneer tossed with capsicum, onions, slit green chillies, ginger, and savory soy sauce gravy.',
                        'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Chinese',
                        'spice' => 'Medium',
                        'serves' => '2 People',
                        'portions' => ['Medium Bowl' => 27000, 'Large Bowl' => 48000],
                        'modifiers' => ['Egg Fried Rice Side' => 120, 'Veg Fried Rice Side' => 90],
                    ],
                ],
            ],
            [
                'user' => ['name' => 'Pastry Chef Ananya Sen', 'email' => 'sweetnirvana@k71food.test', 'phone' => '9820088888'],
                'vendor' => [
                    'shop_name' => 'Sweet Nirvana Bakery & Desserts',
                    'slug' => 'sweet-nirvana-desserts',
                    'vendor_type' => 'food',
                    'description' => 'Handcrafted Belgian chocolate waffles, fusion cheesecakes, artisan brownies, and fresh birthday bakes.',
                    'logo' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=300&q=80',
                    'banner' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=1200&q=80',
                    'city' => 'Jaipur',
                    'state' => 'Rajasthan',
                    'pincode' => '302001',
                    'address' => '7 Boutique Lane, Civil Lines, Jaipur',
                ],
                'settings' => [
                    'is_restaurant' => true,
                    'is_pure_veg' => true,
                    'cuisine_types' => ['Dessert', 'Bakery', 'Cakes', 'Waffles'],
                    'opening_time' => '10:00',
                    'closing_time' => '01:00',
                    'avg_prep_time_mins' => 15,
                    'min_food_order_amount' => 9900,
                    'food_delivery_fee' => 3000,
                    'food_delivery_mode' => 'both',
                    'cod_enabled' => true,
                    'upi_enabled' => true,
                ],
                'dishes' => [
                    [
                        'title' => 'Warm Belgian Chocolate Liege Waffle',
                        'cat' => 'food-bakery-desserts',
                        'price' => 22000,
                        'compare' => 26000,
                        'desc' => 'Crispy pearl sugar caramelized Belgian waffle topped with molten Callebaut milk chocolate, whipped cream, and chocolate flakes.',
                        'image' => 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Dessert',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Single Waffle' => 22000, 'Double Stack' => 38000],
                        'modifiers' => ['Vanilla Bean Ice Cream Scoop' => 50, 'Nutella Drizzle' => 45, 'Fresh Strawberries' => 40],
                    ],
                    [
                        'title' => 'Signature Baked Gulab Jamun Cheesecake Slice',
                        'cat' => 'food-bakery-desserts',
                        'price' => 24000,
                        'compare' => 28000,
                        'desc' => 'New York style velvety cream cheese batter baked with whole soft mawa gulab jamuns on a cardamom biscuit crust.',
                        'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Dessert',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Single Slice' => 24000, 'Whole Cake (500g)' => 79000],
                        'modifiers' => ['Extra Saffron Rabdi Shot' => 40],
                    ],
                    [
                        'title' => 'Fudgy Dark Chocolate Brownie Sizzler Pot',
                        'cat' => 'food-bakery-desserts',
                        'price' => 19000,
                        'compare' => 23000,
                        'desc' => 'Dense gooey walnut brownie drenched in hot chocolate ganache. Microwave for 20 seconds for molten bliss at home.',
                        'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=800&q=80',
                        'diet' => 'Veg',
                        'cuisine' => 'Dessert',
                        'spice' => 'Sweet',
                        'serves' => '1 Person',
                        'portions' => ['Standard Box' => 19000],
                        'modifiers' => ['Vanilla Ice Cream Scoop' => 50, 'Roasted Walnuts' => 30],
                    ],
                ],
            ],
        ];

        // 3. Insert and Seed All Restaurants & Dishes
        foreach ($restaurantsData as $rData) {
            $nameParts = explode(' ', $rData['user']['name'], 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Chef';
            $username = Str::slug($firstName . $lastName, '_');

            // Create or update vendor user
            $user = User::firstOrCreate(
                ['email' => $rData['user']['email']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => substr($username, 0, 15) . rand(10, 99),
                    'phone' => $rData['user']['phone'],
                    'password' => Hash::make('Password@123'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ]
            );
            $user->roles()->syncWithoutDetaching([$vendorRole->id]);

            // Create or update vendor
            $vendor = Vendor::firstOrCreate(
                ['slug' => $rData['vendor']['slug']],
                array_merge($rData['vendor'], [
                    'user_id' => $user->id,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'phone' => $rData['user']['phone'],
                    'email' => $rData['user']['email'],
                    'contact_phone' => $rData['user']['phone'],
                    'contact_email' => $rData['user']['email'],
                    'pickup_address' => [
                        'city' => $rData['vendor']['city'],
                        'state' => $rData['vendor']['state'],
                        'pincode' => $rData['vendor']['pincode'],
                    ],
                ])
            );

            // Update vendor settings
            VendorSetting::updateOrCreate(
                ['vendor_id' => $vendor->id],
                $rData['settings']
            );

            // Create dishes
            foreach ($rData['dishes'] as $dishData) {
                $category = $categoryModels[$dishData['cat']] ?? $categoryModels['food-dining'];
                $slug = Str::slug($dishData['title']) . '-' . $vendor->id;

                $product = Product::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'vendor_id' => $vendor->id,
                        'category_id' => $category->id,
                        'title' => $dishData['title'],
                        'description' => $dishData['desc'],
                        'vertical' => 'food',
                        'listing_type' => 'food',
                        'base_price' => $dishData['price'],
                        'compare_at_price' => $dishData['compare'] ?? null,
                        'gst_rate' => 5,
                        'total_stock' => 100,
                        'status' => 'active',
                        'is_featured' => true,
                        'published_at' => now(),
                        'custom_fields' => [
                            'dietary_type' => $dishData['diet'],
                            'cuisine' => $dishData['cuisine'],
                            'spice_level' => $dishData['spice'] ?? 'Medium',
                            'serving_size' => $dishData['serves'] ?? '1 Person',
                            'prep_time_mins' => $rData['settings']['avg_prep_time_mins'] ?? 20,
                        ],
                    ]
                );

                // Add Dish Image
                if (!empty($dishData['image']) && $product->images()->count() === 0) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $dishData['image'],
                        'alt_text' => $dishData['title'],
                        'sort_order' => 0,
                    ]);
                }

                // Add Portion Variants
                if (!empty($dishData['portions'])) {
                    $vSort = 1;
                    foreach ($dishData['portions'] as $portionName => $portionPrice) {
                        ProductVariant::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'sku' => $portionName,
                            ],
                            [
                                'price_override' => $portionPrice,
                                'price' => $portionPrice / 100,
                                'stock' => 100,
                                'stock_quantity' => 100,
                                'is_active' => true,
                                'sort_order' => $vSort++,
                            ]
                        );
                    }
                }

                // Add Modifiers / Add-ons
                if (!empty($dishData['modifiers'])) {
                    foreach ($dishData['modifiers'] as $modName => $modDelta) {
                        ProductModifier::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'modifier_name' => $modName,
                            ],
                            [
                                'group_name' => 'Popular Add-ons',
                                'price_delta' => $modDelta,
                                'is_required' => false,
                            ]
                        );
                    }
                }
            }
        }

        // 4. Create Active Promotions & Deals
        $coupons = [
            [
                'code' => 'K71FEAST',
                'name' => 'Flat 50% Off Flash Feast',
                'description' => 'Flat 50% off up to ₹150 on your first 3 food orders',
                'type' => 'percentage',
                'value' => 50,
                'max_discount' => 15000,
                'min_cart_value' => 24900,
            ],
            [
                'code' => 'TASTY100',
                'name' => 'Flat ₹100 Off Delicious Dishes',
                'description' => 'Save flat ₹100 on orders above ₹299',
                'type' => 'flat',
                'value' => 10000,
                'max_discount' => 10000,
                'min_cart_value' => 29900,
            ],
            [
                'code' => 'FREEDEL',
                'name' => 'Free Doorstep Delivery',
                'description' => 'Zero delivery fee on orders above ₹199',
                'type' => 'flat',
                'value' => 4000,
                'max_discount' => 4000,
                'min_cart_value' => 19900,
            ],
        ];

        // Assign first restaurant as default vendor for universal coupons if required
        $firstVendor = Vendor::where('slug', 'nawabi-dastarkhwan')->first();
        if ($firstVendor) {
            foreach ($coupons as $cpn) {
                Coupon::firstOrCreate(
                    ['code' => $cpn['code']],
                    [
                        'vendor_id' => $firstVendor->id,
                        'type' => $cpn['type'],
                        'value' => $cpn['value'],
                        'max_discount' => $cpn['max_discount'],
                        'min_cart_value' => $cpn['min_cart_value'],
                        'is_active' => true,
                        'starts_at' => now()->subDay(),
                        'expires_at' => now()->addMonths(6),
                    ]
                );
            }
        }
    }
}
