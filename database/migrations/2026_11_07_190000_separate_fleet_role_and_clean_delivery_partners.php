<?php

use App\Models\DeliveryPartner;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure fleet role exists
        if (Schema::hasTable('roles')) {
            $fleetRole = Role::firstOrCreate(
                ['name' => 'fleet'],
                ['display_name' => 'Fleet Delivery Boy', 'guard_name' => 'web']
            );
        }

        // 2. Safely decouple any Admin or Vendor user IDs mistakenly linked to delivery partners
        if (Schema::hasTable('delivery_partners') && Schema::hasTable('users')) {
            $adminEmails = ['crab.hashir@gmail.com', 'hashir0867@gmail.com'];
            $adminIds = User::whereIn('email', $adminEmails)->pluck('id')->all();
            
            $vendorUserIds = [];
            if (Schema::hasTable('vendors')) {
                $vendorUserIds = Vendor::whereNotNull('user_id')->pluck('user_id')->all();
            }

            $protectedUserIds = array_unique(array_merge($adminIds, $vendorUserIds));

            if (!empty($protectedUserIds)) {
                DeliveryPartner::whereIn('user_id', $protectedUserIds)->update(['user_id' => null]);
            }
        }
    }

    public function down(): void
    {
        // No destructive reverse
    }
};
