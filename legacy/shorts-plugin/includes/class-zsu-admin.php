<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Admin — all WordPress admin pages, menus, and asset enqueuing.
 */
class ZSU_Admin {

    public static function init(): void {
        add_action( 'admin_menu',            [ self::class, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
        add_action( 'admin_bar_menu',        [ self::class, 'admin_bar_node' ], 100 );
        add_filter( 'plugin_action_links_' . plugin_basename( ZSU_FILE ), [ self::class, 'plugin_links' ] );
    }

    // ─── Register admin menus ─────────────────────────────────────────
    public static function register_menus(): void {
        add_menu_page(
            '⚡ Zilla Shorts',
            '⚡ Zilla Shorts',
            ZSU_CAP,
            'zsu-dashboard',
            [ self::class, 'page_dashboard' ],
            'dashicons-video-alt3',
            25
        );

        $pages = [
            [ 'zsu-dashboard',   '📊 Dashboard',     'page_dashboard'   ],
            [ 'zsu-shorts',      '🎬 All Shorts',     'page_shorts'      ],
            [ 'zsu-pending',     '⏳ Pending',        'page_pending'     ],
            [ 'zsu-boosts',      '🚀 Boosts',         'page_boosts'      ],
            [ 'zsu-coins',       '🪙 Coins Log',      'page_coins'       ],
            [ 'zsu-analytics',   '📈 Analytics',      'page_analytics'   ],
            [ 'zsu-presets',     '🎨 Presets',        'page_presets'     ],
            [ 'zsu-settings',    '⚙️ Settings',       'page_settings'    ],
        ];

        foreach ( $pages as [ $slug, $label, $method ] ) {
            add_submenu_page( 'zsu-dashboard', $label, $label, ZSU_CAP, $slug, [ self::class, $method ] );
        }
    }

    // ─── Enqueue admin assets ─────────────────────────────────────────
    public static function enqueue_assets( string $hook ): void {
        // Load on ANY page that belongs to our plugin — check $_GET['page'] directly
        // This is 100% reliable; WP hook names vary by install/locale
        $page = sanitize_key( $_GET['page'] ?? '' );
        if ( strpos( $page, 'zsu-' ) !== 0 ) {
            return;
        }

        // ── Chart.js from CDN (always available even if local file is stub) ──
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            [],
            '4.4.0',
            true
        );

        // ── Admin CSS — REAL file, registered with real src URL ──────────
        wp_enqueue_style(
            'zsu-admin',
            ZSU_ASSETS_URL . 'css/zsu-admin.css',
            [ 'wp-color-picker' ],
            ZSU_VER
        );

        // ── Admin JS — REAL file, registered with real src URL ───────────
        wp_enqueue_script(
            'zsu-admin',
            ZSU_ASSETS_URL . 'js/zsu-admin.js',
            [ 'jquery', 'wp-color-picker', 'chartjs' ],
            ZSU_VER,
            true
        );

        // ── Localize (safe inline data attached to REAL handle) ──────────
        wp_localize_script( 'zsu-admin', 'ZSU_ADMIN', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( ZSU_NONCE ),
            'version'   => ZSU_VER,
            'presets'   => array_keys( ZSU_Ajax::get_presets() ),
            'cpt'       => ZSU_CPT,
            'asset_url' => ZSU_ASSETS_URL,
        ] );

        // WP Color Picker
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );
    }

    // ─── Plugin action links ──────────────────────────────────────────
    public static function plugin_links( array $links ): array {
        $custom = [
            '<a href="' . admin_url( 'admin.php?page=zsu-dashboard' ) . '">Dashboard</a>',
            '<a href="' . admin_url( 'admin.php?page=zsu-settings' ) . '">Settings</a>',
        ];
        return array_merge( $custom, $links );
    }

    // ─── Admin bar quick link ─────────────────────────────────────────
    public static function admin_bar_node( \WP_Admin_Bar $bar ): void {
        if ( ! current_user_can( ZSU_CAP ) ) return;
        $bar->add_node( [
            'id'    => 'zsu-bar',
            'title' => '⚡ Shorts',
            'href'  => admin_url( 'admin.php?page=zsu-dashboard' ),
            'meta'  => [ 'title' => 'Zilla Shorts Admin' ],
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PAGE: DASHBOARD
    // ═══════════════════════════════════════════════════════════════════
    public static function page_dashboard(): void {
        $post_counts  = wp_count_posts( ZSU_CPT );
        $published    = (int) ( $post_counts->publish  ?? 0 );
        $pending      = (int) ( $post_counts->pending  ?? 0 );
        $drafts       = (int) ( $post_counts->draft    ?? 0 );
        $total        = $published + $pending + $drafts;

        global $wpdb;
        $active_boosts = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_boosts
             WHERE status='active' AND ends_at > NOW()"
        );
        $total_coins_circulating = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(CASE WHEN delta > 0 THEN delta ELSE 0 END),0)
             FROM {$wpdb->prefix}zsu_coins"
        );
        $total_vendors = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT meta_value)
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_zsu_vendor_id'"
        );
        $total_views = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(meta_value),0)
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_zsu_views'"
        );
        $total_likes = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(meta_value),0)
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_zsu_likes'"
        );
        $total_follows = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_follows"
        );

        ?>
        <div class="zsu-admin-wrap" id="zsu-dashboard">
            <?php self::render_header( 'Dashboard', '📊' ); ?>

            <!-- HERO STAT CARDS -->
            <div class="zsu-stat-grid">
                <?php
                $cards = [
                    [ '🎬', 'Total Shorts',    $total,                    '#7c3aed', 'zsu-shorts'   ],
                    [ '✅', 'Published',        $published,               '#10b981', 'zsu-shorts'   ],
                    [ '⏳', 'Pending Review',   $pending,                 '#f59e0b', 'zsu-pending'  ],
                    [ '👁️', 'Total Views',      zsu_format_num($total_views), '#06b6d4', null       ],
                    [ '❤️', 'Total Likes',      zsu_format_num($total_likes), '#f43f5e', null       ],
                    [ '🚀', 'Active Boosts',    $active_boosts,           '#8b5cf6', 'zsu-boosts'  ],
                    [ '🏪', 'Active Vendors',   $total_vendors,           '#0ea5e9', null           ],
                    [ '🪙', 'Coins Circulating',zsu_format_num($total_coins_circulating), '#f97316', 'zsu-coins' ],
                    [ '👥', 'Total Follows',    zsu_format_num($total_follows), '#ec4899', null    ],
                ];
                foreach ( $cards as [ $icon, $label, $value, $color, $page ] ) :
                    $href = $page ? admin_url( "admin.php?page={$page}" ) : '#';
                ?>
                <a href="<?php echo esc_url( $href ); ?>" class="zsu-stat-card" style="--card-color:<?php echo esc_attr($color); ?>">
                    <div class="zsu-stat-icon"><?php echo $icon; ?></div>
                    <div class="zsu-stat-body">
                        <div class="zsu-stat-value"><?php echo esc_html( $value ); ?></div>
                        <div class="zsu-stat-label"><?php echo esc_html( $label ); ?></div>
                    </div>
                    <div class="zsu-stat-glow"></div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="zsu-section">
                <h2 class="zsu-section-title">⚡ Quick Actions</h2>
                <div class="zsu-quick-actions">
                    <a href="<?php echo esc_url( admin_url('post-new.php?post_type=' . ZSU_CPT) ); ?>" class="zsu-btn zsu-btn-primary">
                        ➕ Add New Short
                    </a>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=zsu-pending') ); ?>" class="zsu-btn zsu-btn-warning">
                        ⏳ Review Pending (<?php echo $pending; ?>)
                    </a>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=zsu-boosts') ); ?>" class="zsu-btn zsu-btn-purple">
                        🚀 Manage Boosts
                    </a>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=zsu-analytics') ); ?>" class="zsu-btn zsu-btn-cyan">
                        📈 View Analytics
                    </a>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=zsu-settings') ); ?>" class="zsu-btn zsu-btn-ghost">
                        ⚙️ Settings
                    </a>
                    <a href="<?php echo esc_url( home_url('/shorts') ); ?>" target="_blank" class="zsu-btn zsu-btn-ghost">
                        🌐 View Frontend
                    </a>
                </div>
            </div>

            <!-- LIVE CHART (last 30 days) -->
            <div class="zsu-section">
                <div class="zsu-section-header">
                    <h2 class="zsu-section-title">📈 Analytics Overview</h2>
                    <div class="zsu-chart-range-tabs" id="zsuChartRangeTabs">
                        <button class="zsu-tab active" data-days="7">7D</button>
                        <button class="zsu-tab" data-days="14">14D</button>
                        <button class="zsu-tab" data-days="30">30D</button>
                        <button class="zsu-tab" data-days="90">90D</button>
                    </div>
                </div>
                <div class="zsu-chart-card">
                    <canvas id="zsuDashChart" height="80"></canvas>
                    <div class="zsu-chart-loader" id="zsuDashChartLoader">
                        <div class="zsu-spinner"></div>
                    </div>
                </div>
            </div>

            <!-- TOP SHORTS TABLE -->
            <div class="zsu-section">
                <div class="zsu-section-header">
                    <h2 class="zsu-section-title">🏆 Top Performing Shorts</h2>
                    <button class="zsu-btn zsu-btn-sm zsu-btn-ghost" id="zsuExportCsvBtn">⬇ Export CSV</button>
                </div>
                <div class="zsu-table-wrap" id="zsuTopShortsWrap">
                    <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
                </div>
            </div>

            <!-- PENDING QUEUE PREVIEW -->
            <?php if ( $pending > 0 ) : ?>
            <div class="zsu-section zsu-pending-preview">
                <div class="zsu-section-header">
                    <h2 class="zsu-section-title">⏳ Pending Approval Queue</h2>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=zsu-pending') ); ?>" class="zsu-btn zsu-btn-sm zsu-btn-warning">View All</a>
                </div>
                <div id="zsuPendingPreview">
                    <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PAGE: ALL SHORTS MANAGER
    // ═══════════════════════════════════════════════════════════════════
    public static function page_shorts(): void {
        ?>
        <div class="zsu-admin-wrap" id="zsu-shorts-manager">
            <?php self::render_header( 'All Shorts Manager', '🎬' ); ?>

            <!-- FILTER BAR -->
            <div class="zsu-filter-bar">
                <div class="zsu-search-box">
                    <span class="zsu-search-icon">🔍</span>
                    <input type="text" id="zsuShortsSearch" placeholder="Search by title, vendor…" class="zsu-search-input">
                </div>
                <div class="zsu-filter-group">
                    <select id="zsuStatusFilter" class="zsu-select">
                        <option value="">All Statuses</option>
                        <option value="publish">Published</option>
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                    </select>
                    <select id="zsuCatFilter" class="zsu-select">
                        <option value="">All Categories</option>
                        <?php
                        $cats = get_terms( [ 'taxonomy' => ZSU_TAX, 'hide_empty' => false ] );
                        if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
                            foreach ( $cats as $c ) {
                                echo '<option value="' . esc_attr( $c->term_id ) . '">' . esc_html( $c->name ) . ' (' . $c->count . ')</option>';
                            }
                        }
                        ?>
                    </select>
                    <select id="zsuSortFilter" class="zsu-select">
                        <option value="date-desc">Newest First</option>
                        <option value="date-asc">Oldest First</option>
                        <option value="views-desc">Most Views</option>
                        <option value="likes-desc">Most Liked</option>
                        <option value="trending">Trending</option>
                    </select>
                </div>
                <div class="zsu-filter-actions">
                    <button class="zsu-btn zsu-btn-sm zsu-btn-primary" id="zsuShortsRefresh">🔄 Refresh</button>
                    <a href="<?php echo esc_url( admin_url('post-new.php?post_type=' . ZSU_CPT) ); ?>" class="zsu-btn zsu-btn-sm zsu-btn-success">➕ Add New</a>
                </div>
            </div>

            <!-- BULK ACTIONS -->
            <div class="zsu-bulk-bar" id="zsuBulkBar" style="display:none;">
                <span class="zsu-bulk-count" id="zsuBulkCount">0 selected</span>
                <button class="zsu-btn zsu-btn-sm zsu-btn-success" id="zsuBulkApprove">✅ Approve All</button>
                <button class="zsu-btn zsu-btn-sm zsu-btn-danger"  id="zsuBulkDelete">🗑 Delete All</button>
                <button class="zsu-btn zsu-btn-sm zsu-btn-ghost"   id="zsuBulkClear">✕ Clear</button>
            </div>

            <!-- SHORTS GRID -->
            <div class="zsu-shorts-admin-grid" id="zsuShortsAdminGrid">
                <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
            </div>

            <!-- PAGINATION -->
            <div class="zsu-pagination" id="zsuShortsPagination"></div>

        </div>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PAGE: PENDING REVIEW
    // ═══════════════════════════════════════════════════════════════════
    public static function page_pending(): void {
        ?>
        <div class="zsu-admin-wrap" id="zsu-pending-page">
            <?php self::render_header( 'Pending Review Queue', '⏳' ); ?>

            <div class="zsu-section-desc">
                Review and approve or reject shorts submitted by vendors.
            </div>

            <div class="zsu-pending-grid" id="zsuPendingGrid">
                <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
            </div>

            <div class="zsu-empty-state" id="zsuPendingEmpty" style="display:none;">
                <div class="zsu-empty-icon">🎉</div>
                <div class="zsu-empty-title">All caught up!</div>
                <div class="zsu-empty-desc">No shorts are currently pending review.</div>
            </div>

        </div>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PAGE: BOOSTS MANAGER
    // ═══════════════════════════════════════════════════════════════════
    public static function page_boosts(): void {
        global $wpdb;

        $total_boosts  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_boosts" );
        $active_boosts = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_boosts WHERE status='active' AND ends_at > NOW()" );
        $total_spent   = (int) $wpdb->get_var( "SELECT COALESCE(SUM(coins_spent),0) FROM {$wpdb->prefix}zsu_boosts WHERE status IN ('active','expired')" );
        $total_impr    = (int) $wpdb->get_var( "SELECT COALESCE(SUM(impressions),0) FROM {$wpdb->prefix}zsu_boosts" );

        $boost_prices = [
            'mega'     => (int) zsu_opt( 'zsu_boost_price_mega', 2499 ),
        ];

        ?>
        <div class="zsu-admin-wrap" id="zsu-boosts-manager">
            <?php self::render_header( 'Boost Manager', '🚀' ); ?>

            <!-- BOOST STATS CARDS -->
            <div class="zsu-stat-grid zsu-stat-grid-4">
                <?php
                $b_cards = [
                    [ '🚀', 'Active Boosts',    $active_boosts,                   '#8b5cf6' ],
                    [ '📊', 'Total Campaigns',   $total_boosts,                    '#06b6d4' ],
                    [ '🪙', 'Coins Spent',        zsu_format_num($total_spent),    '#f59e0b' ],
                    [ '👁️', 'Total Impressions', zsu_format_num($total_impr),     '#10b981' ],
                ];
                foreach ( $b_cards as [ $icon, $label, $value, $color ] ) :
                ?>
                <div class="zsu-stat-card" style="--card-color:<?php echo esc_attr($color); ?>">
                    <div class="zsu-stat-icon"><?php echo $icon; ?></div>
                    <div class="zsu-stat-body">
                        <div class="zsu-stat-value"><?php echo esc_html($value); ?></div>
                        <div class="zsu-stat-label"><?php echo esc_html($label); ?></div>
                    </div>
                    <div class="zsu-stat-glow"></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- BOOST PRICING CARDS -->
            <div class="zsu-section">
                <h2 class="zsu-section-title">💰 Boost Tiers (Coins per 7 days)</h2>
                <div class="zsu-boost-tiers">
                    <?php
                    $tiers = [
                        [ 'standard', '⭐', 'Standard', 'Moderately boosted — appears in feed', '#6366f1', $boost_prices['standard'] ],
                        [ 'pro',      '🌟', 'Pro',       'Higher priority + category highlights', '#f59e0b', $boost_prices['pro'] ],
                        [ 'ultra',    '💫', 'Ultra',     'Top placement + trending bar',          '#8b5cf6', $boost_prices['ultra'] ],
                        [ 'mega',     '🔥', 'Mega',      'Homepage hero + all category tops',     '#ef4444', $boost_prices['mega'] ],
                    ];
                    foreach ( $tiers as [ $key, $icon, $name, $desc, $color, $price ] ) :
                    ?>
                    <div class="zsu-boost-tier-card" style="--tier-color:<?php echo esc_attr($color); ?>">
                        <div class="zsu-tier-icon"><?php echo $icon; ?></div>
                        <div class="zsu-tier-name"><?php echo esc_html($name); ?></div>
                        <div class="zsu-tier-price"><?php echo esc_html(number_format($price)); ?> <span>coins/7d</span></div>
                        <div class="zsu-tier-desc"><?php echo esc_html($desc); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div class="zsu-filter-bar">
                <div class="zsu-filter-group">
                    <select id="zsuBoostStatusFilter" class="zsu-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select id="zsuBoostTypeFilter" class="zsu-select">
                        <option value="">All Types</option>
                        <option value="standard">Standard</option>
                        <option value="pro">Pro</option>
                        <option value="ultra">Ultra</option>
                        <option value="mega">Mega</option>
                    </select>
                </div>
                <button class="zsu-btn zsu-btn-sm zsu-btn-primary" id="zsuBoostsRefresh">🔄 Refresh</button>
            </div>

            <!-- BOOSTS TABLE -->
            <div class="zsu-table-wrap" id="zsuBoostsTableWrap">
                <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
            </div>

            <!-- ── MANUAL BOOST CONTROL ──────────────────────────── -->
            <div class="zsu-section" style="padding:24px;margin-top:0;">
                <h2 class="zsu-section-title">⚡ Manual Boost — Pin Any Short to Top of Feed</h2>
                <p style="color:#64748b;font-size:13px;margin:0 0 18px;">Search any short by title or ID and instantly pin it to position #1 in the frontend grid. No coins required.</p>
                <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                    <div style="flex:2;min-width:260px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;">Short (ID or title search)</label>
                        <input type="text" id="zsuBoostShortSearch" placeholder="Type title or paste post ID…"
                               style="width:100%;padding:8px 12px;background:#1a1d2e;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#e2e8f0;font-size:13px;">
                        <div id="zsuBoostShortResults"></div>
                        <input type="hidden" id="zsuBoostShortId">
                        <div id="zsuBoostShortDisplay" style="margin-top:6px;font-size:12px;color:#a78bfa;font-weight:600;"></div>
                    </div>
                    <div>
                        <button id="zsuManualBoostEnableBtn" class="zsu-btn zsu-btn-primary" style="padding:8px 20px;">
                            🚀 Pin to Top
                        </button>
                        <button id="zsuManualBoostDisableBtn" class="zsu-btn zsu-btn-ghost" style="padding:8px 20px;margin-left:8px;">
                            ✕ Remove Pin
                        </button>
                    </div>
                </div>
                <div id="zsuManualBoostMsg" style="margin-top:14px;font-size:13px;display:none;padding:10px 14px;border-radius:8px;"></div>
            </div>

        </div>
        <script>
        (function(){
            const searchInput = document.getElementById('zsuBoostShortSearch');
            const hiddenId    = document.getElementById('zsuBoostShortId');
            const display     = document.getElementById('zsuBoostShortDisplay');
            const results     = document.getElementById('zsuBoostShortResults');
            const msgBox      = document.getElementById('zsuManualBoostMsg');
            let debounce;

            if ( searchInput ) {
                searchInput.addEventListener('input', function(){
                    clearTimeout(debounce);
                    const q = this.value.trim();
                    hiddenId.value = '';
                    display.textContent = '';
                    results.innerHTML = '';
                    if ( ! q ) return;
                    if ( /^\d+$/.test(q) ) {
                        hiddenId.value = q;
                        display.textContent = 'Post ID: ' + q;
                        return;
                    }
                    debounce = setTimeout(function(){
                        jQuery.post(ajaxurl, {
                            action: 'zsu_admin_search_shorts',
                            nonce:  ZSU_ADMIN.nonce,
                            q:      q
                        }, function(res){
                            if ( !res || !res.posts ) return;
                            results.innerHTML = res.posts.map(function(p){
                                return '<div class="zsu-boost-short-result" data-id="'+p.id+'" data-title="'+p.title+'" style="padding:8px 12px;background:#1a1d2e;border:1px solid rgba(255,255,255,0.1);border-radius:6px;margin-top:4px;cursor:pointer;font-size:13px;color:#e2e8f0;">'+p.title+' <span style="color:#64748b;font-size:11px;">#'+p.id+'</span></div>';
                            }).join('');
                            results.querySelectorAll('.zsu-boost-short-result').forEach(function(el){
                                el.addEventListener('click', function(){
                                    hiddenId.value      = this.dataset.id;
                                    searchInput.value   = this.dataset.title;
                                    display.textContent = 'Selected: '+this.dataset.title+' (#'+this.dataset.id+')';
                                    results.innerHTML   = '';
                                });
                            });
                        });
                    }, 350);
                });
            }

            function doBoostAction(boostAction) {
                const pid = document.getElementById('zsuBoostShortId').value;
                if ( ! pid ) { alert('Please select a short first.'); return; }
                jQuery.post(ajaxurl, {
                    action:       'zsu_admin_manual_boost',
                    nonce:        ZSU_ADMIN.nonce,
                    post_id:      pid,
                    boost_action: boostAction
                }, function(res){
                    msgBox.style.display = 'block';
                    if ( res && res.success ) {
                        const on = res.boosted;
                        msgBox.style.background = on ? 'rgba(16,185,129,0.15)' : 'rgba(100,116,139,0.15)';
                        msgBox.style.border     = '1px solid ' + (on ? '#10b981' : '#64748b');
                        msgBox.style.color      = on ? '#10b981' : '#94a3b8';
                        msgBox.textContent      = on
                            ? '✅ "' + res.title + '" is now pinned to #1 in the feed.'
                            : '✕ Pin removed from "' + res.title + '".';
                    } else {
                        msgBox.style.background = 'rgba(239,68,68,0.15)';
                        msgBox.style.border     = '1px solid #ef4444';
                        msgBox.style.color      = '#ef4444';
                        msgBox.textContent      = '❌ ' + (res.message || 'Error.');
                    }
                });
            }

            const enableBtn  = document.getElementById('zsuManualBoostEnableBtn');
            const disableBtn = document.getElementById('zsuManualBoostDisableBtn');
            if ( enableBtn  ) enableBtn.addEventListener('click',  function(){ doBoostAction('enable');  });
            if ( disableBtn ) disableBtn.addEventListener('click', function(){ doBoostAction('disable'); });
        })();
        </script>
        <?php
    }
    public static function page_coins(): void {
        global $wpdb;
        $total_issued  = (int) $wpdb->get_var( "SELECT COALESCE(SUM(CASE WHEN delta>0 THEN delta ELSE 0 END),0) FROM {$wpdb->prefix}zsu_coins" );
        $total_spent   = (int) $wpdb->get_var( "SELECT COALESCE(SUM(CASE WHEN delta<0 THEN ABS(delta) ELSE 0 END),0) FROM {$wpdb->prefix}zsu_coins" );
        $unique_users  = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}zsu_coins" );
        $net_circulate = $total_issued - $total_spent;

        ?>
        <div class="zsu-admin-wrap" id="zsu-coins-page">
            <?php self::render_header( 'Coins Ledger', '🪙' ); ?>

            <div class="zsu-stat-grid zsu-stat-grid-4">
                <?php
                $c_cards = [
                    [ '🟡', 'Total Issued',     zsu_format_num($total_issued),    '#f59e0b' ],
                    [ '🔴', 'Total Spent',       zsu_format_num($total_spent),     '#ef4444' ],
                    [ '🟢', 'Net Circulating',   zsu_format_num($net_circulate),   '#10b981' ],
                    [ '👤', 'Unique Holders',    $unique_users,                    '#8b5cf6' ],
                ];
                foreach ( $c_cards as [ $icon, $label, $value, $color ] ) :
                ?>
                <div class="zsu-stat-card" style="--card-color:<?php echo esc_attr($color); ?>">
                    <div class="zsu-stat-icon"><?php echo $icon; ?></div>
                    <div class="zsu-stat-body">
                        <div class="zsu-stat-value"><?php echo esc_html($value); ?></div>
                        <div class="zsu-stat-label"><?php echo esc_html($label); ?></div>
                    </div>
                    <div class="zsu-stat-glow"></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="zsu-section">
                <h2 class="zsu-section-title">📋 Recent Transactions</h2>
                <div class="zsu-table-wrap" id="zsuCoinsLogWrap">
                    <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
                </div>
            </div>

            <!-- ── VENDOR COIN CONTROL ─────────────────────────────── -->
            <div class="zsu-section" style="padding:24px;">
                <h2 class="zsu-section-title">🪙 Give / Deduct Coins from Vendor</h2>
                <p style="color:#64748b;font-size:13px;margin:0 0 18px;">Search a vendor, enter an amount (positive = give, negative = deduct), add a reason and save.</p>
                <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                    <div style="flex:1;min-width:200px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;">Vendor (User ID or name)</label>
                        <input type="text" id="zsuCoinVendorSearch" placeholder="Type name or paste user ID…"
                               style="width:100%;padding:8px 12px;background:#1a1d2e;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#e2e8f0;font-size:13px;">
                        <div id="zsuCoinVendorResults" style="position:relative;"></div>
                        <input type="hidden" id="zsuCoinVendorId">
                        <div id="zsuCoinVendorDisplay" style="margin-top:6px;font-size:12px;color:#a78bfa;font-weight:600;"></div>
                    </div>
                    <div style="min-width:140px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;">Amount</label>
                        <input type="number" id="zsuCoinAmount" placeholder="e.g. 500 or -200"
                               style="width:100%;padding:8px 12px;background:#1a1d2e;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#e2e8f0;font-size:13px;">
                    </div>
                    <div style="flex:2;min-width:200px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;">Reason (optional)</label>
                        <input type="text" id="zsuCoinReason" placeholder="e.g. promotional credit, penalty…"
                               style="width:100%;padding:8px 12px;background:#1a1d2e;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#e2e8f0;font-size:13px;">
                    </div>
                    <div>
                        <button id="zsuCoinAdjustBtn" class="zsu-btn zsu-btn-primary" style="padding:8px 22px;">
                            💾 Apply
                        </button>
                    </div>
                </div>
                <div id="zsuCoinAdjustMsg" style="margin-top:14px;font-size:13px;display:none;padding:10px 14px;border-radius:8px;"></div>
            </div>

        </div>
        <script>
        (function(){
            // Live vendor search
            const searchInput  = document.getElementById('zsuCoinVendorSearch');
            const hiddenId     = document.getElementById('zsuCoinVendorId');
            const display      = document.getElementById('zsuCoinVendorDisplay');
            const results      = document.getElementById('zsuCoinVendorResults');
            let debounce;

            if ( searchInput ) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounce);
                    const q = this.value.trim();
                    hiddenId.value = '';
                    display.textContent = '';
                    if ( ! q ) { results.innerHTML=''; return; }
                    // If pure integer — treat as direct user ID
                    if ( /^\d+$/.test(q) ) {
                        hiddenId.value = q;
                        display.textContent = 'User ID: ' + q;
                        results.innerHTML = '';
                        return;
                    }
                    debounce = setTimeout(function(){
                        jQuery.post(ajaxurl, {
                            action: 'zsu_admin_search_vendor',
                            nonce:  ZSU_ADMIN.nonce,
                            q:      q
                        }, function(res) {
                            if (!res || !res.users) return;
                            results.innerHTML = res.users.map(function(u){
                                return '<div class="zsu-vendor-result" data-id="'+u.id+'" data-name="'+u.name+'" style="padding:8px 12px;background:#1a1d2e;border:1px solid rgba(255,255,255,0.1);border-radius:6px;margin-top:4px;cursor:pointer;font-size:13px;color:#e2e8f0;">'+u.name+' <span style="color:#64748b;font-size:11px;">#'+u.id+' — balance: '+u.balance+' coins</span></div>';
                            }).join('');
                            results.querySelectorAll('.zsu-vendor-result').forEach(function(el){
                                el.addEventListener('click', function(){
                                    hiddenId.value = this.dataset.id;
                                    searchInput.value = this.dataset.name;
                                    display.textContent = 'Selected: '+this.dataset.name+' (ID '+this.dataset.id+')';
                                    results.innerHTML = '';
                                });
                            });
                        });
                    }, 350);
                });
            }

            // Apply button
            const applyBtn = document.getElementById('zsuCoinAdjustBtn');
            const msgBox   = document.getElementById('zsuCoinAdjustMsg');
            if ( applyBtn ) {
                applyBtn.addEventListener('click', function(){
                    const uid    = document.getElementById('zsuCoinVendorId').value;
                    const amount = document.getElementById('zsuCoinAmount').value;
                    const reason = document.getElementById('zsuCoinReason').value;
                    if ( ! uid ) { alert('Please select a vendor first.'); return; }
                    if ( ! amount || parseInt(amount,10) === 0 ) { alert('Amount cannot be zero.'); return; }
                    applyBtn.disabled = true;
                    applyBtn.textContent = 'Saving…';
                    jQuery.post(ajaxurl, {
                        action:  'zsu_admin_adjust_coins',
                        nonce:   ZSU_ADMIN.nonce,
                        user_id: uid,
                        amount:  amount,
                        reason:  reason || 'admin_adjustment'
                    }, function(res){
                        applyBtn.disabled = false;
                        applyBtn.textContent = '💾 Apply';
                        msgBox.style.display = 'block';
                        if ( res && res.success ) {
                            msgBox.style.background = 'rgba(16,185,129,0.15)';
                            msgBox.style.border     = '1px solid #10b981';
                            msgBox.style.color      = '#10b981';
                            msgBox.textContent = '✅ Done! ' + res.user_name + ' new balance: ' + res.balance_after + ' coins (was ' + res.balance_before + ')';
                            display.textContent = '';
                            document.getElementById('zsuCoinAmount').value = '';
                            document.getElementById('zsuCoinReason').value = '';
                        } else {
                            msgBox.style.background = 'rgba(239,68,68,0.15)';
                            msgBox.style.border     = '1px solid #ef4444';
                            msgBox.style.color      = '#ef4444';
                            msgBox.textContent = '❌ ' + (res.message || 'Something went wrong.');
                        }
                    });
                });
            }
        })();
        </script>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ANALYTICS PAGE (stub — full content in Chunk 4)
    // ═══════════════════════════════════════════════════════════════════
    // ═══════════════════════════════════════════════════════════════
    //  PAGE: ANALYTICS
    // ═══════════════════════════════════════════════════════════════
    public static function page_analytics(): void {
        ?>
        <div class="zsu-admin-wrap" id="zsu-analytics-page">
            <?php self::render_header( 'Analytics', '📈' ); ?>

            <!-- CONTROLS -->
            <div class="zsu-filter-bar">
                <div class="zsu-filter-group">
                    <label style="color:#64748b;font-size:12px;font-weight:600;">DATE RANGE</label>
                    <select id="zsuAnalyticsDays" class="zsu-select">
                        <option value="7">Last 7 days</option>
                        <option value="14">Last 14 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="60">Last 60 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
                <button class="zsu-btn zsu-btn-sm zsu-btn-primary" id="zsuAnalyticsRefresh">🔄 Refresh</button>
                <button class="zsu-btn zsu-btn-sm zsu-btn-ghost"   id="zsuAnalyticsExport">⬇ Export CSV</button>
            </div>

            <!-- SUMMARY CARDS -->
            <div class="zsu-stat-grid zsu-stat-grid-4" id="zsuAnalyticsCards">
                <div class="zsu-spinner-center" style="grid-column:1/-1;"><div class="zsu-spinner"></div></div>
            </div>

            <!-- MAIN LINE CHART -->
            <div class="zsu-section">
                <div class="zsu-section-header">
                    <h2 class="zsu-section-title">📈 Events Over Time</h2>
                    <div class="zsu-chart-legend-toggles" id="zsuChartToggles"></div>
                </div>
                <div class="zsu-chart-card" style="height:300px;">
                    <canvas id="zsuAnalyticsLineChart"></canvas>
                </div>
            </div>

            <!-- DEVICE + BROWSER SPLIT -->
            <div class="zsu-analytics-row">
                <div class="zsu-section" style="flex:1;min-width:280px;">
                    <h2 class="zsu-section-title">📱 Device Split</h2>
                    <div style="max-width:260px;margin:0 auto;">
                        <canvas id="zsuDeviceChart"></canvas>
                    </div>
                    <div id="zsuDeviceLegend" class="zsu-donut-legend"></div>
                </div>
                <div class="zsu-section" style="flex:1;min-width:280px;">
                    <h2 class="zsu-section-title">🌐 Browser Split</h2>
                    <div style="max-width:260px;margin:0 auto;">
                        <canvas id="zsuBrowserChart"></canvas>
                    </div>
                    <div id="zsuBrowserLegend" class="zsu-donut-legend"></div>
                </div>
                <div class="zsu-section" style="flex:1;min-width:280px;">
                    <h2 class="zsu-section-title">🏆 Top Vendors</h2>
                    <div id="zsuTopVendorsWrap">
                        <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
                    </div>
                </div>
            </div>

            <!-- TOP 10 SHORTS TABLE -->
            <div class="zsu-section">
                <div class="zsu-section-header">
                    <h2 class="zsu-section-title">🎬 Top 10 Shorts</h2>
                </div>
                <div class="zsu-table-wrap" id="zsuAnalyticsTopTable">
                    <div class="zsu-spinner-center"><div class="zsu-spinner"></div></div>
                </div>
            </div>

            <!-- ENGAGEMENT BAR CHART -->
            <div class="zsu-section">
                <h2 class="zsu-section-title">📊 Engagement Breakdown (Top 10)</h2>
                <div class="zsu-chart-card" style="height:280px;">
                    <canvas id="zsuEngagementChart"></canvas>
                </div>
            </div>

        </div>

        <style>
        .zsu-analytics-row {
            display: flex;
            gap: 0;
            flex-wrap: wrap;
            margin: 0 24px;
        }
        .zsu-analytics-row .zsu-section {
            margin: 20px 8px;
        }
        .zsu-donut-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
            justify-content: center;
        }
        .zsu-donut-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #94a3b8;
        }
        .zsu-donut-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .zsu-vendor-rank-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .zsu-vendor-rank-row:last-child { border-bottom: none; }
        .zsu-vendor-rank-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--zsu-primary);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        </style>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PRESETS PAGE (stub — full content in Chunk 4)
    // ═══════════════════════════════════════════════════════════════════
    // ═══════════════════════════════════════════════════════════════
    //  PAGE: PRESETS
    // ═══════════════════════════════════════════════════════════════
    public static function page_presets(): void {
        $active  = zsu_opt( 'zsu_preset_active', 'clean_white' );
        $wpreset = zsu_opt( 'zsu_wheel_preset',  'same' );

        // ── 5 White + 5 Black presets ──────────────────────────
        $presets = [
            // White themes
            'pure_white'  => ['#111827','#f59e0b','#ffffff','Pure White',   'Crisp white with ink-black type'],
            'frost'       => ['#1e40af','#3b82f6','#f8faff','Frost',        'Cool navy on ice-white tint'],
            'ivory'       => ['#92400e','#d97706','#fffdf7','Ivory',        'Warm amber on soft cream'],
            'blush'       => ['#be185d','#f43f5e','#fff5f7','Blush',        'Deep rose on blush white'],
            'sage'        => ['#065f46','#10b981','#f0fdf9','Sage',         'Forest green on mint white'],
            // Black themes
            'obsidian'    => ['#f9fafb','#f59e0b','#09090b','Obsidian',     'Pure white on deep black'],
            'midnight'    => ['#818cf8','#a78bfa','#0f0f1a','Midnight',     'Indigo glow on midnight black'],
            'noir'        => ['#e5e7eb','#d97706','#111111','Noir',         'Warm grey text on true black'],
            'slate_dark'  => ['#38bdf8','#0ea5e9','#0c1a2e','Slate Dark',   'Sky blue on deep navy'],
            'carbon'      => ['#86efac','#22c55e','#0a1208','Carbon',       'Neon green on carbon black'],
        ];
        ?>
        <div class="zsu-admin-wrap" id="zsu-presets-page">
            <?php self::render_header( 'Visual Presets', '🎨' ); ?>

            <div style="padding:0 24px 20px;">
                <p style="color:#6b7280;font-size:13px;margin:0;">
                    Choose a visual theme for your Shorts rail and Spin Wheel.
                    Active: <strong style="color:#111827;"><?php echo esc_html( $presets[$active][3] ?? $active ); ?></strong>
                </p>
            </div>

            <!-- Rail Presets Grid -->
            <div style="padding:0 24px 8px;">
                <p style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin:0 0 14px;">Rail Theme</p>
            </div>
            <div class="zsu-presets-grid">
                <?php foreach ( $presets as $key => [ $p1, $p2, $bg, $name, $desc ] ) :
                    $is_active = $key === $active;
                ?>
                <div class="zsu-preset-card <?php echo $is_active ? 'zpc-active' : ''; ?>"
                     data-preset="<?php echo esc_attr($key); ?>"
                     style="--p1:<?php echo esc_attr($p1); ?>;--bg:<?php echo esc_attr($bg); ?>;">

                    <!-- Colour preview strip -->
                    <div class="zpc-preview" style="background:<?php echo esc_attr($bg); ?>;">
                        <div class="zpc-phone">
                            <div class="zpc-phone-top" style="background:<?php echo esc_attr($bg); ?>;border-bottom:1px solid rgba(0,0,0,.07);">
                                <div class="zpc-dot" style="background:<?php echo esc_attr($p1); ?>;width:28px;"></div>
                                <div class="zpc-dot" style="background:rgba(0,0,0,.12);width:18px;"></div>
                            </div>
                            <div class="zpc-phone-body" style="background:<?php echo esc_attr($bg); ?>;">
                                <div class="zpc-card-row">
                                    <div class="zpc-mini-card" style="background:rgba(0,0,0,.06);border-radius:8px;transform:rotateY(20deg) scale(.82);"></div>
                                    <div class="zpc-mini-card zpc-main" style="background:linear-gradient(135deg,<?php echo esc_attr($p1); ?>22,<?php echo esc_attr($p2); ?>18);border:1px solid <?php echo esc_attr($p1); ?>30;border-radius:10px;"></div>
                                    <div class="zpc-mini-card" style="background:rgba(0,0,0,.06);border-radius:8px;transform:rotateY(-20deg) scale(.82);"></div>
                                </div>
                                <div class="zpc-cta" style="background:<?php echo esc_attr($p1); ?>;border-radius:100px;"></div>
                            </div>
                        </div>
                        <?php if($is_active): ?>
                        <div class="zpc-active-badge">✓ Active</div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="zpc-info">
                        <div class="zpc-swatches">
                            <div class="zpc-swatch" style="background:<?php echo esc_attr($p1); ?>;"></div>
                            <div class="zpc-swatch" style="background:<?php echo esc_attr($p2); ?>;"></div>
                            <div class="zpc-swatch" style="background:<?php echo esc_attr($bg); ?>;border:1px solid #e5e7eb;"></div>
                        </div>
                        <div class="zpc-name"><?php echo esc_html($name); ?></div>
                        <div class="zpc-desc"><?php echo esc_html($desc); ?></div>
                        <button class="zsu-btn <?php echo $is_active ? 'zsu-btn-ghost' : 'zsu-btn-primary'; ?> zsu-apply-preset-btn"
                                data-preset="<?php echo esc_attr($key); ?>"
                                <?php echo $is_active ? 'disabled' : ''; ?>>
                            <?php echo $is_active ? '✓ Applied' : 'Apply'; ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Wheel Theme Selector -->
            <div style="padding:24px 24px 8px;border-top:1px solid #f3f4f6;margin-top:8px;">
                <p style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin:0 0 6px;">Spin Wheel Theme</p>
                <p style="font-size:12px;color:#6b7280;margin:0 0 14px;">Independent theme for the spin wheel popup.</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php
                    $wopts = array_merge(['same'=>'Same as Rail'], array_column($presets, 3, null));
                    // rebuild with key => label
                    $wlabels = ['same'=>'Same as Rail'];
                    foreach($presets as $k=>$v) $wlabels[$k]=$v[3];
                    foreach($wlabels as $wk=>$wl): $wa=($wpreset===$wk); ?>
                    <button class="zsu-wheel-preset-btn <?php echo $wa?'zwp-active':''; ?>"
                            data-wheel-preset="<?php echo esc_attr($wk); ?>">
                        <?php echo esc_html($wl); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <style>
        /* Preset grid */
        .zsu-presets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            padding: 0 24px 24px;
        }
        .zsu-preset-card {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            transition: border-color .15s, box-shadow .15s;
            cursor: default;
        }
        .zsu-preset-card:hover {
            border-color: #9ca3af;
            box-shadow: 0 4px 16px rgba(0,0,0,.07);
        }
        .zsu-preset-card.zpc-active {
            border-color: var(--p1);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--p1) 15%, transparent);
        }
        /* Preview area */
        .zpc-preview {
            height: 140px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .zpc-phone {
            width: 86px;
            background: #fff;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,.1);
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
        }
        .zpc-phone-top {
            padding: 6px 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .zpc-dot { height: 3px; border-radius: 100px; }
        .zpc-phone-body { padding: 8px; }
        .zpc-card-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            margin-bottom: 7px;
            perspective: 200px;
        }
        .zpc-mini-card { width: 18px; height: 32px; flex-shrink: 0; }
        .zpc-main { width: 24px; height: 40px; }
        .zpc-cta { height: 5px; margin: 0 4px; }
        .zpc-active-badge {
            position: absolute; top: 8px; right: 8px;
            background: #065f46; color: #fff;
            font-size: 10px; font-weight: 700;
            padding: 2px 8px; border-radius: 100px;
        }
        /* Info section */
        .zpc-info { padding: 12px 13px 13px; }
        .zpc-swatches { display: flex; gap: 5px; margin-bottom: 8px; }
        .zpc-swatch { width: 18px; height: 18px; border-radius: 50%; }
        .zpc-name { font-size: 12px; font-weight: 700; color: #111827; margin-bottom: 3px; }
        .zpc-desc { font-size: 11px; color: #6b7280; margin-bottom: 10px; line-height: 1.4; }
        /* Wheel preset buttons */
        .zsu-wheel-preset-btn {
            font-size: 12px; font-weight: 600;
            padding: 6px 14px; border-radius: 100px;
            border: 1.5px solid #e5e7eb;
            background: #fff; color: #374151;
            cursor: pointer;
            transition: border-color .12s, background .12s, color .12s;
        }
        .zsu-wheel-preset-btn:hover { border-color: #9ca3af; }
        .zsu-wheel-preset-btn.zwp-active {
            border-color: #111827;
            background: #111827;
            color: #fff;
        }
        </style>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════
    //  PAGE: SETTINGS
    // ═══════════════════════════════════════════════════════════════
    private static function _input_style(): string {
        return 'width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#fff;font-size:.78rem;box-sizing:border-box;';
    }

    public static function page_settings(): void {
        ?>
        <div class="zsu-admin-wrap" id="zsu-settings-page">
            <?php self::render_header( 'Plugin Settings', '⚙️' ); ?>

            <div class="zsu-settings-layout">

                <!-- SIDEBAR TABS -->
                <div class="zsu-settings-sidebar">
                    <?php
                    $tabs = [
                        'general'   => [ '⚙️',  'General'    ],
                        'design'    => [ '🎨',  'Design'     ],
                        'vendor'    => [ '🏪',  'Vendor'     ],
                        'boost'     => [ '🚀',  'Boost & Coins' ],
                        'features'  => [ '✨',  'Features'   ],
                        'email'     => [ '📧',  'Email'      ],
                        'advanced'  => [ '🔧',  'Advanced'   ],
                        'homerail'  => [ '🎠',  'Home Rail'  ],
                        'spinwheel' => [ '🎡',  'Spin Wheel' ],
                    ];
                    foreach ( $tabs as $tab => [ $icon, $label ] ) :
                    ?>
                    <button class="zsu-settings-tab-btn <?php echo $tab === 'general' ? 'active' : ''; ?>"
                            data-tab="<?php echo esc_attr($tab); ?>">
                        <?php echo $icon . ' ' . esc_html($label); ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- SETTINGS PANELS -->
                <div class="zsu-settings-panels">

                    <!-- ── General ───────────────────────────────── -->
                    <div class="zsu-settings-panel active" id="zsuTab-general">
                        <h2 class="zsu-settings-panel-title">⚙️ General Settings</h2>
                        <?php
                        self::settings_row( 'zsu_shorts_page_slug', 'Shorts Page Slug', 'text',
                            zsu_opt('zsu_shorts_page_slug','shorts'),
                            'The URL slug for your shorts page. e.g. /shorts' );
                        self::settings_row( 'zsu_grid_columns', 'Grid Columns', 'number',
                            zsu_opt('zsu_grid_columns',2), 'Number of columns in shorts grid (1–4)', ['min'=>1,'max'=>4] );
                        self::settings_row( 'zsu_videos_per_page', 'Videos Per Page', 'number',
                            zsu_opt('zsu_videos_per_page',24), 'Shorts loaded per infinite scroll batch', ['min'=>4,'max'=>100] );
                        self::settings_toggle( 'zsu_autoplay',       'Autoplay Videos',        zsu_opt('zsu_autoplay',1) );
                        self::settings_toggle( 'zsu_muted_start',    'Start Muted',            zsu_opt('zsu_muted_start',1) );
                        self::settings_toggle( 'zsu_loop',           'Loop Videos',            zsu_opt('zsu_loop',1) );
                        self::settings_toggle( 'zsu_show_featured',  'Show Featured Rail',     zsu_opt('zsu_show_featured',1) );
                        self::settings_row( 'zsu_featured_count', 'Featured Count', 'number',
                            zsu_opt('zsu_featured_count',8), 'How many featured shorts to show in hero rail', ['min'=>2,'max'=>20] );
                        self::settings_toggle( 'zsu_show_categories','Show Category Rails',    zsu_opt('zsu_show_categories',1) );
                        self::settings_toggle( 'zsu_show_follow_btn','Show Follow Button',     zsu_opt('zsu_show_follow_btn',1) );
                        self::settings_toggle( 'zsu_show_share_btn', 'Show Share Button',      zsu_opt('zsu_show_share_btn',1) );
                        self::settings_toggle( 'zsu_show_like_btn',  'Show Like Button',       zsu_opt('zsu_show_like_btn',1) );
                        self::settings_row( 'zsu_product_tray_items','Product Tray Items','number',
                            zsu_opt('zsu_product_tray_items',6),'Max products shown in shoppable tray',['min'=>1,'max'=>20] );
                        ?>
                    </div>

                    <!-- ── Design ────────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-design">
                        <h2 class="zsu-settings-panel-title">🎨 Design Settings</h2>
                        <div class="zsu-settings-row">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">Primary Color</span>
                                <span class="zsu-settings-hint">Main brand color used for buttons, glows, accents</span>
                            </div>
                            <div class="zsu-settings-control">
                                <input type="text" class="zsu-color-picker-input" id="zsu_primary_color"
                                       name="zsu_primary_color" value="<?php echo esc_attr( zsu_opt('zsu_primary_color','#7c3aed') ); ?>">
                            </div>
                        </div>
                        <div class="zsu-settings-row">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">Section Headings Color</span>
                                <span class="zsu-settings-hint">Color of all section headings on Shorts page (Featured, Latest Shorts, Categories)</span>
                            </div>
                            <div class="zsu-settings-control">
                                <input type="text" class="zsu-color-picker-input" id="zsu_heading_color"
                                       name="zsu_heading_color" value="<?php echo esc_attr( zsu_opt('zsu_heading_color','#ffffff') ); ?>">
                            </div>
                        </div>
                        <div class="zsu-settings-row">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">Accent Color</span>
                                <span class="zsu-settings-hint">Secondary highlight color</span>
                            </div>
                            <div class="zsu-settings-control">
                                <input type="text" class="zsu-color-picker-input" id="zsu_accent_color"
                                       name="zsu_accent_color" value="<?php echo esc_attr( zsu_opt('zsu_accent_color','#f59e0b') ); ?>">
                            </div>
                        </div>
                        <div class="zsu-settings-row">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">Background Gradient Start</span>
                                <span class="zsu-settings-hint">Top color of background gradient</span>
                            </div>
                            <div class="zsu-settings-control">
                                <input type="text" class="zsu-color-picker-input" id="zsu_gradient_start"
                                       name="zsu_gradient_start" value="<?php echo esc_attr( zsu_opt('zsu_gradient_start','#0f0c29') ); ?>">
                            </div>
                        </div>
                        <div class="zsu-settings-row">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">Background Gradient End</span>
                                <span class="zsu-settings-hint">Bottom color of background gradient</span>
                            </div>
                            <div class="zsu-settings-control">
                                <input type="text" class="zsu-color-picker-input" id="zsu_gradient_end"
                                       name="zsu_gradient_end" value="<?php echo esc_attr( zsu_opt('zsu_gradient_end','#302b63') ); ?>">
                            </div>
                        </div>
                        <?php
                        self::settings_row( 'zsu_font_family', 'Font Family', 'text',
                            zsu_opt('zsu_font_family','Inter'), 'Google Font name — must be loaded separately' );
                        self::settings_row( 'zsu_border_radius', 'Card Border Radius (px)', 'number',
                            zsu_opt('zsu_border_radius',20), '', ['min'=>0,'max'=>50] );
                        self::settings_select( 'zsu_animation_level', 'Animation Level',
                            zsu_opt('zsu_animation_level','ultra'),
                            [ 'off'=>'Off', 'low'=>'Low', 'medium'=>'Medium', 'high'=>'High', 'ultra'=>'Ultra ✨' ] );
                        self::settings_toggle( 'zsu_particle_bg',   'Particle Background',  zsu_opt('zsu_particle_bg',1) );
                        self::settings_toggle( 'zsu_glassmorphism', 'Glassmorphism Cards',  zsu_opt('zsu_glassmorphism',1) );
                        self::settings_toggle( 'zsu_neon_glow',     'Neon Glow Effects',    zsu_opt('zsu_neon_glow',1) );
                        self::settings_toggle( 'zsu_tilt_cards',    '3D Tilt Cards',        zsu_opt('zsu_tilt_cards',1) );
                        self::settings_toggle( 'zsu_confetti_like', 'Confetti on Like',     zsu_opt('zsu_confetti_like',1) );
                        self::settings_toggle( 'zsu_sound_effects', 'Sound Effects',        zsu_opt('zsu_sound_effects',0) );
                        ?>
                    </div>

                    <!-- ── Vendor ─────────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-vendor">
                        <h2 class="zsu-settings-panel-title">🏪 Vendor Settings</h2>
                        <?php
                        self::settings_toggle( 'zsu_dokan_studio_tab', 'Enable Dokan Studio Tab',  zsu_opt('zsu_dokan_studio_tab',1) );
                        self::settings_toggle( 'zsu_auto_approve',     'Auto-Approve Uploads',     zsu_opt('zsu_auto_approve',0) );
                        self::settings_row( 'zsu_vendor_max_upload', 'Max Uploads Per Vendor', 'number',
                            zsu_opt('zsu_vendor_max_upload',100), '', ['min'=>1,'max'=>10000] );
                        self::settings_row( 'zsu_allowed_formats', 'Allowed Video Formats', 'text',
                            zsu_opt('zsu_allowed_formats','mp4,webm,mov'), 'Comma-separated extensions' );
                        self::settings_row( 'zsu_max_file_size_mb', 'Max File Size (MB)', 'number',
                            zsu_opt('zsu_max_file_size_mb',150), '', ['min'=>1,'max'=>2000] );
                        self::settings_toggle( 'zsu_watermark',      'Apply Watermark',          zsu_opt('zsu_watermark',0) );
                        self::settings_row( 'zsu_watermark_text', 'Watermark Text', 'text',
                            zsu_opt('zsu_watermark_text','MarbleMandi'), '' );
                        self::settings_row( 'zsu_watermark_opacity', 'Watermark Opacity (%)', 'number',
                            zsu_opt('zsu_watermark_opacity',30), '', ['min'=>5,'max'=>100] );
                        self::settings_select( 'zsu_watermark_position', 'Watermark Position',
                            zsu_opt('zsu_watermark_position','bottom_right'),
                            [ 'top_left'=>'Top Left','top_right'=>'Top Right',
                              'bottom_left'=>'Bottom Left','bottom_right'=>'Bottom Right','center'=>'Center' ] );
                        ?>
                    </div>

                    <!-- ── Boost & Coins ──────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-boost">
                        <h2 class="zsu-settings-panel-title">🚀 Boost & Coins Settings</h2>
                        <?php
                        self::settings_toggle( 'zsu_boost_enabled',      'Enable Boost System',      zsu_opt('zsu_boost_enabled',1) );
                        self::settings_row( 'zsu_coins_per_purchase', 'Coins Earned per Purchase','number',
                            zsu_opt('zsu_coins_per_purchase',10), 'Buyer earns X coins per WooCommerce order', ['min'=>0] );
                        self::settings_row( 'zsu_coins_per_review',   'Coins Earned per Review',  'number',
                            zsu_opt('zsu_coins_per_review',5),  'User earns X coins for leaving a review', ['min'=>0] );
                        self::settings_row( 'zsu_coins_per_share',    'Coins Earned per Share',    'number',
                            zsu_opt('zsu_coins_per_share',2),   'User earns X coins for sharing (once per short)', ['min'=>0] );
                        ?>
                        <div class="zsu-settings-divider"></div>
                        <h3 style="color:#f59e0b;font-size:14px;font-weight:700;margin:16px 0 10px;">🔥 Mega Boost Price (Coins per 7 days)</h3>
                        <?php
                        self::settings_row( 'zsu_boost_price_mega',  '🔥 Mega Boost Price',  'number', zsu_opt('zsu_boost_price_mega',2499), 'Coins required per 7 days for Mega Boost', ['min'=>1] );
                        ?>
                        <div class="zsu-settings-divider"></div>
                        <h3 style="color:#25d366;font-size:14px;font-weight:700;margin:16px 0 10px;">💬 Coin Purchase — WhatsApp Settings</h3>
                        <?php
                        self::settings_row( 'zsu_whatsapp_number',  '📱 WhatsApp Number (with country code)', 'text',   zsu_opt('zsu_whatsapp_number','919999999999'), 'e.g. 919876543210 (India: 91 + number, no +)' );
                        self::settings_row( 'zsu_coin_rate_1',    '💰 Price per 1 Coin (₹)',    'number', zsu_opt('zsu_coin_rate_1',1.00),  'Per-coin rate for custom amounts', ['min'=>0,'step'=>0.01] );
                        self::settings_row( 'zsu_coin_rate_100',  '💰 Price for 100 Coins (₹)', 'number', zsu_opt('zsu_coin_rate_100',90.00), '', ['min'=>0,'step'=>0.01] );
                        self::settings_row( 'zsu_coin_rate_500',  '💰 Price for 500 Coins (₹)', 'number', zsu_opt('zsu_coin_rate_500',400.00), '', ['min'=>0,'step'=>0.01] );
                        self::settings_row( 'zsu_coin_rate_1000', '💰 Price for 1000 Coins (₹)','number', zsu_opt('zsu_coin_rate_1000',750.00),'', ['min'=>0,'step'=>0.01] );
                        ?>
                    </div>

                    <!-- ── Features ───────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-features">
                        <h2 class="zsu-settings-panel-title">✨ Feature Toggles</h2>
                        <?php
                        self::settings_toggle( 'zsu_enable_comments',  'Comments on Shorts',   zsu_opt('zsu_enable_comments',0) );
                        self::settings_toggle( 'zsu_enable_ratings',   'Star Ratings',         zsu_opt('zsu_enable_ratings',1) );
                        self::settings_toggle( 'zsu_enable_wishlists', 'Product Wishlists',    zsu_opt('zsu_enable_wishlists',1) );
                        self::settings_toggle( 'zsu_show_comment_btn', 'Show Comment Button',  zsu_opt('zsu_show_comment_btn',0) );
                        self::settings_toggle( 'zsu_heatmap_enabled',  'Watch Heatmap',        zsu_opt('zsu_heatmap_enabled',1) );
                        self::settings_toggle( 'zsu_ab_testing',       'A/B Thumbnail Testing',zsu_opt('zsu_ab_testing',0) );
                        self::settings_toggle( 'zsu_seo_schema',       'Video SEO Schema',     zsu_opt('zsu_seo_schema',1) );
                        self::settings_toggle( 'zsu_lazy_load',        'Lazy Load Videos',     zsu_opt('zsu_lazy_load',1) );
                        self::settings_toggle( 'zsu_preload_next',     'Preload Next Video',   zsu_opt('zsu_preload_next',1) );
                        ?>
                    </div>

                    <!-- ── Email ──────────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-email">
                        <h2 class="zsu-settings-panel-title">📧 Email Notifications</h2>
                        <?php
                        self::settings_toggle( 'zsu_email_on_upload',  'Email on Upload',         zsu_opt('zsu_email_on_upload',1) );
                        self::settings_toggle( 'zsu_email_on_boost',   'Email on Boost Request',  zsu_opt('zsu_email_on_boost',1) );
                        self::settings_toggle( 'zsu_email_on_approve', 'Email on Approval',       zsu_opt('zsu_email_on_approve',1) );
                        self::settings_toggle( 'zsu_email_on_reject',  'Email on Rejection',      zsu_opt('zsu_email_on_reject',1) );
                        ?>
                    </div>

                    <!-- ── Advanced ───────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-advanced">
                        <h2 class="zsu-settings-panel-title">🔧 Advanced Settings</h2>
                        <?php
                        self::settings_row( 'zsu_analytics_retention', 'Analytics Retention (days)', 'number',
                            zsu_opt('zsu_analytics_retention',90), 'Events older than this are pruned daily', ['min'=>7,'max'=>365] );
                        self::settings_row( 'zsu_trending_window', 'Trending Window (days)', 'number',
                            zsu_opt('zsu_trending_window',7), 'How many days back to calculate trending score', ['min'=>1,'max'=>30] );
                        self::settings_row( 'zsu_trending_threshold', 'Trending Threshold (score)', 'number',
                            zsu_opt('zsu_trending_threshold',50), 'Minimum score to show in trending feed', ['min'=>1] );
                        self::settings_row( 'zsu_cdn_url', 'CDN Base URL', 'url',
                            zsu_opt('zsu_cdn_url',''), 'Optional CDN to replace upload URLs (e.g. https://cdn.example.com/)' );
                        ?>
                        <div class="zsu-settings-divider"></div>
                        <div class="zsu-settings-row">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">⚠️ Danger Zone</span>
                                <span class="zsu-settings-hint">These actions cannot be undone</span>
                            </div>
                            <div class="zsu-settings-control" style="display:flex;gap:8px;flex-wrap:wrap;">
                                <button class="zsu-btn zsu-btn-ghost" id="zsuRecreateTables">🔄 Recreate DB Tables</button>
                                <button class="zsu-btn zsu-btn-ghost" id="zsuRegenerateAssets">⚡ Regenerate Assets</button>
                                <button class="zsu-btn zsu-btn-ghost" id="zsuFlushRewrites">🔀 Flush Rewrites</button>
                            </div>
                        </div>
                    </div>


                    <!-- ── Home Rail ──────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-homerail">
                        <h2 class="zsu-settings-panel-title">🎠 Home Rail Settings</h2>
                        <?php
                        self::settings_row( 'zsu_home_rail_title', 'Section Title', 'text',
                            zsu_opt('zsu_home_rail_title','Top influencer picks'), 'Title shown above the carousel' );
                        self::settings_row( 'zsu_home_rail_cta_label', 'CTA Button Label', 'text',
                            zsu_opt('zsu_home_rail_cta_label','View All Shorts'), 'Text on the "View All" button' );
                        self::settings_row( 'zsu_home_rail_views_min', 'Min Views to Show Badge', 'number',
                            zsu_opt('zsu_home_rail_views_min',0), 'Views badge hidden if views < this. 0 = always show', ['min'=>0] );
                        self::settings_row( 'zsu_home_rail_show_views', 'Show Views Count', 'toggle',
                            zsu_opt('zsu_home_rail_show_views',1), 'Show or hide the views count badge on cards' );
                        self::settings_row( 'zsu_home_rail_views_multiplier', 'Views Display Multiplier', 'number',
                            zsu_opt('zsu_home_rail_views_multiplier',1), 'Multiply displayed view count (e.g. 10 = show 10× real views). Use 1 for real count.', ['min'=>1,'max'=>10000,'step'=>1] );
                        self::settings_row( 'zsu_home_rail_views_floor', 'Views Floor (Minimum Displayed)', 'number',
                            zsu_opt('zsu_home_rail_views_floor',0), 'Minimum views shown on any card. 0 = disabled.', ['min'=>0] );
                        self::settings_select( 'zsu_home_rail_default_filter', 'Default Filter',
                            zsu_opt('zsu_home_rail_default_filter','trending'),
                            ['trending'=>'🔥 Trending','latest'=>'🆕 Latest','popular'=>'👁 Popular'],
                            'Which filter tab loads by default' );
                        self::settings_row( 'zsu_home_rail_count', 'Cards to Load', 'number',
                            zsu_opt('zsu_home_rail_count',8), 'How many shorts to fetch for the rail (2–20)', ['min'=>2,'max'=>20] );
                        self::settings_select( 'zsu_home_rail_animation', 'Card Animation Level',
                            zsu_opt('zsu_home_rail_animation','medium'),
                            ['none'=>'None — Static','low'=>'Low — Subtle','medium'=>'Medium — Default','high'=>'High — Vivid'],
                            'Controls transition speed and 3D tilt depth' );
                        self::settings_toggle( 'zsu_home_rail_show_likes',   'Show Like Button',   zsu_opt('zsu_home_rail_show_likes',1) );
                        self::settings_toggle( 'zsu_home_rail_show_follow',  'Show Follow Button', zsu_opt('zsu_home_rail_show_follow',1) );
                        self::settings_toggle( 'zsu_home_rail_show_products','Show Products Badge',zsu_opt('zsu_home_rail_show_products',1) );
                        self::settings_toggle( 'zsu_home_rail_autoplay',     'Auto-rotate Carousel',zsu_opt('zsu_home_rail_autoplay',1) );
                        self::settings_row( 'zsu_home_rail_autoplay_speed', 'Auto-rotate Speed (ms)', 'number',
                            zsu_opt('zsu_home_rail_autoplay_speed',3200), 'Milliseconds between auto-rotations', ['min'=>1000,'max'=>10000,'step'=>100] );
                        ?>
                    </div>

                    <!-- ── Spin Wheel ──────────────────────────────── -->
                    <div class="zsu-settings-panel" id="zsuTab-spinwheel">
                        <h2 class="zsu-settings-panel-title">🎡 Spin Wheel Settings</h2>
                        <p style="color:rgba(255,255,255,.5);font-size:.8rem;margin-bottom:20px;">
                            Configure up to 10 prize segments. Each segment needs a label, colour, type and coupon code.
                        </p>

                        <?php
                        self::settings_row( 'zsu_spin_wheel_title', 'Wheel Title', 'text',
                            zsu_opt('zsu_spin_wheel_title','Spin & Win'), 'Heading shown on the spin modal' );
                        self::settings_row( 'zsu_spin_wheel_subtitle', 'Wheel Subtitle', 'text',
                            zsu_opt('zsu_spin_wheel_subtitle','One free spin every 24 hours'), 'Subheading shown on the spin modal' );
                        self::settings_toggle( 'zsu_spin_wheel_enabled', 'Enable Spin Wheel', zsu_opt('zsu_spin_wheel_enabled',1) );
                        self::settings_row( 'zsu_spin_cooldown_hours', 'Cooldown (hours)', 'number',
                            zsu_opt('zsu_spin_cooldown_hours',24), 'Hours between spins per IP', ['min'=>1,'max'=>168] );
                        ?>

                        <!-- Prize editor -->
                        <div class="zsu-settings-divider"></div>
                        <div class="zsu-settings-row" style="align-items:flex-start;">
                            <div class="zsu-settings-label">
                                <span class="zsu-settings-key">Prize Segments</span>
                                <span class="zsu-settings-hint">Up to 10 segments — drag to reorder</span>
                            </div>
                            <div class="zsu-settings-control" style="flex:1;min-width:0;">
                                <div id="zsuPrizeList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;">
                                    <?php
                                    $raw   = zsu_opt('zsu_spin_wheel_prizes','');
                                    $prizes = ($raw && is_array(json_decode($raw,true))) ? json_decode($raw,true) : [
                                        ['label'=>'10% OFF',   'color'=>'#6366f1','type'=>'coupon','value'=>'SPIN10'  ],
                                        ['label'=>'₹50 Cash',  'color'=>'#0891b2','type'=>'coupon','value'=>'CASH50'  ],
                                        ['label'=>'Free Ship', 'color'=>'#059669','type'=>'coupon','value'=>'FREESHIP'],
                                        ['label'=>'20% OFF',   'color'=>'#d97706','type'=>'coupon','value'=>'SPIN20'  ],
                                        ['label'=>'Try Again', 'color'=>'#374151','type'=>'none',  'value'=>''        ],
                                        ['label'=>'15% OFF',   'color'=>'#dc2626','type'=>'coupon','value'=>'SPIN15'  ],
                                        ['label'=>'₹100 Cash', 'color'=>'#7c3aed','type'=>'coupon','value'=>'CASH100' ],
                                        ['label'=>'Lucky Dip', 'color'=>'#0d9488','type'=>'coupon','value'=>'LUCKY'   ],
                                    ];
                                    foreach($prizes as $pi=>$prize):
                                    ?>
                                    <div class="zsu-prize-row" data-idx="<?php echo $pi; ?>" style="display:grid;grid-template-columns:24px 1fr 1fr 90px 1fr 36px;gap:6px;align-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:8px 10px;">
                                        <span style="cursor:move;color:rgba(255,255,255,.3);font-size:14px;user-select:none;">⠿</span>
                                        <input type="text" name="prizes[<?php echo $pi; ?>][label]"
                                               value="<?php echo esc_attr($prize['label']); ?>"
                                               placeholder="Label" style="<?php echo self::_input_style(); ?>">
                                        <input type="color" name="prizes[<?php echo $pi; ?>][color]"
                                               value="<?php echo esc_attr($prize['color']); ?>"
                                               style="height:34px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:transparent;cursor:pointer;width:100%;">
                                        <select name="prizes[<?php echo $pi; ?>][type]" style="<?php echo self::_input_style(); ?>">
                                            <option value="coupon" <?php selected($prize['type'],'coupon'); ?>>Coupon</option>
                                            <option value="none"   <?php selected($prize['type'],'none');   ?>>No Prize</option>
                                        </select>
                                        <input type="text" name="prizes[<?php echo $pi; ?>][value]"
                                               value="<?php echo esc_attr($prize['value']); ?>"
                                               placeholder="COUPONCODE" style="<?php echo self::_input_style(); ?>;text-transform:uppercase;font-family:monospace;">
                                        <button type="button" class="zsu-prize-remove" style="width:32px;height:32px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:transparent;color:rgba(255,255,255,.4);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">×</button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button" id="zsuAddPrize"
                                        style="padding:8px 16px;border-radius:8px;border:1px dashed rgba(255,255,255,.2);background:transparent;color:rgba(255,255,255,.5);cursor:pointer;font-size:.75rem;width:100%;">
                                    + Add Segment
                                </button>

                                <input type="hidden" name="zsu_spin_wheel_prizes" id="zsuPrizesJSON" value="<?php echo esc_attr($raw?:json_encode($prizes)); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- SAVE BUTTON -->
                    <div class="zsu-settings-save-bar">
                        <button class="zsu-btn zsu-btn-lg zsu-btn-primary" id="zsuSaveSettings">
                            💾 Save All Settings
                        </button>
                        <span id="zsuSettingsSavedMsg" style="display:none;color:#10b981;font-size:13px;font-weight:600;">
                            ✅ Settings saved successfully!
                        </span>
                    </div>

                </div><!-- .zsu-settings-panels -->
            </div><!-- .zsu-settings-layout -->

        </div>

        <style>
        .zsu-settings-layout {
            display: flex;
            gap: 0;
            padding: 0 24px 40px;
            align-items: flex-start;
        }

        .zsu-settings-sidebar {
            width: 200px;
            flex-shrink: 0;
            position: sticky;
            top: 110px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding-right: 16px;
        }

        .zsu-settings-tab-btn {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s;
            width: 100%;
        }

        .zsu-settings-tab-btn:hover { background: rgba(255,255,255,0.05); color: #e2e8f0; }
        .zsu-settings-tab-btn.active { background: rgba(124,58,237,0.15); color: #a78bfa; font-weight: 700; }

        .zsu-settings-panels {
            flex: 1;
            min-width: 0;
        }

        .zsu-settings-panel {
            display: none;
            background: #1a1d2e;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .zsu-settings-panel.active { display: block; }

        .zsu-settings-panel-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin: 0 0 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .zsu-settings-row {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .zsu-settings-row:last-of-type { border-bottom: none; }

        .zsu-settings-label {
            flex: 1;
            min-width: 180px;
        }

        .zsu-settings-key {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 3px;
        }

        .zsu-settings-hint {
            display: block;
            font-size: 11px;
            color: #475569;
            line-height: 1.4;
        }

        .zsu-settings-control {
            flex-shrink: 0;
            min-width: 220px;
        }

        .zsu-settings-input {
            background: #252840;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e2e8f0;
            padding: 8px 12px;
            font-size: 13px;
            width: 100%;
            max-width: 280px;
            outline: none;
            transition: border-color 0.2s;
        }
        .zsu-settings-input:focus { border-color: #7c3aed; }

        .zsu-settings-select {
            background: #252840;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e2e8f0;
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
            outline: none;
            max-width: 280px;
            width: 100%;
        }

        /* Toggle switch */
        .zsu-toggle-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zsu-toggle {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 26px;
        }

        .zsu-toggle input { display: none; }

        .zsu-toggle-slider {
            position: absolute;
            inset: 0;
            background: #334155;
            border-radius: 99px;
            cursor: pointer;
            transition: 0.3s;
        }

        .zsu-toggle-slider::before {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            left: 3px; bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: 0.3s;
        }

        .zsu-toggle input:checked + .zsu-toggle-slider { background: #7c3aed; }
        .zsu-toggle input:checked + .zsu-toggle-slider::before { transform: translateX(20px); }

        .zsu-toggle-label { font-size: 12px; color: #64748b; }

        .zsu-settings-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 16px 0;
        }

        .zsu-settings-save-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 0;
        }

        @media (max-width: 900px) {
            .zsu-settings-layout { flex-direction: column; padding: 0 12px 40px; }
            .zsu-settings-sidebar { width: 100%; position: static; flex-direction: row; flex-wrap: wrap; padding-right: 0; }
            .zsu-settings-tab-btn { width: auto; flex: 0 0 auto; }
        }
        </style>
        <?php
    }

    // ─── Settings field helpers ───────────────────────────────────
    private static function settings_row( string $key, string $label, string $type, $val, string $hint = '', array $attrs = [] ): void {
        $attr_str = '';
        foreach ( $attrs as $a => $v ) {
            $attr_str .= ' ' . esc_attr($a) . '="' . esc_attr($v) . '"';
        }
        ?>
        <div class="zsu-settings-row">
            <div class="zsu-settings-label">
                <span class="zsu-settings-key"><?php echo esc_html($label); ?></span>
                <?php if ($hint) : ?><span class="zsu-settings-hint"><?php echo esc_html($hint); ?></span><?php endif; ?>
            </div>
            <div class="zsu-settings-control">
                <input type="<?php echo esc_attr($type); ?>"
                       class="zsu-settings-input"
                       id="<?php echo esc_attr($key); ?>"
                       name="<?php echo esc_attr($key); ?>"
                       value="<?php echo esc_attr($val); ?>"
                       <?php echo $attr_str; ?>>
            </div>
        </div>
        <?php
    }

    private static function settings_toggle( string $key, string $label, $val ): void {
        ?>
        <div class="zsu-settings-row">
            <div class="zsu-settings-label">
                <span class="zsu-settings-key"><?php echo esc_html($label); ?></span>
            </div>
            <div class="zsu-settings-control">
                <label class="zsu-toggle-wrap">
                    <label class="zsu-toggle">
                        <input type="checkbox"
                               class="zsu-settings-toggle-input"
                               id="<?php echo esc_attr($key); ?>"
                               name="<?php echo esc_attr($key); ?>"
                               value="1"
                               <?php checked( (bool) $val ); ?>>
                        <span class="zsu-toggle-slider"></span>
                    </label>
                    <span class="zsu-toggle-label"><?php echo $val ? 'Enabled' : 'Disabled'; ?></span>
                </label>
            </div>
        </div>
        <?php
    }

    private static function settings_select( string $key, string $label, $val, array $options ): void {
        ?>
        <div class="zsu-settings-row">
            <div class="zsu-settings-label">
                <span class="zsu-settings-key"><?php echo esc_html($label); ?></span>
            </div>
            <div class="zsu-settings-control">
                <select class="zsu-settings-select" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>">
                    <?php foreach ( $options as $opt_val => $opt_label ) : ?>
                    <option value="<?php echo esc_attr($opt_val); ?>" <?php selected( $val, $opt_val ); ?>>
                        <?php echo esc_html($opt_label); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    //  SHARED: RENDER PAGE HEADER
    // ═══════════════════════════════════════════════════════════════════
    private static function render_header( string $title, string $icon = '⚡' ): void {
        $active_page = sanitize_key( $_GET['page'] ?? 'zsu-dashboard' );
        $nav_items   = [
            'zsu-dashboard'  => [ '📊', 'Dashboard'   ],
            'zsu-shorts'     => [ '🎬', 'Shorts'      ],
            'zsu-pending'    => [ '⏳', 'Pending'     ],
            'zsu-boosts'     => [ '🚀', 'Boosts'      ],
            'zsu-coins'      => [ '🪙', 'Coins'       ],
            'zsu-analytics'  => [ '📈', 'Analytics'   ],
            'zsu-presets'    => [ '🎨', 'Presets'     ],
            'zsu-settings'   => [ '⚙️', 'Settings'    ],
        ];
        ?>
        <div class="zsu-admin-header">
            <div class="zsu-admin-logo">
                <span class="zsu-logo-icon">⚡</span>
                <span class="zsu-logo-text">Zilla Shorts <span class="zsu-version">v<?php echo ZSU_VER; ?></span></span>
            </div>
            <nav class="zsu-admin-nav">
                <?php foreach ( $nav_items as $page => [ $ni, $nl ] ) : ?>
                <a href="<?php echo esc_url( admin_url("admin.php?page={$page}") ); ?>"
                   class="zsu-nav-item <?php echo $active_page === $page ? 'active' : ''; ?>">
                    <?php echo $ni . ' ' . esc_html($nl); ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="zsu-page-title">
            <h1><?php echo $icon . ' ' . esc_html( $title ); ?></h1>
        </div>
        <!-- Admin toast container -->
        <div id="zsuToast" class="zsu-toast" style="display:none;"></div>
        <?php
    }
}