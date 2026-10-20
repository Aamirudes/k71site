<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Men' => ['T-Shirts', 'Shirts', 'Jeans', 'Trousers', 'Ethnic Wear', 'Activewear'],
            'Women' => ['Dresses', 'Tops', 'Jeans', 'Kurtas & Suits', 'Sarees', 'Activewear'],
            'Kids' => ['Kids Wear', 'Boys Clothing', 'Girls Clothing', 'Infants'],
            'Accessories' => ['Bags', 'Belts', 'Watches', 'Jewellery'],
            'Footwear' => ['Sneakers', 'Formal Shoes', 'Heels', 'Flats'],
        ];

        foreach ($categories as $parentName => $children) {
            $parentId = DB::table('categories')->insertGetId([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($children as $childName) {
                DB::table('categories')->insert([
                    'parent_id' => $parentId,
                    'name' => $childName,
                    'slug' => Str::slug($parentName . '-' . $childName),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
