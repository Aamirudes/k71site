<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Install — handles activation, deactivation, DB creation, defaults, asset generation.
 */
class ZSU_Install {

    // ─── Activation ──────────────────────────────────────────────────
    public static function activate(): void {
        self::create_tables();
        self::set_defaults();
        self::ensure_upload_dir();
        self::generate_asset_files();
        self::register_cron();
        update_option( 'zsu_db_version',     ZSU_DB_VER );
        update_option( 'zsu_activated_at',   current_time( 'mysql' ) );
        update_option( 'zsu_flush_rewrites', '1' );
        flush_rewrite_rules();
    }

    // ─── Deactivation ────────────────────────────────────────────────
    public static function deactivate(): void {
        wp_clear_scheduled_hook( 'zsu_daily_cron' );
        wp_clear_scheduled_hook( 'zsu_hourly_cron' );
        flush_rewrite_rules();
    }

    // ─── Cron schedule ───────────────────────────────────────────────
    public static function register_cron(): void {
        if ( ! wp_next_scheduled( 'zsu_daily_cron' ) ) {
            wp_schedule_event( time(), 'daily', 'zsu_daily_cron' );
        }
        if ( ! wp_next_scheduled( 'zsu_hourly_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'zsu_hourly_cron' );
        }
    }

    // ─── Upload directory ─────────────────────────────────────────────
    public static function ensure_upload_dir(): void {
        if ( ! is_dir( ZSU_UPLOAD_DIR ) ) {
            wp_mkdir_p( ZSU_UPLOAD_DIR );
        }
        $htaccess = ZSU_UPLOAD_DIR . '.htaccess';
        $index    = ZSU_UPLOAD_DIR . 'index.php';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess, "Options -Indexes\n" );
        }
        if ( ! file_exists( $index ) ) {
            file_put_contents( $index, "<?php // silence\n" );
        }
    }

    // ─── DB Tables ───────────────────────────────────────────────────
    public static function create_tables(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ── Analytics events ──────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_events (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            short_id    BIGINT UNSIGNED NOT NULL,
            event_type  VARCHAR(30)     NOT NULL DEFAULT 'view',
            user_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
            session_id  VARCHAR(64)     NOT NULL DEFAULT '',
            device      VARCHAR(20)     NOT NULL DEFAULT 'unknown',
            browser     VARCHAR(50)     NOT NULL DEFAULT '',
            country     VARCHAR(4)      NOT NULL DEFAULT '',
            referrer    VARCHAR(500)    NOT NULL DEFAULT '',
            ip_hash     VARCHAR(64)     NOT NULL DEFAULT '',
            watch_pct   TINYINT UNSIGNED NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY short_id  (short_id),
            KEY event_type(event_type),
            KEY created_at(created_at),
            KEY user_id   (user_id)
        ) {$charset};" );

        // ── Boost campaigns ───────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_boosts (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            short_id    BIGINT UNSIGNED NOT NULL,
            vendor_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
            boost_type  VARCHAR(30)     NOT NULL DEFAULT 'standard',
            coins_spent INT             NOT NULL DEFAULT 0,
            starts_at   DATETIME        NOT NULL,
            ends_at     DATETIME        NOT NULL,
            status      VARCHAR(20)     NOT NULL DEFAULT 'pending',
            impressions INT             NOT NULL DEFAULT 0,
            clicks      INT             NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY short_id (short_id),
            KEY vendor_id(vendor_id),
            KEY status   (status),
            KEY ends_at  (ends_at)
        ) {$charset};" );

        // ── Coins ledger ──────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_coins (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id     BIGINT UNSIGNED NOT NULL,
            delta       INT             NOT NULL DEFAULT 0,
            balance     INT             NOT NULL DEFAULT 0,
            reason      VARCHAR(100)    NOT NULL DEFAULT '',
            ref_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
            meta        TEXT,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id  (user_id),
            KEY created_at(created_at)
        ) {$charset};" );

        // ── Follows ───────────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_follows (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            follower_id BIGINT UNSIGNED NOT NULL,
            vendor_id   BIGINT UNSIGNED NOT NULL,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY  follow_pair (follower_id, vendor_id),
            KEY vendor_id(vendor_id),
            KEY follower_id(follower_id)
        ) {$charset};" );

        // ── Shares ────────────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_shares (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            short_id    BIGINT UNSIGNED NOT NULL,
            platform    VARCHAR(40)     NOT NULL DEFAULT 'generic',
            user_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
            session_id  VARCHAR(64)     NOT NULL DEFAULT '',
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY short_id  (short_id),
            KEY platform  (platform),
            KEY created_at(created_at)
        ) {$charset};" );

        // ── Comments on shorts ────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_comments (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            short_id    BIGINT UNSIGNED NOT NULL,
            user_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
            parent_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
            content     TEXT            NOT NULL,
            status      VARCHAR(20)     NOT NULL DEFAULT 'approved',
            likes       INT             NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY short_id (short_id),
            KEY user_id  (user_id),
            KEY status   (status)
        ) {$charset};" );

        // ── Wishlists ─────────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_wishlists (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id     BIGINT UNSIGNED NOT NULL,
            product_id  BIGINT UNSIGNED NOT NULL,
            short_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY  wish_pair (user_id, product_id),
            KEY user_id (user_id)
        ) {$charset};" );

        // ── A/B test variants ─────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_ab_tests (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            short_id    BIGINT UNSIGNED NOT NULL,
            variant     VARCHAR(1)      NOT NULL DEFAULT 'A',
            thumb_url   VARCHAR(500)    NOT NULL DEFAULT '',
            views       INT             NOT NULL DEFAULT 0,
            clicks      INT             NOT NULL DEFAULT 0,
            ctr         FLOAT           NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY short_id(short_id)
        ) {$charset};" );

        // ── Watch heatmap ─────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}zsu_heatmap (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            short_id    BIGINT UNSIGNED NOT NULL,
            second_mark SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            watch_count INT              NOT NULL DEFAULT 0,
            updated_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY  heatmap_pair (short_id, second_mark),
            KEY short_id(short_id)
        ) {$charset};" );

        update_option( 'zsu_db_version', ZSU_DB_VER );
    }

    // ─── Default Settings ─────────────────────────────────────────────
    public static function set_defaults(): void {
        $defaults = [
            // General
            'zsu_shorts_page_slug'      => 'shorts',
            'zsu_grid_columns'          => 2,
            'zsu_videos_per_page'       => 24,
            'zsu_autoplay'              => 1,
            'zsu_muted_start'           => 1,
            'zsu_loop'                  => 1,
            'zsu_show_featured'         => 1,
            'zsu_featured_count'        => 8,
            'zsu_show_categories'       => 1,
            'zsu_show_follow_btn'       => 1,
            'zsu_show_share_btn'        => 1,
            'zsu_show_like_btn'         => 1,
            'zsu_show_comment_btn'      => 0,
            'zsu_product_tray_items'    => 6,
            'zsu_shortcode_page_id'     => 0,

            // Design
            'zsu_primary_color'         => '#7c3aed',
            'zsu_accent_color'          => '#f59e0b',
            'zsu_gradient_start'        => '#0f0c29',
            'zsu_gradient_end'          => '#302b63',
            'zsu_font_family'           => 'Inter',
            'zsu_border_radius'         => 20,
            'zsu_animation_level'       => 'ultra',
            'zsu_particle_bg'           => 1,
            'zsu_glassmorphism'         => 1,
            'zsu_neon_glow'             => 1,
            'zsu_tilt_cards'            => 1,
            'zsu_confetti_like'         => 1,
            'zsu_sound_effects'         => 0,
            'zsu_preset_active'         => 'neon_dark',

            // Boost & Coins
            'zsu_boost_enabled'         => 1,
            'zsu_coins_per_purchase'    => 10,
            'zsu_coins_per_review'      => 5,
            'zsu_coins_per_share'       => 2,
            'zsu_boost_price_std'       => 199,
            'zsu_boost_price_pro'       => 499,
            'zsu_boost_price_ultra'     => 999,
            'zsu_boost_price_mega'      => 2499,

            // Vendor
            'zsu_dokan_studio_tab'      => 1,
            'zsu_auto_approve'          => 0,
            'zsu_vendor_max_upload'     => 100,
            'zsu_allowed_formats'       => 'mp4,webm,mov',
            'zsu_max_file_size_mb'      => 150,
            'zsu_watermark'             => 0,
            'zsu_watermark_text'        => 'MarbleMandi',
            'zsu_watermark_opacity'     => 30,
            'zsu_watermark_position'    => 'bottom_right',

            // Advanced
            'zsu_analytics_retention'   => 90,
            'zsu_trending_window'       => 7,
            'zsu_trending_threshold'    => 50,
            'zsu_lazy_load'             => 1,
            'zsu_preload_next'          => 1,
            'zsu_seo_schema'            => 1,
            'zsu_cdn_url'               => '',
            'zsu_heatmap_enabled'       => 1,
            'zsu_ab_testing'            => 0,

            // Features
            'zsu_enable_comments'       => 0,
            'zsu_enable_ratings'        => 1,
            'zsu_enable_wishlists'      => 1,
            'zsu_enable_duet'           => 0,
            'zsu_enable_stories'        => 0,
            'zsu_enable_live'           => 0,

            // Email
            'zsu_email_on_upload'       => 1,
            'zsu_email_on_boost'        => 1,
            'zsu_email_on_approve'      => 1,
            'zsu_email_on_reject'       => 1,
        ];

        foreach ( $defaults as $key => $val ) {
            if ( false === get_option( $key ) ) {
                update_option( $key, $val );
            }
        }
    }

    // ─── Generate physical CSS/JS asset files on activation ───────────
    // This is the KEY FIX: instead of inline styles/scripts we write
    // proper asset files to the plugin's assets directory.
    public static function generate_asset_files(): void {
        $assets_dir = ZSU_ASSETS_DIR;

        foreach ( [ 'css', 'js', 'fonts' ] as $sub ) {
            if ( ! is_dir( $assets_dir . $sub ) ) {
                wp_mkdir_p( $assets_dir . $sub );
            }
        }

        // Write stub files — real content is in Chunks 4 (admin CSS/JS)
        // and Chunk 6/7 (frontend CSS/JS). These stubs ensure the files
        // exist so wp_enqueue_style/script never fail silently.
        $stubs = [
            'css/zsu-admin.css'    => '/* ZSU Admin CSS - generated */',
            'css/zsu-frontend.css' => '/* ZSU Frontend CSS - generated */',
            'js/zsu-admin.js'      => '/* ZSU Admin JS - generated */',
            'js/zsu-frontend.js'   => '/* ZSU Frontend JS - generated */',
            'js/zsu-vendor.js'     => '/* ZSU Vendor Studio JS - generated */',
        ];

        foreach ( $stubs as $rel => $content ) {
            $path = $assets_dir . $rel;
            if ( ! file_exists( $path ) ) {
                file_put_contents( $path, $content );
            }
        }
    }
}