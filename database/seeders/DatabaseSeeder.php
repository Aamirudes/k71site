<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ReservedSlugSeeder::class,
            CategorySeeder::class,
            All13EcosystemCategoriesSeeder::class,
            MasterProductionPlatformSeeder::class,
            NavigationSeeder::class,
            UsedMarketplaceOlxSeeder::class,
        ]);

        // Seed or Enforce Master Administrator Account
        $admin = User::firstOrCreate(
            ['email' => 'crab.hashir@gmail.com'],
            [
                'first_name' => 'Hashir',
                'last_name' => 'Admin',
                'username' => 'masteradmin',
                'password' => Hash::make('Hashir1284@0'),
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ]
        );

        $admin->update([
            'first_name' => 'Hashir',
            'last_name' => 'Admin',
            'password' => Hash::make('Hashir1284@0'),
            'is_blocked' => 0,
            'blocked_reason' => null,
            'email_verified_at' => $admin->email_verified_at ?: now(),
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $custRole = Role::firstOrCreate(['name' => 'customer']);
        $admin->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);
    }
}
