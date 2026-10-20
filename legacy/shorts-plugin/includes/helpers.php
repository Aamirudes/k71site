<?php
defined( 'ABSPATH' ) || exit;

// PHP 7.4 polyfills (str_contains / str_starts_with / str_ends_with require PHP 8.0+)
if ( ! function_exists( 'str_contains' ) ) {
    function str_contains( string $haystack, string $needle ): bool {
        return '' === $needle || false !== strpos( $haystack, $needle );
    }
}
if ( ! function_exists( 'str_starts_with' ) ) {
    function str_starts_with( string $haystack, string $needle ): bool {
        return '' === $needle || 0 === strncmp( $haystack, $needle, strlen( $needle ) );
    }
}
if ( ! function_exists( 'str_ends_with' ) ) {
    function str_ends_with( string $haystack, string $needle ): bool {
        return '' === $needle || substr( $haystack, -strlen( $needle ) ) === $needle;
    }
}

// ═══════════════════════════════════════════════════════════════════
//  GLOBAL HELPERS
// ═══════════════════════════════════════════════════════════════════

/**
 * Get a plugin option with optional default.
 */
function zsu_opt( string $key, $default = '' ) {
    return get_option( $key, $default );
}

/**
 * Logger — only writes when WP_DEBUG is true.
 */
function zsu_log( string $msg, string $level = 'INFO' ): void {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }
    $line = sprintf( "[%s][%s] %s\n", gmdate( 'Y-m-d H:i:s' ), $level, $msg );
    error_log( $line, 3, ZSU_LOG );
}

/**
 * Format a number into human-readable string (1.2K, 3.4M).
 */
function zsu_format_num( int $n ): string {
    if ( $n >= 1_000_000 ) {
        return round( $n / 1_000_000, 1 ) . 'M';
    }
    if ( $n >= 1_000 ) {
        return round( $n / 1_000, 1 ) . 'K';
    }
    return (string) $n;
}

/**
 * Get the current vendor ID (Dokan).
 */
function zsu_current_vendor_id(): int {
    if ( ! is_user_logged_in() ) {
        return 0;
    }
    $uid = get_current_user_id();
    if ( function_exists( 'dokan_is_user_seller' ) && dokan_is_user_seller( $uid ) ) {
        return $uid;
    }
    return 0;
}

/**
 * Check if a short is currently boosted.
 */
function zsu_is_boosted( int $short_id ): bool {
    global $wpdb;
    $row = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}zsu_boosts
         WHERE short_id = %d
           AND status   = 'active'
           AND ends_at  > %s
         LIMIT 1",
        $short_id,
        current_time( 'mysql' )
    ) );
    return ! empty( $row );
}

/**
 * Ground-truth check for whether a short currently has an active boost.
 * Also self-heals the cached `_zsu_is_boosted` / `_zsu_boost_type` post meta
 * so a stale flag can't keep a short showing as boosted/Elite after its
 * boost has actually expired (the cached meta is normally only cleared
 * when a vendor happens to open their boost dashboard).
 */
function zsu_sync_boost_meta( int $short_id ): bool {
    $active = zsu_is_boosted( $short_id );

    if ( ! $active && get_post_meta( $short_id, '_zsu_is_boosted', true ) ) {
        update_post_meta( $short_id, '_zsu_is_boosted', 0 );
        delete_post_meta( $short_id, '_zsu_boost_type' );
    }

    return $active;
}

/**
 * Get all meta for a short post in one call.
 */
