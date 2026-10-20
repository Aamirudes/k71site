<?php
/**
 * Plugin Name:       Zilla Shorts Ultra v11
 * Plugin URI:        https://marblemandi.com/shorts
 * Description:       Enterprise-grade shoppable vertical reels engine. Instagram-style lightbox, Dokan Vendor Studio, Admin Analytics, Boost/Coins system, 3D glassmorphic UI, particle backgrounds, 10 premium presets, 100+ features.
 * Version:           11.0.0
 * Author:            MarbleMandi Dev Team
 * Author URI:        https://marblemandi.com
 * Text Domain:       zsu
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Tested up to:      6.7
 * WC requires at least: 7.0
 * WC tested up to:   9.5
 * License:           GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
//  §0  SHARED HOSTING: CLOSE DB ON AJAX SHUTDOWN
// ═══════════════════════════════════════════════════════════════════
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    add_action( 'shutdown', static function () {
        global $wpdb;
        if ( isset( $wpdb->dbh ) && $wpdb->dbh instanceof mysqli ) {
            @mysqli_close( $wpdb->dbh );
        }
    }, PHP_INT_MAX );
}

// ═══════════════════════════════════════════════════════════════════
//  §1  CONSTANTS
// ═══════════════════════════════════════════════════════════════════
if ( ! defined( 'ZSU_VER' ) ) {
    define( 'ZSU_VER', '11.14.0' );
    define( 'ZSU_SLUG',         'zsu' );
    define( 'ZSU_FILE',         __FILE__ );
    define( 'ZSU_DIR',          plugin_dir_path( __FILE__ ) );
    define( 'ZSU_URL',          plugin_dir_url( __FILE__ ) );
    define( 'ZSU_ASSETS_URL',   ZSU_URL  . 'assets/' );
    define( 'ZSU_ASSETS_DIR',   ZSU_DIR  . 'assets/' );
    define( 'ZSU_CPT',          'zsu_short' );
    define( 'ZSU_TAX',          'zsu_short_cat' );
    define( 'ZSU_NONCE',        'zsu_nonce_v11' );
    define( 'ZSU_CAP',          'manage_options' );
    define( 'ZSU_DB_VER',       '11' );
    define( 'ZSU_LOG',          ZSU_DIR . 'zsu-debug.log' );
    define( 'ZSU_UPLOAD_DIR',   WP_CONTENT_DIR . '/uploads/zsu-shorts/' );
    define( 'ZSU_UPLOAD_URL',   WP_CONTENT_URL  . '/uploads/zsu-shorts/' );
    define( 'ZSU_OPTION_GROUP', 'zsu_options' );
}

// ═══════════════════════════════════════════════════════════════════
//  §2  AUTOLOAD INCLUDES — only files that actually exist
// ═══════════════════════════════════════════════════════════════════
$zsu_includes = [
    'includes/helpers.php',
    'includes/class-zsu-install.php',
    'includes/class-zsu-cpt.php',
    'includes/class-zsu-coins.php',
    'includes/class-zsu-analytics.php',
    'includes/class-zsu-boost.php',
    'includes/class-zsu-seo.php',
    'includes/class-zsu-email.php',
    'includes/class-zsu-ajax.php',
    'includes/class-zsu-admin.php',
    'includes/class-zsu-frontend.php',
    'includes/class-zsu-home-rail.php',
];

foreach ( $zsu_includes as $file ) {
    $path = ZSU_DIR . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

// ═══════════════════════════════════════════════════════════════════
//  §3  ACTIVATION HOOK
// ═══════════════════════════════════════════════════════════════════
register_activation_hook( __FILE__, [ 'ZSU_Install', 'activate' ] );

// ═══════════════════════════════════════════════════════════════════
//  §4  DEACTIVATION HOOK
// ═══════════════════════════════════════════════════════════════════
register_deactivation_hook( __FILE__, [ 'ZSU_Install', 'deactivate' ] );

// ═══════════════════════════════════════════════════════════════════
//  §5  BOOT PLUGIN
// ═══════════════════════════════════════════════════════════════════
add_action( 'plugins_loaded', 'zsu_boot', 5 );
function zsu_boot() {
    // DB upgrade check
    if ( get_option( 'zsu_db_version' ) !== ZSU_DB_VER ) {
        ZSU_Install::create_tables();
        update_option( 'zsu_db_version', ZSU_DB_VER );
    }

    // Ensure upload directory exists
    ZSU_Install::ensure_upload_dir();

    // Boot core modules
    ZSU_CPT::init();
    ZSU_Ajax::init();
    ZSU_Admin::init();
    ZSU_Frontend::init();
    ZSU_Home_Rail::init();
    ZSU_Analytics::init();
    ZSU_Boost::init();
    ZSU_SEO::init();
    ZSU_Email::init();

    // Boot Dokan Vendor Studio ONLY if Dokan plugin is active
    // and the studio file exists — safe conditional load
    if (
        ( function_exists( 'dokan' ) || class_exists( 'WeDevs_Dokan' ) || defined( 'DOKAN_PLUGIN_VERSION' ) )
        && file_exists( ZSU_DIR . 'includes/class-zsu-vendor-studio.php' )
    ) {
        require_once ZSU_DIR . 'includes/class-zsu-vendor-studio.php';
        if ( class_exists( 'ZSU_Vendor_Studio' ) ) {
            ZSU_Vendor_Studio::init();
        }

        // Inject "Shorts" tab on public vendor store pages
        if ( file_exists( ZSU_DIR . 'includes/class-zsu-dokan-store-tab.php' ) ) {
            require_once ZSU_DIR . 'includes/class-zsu-dokan-store-tab.php';
            if ( class_exists( 'ZSU_Dokan_Store_Tab' ) ) {
                ZSU_Dokan_Store_Tab::init();
            }
        }
    }
}
