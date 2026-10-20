<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
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
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'is_active' => true,
                ]
            );

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($parentName . '-' . $childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
