<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_Vendor_Studio — Dokan vendor dashboard tab with:
 *  - Upload wizard (drag-drop, canvas thumbnail capture)
 *  - Video editor (crop/rotate/zoom/transform)
 *  - My Shorts manager
 *  - Vendor analytics dashboard
 *  - Boost purchase panel
 */
class ZSU_Vendor_Studio {

    public static function init(): void {
        if ( ! zsu_opt( 'zsu_dokan_studio_tab', 1 ) ) return;

        add_filter( 'dokan_get_dashboard_nav',       [ self::class, 'add_nav_tab'       ] );
        add_action( 'dokan_load_custom_template',    [ self::class, 'load_template'     ] );
        add_filter( 'dokan_query_var_filter',        [ self::class, 'add_query_var'     ] );
        add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
        // Inject icon replacement on ALL seller dashboard pages
        add_action( 'wp_footer', [ self::class, 'inject_nav_icon_fix' ] );

        // Dokan rewrite slug
        add_action( 'init', static function () {
            add_rewrite_endpoint( 'zsu-studio', EP_PAGES );
        } );
    }

    /**
     * Replace Dokan's default lucide-settings SVG with the YouTube Shorts icon
     * for the zsu-studio nav item. Dokan ignores the 'icon' field for custom tabs
     * and always renders lucide-settings, so we override via JS on DOM ready.
     */
    public static function inject_nav_icon_fix(): void {
        if ( ! is_user_logged_in() ) return;
        if ( ! function_exists( 'dokan_is_seller_dashboard' ) || ! dokan_is_seller_dashboard() ) return;
        ?>
        <script>
        (function() {
            var SHORTS_SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 98.94 122.88" width="20" height="20" style="display:inline-block;vertical-align:middle;flex-shrink:0;" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M63.49,2.71c11.59-6.04,25.94-1.64,32.04,9.83c6.1,11.47,1.65,25.66-9.94,31.7l-9.53,5.01c8.21,0.3,16.04,4.81,20.14,12.52c6.1,11.47,1.66,25.66-9.94,31.7l-50.82,26.7c-11.59,6.04-25.94,1.64-32.04-9.83c-6.1-11.47-1.65-25.66,9.94-31.7l9.53-5.01c-8.21-0.3-16.04-4.81-20.14-12.52c-6.1-11.47-1.65-25.66,9.94-31.7L63.49,2.71L63.49,2.71z M36.06,42.53l30.76,18.99l-30.76,18.9V42.53L36.06,42.53z"/></svg>';

           
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', replaceIcon);
            } else {
                replaceIcon();
            }
            // Also run after a short delay in case Dokan renders via JS
            setTimeout(replaceIcon, 500);
            setTimeout(replaceIcon, 1500);
        })();
        </script>
        <?php
    }

   // 1. Tab add karo (Normal way)
