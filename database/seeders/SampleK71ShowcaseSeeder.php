<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Short;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleK71ShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);

        // 1. Fashion Boutique Vendor
        $user1 = User::firstOrCreate(['email' => 'artisan@k71.in'], [
            'first_name' => 'Kavita',
            'last_name' => 'Mehta',
            'username' => 'kavitamehta',
            'password' => Hash::make('Password@123'),
            'email_verified_at' => now(),
        ]);
        $user1->roles()->syncWithoutDetaching([$vendorRole->id]);

        $vendor1 = Vendor::firstOrCreate(['slug' => 'kavita-couture'], [
            'user_id' => $user1->id,
            'shop_name' => 'Kavita Couture Atelier',
            'description' => 'Bespoke handwoven sarees and slow-fashion couture crafted in Jaipur.',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'status' => 'approved',
            'approved_at' => now(),
            'colour_palette' => 'sage',
            'layout_preset' => 'editorial',
            'product_preset' => 'immersive',
        ]);

        VendorSetting::firstOrCreate(['vendor_id' => $vendor1->id], [
            'upi_id' => 'kavitamehta@okaxis',
            'cod_enabled' => 1,
            'upi_enabled' => 1,
        ]);

        // 2. Food Kitchen Vendor
        $user2 = User::firstOrCreate(['email' => 'kitchen@k71.in'], [
            'first_name' => 'Chef',
            'last_name' => 'Farhan',
            'username' => 'cheffarhan',
            'password' => Hash::make('Password@123'),
            'email_verified_at' => now(),
        ]);
        $user2->roles()->syncWithoutDetaching([$vendorRole->id]);

        $vendor2 = Vendor::firstOrCreate(['slug' => 'nawabi-dastarkhwan'], [
            'user_id' => $user2->id,
            'shop_name' => 'Nawabi Dastarkhwan Kitchen',
            'description' => 'Authentic slow-cooked Awadhi biryanis, kebabs, and artisan flatbreads.',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        VendorSetting::firstOrCreate(['vendor_id' => $vendor2->id], [
            'upi_id' => 'farhan@upi',
            'is_restaurant' => 1,
            'is_pure_veg' => 0,
            'cuisine_types' => ['Mughlai', 'Awadhi', 'Biryani'],
            'opening_time' => '11:00',
            'closing_time' => '23:30',
            'avg_prep_time_mins' => 25,
            'food_delivery_fee' => 4000,
            'cod_enabled' => 1,
            'upi_enabled' => 1,
        ]);

        // 3. Service Provider Vendor
        $user3 = User::firstOrCreate(['email' => 'services@k71.in'], [
            'first_name' => 'Vikram',
            'last_name' => 'Singhania',
            'username' => 'vikramtailor',
            'password' => Hash::make('Password@123'),
            'email_verified_at' => now(),
        ]);
        $user3->roles()->syncWithoutDetaching([$vendorRole->id]);

        $vendor3 = Vendor::firstOrCreate(['slug' => 'vikram-master-tailors'], [
            'user_id' => $user3->id,
            'shop_name' => 'Vikram Master Tailors & Alterations',
            'description' => 'Over 25 years of bespoke suit tailoring, bridal lehenga alterations, and express restyling.',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        VendorSetting::firstOrCreate(['vendor_id' => $vendor3->id], [
            'upi_id' => 'vikram@upi',
            'cod_enabled' => 0,
            'upi_enabled' => 1,
        ]);

        // Category reference
        $catFashion = Category::firstOrCreate(['name' => 'Handloom Sarees'], ['slug' => 'handloom-sarees', 'is_active' => true]);
        $catFood = Category::firstOrCreate(['name' => 'Artisan Biryanis & Meals'], ['slug' => 'artisan-biryanis', 'is_active' => true]);
        $catService = Category::firstOrCreate(['name' => 'Tailoring & Alterations'], ['slug' => 'tailoring-alterations', 'is_active' => true]);
        $catUsed = Category::firstOrCreate(['name' => 'Pre-Owned Horology & Watches'], ['slug' => 'pre-owned-watches', 'is_active' => true]);

        // Products
        $p1 = Product::firstOrCreate(['slug' => 'banarasi-kadwa-silk-saree'], [
            'vendor_id' => $vendor1->id,
            'category_id' => $catFashion->id,
            'title' => 'Pure Katan Silk Banarasi Kadwa Saree',
            'description' => 'Authentic handwoven Varanasi silk saree with intricate real silver zari work. 100% pure silk mark certified.',
            'base_price' => 1450000,
            'compare_at_price' => 1850000,
            'gst_rate' => 5,
            'status' => 'active',
            'is_featured' => true,
            'vertical' => 'goods',
            'listing_type' => 'purchase',
            'total_stock' => 5,
            'brand' => 'Kavita Atelier',
        ]);

        $p2 = Product::firstOrCreate(['slug' => 'awadhi-dum-biryani-clay-pot'], [
            'vendor_id' => $vendor2->id,
            'category_id' => $catFood->id,
            'title' => 'Slow-Cooked Awadhi Dum Mutton Biryani (Sealed Clay Handi)',
            'description' => 'Aged fragrant basmati rice layered with tender marinated meat, saffron, and whole spices, sealed in traditional earthen handi.',
            'base_price' => 64900,
            'gst_rate' => 5,
            'status' => 'active',
            'is_featured' => true,
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'total_stock' => 20,
            'brand' => 'Nawabi Dastarkhwan',
        ]);

        $p3 = Product::firstOrCreate(['slug' => 'bespoke-suit-doorstep-alteration'], [
            'vendor_id' => $vendor3->id,
            'category_id' => $catService->id,
            'title' => 'Executive Blazer & Suit Tailoring (Doorstep Fitting)',
            'description' => 'Master measurement and doorstep fitting session. Includes shoulder adjustments, sleeve taper, and waist shaping.',
            'base_price' => 180000,
            'gst_rate' => 18,
            'status' => 'active',
            'is_featured' => true,
            'vertical' => 'services',
            'listing_type' => 'booking',
            'total_stock' => 10,
            'brand' => 'Vikram Tailors',
        ]);

        $p4 = Product::firstOrCreate(['slug' => 'vintage-seiko-automatic-diver-1984'], [
            'vendor_id' => $vendor1->id,
            'category_id' => $catUsed->id,
            'title' => 'Vintage 1984 Automatic Diver Watch (Verified Authentic)',
            'description' => 'Collectors vintage timepiece in pristine working condition. Original patina with new sapphire crystal service.',
            'base_price' => 3200000,
            'gst_rate' => 18,
            'status' => 'active',
            'is_featured' => true,
            'is_negotiable' => true,
            'vertical' => 'used',
            'listing_type' => 'purchase',
            'total_stock' => 1,
            'brand' => 'Seiko Collector Edition',
        ]);

        // Attach sample images
        foreach ([$p1, $p2, $p3, $p4] as $idx => $prod) {
            $prod->images()->firstOrCreate(['path' => 'demo/product_' . ($idx + 1) . '.jpg'], [
                'alt_text' => $prod->title,
                'sort_order' => 0,
            ]);
        }

        $vendor1->update(['vendor_type' => 'clothing']);
        $vendor2->update(['vendor_type' => 'food']);
        $vendor3->update(['vendor_type' => 'services']);

        // 4. Shorts Video Commerce reels
        $short1 = Short::updateOrCreate(['slug' => 'weaving-royal-banarasi-saree'], [
            'title' => 'Weaving the Royal Banarasi Saree on Handloom',
            'vendor_id' => $vendor1->id,
            'category_id' => $catFashion->id,
            'description' => 'Watch our master artisans craft intricate zari patterns row by row on handloom.',
            'video_url' => '/videos/sample1.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=480&q=75',
            'status' => 'published',
            'is_featured' => true,
            'is_boosted' => true,
            'views' => 14820,
            'likes' => 2940,
            'duration' => 28,
            'trending_score' => 142.5,
        ]);
        $short1->products()->syncWithoutDetaching([$p1->id]);

        $short2 = Short::updateOrCreate(['slug' => 'unsealing-clay-handi-dum-biryani'], [
            'title' => 'Unsealing the Clay Handi: Steamy Dum Biryani',
            'vendor_id' => $vendor2->id,
            'category_id' => $catFood->id,
            'description' => 'Freshly unsealed after 4 hours of dum cooking. Incredible aroma and texture.',
            'video_url' => '/videos/sample2.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=480&q=75',
            'status' => 'published',
            'is_featured' => true,
            'is_boosted' => true,
            'views' => 19320,
            'likes' => 3840,
            'duration' => 34,
            'trending_score' => 178.0,
        ]);
        $short2->products()->syncWithoutDetaching([$p2->id]);

        $short3 = Short::updateOrCreate(['slug' => 'doorstep-bespoke-fitting-mastery'], [
            'title' => 'Doorstep Bespoke Fitting: Master Tailor in Action',
            'vendor_id' => $vendor3->id,
            'category_id' => $catService->id,
            'description' => 'Precision lapel measurements and shoulder taper done right in the client living room.',
            'video_url' => '/videos/sample1.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=480&q=75',
            'status' => 'published',
            'is_featured' => true,
            'views' => 8420,
            'likes' => 1650,
            'duration' => 24,
            'trending_score' => 95.0,
        ]);
        $short3->products()->syncWithoutDetaching([$p3->id]);

        $short4 = Short::updateOrCreate(['slug' => 'restoring-vintage-diver-watch-1984'], [
            'title' => 'Restoring a 1984 Automatic Diver Watch: Full Service',
            'vendor_id' => $vendor1->id,
            'category_id' => $catUsed->id,
            'description' => 'Watch this rare vintage diver get ultrasonically cleaned, oiled, and pressure tested.',
            'video_url' => '/videos/sample2.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=480&q=75',
            'status' => 'published',
            'is_featured' => true,
            'views' => 11200,
            'likes' => 2480,
            'duration' => 45,
            'trending_score' => 110.0,
        ]);
        $short4->products()->syncWithoutDetaching([$p4->id]);

        $short5 = Short::updateOrCreate(['slug' => '30-second-royal-pallu-drape-hack'], [
            'title' => 'The 30-Second Royal Pallu Drape Hack for Weddings',
            'vendor_id' => $vendor1->id,
            'category_id' => $catFashion->id,
            'description' => 'Styling tip: How to get crisp pleats on heavy Kadwa zari sarees without pin damage.',
            'video_url' => '/videos/sample1.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=480&q=75',
            'status' => 'published',
            'is_featured' => false,
            'is_boosted' => false,
            'views' => 7650,
            'likes' => 1340,
            'duration' => 22,
            'trending_score' => 82.0,
        ]);
        $short5->products()->syncWithoutDetaching([$p1->id]);

        $short6 = Short::updateOrCreate(['slug' => 'saffron-infusion-layering-dum-rice'], [
            'title' => 'Slow-Steeped Saffron Infusion in Aged Basmati',
            'vendor_id' => $vendor2->id,
            'category_id' => $catFood->id,
            'description' => 'Pure Kashmiri saffron infused in whole milk poured over marinated lamb.',
            'video_url' => '/videos/sample2.mp4',
            'thumb_url' => 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?auto=format&fit=crop&w=480&q=75',
            'status' => 'published',
            'is_featured' => false,
            'is_boosted' => false,
            'views' => 13200,
            'likes' => 2710,
            'duration' => 31,
            'trending_score' => 125.0,
        ]);
        $short6->products()->syncWithoutDetaching([$p2->id]);

        // 5. Active Flash Sale Promotion
        Promotion::firstOrCreate(['slug' => 'diwali-luxury-carnival'], [
            'title' => 'Diwali Luxury Festive Carnival - Flat 20% Off',
            'badge' => 'K71FESTIVE',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(14),
            'is_active' => true,
        ]);
    }
}
