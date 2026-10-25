<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomepageController extends Controller
{
    public function edit()
    {
        $homepage = Setting::getGroup('homepage');
        $servicesPage = Setting::getGroup('services_page');
        $foodPage = Setting::getGroup('food_page');
        $usedPage = Setting::getGroup('used_page');
        $seo = Setting::getGroup('seo');
        $categories = Category::whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.homepage.edit', compact('homepage', 'servicesPage', 'foodPage', 'usedPage', 'seo', 'categories'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
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

            // Homepage Hero Section
            'hero_badge' => ['nullable', 'string', 'max:80'],
            'hero_title_line1' => ['nullable', 'string', 'max:100'],
            'hero_title_line2' => ['nullable', 'string', 'max:100'],
            'hero_description' => ['nullable', 'string', 'max:500'],
            'hero_cta_primary_text' => ['nullable', 'string', 'max:50'],
            'hero_cta_primary_link' => ['nullable', 'string', 'max:255'],
            'search_placeholder' => ['nullable', 'string', 'max:100'],

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

            // Food Page Studio
            'food_hero_badge' => ['nullable', 'string', 'max:100'],
            'food_hero_title' => ['nullable', 'string', 'max:150'],
            'food_hero_subtitle' => ['nullable', 'string', 'max:300'],
            'food_search_placeholder' => ['nullable', 'string', 'max:120'],
            'food_cuisines_title' => ['nullable', 'string', 'max:100'],
            'food_trending_title' => ['nullable', 'string', 'max:100'],
            'food_restaurants_title' => ['nullable', 'string', 'max:100'],

            // Used Page Studio
            'used_hero_badge' => ['nullable', 'string', 'max:100'],
            'used_hero_title' => ['nullable', 'string', 'max:150'],
            'used_hero_subtitle' => ['nullable', 'string', 'max:300'],
        ]);

        // File uploads
        if ($request->hasFile('hero_image')) {
            $oldHero = Setting::get('hero_image');
            if ($oldHero) Storage::disk('public')->delete($oldHero);
            $path = $request->file('hero_image')->store('homepage', 'public');
            Setting::set('hero_image', $path, 'homepage', 'string');
        }

        if ($request->hasFile('seo_og_image')) {
            $oldOg = Setting::get('seo_og_image');
            if ($oldOg) Storage::disk('public')->delete($oldOg);
            $path = $request->file('seo_og_image')->store('seo', 'public');
            Setting::set('seo_og_image', $path, 'seo', 'string');
        }

        // Category Custom Images Upload / Removal
        if ($request->hasFile('category_images')) {
            foreach ($request->file('category_images') as $catId => $file) {
                $cat = Category::find($catId);
                if ($cat && $file && $file->isValid()) {
                    if ($cat->image) Storage::disk('public')->delete($cat->image);
                    $path = $file->store('categories', 'public');
                    $cat->update(['image' => $path]);
                }
            }
        }

        if ($request->has('remove_category_image')) {
            foreach ((array)$request->input('remove_category_image') as $catId => $val) {
                $cat = Category::find($catId);
                if ($cat && $cat->image) {
                    Storage::disk('public')->delete($cat->image);
                    $cat->update(['image' => null]);
                }
            }
        }

        // Save SEO Settings
        $seoFields = [
            'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords', 
            'seo_canonical_url', 'seo_robots', 'seo_og_title', 'seo_og_description',
            'seo_footer_title', 'seo_footer_content'
        ];
        foreach ($seoFields as $field) {
            Setting::set($field, $request->input($field), 'seo', 'string');
        }
        Setting::set('seo_schema_enabled', $request->has('seo_schema_enabled'), 'seo', 'boolean');

        // Save Homepage Settings
        $stringHomepageFields = [
            'announcement_badge', 'announcement_text', 'announcement_emoji', 'announcement_link',
            'hero_badge', 'hero_title_line1', 'hero_title_line2', 'hero_description',
            'hero_cta_primary_text', 'hero_cta_primary_link', 'search_placeholder',
            'arrivals_section_title', 'services_section_title', 'services_section_subtitle',
            'food_section_title', 'food_section_subtitle', 'cat_section_title', 'cat_section_subtitle'
        ];
        foreach ($stringHomepageFields as $field) {
            Setting::set($field, $request->input($field), 'homepage', 'string');
        }

        // Save Homepage Booleans
        $boolHomepageFields = [
            'announcement_enabled', 'show_trending_products', 'show_services_showcase',
            'show_food_showcase', 'show_categories', 'show_value_pillars', 'show_seller_banner'
        ];
        foreach ($boolHomepageFields as $field) {
            Setting::set($field, $request->has($field), 'homepage', 'boolean');
        }

        // Save Services Page Settings
        $servicesFields = [
            'services_hero_badge', 'services_hero_title', 'services_hero_subtitle',
            'services_search_placeholder', 'services_cat_title', 'services_cat_subtitle',
            'services_trending_title', 'services_trending_subtitle',
            'services_seller_cta_title', 'services_seller_cta_subtitle'
        ];
        foreach ($servicesFields as $field) {
            Setting::set($field, $request->input($field), 'services_page', 'string');
        }

        // Save Food Page Settings
        $foodFields = [
            'food_hero_badge', 'food_hero_title', 'food_hero_subtitle',
            'food_search_placeholder', 'food_cuisines_title', 'food_trending_title',
            'food_restaurants_title'
        ];
        foreach ($foodFields as $field) {
            Setting::set($field, $request->input($field), 'food_page', 'string');
        }

        // Save Used Page Settings
        $usedFields = [
            'used_hero_badge', 'used_hero_title', 'used_hero_subtitle'
        ];
        foreach ($usedFields as $field) {
            Setting::set($field, $request->input($field), 'used_page', 'string');
        }

        return back()->with('success', 'Page Studio settings successfully published live across all verticals!');
    }
}
