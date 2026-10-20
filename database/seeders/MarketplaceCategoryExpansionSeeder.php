<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceCategoryExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Women' => [
                'Suit Sets (3-Piece)' => 'women-suit-sets',
                'Unstitched Suits & Dress Materials' => 'women-unstitched-suits',
                'Kurtas & Kurtis' => 'women-kurtas-kurtis',
                'Sarees' => 'women-sarees',
                'Lehenga Choli & Sets' => 'women-lehenga-choli',
                'Co-ord Sets' => 'women-co-ord-sets',
                'Dresses & Gowns' => 'women-dresses',
                'Tops & Tunics' => 'women-tops',
                'Ethnic Bottoms & Palazzos' => 'women-ethnic-bottoms',
                'Dupattas & Shawls' => 'women-dupattas-shawls',
                'Jeans & Jeggings' => 'women-jeans',
                'Trousers & Pants' => 'women-trousers',
                'Nightwear & Loungewear' => 'women-nightwear',
                'Winterwear & Jackets' => 'women-winterwear',
                'Activewear' => 'women-activewear',
            ],
            'Men' => [
                'T-Shirts & Polos' => 'men-t-shirts',
                'Shirts' => 'men-shirts',
                'Jeans' => 'men-jeans',
                'Trousers & Chinos' => 'men-trousers',
                'Kurta Sets & Ethnic Wear' => 'men-ethnic-wear',
                'Nehru Jackets & Vests' => 'men-nehru-jackets',
                'Trackpants & Joggers' => 'men-trackpants-joggers',
                'Winterwear & Hoodies' => 'men-winterwear',
                'Activewear' => 'men-activewear',
            ],
            'Kids' => [
                'Kids Wear' => 'kids-wear',
                'Boys Clothing' => 'kids-boys-clothing',
                'Girls Clothing' => 'kids-girls-clothing',
                'Kids Ethnic Wear' => 'kids-ethnic-wear',
                'Infants' => 'kids-infants',
            ],
            'Accessories' => [
                'Bags & Clutches' => 'accessories-bags',
                'Belts' => 'accessories-belts',
                'Watches' => 'accessories-watches',
                'Jewellery' => 'accessories-jewellery',
            ],
            'Footwear' => [
                'Ethnic Juttis & Mojaris' => 'footwear-ethnic-juttis',
                'Sneakers' => 'footwear-sneakers',
                'Formal Shoes' => 'footwear-formal-shoes',
                'Heels' => 'footwear-heels',
                'Flats' => 'footwear-flats',
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
                }
            }
        }
    }
}
