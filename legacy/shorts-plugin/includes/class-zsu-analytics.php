<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Analytics — analytics init and helper queries.
 */
class ZSU_Analytics {

    public static function init(): void {
        // Nothing to hook yet — all analytics queries are in AJAX handlers (Chunk 2)
        // and admin pages (Chunk 3/4).
    }

    /**
     * Get platform-wide stats for a given day range.
     */
    public static function global_stats( int $days = 30 ): object {
        global $wpdb;
        return (object) ( $wpdb->get_row( $wpdb->prepare(
            "SELECT
                SUM( CASE WHEN event_type = 'view'    THEN 1 ELSE 0 END ) AS views,
                SUM( CASE WHEN event_type = 'like'    THEN 1 ELSE 0 END ) AS likes,
                SUM( CASE WHEN event_type = 'share'   THEN 1 ELSE 0 END ) AS shares,
                SUM( CASE WHEN event_type = 'click'   THEN 1 ELSE 0 END ) AS clicks,
                SUM( CASE WHEN event_type = 'comment' THEN 1 ELSE 0 END ) AS comments
             FROM {$wpdb->prefix}zsu_events
             WHERE created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )",
            $days
        ), ARRAY_A ) ?: [] );
    }

    /**
     * Get daily chart data for a given day range.
     */
    public static function daily_chart( int $days = 30, array $short_ids = [] ): array {
        global $wpdb;

        $where = $wpdb->prepare( 'created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )', $days );

        if ( ! empty( $short_ids ) ) {
            $placeholders = implode( ',', array_fill( 0, count( $short_ids ), '%d' ) );
            $where       .= $wpdb->prepare( " AND short_id IN ($placeholders)", ...$short_ids );
        }

        return (array) $wpdb->get_results(
            "SELECT
                DATE(created_at) AS day,
                SUM( CASE WHEN event_type = 'view'  THEN 1 ELSE 0 END ) AS views,
                SUM( CASE WHEN event_type = 'like'  THEN 1 ELSE 0 END ) AS likes,
                SUM( CASE WHEN event_type = 'share' THEN 1 ELSE 0 END ) AS shares,
                SUM( CASE WHEN event_type = 'click' THEN 1 ELSE 0 END ) AS clicks
             FROM {$wpdb->prefix}zsu_events
             WHERE $where
             GROUP BY DATE(created_at)
             ORDER BY day ASC"
        );
    }

    /**
     * Get device split data.
     */
    public static function device_split( int $days = 30 ): array {
        global $wpdb;
        return (array) $wpdb->get_results( $wpdb->prepare(
            "SELECT device, COUNT(*) AS cnt
             FROM {$wpdb->prefix}zsu_events
             WHERE created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )
             GROUP BY device
             ORDER BY cnt DESC",
            $days
        ) );
    }

    /**
     * Get browser split data.
     */
    public static function browser_split( int $days = 30 ): array {
        global $wpdb;
        return (array) $wpdb->get_results( $wpdb->prepare(
            "SELECT browser, COUNT(*) AS cnt
             FROM {$wpdb->prefix}zsu_events
             WHERE created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )
             GROUP BY browser
             ORDER BY cnt DESC",
            $days
        ) );
    }

    /**
     * Get top performing shorts by views.
     */
    public static function top_shorts( int $limit = 10, int $days = 30 ): array {
        global $wpdb;
        return (array) $wpdb->get_results( $wpdb->prepare(
            "SELECT
                p.ID,
                p.post_title,
                COALESCE( ( SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_zsu_views'  LIMIT 1 ), 0 ) AS views,
                COALESCE( ( SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_zsu_likes'  LIMIT 1 ), 0 ) AS likes,
                COALESCE( ( SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_zsu_shares' LIMIT 1 ), 0 ) AS shares
             FROM {$wpdb->posts} p
             WHERE p.post_type   = %s
               AND p.post_status = 'publish'
             ORDER BY CAST(views AS UNSIGNED) DESC
             LIMIT %d",
            ZSU_CPT,
            $limit
        ) );
    }

    /**
     * Get vendor-specific stats.
     */
    public static function vendor_stats( int $vendor_id, int $days = 30 ): array {
        global $wpdb;

        $short_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_zsu_vendor_id'
               AND meta_value = %d",
            $vendor_id
        ) );

        if ( empty( $short_ids ) ) {
            return [
                'totals'       => [ 'views' => 0, 'likes' => 0, 'shares' => 0, 'clicks' => 0 ],
                'chart'        => [],
                'top'          => [],
                'coins'        => ZSU_Coins::balance( $vendor_id ),
                'shorts_count' => 0,
                'short_ids'    => [],
            ];
        }

        $placeholders = implode( ',', array_fill( 0, count( $short_ids ), '%d' ) );
        $in_clause    = $wpdb->prepare( "short_id IN ($placeholders)", ...$short_ids );

        $totals = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    SUM( CASE WHEN event_type = 'view'  THEN 1 ELSE 0 END ) AS views,
                    SUM( CASE WHEN event_type = 'like'  THEN 1 ELSE 0 END ) AS likes,
                    SUM( CASE WHEN event_type = 'share' THEN 1 ELSE 0 END ) AS shares,
                    SUM( CASE WHEN event_type = 'click' THEN 1 ELSE 0 END ) AS clicks
                 FROM {$wpdb->prefix}zsu_events
                 WHERE $in_clause
                   AND created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )",
                $days
            ),
            ARRAY_A
        ) ?: [ 'views' => 0, 'likes' => 0, 'shares' => 0, 'clicks' => 0 ];

        $chart = ZSU_Analytics::daily_chart( $days, $short_ids );

        $top = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT short_id, COUNT(*) AS cnt
                 FROM {$wpdb->prefix}zsu_events
                 WHERE $in_clause
                   AND event_type = 'view'
                   AND created_at >= DATE_SUB( NOW(), INTERVAL %d DAY )
                 GROUP BY short_id
                 ORDER BY cnt DESC
                 LIMIT 5",
                $days
            )
        );

        return [
            'totals'       => $totals,
            'chart'        => $chart,
            'top'          => $top,
            'coins'        => ZSU_Coins::balance( $vendor_id ),
            'shorts_count' => count( $short_ids ),
            'short_ids'    => $short_ids,
        ];
    }
}