function zsu_get_short_meta( int $short_id ): array {
    $product_ids = get_post_meta( $short_id, '_zsu_product_ids', true );
    if ( ! is_array( $product_ids ) ) {
        $product_ids = [];
    }

    return [
        'video_url'       => (string)  get_post_meta( $short_id, '_zsu_video_url',       true ),
        'video_path'      => (string)  get_post_meta( $short_id, '_zsu_video_path',      true ),
        'thumb_url'       => (string)  get_post_meta( $short_id, '_zsu_thumb_url',       true ),
        'thumb_b_url'     => (string)  get_post_meta( $short_id, '_zsu_thumb_b_url',     true ),  // A/B variant
        'product_ids'     => $product_ids,
        'vendor_id'       => (int)     get_post_meta( $short_id, '_zsu_vendor_id',        true ),
        'views'           => (int)     get_post_meta( $short_id, '_zsu_views',            true ),
        'likes'           => (int)     get_post_meta( $short_id, '_zsu_likes',            true ),
        'shares'          => (int)     get_post_meta( $short_id, '_zsu_shares',           true ),
        'clicks'          => (int)     get_post_meta( $short_id, '_zsu_clicks',           true ),
        'comments'        => (int)     get_post_meta( $short_id, '_zsu_comments',         true ),
        'is_featured'     => (bool)    get_post_meta( $short_id, '_zsu_featured',         true ),
        'is_boosted'      => zsu_is_boosted( $short_id ),
        'trending_score'  => (float)   get_post_meta( $short_id, '_zsu_trending_score',   true ),
        'status'          => (string) ( get_post_meta( $short_id, '_zsu_status',          true ) ?: 'published' ),
        'ratio'           => (string) ( get_post_meta( $short_id, '_zsu_ratio',           true ) ?: '9:16' ),
        'duration'        => (int)     get_post_meta( $short_id, '_zsu_duration',         true ),
        'transform'       => (string)  get_post_meta( $short_id, '_zsu_transform',        true ),
        'watch_avg_pct'   => (int)     get_post_meta( $short_id, '_zsu_watch_avg_pct',    true ),
        'ab_variant'      => (string) ( get_post_meta( $short_id, '_zsu_ab_variant',      true ) ?: 'A' ),
        'rating_avg'      => (float)   get_post_meta( $short_id, '_zsu_rating_avg',       true ),
        'rating_count'    => (int)     get_post_meta( $short_id, '_zsu_rating_count',     true ),
    ];
}

/**
 * Generate a clean minimal placeholder thumbnail - neutral dark with subtle play icon.
 */
