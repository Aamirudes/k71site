<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Coins — manages the vendor/user coin ledger.
 */
class ZSU_Coins {

    /** Boot — no automatic coin rewards from activities. */
    public static function init(): void {
        // Activity-based coin rewards disabled.
    }


    /**
     * Get a user's current coin balance.
     */
    public static function balance( int $user_id ): int {
        global $wpdb;
        $balance = $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE( SUM(delta), 0 )
             FROM {$wpdb->prefix}zsu_coins
             WHERE user_id = %d",
            $user_id
        ) );
        return (int) $balance;
    }

    /**
     * Add (or subtract) coins for a user.
     */
    public static function add( int $user_id, int $delta, string $reason = '', int $ref_id = 0, array $meta = [] ): bool {
        global $wpdb;

        $new_balance = self::balance( $user_id ) + $delta;

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'zsu_coins',
            [
                'user_id'    => $user_id,
                'delta'      => $delta,
                'balance'    => $new_balance,
                'reason'     => sanitize_text_field( $reason ),
                'ref_id'     => $ref_id,
                'meta'       => ! empty( $meta ) ? wp_json_encode( $meta ) : null,
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%d', '%s', '%d', '%s', '%s' ]
        );

        return (bool) $inserted;
    }

    /**
     * Spend coins — returns false if balance is insufficient.
     */
    public static function spend( int $user_id, int $amount, string $reason = '', int $ref_id = 0 ): bool {
        if ( self::balance( $user_id ) < $amount ) {
            return false;
        }
        return self::add( $user_id, -$amount, $reason, $ref_id );
    }

    /**
     * Get a user's transaction history (most recent first).
     */
    public static function history( int $user_id, int $limit = 50 ): array {
        global $wpdb;
        return (array) $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}zsu_coins
             WHERE user_id = %d
             ORDER BY created_at DESC
             LIMIT %d",
            $user_id,
            $limit
        ) );
    }
}