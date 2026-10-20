<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Frontend — handles the /shorts virtual page, shortcode,
 * asset enqueuing, and all frontend HTML output.
 */
class ZSU_Frontend {

    public static function init(): void {
        add_action( 'init',              [ self::class, 'add_rewrite_rules' ] );
        add_filter( 'query_vars',        [ self::class, 'add_query_vars' ] );

        // Hook into parse_request (very early, before template_redirect)
        // This catches the /shorts/ URL even if WP resolves it to a real page
        add_action( 'parse_request',     [ self::class, 'early_intercept' ], 1 );

        // Fallback: template_redirect (standard hook)
        add_action( 'template_redirect', [ self::class, 'intercept_shorts_page' ] );

        // Fallback 2: template_include filter — catches cases where a real WP page
        // has the slug "shorts" and gets routed there instead of our virtual page
        add_filter( 'template_include',  [ self::class, 'intercept_via_template' ], 999 );

        add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
        add_shortcode( 'zsu_shorts',  [ self::class, 'shortcode' ] );
        add_shortcode( 'zsu_short',   [ self::class, 'shortcode_single' ] );

        // Flush rewrites once after any slug change
        add_action( 'template_redirect', [ self::class, 'maybe_flush' ], 1 );

        // Safety net: inject cleanup script on non-shorts pages (removes scroll lock)
        add_action( 'wp_footer', [ self::class, 'inject_cleanup_script' ], 999 );

        // Prevent WordPress from issuing a 404 on our virtual /shorts/ URL
        add_filter( 'pre_handle_404', [ self::class, 'pre_handle_404_check' ], 1, 2 );
    }

    /**
     * Inject a tiny inline script on every regular WP page (not the virtual shorts page).
     * This is a safety net: if the user navigated away while lightbox was open,
     * the zsu-no-scroll class on <body> would have persisted — this removes it instantly.
     */
    public static function inject_cleanup_script(): void {
        // Never inject on the shorts virtual page itself
        if ( self::is_shorts_url() || get_query_var( 'zsu_shorts_page' ) ) return;

        // Inject tiny cleanup script — removes any scroll lock left over from shorts lightbox
        // This is a pure safety net for logged-in users navigating back from shorts
        echo '<script>!function(){try{var b=document.body,h=document.documentElement;b.classList.remove("zsu-no-scroll");b.style.overflow="";b.style.position="";b.style.top="";h.style.overflow="";}catch(e){}}();</script>' . "\n";
    }

    // ─── Rewrite rules ───────────────────────────────────────────
    public static function add_rewrite_rules(): void {
        $slug = sanitize_title( zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) );
        add_rewrite_rule( '^' . $slug . '/?$',                    'index.php?zsu_shorts_page=1', 'top' );
        add_rewrite_rule( '^' . $slug . '/cat/([^/]+)/?$',        'index.php?zsu_shorts_page=1&zsu_cat=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $slug . '/v/([0-9]+)/?$',         'index.php?zsu_shorts_page=1&zsu_open=$matches[1]', 'top' );

        // Auto-flush rewrite rules if our rules are missing from WP's compiled list.
        // This self-heals after plugin updates, slug changes, or cache purges.
        global $wp_rewrite;
        $compiled = $wp_rewrite->rules ?? [];
        $our_rule = '^' . $slug . '/?$';
        if ( ! isset( $compiled[ $our_rule ] ) ) {
            // Schedule a flush on shutdown (safe — doesn't double-flush)
            add_action( 'shutdown', static function () {
                flush_rewrite_rules( false );
            }, 999 );
        }
    }

