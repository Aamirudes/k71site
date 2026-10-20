<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Boost — boost campaign management.
 */
class ZSU_Boost {

    public static function init(): void {
        // Nothing to hook at boot — all handled via AJAX (Chunk 2)
    }

    /**
     * Get boost price in coins for a given type and duration.
     */
    public static function get_price( string $boost_type, int $days = 7 ): int {
        $prices = [
            'standard' => (int) zsu_opt( 'zsu_boost_price_std',   199 ),
            'pro'      => (int) zsu_opt( 'zsu_boost_price_pro',   499 ),
            'ultra'    => (int) zsu_opt( 'zsu_boost_price_ultra', 999 ),
            'mega'     => (int) zsu_opt( 'zsu_boost_price_mega', 2499 ),
        ];
        $base = $prices[ $boost_type ] ?? $prices['standard'];
        return (int) round( $base * ( $days / 7 ) );
    }

    /**
     * Create a new boost campaign.
     * Returns [ 'success' => bool, 'message' => string, 'boost_id' => int ]
     */
    public static function create( int $short_id, int $vendor_id, string $boost_type, int $days ): array {
        global $wpdb;

        if ( ! zsu_opt( 'zsu_boost_enabled', 1 ) ) {
            return [ 'success' => false, 'message' => 'Boost system is disabled.' ];
        }

        $cost    = self::get_price( $boost_type, $days );
        $balance = ZSU_Coins::balance( $vendor_id );

        if ( $balance < $cost ) {
            return [
                'success' => false,
                'message' => "Insufficient coins. Need {$cost}, have {$balance}.",
            ];
        }

        // Deduct coins first
        $deducted = ZSU_Coins::spend( $vendor_id, $cost, "boost_{$boost_type}_d{$days}", $short_id );
        if ( ! $deducted ) {
            return [ 'success' => false, 'message' => 'Failed to deduct coins.' ];
        }

        // Insert boost record
        $wpdb->insert(
            $wpdb->prefix . 'zsu_boosts',
            [
                'short_id'   => $short_id,
                'vendor_id'  => $vendor_id,
                'boost_type' => $boost_type,
                'coins_spent'=> $cost,
                'starts_at'  => current_time( 'mysql' ),
                'ends_at'    => gmdate( 'Y-m-d H:i:s', strtotime( "+{$days} days" ) ),
                'status'     => 'active',
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s' ]
        );

        $boost_id = (int) $wpdb->insert_id;

        // Update short meta
        update_post_meta( $short_id, '_zsu_is_boosted', 1 );
        update_post_meta( $short_id, '_zsu_boost_type', $boost_type );

        // Send admin email
        ZSU_Email::notify_boost( $short_id, $vendor_id, $boost_type, $days, $cost );

        return [
            'success'    => true,
            'boost_id'   => $boost_id,
            'coins_spent'=> $cost,
            'coins_left' => ZSU_Coins::balance( $vendor_id ),
            'boost_type' => $boost_type,
            'days'       => $days,
            'message'    => "Boost activated for {$days} days!",
        ];
    }

    /**
     * Get active boosts for admin listing.
     */
    public static function get_all( int $limit = 200 ): array {
        global $wpdb;
        return (array) $wpdb->get_results( $wpdb->prepare(
            "SELECT b.*, p.post_title
             FROM {$wpdb->prefix}zsu_boosts b
             LEFT JOIN {$wpdb->posts} p ON p.ID = b.short_id
             ORDER BY b.created_at DESC
             LIMIT %d",
            $limit
        ) );
    }

    /**
     * Update boost status.
     */
    public static function set_status( int $boost_id, string $status ): bool {
        global $wpdb;
        $allowed = [ 'active', 'pending', 'expired', 'cancelled' ];
        if ( ! in_array( $status, $allowed, true ) ) return false;
        return (bool) $wpdb->update(
            $wpdb->prefix . 'zsu_boosts',
            [ 'status' => $status ],
            [ 'id'     => $boost_id ],
            [ '%s' ],
            [ '%d' ]
        );
    }
}