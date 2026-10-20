<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Ajax — registers and handles every AJAX action.
 *
 * Public actions  → available to logged-out users too (nopriv)
 * Private actions → require login
 * Admin actions   → require manage_options
 */
class ZSU_Ajax {

    // ─── Public actions (logged in + logged out) ──────────────────────
    private const PUBLIC_ACTIONS = [
        'zsu_get_shorts',
        'zsu_get_featured',
        'zsu_get_cat_rail',
        'zsu_record_view',
        'zsu_record_like',
        'zsu_record_share',
        'zsu_record_watch_pct',
        'zsu_get_products',
        'zsu_get_short_data',
        'zsu_follow_vendor',
        'zsu_unfollow_vendor',
        'zsu_check_follow',
        'zsu_get_comments',
        'zsu_post_comment',
        'zsu_rate_short',
        'zsu_wishlist_toggle',
        'zsu_search_shorts',
        'zsu_update_heatmap', // moved to public so logged-out viewers don't get 400 errors
    ];

    // ─── Private actions (login required) ────────────────────────────
    private const PRIVATE_ACTIONS = [
        'zsu_upload_short',
        'zsu_save_short',
        'zsu_vendor_delete_short',
        'zsu_vendor_update_short',
        'zsu_vendor_get_shorts',
        'zsu_vendor_analytics',
        'zsu_request_boost',
        'zsu_vendor_get_boosts',
        'zsu_get_coins_balance',
        'zsu_coins_history',
        'zsu_get_wishlist',
        'zsu_vendor_search_products',
    ];

    // ─── Admin-only actions ───────────────────────────────────────────
    private const ADMIN_ACTIONS = [
        'zsu_admin_get_stats',
        'zsu_admin_approve_short',
        'zsu_admin_reject_short',
        'zsu_admin_delete_short',
        'zsu_admin_boost_action',
        'zsu_admin_save_settings',
        'zsu_admin_apply_preset',
        'zsu_admin_save_option',
        'zsu_admin_get_boosts',
        'zsu_admin_get_pending',
        'zsu_admin_get_coins_log',
        'zsu_admin_reset_counters',
        'zsu_admin_export_analytics',
        'zsu_admin_search_products',
        'zsu_admin_adjust_coins',
        'zsu_admin_manual_boost',
        'zsu_admin_search_vendor',
        'zsu_admin_search_shorts',
    ];

    // ─── Init: register all hooks ──────────────────────────────────────
    public static function init(): void {
        foreach ( self::PUBLIC_ACTIONS as $action ) {
            add_action( 'wp_ajax_nopriv_' . $action, [ self::class, 'dispatch' ] );
            add_action( 'wp_ajax_'        . $action, [ self::class, 'dispatch' ] );
        }
        foreach ( self::PRIVATE_ACTIONS as $action ) {
            add_action( 'wp_ajax_' . $action, [ self::class, 'dispatch' ] );
        }
        foreach ( self::ADMIN_ACTIONS as $action ) {
            add_action( 'wp_ajax_' . $action, [ self::class, 'dispatch_admin' ] );
        }
    }

    // ─── Dispatcher ───────────────────────────────────────────────────
    public static function dispatch(): void {
        $action  = sanitize_key( $_REQUEST['action'] ?? '' );
        $method  = 'handle_' . $action;
        if ( method_exists( self::class, $method ) ) {
            self::$method();
        } else {
            zsu_json_error( 'Unknown action', 400 );
        }
    }

