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
        $seo = Setting::getGroup('seo');
        $categories = Category::whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.homepage.edit', compact('homepage', 'seo', 'categories'));
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

            // Hero Section
            'hero_badge' => ['nullable', 'string', 'max:80'],
            'hero_title_line1' => ['nullable', 'string', 'max:100'],
            'hero_title_line2' => ['nullable', 'string', 'max:100'],
            'hero_description' => ['nullable', 'string', 'max:500'],
            'hero_cta_primary_text' => ['nullable', 'string', 'max:50'],
            'hero_cta_primary_link' => ['nullable', 'string', 'max:255'],
            'hero_cta_secondary_text' => ['nullable', 'string', 'max:50'],
            'hero_cta_secondary_link' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
            'hero_stats_enabled' => ['nullable', 'boolean'],
            'hero_stat_1_val' => ['nullable', 'string', 'max:30'],
            'hero_stat_1_lbl' => ['nullable', 'string', 'max:50'],
            'hero_stat_2_val' => ['nullable', 'string', 'max:30'],
            'hero_stat_2_lbl' => ['nullable', 'string', 'max:50'],
            'hero_stat_3_val' => ['nullable', 'string', 'max:30'],
            'hero_stat_3_lbl' => ['nullable', 'string', 'max:50'],

            // Section Titles
            'cat_section_title' => ['nullable', 'string', 'max:100'],
            'cat_section_subtitle' => ['nullable', 'string', 'max:200'],
            'boutiques_section_title' => ['nullable', 'string', 'max:100'],
            'boutiques_section_subtitle' => ['nullable', 'string', 'max:200'],
            'spotlight_section_title' => ['nullable', 'string', 'max:100'],
            'spotlight_section_subtitle' => ['nullable', 'string', 'max:200'],
            'arrivals_section_title' => ['nullable', 'string', 'max:100'],
            'arrivals_section_subtitle' => ['nullable', 'string', 'max:200'],

            // Section Toggles
            'show_announcement' => ['nullable', 'boolean'],
            'show_categories' => ['nullable', 'boolean'],
            'show_boutiques' => ['nullable', 'boolean'],
            'show_featured_products' => ['nullable', 'boolean'],
            'show_new_arrivals' => ['nullable', 'boolean'],
            'show_value_pillars' => ['nullable', 'boolean'],
            'show_seo_editorial' => ['nullable', 'boolean'],
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
            'hero_cta_primary_text', 'hero_cta_primary_link',
            'hero_cta_secondary_text', 'hero_cta_secondary_link',
            'hero_stat_1_val', 'hero_stat_1_lbl',
            'hero_stat_2_val', 'hero_stat_2_lbl',
            'hero_stat_3_val', 'hero_stat_3_lbl',
            'cat_section_title', 'cat_section_subtitle',
            'boutiques_section_title', 'boutiques_section_subtitle',
            'spotlight_section_title', 'spotlight_section_subtitle',
            'arrivals_section_title', 'arrivals_section_subtitle',
        ];
        foreach ($stringHomepageFields as $field) {
            Setting::set($field, $request->input($field), 'homepage', 'string');
        }

        // Save Homepage Booleans
        $boolHomepageFields = [
            'announcement_enabled', 'hero_stats_enabled', 'show_announcement',
            'show_categories', 'show_boutiques', 'show_featured_products',
            'show_new_arrivals', 'show_value_pillars', 'show_seo_editorial'
        ];
        foreach ($boolHomepageFields as $field) {
            Setting::set($field, $request->has($field), 'homepage', 'boolean');
        }

        return back()->with('success', 'Marketplace homepage & SEO configuration successfully published live!');
    }
}