function zsu_placeholder_thumb( int $short_id, string $title = '' ): string {
    $palettes = [
        [ '#0f172a', '#1e293b' ],
        [ '#111827', '#1f2937' ],
        [ '#0c1a2e', '#162540' ],
        [ '#0a0a0a', '#1a1a1a' ],
        [ '#0f0f1a', '#1a1a2e' ],
        [ '#0a1208', '#0f1f10' ],
    ];
    $pair  = $palettes[ $short_id % count( $palettes ) ];
    $c0    = esc_attr( $pair[0] );
    $c1    = esc_attr( $pair[1] );
    $label = esc_html( strtoupper( substr( $title ?: 'ZS', 0, 2 ) ) );

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="711">'
         . '<defs>'
         . '<linearGradient id="g" x1="0" y1="0" x2="0" y2="1">'
         . '<stop offset="0" stop-color="' . $c0 . '"/>'
         . '<stop offset="1" stop-color="' . $c1 . '"/>'
         . '</linearGradient>'
         . '</defs>'
         . '<rect width="400" height="711" fill="url(#g)"/>'
         . '<line x1="0" y1="355" x2="400" y2="355" stroke="rgba(255,255,255,0.03)" stroke-width="1"/>'
         . '<line x1="200" y1="0" x2="200" y2="711" stroke="rgba(255,255,255,0.03)" stroke-width="1"/>'
         . '<circle cx="200" cy="340" r="48" fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.15)" stroke-width="1.5"/>'
         . '<polygon points="188,320 188,360 228,340" fill="rgba(255,255,255,0.4)"/>'
         . '<text x="200" y="420" font-family="Arial,sans-serif" font-size="22" font-weight="600"'
         . ' fill="rgba(255,255,255,0.18)" text-anchor="middle" letter-spacing="4">'
         . $label
         . '</text>'
         . '</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

/**
 * Record an analytics event and update post meta counters.
 */
function zsu_record_event( int $short_id, string $event_type, int $user_id = 0, array $extra = [] ): void {
    global $wpdb;

    $ua     = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' );
    $device = 'desktop';
    if ( preg_match( '/Mobile|Android|iPhone|iPad|iPod/i', $ua ) ) {
        $device = 'mobile';
    } elseif ( preg_match( '/Tablet|iPad/i', $ua ) ) {
        $device = 'tablet';
    }

    $browser = 'unknown';
    if ( str_contains( $ua, 'Chrome' ) )  $browser = 'chrome';
    elseif ( str_contains( $ua, 'Firefox' ) ) $browser = 'firefox';
    elseif ( str_contains( $ua, 'Safari' ) )  $browser = 'safari';
    elseif ( str_contains( $ua, 'Edge' ) )    $browser = 'edge';

    $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
    $ip_hash = $ip ? hash( 'sha256', $ip . wp_salt() ) : '';

    $wpdb->insert(
        $wpdb->prefix . 'zsu_events',
        [
            'short_id'   => $short_id,
            'event_type' => sanitize_key( $event_type ),
            'user_id'    => $user_id,
            'session_id' => $extra['session_id'] ?? ( session_id() ?: substr( md5( uniqid( '', true ) ), 0, 16 ) ),
            'device'     => $device,
            'browser'    => $browser,
            'ip_hash'    => $ip_hash,
            'watch_pct'  => (int) ( $extra['watch_pct'] ?? 0 ),
            'referrer'   => sanitize_text_field( substr( wp_get_referer() ?: '', 0, 500 ) ),
            'created_at' => current_time( 'mysql' ),
        ],
        [ '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
    );

    // Update fast-read post meta counters
    $counter_map = [
        'view'  => '_zsu_views',
        'like'  => '_zsu_likes',
        'share' => '_zsu_shares',
        'click' => '_zsu_clicks',
    ];
    if ( isset( $counter_map[ $event_type ] ) ) {
        $meta_key = $counter_map[ $event_type ];
        $current  = (int) get_post_meta( $short_id, $meta_key, true );
        update_post_meta( $short_id, $meta_key, $current + 1 );
    }
}

/**
 * Recalculate trending scores for all shorts.
 * Called by daily cron.
 */
function zsu_recalculate_trending(): void {
    global $wpdb;
    $window = absint( zsu_opt( 'zsu_trending_window', 7 ) );

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT short_id,
            ( SUM( CASE WHEN event_type='view'    THEN 1 ELSE 0 END ) * 1  ) +
            ( SUM( CASE WHEN event_type='like'    THEN 1 ELSE 0 END ) * 4  ) +
            ( SUM( CASE WHEN event_type='share'   THEN 1 ELSE 0 END ) * 6  ) +
            ( SUM( CASE WHEN event_type='click'   THEN 1 ELSE 0 END ) * 5  ) +
            ( SUM( CASE WHEN event_type='comment' THEN 1 ELSE 0 END ) * 3  ) AS score
         FROM {$wpdb->prefix}zsu_events
         WHERE created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )
         GROUP BY short_id",
        $window
    ) );

    foreach ( $rows as $row ) {
        update_post_meta( (int) $row->short_id, '_zsu_trending_score', (float) $row->score );
    }
}

/**
 * Save a base64 data URI image to disk and return its URL.
 */
function zsu_save_datauri_thumb( string $data_uri, string $dir, string $prefix = 'thumb' ): string {
    if ( strpos( $data_uri, 'data:image/' ) !== 0 ) {
        return '';
    }
    $parts = explode( ',', $data_uri, 2 );
    if ( count( $parts ) !== 2 ) {
        return '';
    }

    $img_data = base64_decode( $parts[1] );
    if ( ! $img_data ) {
        return '';
    }

    // Detect extension
    $ext = 'jpg';
    if ( strpos( $parts[0], 'image/png' ) !== false ) {
        $ext = 'png';
    } elseif ( strpos( $parts[0], 'image/webp' ) !== false ) {
        $ext = 'webp';
    }

    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    $filename = sanitize_file_name( $prefix . '-thumb-' . time() . '.' . $ext );
    $filepath = $dir . $filename;

    if ( file_put_contents( $filepath, $img_data ) === false ) {
        return '';
    }

    // Build URL from path
    $rel_path = str_replace( ZSU_UPLOAD_DIR, '', $dir );
    return ZSU_UPLOAD_URL . $rel_path . $filename;
}

/**
 * Send a JSON response and exit.
 */
function zsu_json( array $data, bool $success = true, int $code = 200 ): void {
    status_header( $code );
    wp_send_json( array_merge( [ 'success' => $success ], $data ) );
}

function zsu_json_error( string $msg, int $code = 400 ): void {
    zsu_json( [ 'message' => $msg ], false, $code );
}