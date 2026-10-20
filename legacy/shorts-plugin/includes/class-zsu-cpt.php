<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_CPT — registers the custom post type, taxonomy, meta boxes, and cron tasks.
 */
class ZSU_CPT {

    public static function init(): void {
        add_action( 'init', [ self::class, 'register_cpt' ],        0 );
        add_action( 'init', [ self::class, 'register_taxonomy' ],   0 );
        add_action( 'init', [ self::class, 'maybe_flush_rewrites' ], 99 );

        // Meta boxes
        add_action( 'add_meta_boxes',           [ self::class, 'register_meta_boxes' ] );
        add_action( 'save_post_' . ZSU_CPT,     [ self::class, 'save_meta_box' ], 10, 2 );

        // Cron callbacks
        add_action( 'zsu_daily_cron',  [ self::class, 'daily_tasks' ] );
        add_action( 'zsu_hourly_cron', [ self::class, 'hourly_tasks' ] );

        // Register cron if not already scheduled (post-activation safety net)
        ZSU_Install::register_cron();

        // WooCommerce coins on order complete
        add_action( 'woocommerce_order_status_completed', [ self::class, 'grant_coins_on_purchase' ] );

        // Dokan coins on sale
        add_action( 'dokan_order_status_completed', [ self::class, 'vendor_coins_on_sale' ], 10, 2 );
    }

    // ─── CPT Registration ─────────────────────────────────────────────
    public static function register_cpt(): void {
        register_post_type( ZSU_CPT, [
            'labels'             => [
                'name'               => __( 'Shorts',            'zsu' ),
                'singular_name'      => __( 'Short',             'zsu' ),
                'add_new'            => __( 'Add Short',         'zsu' ),
                'add_new_item'       => __( 'Add New Short',     'zsu' ),
                'edit_item'          => __( 'Edit Short',        'zsu' ),
                'new_item'           => __( 'New Short',         'zsu' ),
                'view_item'          => __( 'View Short',        'zsu' ),
                'search_items'       => __( 'Search Shorts',     'zsu' ),
                'not_found'          => __( 'No shorts found',   'zsu' ),
                'not_found_in_trash' => __( 'No shorts in trash','zsu' ),
                'menu_name'          => __( 'Zilla Shorts',      'zsu' ),
            ],
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_rest'       => true,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'hierarchical'       => false,
            'supports'           => [ 'title', 'thumbnail', 'author', 'custom-fields', 'excerpt' ],
            'rewrite'            => [ 'slug' => 'zsu-short', 'with_front' => false ],
            'has_archive'        => false,
            'query_var'          => true,
            'menu_icon'          => 'dashicons-video-alt3',
        ] );
    }

