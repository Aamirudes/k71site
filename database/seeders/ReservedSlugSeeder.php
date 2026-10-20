<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservedSlugSeeder extends Seeder
{
    public function run(): void
    {
        $slugs = config('marketplace.reserved_subdomains', []);

        foreach ($slugs as $slug) {
            DB::table('reserved_slugs')->insertOrIgnore([
                'slug' => $slug,
                'reason' => 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
