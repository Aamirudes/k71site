<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Email — all plugin email notifications.
 */
class ZSU_Email {

    public static function init(): void {
        // Hooks added on demand via other classes
    }

    private static function admin_email(): string {
        return get_option( 'admin_email' );
    }

    private static function site_name(): string {
        return get_bloginfo( 'name' );
    }

    /**
     * Notify admin when a vendor uploads a short.
     */
    public static function notify_upload( int $post_id, int $vendor_id, string $title, string $status ): void {
        if ( ! zsu_opt( 'zsu_email_on_upload', 1 ) ) return;

        wp_mail(
            self::admin_email(),
            sprintf( '🎬 [%s] New Short Uploaded — %s', self::site_name(), $title ),
            sprintf(
                "Vendor #%d uploaded a new short.\n\nTitle: %s\nStatus: %s\n\nEdit: %s",
                $vendor_id,
                $title,
                $status,
                admin_url( "post.php?post={$post_id}&action=edit" )
            )
        );
    }

    /**
     * Notify admin when a vendor requests a boost.
     */
    public static function notify_boost( int $short_id, int $vendor_id, string $type, int $days, int $cost ): void {
        if ( ! zsu_opt( 'zsu_email_on_boost', 1 ) ) return;

        wp_mail(
            self::admin_email(),
            sprintf( '🚀 [%s] Boost Activated', self::site_name() ),
            sprintf(
                "Vendor #%d activated a %s boost.\n\nShort ID: %d\nDuration: %d days\nCoins: %d\n\nEdit Short: %s",
                $vendor_id,
                strtoupper( $type ),
                $short_id,
                $days,
                $cost,
                admin_url( "post.php?post={$short_id}&action=edit" )
            )
        );
    }

    /**
     * Notify vendor when their short is approved.
     */
    public static function notify_approved( int $post_id, int $vendor_id ): void {
        if ( ! zsu_opt( 'zsu_email_on_approve', 1 ) ) return;
        $vendor = get_userdata( $vendor_id );
        if ( ! $vendor ) return;

        wp_mail(
            $vendor->user_email,
            sprintf( '✅ [%s] Your Short Was Approved!', self::site_name() ),
            sprintf(
                "Great news! Your short \"%s\" has been approved and is now live.\n\nView it here: %s",
                get_the_title( $post_id ),
                home_url( '/shorts' )
            )
        );
    }

    /**
     * Notify vendor when their short is rejected.
     */
    public static function notify_rejected( int $post_id, int $vendor_id ): void {
        if ( ! zsu_opt( 'zsu_email_on_reject', 1 ) ) return;
        $vendor = get_userdata( $vendor_id );
        if ( ! $vendor ) return;

        wp_mail(
            $vendor->user_email,
            sprintf( '❌ [%s] Your Short Needs Revision', self::site_name() ),
            sprintf(
                "Unfortunately your short \"%s\" was not approved. Please review it and re-submit.\n\nContact us if you have questions.",
                get_the_title( $post_id )
            )
        );
    }
}