public static function add_nav_tab( $nav ) {
    $nav['zsu-studio'] = array(
        'title' => 'Shorts Studio',
        'url'   => home_url( '/dashboard/zsu-studio/' ),
        'pos'   => 55,
        'icon'  => '' // Icon replaced via inject_nav_icon_fix() JS in wp_footer
    );
    return $nav;
}



    public static function add_query_var( array $vars ): array {
        $vars[] = 'zsu-studio';
        return $vars;
    }

    public static function load_template( array $query_vars ): void {
        if ( isset( $query_vars['zsu-studio'] ) ) {
            self::render_studio_page();
        }
    }

    // ─── Enqueue studio assets ────────────────────────────────────
    public static function enqueue_assets(): void {
        if ( ! is_user_logged_in() ) return;
        if ( ! function_exists( 'dokan_is_seller_dashboard' ) || ! dokan_is_seller_dashboard() ) return;
        // Endpoint 'zsu-studio' is set as '' (empty string) when active — check key existence
        global $wp_query;
        if ( ! isset( $wp_query->query_vars['zsu-studio'] ) ) return;

        wp_enqueue_style(
            'zsu-studio',
            ZSU_ASSETS_URL . 'css/zsu-studio.css',
            [],
            ZSU_VER
        );

        wp_enqueue_script(
            'zsu-studio',
            ZSU_ASSETS_URL . 'js/zsu-studio.js',
            [],
            ZSU_VER,
            false  // load in <head> to ensure DOMContentLoaded fires after
        );

        // Get vendor stats for initial render
        $vendor_id  = zsu_current_vendor_id();
        $balance    = $vendor_id ? ZSU_Coins::balance( $vendor_id ) : 0;
        $upload_cnt = (int) get_user_meta( $vendor_id, '_zsu_upload_count', true );
        $max_cnt    = (int) zsu_opt( 'zsu_vendor_max_upload', 100 );

        $cats = get_terms( [ 'taxonomy' => ZSU_TAX, 'hide_empty' => false ] );
        $cat_options = [];
        if ( ! is_wp_error( $cats ) ) {
            foreach ( $cats as $c ) {
                $cat_options[] = [ 'id' => $c->term_id, 'name' => $c->name ];
            }
        }

        wp_localize_script( 'zsu-studio', 'ZSU_STUDIO', [
            'ajax_url'     => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( ZSU_NONCE ),
            'vendor_id'    => $vendor_id,
            'vendor_name'  => wp_get_current_user()->display_name ?? '',
            'coins'        => $balance,
            'upload_count' => $upload_cnt,
            'max_uploads'  => $max_cnt,
            'max_mb'       => (int) zsu_opt( 'zsu_max_file_size_mb', 150 ),
            'categories'   => $cat_options,
            'auto_approve' => (int) zsu_opt( 'zsu_auto_approve', 0 ),
            'boost_enabled'=> (int) zsu_opt( 'zsu_boost_enabled', 1 ),
            'boost_prices' => [
                'mega'     => (int) zsu_opt( 'zsu_boost_price_mega',  2499  ),
            ],
            'coin_rates'   => [
                'per_1'    => (float) zsu_opt( 'zsu_coin_rate_1',    1.00 ),
                'per_100'  => (float) zsu_opt( 'zsu_coin_rate_100',  90.00 ),
                'per_500'  => (float) zsu_opt( 'zsu_coin_rate_500',  400.00 ),
                'per_1000' => (float) zsu_opt( 'zsu_coin_rate_1000', 750.00 ),
            ],
            'whatsapp_number' => zsu_opt( 'zsu_whatsapp_number', '919999999999' ),
            'ratios'       => [
                '9:16' => '9:16 — Vertical (Default)',
                '1:1'  => '1:1 — Square',
                '4:5'  => '4:5 — Portrait',
                '16:9' => '16:9 — Landscape',
            ],
            'shorts_url'   => home_url( '/' . zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) . '/' ),
        ] );
    }

    // ═══════════════════════════════════════════════════════════════
    //  STUDIO PAGE HTML
    // ═══════════════════════════════════════════════════════════════
    public static function render_studio_page(): void {
        $vendor_id  = zsu_current_vendor_id();
        if ( ! $vendor_id ) {
            echo '<div style="padding:40px;text-align:center;color:#64748b;">Please log in as a vendor to access the Studio.</div>';
            return;
        }

        $balance    = ZSU_Coins::balance( $vendor_id );
        $upload_cnt = (int) get_user_meta( $vendor_id, '_zsu_upload_count', true );
        $max_cnt    = (int) zsu_opt( 'zsu_vendor_max_upload', 100 );
        $used_pct   = $max_cnt > 0 ? round( ( $upload_cnt / $max_cnt ) * 100 ) : 0;

        // Assets loaded via wp_enqueue_scripts hook.
        // Fallback: if enqueue didn't fire (e.g. heavy caching), output inline.
        if ( ! wp_script_is( 'zsu-studio', 'done' ) && ! wp_script_is( 'zsu-studio', 'enqueued' ) ) {
            echo '<link rel="stylesheet" href="' . esc_url( ZSU_ASSETS_URL . 'css/zsu-studio.css?v=' . ZSU_VER ) . '">' . "\n";
        }
        echo '<script>var ZSU_STUDIO = ' . wp_json_encode([
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( ZSU_NONCE ),
            'vendor_id'     => $vendor_id,
            'vendor_name'   => wp_get_current_user()->display_name ?? '',
            'coins'         => $balance,
            'upload_count'  => $upload_cnt,
            'max_uploads'   => $max_cnt,
            'max_mb'        => (int) zsu_opt( 'zsu_max_file_size_mb', 150 ),
            'auto_approve'  => (int) zsu_opt( 'zsu_auto_approve', 0 ),
            'boost_enabled' => (int) zsu_opt( 'zsu_boost_enabled', 1 ),
            'boost_prices'  => [
                'mega'     => (int) zsu_opt( 'zsu_boost_price_mega', 2499 ),
            ],
            'coin_rates'    => [
                'per_1'    => (float) zsu_opt( 'zsu_coin_rate_1',    1.00 ),
                'per_100'  => (float) zsu_opt( 'zsu_coin_rate_100',  90.00 ),
                'per_500'  => (float) zsu_opt( 'zsu_coin_rate_500',  400.00 ),
                'per_1000' => (float) zsu_opt( 'zsu_coin_rate_1000', 750.00 ),
            ],
            'whatsapp_number' => zsu_opt( 'zsu_whatsapp_number', '919999999999' ),
            'categories'    => (function() {
                $cats = get_terms( [ 'taxonomy' => ZSU_TAX, 'hide_empty' => false ] );
                $out  = [];
                if ( ! is_wp_error( $cats ) ) {
                    foreach ( $cats as $c ) { $out[] = [ 'id' => $c->term_id, 'name' => $c->name ]; }
                }
                return $out;
            })(),
            'ratios'        => [
                '9:16' => '9:16 — Vertical (Default)',
                '1:1'  => '1:1 — Square',
                '4:5'  => '4:5 — Portrait',
                '16:9' => '16:9 — Landscape',
            ],
            'shorts_url'    => home_url( '/' . zsu_opt( 'zsu_shorts_page_slug', 'shorts' ) . '/' ),
        ]) . ';</script>' . "\n";
        // Only echo script src if wp_enqueue didn't already load it
        if ( ! wp_script_is( 'zsu-studio', 'done' ) && ! wp_script_is( 'zsu-studio', 'enqueued' ) ) {
            echo '<script src="' . esc_url( ZSU_ASSETS_URL . 'js/zsu-studio.js?v=' . ZSU_VER ) . '"></script>' . "\n";
        }
        ?>

<div class="zsu-studio-wrap" id="zsuStudio">

    <!-- ── Studio Header ──────────────────────────────────────── -->
    <div class="zsu-studio-header">
        <div class="zsu-studio-header-left">
            <div class="zsu-studio-logo">
                <span class="zsu-studio-logo-icon">⚡</span>
                <div>
                    <div class="zsu-studio-logo-title">Shorts Studio</div>
                    <div class="zsu-studio-logo-sub">Vendor Dashboard</div>
                </div>
            </div>
        </div>
        <div class="zsu-studio-header-right">
            <!-- Coin balance -->
            <div class="zsu-studio-coins" id="zsuStudioCoins" title="Your coin balance">
                <span class="zsu-studio-coin-icon">🪙</span>
                <span class="zsu-studio-coin-count" id="zsuStudioCoinCount"><?php echo number_format($balance); ?></span>
                <span class="zsu-studio-coin-label">Coins</span>
            </div>
            <!-- Upload quota -->
            <div class="zsu-studio-quota">
                <div class="zsu-studio-quota-text">
                    <span><?php echo $upload_cnt; ?> / <?php echo $max_cnt; ?> uploads</span>
                </div>
                <div class="zsu-studio-quota-bar">
                    <div class="zsu-studio-quota-fill" style="width:<?php echo min(100,$used_pct); ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tab Nav ────────────────────────────────────────────── -->
    <nav class="zsu-studio-tabs" role="tablist">
        <button class="zsu-studio-tab active" data-tab="upload"    role="tab" aria-selected="true">
            <span class="zsu-tab-icon">📤</span> Upload
        </button>
        <button class="zsu-studio-tab" data-tab="my-shorts"  role="tab" aria-selected="false">
            <span class="zsu-tab-icon">🎬</span> My Shorts
        </button>
        <button class="zsu-studio-tab" data-tab="analytics"  role="tab" aria-selected="false">
            <span class="zsu-tab-icon">📈</span> Analytics
        </button>
        <?php if ( zsu_opt('zsu_boost_enabled',1) ) : ?>
        <button class="zsu-studio-tab" data-tab="boost"      role="tab" aria-selected="false">
            <span class="zsu-tab-icon">🚀</span> Boost
        </button>
        <?php endif; ?>
        <button class="zsu-studio-tab" data-tab="coins"      role="tab" aria-selected="false">
            <span class="zsu-tab-icon">🪙</span> Coins
        </button>
    </nav>

    <!-- ═══════════════════════════════════════════════════════
         TAB: UPLOAD WIZARD
         ═══════════════════════════════════════════════════════ -->
    <div class="zsu-studio-panel active" id="zsuTab-upload" role="tabpanel">

        <!-- Wizard steps indicator -->
        <div class="zsu-wizard-steps" id="zsuWizardSteps">
            <div class="zsu-wizard-step active" data-step="1">
                <div class="zsu-wizard-step-num">1</div>
                <div class="zsu-wizard-step-label">Upload Video</div>
            </div>
            <div class="zsu-wizard-step-connector"></div>
            <div class="zsu-wizard-step" data-step="2">
                <div class="zsu-wizard-step-num">2</div>
                <div class="zsu-wizard-step-label">Edit & Preview</div>
            </div>
            <div class="zsu-wizard-step-connector"></div>
            <div class="zsu-wizard-step" data-step="3">
                <div class="zsu-wizard-step-num">3</div>
                <div class="zsu-wizard-step-label">Details</div>
            </div>
            <div class="zsu-wizard-step-connector"></div>
            <div class="zsu-wizard-step" data-step="4">
                <div class="zsu-wizard-step-num">4</div>
                <div class="zsu-wizard-step-label">Publish</div>
            </div>
        </div>

        <!-- ── STEP 1: DROP ZONE ─────────────────────────────── -->
        <div class="zsu-wizard-pane active" id="zsuStep1">
            <div class="zsu-dropzone" id="zsuDropzone"
                 role="button" tabindex="0" aria-label="Upload video">
                <div class="zsu-dropzone-inner" id="zsuDropzoneInner">
                    <div class="zsu-dropzone-icon">📹</div>
                    <div class="zsu-dropzone-title">Drop your video here</div>
                    <div class="zsu-dropzone-sub">MP4, MOV, MKV, AVI, WebM &amp; more · Up to <?php echo (int)zsu_opt('zsu_max_file_size_mb',150); ?>MB</div>
                    <button class="zsu-dz-btn" id="zsuDzBrowseBtn" type="button">
                        Browse Files
                    </button>
                    <input type="file" id="zsuFileInput" accept="video/*"
                           style="display:none;" aria-hidden="true">
                </div>

                <!-- Upload progress (hidden initially) -->
                <div class="zsu-dz-progress" id="zsuDzProgress" style="display:none;">
                    <div class="zsu-dz-progress-bar" id="zsuDzProgressBar"></div>
                    <div class="zsu-dz-progress-text" id="zsuDzProgressText">Uploading…</div>
                </div>
            </div>
            <!-- Validation errors -->
            <div class="zsu-upload-error" id="zsuUploadError" style="display:none;" role="alert"></div>
        </div>

        <!-- ── STEP 2: VIDEO EDITOR ──────────────────────────── -->
        <div class="zsu-wizard-pane" id="zsuStep2" style="display:none;">
            <div class="zsu-editor-layout">

                <!-- Preview panel -->
                <div class="zsu-editor-preview-panel">
                    <div class="zsu-editor-phone-frame">
                        <div class="zsu-editor-phone-screen" id="zsuEditorPhoneScreen">
                            <video class="zsu-editor-video" id="zsuEditorVideo"
                                   playsinline muted loop></video>
                            <canvas class="zsu-editor-overlay-canvas"
                                    id="zsuEditorCanvas" aria-hidden="true"></canvas>
                        </div>
                    </div>
                    <!-- Ratio selector -->
                    <div class="zsu-ratio-selector" id="zsuRatioSelector">
                        <button class="zsu-ratio-btn active" data-ratio="9:16">9:16</button>
                        <button class="zsu-ratio-btn" data-ratio="1:1">1:1</button>
                        <button class="zsu-ratio-btn" data-ratio="4:5">4:5</button>
                        <button class="zsu-ratio-btn" data-ratio="16:9">16:9</button>
                    </div>
                </div>

                <!-- Transform controls -->
                <div class="zsu-editor-controls">

                    <!-- Collapsible toggle -->
                    <button type="button" id="zsuTransformToggle"
                            style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:10px 14px;background:#1e2235;border:1px solid rgba(255,255,255,.1);border-radius:10px;cursor:pointer;color:#fff;font-size:13px;font-weight:700;font-family:inherit;margin-bottom:4px;"
                            aria-expanded="false">
                        <span>🎛 Transform</span>
                        <span id="zsuTransformChevron" style="font-size:10px;transition:transform .25s;color:#94a3b8;">▼</span>
                    </button>
                    <div id="zsuTransformPanel" style="overflow:hidden;max-height:0;transition:max-height .35s ease;padding-top:0;">

                    <!-- Scale -->
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">
                            🔍 Zoom
                            <span class="zsu-control-val" id="zsuZoomVal">100%</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuZoomSlider"
                               min="50" max="300" value="100" step="1">
                    </div>

                    <!-- Rotate -->
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">
                            🔄 Rotate
                            <span class="zsu-control-val" id="zsuRotateVal">0°</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuRotateSlider"
                               min="-180" max="180" value="0" step="1">
                    </div>

                    <!-- Brightness -->
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">
                            ☀️ Brightness
                            <span class="zsu-control-val" id="zsuBrightVal">100%</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuBrightSlider"
                               min="50" max="200" value="100" step="1">
                    </div>

                    <!-- Contrast -->
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">
                            🌑 Contrast
                            <span class="zsu-control-val" id="zsuContrastVal">100%</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuContrastSlider"
                               min="50" max="200" value="100" step="1">
                    </div>

                    <!-- Saturation -->
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">
                            🎨 Saturation
                            <span class="zsu-control-val" id="zsuSatVal">100%</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuSatSlider"
                               min="0" max="300" value="100" step="1">
                    </div>

                    <!-- Drag X/Y offset -->
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">↔️ Offset X
                            <span class="zsu-control-val" id="zsuOffsetXVal">0px</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuOffsetXSlider"
                               min="-200" max="200" value="0" step="1">
                    </div>
                    <div class="zsu-editor-control-row">
                        <label class="zsu-editor-control-label">↕️ Offset Y
                            <span class="zsu-control-val" id="zsuOffsetYVal">0px</span>
                        </label>
                        <input type="range" class="zsu-editor-slider" id="zsuOffsetYSlider"
                               min="-200" max="200" value="0" step="1">
                    </div>

                    <!-- Quick actions -->
                    <div class="zsu-editor-quick-actions">
                        <button class="zsu-editor-quick-btn" id="zsuFlipH" title="Flip horizontal">↔ Flip</button>
                        <button class="zsu-editor-quick-btn" id="zsuRotate90" title="Rotate 90°">↻ 90°</button>
                        <button class="zsu-editor-quick-btn" id="zsuResetTransform" title="Reset all">✕ Reset</button>
                    </div>

                    </div><!-- /.zsuTransformPanel -->

                    <!-- Capture thumbnail -->
                    <div class="zsu-editor-thumb-row">
                        <h3 class="zsu-editor-controls-title">🖼 Thumbnail</h3>
                        <div class="zsu-thumb-preview-wrap">
                            <img id="zsuThumbPreview" src="" alt="Thumbnail preview"
                                 style="display:none;width:68px;height:120px;border-radius:8px;aspect-ratio:9/16;object-fit:cover;">
                            <div class="zsu-thumb-placeholder" id="zsuThumbPlaceholder">
                                <span>No thumbnail yet</span>
                            </div>
                        </div>
                        <div class="zsu-thumb-actions">
                            <button class="zsu-editor-btn" id="zsuCaptureThumbnail">
                                📸 Capture Current Frame
                            </button>
                            <button class="zsu-editor-btn zsu-editor-btn-ghost" id="zsuUploadThumbnail">
                                📂 Upload Image
                            </button>
                            <input type="file" id="zsuThumbFileInput"
                                   accept="image/jpeg,image/png,image/webp"
                                   style="display:none;" aria-hidden="true">
                        </div>
                        <input type="hidden" id="zsuThumbDataUri">
                    </div>

                    <button class="zsu-wizard-next-btn" id="zsuStep2Next">
                        Continue to Details →
                    </button>
                </div>

            </div>
        </div>

        <!-- ── STEP 3: DETAILS FORM ──────────────────────────── -->
        <div class="zsu-wizard-pane" id="zsuStep3" style="display:none;">
            <div class="zsu-details-form">

                <div class="zsu-form-row">
                    <label class="zsu-form-label" for="zsuShortTitle">
                        📝 Short Title <span class="zsu-required">*</span>
                    </label>
                    <input type="text" id="zsuShortTitle" class="zsu-form-input"
                           placeholder="Give your short a catchy title…" maxlength="100">
                    <div class="zsu-form-counter"><span id="zsuTitleCount">0</span>/100</div>
                </div>


                <div class="zsu-form-row">
                    <label class="zsu-form-label" for="zsuShortCategory">
                        📂 Category
                    </label>
                    <select id="zsuShortCategory" class="zsu-form-select">
                        <option value="">— Select a category —</option>
                        <?php
                        $cats = get_terms(['taxonomy'=>ZSU_TAX,'hide_empty'=>false]);
                        if (!is_wp_error($cats)) {
                            foreach ($cats as $c) {
                                echo '<option value="' . esc_attr($c->term_id) . '">' . esc_html($c->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Product tagging -->
                <div class="zsu-form-row">
                    <label class="zsu-form-label">
                        🛍️ Tag Products
                        <span class="zsu-form-hint">Link products to your short for shoppable experience</span>
                    </label>
                    <div class="zsu-product-tagger" id="zsuProductTagger">
                        <div class="zsu-product-search-wrap">
                            <input type="text" id="zsuProductSearchInput"
                                   class="zsu-form-input" placeholder="Search products…">
                            <div class="zsu-product-search-dropdown" id="zsuProductSearchDropdown"
                                 style="display:none;"></div>
                        </div>
                        <div class="zsu-tagged-products" id="zsuTaggedProducts">
                            <div class="zsu-tagged-empty" id="zsuTaggedEmpty">No products tagged yet</div>
                        </div>
                        <input type="hidden" id="zsuTaggedProductIds">
                    </div>
                </div>

                <div class="zsu-form-row">
                        <label class="zsu-form-label">
                            ✅ Visibility
                            <span class="zsu-form-hint"><?php echo zsu_opt('zsu_auto_approve',0) ? 'Auto-approved on submit' : 'Pending admin review'; ?></span>
                        </label>
                        <div class="zsu-form-visibility">
                            <span class="zsu-vis-icon"><?php echo zsu_opt('zsu_auto_approve',0) ? '🌐' : '⏳'; ?></span>
                            <span class="zsu-vis-label"><?php echo zsu_opt('zsu_auto_approve',0) ? 'Public' : 'Pending Review'; ?></span>
                        </div>
                </div>

                <div class="zsu-form-nav">
                    <button class="zsu-wizard-back-btn" id="zsuStep3Back">← Back</button>
                    <button class="zsu-wizard-next-btn" id="zsuStep3Next">
                        Review & Publish →
                    </button>
                </div>
            </div>
        </div>

        <!-- ── STEP 4: REVIEW & PUBLISH ──────────────────────── -->
        <div class="zsu-wizard-pane" id="zsuStep4" style="display:none;">
            <div class="zsu-review-layout">

                <!-- Preview card -->
                <div class="zsu-review-preview">
                    <div class="zsu-review-phone">
                        <div class="zsu-review-phone-screen">
                            <video class="zsu-review-video" id="zsuReviewVideo"
                                   playsinline muted loop></video>
                            <div class="zsu-review-overlay">
                                <div class="zsu-review-title" id="zsuReviewTitle">—</div>
                                <div class="zsu-review-stats">
                                    <span>👁 0</span>
                                    <span>❤️ 0</span>
                                    <span>🔗 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review details -->
                <div class="zsu-review-details">
                    <h3 class="zsu-review-heading">📋 Review Your Short</h3>

                    <div class="zsu-review-meta-grid" id="zsuReviewMeta">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Publish button -->
                    <div class="zsu-publish-section">
                        <button class="zsu-publish-btn" id="zsuPublishBtn">
                            <span id="zsuPublishBtnLabel">🚀 Publish Short</span>
                        </button>
                        <p class="zsu-publish-note" id="zsuPublishNote">
                            <?php echo zsu_opt('zsu_auto_approve',0)
                                ? '✅ Your short will go live immediately.'
                                : '⏳ Your short will be reviewed before going live.'; ?>
                        </p>
                    </div>

                    <!-- Success state (hidden initially) -->
                    <div class="zsu-publish-success" id="zsuPublishSuccess" style="display:none;">
                        <div class="zsu-success-icon">🎉</div>
                        <h3 class="zsu-success-title">Short Published!</h3>
                        <p class="zsu-success-desc" id="zsuSuccessDesc"></p>
                        <div class="zsu-success-actions">
                            <a id="zsuViewShortLink" href="#" target="_blank" class="zsu-editor-btn">
                                🌐 View Short
                            </a>
                            <button class="zsu-editor-btn zsu-editor-btn-ghost" id="zsuUploadAnother">
                                ➕ Upload Another
                            </button>
                        </div>
                    </div>

                    <button class="zsu-wizard-back-btn" id="zsuStep4Back" style="margin-top:12px;">← Back to Details</button>
                </div>
            </div>
        </div>

    </div><!-- end upload panel -->

    <!-- ═══════════════════════════════════════════════════════
         TAB: MY SHORTS
         ═══════════════════════════════════════════════════════ -->
    <div class="zsu-studio-panel" id="zsuTab-my-shorts" role="tabpanel" style="display:none;">

        <div class="zsu-studio-subheader">
            <h2 class="zsu-studio-subtitle">🎬 My Shorts</h2>
            <div class="zsu-studio-actions">
                <select id="zsuMyStatusFilter" class="zsu-studio-select">
                    <option value="">All Statuses</option>
                    <option value="publish">Published</option>
                    <option value="pending">Pending</option>
                    <option value="draft">Draft</option>
                </select>
                <button class="zsu-studio-refresh-btn" id="zsuMyRefresh">🔄 Refresh</button>
            </div>
        </div>

        <div class="zsu-my-shorts-grid" id="zsuMyShortsGrid">
            <div class="zsu-studio-spinner-wrap">
                <div class="zsu-studio-spinner"></div>
            </div>
        </div>

        <div class="zsu-my-empty" id="zsuMyEmpty" style="display:none;">
            <div class="zsu-my-empty-icon">🎬</div>
            <div class="zsu-my-empty-title">No Shorts Yet</div>
            <div class="zsu-my-empty-desc">Upload your first short to get started!</div>
            <button class="zsu-wizard-next-btn" id="zsuGoUpload">➕ Upload Short</button>
        </div>

        <!-- Edit short modal -->
        <div class="zsu-edit-modal" id="zsuEditModal" style="display:none;" role="dialog" aria-modal="true">
            <div class="zsu-edit-modal-inner">
                <button class="zsu-edit-modal-close" id="zsuEditClose" aria-label="Close">✕</button>
                <h3 class="zsu-edit-modal-title">✏️ Edit Short</h3>
                <input type="hidden" id="zsuEditShortId">
                <div class="zsu-form-row">
                    <label class="zsu-form-label">Title</label>
                    <input type="text" id="zsuEditTitle" class="zsu-form-input" maxlength="100">
                </div>
                <div class="zsu-form-row">
                    <label class="zsu-form-label">Description</label>
                    <textarea id="zsuEditDesc" class="zsu-form-textarea" rows="3" maxlength="500"></textarea>
                </div>
                <div class="zsu-form-row">
                    <label class="zsu-form-label">Tagged Products (IDs, comma-separated)</label>
                    <input type="text" id="zsuEditProducts" class="zsu-form-input" placeholder="e.g. 12, 45, 89">
                </div>
                <div class="zsu-form-nav" style="margin-top:16px;">
                    <button class="zsu-wizard-back-btn" id="zsuEditCancel">Cancel</button>
                    <button class="zsu-wizard-next-btn" id="zsuEditSave">💾 Save Changes</button>
                </div>
            </div>
        </div>
        <div class="zsu-studio-overlay" id="zsuEditOverlay" style="display:none;"></div>

    </div>

    <!-- ═══════════════════════════════════════════════════════
         TAB: ANALYTICS
         ═══════════════════════════════════════════════════════ -->
    <div class="zsu-studio-panel" id="zsuTab-analytics" role="tabpanel" style="display:none;">

        <div class="zsu-studio-subheader">
            <h2 class="zsu-studio-subtitle">📈 My Analytics</h2>
            <select id="zsuAnalyticsDays" class="zsu-studio-select">
                <option value="7">Last 7 days</option>
                <option value="14">Last 14 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>
        </div>

        <!-- Stats cards -->
        <div class="zsu-analytics-cards" id="zsuAnalyticsCards">
            <div class="zsu-studio-spinner-wrap" style="grid-column:1/-1;">
                <div class="zsu-studio-spinner"></div>
            </div>
        </div>

        <!-- Chart -->
        <div class="zsu-analytics-chart-card">
            <canvas id="zsuVendorChart" height="90"></canvas>
        </div>

        <!-- Top shorts table -->
        <div class="zsu-analytics-table-wrap" id="zsuAnalyticsTopWrap">
            <h3 class="zsu-studio-subtitle" style="margin-bottom:12px;">🏆 Top Performing Shorts</h3>
            <div id="zsuAnalyticsTop">
                <div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════
         TAB: BOOST
         ═══════════════════════════════════════════════════════ -->
    <?php if ( zsu_opt('zsu_boost_enabled',1) ) : ?>
    <div class="zsu-studio-panel" id="zsuTab-boost" role="tabpanel" style="display:none;">

        <div class="zsu-studio-subheader">
            <h2 class="zsu-studio-subtitle">🚀 Boost Your Shorts</h2>
            <div class="zsu-studio-coin-badge">
                🪙 <span id="zsuBoostCoinBadge"><?php echo number_format($balance); ?></span> coins available
            </div>
        </div>

        <!-- Tier cards — MEGA ONLY -->
        <div class="zsu-boost-tiers-grid" style="grid-template-columns:1fr;max-width:340px;">
            <?php
            $mega_price = (int)zsu_opt('zsu_boost_price_mega',2499);
            $can_afford = $balance >= $mega_price;
            ?>
            <div class="zsu-boost-tier zsu-boost-tier--mega selected" data-tier="mega"
                 style="--tier-color:#ef4444;">
                <div class="zsu-boost-tier-glow"></div>
                <div class="zsu-boost-tier-icon">🔥</div>
                <div class="zsu-boost-tier-name">Mega Boost</div>
                <div class="zsu-boost-tier-price">
                    🪙 <?php echo number_format($mega_price); ?>
                    <span>/7 days</span>
                </div>
                <div class="zsu-boost-tier-desc">Homepage hero + every category top spot</div>
                <?php if ( ! $can_afford ) : ?>
                <div class="zsu-boost-tier-insufficient">
                    Need <?php echo number_format($mega_price - $balance); ?> more coins — <a href="#" onclick="document.querySelector('[data-tab=\'coins\']').click();return false;" style="color:#f59e0b;">Buy Coins</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Short selector for boosting -->
        <div class="zsu-boost-selector-section">
            <h3 class="zsu-studio-subtitle">Select a Short to Boost</h3>
            <div class="zsu-boost-short-picker" id="zsuBoostShortPicker">
                <div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>
            </div>
        </div>

        <!-- Boost form -->
        <div class="zsu-boost-form-section" id="zsuBoostFormSection" style="display:none;">
            <h3 class="zsu-studio-subtitle" id="zsuBoostFormTitle">Configure Boost</h3>

            <div class="zsu-boost-form">
                <input type="hidden" id="zsuBoostShortId">
                <input type="hidden" id="zsuBoostTier" value="standard">

                <div class="zsu-form-row">
                    <label class="zsu-form-label">Boost Duration</label>
                    <div class="zsu-boost-duration-btns">
                        <button class="zsu-duration-btn active" data-days="7">7 days</button>
                        <button class="zsu-duration-btn" data-days="14">14 days</button>
                        <button class="zsu-duration-btn" data-days="30">30 days</button>
                    </div>
                </div>

                <div class="zsu-boost-cost-preview" id="zsuBoostCostPreview">
                    <div class="zsu-boost-cost-row">
                        <span>Base cost (7 days)</span>
                        <span id="zsuBoostBaseCost">🪙 —</span>
                    </div>
                    <div class="zsu-boost-cost-row zsu-boost-cost-total">
                        <span>Total cost</span>
                        <span id="zsuBoostTotalCost">🪙 —</span>
                    </div>
                    <div class="zsu-boost-cost-row">
                        <span>Your balance</span>
                        <span id="zsuBoostBalance">🪙 <?php echo number_format($balance); ?></span>
                    </div>
                    <div class="zsu-boost-cost-row">
                        <span>After boost</span>
                        <span id="zsuBoostAfterBalance">🪙 —</span>
                    </div>
                </div>

                <button class="zsu-publish-btn" id="zsuBoostSubmitBtn" style="margin-top:16px;">
                    🚀 Activate Boost
                </button>
                <div class="zsu-boost-result" id="zsuBoostResult" style="display:none;"></div>
            </div>
        </div>

        <!-- Active boosts table -->
        <div class="zsu-active-boosts-section">
            <h3 class="zsu-studio-subtitle">Active Boosts</h3>
            <div id="zsuActiveBoostsWrap">
                <div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>
            </div>
        </div>

    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════
         TAB: COINS
         ═══════════════════════════════════════════════════════ -->
    <div class="zsu-studio-panel" id="zsuTab-coins" role="tabpanel" style="display:none;">

        <div class="zsu-studio-subheader">
            <h2 class="zsu-studio-subtitle">🪙 Buy Coins</h2>
        </div>

        <div class="zsu-coins-wallet">
            <!-- ═══ BUY COINS — Compact Banner ═══ -->
            <div class="zsu-buy-coins-banner" id="zsuBuyCoinsBanner">
                <div class="zsu-bcb-inner">
                    <div class="zsu-bcb-heading">
                        <span class="zsu-bcb-coin-anim">🪙</span>
                        <h2 class="zsu-bcb-title">Power Up With Coins</h2>
                        <p class="zsu-bcb-subtitle">Instant delivery · WhatsApp checkout · Mega Boost ready</p>
                    </div>

                    <!-- Package selector -->
                    <div class="zsu-bcb-packages">
                        <button class="zsu-bcb-pkg" data-coins="100"  data-price="<?php echo (float)zsu_opt('zsu_coin_rate_100',90); ?>">
                            <span class="zsu-bcb-pkg-coins">100 🪙</span>
                            <span class="zsu-bcb-pkg-price">₹<?php echo (float)zsu_opt('zsu_coin_rate_100',90); ?></span>
                            <span class="zsu-bcb-pkg-tag">Most Popular</span>
                        </button>
                        <button class="zsu-bcb-pkg" data-coins="500" data-price="<?php echo (float)zsu_opt('zsu_coin_rate_500',400); ?>">
                            <span class="zsu-bcb-pkg-coins">500 🪙</span>
                            <span class="zsu-bcb-pkg-price">₹<?php echo (float)zsu_opt('zsu_coin_rate_500',400); ?></span>
                            <span class="zsu-bcb-pkg-tag">Best Value</span>
                        </button>
                        <button class="zsu-bcb-pkg" data-coins="1000" data-price="<?php echo (float)zsu_opt('zsu_coin_rate_1000',750); ?>">
                            <span class="zsu-bcb-pkg-coins">1000 🪙</span>
                            <span class="zsu-bcb-pkg-price">₹<?php echo (float)zsu_opt('zsu_coin_rate_1000',750); ?></span>
                            <span class="zsu-bcb-pkg-tag">Power Pack</span>
                        </button>
                        <div class="zsu-bcb-pkg zsu-bcb-pkg--custom">
                            <span class="zsu-bcb-pkg-coins">Custom</span>
                            <input type="number" id="zsuCustomCoins" class="zsu-bcb-custom-input" placeholder="Enter coins" min="1" step="1">
                            <span class="zsu-bcb-pkg-price" id="zsuCustomPrice">₹—</span>
                        </div>
                    </div>

                    <div class="zsu-bcb-selected-info" id="zsuBcbSelectedInfo" style="display:none;">
                        <span class="zsu-bcb-selected-label">Selected:</span>
                        <span id="zsuBcbSelectedCoins"></span> coins for
                        <span id="zsuBcbSelectedPrice"></span>
                    </div>

                    <button class="zsu-bcb-buy-btn" id="zsuBuyCoinsBtn" disabled>
                        <span class="zsu-bcb-btn-icon">💬</span>
                        Buy via WhatsApp
                        <span class="zsu-bcb-btn-arrow">→</span>
                    </button>
                    <p class="zsu-bcb-note">Tap Buy → WhatsApp opens with your order. We confirm & credit instantly.</p>
                </div>
            </div>
            <!-- ═══ END BUY COINS ═══ -->

            <!-- Admin WhatsApp number (hidden field for JS) -->
            <span id="zsuWaNumber" style="display:none;"><?php echo esc_html(zsu_opt('zsu_whatsapp_number','919999999999')); ?></span>
        </div>

        <!-- Transaction history -->
        <div class="zsu-coins-history-section">
            <h3 class="zsu-studio-subtitle">📋 Transaction History</h3>
            <div id="zsuCoinsHistoryWrap">
                <div class="zsu-studio-spinner-wrap"><div class="zsu-studio-spinner"></div></div>
            </div>
        </div>

    </div>

    <!-- Studio toast -->
    <div class="zsu-studio-toast" id="zsuStudioToast" style="display:none;"></div>

</div><!-- .zsu-studio-wrap -->
        <?php
    }
}