    public static function dispatch_admin(): void {
        if ( ! current_user_can( ZSU_CAP ) ) {
            zsu_json_error( 'Forbidden', 403 );
        }
        self::dispatch();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §A  GET SHORTS  (paginated, filtered, searchable)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_shorts(): void {
        $page     = max( 1, absint( $_POST['page']     ?? 1 ) );
        $per_page = min( 50, absint( $_POST['per_page'] ?? zsu_opt( 'zsu_videos_per_page', 24 ) ) );
        $cat      = sanitize_text_field( $_POST['cat']       ?? '' );
        $filter   = sanitize_key(        $_POST['filter']    ?? 'latest' );
        $vendor   = absint(              $_POST['vendor_id'] ?? 0 );
        $search   = sanitize_text_field( $_POST['search']    ?? '' );
        $exclude  = array_filter( array_map( 'absint', (array) ( $_POST['exclude'] ?? [] ) ) );

        $args = [
            'post_type'      => ZSU_CPT,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'meta_query'     => [ 'relation' => 'AND' ],
        ];

        // Search
        if ( $search ) {
            $args['s'] = $search;
        }

        // Exclude IDs
        if ( ! empty( $exclude ) ) {
            $args['post__not_in'] = $exclude;
        }

        // Category filter
        if ( $cat ) {
            $args['tax_query'] = [ [
                'taxonomy' => ZSU_TAX,
                'field'    => 'slug',
                'terms'    => $cat,
            ] ];
        }

        // Vendor filter
        if ( $vendor ) {
            $args['meta_query'][] = [
                'key'     => '_zsu_vendor_id',
                'value'   => $vendor,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ];
        }

        // Ordering
        switch ( $filter ) {
            case 'trending':
                $args['meta_key'] = '_zsu_trending_score';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'popular':
                $args['meta_key'] = '_zsu_views';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'featured':
                $args['meta_query'][] = [
                    'key'     => '_zsu_featured',
                    'value'   => '1',
                    'compare' => '=',
                ];
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
            case 'boosted':
                // Boosted posts appear first, then rest
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
            default: // latest
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
        }

        $query  = new WP_Query( $args );
        $shorts = [];

        foreach ( $query->posts as $post ) {
            $data = self::build_short_data( $post );
            if ( $data ) {
                $shorts[] = $data;
            }
        }

        // For 'boosted' filter, sort so boosted items come first
        if ( $filter === 'boosted' ) {
            usort( $shorts, static fn( $a, $b ) => (int) $b['is_boosted'] - (int) $a['is_boosted'] );
        }

        // Inject boosted/manual-boosted shorts ONLY for non-latest, non-featured filters on page 1
        // 'latest' feed shows pure chronological order — no promotional injection
        if ( $page === 1 && $filter !== 'boosted' && $filter !== 'latest' ) {

            // ── MANUAL BOOST: admin-pinned shorts always go first ──────────
            $manual_query = new WP_Query( [
                'post_type'      => ZSU_CPT,
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'meta_query'     => [ [
                    'key'     => '_zsu_manual_boost',
                    'value'   => '1',
                    'compare' => '=',
                ] ],
                'orderby' => 'date',
                'order'   => 'DESC',
            ] );
            if ( $manual_query->have_posts() ) {
                $manual_shorts = [];
                foreach ( $manual_query->posts as $mp ) {
                    $md = self::build_short_data( $mp );
                    if ( $md ) {
                        $md['is_manual_boost'] = true;
                        $manual_shorts[] = $md;
                    }
                }
                if ( ! empty( $manual_shorts ) ) {
                    $manual_ids = array_column( $manual_shorts, 'id' );
                    $shorts     = array_filter( $shorts, fn( $s ) => ! in_array( $s['id'], $manual_ids, true ) );
                    $shorts     = array_merge( $manual_shorts, array_values( $shorts ) );
                }
            }
            global $wpdb;
            $now = current_time( 'mysql' );
            $boosted_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT short_id FROM {$wpdb->prefix}zsu_boosts
                 WHERE status = 'active' AND starts_at <= %s AND ends_at >= %s
                 ORDER BY boost_type DESC, created_at DESC
                 LIMIT 5",
                $now, $now
            ) );

            if ( ! empty( $boosted_ids ) ) {
                // Track impression for each boosted short
                foreach ( $boosted_ids as $bid ) {
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}zsu_boosts SET impressions = impressions + 1
                         WHERE short_id = %d AND status = 'active' AND ends_at >= %s",
                        $bid, $now
                    ) );
                }

                // Fetch boosted shorts data (only those not already in results)
                $existing_ids = array_column( $shorts, 'id' );
                $missing_ids  = array_diff( array_map( 'intval', $boosted_ids ), $existing_ids );

                $boosted_shorts = [];
                foreach ( $boosted_ids as $bid ) {
                    $post = get_post( (int) $bid );
                    if ( ! $post || $post->post_status !== 'publish' ) continue;
                    $data = self::build_short_data( $post );
                    if ( $data ) $boosted_shorts[] = $data;
                }

                // Remove duplicates from main list, put boosted first
                $shorts = array_filter( $shorts, fn( $s ) => ! in_array( $s['id'], array_column( $boosted_shorts, 'id' ), true ) );
                $shorts = array_merge( $boosted_shorts, array_values( $shorts ) );
            }
        }

        zsu_json( [
            'shorts' => $shorts,
            'total'  => $query->found_posts,
            'pages'  => $query->max_num_pages,
            'page'   => $page,
        ] );
    }

    // ─── Build short data array from WP_Post ──────────────────────────
    private static function build_short_data( \WP_Post $post ): ?array {
        $meta = zsu_get_short_meta( $post->ID );

        if ( empty( $meta['video_url'] ) ) {
            return null;
        }

        // Thumbnail resolution order: meta → attachment → placeholder
        $thumb = $meta['thumb_url'];
        if ( ! $thumb ) {
            $thumb = get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: '';
        }
        if ( ! $thumb ) {
            $thumb = zsu_placeholder_thumb( $post->ID, $post->post_title );
        }

        // CDN URL replacement
        $cdn = zsu_opt( 'zsu_cdn_url', '' );
        if ( $cdn ) {
            $upload_base  = ZSU_UPLOAD_URL;
            $meta['video_url'] = str_replace( $upload_base, trailingslashit( $cdn ), $meta['video_url'] );
            $thumb             = str_replace( $upload_base, trailingslashit( $cdn ), $thumb );
        }

        // Vendor info
        $vendor_name      = '';
        $vendor_avatar    = '';
        $vendor_store     = '';
        $vendor_store_url = '';
        if ( $meta['vendor_id'] && function_exists( 'dokan_get_store_info' ) ) {
            $store_info       = dokan_get_store_info( $meta['vendor_id'] );
            $vendor_name      = $store_info['store_name'] ?? get_the_author_meta( 'display_name', $meta['vendor_id'] );
            $vendor_store     = $store_info['store_name'] ?? '';
            $vendor_avatar    = get_avatar_url( $meta['vendor_id'], [ 'size' => 100 ] );
            // Dokan store URL — try dokan_get_store_url(), fallback to author page
            if ( function_exists( 'dokan_get_store_url' ) ) {
                $vendor_store_url = dokan_get_store_url( $meta['vendor_id'] );
            } else {
                $vendor_store_url = get_author_posts_url( $meta['vendor_id'] );
            }
        }

        // Categories
        $cats = wp_get_post_terms( $post->ID, ZSU_TAX, [ 'fields' => 'names' ] );
        if ( is_wp_error( $cats ) ) {
            $cats = [];
        }

        return [
            'id'              => $post->ID,
            'title'           => get_the_title( $post ),
            'excerpt'         => wp_trim_words( get_the_excerpt( $post ), 20 ),
            'video_url'       => $meta['video_url'],
            'thumb_url'       => $thumb,
            'thumb_b_url'     => $meta['thumb_b_url'],
            'product_ids'     => $meta['product_ids'],
            'vendor_id'       => $meta['vendor_id'],
            'vendor_name'     => $vendor_name,
            'vendor_store'    => $vendor_store,
            'vendor_store_url'=> $vendor_store_url,
            'vendor_avatar'   => $vendor_avatar,
            'views'           => zsu_format_num( $meta['views'] ),
            'views_raw'       => $meta['views'],
            'likes'           => zsu_format_num( $meta['likes'] ),
            'likes_raw'       => $meta['likes'],
            'shares'          => zsu_format_num( $meta['shares'] ),
            'shares_raw'      => $meta['shares'],
            'clicks'          => zsu_format_num( $meta['clicks'] ),
            'comments_count'  => $meta['comments'],
            'is_featured'     => $meta['is_featured'],
            'is_boosted'      => $meta['is_boosted'],
            'trending_score'  => $meta['trending_score'],
            'ratio'           => $meta['ratio'],
            'duration'        => $meta['duration'],
            'watch_avg_pct'   => $meta['watch_avg_pct'],
            'rating_avg'      => $meta['rating_avg'],
            'rating_count'    => $meta['rating_count'],
            'ab_variant'      => $meta['ab_variant'],
            'categories'      => $cats,
            'date'            => get_the_date( 'U', $post ),
            'date_formatted'  => get_the_date( 'M j, Y', $post ),
            'permalink'       => get_permalink( $post ),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §B  GET FEATURED SHORTS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_featured(): void {
        $count = absint( zsu_opt( 'zsu_featured_count', 8 ) );

        // Featured rail shows ONLY actively boosted shorts
        $query = new WP_Query( [
            'post_type'      => ZSU_CPT,
            'post_status'    => 'publish',
            'posts_per_page' => $count,
            'meta_query'     => [
                [
                    'key'     => '_zsu_is_boosted',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $shorts = [];
        foreach ( $query->posts as $post ) {
            // Ground-truth boost check — skip (and self-heal) anything whose boost has actually
            // expired, even if the cached "_zsu_is_boosted" meta hasn't been cleared yet.
            if ( ! zsu_sync_boost_meta( $post->ID ) ) {
                continue;
            }
            $data = self::build_short_data( $post );
            if ( $data ) {
                $shorts[] = $data;
            }
        }

        zsu_json( [ 'shorts' => $shorts ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §C  GET CATEGORY RAIL
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_cat_rail(): void {
        $cat      = sanitize_text_field( $_POST['cat'] ?? '' );
        $per_page = min( 12, absint( $_POST['per_page'] ?? 8 ) );

        if ( ! $cat ) {
            zsu_json_error( 'Category required' );
        }

        $query = new WP_Query( [
            'post_type'      => ZSU_CPT,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'tax_query'      => [ [
                'taxonomy' => ZSU_TAX,
                'field'    => 'slug',
                'terms'    => $cat,
            ] ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $shorts = [];
        foreach ( $query->posts as $post ) {
            $data = self::build_short_data( $post );
            if ( $data ) {
                $shorts[] = $data;
            }
        }

        zsu_json( [ 'shorts' => $shorts, 'cat' => $cat ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §D  SEARCH SHORTS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_search_shorts(): void {
        $q        = sanitize_text_field( $_POST['q'] ?? '' );
        $per_page = min( 24, absint( $_POST['per_page'] ?? 12 ) );

        if ( strlen( $q ) < 2 ) {
            zsu_json( [ 'shorts' => [] ] );
        }

        $query = new WP_Query( [
            'post_type'      => ZSU_CPT,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            's'              => $q,
            'orderby'        => 'relevance',
        ] );

        $shorts = [];
        foreach ( $query->posts as $post ) {
            $data = self::build_short_data( $post );
            if ( $data ) {
                $shorts[] = $data;
            }
        }

        zsu_json( [ 'shorts' => $shorts, 'total' => $query->found_posts ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §E  RECORD VIEW
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_record_view(): void {
        $short_id = absint( $_POST['short_id'] ?? 0 );
        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }
        zsu_record_event( $short_id, 'view', get_current_user_id() );
        $views = (int) get_post_meta( $short_id, '_zsu_views', true );
        zsu_json( [ 'recorded' => true, 'views' => zsu_format_num( $views ) ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §F  RECORD WATCH PERCENTAGE (heatmap data)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_record_watch_pct(): void {
        $short_id   = absint( $_POST['short_id']  ?? 0 );
        $watch_pct  = min( 100, absint( $_POST['watch_pct'] ?? 0 ) );
        $second_mark = absint( $_POST['second'] ?? 0 );

        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        // Update average watch percentage
        $current_avg   = (int) get_post_meta( $short_id, '_zsu_watch_avg_pct', true );
        $current_count = (int) get_post_meta( $short_id, '_zsu_views', true ) ?: 1;
        $new_avg       = (int) round( ( ( $current_avg * ( $current_count - 1 ) ) + $watch_pct ) / $current_count );
        update_post_meta( $short_id, '_zsu_watch_avg_pct', $new_avg );

        // Heatmap second-mark
        if ( zsu_opt( 'zsu_heatmap_enabled', 1 ) && $second_mark ) {
            global $wpdb;
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}zsu_heatmap (short_id, second_mark, watch_count)
                 VALUES (%d, %d, 1)
                 ON DUPLICATE KEY UPDATE watch_count = watch_count + 1",
                $short_id,
                $second_mark
            ) );
        }

        zsu_json( [ 'recorded' => true ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §G  RECORD LIKE / UNLIKE  (toggle)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_record_like(): void {
        $short_id = absint( $_POST['short_id'] ?? 0 );
        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        $uid      = get_current_user_id();
        $like_key = 'zsu_liked_' . $short_id;

        // Determine current like state
        $already_liked = $uid
            ? (bool) get_user_meta( $uid, $like_key, true )
            : isset( $_COOKIE[ $like_key ] );

        $current = max( 0, (int) get_post_meta( $short_id, '_zsu_likes', true ) );

        if ( $already_liked ) {
            // ── Unlike ──────────────────────────────────────────────
            if ( $uid ) {
                delete_user_meta( $uid, $like_key );
            }
            setcookie( $like_key, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
            $new_count = max( 0, $current - 1 );
            update_post_meta( $short_id, '_zsu_likes', $new_count );
            zsu_json( [
                'action' => 'unliked',
                'likes'  => zsu_format_num( $new_count ),
                'raw'    => $new_count,
            ] );
        } else {
            // ── Like ────────────────────────────────────────────────
            if ( $uid ) {
                update_user_meta( $uid, $like_key, 1 );
            }
            setcookie( $like_key, '1', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
            zsu_record_event( $short_id, 'like', $uid );

            $new_count = $current + 1;
            zsu_json( [
                'action' => 'liked',
                'likes'  => zsu_format_num( $new_count ),
                'raw'    => $new_count,
            ] );
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §H  RECORD SHARE
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_record_share(): void {
        $short_id = absint( $_POST['short_id'] ?? 0 );
        $platform = sanitize_key( $_POST['platform'] ?? 'generic' );

        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        $uid = get_current_user_id();
        zsu_record_event( $short_id, 'share', $uid );

        // Log platform-specific share
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'zsu_shares',
            [
                'short_id'   => $short_id,
                'platform'   => $platform,
                'user_id'    => $uid,
                'session_id' => session_id() ?: '',
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%d', '%s', '%s' ]
        );

        $shares = (int) get_post_meta( $short_id, '_zsu_shares', true );
        zsu_json( [
            'shares'   => zsu_format_num( $shares ),
            'platform' => $platform,
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §I  GET PRODUCTS FOR SHOPPABLE TRAY
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_products(): void {
        // Support both ids[] (array) and ids (comma-string) formats
        $raw_ids  = $_POST['ids'] ?? [];
        if ( is_array( $raw_ids ) ) {
            $ids = array_filter( array_map( 'absint', $raw_ids ) );
        } else {
            $ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( $raw_ids ) ) ) );
        }
        $short_id = absint( $_POST['short_id'] ?? 0 );

        if ( empty( $ids ) ) {
            zsu_json( [ 'success' => true, 'products' => [] ] );
            return;
        }

        $max      = absint( zsu_opt( 'zsu_product_tray_items', 6 ) );
        $ids      = array_slice( $ids, 0, $max );
        $products = [];

        foreach ( $ids as $pid ) {
            if ( ! function_exists( 'wc_get_product' ) ) {
                break;
            }
            $product = wc_get_product( $pid );
            if ( ! $product || ! $product->is_visible() ) {
                continue;
            }

            $img_id  = $product->get_image_id();
            $img_url = $img_id
                ? wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' )
                : ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '' );

            // Gallery images
            $gallery_ids  = $product->get_gallery_image_ids();
            $gallery_urls = [];
            foreach ( array_slice( $gallery_ids, 0, 3 ) as $gid ) {
                $gurl = wp_get_attachment_image_url( $gid, 'woocommerce_thumbnail' );
                if ( $gurl ) {
                    $gallery_urls[] = $gurl;
                }
            }

            $products[] = [
                'id'             => $pid,
                'name'           => $product->get_name(),
                'price'          => $product->get_price_html(),
                'price_raw'      => (float) $product->get_price(),
                'sale_price'     => (float) $product->get_sale_price(),
                'regular_price'  => (float) $product->get_regular_price(),
                'on_sale'        => $product->is_on_sale(),
                'discount_pct'   => $product->is_on_sale() && $product->get_regular_price()
                    ? (int) round( ( 1 - ( (float) $product->get_sale_price() / (float) $product->get_regular_price() ) ) * 100 )
                    : 0,
                'image'          => $img_url,
                'gallery'        => $gallery_urls,
                'url'            => get_permalink( $pid ),
                'add_to_cart'    => $product->add_to_cart_url(),
                'in_stock'       => $product->is_in_stock(),
                'stock_qty'      => $product->get_stock_quantity(),
                'rating'         => (float) $product->get_average_rating(),
                'review_count'   => $product->get_review_count(),
                'sku'            => $product->get_sku(),
                'type'           => $product->get_type(),
                'badge'          => $product->is_on_sale()
                    ? ( '-' . ( (int) round( ( 1 - ( (float) $product->get_sale_price() / (float) $product->get_regular_price() ) ) * 100 ) ) . '%' )
                    : ( $product->is_featured() ? 'Featured' : '' ),
            ];
        }

        // Record product click event
        if ( $short_id ) {
            zsu_record_event( $short_id, 'click', get_current_user_id() );
        }

        zsu_json( [ 'products' => $products ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §J  GET SINGLE SHORT DATA
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_short_data(): void {
        $short_id      = absint( $_POST['short_id'] ?? 0 );
        $with_adjacent = ! empty( $_POST['with_adjacent'] );
        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        $post = get_post( $short_id );
        if ( ! $post || $post->post_type !== ZSU_CPT || $post->post_status !== 'publish' ) {
            zsu_json_error( 'Not found', 404 );
        }

        $data = self::build_short_data( $post );
        if ( ! $data ) {
            zsu_json_error( 'No video attached', 404 );
        }

        // When opened from a direct URL, also fetch predecessor + successors so user can scroll
        if ( $with_adjacent ) {
            $shorts = [ $data ];

            // Predecessors: IDs < short_id (older), order DESC → most recent first
            $prev_posts = get_posts( [
                'post_type'      => ZSU_CPT,
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'orderby'        => 'ID',
                'order'          => 'DESC',
                'post__not_in'   => [ $short_id ],
                'date_query'     => [],
                'meta_query'     => [ 'relation' => 'AND' ],
                // IDs < short_id
                'fields'         => 'all',
            ] );
            // Filter to IDs < short_id
            $prev_posts = array_filter( $prev_posts, fn( $p ) => $p->ID < $short_id );
            // Sort descending so most recent predecessor is first
            usort( $prev_posts, fn( $a, $b ) => $b->ID - $a->ID );
            $prev_posts = array_slice( $prev_posts, 0, 5 );

            // Successors: IDs > short_id (newer), order ASC
            $next_posts = get_posts( [
                'post_type'      => ZSU_CPT,
                'post_status'    => 'publish',
                'posts_per_page' => 10,
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'post__not_in'   => [ $short_id ],
            ] );
            $next_posts = array_filter( $next_posts, fn( $p ) => $p->ID > $short_id );
            usort( $next_posts, fn( $a, $b ) => $a->ID - $b->ID );
            $next_posts = array_slice( $next_posts, 0, 10 );

            // Build predecessor data (reversed so most recent is index 0)
            $prev_data = [];
            foreach ( array_reverse( $prev_posts ) as $p ) {
                $d = self::build_short_data( $p );
                if ( $d ) $prev_data[] = $d;
            }

            // Build successor data
            $next_data = [];
            foreach ( $next_posts as $p ) {
                $d = self::build_short_data( $p );
                if ( $d ) $next_data[] = $d;
            }

            // Final order: predecessors oldest→newest, focal short, successors newest→oldest
            // Swipe UP (scrollTop increases) = higher index = successors (newer/higher ID)
            // Swipe DOWN (scrollTop decreases) = lower index = predecessors (older/lower ID)
            $all_shorts  = array_merge( $prev_data, [ $data ], $next_data );
            $focal_index = count( $prev_data ); // focal is after predecessors

            zsu_json( [ 'short' => $data, 'shorts' => $all_shorts, 'focal_index' => $focal_index ] );
            return;
        }

        zsu_json( [ 'short' => $data ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §K  FOLLOW / UNFOLLOW VENDOR
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_follow_vendor(): void {
        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }

        $vendor_id   = absint( $_POST['vendor_id'] ?? 0 );
        $follower_id = get_current_user_id();

        if ( ! $vendor_id ) {
            zsu_json_error( 'Invalid vendor' );
        }

        global $wpdb;

        // Prevent duplicate
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}zsu_follows
             WHERE follower_id = %d AND vendor_id = %d",
            $follower_id, $vendor_id
        ) );

        if ( ! $exists ) {
            $wpdb->insert(
                $wpdb->prefix . 'zsu_follows',
                [
                    'follower_id' => $follower_id,
                    'vendor_id'   => $vendor_id,
                    'created_at'  => current_time( 'mysql' ),
                ],
                [ '%d', '%d', '%s' ]
            );

            // Sync with Dokan native follow system
            if ( function_exists( 'dokan_follow_store' ) ) {
                dokan_follow_store( $vendor_id );
            }
        }

        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_follows WHERE vendor_id = %d",
            $vendor_id
        ) );

        zsu_json( [
            'following' => true,
            'followers' => zsu_format_num( $count ),
            'count_raw' => $count,
        ] );
    }

    private static function handle_zsu_unfollow_vendor(): void {
        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }

        $vendor_id   = absint( $_POST['vendor_id'] ?? 0 );
        $follower_id = get_current_user_id();

        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . 'zsu_follows',
            [ 'follower_id' => $follower_id, 'vendor_id' => $vendor_id ],
            [ '%d', '%d' ]
        );

        if ( function_exists( 'dokan_unfollow_store' ) ) {
            dokan_unfollow_store( $vendor_id );
        }

        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_follows WHERE vendor_id = %d",
            $vendor_id
        ) );

        zsu_json( [
            'following' => false,
            'followers' => zsu_format_num( $count ),
            'count_raw' => $count,
        ] );
    }

    private static function handle_zsu_check_follow(): void {
        $vendor_id   = absint( $_POST['vendor_id'] ?? 0 );
        $follower_id = get_current_user_id();

        if ( ! $follower_id || ! $vendor_id ) {
            zsu_json( [ 'following' => false ] );
        }

        global $wpdb;
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}zsu_follows
             WHERE follower_id = %d AND vendor_id = %d",
            $follower_id, $vendor_id
        ) );

        zsu_json( [ 'following' => ! empty( $exists ) ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §L  COMMENTS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_comments(): void {
        $short_id = absint( $_POST['short_id'] ?? 0 );
        $page     = max( 1, absint( $_POST['page'] ?? 1 ) );
        $per_page = 20;

        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        global $wpdb;
        $offset = ( $page - 1 ) * $per_page;

        $comments = $wpdb->get_results( $wpdb->prepare(
            "SELECT c.*, u.display_name, u.user_email
             FROM {$wpdb->prefix}zsu_comments c
             LEFT JOIN {$wpdb->users} u ON u.ID = c.user_id
             WHERE c.short_id = %d AND c.status = 'approved' AND c.parent_id = 0
             ORDER BY c.created_at DESC
             LIMIT %d OFFSET %d",
            $short_id, $per_page, $offset
        ) );

        $result = [];
        foreach ( $comments as $c ) {
            $avatar = get_avatar_url( $c->user_id ?: $c->user_email, [ 'size' => 48 ] );
            $result[] = [
                'id'           => $c->id,
                'user_id'      => $c->user_id,
                'display_name' => $c->display_name ?: 'Guest',
                'avatar'       => $avatar,
                'content'      => wp_kses_post( $c->content ),
                'likes'        => (int) $c->likes,
                'date'         => human_time_diff( strtotime( $c->created_at ) ) . ' ago',
            ];
        }

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_comments
             WHERE short_id = %d AND status = 'approved'",
            $short_id
        ) );

        zsu_json( [
            'comments' => $result,
            'total'    => $total,
            'pages'    => (int) ceil( $total / $per_page ),
        ] );
    }

    private static function handle_zsu_post_comment(): void {
        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }
        if ( ! zsu_opt( 'zsu_enable_comments', 0 ) ) {
            zsu_json_error( 'Comments disabled' );
        }

        $short_id  = absint( $_POST['short_id'] ?? 0 );
        $content   = sanitize_textarea_field( $_POST['content'] ?? '' );
        $parent_id = absint( $_POST['parent_id'] ?? 0 );

        if ( ! $short_id || strlen( $content ) < 1 ) {
            zsu_json_error( 'Invalid comment' );
        }

        global $wpdb;
        $uid    = get_current_user_id();
        $status = current_user_can( 'moderate_comments' ) ? 'approved' : 'approved'; // auto-approve for now

        $wpdb->insert(
            $wpdb->prefix . 'zsu_comments',
            [
                'short_id'   => $short_id,
                'user_id'    => $uid,
                'parent_id'  => $parent_id,
                'content'    => $content,
                'status'     => $status,
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%d', '%s', '%s', '%s' ]
        );

        // Update comment count meta
        $count = (int) get_post_meta( $short_id, '_zsu_comments', true );
        update_post_meta( $short_id, '_zsu_comments', $count + 1 );

        zsu_record_event( $short_id, 'comment', $uid );

        $user   = get_userdata( $uid );
        $avatar = get_avatar_url( $uid, [ 'size' => 48 ] );

        zsu_json( [
            'comment' => [
                'id'           => $wpdb->insert_id,
                'user_id'      => $uid,
                'display_name' => $user->display_name ?? 'You',
                'avatar'       => $avatar,
                'content'      => wp_kses_post( $content ),
                'likes'        => 0,
                'date'         => 'Just now',
            ],
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §M  RATE SHORT (1-5 stars)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_rate_short(): void {
        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }
        if ( ! zsu_opt( 'zsu_enable_ratings', 1 ) ) {
            zsu_json_error( 'Ratings disabled' );
        }

        $short_id = absint( $_POST['short_id'] ?? 0 );
        $rating   = min( 5, max( 1, (int) ( $_POST['rating'] ?? 0 ) ) );

        if ( ! $short_id || ! $rating ) {
            zsu_json_error( 'Invalid rating' );
        }

        $uid       = get_current_user_id();
        $rated_key = "zsu_rated_{$short_id}";

        if ( get_user_meta( $uid, $rated_key, true ) ) {
            zsu_json_error( 'Already rated' );
        }

        update_user_meta( $uid, $rated_key, $rating );

        $current_count = (int) get_post_meta( $short_id, '_zsu_rating_count', true );
        $current_avg   = (float) get_post_meta( $short_id, '_zsu_rating_avg', true );
        $new_count     = $current_count + 1;
        $new_avg       = round( ( ( $current_avg * $current_count ) + $rating ) / $new_count, 2 );

        update_post_meta( $short_id, '_zsu_rating_count', $new_count );
        update_post_meta( $short_id, '_zsu_rating_avg',   $new_avg );

        zsu_json( [
            'rating_avg'   => $new_avg,
            'rating_count' => $new_count,
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §N  WISHLIST TOGGLE (product)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_wishlist_toggle(): void {
        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }
        if ( ! zsu_opt( 'zsu_enable_wishlists', 1 ) ) {
            zsu_json_error( 'Wishlists disabled' );
        }

        $uid        = get_current_user_id();
        $product_id = absint( $_POST['product_id'] ?? 0 );
        $short_id   = absint( $_POST['short_id']   ?? 0 );

        if ( ! $product_id ) {
            zsu_json_error( 'Invalid product' );
        }

        global $wpdb;
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}zsu_wishlists
             WHERE user_id = %d AND product_id = %d",
            $uid, $product_id
        ) );

        if ( $exists ) {
            $wpdb->delete(
                $wpdb->prefix . 'zsu_wishlists',
                [ 'user_id' => $uid, 'product_id' => $product_id ],
                [ '%d', '%d' ]
            );
            zsu_json( [ 'action' => 'removed', 'in_wishlist' => false ] );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'zsu_wishlists',
                [
                    'user_id'    => $uid,
                    'product_id' => $product_id,
                    'short_id'   => $short_id,
                    'created_at' => current_time( 'mysql' ),
                ],
                [ '%d', '%d', '%d', '%s' ]
            );
            zsu_json( [ 'action' => 'added', 'in_wishlist' => true ] );
        }
    }

    private static function handle_zsu_get_wishlist(): void {
        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }

        $uid = get_current_user_id();
        global $wpdb;

        $ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT product_id FROM {$wpdb->prefix}zsu_wishlists
             WHERE user_id = %d ORDER BY created_at DESC",
            $uid
        ) );

        zsu_json( [ 'product_ids' => array_map( 'intval', $ids ) ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §O  UPLOAD SHORT (Vendor Studio — file upload)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_upload_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        if ( empty( $_FILES['video'] ) || $_FILES['video']['error'] !== UPLOAD_ERR_OK ) {
            zsu_json_error( 'No video file or upload error.' );
        }

        $file      = $_FILES['video'];
        $max_mb    = absint( zsu_opt( 'zsu_max_file_size_mb', 150 ) );
        $max_bytes = $max_mb * 1024 * 1024;

        if ( $file['size'] > $max_bytes ) {
            zsu_json_error( "File too large. Max {$max_mb}MB." );
        }

        // Validate extension — all common video formats allowed
        $all_video_exts = [ 'mp4','webm','mov','avi','mkv','m4v','3gp','3g2','flv','ts','ogv','wmv','asf','mpeg','mpg','mts','m2ts','vob','divx','xvid','rm','rmvb','f4v' ];
        $allowed_exts   = array_unique( array_merge(
            $all_video_exts,
            array_map( 'trim', explode( ',', str_replace( '.', '', zsu_opt( 'zsu_allowed_formats', 'mp4,webm,mov' ) ) ) )
        ) );
        $ext          = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, $allowed_exts, true ) ) {
            zsu_json_error( "Format .{$ext} not allowed. Allowed: " . implode( ', ', $allowed_exts ) );
        }

        // Validate MIME type for security
        $finfo     = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );
        $allowed_mimes = [
            'video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo',
            'video/ogg', 'video/x-matroska', 'video/x-m4v', 'video/3gpp',
            'video/3gpp2', 'video/x-flv', 'video/MP2T', 'video/mpeg',
            'video/x-ms-wmv', 'video/x-ms-asf', 'video/x-msvideo',
            'application/octet-stream', // some servers misdetect mkv/avi
        ];
        if ( ! in_array( $mime_type, $allowed_mimes, true ) ) {
            zsu_json_error( 'Invalid file type detected.' );
        }

        // Check upload quota
        $upload_count = (int) get_user_meta( $vendor_id, '_zsu_upload_count', true );
        $max_uploads  = absint( zsu_opt( 'zsu_vendor_max_upload', 100 ) );
        if ( $upload_count >= $max_uploads ) {
            zsu_json_error( "Upload limit ({$max_uploads}) reached." );
        }

        // Prepare destination
        ZSU_Install::ensure_upload_dir();
        $vendor_dir = ZSU_UPLOAD_DIR . 'vendor-' . $vendor_id . '/';
        if ( ! is_dir( $vendor_dir ) ) {
            wp_mkdir_p( $vendor_dir );
        }

        // Generate safe filename
        $filename  = 'zsu-' . time() . '-' . wp_generate_password( 8, false ) . '.' . $ext;
        $dest_path = $vendor_dir . $filename;
        $dest_url  = ZSU_UPLOAD_URL . 'vendor-' . $vendor_id . '/' . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $dest_path ) ) {
            zsu_json_error( 'Upload failed. Check server write permissions.' );
        }

        // chmod to prevent execution
        chmod( $dest_path, 0644 );

        // Handle thumbnail from canvas frame extraction (base64 data URI)
        $thumb_url = '';
        if ( ! empty( $_POST['thumb_data'] ) ) {
            $thumb_url = zsu_save_datauri_thumb(
                wp_unslash( $_POST['thumb_data'] ),
                $vendor_dir,
                'vendor-' . $vendor_id
            );
        }

        // Increment upload counter
        update_user_meta( $vendor_id, '_zsu_upload_count', $upload_count + 1 );

        zsu_log( "Vendor #{$vendor_id} uploaded {$filename} ({$mime_type}, " . round( $file['size'] / 1024 / 1024, 2 ) . "MB)", 'UPLOAD' );

        zsu_json( [
            'video_url' => $dest_url,
            'thumb_url' => $thumb_url,
            'filename'  => $filename,
            'mime_type' => $mime_type,
            'size_mb'   => round( $file['size'] / 1024 / 1024, 2 ),
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §P  SAVE SHORT (after upload wizard)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_save_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        if ( ! is_user_logged_in() ) {
            zsu_json_error( 'Login required', 401 );
        }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        $title       = sanitize_text_field( wp_unslash( $_POST['title']       ?? '' ) );
        $video_url   = esc_url_raw(          wp_unslash( $_POST['video_url']   ?? '' ) );
        $thumb_url   = esc_url_raw(          wp_unslash( $_POST['thumb_url']   ?? '' ) );
        $category    = absint(                             $_POST['category']    ?? $_POST['category_id'] ?? 0 );
        $ratio       = sanitize_text_field(               $_POST['ratio']       ?? '9:16' );
        $transform   = sanitize_text_field( wp_unslash( $_POST['transform']   ?? '' ) );

        $product_ids = array_values( array_unique( array_filter(
            array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['product_ids'] ?? '' ) ) ) )
        ) ) );

        if ( ! $title )     { zsu_json_error( 'Title is required.' ); }
        if ( ! $video_url ) { zsu_json_error( 'Video URL is required.' ); }

        // Save thumb from base64 data URI if provided
        if ( empty( $thumb_url ) && ! empty( $_POST['thumb_data'] ) ) {
            $vendor_dir = ZSU_UPLOAD_DIR . 'vendor-' . $vendor_id . '/';
            $thumb_url  = zsu_save_datauri_thumb(
                wp_unslash( $_POST['thumb_data'] ),
                $vendor_dir,
                'vendor-' . $vendor_id
            );
        }

        $auto_approve = (bool) zsu_opt( 'zsu_auto_approve', 1 ); // Default ON — admin can disable moderation in settings
        $status       = $auto_approve ? 'publish' : 'pending';

        $post_id = wp_insert_post( [
            'post_title'   => $title,
            'post_type'    => ZSU_CPT,
            'post_status'  => $status,
            'post_author'  => $vendor_id,
            'post_excerpt' => sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ?? '' ) ),
        ], true );

        if ( is_wp_error( $post_id ) ) {
            zsu_json_error( $post_id->get_error_message() );
        }

        // Save all meta
        update_post_meta( $post_id, '_zsu_video_url',   $video_url );
        update_post_meta( $post_id, '_zsu_thumb_url',   $thumb_url );
        update_post_meta( $post_id, '_zsu_product_ids', $product_ids );
        update_post_meta( $post_id, '_zsu_vendor_id',   $vendor_id );
        update_post_meta( $post_id, '_zsu_ratio',       $ratio );
        update_post_meta( $post_id, '_zsu_transform',   $transform );
        update_post_meta( $post_id, '_zsu_status',      $status );
        update_post_meta( $post_id, '_zsu_views',       0 );
        update_post_meta( $post_id, '_zsu_likes',       0 );
        update_post_meta( $post_id, '_zsu_shares',      0 );
        update_post_meta( $post_id, '_zsu_clicks',      0 );

        // Set category
        if ( $category ) {
            wp_set_post_terms( $post_id, [ $category ], ZSU_TAX );
        }

        // Email notification
        ZSU_Email::notify_upload( $post_id, $vendor_id, $title, $status );

        zsu_json( [
            'post_id' => $post_id,
            'status'  => $status,
            'message' => $auto_approve ? 'Short published!' : 'Short submitted for review!',
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §Q  VENDOR: GET MY SHORTS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_vendor_get_shorts(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        $posts = get_posts( [
            'post_type'      => ZSU_CPT,
            'post_status'    => [ 'publish', 'pending', 'draft' ],
            'posts_per_page' => 100,
            'meta_query'     => [ [
                'key'   => '_zsu_vendor_id',
                'value' => $vendor_id,
                'type'  => 'NUMERIC',
            ] ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $shorts = [];
        foreach ( $posts as $p ) {
            $meta  = zsu_get_short_meta( $p->ID );
            $thumb = $meta['thumb_url']
                ?: get_the_post_thumbnail_url( $p->ID, 'thumbnail' )
                ?: zsu_placeholder_thumb( $p->ID, $p->post_title );

            $shorts[] = [
                'id'          => $p->ID,
                'title'       => $p->post_title,
                'status'      => $p->post_status,
                'thumb_url'   => $thumb,
                'video_url'   => $meta['video_url'],
                'views'       => zsu_format_num( $meta['views'] ),
                'views_raw'   => $meta['views'],
                'likes'       => zsu_format_num( $meta['likes'] ),
                'likes_raw'   => $meta['likes'],
                'shares'      => zsu_format_num( $meta['shares'] ),
                'is_boosted'  => $meta['is_boosted'],
                'product_ids' => $meta['product_ids'],
                'rating_avg'  => $meta['rating_avg'],
                'date'        => get_the_date( 'M j, Y', $p ),
                'date_u'      => get_the_date( 'U', $p ),
                'permalink'   => home_url( '/' . zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) . '/v/' . $p->ID . '/' ),
            ];
        }

        zsu_json( [ 'shorts' => $shorts, 'total' => count( $shorts ) ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §R  VENDOR: UPDATE SHORT
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_vendor_update_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_type !== ZSU_CPT ) {
            zsu_json_error( 'Not found', 404 );
        }

        if ( (int) get_post_meta( $post_id, '_zsu_vendor_id', true ) !== $vendor_id ) {
            zsu_json_error( 'Forbidden', 403 );
        }

        // Update title
        if ( ! empty( $_POST['title'] ) ) {
            wp_update_post( [
                'ID'         => $post_id,
                'post_title' => sanitize_text_field( wp_unslash( $_POST['title'] ) ),
            ] );
        }

        // Update product IDs
        if ( isset( $_POST['product_ids'] ) ) {
            $ids = array_values( array_unique( array_filter(
                array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['product_ids'] ) ) ) )
            ) ) );
            update_post_meta( $post_id, '_zsu_product_ids', $ids );
        }

        // Update excerpt
        if ( isset( $_POST['excerpt'] ) ) {
            wp_update_post( [
                'ID'           => $post_id,
                'post_excerpt' => sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ) ),
            ] );
        }

        // Update thumbnail
        if ( ! empty( $_POST['thumb_url'] ) ) {
            update_post_meta( $post_id, '_zsu_thumb_url', esc_url_raw( wp_unslash( $_POST['thumb_url'] ) ) );
        }

        zsu_json( [ 'updated' => true ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §S  VENDOR: DELETE SHORT
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_vendor_delete_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        $post    = get_post( $post_id );

        if ( ! $post || $post->post_type !== ZSU_CPT ) {
            zsu_json_error( 'Not found', 404 );
        }

        if ( (int) get_post_meta( $post_id, '_zsu_vendor_id', true ) !== $vendor_id ) {
            zsu_json_error( 'Forbidden', 403 );
        }

        // Optionally delete the physical video file
        $video_path = get_post_meta( $post_id, '_zsu_video_path', true );
        if ( $video_path && file_exists( $video_path ) ) {
            @unlink( $video_path );
        }

        wp_delete_post( $post_id, true );

        // Decrement upload counter
        $count = (int) get_user_meta( $vendor_id, '_zsu_upload_count', true );
        if ( $count > 0 ) {
            update_user_meta( $vendor_id, '_zsu_upload_count', $count - 1 );
        }

        zsu_json( [ 'deleted' => true ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §T  VENDOR ANALYTICS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_vendor_analytics(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        $days  = absint( $_POST['days'] ?? 30 );
        $stats = ZSU_Analytics::vendor_stats( $vendor_id, $days );

        zsu_json( $stats );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §U  VENDOR: REQUEST BOOST
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_request_boost(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $vendor_id  = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        $short_id   = absint( sanitize_text_field( $_POST['short_id']   ?? 0 ) );
        $boost_type = sanitize_key(               $_POST['boost_type'] ?? 'standard' );
        $days       = absint(                      $_POST['days']       ?? 7 );

        if ( ! $short_id ) {
            zsu_json_error( 'Short ID required.' );
        }

        // Verify ownership
        if ( (int) get_post_meta( $short_id, '_zsu_vendor_id', true ) !== $vendor_id ) {
            zsu_json_error( 'You do not own this short.', 403 );
        }

        $result = ZSU_Boost::create( $short_id, $vendor_id, $boost_type, $days );

        if ( ! $result['success'] ) {
            zsu_json_error( $result['message'] );
        }

        zsu_json( $result );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §V-BOOSTS  VENDOR: GET ACTIVE BOOSTS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_vendor_get_boosts(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $vendor_id = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            zsu_json_error( 'Vendor access required', 403 );
        }

        global $wpdb;

        $boosts = $wpdb->get_results( $wpdb->prepare(
            "SELECT b.*, p.post_title AS short_title
             FROM {$wpdb->prefix}zsu_boosts b
             LEFT JOIN {$wpdb->posts} p ON p.ID = b.short_id
             WHERE b.vendor_id = %d
             ORDER BY b.created_at DESC
             LIMIT 50",
            $vendor_id
        ) );

        // Auto-expire any boosts past their end date
        $now = current_time( 'mysql' );
        foreach ( $boosts as $b ) {
            if ( $b->status === 'active' && $b->ends_at < $now ) {
                $wpdb->update(
                    $wpdb->prefix . 'zsu_boosts',
                    [ 'status' => 'expired' ],
                    [ 'id' => $b->id ],
                    [ '%s' ], [ '%d' ]
                );
                // Remove boost meta if no other active boosts for this short
                $still_active = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_boosts
                     WHERE short_id = %d AND status = 'active' AND ends_at > %s",
                    $b->short_id, $now
                ) );
                if ( ! $still_active ) {
                    update_post_meta( (int) $b->short_id, '_zsu_is_boosted', 0 );
                    delete_post_meta( (int) $b->short_id, '_zsu_boost_type' );
                }
                $b->status = 'expired';
            }
        }

        zsu_json( [ 'boosts' => $boosts ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §V  VENDOR: COIN BALANCE + HISTORY
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_get_coins_balance(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $uid = get_current_user_id();
        if ( ! $uid ) {
            zsu_json_error( 'Login required', 401 );
        }

        zsu_json( [ 'coins' => ZSU_Coins::balance( $uid ) ] );
    }

    private static function handle_zsu_coins_history(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $uid = get_current_user_id();
        if ( ! $uid ) {
            zsu_json_error( 'Login required', 401 );
        }

        $history = ZSU_Coins::history( $uid, 30 );
        zsu_json( [ 'history' => $history, 'balance' => ZSU_Coins::balance( $uid ) ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §W  HEATMAP UPDATE
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_update_heatmap(): void {
        $short_id    = absint( $_POST['short_id']    ?? 0 );
        $second_mark = absint( $_POST['second_mark'] ?? 0 );

        if ( ! $short_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}zsu_heatmap (short_id, second_mark, watch_count)
             VALUES (%d, %d, 1)
             ON DUPLICATE KEY UPDATE watch_count = watch_count + 1",
            $short_id,
            $second_mark
        ) );

        zsu_json( [ 'recorded' => true ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §X  ADMIN: GET STATS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_get_stats(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $days  = absint( $_POST['days'] ?? 30 );
        $posts = wp_count_posts( ZSU_CPT );

        $stats = ZSU_Analytics::global_stats( $days );
        $chart = ZSU_Analytics::daily_chart( $days );
        $top   = ZSU_Analytics::top_shorts( 10, $days );

        global $wpdb;

        $active_boosts = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}zsu_boosts
             WHERE status = 'active' AND ends_at > NOW()"
        );

        $total_vendors = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT meta_value)
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_zsu_vendor_id'"
        );

        $device_split  = ZSU_Analytics::device_split( $days );
        $browser_split = ZSU_Analytics::browser_split( $days );

        $total_coins_spent = (int) $wpdb->get_var(
            "SELECT COALESCE(SUM(coins_spent),0)
             FROM {$wpdb->prefix}zsu_boosts
             WHERE status IN ('active','expired')"
        );

        $top_vendors = $wpdb->get_results( $wpdb->prepare(
            "SELECT meta_value AS vendor_id, COUNT(*) AS short_count
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_zsu_vendor_id'
             GROUP BY meta_value
             ORDER BY short_count DESC
             LIMIT 5"
        ) );

        zsu_json( [
            'posts'             => (array) $posts,
            'events'            => $stats,
            'chart'             => $chart,
            'top_shorts'        => $top,
            'active_boosts'     => $active_boosts,
            'total_vendors'     => $total_vendors,
            'device_split'      => $device_split,
            'browser_split'     => $browser_split,
            'total_coins_spent' => $total_coins_spent,
            'top_vendors'       => $top_vendors,
            'days'              => $days,
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §Y  ADMIN: APPROVE / REJECT / DELETE SHORT
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_approve_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        wp_update_post( [ 'ID' => $post_id, 'post_status' => 'publish' ] );
        update_post_meta( $post_id, '_zsu_status', 'published' );

        // Email vendor
        $vendor_id = (int) get_post_meta( $post_id, '_zsu_vendor_id', true );
        if ( $vendor_id ) {
            ZSU_Email::notify_approved( $post_id, $vendor_id );
        }

        zsu_json( [ 'approved' => true, 'post_id' => $post_id ] );
    }

    private static function handle_zsu_admin_reject_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
        update_post_meta( $post_id, '_zsu_status', 'rejected' );

        $vendor_id = (int) get_post_meta( $post_id, '_zsu_vendor_id', true );
        if ( $vendor_id ) {
            ZSU_Email::notify_rejected( $post_id, $vendor_id );
        }

        zsu_json( [ 'rejected' => true, 'post_id' => $post_id ] );
    }

    private static function handle_zsu_admin_delete_short(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        wp_delete_post( $post_id, true );
        zsu_json( [ 'deleted' => true ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §Z  ADMIN: BOOST ACTIONS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_boost_action(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $boost_id = absint( sanitize_text_field( $_POST['boost_id']     ?? 0 ) );
        $action   = sanitize_key(               $_POST['boost_action'] ?? '' );

        $status_map = [
            'approve' => 'active',
            'cancel'  => 'cancelled',
            'expire'  => 'expired',
        ];

        if ( ! $boost_id || ! isset( $status_map[ $action ] ) ) {
            zsu_json_error( 'Invalid boost action' );
        }

        $new_status = $status_map[ $action ];
        $updated    = ZSU_Boost::set_status( $boost_id, $new_status );

        zsu_json( [ 'updated' => $updated, 'status' => $new_status ] );
    }

    private static function handle_zsu_admin_get_boosts(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }
        $boosts = ZSU_Boost::get_all( 200 );
        zsu_json( [ 'boosts' => $boosts ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §AA  ADMIN: GET PENDING SHORTS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_get_pending(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $posts = get_posts( [
            'post_type'      => ZSU_CPT,
            'post_status'    => 'pending',
            'posts_per_page' => 20,
            'orderby'        => 'date',
            'order'          => 'ASC',
        ] );

        $result = [];
        foreach ( $posts as $p ) {
            $meta    = zsu_get_short_meta( $p->ID );
            $result[] = [
                'id'        => $p->ID,
                'title'     => $p->post_title,
                'thumb'     => $meta['thumb_url'] ?: zsu_placeholder_thumb( $p->ID, $p->post_title ),
                'vendor_id' => $meta['vendor_id'],
                'date'      => get_the_date( 'M j, Y', $p ),
            ];
        }

        zsu_json( [ 'pending' => $result, 'total' => count( $result ) ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §BB  ADMIN: SAVE SETTINGS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_save_settings(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $allowed_keys = [
            'zsu_shorts_page_slug','zsu_grid_columns','zsu_videos_per_page',
            'zsu_autoplay','zsu_muted_start','zsu_loop','zsu_show_featured',
            'zsu_featured_count','zsu_show_categories','zsu_show_follow_btn',
            'zsu_show_share_btn','zsu_show_like_btn','zsu_show_comment_btn',
            'zsu_product_tray_items','zsu_primary_color','zsu_accent_color',
            'zsu_gradient_start','zsu_gradient_end','zsu_font_family',
            'zsu_border_radius','zsu_heading_color','zsu_animation_level','zsu_particle_bg',
            'zsu_glassmorphism','zsu_neon_glow','zsu_tilt_cards',
            'zsu_confetti_like','zsu_sound_effects','zsu_preset_active',
            'zsu_boost_enabled','zsu_coins_per_purchase','zsu_coins_per_review',
            'zsu_coins_per_share','zsu_boost_price_mega','zsu_dokan_studio_tab',
            'zsu_whatsapp_number','zsu_coin_rate_1','zsu_coin_rate_100',
            'zsu_coin_rate_500','zsu_coin_rate_1000',
            'zsu_auto_approve','zsu_vendor_max_upload','zsu_allowed_formats',
            'zsu_max_file_size_mb','zsu_watermark','zsu_watermark_text',
            'zsu_watermark_opacity','zsu_watermark_position',
            'zsu_home_rail_views_multiplier','zsu_home_rail_views_floor','zsu_home_rail_default_filter',
            'zsu_home_rail_title','zsu_home_rail_cta_label','zsu_home_rail_views_min',
            'zsu_home_rail_show_views','zsu_home_rail_count','zsu_home_rail_animation',
            'zsu_home_rail_show_likes','zsu_home_rail_show_follow','zsu_home_rail_show_products',
            'zsu_home_rail_autoplay','zsu_home_rail_autoplay_speed',
            'zsu_spin_wheel_enabled','zsu_spin_wheel_title','zsu_spin_wheel_subtitle',
            'zsu_spin_cooldown_hours','zsu_spin_wheel_prizes',
            'zsu_lazy_load','zsu_preload_next','zsu_seo_schema','zsu_cdn_url',
            'zsu_heatmap_enabled','zsu_ab_testing','zsu_enable_comments',
            'zsu_enable_ratings','zsu_enable_wishlists','zsu_email_on_upload',
            'zsu_email_on_boost','zsu_email_on_approve','zsu_email_on_reject',
        ];

        $saved = 0;
        foreach ( $allowed_keys as $key ) {
            if ( array_key_exists( $key, $_POST ) ) {
                update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
                $saved++;
            }
        }

        // Flush rewrites if slug changed
        if ( isset( $_POST['zsu_shorts_page_slug'] ) ) {
            flush_rewrite_rules();
        }

        zsu_json( [ 'saved' => $saved, 'message' => "{$saved} settings saved successfully." ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §CC  ADMIN: APPLY PRESET
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_save_option(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { wp_send_json_error( 'Invalid nonce', 403 ); return; }
        if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( 'Forbidden', 403 ); return; }
        $key   = sanitize_key( $_POST['key'] ?? '' );
        $value = sanitize_text_field( $_POST['value'] ?? '' );
        if ( ! $key ) { wp_send_json_error( 'No key' ); return; }
        // Only allow safe option keys
        $allowed = [ 'zsu_wheel_preset', 'zsu_preset_active', 'zsu_border_radius' ];
        if ( ! in_array( $key, $allowed, true ) ) { wp_send_json_error( 'Forbidden key' ); return; }
        update_option( $key, $value );
        wp_send_json_success( [ 'key' => $key, 'value' => $value ] );
    }

    private static function handle_zsu_admin_apply_preset(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $preset_key = sanitize_key( $_POST['preset'] ?? '' );
        $presets    = self::get_presets();

        if ( ! $preset_key || ! isset( $presets[ $preset_key ] ) ) {
            zsu_json_error( 'Preset not found.' );
        }

        foreach ( $presets[ $preset_key ] as $key => $val ) {
            update_option( $key, $val );
        }
        update_option( 'zsu_preset_active', $preset_key );

        zsu_json( [ 'applied' => true, 'preset' => $preset_key ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §DD  ADMIN: RESET COUNTERS
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_reset_counters(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            zsu_json_error( 'Invalid ID' );
        }

        foreach ( [ '_zsu_views', '_zsu_likes', '_zsu_shares', '_zsu_clicks', '_zsu_comments' ] as $key ) {
            update_post_meta( $post_id, $key, 0 );
        }

        zsu_json( [ 'reset' => true ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §EE  ADMIN: EXPORT ANALYTICS CSV
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_export_analytics(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $days  = absint( $_POST['days'] ?? 30 );
        $chart = ZSU_Analytics::daily_chart( $days );

        $rows   = [ [ 'Date', 'Views', 'Likes', 'Shares', 'Clicks' ] ];
        foreach ( $chart as $row ) {
            $rows[] = [ $row->day, $row->views, $row->likes, $row->shares, $row->clicks ];
        }

        $csv = '';
        foreach ( $rows as $row ) {
            $csv .= implode( ',', array_map( static fn( $v ) => '"' . str_replace( '"', '""', $v ) . '"', $row ) ) . "\n";
        }

        zsu_json( [ 'csv' => $csv, 'filename' => "zsu-analytics-{$days}d.csv" ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §FF-V  VENDOR: SEARCH PRODUCTS (no manage_options needed)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_vendor_search_products(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }
        if ( ! is_user_logged_in() ) { zsu_json_error( 'Login required', 401 ); }

        $q = sanitize_text_field( $_POST['q'] ?? '' );
        if ( strlen( $q ) < 2 || ! function_exists( 'wc_get_products' ) ) {
            zsu_json( [ 'success' => true, 'products' => [] ] );
            return;
        }

        $vendor_id = get_current_user_id();

        $products = wc_get_products( [
            's'      => $q,
            'limit'  => 20,
            'status' => 'publish',
            'author' => $vendor_id,
        ] );

        // Fallback: if Dokan stores vendor ID differently, also try post_author query
        if ( empty( $products ) ) {
            $post_ids = get_posts( [
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'author'         => $vendor_id,
                's'              => $q,
                'posts_per_page' => 20,
                'fields'         => 'ids',
            ] );
            if ( ! empty( $post_ids ) ) {
                $products = array_map( 'wc_get_product', $post_ids );
                $products = array_filter( $products );
            }
        }

        $result = [];
        foreach ( $products as $p ) {
            if ( ! $p || (int) $p->get_id() === 0 ) continue;
            $result[] = [
                'id'    => $p->get_id(),
                'name'  => $p->get_name(),
                'image' => wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) ?: wc_placeholder_img_src( 'thumbnail' ),
            ];
        }
        zsu_json( [ 'success' => true, 'products' => $result ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §FF  ADMIN: SEARCH PRODUCTS (for meta box autocomplete)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_search_products(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        $q = sanitize_text_field( $_POST['q'] ?? '' );
        if ( strlen( $q ) < 2 || ! function_exists( 'wc_get_products' ) ) {
            zsu_json( [ 'products' => [] ] );
        }

        $products = wc_get_products( [
            's'     => $q,
            'limit' => 10,
            'status'=> 'publish',
        ] );

        $result = [];
        foreach ( $products as $p ) {
            $result[] = [
                'id'    => $p->get_id(),
                'name'  => $p->get_name(),
                'price' => $p->get_price_html(),
                'image' => wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) ?: '',
            ];
        }

        zsu_json( [ 'products' => $result ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §GG  ADMIN: GET COINS LOG
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_get_coins_log(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) { zsu_json_error( 'Invalid nonce', 403 ); }

        global $wpdb;
        $log = $wpdb->get_results(
            "SELECT c.*, u.display_name, u.user_email
             FROM {$wpdb->prefix}zsu_coins c
             LEFT JOIN {$wpdb->users} u ON u.ID = c.user_id
             ORDER BY c.created_at DESC
             LIMIT 100"
        );

        zsu_json( [ 'log' => $log ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  §HH  PRESETS DATA
    // ═══════════════════════════════════════════════════════════════════
    public static function get_presets(): array {
        $base = [
            'zsu_border_radius'   => 22,
            'zsu_animation_level' => 'medium',
            'zsu_neon_glow'       => 0,
            'zsu_glassmorphism'   => 0,
            'zsu_particle_bg'     => 0,
            'zsu_tilt_cards'      => 1,
            'zsu_font_family'     => 'Inter',
        ];
        $dark_base = array_merge( $base, [ 'zsu_tilt_cards' => 1 ] );

        $presets = [
            // ── 5 White themes ───────────────────────────────────────────────
            'pure_white'  => array_merge( $base, [ 'zsu_primary_color'=>'#111827','zsu_accent_color'=>'#f59e0b','zsu_gradient_start'=>'#ffffff','zsu_gradient_end'=>'#f9fafb' ] ),
            'frost'       => array_merge( $base, [ 'zsu_primary_color'=>'#1e40af','zsu_accent_color'=>'#3b82f6','zsu_gradient_start'=>'#f8faff','zsu_gradient_end'=>'#eff6ff' ] ),
            'ivory'       => array_merge( $base, [ 'zsu_primary_color'=>'#92400e','zsu_accent_color'=>'#d97706','zsu_gradient_start'=>'#fffdf7','zsu_gradient_end'=>'#fef9ee','zsu_font_family'=>'Roboto' ] ),
            'blush'       => array_merge( $base, [ 'zsu_primary_color'=>'#be185d','zsu_accent_color'=>'#f43f5e','zsu_gradient_start'=>'#fff5f7','zsu_gradient_end'=>'#ffe4e6','zsu_font_family'=>'Nunito' ] ),
            'sage'        => array_merge( $base, [ 'zsu_primary_color'=>'#065f46','zsu_accent_color'=>'#10b981','zsu_gradient_start'=>'#f0fdf9','zsu_gradient_end'=>'#d1fae5' ] ),
            // ── 5 Black / dark themes ─────────────────────────────────────────
            'obsidian'    => array_merge( $dark_base, [ 'zsu_primary_color'=>'#f9fafb','zsu_accent_color'=>'#f59e0b','zsu_gradient_start'=>'#09090b','zsu_gradient_end'=>'#18181b' ] ),
            'midnight'    => array_merge( $dark_base, [ 'zsu_primary_color'=>'#818cf8','zsu_accent_color'=>'#a78bfa','zsu_gradient_start'=>'#0f0f1a','zsu_gradient_end'=>'#1e1b4b','zsu_font_family'=>'Nunito' ] ),
            'noir'        => array_merge( $dark_base, [ 'zsu_primary_color'=>'#e5e7eb','zsu_accent_color'=>'#d97706','zsu_gradient_start'=>'#111111','zsu_gradient_end'=>'#1c1c1c' ] ),
            'slate_dark'  => array_merge( $dark_base, [ 'zsu_primary_color'=>'#38bdf8','zsu_accent_color'=>'#0ea5e9','zsu_gradient_start'=>'#0c1a2e','zsu_gradient_end'=>'#0f2444' ] ),
            'carbon'      => array_merge( $dark_base, [ 'zsu_primary_color'=>'#86efac','zsu_accent_color'=>'#22c55e','zsu_gradient_start'=>'#0a1208','zsu_gradient_end'=>'#0f1f10' ] ),
            // ── Legacy aliases (keep for back-compat with saved options) ──────
            'clean_white'   => array_merge( $base, [ 'zsu_primary_color'=>'#2563eb','zsu_accent_color'=>'#f59e0b','zsu_gradient_start'=>'#ffffff','zsu_gradient_end'=>'#f9fafb' ] ),
            'soft_gray'     => array_merge( $base, [ 'zsu_primary_color'=>'#374151','zsu_accent_color'=>'#6b7280','zsu_gradient_start'=>'#f9fafb','zsu_gradient_end'=>'#f3f4f6' ] ),
            'slate_blue'    => array_merge( $base, [ 'zsu_primary_color'=>'#1e40af','zsu_accent_color'=>'#3b82f6','zsu_gradient_start'=>'#f0f4ff','zsu_gradient_end'=>'#e0e7ff' ] ),
            'warm_cream'    => array_merge( $base, [ 'zsu_primary_color'=>'#92400e','zsu_accent_color'=>'#d97706','zsu_gradient_start'=>'#fffbeb','zsu_gradient_end'=>'#fef3c7','zsu_font_family'=>'Roboto' ] ),
            'rose_blush'    => array_merge( $base, [ 'zsu_primary_color'=>'#be185d','zsu_accent_color'=>'#db2777','zsu_gradient_start'=>'#fff1f2','zsu_gradient_end'=>'#ffe4e6','zsu_font_family'=>'Nunito' ] ),
            'sage_green'    => array_merge( $base, [ 'zsu_primary_color'=>'#065f46','zsu_accent_color'=>'#059669','zsu_gradient_start'=>'#ecfdf5','zsu_gradient_end'=>'#d1fae5' ] ),
            'charcoal_dark' => array_merge( $dark_base, [ 'zsu_primary_color'=>'#f9fafb','zsu_accent_color'=>'#f59e0b','zsu_gradient_start'=>'#111827','zsu_gradient_end'=>'#1f2937' ] ),
            'navy_classic'  => array_merge( $base, [ 'zsu_primary_color'=>'#1d4ed8','zsu_accent_color'=>'#fbbf24','zsu_gradient_start'=>'#f8faff','zsu_gradient_end'=>'#eff6ff','zsu_font_family'=>'Poppins' ] ),
            'minimal_ink'   => array_merge( $base, [ 'zsu_primary_color'=>'#18181b','zsu_accent_color'=>'#52525b','zsu_gradient_start'=>'#fafafa','zsu_gradient_end'=>'#f4f4f5','zsu_animation_level'=>'low','zsu_tilt_cards'=>0 ] ),
            'dusty_mauve'   => array_merge( $base, [ 'zsu_primary_color'=>'#7c3aed','zsu_accent_color'=>'#a78bfa','zsu_gradient_start'=>'#faf5ff','zsu_gradient_end'=>'#ede9fe','zsu_font_family'=>'Nunito' ] ),
            'terracotta'    => array_merge( $base, [ 'zsu_primary_color'=>'#c2410c','zsu_accent_color'=>'#ea580c','zsu_gradient_start'=>'#fff7ed','zsu_gradient_end'=>'#ffedd5','zsu_font_family'=>'Roboto' ] ),
            'steel'         => array_merge( $base, [ 'zsu_primary_color'=>'#0f172a','zsu_accent_color'=>'#0ea5e9','zsu_gradient_start'=>'#f1f5f9','zsu_gradient_end'=>'#e2e8f0' ] ),
        ];

        return $presets;
    }

    private static function handle_zsu_admin_search_vendor(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) {
            zsu_json_error( 'Invalid nonce', 403 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            zsu_json_error( 'Forbidden', 403 );
        }

        $q = sanitize_text_field( $_POST['q'] ?? '' );
        if ( strlen( $q ) < 2 ) {
            zsu_json( [ 'users' => [] ] );
        }

        $users = get_users( [
            'search'         => '*' . $q . '*',
            'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
            'number'         => 10,
            'fields'         => [ 'ID', 'display_name', 'user_email' ],
        ] );

        $result = [];
        if ( is_array( $users ) ) {
            foreach ( $users as $u ) {
                $result[] = [
                    'id'      => $u->ID,
                    'name'    => $u->display_name . ' (' . $u->user_email . ')',
                    'balance' => ZSU_Coins::balance( $u->ID ),
                ];
            }
        }
        zsu_json( [ 'users' => $result ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_adjust_coins(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) {
            zsu_json_error( 'Invalid nonce', 403 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            zsu_json_error( 'Forbidden', 403 );
        }

        $user_id = absint( $_POST['user_id'] ?? 0 );
        $amount  = (int) ( $_POST['amount']  ?? 0 );
        $reason  = sanitize_text_field( $_POST['reason'] ?? 'admin_adjustment' );

        if ( ! $user_id ) {
            zsu_json_error( 'Invalid vendor ID' );
        }
        if ( $amount === 0 ) {
            zsu_json_error( 'Amount cannot be zero' );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            zsu_json_error( 'User not found' );
        }

        $before  = ZSU_Coins::balance( $user_id );
        $success = ZSU_Coins::add( $user_id, $amount, $reason ?: 'admin_adjustment', 0 );
        $after   = ZSU_Coins::balance( $user_id );

        zsu_json( [
            'success'      => $success,
            'user_id'      => $user_id,
            'user_name'    => $user->display_name,
            'balance_before' => $before,
            'balance_after'  => $after,
            'delta'          => $amount,
        ] );
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ADMIN: MANUAL BOOST — pin any short to top of feed instantly
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_manual_boost(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) {
            zsu_json_error( 'Invalid nonce', 403 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            zsu_json_error( 'Forbidden', 403 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        $action  = sanitize_key( $_POST['boost_action'] ?? 'enable' ); // 'enable' | 'disable'

        if ( ! $post_id ) {
            zsu_json_error( 'Invalid short ID' );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== ZSU_CPT ) {
            zsu_json_error( 'Short not found' );
        }

        if ( $action === 'disable' ) {
            update_post_meta( $post_id, '_zsu_manual_boost', 0 );
            zsu_json( [ 'boosted' => false, 'title' => get_the_title( $post_id ) ] );
        } else {
            update_post_meta( $post_id, '_zsu_manual_boost', 1 );
            zsu_json( [ 'boosted' => true, 'title' => get_the_title( $post_id ) ] );
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ADMIN: SEARCH SHORTS by title (for manual boost autocomplete)
    // ═══════════════════════════════════════════════════════════════════
    private static function handle_zsu_admin_search_shorts(): void {
        if ( ! check_ajax_referer( ZSU_NONCE, 'nonce', false ) ) {
            zsu_json_error( 'Invalid nonce', 403 );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            zsu_json_error( 'Forbidden', 403 );
        }

        $q = sanitize_text_field( $_POST['q'] ?? '' );
        if ( strlen( $q ) < 2 ) {
            zsu_json( [ 'posts' => [] ] );
        }

        $posts = get_posts( [
            'post_type'      => ZSU_CPT,
            'post_status'    => [ 'publish', 'pending', 'draft' ],
            'posts_per_page' => 10,
            's'              => $q,
            'orderby'        => 'relevance',
        ] );

        $result = [];
        if ( is_array( $posts ) ) {
            foreach ( $posts as $p ) {
                $result[] = [
                    'id'    => $p->ID,
                    'title' => $p->post_title ?: '(Untitled #' . $p->ID . ')',
                ];
            }
        }

        zsu_json( [ 'posts' => $result ] );
    }
}