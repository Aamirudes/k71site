<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PurgeDummyMarketplaceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:purge-dummy {--force : Force execute without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipes all dummy test vendors, products, shorts, enquiries, and orders to leave a pristine clean production marketplace.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('Purging all dummy vendors, products, reviews, and test data...');

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $tablesToWipe = [
            'order_items',
            'orders',
            'cart_items',
            'carts',
            'reviews',
            'quotes',
            'enquiries',
            'shorts',
            'product_gig_packages',
            'product_modifiers',
            'product_variants',
            'product_images',
            'products',
            'vendor_settings',
            'vendors',
        ];

        foreach ($tablesToWipe as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->info("✓ Truncated table: {$table}");
            }
        }

        // Delete all users who are NOT administrators
        if (Schema::hasTable('users')) {
            $adminRole = Role::where('name', 'admin')->first();
            $adminUserIds = [];

            if ($adminRole) {
                $adminUserIds = DB::table('role_user')
                    ->where('role_id', $adminRole->id)
                    ->pluck('user_id')
                    ->toArray();
            }

            // Always preserve crab.hashir@gmail.com
            $masterAdmin = User::where('email', 'crab.hashir@gmail.com')->first();
            if ($masterAdmin) {
                $adminUserIds[] = $masterAdmin->id;
            }

            $adminUserIds = array_unique(array_filter($adminUserIds));

            $deletedCount = DB::table('users')
                ->whereNotIn('id', $adminUserIds)
                ->delete();

            $this->info("✓ Removed {$deletedCount} non-admin / test user accounts.");
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Ensure Master Administrator is intact
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $custRole = Role::firstOrCreate(['name' => 'customer']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);

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

        $admin->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);

        $this->info("✓ Master Administrator verified (crab.hashir@gmail.com).");
        $this->info('🎉 Marketplace is completely clean and ready for real vendor onboarding and live products.');

        return 0;
    }
}
