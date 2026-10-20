<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FootwearCategoryExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Footwear' => [
                'Sneakers & Casual Shoes' => 'footwear-sneakers',
                'Sports & Running Shoes' => 'footwear-sports-running',
                'Formal Shoes & Brogues' => 'footwear-formal-shoes',
                'Loafers & Moccasins' => 'footwear-loafers',
                'Sandals & Floaters' => 'footwear-sandals-floaters',
                'Slippers, Slides & Flip-Flops' => 'footwear-slippers-flip-flops',
                'Boots & High-Tops' => 'footwear-boots',
                'Ethnic Juttis, Mojaris & Kolhapuris' => 'footwear-ethnic-juttis',
                'Heels & Wedges' => 'footwear-heels',
                'Flats & Bellies' => 'footwear-flats',
                'Clogs & Mules' => 'footwear-clogs-mules',
                'Kids & School Shoes' => 'footwear-kids-footwear',
                'Newborn & Baby Booties' => 'footwear-baby-booties',
            ],
            'Kids' => [
                'Kids Footwear & Shoes' => 'kids-footwear',
                'Baby Booties & Infant Shoes' => 'kids-baby-booties',
            ],
        ];

        foreach ($structure as $parentName => $subcategories) {
            $parent = DB::table('categories')
                ->whereNull('parent_id')
                ->where(function ($q) use ($parentName) {
                    $q->where('name', $parentName)
                      ->orWhere('slug', Str::slug($parentName));
                })
                ->first();

            if (!$parent) {
                $parentId = DB::table('categories')->insertGetId([
                    'name' => $parentName,
                    'slug' => Str::slug($parentName),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $parentId = $parent->id;
            }

            foreach ($subcategories as $subName => $subSlug) {
                $existing = DB::table('categories')
                    ->where(function ($q) use ($subSlug, $subName, $parentId) {
                        $q->where('slug', $subSlug)
                          ->orWhere(function ($q2) use ($subName, $parentId) {
                              $q2->where('parent_id', $parentId)->where('name', $subName);
                          });
                    })
                    ->first();

                if (!$existing) {
                    DB::table('categories')->insert([
                        'parent_id' => $parentId,
                        'name' => $subName,
                        'slug' => $subSlug,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Update name to richer label if it was basic
                    DB::table('categories')->where('id', $existing->id)->update([
                        'name' => $subName,
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