    // ─── Taxonomy Registration ────────────────────────────────────────
    public static function register_taxonomy(): void {
        register_taxonomy( ZSU_TAX, ZSU_CPT, [
            'labels'            => [
                'name'              => __( 'Short Categories',    'zsu' ),
                'singular_name'     => __( 'Short Category',     'zsu' ),
                'search_items'      => __( 'Search Categories',  'zsu' ),
                'all_items'         => __( 'All Categories',     'zsu' ),
                'parent_item'       => __( 'Parent Category',    'zsu' ),
                'parent_item_colon' => __( 'Parent Category:',   'zsu' ),
                'edit_item'         => __( 'Edit Category',      'zsu' ),
                'update_item'       => __( 'Update Category',    'zsu' ),
                'add_new_item'      => __( 'Add New Category',   'zsu' ),
                'new_item_name'     => __( 'New Category Name',  'zsu' ),
                'menu_name'         => __( 'Categories',         'zsu' ),
            ],
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'short-cat' ],
        ] );
    }

    // ─── Flush rewrites once after activation ─────────────────────────
    public static function maybe_flush_rewrites(): void {
        if ( get_option( 'zsu_flush_rewrites' ) === '1' ) {
            flush_rewrite_rules();
            delete_option( 'zsu_flush_rewrites' );
        }
    }

    // ─── Meta Boxes ───────────────────────────────────────────────────
    public static function register_meta_boxes(): void {
        add_meta_box(
            'zsu_video_meta',
            '🎬 Short Video Details',
            [ self::class, 'video_meta_box_html' ],
            ZSU_CPT,
            'normal',
            'high'
        );
    }

    public static function video_meta_box_html( \WP_Post $post ): void {
        wp_nonce_field( 'zsu_save_meta', 'zsu_meta_nonce' );
        $meta = zsu_get_short_meta( $post->ID );
        ?>
        <style>
        .zsu-meta-table { width:100%; border-collapse:collapse; }
        .zsu-meta-table th { width:160px; font-weight:600; padding:10px 12px; text-align:left; vertical-align:top; color:#1e293b; }
        .zsu-meta-table td { padding:8px 12px; }
        .zsu-meta-table tr { border-bottom:1px solid #f1f5f9; }
        .zsu-meta-table input[type=url],
        .zsu-meta-table input[type=text],
        .zsu-meta-table input[type=number] { width:100%; max-width:500px; padding:7px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; }
        .zsu-meta-table input[type=url]:focus,
        .zsu-meta-table input[type=text]:focus,
        .zsu-meta-table input[type=number]:focus { outline:none; border-color:#7c3aed; box-shadow:0 0 0 2px #7c3aed22; }
        </style>
        <table class="zsu-meta-table">
            <tr>
                <th><label for="zsu_video_url">🎥 Video URL</label></th>
                <td><input type="url" id="zsu_video_url" name="zsu_video_url" value="<?php echo esc_attr( $meta['video_url'] ); ?>"></td>
            </tr>
            <tr>
                <th><label for="zsu_thumb_url">🖼 Thumbnail URL</label></th>
                <td><input type="url" id="zsu_thumb_url" name="zsu_thumb_url" value="<?php echo esc_attr( $meta['thumb_url'] ); ?>"></td>
            </tr>
            <tr>
                <th><label for="zsu_product_ids">🛒 Product IDs</label></th>
                <td>
                    <input type="text" id="zsu_product_ids" name="zsu_product_ids" value="<?php echo esc_attr( implode( ',', (array) $meta['product_ids'] ) ); ?>" placeholder="12,34,56">
                    <p style="font-size:11px;color:#64748b;margin:4px 0 0">Comma-separated WooCommerce product IDs for shoppable tray.</p>
                </td>
            </tr>
            <tr>
                <th><label for="zsu_vendor_id">🏪 Vendor ID</label></th>
                <td><input type="number" id="zsu_vendor_id" name="zsu_vendor_id" value="<?php echo esc_attr( $meta['vendor_id'] ); ?>" min="0"></td>
            </tr>
            <tr>
                <th><label for="zsu_duration">⏱ Duration (s)</label></th>
                <td><input type="number" id="zsu_duration" name="zsu_duration" value="<?php echo esc_attr( $meta['duration'] ); ?>" min="0" max="3600"></td>
            </tr>
            <tr>
                <th>⭐ Featured</th>
                <td>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="zsu_featured" value="1" <?php checked( $meta['is_featured'] ); ?>>
                        <span style="font-size:13px">Pin as Featured Short</span>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_meta_box( int $post_id, \WP_Post $post ): void {
        if ( ! isset( $_POST['zsu_meta_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zsu_meta_nonce'] ) ), 'zsu_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = [
            'zsu_video_url' => '_zsu_video_url',
            'zsu_thumb_url' => '_zsu_thumb_url',
        ];
        foreach ( $fields as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $meta_key, esc_url_raw( wp_unslash( $_POST[ $post_key ] ) ) );
            }
        }

        if ( isset( $_POST['zsu_vendor_id'] ) ) {
            update_post_meta( $post_id, '_zsu_vendor_id', absint( $_POST['zsu_vendor_id'] ) );
        }
        if ( isset( $_POST['zsu_duration'] ) ) {
            update_post_meta( $post_id, '_zsu_duration', absint( $_POST['zsu_duration'] ) );
        }

        update_post_meta( $post_id, '_zsu_featured', isset( $_POST['zsu_featured'] ) ? 1 : 0 );

        if ( isset( $_POST['zsu_product_ids'] ) ) {
            $ids = array_values( array_unique( array_filter(
                array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['zsu_product_ids'] ) ) ) )
            ) ) );
            update_post_meta( $post_id, '_zsu_product_ids', $ids );
        }
    }

    // ─── Daily Cron ───────────────────────────────────────────────────
    public static function daily_tasks(): void {
        global $wpdb;

        // Expire old boosts
        $now = current_time( 'mysql' );
        $just_expired_short_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT short_id FROM {$wpdb->prefix}zsu_boosts
             WHERE ends_at < %s
               AND status   = 'active'",
            $now
        ) );

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}zsu_boosts
             SET status = 'expired'
             WHERE ends_at < %s
               AND status   = 'active'",
            $now
        ) );

        // Clear the cached "_zsu_is_boosted" flag on any short that no longer has
        // another still-active boost, so it stops showing as Elite/boosted anywhere.
        foreach ( $just_expired_short_ids as $short_id ) {
            zsu_sync_boost_meta( (int) $short_id );
        }

        // Prune old analytics events
        $retention = absint( zsu_opt( 'zsu_analytics_retention', 90 ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}zsu_events
             WHERE created_at < DATE_SUB( NOW(), INTERVAL %d DAY )",
            $retention
        ) );

        // Recalculate trending scores
        zsu_recalculate_trending();

        // Update A/B test CTR
        self::update_ab_ctr();
    }

    // ─── Hourly Cron ─────────────────────────────────────────────────
    public static function hourly_tasks(): void {
        global $wpdb;

        // Activate pending boosts whose start time has arrived
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}zsu_boosts
             SET status = 'active'
             WHERE starts_at <= %s
               AND status    = 'pending'",
            current_time( 'mysql' )
        ) );
    }

    // ─── Update A/B test CTR ──────────────────────────────────────────
    private static function update_ab_ctr(): void {
        global $wpdb;
        $wpdb->query(
            "UPDATE {$wpdb->prefix}zsu_ab_tests
             SET ctr = CASE WHEN views > 0 THEN ( clicks / views ) * 100 ELSE 0 END"
        );
    }

    // ─── Coins on WooCommerce purchase ───────────────────────────────
    public static function grant_coins_on_purchase( int $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;
        $user_id = $order->get_user_id();
        if ( ! $user_id ) return;
        $coins = absint( zsu_opt( 'zsu_coins_per_purchase', 10 ) );
        if ( $coins > 0 ) {
            ZSU_Coins::add( $user_id, $coins, 'purchase_reward', $order_id );
        }
    }

    // ─── Vendor coins on Dokan sale ───────────────────────────────────
    public static function vendor_coins_on_sale( int $order_id, int $vendor_id ): void {
        ZSU_Coins::add( $vendor_id, 5, 'sale_reward', $order_id );
    }
}