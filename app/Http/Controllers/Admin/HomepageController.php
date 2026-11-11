<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Category;
use App\Services\ScrollSequenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomepageController extends Controller
{
    public function edit(Request $request)
    {
        $homepage = Setting::getGroup('homepage');
        $servicesPage = Setting::getGroup('services_page');
        $foodPage = Setting::getGroup('food_page');
        $usedPage = Setting::getGroup('used_page');
        $seo = Setting::getGroup('seo');

        $categories = Category::whereNull('parent_id')
            ->withCount(['products', 'children'])
            ->orderByDesc('show_on_home')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $scrollSequence = ScrollSequenceService::resolveSequence($homepage['scroll_seq_folder'] ?? null);
        $allowedTabs = ['homepage', 'categories', 'scroll_sequence', 'services', 'food', 'used', 'seo'];
        $requestedTab = (string) $request->input('tab', session('active_tab', 'homepage'));
        $activeTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'homepage';

        return view('admin.homepage.edit', compact(
            'homepage',
            'servicesPage',
            'foodPage',
            'usedPage',
            'seo',
            'categories',
            'scrollSequence',
            'activeTab'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'active_tab' => ['nullable', 'string', 'max:32'],

            // SEO Meta Settings
            'seo_meta_title' => ['nullable', 'string', 'max:100'],
            'seo_meta_description' => ['nullable', 'string', 'max:255'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:255'],
            'seo_canonical_url' => ['nullable', 'url', 'max:255'],
            'seo_robots' => ['nullable', 'string', 'in:index, follow,noindex, follow,index, nofollow,noindex, nofollow'],
            'seo_og_title' => ['nullable', 'string', 'max:100'],
            'seo_og_description' => ['nullable', 'string', 'max:255'],
            'seo_og_image' => ['nullable', 'image', 'max:4096'],
            'seo_schema_enabled' => ['nullable', 'boolean'],
            'seo_footer_title' => ['nullable', 'string', 'max:150'],
            'seo_footer_content' => ['nullable', 'string', 'max:10000'],

            // Announcement Marquee
            'announcement_enabled' => ['nullable', 'boolean'],
            'announcement_badge' => ['nullable', 'string', 'max:50'],
            'announcement_text' => ['nullable', 'string', 'max:255'],
            'announcement_emoji' => ['nullable', 'string', 'max:10'],
            'announcement_link' => ['nullable', 'string', 'max:255'],

            // Homepage Hero Section & 3-Image Carousel
            'show_hero_carousel' => ['nullable', 'boolean'],
            'hero_badge' => ['nullable', 'string', 'max:80'],
            'hero_title_line1' => ['nullable', 'string', 'max:100'],
            'hero_title_line2' => ['nullable', 'string', 'max:100'],
            'hero_description' => ['nullable', 'string', 'max:500'],
            'hero_cta_primary_text' => ['nullable', 'string', 'max:50'],
            'hero_cta_primary_link' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
            'search_placeholder' => ['nullable', 'string', 'max:100'],

            'slide1_badge' => ['nullable', 'string', 'max:80'],
            'slide1_title' => ['nullable', 'string', 'max:150'],
            'slide1_subtitle' => ['nullable', 'string', 'max:300'],
            'slide1_btn1_text' => ['nullable', 'string', 'max:60'],
            'slide1_btn1_link' => ['nullable', 'string', 'max:255'],
            'slide1_btn2_text' => ['nullable', 'string', 'max:60'],
            'slide1_btn2_link' => ['nullable', 'string', 'max:255'],
            'slide1_image' => ['nullable', 'image', 'max:5120'],
            'slide1_image_url' => ['nullable', 'string', 'max:500'],
            'remove_slide1_image' => ['nullable', 'boolean'],

            'slide2_badge' => ['nullable', 'string', 'max:80'],
            'slide2_title' => ['nullable', 'string', 'max:150'],
            'slide2_subtitle' => ['nullable', 'string', 'max:300'],
            'slide2_btn1_text' => ['nullable', 'string', 'max:60'],
            'slide2_btn1_link' => ['nullable', 'string', 'max:255'],
            'slide2_btn2_text' => ['nullable', 'string', 'max:60'],
            'slide2_btn2_link' => ['nullable', 'string', 'max:255'],
            'slide2_image' => ['nullable', 'image', 'max:5120'],
            'slide2_image_url' => ['nullable', 'string', 'max:500'],
            'remove_slide2_image' => ['nullable', 'boolean'],

            'slide3_badge' => ['nullable', 'string', 'max:80'],
            'slide3_title' => ['nullable', 'string', 'max:150'],
            'slide3_subtitle' => ['nullable', 'string', 'max:300'],
            'slide3_btn1_text' => ['nullable', 'string', 'max:60'],
            'slide3_btn1_link' => ['nullable', 'string', 'max:255'],
            'slide3_btn2_text' => ['nullable', 'string', 'max:60'],
            'slide3_btn2_link' => ['nullable', 'string', 'max:255'],
            'slide3_image' => ['nullable', 'image', 'max:5120'],
            'slide3_image_url' => ['nullable', 'string', 'max:500'],
            'remove_slide3_image' => ['nullable', 'boolean'],

            // Homepage Section Titles
            'arrivals_section_title' => ['nullable', 'string', 'max:100'],
            'services_section_title' => ['nullable', 'string', 'max:100'],
            'services_section_subtitle' => ['nullable', 'string', 'max:200'],
            'food_section_title' => ['nullable', 'string', 'max:100'],
            'food_section_subtitle' => ['nullable', 'string', 'max:200'],
            'cat_section_title' => ['nullable', 'string', 'max:100'],
            'cat_section_subtitle' => ['nullable', 'string', 'max:200'],

            // Homepage Section Toggles
            'show_trending_products' => ['nullable', 'boolean'],
            'show_services_showcase' => ['nullable', 'boolean'],
            'show_food_showcase' => ['nullable', 'boolean'],
            'show_categories' => ['nullable', 'boolean'],
            'show_value_pillars' => ['nullable', 'boolean'],
            'show_seller_banner' => ['nullable', 'boolean'],

            // Homepage Category Management
            'category_images' => ['nullable', 'array'],
            'category_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif,avif', 'max:5120'],
            'category_image_urls' => ['nullable', 'array'],
            'category_image_urls.*' => ['nullable', 'string', 'max:500'],
            'category_meta' => ['nullable', 'array'],
            'category_meta.*.name' => ['nullable', 'string', 'max:100'],
            'category_meta.*.icon' => ['nullable', 'string', 'max:20'],
            'category_meta.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'category_meta.*.show_on_home' => ['nullable'],
            'category_meta.*.is_active' => ['nullable'],

            // Services Page Studio
            'services_hero_badge' => ['nullable', 'string', 'max:100'],
            'services_hero_title' => ['nullable', 'string', 'max:150'],
            'services_hero_subtitle' => ['nullable', 'string', 'max:300'],
            'services_search_placeholder' => ['nullable', 'string', 'max:120'],
            'services_cat_title' => ['nullable', 'string', 'max:100'],
            'services_cat_subtitle' => ['nullable', 'string', 'max:200'],
            'services_trending_title' => ['nullable', 'string', 'max:100'],
            'services_trending_subtitle' => ['nullable', 'string', 'max:200'],
            'services_seller_cta_title' => ['nullable', 'string', 'max:120'],
            'services_seller_cta_subtitle' => ['nullable', 'string', 'max:300'],
            'services_seller_cta_btn_text' => ['nullable', 'string', 'max:60'],
            'services_seller_cta_btn_link' => ['nullable', 'string', 'max:255'],
            'services_hero_image_url' => ['nullable', 'string', 'max:500'],
            'services_hero_image' => ['nullable', 'image', 'max:5120'],

            // Food Page Studio
            'food_hero_badge' => ['nullable', 'string', 'max:100'],
            'food_hero_title' => ['nullable', 'string', 'max:150'],
            'food_hero_subtitle' => ['nullable', 'string', 'max:300'],
            'food_search_placeholder' => ['nullable', 'string', 'max:120'],
            'food_cuisines_title' => ['nullable', 'string', 'max:100'],
            'food_cuisines_subtitle' => ['nullable', 'string', 'max:200'],
            'food_trending_title' => ['nullable', 'string', 'max:100'],
            'food_restaurants_title' => ['nullable', 'string', 'max:100'],
            'food_restaurants_subtitle' => ['nullable', 'string', 'max:200'],
            'food_seller_cta_title' => ['nullable', 'string', 'max:120'],
            'food_seller_cta_subtitle' => ['nullable', 'string', 'max:300'],
            'food_seller_cta_btn_text' => ['nullable', 'string', 'max:60'],
            'food_seller_cta_btn_link' => ['nullable', 'string', 'max:255'],
            'food_hero_image_url' => ['nullable', 'string', 'max:500'],
            'food_hero_image' => ['nullable', 'image', 'max:5120'],

            // Used Page Studio
            'used_hero_badge' => ['nullable', 'string', 'max:100'],
            'used_hero_title' => ['nullable', 'string', 'max:150'],
            'used_hero_subtitle' => ['nullable', 'string', 'max:300'],
            'used_search_placeholder' => ['nullable', 'string', 'max:120'],
            'used_cat_title' => ['nullable', 'string', 'max:100'],
            'used_cat_subtitle' => ['nullable', 'string', 'max:200'],
            'used_trending_title' => ['nullable', 'string', 'max:100'],
            'used_trending_subtitle' => ['nullable', 'string', 'max:200'],
            'used_seller_cta_title' => ['nullable', 'string', 'max:120'],
            'used_seller_cta_subtitle' => ['nullable', 'string', 'max:300'],
            'used_seller_cta_btn_text' => ['nullable', 'string', 'max:60'],
            'used_seller_cta_btn_link' => ['nullable', 'string', 'max:255'],
            'used_hero_image_url' => ['nullable', 'string', 'max:500'],
            'used_hero_image' => ['nullable', 'image', 'max:5120'],

            // Scroll Frame Sequence Studio
            'scroll_seq_enabled' => ['nullable', 'boolean'],
            'scroll_seq_folder' => ['nullable', 'string', 'max:64'],
            'scroll_seq_smoothing' => ['nullable', 'numeric', 'min:0.04', 'max:0.35'],
            'scroll_seq_height_vh' => ['nullable', 'integer', 'min:180', 'max:450'],
            'scroll_seq_beat1_kicker' => ['nullable', 'string', 'max:80'],
            'scroll_seq_beat1_title' => ['nullable', 'string', 'max:140'],
            'scroll_seq_beat1_desc' => ['nullable', 'string', 'max:400'],
            'scroll_seq_beat2_kicker' => ['nullable', 'string', 'max:80'],
            'scroll_seq_beat2_title' => ['nullable', 'string', 'max:140'],
            'scroll_seq_beat2_desc' => ['nullable', 'string', 'max:400'],
            'scroll_seq_beat3_kicker' => ['nullable', 'string', 'max:80'],
            'scroll_seq_beat3_title' => ['nullable', 'string', 'max:140'],
            'scroll_seq_beat3_desc' => ['nullable', 'string', 'max:400'],
        ]);

        // File uploads
        if ($request->hasFile('hero_image')) {
            $oldHero = Setting::get('hero_image');
            if ($oldHero && !str_starts_with($oldHero, 'http')) {
                k71_delete_public_storage($oldHero);
            }
            $path = $request->file('hero_image')->store('homepage', 'public');
            k71_sync_public_storage($path);
            Setting::set('hero_image', $path, 'homepage', 'string');
        }

        if ($request->hasFile('seo_og_image')) {
            $oldOg = Setting::get('seo_og_image');
            if ($oldOg && !str_starts_with($oldOg, 'http')) {
                k71_delete_public_storage($oldOg);
            }
            $path = $request->file('seo_og_image')->store('seo', 'public');
            k71_sync_public_storage($path);
            Setting::set('seo_og_image', $path, 'seo', 'string');
        }

        // Slide Images Uploads & Removals (3-Image Carousel)
        for ($s = 1; $s <= 3; $s++) {
            if ($request->hasFile("slide{$s}_image")) {
                $oldSlide = Setting::get("slide{$s}_image");
                if ($oldSlide && !str_starts_with($oldSlide, 'http')) {
                    k71_delete_public_storage($oldSlide);
                }
                $path = $request->file("slide{$s}_image")->store('homepage', 'public');
                k71_sync_public_storage($path);
                Setting::set("slide{$s}_image", $path, 'homepage', 'string');
            } elseif ($request->boolean("remove_slide{$s}_image")) {
                $oldSlide = Setting::get("slide{$s}_image");
                if ($oldSlide && !str_starts_with($oldSlide, 'http')) {
                    k71_delete_public_storage($oldSlide);
                }
                Setting::set("slide{$s}_image", null, 'homepage', 'string');
            }
        }

        // Services Hero Image Upload
        if ($request->hasFile('services_hero_image')) {
            $oldImg = Setting::get('services_hero_image');
            if ($oldImg && !str_starts_with($oldImg, 'http')) {
                k71_delete_public_storage($oldImg);
            }
            $path = $request->file('services_hero_image')->store('services_page', 'public');
            k71_sync_public_storage($path);
            Setting::set('services_hero_image', $path, 'services_page', 'string');
        }

        // Food Hero Image Upload
        if ($request->hasFile('food_hero_image')) {
            $oldImg = Setting::get('food_hero_image');
            if ($oldImg && !str_starts_with($oldImg, 'http')) {
                k71_delete_public_storage($oldImg);
            }
            $path = $request->file('food_hero_image')->store('food_page', 'public');
            k71_sync_public_storage($path);
            Setting::set('food_hero_image', $path, 'food_page', 'string');
        }

        // Used Hero Image Upload
        if ($request->hasFile('used_hero_image')) {
            $oldImg = Setting::get('used_hero_image');
            if ($oldImg && !str_starts_with($oldImg, 'http')) {
                k71_delete_public_storage($oldImg);
            }
            $path = $request->file('used_hero_image')->store('used_page', 'public');
            k71_sync_public_storage($path);
            Setting::set('used_hero_image', $path, 'used_page', 'string');
        }

        // Category Inline Edits (Name, Icon, Sort Order, Show on Homepage, Active)
        $categoryMeta = $request->input('category_meta', []);
        if (is_array($categoryMeta)) {
            foreach ($categoryMeta as $catId => $meta) {
                if (!is_array($meta)) {
                    continue;
                }
                $cat = Category::find($catId);
                if (!$cat) {
                    continue;
                }
                $updates = [];
                if (isset($meta['name']) && trim((string) $meta['name']) !== '') {
                    $updates['name'] = Str::limit(trim((string) $meta['name']), 100, '');
                }
                if (array_key_exists('icon', $meta)) {
                    $updates['icon'] = trim((string) $meta['icon']) ?: null;
                }
                if (array_key_exists('sort_order', $meta) && $meta['sort_order'] !== null && $meta['sort_order'] !== '') {
                    $updates['sort_order'] = max(0, (int) $meta['sort_order']);
                }
                if (array_key_exists('show_on_home', $meta)) {
                    $updates['show_on_home'] = filter_var($meta['show_on_home'], FILTER_VALIDATE_BOOLEAN);
                }
                if (array_key_exists('is_active', $meta)) {
                    $updates['is_active'] = filter_var($meta['is_active'], FILTER_VALIDATE_BOOLEAN);
                }
                if (!empty($updates)) {
                    $cat->update($updates);
                }
            }
        }

        // Category Custom Image Removal / External URL / File Upload
        $removeCategoryImages = (array) $request->input('remove_category_image', []);
        foreach ($removeCategoryImages as $catId => $val) {
            if (!$val) {
                continue;
            }
            $cat = Category::find($catId);
            if ($cat && $cat->image) {
                if (!str_starts_with($cat->image, 'http')) {
                    k71_delete_public_storage($cat->image);
                }
                $cat->update(['image' => null]);
            }
        }

        $uploadedCategoryIds = [];
        if ($request->hasFile('category_images')) {
            foreach ($request->file('category_images') as $catId => $file) {
                $cat = Category::find($catId);
                if ($cat && $file && $file->isValid()) {
                    if ($cat->image && !str_starts_with($cat->image, 'http')) {
                        k71_delete_public_storage($cat->image);
                    }
                    $path = $file->store('categories', 'public');
                    k71_sync_public_storage($path);
                    $cat->update(['image' => $path]);
                    $uploadedCategoryIds[] = (int) $catId;
                }
            }
        }

        $categoryImageUrls = $request->input('category_image_urls', []);
        if (is_array($categoryImageUrls)) {
            foreach ($categoryImageUrls as $catId => $rawUrl) {
                if (in_array((int) $catId, $uploadedCategoryIds, true) || !empty($removeCategoryImages[$catId])) {
                    continue;
                }
                $url = trim((string) $rawUrl);
                if ($url === '') {
                    continue;
                }
                $cat = Category::find($catId);
                if ($cat && $cat->image !== $url) {
                    if ($cat->image && !str_starts_with($cat->image, 'http')) {
                        k71_delete_public_storage($cat->image);
                    }
                    $cat->update(['image' => Str::limit($url, 500, '')]);
                }
            }
        }

        // Save SEO Settings
        $seoFields = [
            'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords',
            'seo_canonical_url', 'seo_robots', 'seo_og_title', 'seo_og_description',
            'seo_footer_title', 'seo_footer_content',
        ];
        foreach ($seoFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'seo', 'string');
            }
        }
        if ($request->has('seo_schema_enabled')) {
            Setting::set('seo_schema_enabled', $request->boolean('seo_schema_enabled'), 'seo', 'boolean');
        }

        // Save Homepage Settings
        $stringHomepageFields = [
            'announcement_badge', 'announcement_text', 'announcement_emoji', 'announcement_link',
            'hero_badge', 'hero_title_line1', 'hero_title_line2', 'hero_description',
            'hero_cta_primary_text', 'hero_cta_primary_link', 'search_placeholder',
            'arrivals_section_title', 'services_section_title', 'services_section_subtitle',
            'food_section_title', 'food_section_subtitle', 'cat_section_title', 'cat_section_subtitle',
            'slide1_badge', 'slide1_title', 'slide1_subtitle', 'slide1_btn1_text', 'slide1_btn1_link', 'slide1_btn2_text', 'slide1_btn2_link', 'slide1_image_url',
            'slide2_badge', 'slide2_title', 'slide2_subtitle', 'slide2_btn1_text', 'slide2_btn1_link', 'slide2_btn2_text', 'slide2_btn2_link', 'slide2_image_url',
            'slide3_badge', 'slide3_title', 'slide3_subtitle', 'slide3_btn1_text', 'slide3_btn1_link', 'slide3_btn2_text', 'slide3_btn2_link', 'slide3_image_url',
            'scroll_seq_folder', 'scroll_seq_smoothing', 'scroll_seq_height_vh',
            'scroll_seq_beat1_kicker', 'scroll_seq_beat1_title', 'scroll_seq_beat1_desc',
            'scroll_seq_beat2_kicker', 'scroll_seq_beat2_title', 'scroll_seq_beat2_desc',
            'scroll_seq_beat3_kicker', 'scroll_seq_beat3_title', 'scroll_seq_beat3_desc',
        ];
        foreach ($stringHomepageFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'homepage', 'string');
            }
        }

        // Save Homepage Booleans
        $boolHomepageFields = [
            'announcement_enabled', 'show_hero_carousel', 'show_trending_products', 'show_services_showcase',
            'show_food_showcase', 'show_categories', 'show_value_pillars', 'show_seller_banner',
            'scroll_seq_enabled',
        ];
        foreach ($boolHomepageFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->boolean($field), 'homepage', 'boolean');
            }
        }

        // Save Services Page Settings
        $servicesFields = [
            'services_hero_badge', 'services_hero_title', 'services_hero_subtitle',
            'services_search_placeholder', 'services_cat_title', 'services_cat_subtitle',
            'services_trending_title', 'services_trending_subtitle',
            'services_seller_cta_title', 'services_seller_cta_subtitle',
            'services_seller_cta_btn_text', 'services_seller_cta_btn_link',
            'services_hero_image_url',
        ];
        foreach ($servicesFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'services_page', 'string');
            }
        }

        // Save Food Page Settings
        $foodFields = [
            'food_hero_badge', 'food_hero_title', 'food_hero_subtitle',
            'food_search_placeholder', 'food_cuisines_title', 'food_cuisines_subtitle',
            'food_trending_title', 'food_restaurants_title', 'food_restaurants_subtitle',
            'food_seller_cta_title', 'food_seller_cta_subtitle',
            'food_seller_cta_btn_text', 'food_seller_cta_btn_link',
            'food_hero_image_url',
        ];
        foreach ($foodFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'food_page', 'string');
            }
        }

        // Save Used Page Settings
        $usedFields = [
            'used_hero_badge', 'used_hero_title', 'used_hero_subtitle',
            'used_search_placeholder', 'used_cat_title', 'used_cat_subtitle',
            'used_trending_title', 'used_trending_subtitle',
            'used_seller_cta_title', 'used_seller_cta_subtitle',
            'used_seller_cta_btn_text', 'used_seller_cta_btn_link',
            'used_hero_image_url',
        ];
        foreach ($usedFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'used_page', 'string');
            }
        }

        $targetTab = $request->input('active_tab', 'homepage');

        return redirect()
            ->route('admin.homepage', ['tab' => $targetTab])
            ->with('success', 'Page Studio settings successfully published live across all verticals!');
    }

    /**
     * Create a new parent category directly from Admin Homepage Studio.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'vertical' => ['nullable', 'string', 'in:marketplace,food,services,enquiry,used'],
            'icon' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'show_on_home' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif,avif', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $name = trim($validated['name']);
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $vertical = $validated['vertical'] ?? 'marketplace';
        $imagePath = null;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('categories', 'public');
            k71_sync_public_storage($imagePath);
        } elseif (!empty($validated['image_url'])) {
            $imagePath = Str::limit(trim($validated['image_url']), 500, '');
        }

        Category::create([
            'parent_id' => null,
            'vertical' => $vertical,
            'vertical_type' => $vertical === 'marketplace' ? 'retail' : $vertical,
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'image' => $imagePath,
            'icon' => trim((string) ($validated['icon'] ?? '')) ?: null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'show_on_home' => $request->has('show_on_home') ? $request->boolean('show_on_home') : true,
        ]);

        return redirect()
            ->route('admin.homepage', ['tab' => 'categories'])
            ->with('success', "Category '{$name}' added cleanly and ready for the main homepage!");
    }

    /**
     * Safely remove an unused category created in Admin Studio.
     */
    public function deleteCategory(Category $category)
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return redirect()
                ->route('admin.homepage', ['tab' => 'categories'])
                ->withErrors(['category_delete' => "Category '{$category->name}' has linked products or subcategories. Untick 'Show on Homepage' or deactivate it instead."]);
        }

        $name = $category->name;
        if ($category->image && !str_starts_with($category->image, 'http')) {
            k71_delete_public_storage($category->image);
        }
        $category->delete();

        return redirect()
            ->route('admin.homepage', ['tab' => 'categories'])
            ->with('success', "Unused category '{$name}' deleted cleanly.");
    }

    /**
     * Upload a chunked batch of video/scroll-sequence frames (webp/jpg/png).
     */
    public function uploadScrollFramesBatch(Request $request)
    {
        $request->validate([
            'frames' => ['required', 'array', 'min:1', 'max:30'],
            'frames.*' => ['file', 'image', 'mimes:webp,jpg,jpeg,png', 'max:8192'],
            'start_index' => ['nullable', 'integer', 'min:1', 'max:' . ScrollSequenceService::MAX_FRAMES],
            'reset' => ['nullable', 'boolean'],
        ]);

        $files = $request->file('frames', []);
        $startIndex = max(1, (int) $request->input('start_index', 1));
        $resetBefore = $request->boolean('reset');

        $result = ScrollSequenceService::storeFrameBatch($files, $startIndex, $resetBefore);

        return response()->json([
            'ok' => true,
            'written' => $result['written'],
            'sequence' => $result['sequence'],
        ]);
    }

    /**
     * Extract and replace uploaded scroll sequence from a ZIP archive of numbered frames.
     */
    public function uploadScrollFramesZip(Request $request)
    {
        $request->validate([
            'archive' => ['required', 'file', 'max:102400'],
        ]);

        $file = $request->file('archive');
        if (strtolower((string) $file->getClientOriginalExtension()) !== 'zip') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Please upload a valid .zip archive containing WebP, JPG, or PNG frames.'], 422);
            }
            return back()->withErrors(['archive' => 'Please upload a valid .zip archive.']);
        }

        try {
            $sequence = ScrollSequenceService::storeZipArchive($file);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['archive' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'sequence' => $sequence,
            ]);
        }

        return back()->with('success', "Uploaded {$sequence['count']} scroll frames from ZIP archive!");
    }

    /**
     * Delete all admin-uploaded scroll sequence frames and revert to drop-in folder or 3D fallback.
     */
    public function clearScrollFrames(Request $request)
    {
        ScrollSequenceService::clearUploadedFrames();
        $sequence = ScrollSequenceService::resolveSequence(Setting::get('scroll_seq_folder'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'sequence' => $sequence,
            ]);
        }

        return back()->with('success', 'Uploaded scroll sequence frames cleared cleanly.');
    }
}
