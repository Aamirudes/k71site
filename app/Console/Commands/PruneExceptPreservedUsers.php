<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class PruneExceptPreservedUsers extends Command
{
    protected $signature = 'marketplace:prune-except
                            {--dry-run : Preview exactly what would be kept and deleted without modifying database or files}
                            {--force : Execute the database prune}
                            {--clean-storage : Also delete orphaned uploaded files in storage/app/public and clear image cache/logs}
                            {--user-ids= : Optional comma-separated extra user IDs to keep}
                            {--vendor-ids= : Optional comma-separated extra vendor IDs to keep}
                            {--include-admin-vendors : Keep vendors owned by kept admin user_id}';

    protected $description = 'Prune all users, vendor stores, products, orders, chats, and orphaned storage files EXCEPT admin hashir, safiya, vendor smartx, and vendor gaddito.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run') || !$this->option('force');

        if ($dryRun) {
            $this->info('=== DRY RUN MODE (no database or file changes will be made; pass --force to execute) ===');
        }

        // 1. Resolve Preserved Vendors (smartx, gaddito + optional explicit --vendor-ids)
        $keepVendorIds = [];
        if (Schema::hasTable('vendors')) {
            $matchedVendors = DB::table('vendors')
                ->where(function ($q) {
                    $q->whereRaw('LOWER(shop_name) LIKE ?', ['%smartx%'])
                      ->orWhereRaw('LOWER(slug) LIKE ?', ['%smartx%'])
                      ->orWhereRaw('LOWER(shop_name) LIKE ?', ['%gaddito%'])
                      ->orWhereRaw('LOWER(slug) LIKE ?', ['%gaddito%']);
                })
                ->get(['id', 'user_id', 'shop_name', 'slug']);

            foreach ($matchedVendors as $v) {
                $keepVendorIds[] = (int) $v->id;
            }
        }

        if ($extraVendorIds = $this->option('vendor-ids')) {
            foreach (explode(',', (string) $extraVendorIds) as $id) {
                $intId = (int) trim($id);
                if ($intId > 0) {
                    $keepVendorIds[] = $intId;
                }
            }
        }
        $keepVendorIds = array_values(array_unique(array_filter($keepVendorIds)));

        // 2. Resolve Preserved Users:
        //    - admin hashir (crab.hashir@gmail.com or admin role named hashir)
        //    - safiya (first_name/last_name/username/email matching safiya)
        //    - owners of kept vendors (smartx, gaddito)
        $keepUserIds = [];
        if (Schema::hasTable('users')) {
            $adminRoleId = Schema::hasTable('roles')
                ? DB::table('roles')->where('name', 'admin')->value('id')
                : null;

            $adminRoleUserIds = ($adminRoleId && Schema::hasTable('role_user'))
                ? DB::table('role_user')->where('role_id', $adminRoleId)->pluck('user_id')->map(fn ($id) => (int) $id)->all()
                : [];

            $hashirAdmins = DB::table('users')
                ->where(function ($q) use ($adminRoleUserIds) {
                    $q->whereRaw('LOWER(email) = ?', ['crab.hashir@gmail.com']);
                    if (!empty($adminRoleUserIds)) {
                        $q->orWhere(function ($aq) use ($adminRoleUserIds) {
                            $aq->whereIn('id', $adminRoleUserIds)
                               ->where(function ($nq) {
                                   $nq->whereRaw('LOWER(COALESCE(first_name, "")) LIKE ?', ['%hashir%'])
                                      ->orWhereRaw('LOWER(COALESCE(last_name, "")) LIKE ?', ['%hashir%'])
                                      ->orWhereRaw('LOWER(COALESCE(username, "")) LIKE ?', ['%hashir%'])
                                      ->orWhereRaw('LOWER(email) LIKE ?', ['%hashir%']);
                               });
                        });
                    }
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $safiyaUsers = DB::table('users')
                ->where(function ($q) {
                    $q->whereRaw('LOWER(COALESCE(first_name, "")) LIKE ?', ['%safiya%'])
                      ->orWhereRaw('LOWER(COALESCE(last_name, "")) LIKE ?', ['%safiya%'])
                      ->orWhereRaw('LOWER(COALESCE(username, "")) LIKE ?', ['%safiya%'])
                      ->orWhereRaw('LOWER(email) LIKE ?', ['%safiya%']);
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $vendorOwnerIds = (!empty($keepVendorIds) && Schema::hasTable('vendors'))
                ? DB::table('vendors')->whereIn('id', $keepVendorIds)->pluck('user_id')->map(fn ($id) => (int) $id)->all()
                : [];

            $keepUserIds = array_merge($hashirAdmins, $safiyaUsers, $vendorOwnerIds);
        }

        if ($extraUserIds = $this->option('user-ids')) {
            foreach (explode(',', (string) $extraUserIds) as $id) {
                $intId = (int) trim($id);
                if ($intId > 0) {
                    $keepUserIds[] = $intId;
                }
            }
        }
        $keepUserIds = array_values(array_unique(array_filter($keepUserIds)));

        if ($this->option('include-admin-vendors') && !empty($keepUserIds) && Schema::hasTable('vendors')) {
            $ownedVendorIds = DB::table('vendors')->whereIn('user_id', $keepUserIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $keepVendorIds = array_values(array_unique(array_merge($keepVendorIds, $ownedVendorIds)));
        }

        // Also keep any vendors owned specifically by non-admin kept users (safiya / smartx owner / gaddito owner)
        if (!empty($keepUserIds) && Schema::hasTable('vendors')) {
            $nonAdminKeepUserIds = array_diff($keepUserIds, $hashirAdmins ?? []);
            if (!empty($nonAdminKeepUserIds)) {
                $ownedVendorIds = DB::table('vendors')->whereIn('user_id', $nonAdminKeepUserIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
                $keepVendorIds = array_values(array_unique(array_merge($keepVendorIds, $ownedVendorIds)));
            }
        }

        // Display exact keep plan
        $keptUsers = Schema::hasTable('users') && !empty($keepUserIds)
            ? DB::table('users')->whereIn('id', $keepUserIds)->get(['id', 'first_name', 'last_name', 'username', 'email'])
            : collect();
        $keptVendors = Schema::hasTable('vendors') && !empty($keepVendorIds)
            ? DB::table('vendors')->whereIn('id', $keepVendorIds)->get(['id', 'user_id', 'shop_name', 'slug', 'vendor_type', 'status'])
            : collect();

        $this->info('Preserved Users (' . $keptUsers->count() . '):');
        foreach ($keptUsers as $u) {
            $this->line("  - User #{$u->id}: {$u->first_name} {$u->last_name} ({$u->username}) <{$u->email}>");
        }

        $this->info('Preserved Vendors (' . $keptVendors->count() . '):');
        foreach ($keptVendors as $v) {
            $this->line("  - Vendor #{$v->id} (User #{$v->user_id}): {$v->shop_name} [/s/{$v->slug}] [{$v->vendor_type} / {$v->status}]");
        }

        // Guard against misconfigured wipe when nothing matched in non-local safety
        if (empty($keepUserIds)) {
            $this->error('SAFETY ABORT: Zero preserved users matched! Refusing to prune.');
            return 1;
        }

        // 3. Compute kept entity IDs (products, orders, conversations, used threads, etc.)
        $keepProductIds = [];
        if (Schema::hasTable('products')) {
            $hasUserIdCol = Schema::hasColumn('products', 'user_id');
            $hasSellerUserCol = Schema::hasColumn('products', 'seller_user_id');
            if (!empty($keepVendorIds) || (($hasUserIdCol || $hasSellerUserCol) && !empty($keepUserIds))) {
                $keepProductIds = DB::table('products')
                    ->where(function ($q) use ($keepVendorIds, $keepUserIds, $hasUserIdCol, $hasSellerUserCol) {
                        $applied = false;
                        if (!empty($keepVendorIds)) {
                            $q->whereIn('vendor_id', $keepVendorIds);
                            $applied = true;
                        }
                        if ($hasUserIdCol && !empty($keepUserIds)) {
                            $applied ? $q->orWhereIn('user_id', $keepUserIds) : $q->whereIn('user_id', $keepUserIds);
                            $applied = true;
                        }
                        if ($hasSellerUserCol && !empty($keepUserIds)) {
                            $applied ? $q->orWhereIn('seller_user_id', $keepUserIds) : $q->whereIn('seller_user_id', $keepUserIds);
                        }
                    })
                    ->pluck('id')->map(fn ($id) => (int) $id)->all();
            }
        }

        $keepOrderIds = Schema::hasTable('orders')
            ? DB::table('orders')
                ->where(function ($q) use ($keepUserIds, $keepVendorIds) {
                    $q->whereIn('user_id', $keepUserIds);
                    if (!empty($keepVendorIds)) {
                        $q->orWhereIn('vendor_id', $keepVendorIds);
                    }
                })
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepCheckoutGroupIds = Schema::hasTable('checkout_groups')
            ? DB::table('checkout_groups')->whereIn('user_id', $keepUserIds)->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepCartIds = Schema::hasTable('carts')
            ? DB::table('carts')->whereIn('user_id', $keepUserIds)->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepChatConvIds = Schema::hasTable('chat_conversations')
            ? DB::table('chat_conversations')
                ->whereIn('user_one_id', $keepUserIds)
                ->whereIn('user_two_id', $keepUserIds)
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepLegacyConvIds = Schema::hasTable('conversations')
            ? DB::table('conversations')
                ->whereIn('user_id', $keepUserIds)
                ->whereIn('vendor_id', !empty($keepVendorIds) ? $keepVendorIds : [-1])
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepUsedThreadIds = Schema::hasTable('used_threads')
            ? DB::table('used_threads')
                ->whereIn('buyer_id', $keepUserIds)
                ->whereIn('seller_id', $keepUserIds)
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepServiceOrderIds = Schema::hasTable('service_orders')
            ? DB::table('service_orders')
                ->whereIn('buyer_id', $keepUserIds)
                ->whereIn('vendor_id', !empty($keepVendorIds) ? $keepVendorIds : [-1])
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepServiceProjectIds = Schema::hasTable('service_projects')
            ? DB::table('service_projects')
                ->whereIn('buyer_id', $keepUserIds)
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepContractIds = Schema::hasTable('project_contracts')
            ? DB::table('project_contracts')
                ->whereIn('buyer_id', $keepUserIds)
                ->whereIn('vendor_id', !empty($keepVendorIds) ? $keepVendorIds : [-1])
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepShortIds = Schema::hasTable('shorts')
            ? DB::table('shorts')
                ->whereIn('vendor_id', !empty($keepVendorIds) ? $keepVendorIds : [-1])
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepEnquiryIds = Schema::hasTable('enquiries')
            ? DB::table('enquiries')
                ->where(function ($q) use ($keepUserIds, $keepVendorIds) {
                    $q->whereIn('user_id', $keepUserIds);
                    if (!empty($keepVendorIds) && Schema::hasColumn('enquiries', 'vendor_id')) {
                        $q->orWhereIn('vendor_id', $keepVendorIds);
                    }
                })
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $keepDisputeIds = Schema::hasTable('disputes')
            ? DB::table('disputes')
                ->whereIn('reporter_id', $keepUserIds)
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        // Summary counts before pruning
        $totalUsers = Schema::hasTable('users') ? DB::table('users')->count() : 0;
        $totalVendors = Schema::hasTable('vendors') ? DB::table('vendors')->count() : 0;
        $totalProducts = Schema::hasTable('products') ? DB::table('products')->count() : 0;
        $totalOrders = Schema::hasTable('orders') ? DB::table('orders')->count() : 0;

        $this->line('');
        $this->info('Database Prune Summary:');
        $this->line("  Users: keeping " . count($keepUserIds) . " / {$totalUsers} (deleting " . max(0, $totalUsers - count($keepUserIds)) . ")");
        $this->line("  Vendors: keeping " . count($keepVendorIds) . " / {$totalVendors} (deleting " . max(0, $totalVendors - count($keepVendorIds)) . ")");
        $this->line("  Products: keeping " . count($keepProductIds) . " / {$totalProducts} (deleting " . max(0, $totalProducts - count($keepProductIds)) . ")");
        $this->line("  Orders: keeping " . count($keepOrderIds) . " / {$totalOrders} (deleting " . max(0, $totalOrders - count($keepOrderIds)) . ")");
        $this->line("  Chat conversations kept: " . count($keepChatConvIds) . " (legacy: " . count($keepLegacyConvIds) . ", used threads: " . count($keepUsedThreadIds) . ")");

        // Gather all files referenced by KEPT records BEFORE deletion
        $keptRelativePaths = $this->collectKeptStoragePaths($keepUserIds, $keepVendorIds, $keepProductIds, $keepChatConvIds, $keepUsedThreadIds, $keepShortIds);
        $this->line('  Storage files referenced by kept entities/settings: ' . count($keptRelativePaths));

        if ($dryRun) {
            if ($this->option('clean-storage')) {
                $orphans = $this->findOrphanPublicStorageFiles($keptRelativePaths);
                $this->line('  Orphaned storage files that would be removed: ' . count($orphans));
                foreach (array_slice($orphans, 0, 25) as $orphan) {
                    $this->line("    - {$orphan}");
                }
                if (count($orphans) > 25) {
                    $this->line('    ... and ' . (count($orphans) - 25) . ' more');
                }
            }
            $this->info('Dry run complete. Re-run with --force (and optionally --clean-storage) to apply.');
            return 0;
        }

        // 4. Execute Database Prune cleanly with foreign keys temporarily disabled
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            $pruneLog = [];

            // Transient / stale auth & log tables
            foreach (['sessions', 'password_reset_tokens', 'failed_login_attempts', 'otp_codes', 'audit_logs', 'jobs', 'failed_jobs', 'job_batches', 'storefront_analytics_events', 'short_events'] as $t) {
                $pruneLog[$t] = $this->deleteWhereNotIn($t, 'id', []);
            }

            // Notifications & user metadata
            $pruneLog['notifications'] = Schema::hasTable('notifications')
                ? DB::table('notifications')->whereNotIn('notifiable_id', $keepUserIds)->delete()
                : 0;
            $pruneLog['addresses'] = $this->deleteWhereNotIn('addresses', 'user_id', $keepUserIds);
            $pruneLog['wishlists'] = Schema::hasTable('wishlists')
                ? DB::table('wishlists')->where(function ($q) use ($keepUserIds, $keepProductIds) {
                    $q->whereNotIn('user_id', $keepUserIds);
                    if (!empty($keepProductIds)) {
                        $q->orWhereNotIn('product_id', $keepProductIds);
                    }
                })->delete()
                : 0;
            $pruneLog['user_follows'] = Schema::hasTable('user_follows')
                ? DB::table('user_follows')->where(function ($q) use ($keepUserIds, $keepVendorIds) {
                    $q->whereNotIn('user_id', $keepUserIds);
                    if (!empty($keepVendorIds) && Schema::hasColumn('user_follows', 'vendor_id')) {
                        $q->orWhereNotIn('vendor_id', $keepVendorIds);
                    }
                })->delete()
                : 0;
            $pruneLog['user_consents'] = $this->deleteWhereNotIn('user_consents', 'user_id', $keepUserIds);

            // Carts & Checkout
            $pruneLog['cart_items'] = $this->deleteWhereNotIn('cart_items', 'cart_id', $keepCartIds);
            $pruneLog['carts'] = $this->deleteWhereNotIn('carts', 'id', $keepCartIds);
            $pruneLog['checkout_groups'] = $this->deleteWhereNotIn('checkout_groups', 'id', $keepCheckoutGroupIds);

            // Orders & Children
            foreach (['order_items', 'order_status_logs', 'stock_reservations', 'payment_claims', 'return_requests', 'shipments', 'commission_ledger', 'coupon_usages'] as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'order_id')) {
                    $pruneLog[$t] = $this->deleteWhereNotIn($t, 'order_id', $keepOrderIds);
                }
            }
            $pruneLog['orders'] = $this->deleteWhereNotIn('orders', 'id', $keepOrderIds);

            // Reviews, Offers, Bids, Bookings, Reports, Disputes
            $pruneLog['reviews'] = Schema::hasTable('reviews')
                ? DB::table('reviews')->where(function ($q) use ($keepUserIds, $keepProductIds) {
                    $q->whereNotIn('user_id', $keepUserIds)
                      ->orWhereNotIn('product_id', !empty($keepProductIds) ? $keepProductIds : [-1]);
                })->delete()
                : 0;
            $pruneLog['offers'] = Schema::hasTable('offers')
                ? DB::table('offers')->where(function ($q) use ($keepUserIds, $keepProductIds, $keepVendorIds) {
                    $q->whereNotIn('product_id', !empty($keepProductIds) ? $keepProductIds : [-1])
                      ->orWhereNotIn('vendor_id', !empty($keepVendorIds) ? $keepVendorIds : [-1])
                      ->orWhere(function ($uq) use ($keepUserIds) {
                          $uq->whereNotNull('user_id')->whereNotIn('user_id', $keepUserIds);
                      });
                })->delete()
                : 0;
            $pruneLog['bids'] = Schema::hasTable('bids')
                ? DB::table('bids')->whereNotIn('user_id', $keepUserIds)->delete()
                : 0;
            $pruneLog['bookings'] = Schema::hasTable('bookings')
                ? DB::table('bookings')->where(function ($q) use ($keepUserIds, $keepVendorIds) {
                    $q->whereNotIn('vendor_id', !empty($keepVendorIds) ? $keepVendorIds : [-1])
                      ->orWhere(function ($uq) use ($keepUserIds) {
                          $uq->whereNotNull('user_id')->whereNotIn('user_id', $keepUserIds);
                      });
                })->delete()
                : 0;
            $pruneLog['reports'] = Schema::hasTable('reports')
                ? DB::table('reports')->whereNotIn('user_id', $keepUserIds)->delete()
                : 0;
            $pruneLog['disputes'] = $this->deleteWhereNotIn('disputes', 'id', $keepDisputeIds);

            // Enquiries & Quotes
            $pruneLog['quotes'] = $this->deleteWhereNotIn('quotes', 'enquiry_id', $keepEnquiryIds);
            $pruneLog['enquiries'] = $this->deleteWhereNotIn('enquiries', 'id', $keepEnquiryIds);

            // Chats & Used Threads
            $pruneLog['chat_messages'] = $this->deleteWhereNotIn('chat_messages', 'conversation_id', $keepChatConvIds);
            $pruneLog['chat_conversations'] = $this->deleteWhereNotIn('chat_conversations', 'id', $keepChatConvIds);
            $pruneLog['messages'] = $this->deleteWhereNotIn('messages', 'conversation_id', $keepLegacyConvIds);
            $pruneLog['conversations'] = $this->deleteWhereNotIn('conversations', 'id', $keepLegacyConvIds);
            $pruneLog['used_thread_messages'] = $this->deleteWhereNotIn('used_thread_messages', 'thread_id', $keepUsedThreadIds);
            $pruneLog['used_threads'] = $this->deleteWhereNotIn('used_threads', 'id', $keepUsedThreadIds);
            $pruneLog['used_saved_searches'] = $this->deleteWhereNotIn('used_saved_searches', 'user_id', $keepUserIds);
            $pruneLog['used_seller_profiles'] = $this->deleteWhereNotIn('used_seller_profiles', 'user_id', $keepUserIds);
            $pruneLog['used_transactions'] = Schema::hasTable('used_transactions')
                ? DB::table('used_transactions')->whereNotIn('buyer_id', $keepUserIds)->whereNotIn('seller_id', $keepUserIds)->delete()
                : 0;

            // Services 2.0 tables
            $pruneLog['service_order_messages'] = $this->deleteWhereNotIn('service_order_messages', 'service_order_id', $keepServiceOrderIds);
            $pruneLog['service_orders'] = $this->deleteWhereNotIn('service_orders', 'id', $keepServiceOrderIds);
            $pruneLog['project_milestones'] = $this->deleteWhereNotIn('project_milestones', 'contract_id', $keepContractIds);
            $pruneLog['project_contracts'] = $this->deleteWhereNotIn('project_contracts', 'id', $keepContractIds);
            $pruneLog['project_proposals'] = Schema::hasTable('project_proposals')
                ? DB::table('project_proposals')->where(function ($q) use ($keepServiceProjectIds, $keepVendorIds) {
                    $q->whereNotIn('project_id', $keepServiceProjectIds);
                    if (!empty($keepVendorIds) && Schema::hasColumn('project_proposals', 'vendor_id')) {
                        $q->whereNotIn('vendor_id', $keepVendorIds);
                    }
                })->delete()
                : 0;
            $pruneLog['service_projects'] = $this->deleteWhereNotIn('service_projects', 'id', $keepServiceProjectIds);

            // Shorts
            foreach (['short_products', 'short_boosts', 'short_coins', 'short_comments', 'short_follows', 'short_shares'] as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'short_id')) {
                    $pruneLog[$t] = $this->deleteWhereNotIn($t, 'short_id', $keepShortIds);
                } elseif (Schema::hasTable($t) && Schema::hasColumn($t, 'vendor_id')) {
                    $pruneLog[$t] = $this->deleteWhereNotIn($t, 'vendor_id', $keepVendorIds);
                }
            }
            $pruneLog['shorts'] = $this->deleteWhereNotIn('shorts', 'id', $keepShortIds);

            // Delivery Partners
            $pruneLog['delivery_assignments'] = $this->deleteWhereNotIn('delivery_assignments', 'order_id', $keepOrderIds);
            $pruneLog['delivery_partners'] = $this->deleteWhereNotIn('delivery_partners', 'user_id', $keepUserIds);

            // Products & Children
            $keptVariantIds = Schema::hasTable('product_variants') && !empty($keepProductIds)
                ? DB::table('product_variants')->whereIn('product_id', $keepProductIds)->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [];
            $pruneLog['product_variant_attributes'] = $this->deleteWhereNotIn('product_variant_attributes', 'variant_id', $keptVariantIds);
            foreach (['product_images', 'product_variants', 'product_modifiers', 'product_option_groups', 'product_gig_packages', 'collection_product', 'stock_notifications'] as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'product_id')) {
                    $pruneLog[$t] = $this->deleteWhereNotIn($t, 'product_id', $keepProductIds);
                }
            }
            $pruneLog['products'] = $this->deleteWhereNotIn('products', 'id', $keepProductIds);

            // Vendors & Children
            foreach ([
                'vendor_settings', 'vendor_storefront_configs', 'vendor_storefront_revisions',
                'vendor_subdomain_histories', 'vendor_customers', 'vendor_subscriptions',
                'subscription_invoices', 'portfolio_projects', 'food_menu_categories',
                'coupons', 'promotions', 'size_charts', 'settlements', 'vendor_payouts',
            ] as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'vendor_id')) {
                    $pruneLog[$t] = $this->deleteWhereNotIn($t, 'vendor_id', $keepVendorIds);
                }
            }
            $pruneLog['vendors'] = $this->deleteWhereNotIn('vendors', 'id', $keepVendorIds);

            // Users & Role Links
            $pruneLog['role_user'] = $this->deleteWhereNotIn('role_user', 'user_id', $keepUserIds);
            $pruneLog['users'] = $this->deleteWhereNotIn('users', 'id', $keepUserIds);

            foreach ($pruneLog as $table => $deleted) {
                if ($deleted > 0) {
                    $this->line("  ✓ {$table}: deleted {$deleted} row(s)");
                }
            }
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        // 5. Optional Storage & Log Cleanup (preserving kept entity files)
        if ($this->option('clean-storage')) {
            $removedFiles = $this->cleanOrphanPublicStorage($keptRelativePaths);
            $this->info("  ✓ Removed {$removedFiles} orphaned / cached storage file(s) from storage/app/public.");

            $publicMirror = public_path('storage');
            if (is_dir($publicMirror) && !is_link($publicMirror)) {
                $mirrorRemoved = $this->cleanOrphanPublicStorage($keptRelativePaths, $publicMirror);
                if ($mirrorRemoved > 0) {
                    $this->info("  ✓ Removed {$mirrorRemoved} orphaned mirror file(s) from public/storage.");
                }
            }

            foreach (['laravel.log', 'request_debug.log'] as $logName) {
                $logPath = storage_path('logs/' . $logName);
                if (File::exists($logPath)) {
                    File::put($logPath, '');
                    $this->info("  ✓ Truncated log: storage/logs/{$logName}");
                }
            }
        }

        $this->info('Prune completed cleanly while preserving all data & files for admin hashir, safiya, vendor smartx, and vendor gaddito.');
        return 0;
    }

    private function deleteWhereNotIn(string $table, string $column, array $keepIds): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }
        $query = DB::table($table);
        if (!empty($keepIds)) {
            $query->whereNotIn($column, $keepIds);
        }
        return $query->delete();
    }

    /**
     * Collect all storage/app/public relative file paths referenced by kept users, vendors, products, chats, and site settings.
     */
    private function collectKeptStoragePaths(array $keepUserIds, array $keepVendorIds, array $keepProductIds, array $keepChatConvIds, array $keepUsedThreadIds, array $keepShortIds): array
    {
        $rawValues = [];

        if (Schema::hasTable('settings')) {
            $rawValues = array_merge($rawValues, DB::table('settings')->pluck('value')->filter()->all());
        }
        if (Schema::hasTable('categories')) {
            $rawValues = array_merge($rawValues, DB::table('categories')->pluck('image')->filter()->all());
        }
        if (Schema::hasTable('navigation_items')) {
            $rawValues = array_merge($rawValues, DB::table('navigation_items')->pluck('icon')->filter()->all());
            if (Schema::hasColumn('navigation_items', 'image')) {
                $rawValues = array_merge($rawValues, DB::table('navigation_items')->pluck('image')->filter()->all());
            }
        }
        if (Schema::hasTable('banners')) {
            foreach (['image', 'mobile_image', 'image_path'] as $col) {
                if (Schema::hasColumn('banners', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('banners')->pluck($col)->filter()->all());
                }
            }
        }

        if (!empty($keepUserIds) && Schema::hasTable('users')) {
            foreach (['avatar', 'profile_photo_path'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('users')->whereIn('id', $keepUserIds)->pluck($col)->filter()->all());
                }
            }
        }

        if (!empty($keepVendorIds)) {
            foreach (['logo', 'banner', 'og_image', 'portfolio_items'] as $col) {
                if (Schema::hasColumn('vendors', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('vendors')->whereIn('id', $keepVendorIds)->pluck($col)->filter()->all());
                }
            }
            if (Schema::hasTable('vendor_settings')) {
                foreach (['fssai_certificate', 'fssai_document', 'gst_certificate', 'hero_image', 'banner_image'] as $col) {
                    if (Schema::hasColumn('vendor_settings', $col)) {
                        $rawValues = array_merge($rawValues, DB::table('vendor_settings')->whereIn('vendor_id', $keepVendorIds)->pluck($col)->filter()->all());
                    }
                }
            }
            if (Schema::hasTable('vendor_storefront_configs')) {
                $configs = DB::table('vendor_storefront_configs')->whereIn('vendor_id', $keepVendorIds)->get();
                foreach ($configs as $cfg) {
                    foreach ((array) $cfg as $val) {
                        if (is_string($val) && $val !== '') {
                            $rawValues[] = $val;
                        }
                    }
                }
            }
            if (Schema::hasTable('portfolio_projects')) {
                foreach (['cover_image', 'gallery_images', 'images'] as $col) {
                    if (Schema::hasColumn('portfolio_projects', $col)) {
                        $rawValues = array_merge($rawValues, DB::table('portfolio_projects')->whereIn('vendor_id', $keepVendorIds)->pluck($col)->filter()->all());
                    }
                }
            }
        }

        if (!empty($keepProductIds)) {
            if (Schema::hasTable('product_images')) {
                $rawValues = array_merge($rawValues, DB::table('product_images')->whereIn('product_id', $keepProductIds)->pluck('path')->filter()->all());
            }
            if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'image')) {
                $rawValues = array_merge($rawValues, DB::table('product_variants')->whereIn('product_id', $keepProductIds)->pluck('image')->filter()->all());
            }
            foreach (['og_image', 'video_path', 'video_url'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('products')->whereIn('id', $keepProductIds)->pluck($col)->filter()->all());
                }
            }
        }

        if (!empty($keepChatConvIds) && Schema::hasTable('chat_messages')) {
            foreach (['attachment_path', 'media_path', 'image', 'file_path'] as $col) {
                if (Schema::hasColumn('chat_messages', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('chat_messages')->whereIn('conversation_id', $keepChatConvIds)->pluck($col)->filter()->all());
                }
            }
        }

        if (!empty($keepUsedThreadIds) && Schema::hasTable('used_thread_messages')) {
            foreach (['attachment_path', 'image'] as $col) {
                if (Schema::hasColumn('used_thread_messages', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('used_thread_messages')->whereIn('thread_id', $keepUsedThreadIds)->pluck($col)->filter()->all());
                }
            }
        }

        if (!empty($keepShortIds) && Schema::hasTable('shorts')) {
            foreach (['video_path', 'video_url', 'thumb_url', 'thumb_b_url'] as $col) {
                if (Schema::hasColumn('shorts', $col)) {
                    $rawValues = array_merge($rawValues, DB::table('shorts')->whereIn('id', $keepShortIds)->pluck($col)->filter()->all());
                }
            }
        }

        // Normalize all paths and extract storage-relative paths even from JSON strings
        $normalized = [];
        $pushPath = function (string $candidate) use (&$normalized) {
            $clean = str_replace('\\', '/', trim($candidate));
            $clean = preg_replace('#^https?://[^/]+/#i', '', $clean);
            $clean = preg_replace('#^/?storage/#i', '', $clean);
            $clean = ltrim($clean, '/');
            if ($clean !== '' && !str_contains($clean, '..')) {
                $normalized[$clean] = true;
            }
        };

        foreach ($rawValues as $raw) {
            if (!is_string($raw) || $raw === '') {
                continue;
            }
            if (preg_match_all('/(?:vendors|products|categories|settings|chats|shorts|portfolio|scroll-frames|uploads|avatars|banners)\/[A-Za-z0-9._\-\/]+/i', $raw, $m)) {
                foreach ($m[0] as $match) {
                    $pushPath($match);
                }
            }
            $pushPath($raw);
        }

        // Always keep files directly inside kept vendor directories (`vendors/{id}/...`)
        foreach ($keepVendorIds as $vid) {
            $normalized["__DIR_PREFIX__:vendors/{$vid}/"] = true;
        }
        // Keep shared non-user system folders like categories/ and settings/
        $normalized['__DIR_PREFIX__:categories/'] = true;
        $normalized['__DIR_PREFIX__:settings/'] = true;

        return array_keys($normalized);
    }

    private function isPathPreserved(string $relativePath, array $keptRelativePaths): bool
    {
        $rel = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($rel === '.gitignore') {
            return true;
        }
        foreach ($keptRelativePaths as $keep) {
            if (str_starts_with($keep, '__DIR_PREFIX__:')) {
                $prefix = substr($keep, strlen('__DIR_PREFIX__:'));
                if (str_starts_with($rel, $prefix)) {
                    return true;
                }
            } elseif ($rel === $keep) {
                return true;
            }
        }
        return false;
    }

    private function findOrphanPublicStorageFiles(array $keptRelativePaths, ?string $baseDir = null): array
    {
        $root = $baseDir ?: storage_path('app/public');
        if (!is_dir($root)) {
            return [];
        }

        $orphans = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $full = $item->getPathname();
                $rel = ltrim(str_replace('\\', '/', substr($full, strlen($root))), '/');
                if (!$this->isPathPreserved($rel, $keptRelativePaths)) {
                    $orphans[] = $rel;
                }
            }
        }

        return $orphans;
    }

    private function cleanOrphanPublicStorage(array $keptRelativePaths, ?string $baseDir = null): int
    {
        $root = $baseDir ?: storage_path('app/public');
        if (!is_dir($root)) {
            return 0;
        }

        $removed = 0;
        $orphans = $this->findOrphanPublicStorageFiles($keptRelativePaths, $root);
        foreach ($orphans as $rel) {
            $full = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_file($full) && @unlink($full)) {
                $removed++;
            }
        }

        // Prune empty directories under storage/app/public
        $dirs = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($dirs as $item) {
            if ($item->isDir()) {
                $dirPath = $item->getPathname();
                $children = @scandir($dirPath);
                if ($children !== false && count($children) <= 2) {
                    @rmdir($dirPath);
                }
            }
        }

        return $removed;
    }
}
