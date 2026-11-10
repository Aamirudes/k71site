<?php

use App\Models\DeliveryAssignment;
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
        if (!Schema::hasTable('delivery_partners')) {
            return;
        }

        $fleetRole = Schema::hasTable('roles')
            ? Role::firstOrCreate(
                ['name' => 'fleet'],
                ['display_name' => 'Fleet Partner', 'guard_name' => 'web']
            )
            : null;

        $protectedUserIds = [];
        if (Schema::hasTable('users')) {
            $adminEmails = ['crab.hashir@gmail.com', 'hashir0867@gmail.com'];
            $adminIds = User::whereIn('email', $adminEmails)->pluck('id')->all();

            $adminRoleIds = [];
            if (Schema::hasTable('roles') && Schema::hasTable('role_user')) {
                $adminRoleId = Role::where('name', 'admin')->value('id');
                if ($adminRoleId) {
                    $adminRoleIds = \Illuminate\Support\Facades\DB::table('role_user')
                        ->where('role_id', $adminRoleId)
                        ->pluck('user_id')
                        ->all();
                }
            }

            $vendorUserIds = Schema::hasTable('vendors')
                ? Vendor::whereNotNull('user_id')->pluck('user_id')->all()
                : [];

            $protectedUserIds = array_values(array_unique(array_merge($adminIds, $adminRoleIds, $vendorUserIds)));
        }

        $validFleetUserIds = [];
        if ($fleetRole && Schema::hasTable('role_user')) {
            $validFleetUserIds = \Illuminate\Support\Facades\DB::table('role_user')
                ->where('role_id', $fleetRole->id)
                ->pluck('user_id')
                ->all();
        }

        $fakePartnerIds = DeliveryPartner::query()
            ->where(function ($q) use ($protectedUserIds, $validFleetUserIds) {
                $q->whereNull('user_id')
                    ->orWhereIn('name', [
                        'K71 Fleet Pilot',
                        'Kishangarh Fleet Pilot',
                        'K71 Kishangarh Fleet Pilot',
                    ])
                    ->orWhereIn('vehicle_number', ['RJ-01-AB-1234', 'RJ-21-SD-4091'])
                    ->orWhere('payout_upi_id', 'k71fleet@okhdfcbank');

                if (!empty($protectedUserIds)) {
                    $q->orWhereIn('user_id', $protectedUserIds);
                }

                if (empty($validFleetUserIds)) {
                    $q->orWhereNotNull('user_id');
                } else {
                    $q->orWhereNotIn('user_id', $validFleetUserIds);
                }
            })
            ->pluck('id')
            ->all();

        if (!empty($fakePartnerIds)) {
            if (Schema::hasTable('delivery_assignments')) {
                DeliveryAssignment::whereIn('delivery_partner_id', $fakePartnerIds)->delete();
            }
            DeliveryPartner::whereIn('id', $fakePartnerIds)->delete();
        }
    }

    public function down(): void
    {
        // One-way purge of synthetic/orphaned fake delivery partners
    }
};
