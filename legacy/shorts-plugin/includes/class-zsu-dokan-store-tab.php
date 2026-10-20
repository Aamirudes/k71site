<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Dokan_Store_Tab
 * Injects a "Shorts" tab on Dokan vendor store pages.
 * Self-contained: renders grid + full lightbox HTML + boots ZSU_App.
 */
class ZSU_Dokan_Store_Tab {

    public static function init(): void {
        add_filter( 'dokan_store_tabs',                [ self::class, 'add_tab'      ] );
        add_action( 'dokan_store_profile_frame_after', [ self::class, 'render_panel' ] );
        add_action( 'wp_enqueue_scripts',              [ self::class, 'enqueue'      ] );
    }

    public static function add_tab( array $tabs ): array {
        $tabs['zsu_shorts'] = [
            'title' => __( 'Shorts', 'mm-shorts' ),
            'url'   => '#zsu-shorts-tab',
        ];
        return $tabs;
    }

    private static function vendor_id(): int {
        if ( ! function_exists( 'dokan' ) ) return 0;

        // Dokan store URLs use /store/vendor-slug/ — WordPress sets 'author_name' (slug),
        // NOT 'author' (numeric ID). Always resolve via author_name first.
        $author_name = get_query_var( 'author_name' );
        if ( $author_name ) {
            $user = get_user_by( 'slug', $author_name );
            if ( $user ) return (int) $user->ID;
        }

        // Fallback: some Dokan setups do set numeric 'author' query var
        $author = (int) get_query_var( 'author' );
        if ( $author ) return $author;

        return 0;
    }

    public static function enqueue(): void {
        if ( ! function_exists( 'dokan_is_store_page' ) || ! dokan_is_store_page() ) return;
        $vendor_id = self::vendor_id();
        if ( ! $vendor_id ) return;

        wp_enqueue_style( 'zsu-frontend', ZSU_ASSETS_URL . 'css/zsu-frontend.css', [], ZSU_VER );
        wp_enqueue_script( 'zsu-frontend', ZSU_ASSETS_URL . 'js/zsu-frontend.js', [], ZSU_VER, true );

        wp_localize_script( 'zsu-frontend', 'ZSU', [
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( ZSU_NONCE ),
            'shorts_url'    => home_url( '/' . zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) ),
            'home_url'      => home_url(),
            'open_id'       => 0,
            'per_page'      => 12,
            'loop'          => (bool) zsu_opt( 'zsu_loop_video', 1 ),
            'show_follow'   => (bool) zsu_opt( 'zsu_show_follow_btn', 1 ),
            'show_like'     => (bool) zsu_opt( 'zsu_show_like_btn', 1 ),
            'show_share'    => (bool) zsu_opt( 'zsu_show_share_btn', 1 ),
            'show_comments' => (bool) zsu_opt( 'zsu_show_comment_btn', 0 ),
            'show_products' => (bool) zsu_opt( 'zsu_enable_shop_tray', 1 ),
            'is_logged_in'  => is_user_logged_in(),
        ] );

        wp_add_inline_script( 'zsu-frontend', self::boot_script( $vendor_id ), 'after' );
    }

    public static function render_panel(): void {
        if ( ! function_exists( 'dokan_is_store_page' ) || ! dokan_is_store_page() ) return;
        $vendor_id = self::vendor_id();
        if ( ! $vendor_id ) return;
        ?>

<div id="zsu-store-shorts-tab"
     class="dokan-store-tab-content zsu-vendor-store-shorts"
     style="display:none;padding:16px 0;">

    <!-- 2-col grid -->
    <div id="zsuVendorGrid"
         class="zsu-grid"
         style="--cols:2;padding:0;"
         data-vendor-id="<?php echo esc_attr( $vendor_id ); ?>">
    </div>

    <div id="zsuVendorSpin"
         style="display:flex;align-items:center;justify-content:center;gap:10px;padding:32px 16px;color:#888;font-size:13px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             style="animation:zsu-spin .8s linear infinite;flex-shrink:0;">
            <circle cx="12" cy="12" r="10" stroke-dasharray="40" stroke-dashoffset="30"/>
        </svg>
        Loading shorts…
    </div>

    <div id="zsuVendorEmpty" style="display:none;text-align:center;padding:40px 16px;">
        <div style="font-size:36px;margin-bottom:10px;">🎬</div>
        <p style="color:#888;font-size:13px;margin:0;">No shorts yet from this vendor.</p>
    </div>

    <div id="zsuVendorMoreWrap" style="display:none;text-align:center;padding:16px;">
        <button id="zsuVendorMore"
                style="padding:10px 28px;background:#111827;color:#fff;border:none;border-radius:100px;font-size:13px;font-weight:700;cursor:pointer;">
            Load more
        </button>
    </div>
</div>

<?php ZSU_Frontend::render_lightbox_html(); ?>

        <?php
    }

    private static function boot_script( int $vendor_id ): string {
        $shorts_url = esc_js( home_url( '/' . zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) ) );
        return <<<JSEOF