    // ─── Prevent WP 404 on our virtual slug ──────────────────────
    // Some themes / plugins set 404 headers before template_redirect fires.
    // This hook fires early and tells WP our URL is valid.
    public static function pre_handle_404_check( bool $handled, \WP_Query $query ): bool {
        $slug = sanitize_title( zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) );
        $uri  = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
        if ( strpos( $uri, $slug ) === 0 || strpos( $uri, '/' . $slug ) !== false ) {
            // Tell WP this is handled — prevents premature 404
            return true;
        }
        return $handled;
    }

    // ─── Is the current URL our shorts slug? ─────────────────────
    // This is the ONLY reliable check. It doesn't care what WordPress
    // thinks the page is (home, blog, 404, page) — just checks the URL.
    private static function is_shorts_url(): bool {
        $slug = sanitize_title( zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) );
        if ( empty( $slug ) ) return false;
        $uri   = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?? '/', '/' );
        // Remove subdirectory prefix if WP is installed in a subfolder
        $home  = trim( parse_url( home_url(), PHP_URL_PATH ) ?? '/', '/' );
        if ( $home && strpos( $uri, $home ) === 0 ) {
            $uri = trim( substr( $uri, strlen( $home ) ), '/' );
        }
        // Match: "shorts" or "shorts/v/123" or "shorts/cat/foo"
        return $uri === $slug
            || strpos( $uri, $slug . '/' ) === 0;
    }

    public static function add_query_vars( array $vars ): array {
        $vars[] = 'zsu_shorts_page';
        $vars[] = 'zsu_cat';
        $vars[] = 'zsu_open';
        return $vars;
    }

    public static function maybe_flush(): void {
        if ( get_option( 'zsu_flush_rewrites' ) === '1' ) {
            flush_rewrite_rules();
            delete_option( 'zsu_flush_rewrites' );
        }
    }

    // ─── Early intercept via parse_request ──────────────────────
    public static function early_intercept( \WP $wp ): void {
        if ( ! self::is_shorts_url() ) return;

        // Force our query var
        $wp->set_query_var( 'zsu_shorts_page', 1 );
        set_query_var( 'zsu_shorts_page', 1 );

        // Parse sub-paths from URL
        $slug  = sanitize_title( zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) );
        $uri   = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?? '/', '/' );
        $home  = trim( parse_url( home_url(), PHP_URL_PATH ) ?? '/', '/' );
        if ( $home && strpos( $uri, $home ) === 0 ) {
            $uri = trim( substr( $uri, strlen( $home ) ), '/' );
        }
        $parts = explode( '/', ltrim( substr( $uri, strlen( $slug ) ), '/' ) );

        if ( isset( $parts[0], $parts[1] ) && $parts[0] === 'v' ) {
            $wp->set_query_var( 'zsu_open', absint( $parts[1] ) );
            set_query_var( 'zsu_open', absint( $parts[1] ) );
        }
        if ( isset( $parts[0], $parts[1] ) && $parts[0] === 'cat' ) {
            $wp->set_query_var( 'zsu_cat', sanitize_text_field( $parts[1] ) );
            set_query_var( 'zsu_cat', sanitize_text_field( $parts[1] ) );
        }
    }

    // ─── Intercept via template_include (absolute last resort) ──
    public static function intercept_via_template( string $template ): string {
        if ( ! self::is_shorts_url() ) return $template;

        // Render shorts page (cleans buffer inside render_full_page)
        while ( ob_get_level() > 0 ) ob_end_clean();
        self::render_full_page();
        exit;
    }

    // ─── Intercept /shorts virtual page ─────────────────────────
    public static function intercept_shorts_page(): void {
        // Must match our shorts URL (query var OR direct URL match)
        $url_match = self::is_shorts_url();
        $qv_match  = (bool) get_query_var( 'zsu_shorts_page' );

        if ( ! $url_match && ! $qv_match ) return;

        // Clean any buffered output from theme/plugins
        while ( ob_get_level() > 0 ) ob_end_clean();
        self::render_full_page();
        exit;
    }

    // ─── WHITELIST: Is this page a ZSU shorts page? ─────────────
    // Returns TRUE only when the current page explicitly uses our shortcode.
    // Checks EVERY possible scenario so logged-in users get same protection.
    private static function is_zsu_shortcode_page(): bool {
        // Never in admin
        if ( is_admin() ) return false;

        // Never on the shorts virtual URL itself (it has its own asset pipeline)
        if ( self::is_shorts_url() || get_query_var( 'zsu_shorts_page' ) ) return false;

        // Never on the true homepage (empty path)
        if ( is_front_page() && ! is_home() ) return false;

        // Must be singular (single post/page). Archives/search/404 = never.
        if ( ! is_singular() ) return false;

        // Must be a real WP_Post object
        $post = get_queried_object();
        if ( ! ( $post instanceof WP_Post ) ) return false;

        // ── Absolute blocklist for known non-shorts pages ──────────
        // WooCommerce pages (logged-in users hit these a lot)
        $wc_page_ids = array_filter( [
            (int) get_option( 'woocommerce_shop_page_id' ),
            (int) get_option( 'woocommerce_cart_page_id' ),
            (int) get_option( 'woocommerce_checkout_page_id' ),
            (int) get_option( 'woocommerce_myaccount_page_id' ),
        ] );
        if ( in_array( $post->ID, $wc_page_ids, true ) ) return false;

        // Block any WooCommerce endpoint pages (logged-in users on /my-account/orders/ etc.)
        if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) return false;
        if ( function_exists( 'is_account_page' ) && is_account_page() )  return false;
        if ( function_exists( 'is_cart' )          && is_cart() )          return false;
        if ( function_exists( 'is_checkout' )      && is_checkout() )      return false;

        // Block Dokan vendor dashboard (logged-in vendors)
        if ( function_exists( 'dokan_is_seller_dashboard' ) && dokan_is_seller_dashboard() ) return false;

        // Block any page with Dokan, WooCommerce, or other known shortcodes
        // (belt-and-suspenders: if post has those, it's not our page)
        $blocked_shortcodes = [ 'woocommerce_my_account', 'dokan-dashboard', 'dokan_dashboard', 'woocommerce_cart', 'woocommerce_checkout' ];
        foreach ( $blocked_shortcodes as $sc ) {
            if ( has_shortcode( $post->post_content, $sc ) ) return false;
        }

        // ── WHITELIST check: post must have our shortcode ──────────
        $content = $post->post_content ?? '';
        return has_shortcode( $content, 'zsu_shorts' )
            || has_shortcode( $content, 'zsu_short' );
    }

    // ─── Enqueue frontend assets ─────────────────────────────────
    public static function enqueue_assets(): void {
        // On the virtual /shorts/ full page, assets are output directly in render_full_page()
        if ( get_query_var( 'zsu_shorts_page' ) ) {
            return;
        }

        // ════════════════════════════════════════════════════════
        // NUCLEAR ISOLATION — WHITELIST ONLY approach.
        // Assets load ONLY when we can 100% confirm we are on a
        // page that explicitly uses our shortcode.
        // Works for both logged-in and logged-out users.
        // ════════════════════════════════════════════════════════
        if ( ! self::is_zsu_shortcode_page() ) return;

        // Google Font (based on active preset)
        $font = sanitize_text_field( zsu_opt( 'zsu_font_family', 'Inter' ) );
        if ( $font && $font !== 'inherit' ) {
            $font_url = 'https://fonts.googleapis.com/css2?family='
                . urlencode( $font ) . ':wght@400;500;600;700;800;900&display=swap';
            wp_enqueue_style( 'zsu-gfont', $font_url, [], null );
        }

        // ── Frontend CSS — REAL file ─────────────────────────────
        wp_enqueue_style(
            'zsu-frontend',
            ZSU_ASSETS_URL . 'css/zsu-frontend.css',
            [],
            ZSU_VER . '.' . @filemtime( ZSU_ASSETS_DIR . 'css/zsu-frontend.css' )
        );

        // ── Frontend JS — REAL file ──────────────────────────────
        wp_enqueue_script(
            'zsu-frontend',
            ZSU_ASSETS_URL . 'js/zsu-frontend.js',
            [],
            ZSU_VER . '.' . @filemtime( ZSU_ASSETS_DIR . 'js/zsu-frontend.js' ),
            false  // load in <head> — DOMContentLoaded must fire AFTER script executes
        );

        // ── Dynamic CSS vars injected via wp_add_inline_style ────
        // This is the CORRECT pattern: attach inline styles to a
        // REAL registered handle (zsu-frontend above), NOT false.
        $inline_css = self::build_dynamic_css();
        wp_add_inline_style( 'zsu-frontend', $inline_css );

        // ── Localize script (attached to REAL handle) ────────────
        wp_localize_script( 'zsu-frontend', 'ZSU', self::build_js_config() );
    }

    // ─── Dynamic CSS vars (theme engine) ────────────────────────
    private static function build_dynamic_css(): string {
        $primary   = sanitize_hex_color( zsu_opt( 'zsu_primary_color',   '#7c3aed' ) ) ?: '#7c3aed';
        $accent    = sanitize_hex_color( zsu_opt( 'zsu_accent_color',    '#f59e0b' ) ) ?: '#f59e0b';
        $g1        = sanitize_hex_color( zsu_opt( 'zsu_gradient_start',  '#0f0c29' ) ) ?: '#0f0c29';
        $g2        = sanitize_hex_color( zsu_opt( 'zsu_gradient_end',    '#302b63' ) ) ?: '#302b63';
        $font      = sanitize_text_field( zsu_opt( 'zsu_font_family',    'Inter' ) );
        $radius    = absint( zsu_opt( 'zsu_border_radius', 20 ) );
        $neon      = zsu_opt( 'zsu_neon_glow', 1 ) ? '0 0 20px ' . $primary . '55, 0 0 40px ' . $primary . '22' : 'none';
        $glass_bg  = zsu_opt( 'zsu_glassmorphism', 1 ) ? 'rgba(15,12,41,0.6)' : 'rgba(15,12,41,0.92)';
        $font_safe = esc_attr( $font );
        $heading_color = sanitize_hex_color( zsu_opt( 'zsu_heading_color', '#ffffff' ) ) ?: '#ffffff';

        return ".zsu-body, .zsu-embedded-wrap, .zsu-lightbox {
            --zsu-primary:       {$primary};
            --zsu-accent:        {$accent};
            --zsu-grad-start:    {$g1};
            --zsu-grad-end:      {$g2};
            --zsu-font:          '{$font_safe}', -apple-system, BlinkMacSystemFont, sans-serif;
            --zsu-radius:        {$radius}px;
            --zsu-radius-sm:     " . max(4, $radius - 8) . "px;
            --zsu-radius-lg:     " . ($radius + 8) . "px;
            --zsu-neon:          {$neon};
            --zsu-glass-bg:      {$glass_bg};
            --zsu-primary-rgb:   " . self::hex_to_rgb( $primary ) . ";
            --zsu-accent-rgb:    " . self::hex_to_rgb( $accent ) . ";
            --zsu-heading-color: {$heading_color};
        }
        .zsu-section-heading { color: {$heading_color} !important; }
        .zsu-section-heading .zsu-heading-icon { color: {$heading_color} !important; }
        .zsu-section-heading .zsu-heading-count { color: {$heading_color} !important; opacity: 0.7; }
        .zsu-body #zsuWheelFab { display: none !important; }";
    }

    private static function hex_to_rgb( string $hex ): string {
        $hex = ltrim( $hex, '#' );
        if ( strlen($hex) === 3 ) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec( substr($hex,0,2) );
        $g = hexdec( substr($hex,2,2) );
        $b = hexdec( substr($hex,4,2) );
        return "{$r},{$g},{$b}";
    }

    // ─── JS config object ────────────────────────────────────────
    private static function build_js_config(): array {
        $cats  = get_terms( [ 'taxonomy' => ZSU_TAX, 'hide_empty' => true ] );
        $cat_data = [];
        if ( ! is_wp_error( $cats ) ) {
            foreach ( $cats as $c ) {
                $cat_data[] = [
                    'slug'  => $c->slug,
                    'name'  => $c->name,
                    'count' => $c->count,
                ];
            }
        }

        return [
            'ajax_url'       => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( ZSU_NONCE ),
            'logged_in'      => is_user_logged_in() ? 1 : 0,
            'user_id'        => get_current_user_id(),
            'autoplay'       => (int) zsu_opt( 'zsu_autoplay', 1 ),
            'muted_start'    => (int) zsu_opt( 'zsu_muted_start', 1 ),
            'loop'           => (int) zsu_opt( 'zsu_loop', 1 ),
            'lazy_load'      => (int) zsu_opt( 'zsu_lazy_load', 1 ),
            'preload_next'   => (int) zsu_opt( 'zsu_preload_next', 1 ),
            'per_page'       => (int) zsu_opt( 'zsu_videos_per_page', 24 ),
            'grid_cols'      => (int) zsu_opt( 'zsu_grid_columns', 2 ),
            'show_follow'    => (int) zsu_opt( 'zsu_show_follow_btn', 1 ),
            'show_share'     => (int) zsu_opt( 'zsu_show_share_btn', 1 ),
            'show_like'      => (int) zsu_opt( 'zsu_show_like_btn', 1 ),
            'show_featured'  => (int) zsu_opt( 'zsu_show_featured', 1 ),
            'show_comments'  => (int) zsu_opt( 'zsu_show_comment_btn', 0 ),
            'particle_bg'    => (int) zsu_opt( 'zsu_particle_bg', 1 ),
            'tilt_cards'     => (int) zsu_opt( 'zsu_tilt_cards', 1 ),
            'confetti_like'  => (int) zsu_opt( 'zsu_confetti_like', 1 ),
            'neon_glow'      => (int) zsu_opt( 'zsu_neon_glow', 1 ),
            'glassmorphism'  => (int) zsu_opt( 'zsu_glassmorphism', 1 ),
            'animation_level'=> zsu_opt( 'zsu_animation_level', 'ultra' ),
            'heatmap'        => (int) zsu_opt( 'zsu_heatmap_enabled', 1 ),
            'ab_testing'     => (int) zsu_opt( 'zsu_ab_testing', 0 ),
            'enable_comments'=> (int) zsu_opt( 'zsu_enable_comments', 0 ),
            'enable_ratings' => (int) zsu_opt( 'zsu_enable_ratings', 1 ),
            'enable_wishlists'=> (int) zsu_opt( 'zsu_enable_wishlists', 1 ),
            'categories'     => $cat_data,
            'home_url'       => home_url(),
            'shorts_url'     => home_url( '/' . zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) ),
            'open_id'        => (int) get_query_var( 'zsu_open', 0 ),
            'open_cat'       => sanitize_text_field( get_query_var( 'zsu_cat', '' ) ),
            'primary_color'  => zsu_opt( 'zsu_primary_color', '#7c3aed' ),
            'accent_color'   => zsu_opt( 'zsu_accent_color', '#f59e0b' ),
            'spin_wheel_enabled' => (int) zsu_opt( 'zsu_spin_wheel_enabled', 1 ),
            'spin_wheel_prizes'  => json_decode( zsu_opt( 'zsu_spin_wheel_prizes', '[]' ), true ) ?: [],
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  FULL PAGE RENDER
    // ═══════════════════════════════════════════════════════════════
    public static function render_full_page(): void {
        // Guard: only render if we're actually on the shorts URL
        if ( ! self::is_shorts_url() && ! get_query_var( 'zsu_shorts_page' ) ) return;

        // Assets output directly below (not via wp_enqueue) for virtual page compatibility

        $site_name  = get_bloginfo( 'name' );
        $site_desc  = get_bloginfo( 'description' );
        $slug       = zsu_opt( 'zsu_shorts_page_slug', 'shorts' );
        $canonical  = home_url( '/' . $slug . '/' );
        $og_image   = get_site_icon_url( 1200 ) ?: '';

        // When sharing a specific short (/shorts/v/{id}/), use that short's thumbnail
        $open_id = (int) get_query_var( 'zsu_open', 0 );
        $og_title       = 'Shorts — ' . $site_name;
        $og_description = 'Shoppable video shorts on ' . $site_name;
        if ( $open_id ) {
            $open_post = get_post( $open_id );
            if ( $open_post && $open_post->post_type === ZSU_CPT && $open_post->post_status === 'publish' ) {
                $open_meta = zsu_get_short_meta( $open_id );
                if ( ! empty( $open_meta['thumb_url'] ) ) {
                    $og_image = $open_meta['thumb_url'];
                }
                $og_title       = get_the_title( $open_id ) . ' — ' . $site_name;
                $og_description = wp_strip_all_tags( get_the_excerpt( $open_id ) ) ?: $og_description;
                $canonical      = home_url( '/' . $slug . '/v/' . $open_id . '/' );
            }
        }

        // Get categories for rails
        $cats = get_terms( [ 'taxonomy' => ZSU_TAX, 'hide_empty' => true, 'number' => 20 ] );
        if ( is_wp_error($cats) ) $cats = [];

        ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="<?php echo esc_attr( zsu_opt('zsu_gradient_start','#0f0c29') ); ?>">
<title>Shorts — <?php echo esc_html($site_name); ?></title>
<meta name="description" content="Discover shoppable video shorts on <?php echo esc_html($site_name); ?>">
<link rel="canonical" href="<?php echo esc_url($canonical); ?>">
<meta property="og:type"        content="website">
<meta property="og:url"         content="<?php echo esc_url($canonical); ?>">
<meta property="og:title"       content="<?php echo esc_html($og_title); ?>">
<meta property="og:description" content="<?php echo esc_html($og_description); ?>">
<?php if ($og_image) : ?><meta property="og:image" content="<?php echo esc_url($og_image); ?>"><?php endif; ?>
<meta name="twitter:card"  content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_html($og_title); ?>">
<?php
    // Direct asset output - guarantees CSS/JS loads on virtual page
    // (template_redirect fires before wp_enqueue_scripts, so we output tags directly)
    $zsu_font = sanitize_text_field( zsu_opt( 'zsu_font_family', 'Inter' ) );
    if ( $zsu_font && $zsu_font !== 'inherit' ) {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="stylesheet" href="' . esc_url('https://fonts.googleapis.com/css2?family=' . urlencode($zsu_font) . ':wght@400;500;600;700;800;900&display=swap') . '">' . "\n";
    }
    echo '<link rel="stylesheet" href="' . esc_url( ZSU_ASSETS_URL . 'css/zsu-frontend.css?v=' . ZSU_VER ) . '">' . "\n";
    echo '<style id="zsu-inline-css">' . self::build_dynamic_css() . '</style>' . "\n";
    echo '<script>var ZSU = ' . wp_json_encode( self::build_js_config() ) . ';</script>' . "\n";
    echo '<script src="' . esc_url( ZSU_ASSETS_URL . 'js/zsu-frontend.js?v=' . ZSU_VER ) . '"></script>' . "\n";
    wp_head();
?>
</head>
<body class="zsu-body <?php echo zsu_opt('zsu_neon_glow',1) ? 'zsu-neon-on' : ''; ?> <?php echo zsu_opt('zsu_glassmorphism',1) ? 'zsu-glass-on' : ''; ?> <?php echo zsu_opt('zsu_particle_bg',1) ? 'zsu-particles-on' : ''; ?>">

<!-- ════════════════════════════════════════════════════════════ -->
<!--  PARTICLE BACKGROUND CANVAS                                 -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if ( zsu_opt('zsu_particle_bg', 1) ) : ?>
<canvas id="zsuParticleCanvas" aria-hidden="true"></canvas>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!--  TOP NAV BAR                                                 -->
<!-- ════════════════════════════════════════════════════════════ -->
<header class="zsu-header" id="zsuHeader" role="banner">
    <div class="zsu-header-inner">

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url('/') ); ?>" class="zsu-logo" aria-label="Home">
            <?php
            $logo_id = get_option('site_icon');
            if ( $logo_id ) {
                echo wp_get_attachment_image( $logo_id, [32,32], false, ['class'=>'zsu-logo-img','alt'=>esc_attr($site_name)] );
            } else {
                echo '<span class="zsu-logo-text">⚡ ' . esc_html($site_name) . '</span>';
            }
            ?>
        </a>

        <!-- Search bar -->
        <div class="zsu-header-search" id="zsuHeaderSearch">
            <button class="zsu-search-trigger" id="zsuSearchTrigger" aria-label="Search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>
            <div class="zsu-search-dropdown" id="zsuSearchDropdown">
                <div class="zsu-search-input-wrap">
                    <svg class="zsu-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="search" class="zsu-search-field" id="zsuSearchField"
                           placeholder="Search shorts, products, vendors…" autocomplete="off" aria-label="Search">
                    <button class="zsu-search-clear" id="zsuSearchClear" aria-label="Clear">✕</button>
                </div>
                <div class="zsu-search-results" id="zsuSearchResults" role="listbox" aria-label="Search results"></div>
            </div>
        </div>

        <!-- Right actions -->
        <div class="zsu-header-actions">

            <!-- View toggle -->
            <div class="zsu-view-toggle" role="group" aria-label="View mode">
                <button class="zsu-view-btn active" id="zsuViewGrid" aria-label="Grid view" title="Grid">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </button>
                <button class="zsu-view-btn" id="zsuViewFeed" aria-label="Feed view" title="Feed">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="5" rx="1"/><rect x="3" y="10" width="18" height="5" rx="1"/><rect x="3" y="17" width="18" height="5" rx="1"/></svg>
                </button>
            </div>

            <!-- Upload CTA (vendor only) -->
            <?php if ( zsu_current_vendor_id() ) : ?>
            <a href="<?php echo esc_url( function_exists('dokan_get_navigation_url') ? dokan_get_navigation_url('zsu-studio') : home_url('/dashboard/zsu-studio/') ); ?>"
               class="zsu-btn-upload" title="Upload Short">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Upload
            </a>
            <?php endif; ?>

            <!-- Login / Avatar -->
            <?php if ( is_user_logged_in() ) :
                $uid    = get_current_user_id();
                $avatar = get_avatar_url($uid, ['size'=>36]);
            ?>
            <button class="zsu-avatar-btn" id="zsuAvatarBtn" aria-label="Account">
                <img src="<?php echo esc_url($avatar); ?>" class="zsu-avatar-img" alt="Account" loading="lazy">
            </button>
            <div class="zsu-avatar-dropdown" id="zsuAvatarDropdown">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="zsu-dd-item">👤 My Account</a>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"   class="zsu-dd-item">📦 Orders</a>
                <?php if ( zsu_current_vendor_id() ) : ?>
                <a href="<?php echo esc_url( function_exists('dokan_get_navigation_url') ? dokan_get_navigation_url('') : home_url('/dashboard/') ); ?>" class="zsu-dd-item">🏪 Vendor Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo esc_url(wp_logout_url()); ?>" class="zsu-dd-item zsu-dd-item-danger">🚪 Logout</a>
            </div>
            <?php else : ?>
           <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="zsu-btn-login">
    Sign In
</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ════════════════════════════════════════════════════════════ -->
<!--  MAIN CONTENT WRAPPER                                        -->
<!-- ════════════════════════════════════════════════════════════ -->
<main class="zsu-main" id="zsuMain" role="main">

    <!-- ── FEATURED HERO RAIL ──────────────────────────────────── -->
    <?php if ( zsu_opt('zsu_show_featured', 1) ) : ?>
    <section class="zsu-featured-section" id="zsuFeaturedSection" aria-label="Featured Shorts">
        <div class="zsu-section-header-row">
            <h2 class="zsu-section-heading">
                <span class="zsu-heading-icon">⭐</span>
                Featured
            </h2>
            <a href="#" class="zsu-see-all" data-filter="featured">See All →</a>
        </div>
        <div class="zsu-featured-rail" id="zsuFeaturedRail" role="list" aria-label="Featured shorts">
            <?php echo self::render_skeleton_cards(8, 'rail'); ?>
        </div>
    </section>
    <?php endif; ?>

    

    <!-- ── MAIN SHORTS GRID ─────────────────────────────────────── -->
    <section class="zsu-grid-section" id="zsuGridSection" aria-label="All Shorts">
        <div class="zsu-section-header-row">
            <h2 class="zsu-section-heading" id="zsuGridHeading">
                <span class="zsu-heading-icon">🎬</span>
                Latest Shorts
            </h2>
            <div class="zsu-grid-result-count" id="zsuGridCount"></div>
        </div>

        <!-- Shorts grid -->
        <div class="zsu-grid" id="zsuGrid"
             style="--cols:<?php echo (int) zsu_opt('zsu_grid_columns',2); ?>"
             role="list" aria-label="Shorts grid">
            <?php echo self::render_skeleton_cards(12, 'grid'); ?>
        </div>

        <!-- Infinite scroll sentinel -->
        <div class="zsu-load-more-sentinel" id="zsuSentinel" aria-hidden="true">
            <div class="zsu-load-spinner" id="zsuLoadSpinner" style="display:none;">
                <div class="zsu-spinner-ring"></div>
                <span>Loading more…</span>
            </div>
            <button class="zsu-load-more-btn" id="zsuLoadMoreBtn" style="display:none;">
                Load More
            </button>
        </div>

        <!-- Empty state -->
        <div class="zsu-grid-empty" id="zsuGridEmpty" style="display:none;" role="alert">
            <div class="zsu-empty-icon-wrap">
                <span class="zsu-empty-emoji">🎬</span>
                <div class="zsu-empty-rings">
                    <div class="zsu-empty-ring"></div>
                    <div class="zsu-empty-ring"></div>
                    <div class="zsu-empty-ring"></div>
                </div>
            </div>
            <h3 class="zsu-empty-title">No Shorts Found</h3>
            <p class="zsu-empty-desc">Try a different category or filter.</p>
            <button class="zsu-btn-primary-pill" id="zsuEmptyReset">Show All Shorts</button>
        </div>
    </section>

    <!-- ── CATEGORY RAILS (loaded by JS) ───────────────────────── -->
    <?php if ( zsu_opt('zsu_show_categories', 1) && ! empty($cats) ) :
        foreach ( array_slice($cats, 0, 5) as $cat ) :
            $emoji = self::cat_emoji( $cat->slug );
    ?>
    <section class="zsu-cat-rail-section" id="zsuCatRail-<?php echo esc_attr($cat->slug); ?>"
             data-cat="<?php echo esc_attr($cat->slug); ?>"
             aria-label="<?php echo esc_attr($cat->name); ?> Shorts">
        <div class="zsu-section-header-row">
            <h2 class="zsu-section-heading">
                <span class="zsu-heading-icon"><?php echo $emoji; ?></span>
                <?php echo esc_html($cat->name); ?>
                <span class="zsu-heading-count"><?php echo $cat->count; ?></span>
            </h2>
            <a href="#" class="zsu-see-all" data-cat="<?php echo esc_attr($cat->slug); ?>">See All →</a>
        </div>
        <div class="zsu-rail-scroll" id="zsuRailScroll-<?php echo esc_attr($cat->slug); ?>" role="list">
            <?php echo self::render_skeleton_cards(6, 'rail'); ?>
        </div>
    </section>
    <?php endforeach; endif; ?>

</main><!-- .zsu-main -->

<?php self::render_overlays(); ?>

<?php wp_footer(); ?>
</body>
</html>
        <?php
    }

    // ─── Shortcode ───────────────────────────────────────────────
    public static function shortcode( array $atts ): string {
        $atts = shortcode_atts( [
            'cat'    => '',
            'filter' => 'latest',
            'limit'  => 12,
            'cols'   => zsu_opt('zsu_grid_columns', 2),
        ], $atts, 'zsu_shorts' );

        // Safety: never render the shortcode on homepage or admin
        if ( self::is_shorts_url() || is_admin() ) return '';

        // Enqueue assets if not already done (page builders can bypass wp_enqueue_scripts)
        // We use wp_enqueue here (not direct echo) so WP deduplicates correctly
        if ( ! wp_style_is( 'zsu-frontend', 'done' ) && ! wp_style_is( 'zsu-frontend', 'enqueued' ) ) {
            wp_enqueue_style( 'zsu-frontend', ZSU_ASSETS_URL . 'css/zsu-frontend.css', [], ZSU_VER );
            wp_enqueue_script( 'zsu-frontend', ZSU_ASSETS_URL . 'js/zsu-frontend.js', [], ZSU_VER, false );
            wp_add_inline_style( 'zsu-frontend', self::build_dynamic_css() );
            wp_localize_script( 'zsu-frontend', 'ZSU', self::build_js_config() );
        }

        ob_start();
        self::render_embedded( $atts );
        return ob_get_clean();
    }


    // ─── Shared overlays: lightbox, share sheet, product modal, toast, confetti ──
    // Called by both render_full_page() and render_embedded() (shortcode)
    private static function render_overlays(): void {
        // Only output overlays ONCE per page load — prevent duplication
        static $rendered = false;
        if ( $rendered ) { return; }
        $rendered = true;
        ?>
<!-- ════════════════════════════════════════════════════════════ -->
<!--  LIGHTBOX / FULLSCREEN PLAYER                               -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php ZSU_Frontend::render_lightbox_html(); ?>

<!-- ════════════════════════════════════════════════════════════ -->
<!--  SPIN-TO-WIN WHEEL                                           -->
<!-- ════════════════════════════════════════════════════════════ -->
<?php if ( zsu_opt('zsu_spin_wheel_enabled', 1) ) : ?>
<div class="zsu-wheel-overlay" id="zsuWheelOverlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Spin to Win">
    <div class="zsu-wheel-modal">
        <button class="zsu-wheel-close" id="zsuWheelClose" aria-label="Close">✕</button>
        <div class="zsu-wheel-header">
            <h2 class="zsu-wheel-title">
                <span class="zsu-wheel-title-icon">🎡</span>
                <?php echo esc_html( zsu_opt('zsu_spin_wheel_title','Spin & Win!') ); ?>
            </h2>
            <p class="zsu-wheel-subtitle"><?php echo esc_html( zsu_opt('zsu_spin_wheel_subtitle','Spin once a day for exclusive prizes') ); ?></p>
        </div>
        <div class="zsu-wheel-arena">
            <div class="zsu-wheel-pointer" aria-hidden="true">▼</div>
            <div class="zsu-wheel-glow-ring" aria-hidden="true"></div>
            <canvas id="zsuWheelCanvas" width="340" height="340" aria-hidden="true"></canvas>
            <div class="zsu-wheel-center-hub" aria-hidden="true">
                <span class="zsu-wheel-hub-icon">⚡</span>
            </div>
        </div>
        <button class="zsu-wheel-spin-btn" id="zsuWheelSpinBtn">
            <span class="zsu-wheel-spin-label">SPIN!</span>
            <span class="zsu-wheel-spin-sub">1 spin remaining today</span>
        </button>
        <div class="zsu-wheel-result" id="zsuWheelResult" style="display:none;">
            <div class="zsu-wheel-result-emoji" id="zsuWheelResultEmoji">🎉</div>
            <div class="zsu-wheel-result-text" id="zsuWheelResultText"></div>
            <div class="zsu-wheel-result-code" id="zsuWheelResultCode" style="display:none;">
                <span class="zsu-coupon-label">Your coupon code:</span>
                <div class="zsu-coupon-box" id="zsuCouponBox">
                    <span id="zsuCouponCode"></span>
                    <button class="zsu-coupon-copy" id="zsuCouponCopy" aria-label="Copy code">📋</button>
                </div>
            </div>
            <button class="zsu-wheel-play-again" id="zsuWheelPlayAgain" style="display:none;">Try Again Tomorrow</button>
        </div>
        <div class="zsu-wheel-particles" id="zsuWheelParticles" aria-hidden="true"></div>
    </div>
</div>

<!-- Floating trigger bubble -->
<button class="zsu-wheel-fab" id="zsuWheelFab" aria-label="Spin to Win" title="Spin to Win!" style="background:transparent;border:none;padding:0;overflow:hidden;border-radius:50px;width:60px;height:60px;cursor:pointer;">
    <img src="https://deepakclothings.in/wp-content/uploads/2026/05/01b935135728003.Y3JvcCw4MDgsNjMyLDAsMA.jpg" alt="Spin to Win" style="width:100%;height:100%;object-fit:cover;border-radius:50px;display:block;">
</button>
<?php endif; ?>
        <?php
    }

    private static function render_embedded( array $atts ): void {
        $uid = uniqid('zsu_sc_');

        // Assets should already be enqueued via enqueue_assets() hooked to wp_enqueue_scripts.
        // Only inject inline as fallback if somehow not enqueued (e.g. extreme page-builder edge cases).
        if ( ! wp_style_is( 'zsu-frontend', 'done' ) && ! wp_style_is( 'zsu-frontend', 'enqueued' ) ) {
            $css_url    = esc_url( ZSU_ASSETS_URL . 'css/zsu-frontend.css?v=' . ZSU_VER );
            $js_url     = esc_url( ZSU_ASSETS_URL . 'js/zsu-frontend.js?v='  . ZSU_VER );
            $zsu_config = wp_json_encode( self::build_js_config() );
            echo '<link rel="stylesheet" href="' . $css_url . '">' . "\n";
            echo '<style>' . self::build_dynamic_css() . '</style>' . "\n";
            echo '<script>if(typeof ZSU===\'undefined\'){var ZSU=' . $zsu_config . ';}</script>' . "\n";
            echo '<script src="' . $js_url . '"></script>' . "\n";
        }
        ?>
        <div class="zsu-embedded-wrap" id="<?php echo esc_attr($uid); ?>"
             data-cat="<?php echo esc_attr($atts['cat']); ?>"
             data-filter="<?php echo esc_attr($atts['filter']); ?>"
             data-limit="<?php echo (int)$atts['limit']; ?>"
             data-cols="<?php echo (int)$atts['cols']; ?>">

            <!-- Filter strip -->
            <div class="zsu-filter-strip zsu-filter-strip-embedded">
                <div class="zsu-cat-pills" id="<?php echo esc_attr($uid); ?>-pills">
                    <button class="zsu-cat-pill active" data-cat="">🔥 All</button>
                    <?php
                    $cats = get_terms(['taxonomy'=>ZSU_TAX,'hide_empty'=>true]);
                    if (!is_wp_error($cats)) {
                        foreach ($cats as $cat) {
                            echo '<button class="zsu-cat-pill" data-cat="' . esc_attr($cat->slug) . '">'
                               . self::cat_emoji($cat->slug) . ' ' . esc_html($cat->name)
                               . '</button>';
                        }
                    }
                    ?>
                    <div class="zsu-sort-select-wrap" style="margin-left:auto;">
                        <select class="zsu-sort-select" id="zsuSortSelect" aria-label="Sort by">
                            <option value="latest">Latest</option>
                            <option value="trending">🔥 Trending</option>
                            <option value="popular">👁 Popular</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grid -->
            <div class="zsu-grid" id="zsuGrid"
                 style="--cols:<?php echo (int)$atts['cols']; ?>"
                 role="list" aria-label="Shorts grid">
                <?php echo self::render_skeleton_cards(6, 'grid'); ?>
            </div>

            <!-- Required supporting elements -->
            <div class="zsu-load-more-sentinel" id="zsuSentinel" aria-hidden="true">
                <div class="zsu-load-spinner" id="zsuLoadSpinner" style="display:none;"><div class="zsu-spinner-ring"></div></div>
                <button class="zsu-load-more-btn" id="zsuLoadMoreBtn" style="display:none;">Load More</button>
            </div>
            <div class="zsu-grid-empty" id="zsuGridEmpty" style="display:none;" role="alert">
                <h3 class="zsu-empty-title">No Shorts Found</h3>
                <button class="zsu-btn-primary-pill" id="zsuEmptyReset">Show All Shorts</button>
            </div>

            <!-- Overlays: lightbox, share sheet, product modal, toast -->
            <?php self::render_overlays(); ?>

        </div>
        <?php
    }

    public static function shortcode_single( array $atts ): string {
        $atts = shortcode_atts(['id' => 0], $atts, 'zsu_short');
        if (!$atts['id']) return '';
        $post = get_post(absint($atts['id']));
        if (!$post || $post->post_type !== ZSU_CPT) return '';
        $meta  = zsu_get_short_meta($post->ID);
        $thumb = $meta['thumb_url'] ?: zsu_placeholder_thumb($post->ID, $post->post_title);
        ob_start();
        ?>
        <div class="zsu-single-short" data-id="<?php echo $post->ID; ?>" style="cursor:pointer;max-width:320px;">
            <div style="aspect-ratio:9/16;position:relative;overflow:hidden;border-radius:var(--zsu-radius,16px);">
                <img src="<?php echo esc_url($thumb); ?>" style="width:100%;height:100%;object-fit:cover;" loading="lazy" alt="<?php echo esc_attr($post->post_title); ?>">
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.2);">
                    <div style="width:52px;height:52px;border-radius:50%;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ─── Skeleton loader cards ────────────────────────────────────
    private static function render_skeleton_cards( int $count, string $mode = 'grid' ): string {
        $html = '';
        for ( $i = 0; $i < $count; $i++ ) {
            if ( $mode === 'rail' ) {
                $html .= '<div class="zsu-short-card zsu-skeleton zsu-skeleton-rail" aria-hidden="true">'
                       . '<div class="zsu-skeleton-thumb"></div>'
                       . '</div>';
            } else {
                $html .= '<div class="zsu-short-card zsu-skeleton" aria-hidden="true">'
                       . '<div class="zsu-skeleton-thumb"></div>'
                       . '<div class="zsu-skeleton-line zsu-skeleton-line-lg"></div>'
                       . '<div class="zsu-skeleton-line zsu-skeleton-line-sm"></div>'
                       . '</div>';
            }
        }
        return $html;
    }

    // ─── Category emoji map ───────────────────────────────────────
    public static function cat_emoji( string $slug ): string {
        $map = [
            'fashion'      => '👗', 'style'        => '👗',
            'home'         => '🏠', 'furniture'    => '🛋️',
            'food'         => '🍕', 'cooking'      => '👨‍🍳',
            'beauty'       => '💄', 'skincare'     => '✨',
            'tech'         => '💻', 'gadgets'      => '📱',
            'fitness'      => '💪', 'sport'        => '⚽',
            'travel'       => '✈️', 'outdoor'      => '🌿',
            'art'          => '🎨', 'crafts'       => '✂️',
            'music'        => '🎵', 'entertainment'=> '🎬',
            'pets'         => '🐾', 'kids'         => '🧸',
            'jewellery'    => '💍', 'jewelry'      => '💍',
            'marble'       => '🪨', 'stone'        => '🪨',
            'tools'        => '🔧', 'hardware'     => '⚙️',
            'health'       => '💊', 'wellness'     => '🧘',
        ];
        foreach ( $map as $keyword => $emoji ) {
            if ( str_contains( $slug, $keyword ) ) {
                return $emoji;
            }
        }
        return '📦';
    }

    /**
     * Output the full lightbox + share sheet + product modal HTML.
     * Called from both the main shorts page and vendor store tab.
     */
    public static function render_lightbox_html(): void {
        if ( defined( 'ZSU_LIGHTBOX_RENDERED' ) ) return;
        define( 'ZSU_LIGHTBOX_RENDERED', true );
        include ZSU_DIR . 'includes/partials/lightbox.php';
    }
}