(function() {
  'use strict';
  var VID = {$vendor_id};
  var page = 1, PER = 12, loading = false, done = false, fetched = false;

  function showSkeletons() {
    var g = document.getElementById('zsuVendorGrid');
    if (!g) return;
    g.innerHTML = '';
    for (var i = 0; i < 6; i++) {
      var d = document.createElement('div');
      d.className = 'zsu-vskel';
      g.appendChild(d);
    }
    document.getElementById('zsuVendorSpin').style.display = 'none';
  }

  console.log('[ZSU Store Tab] vendor_id=' + VID + ', ajax=' + ((window.ZSU && ZSU.ajax_url) || 'missing'));
  function fetchPage(p, reset) {
    if (loading || done) return;
    loading = true;
    if (reset) showSkeletons();

    var ajaxUrl = (window.ZSU && ZSU.ajax_url) || '/wp-admin/admin-ajax.php';
    var nonce   = (window.ZSU && ZSU.nonce)    || '';

    var fd = new FormData();
    fd.append('action',    'zsu_get_shorts');
    fd.append('nonce',     nonce);
    fd.append('vendor_id', VID);
    fd.append('page',      p);
    fd.append('per_page',  PER);
    fd.append('filter',    'latest');

    fetch(ajaxUrl, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        loading = false;
        var g = document.getElementById('zsuVendorGrid');
        if (!g) return;

        if (!res.success || !(res.shorts || []).length) {
          if (reset) {
            g.innerHTML = '';
            document.getElementById('zsuVendorEmpty').style.display = 'block';
          }
          done = true;
          document.getElementById('zsuVendorMoreWrap').style.display = 'none';
          return;
        }

        page = p;
        if (reset) g.innerHTML = '';
        res.shorts.forEach(function(s) { g.appendChild(buildCard(s)); });

        var hasMore = p < (res.pages || 1) && res.shorts.length === PER;
        document.getElementById('zsuVendorMoreWrap').style.display = hasMore ? 'block' : 'none';
        if (!hasMore) done = true;
      })
      .catch(function() { loading = false; });
  }

  function buildCard(s) {
    var card = document.createElement('div');
    card.className = 'zsu-short-card';
    card.dataset.id = s.id;
    card.innerHTML =
      (s.thumb_url ? '<img src="' + x(s.thumb_url) + '" alt="' + x(s.title) + '" loading="lazy">' : '') +
      '<div class="zsu-c-scrim"></div>' +
      '<div class="zsu-c-meta">' +
        '<div class="zsu-c-title">' + x(s.title) + '</div>' +
        (s.views ? '' : '') +
      '</div>';
    return card;
  }

  function x(s) {
    return String(s).replace(/[&<>"']/g, function(c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }

  function openShort(id) {
    // Prefer ZSU_App lightbox (exposed after frontend.js init)
    if (window.ZSU_App && typeof window.ZSU_App.fetchAndOpenLightbox === 'function') {
      window.ZSU_App.fetchAndOpenLightbox(id);
    } else {
      window.location.href = '{$shorts_url}/v/' + id + '/';
    }
  }

  function init() {
    var grid = document.getElementById('zsuVendorGrid');
    if (grid) {
      grid.addEventListener('click', function(e) {
        var card = e.target.closest('.zsu-short-card[data-id]');
        if (!card) return;
        openShort(parseInt(card.dataset.id, 10));
      });
    }

    var more = document.getElementById('zsuVendorMore');
    if (more) more.addEventListener('click', function() { fetchPage(page + 1, false); });

    // Hook Dokan tab clicks
    document.addEventListener('click', function(e) {
      var link = e.target.closest('a[href="#zsu-shorts-tab"]');
      if (!link) return;
      e.preventDefault();

      // Hide all dokan tab panels (Dokan uses .dokan-store-tab-content)
      document.querySelectorAll('.dokan-store-tab-content').forEach(function(p) {
        p.style.display = 'none';
      });
      var panel = document.getElementById('zsu-store-shorts-tab');
      if (panel) panel.style.display = 'block';

      document.querySelectorAll('.dokan-store-tabs li').forEach(function(li) {
        li.classList.remove('active');
      });
      var li = link.closest('li');
      if (li) li.classList.add('active');

      if (!fetched) { fetched = true; fetchPage(1, true); }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
JSEOF;
    }
}
