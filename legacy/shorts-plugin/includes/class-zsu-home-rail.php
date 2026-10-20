<?php
/**
 * ZSU_Home_Rail — v17.0 CINEMATIC3D
 * 3D cylindrical carousel + pure black BG + optional BG image
 */
defined( 'ABSPATH' ) || exit;

class ZSU_Home_Rail {

    public static function init(): void {
        add_shortcode( 'zsu_home_rail', [ self::class, 'render' ] );
        add_action( 'wp_enqueue_scripts', [ self::class, 'maybe_enqueue' ] );
        add_action( 'wp_ajax_zsu_home_rail_fetch',         [ self::class, 'ajax_fetch' ] );
        add_action( 'wp_ajax_nopriv_zsu_home_rail_fetch',  [ self::class, 'ajax_fetch' ] );
        add_action( 'wp_ajax_zsu_home_spin_check',         [ self::class, 'ajax_spin_check' ] );
        add_action( 'wp_ajax_nopriv_zsu_home_spin_check',  [ self::class, 'ajax_spin_check' ] );
        add_action( 'wp_ajax_zsu_home_spin_result',        [ self::class, 'ajax_spin_result' ] );
        add_action( 'wp_ajax_nopriv_zsu_home_spin_result', [ self::class, 'ajax_spin_result' ] );
    }

    public static function maybe_enqueue(): void {
        if ( is_admin() ) return;
        global $post;
        if ( ! $post instanceof WP_Post ) return;
        if ( ! has_shortcode( $post->post_content, 'zsu_home_rail' ) ) return;
        self::enqueue();
    }

    private static function enqueue(): void {
        if ( wp_style_is( 'zsu-home-rail', 'enqueued' ) ) return;
        $css_ver = ZSU_VER . '.' . @filemtime( ZSU_ASSETS_DIR . 'css/zsu-home-rail.css' );
        $js_ver  = ZSU_VER . '.' . @filemtime( ZSU_ASSETS_DIR . 'js/zsu-home-rail.js' );
        wp_enqueue_style(  'zsu-home-rail', ZSU_ASSETS_URL.'css/zsu-home-rail.css', [], $css_ver );
        wp_enqueue_script( 'zsu-home-rail', ZSU_ASSETS_URL.'js/zsu-home-rail.js',  [], $js_ver, true );
        $primary = sanitize_hex_color( zsu_opt('zsu_primary_color','#7c3aed') ) ?: '#7c3aed';
        wp_add_inline_style( 'zsu-home-rail', ".zhr{--p:{$primary};}" );
        wp_localize_script( 'zsu-home-rail', 'ZSU_HR', [
            'ajax_url'           => admin_url('admin-ajax.php'),
            'nonce'              => wp_create_nonce( ZSU_NONCE ),
            'shorts_url'         => home_url('/'.zsu_opt('zsu_shorts_page_slug','shorts')),
            'spin_prizes'        => self::prizes(),
            'spin_enabled'       => (bool)zsu_opt('zsu_spin_wheel_enabled',1),
            'spin_title'         => zsu_opt('zsu_spin_wheel_title','Spin & Win'),
            'spin_subtitle'      => zsu_opt('zsu_spin_wheel_subtitle','One free spin every 24 hours'),
            'views_min_display'  => (int)zsu_opt('zsu_home_rail_views_min',0),
            'animation_level'    => zsu_opt('zsu_home_rail_animation','medium'),
            'autoplay'           => (bool)zsu_opt('zsu_home_rail_autoplay',1),
            'autoplay_speed'     => (int)zsu_opt('zsu_home_rail_autoplay_speed',3200),
            'show_likes'         => (bool)zsu_opt('zsu_home_rail_show_likes',1),
            'show_follow'        => (bool)zsu_opt('zsu_home_rail_show_follow',1),
            'show_products'      => (bool)zsu_opt('zsu_home_rail_show_products',1),
            'active_preset'      => zsu_opt('zsu_preset_active','clean_white'),
            'wheel_preset'       => zsu_opt('zsu_wheel_preset','same'),
            'border_radius'      => (int)zsu_opt('zsu_border_radius',50),
            'primary_color'      => sanitize_hex_color(zsu_opt('zsu_primary_color','#2563eb')) ?: '#2563eb',
            'accent_color'       => sanitize_hex_color(zsu_opt('zsu_accent_color','#f59e0b')) ?: '#f59e0b',
            'max_items'          => (int)zsu_opt('zsu_home_rail_count', 8),
            'views_min_display'  => (int)zsu_opt('zsu_home_rail_views_min', 0),
            'show_views'         => (bool)zsu_opt('zsu_home_rail_show_views', 1),
            'views_multiplier'   => max(1,(int)zsu_opt('zsu_home_rail_views_multiplier',1)),
            'views_floor'        => (int)zsu_opt('zsu_home_rail_views_floor',0),
            'default_filter'     => zsu_opt('zsu_home_rail_default_filter','trending'),
        ]);
    }

    private static function prizes(): array {
        $raw    = zsu_opt('zsu_spin_wheel_prizes','');
        $parsed = $raw ? json_decode($raw,true) : null;
        return (is_array($parsed)&&!empty($parsed)) ? $parsed : [
            ['label'=>'10% OFF',    'color'=>'#7c3aed','type'=>'coupon','value'=>'SPIN10'  ],
            ['label'=>'₹50 Cash',   'color'=>'#0891b2','type'=>'coupon','value'=>'CASH50'  ],
            ['label'=>'Free Ship',  'color'=>'#059669','type'=>'coupon','value'=>'FREESHIP'],
            ['label'=>'20% OFF',    'color'=>'#d97706','type'=>'coupon','value'=>'SPIN20'  ],
            ['label'=>'Try Again',  'color'=>'#374151','type'=>'none',  'value'=>''        ],
            ['label'=>'15% OFF',    'color'=>'#dc2626','type'=>'coupon','value'=>'SPIN15'  ],
            ['label'=>'₹100 Cash',  'color'=>'#7c3aed','type'=>'coupon','value'=>'CASH100' ],
            ['label'=>'Lucky Dip',  'color'=>'#0d9488','type'=>'coupon','value'=>'LUCKY'   ],
        ];
    }

    private static function client_ip(): string {
        foreach(['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k)
            if(!empty($_SERVER[$k])) return sanitize_text_field(strtok($_SERVER[$k],','));
        return '0.0.0.0';
    }
    private static function spin_key(): string { return 'zsu_spin_'.md5(self::client_ip()); }

    public static function ajax_spin_check(): void {
        check_ajax_referer(ZSU_NONCE,'nonce');
        $spun = get_transient(self::spin_key()); $ch=(int)zsu_opt('zsu_spin_cooldown_hours',24);
        if(false!==$spun){ $left=max(0,(int)$spun+$ch*HOUR_IN_SECONDS-time()); wp_send_json_success(['blocked'=>true,'seconds_left'=>$left]); }
        wp_send_json_success(['blocked'=>false]);
    }

    public static function ajax_spin_result(): void {
        check_ajax_referer(ZSU_NONCE,'nonce');
        $key=$self=self::spin_key(); $spun=get_transient($key);
        if(false!==$spun){ $left=max(0,(int)$spun+DAY_IN_SECONDS-time()); wp_send_json_error(['message'=>'already_spun','seconds_left'=>$left]); }
        $cooldown_hours=(int)zsu_opt('zsu_spin_cooldown_hours',24); set_transient($key,time(),$cooldown_hours*HOUR_IN_SECONDS);
        $uid=get_current_user_id(); if($uid) update_user_meta($uid,'zsu_last_spin_ts',time());
        $idx=absint($_POST['prize_index']??0); $prizes=self::prizes(); $prize=$prizes[$idx]??null;
        if(!$prize) wp_send_json_error(['message'=>'Invalid prize']);
        wp_send_json_success(['prize'=>$prize,'message'=>$prize['type']==='none'?'Better luck next time!':'You won '.$prize['label'].'!']);
    }

    public static function ajax_fetch(): void {
        check_ajax_referer(ZSU_NONCE,'nonce');
        $count=min(absint($_POST['count']??8),20);
        $filter=sanitize_key($_POST['filter']??'trending');
        $args=['post_type'=>ZSU_CPT,'post_status'=>'publish','posts_per_page'=>$count,'no_found_rows'=>true,'meta_query'=>[]];
        // Always restrict home rail to premium (boosted) shorts only
        $args['meta_query'][] = [ 'key' => '_zsu_is_boosted', 'value' => '1' ];

        switch($filter){
            case 'featured': $args['meta_query'][]=['key'=>'_zsu_is_featured','value'=>'1']; break;
            case 'premium':
            case 'boosted':  /* already filtered above */ break;
            case 'trending': $args['meta_key']='_zsu_views'; $args['orderby']='meta_value_num'; $args['order']='DESC'; break;
            default: $args['orderby']='date'; $args['order']='DESC';
        }
        $query=$shorts=[]; $slug=zsu_opt('zsu_shorts_page_slug','shorts');
        $query=new WP_Query($args);
        foreach($query->posts as $post){
            // Ground-truth boost check — skip (and self-heal) anything whose boost has actually
            // expired, even if the cached "_zsu_is_boosted" meta hasn't been cleared yet.
            if ( ! zsu_sync_boost_meta( $post->ID ) ) {
                continue;
            }
            $meta=function_exists('zsu_get_short_meta')?zsu_get_short_meta($post->ID):[];
            $thumb=$meta['thumb_url']??(get_the_post_thumbnail_url($post->ID,'large')?:'');
            $vendor=[];
            if(!empty($meta['vendor_id'])){ $vuser=get_user_by('id',$meta['vendor_id']); if($vuser) $vendor=['name'=>$vuser->display_name,'avatar'=>get_avatar_url($vuser->ID,['size'=>48])]; }
            $shorts[]=['id'=>$post->ID,'title'=>$post->post_title,'thumb_url'=>$thumb,'video_url'=>$meta['video_url']??'',
                'likes'=>(int)get_post_meta($post->ID,'_zsu_likes',true),'views'=>(int)get_post_meta($post->ID,'_zsu_views',true),
                'is_boosted'=>true,'is_featured'=>(bool)get_post_meta($post->ID,'_zsu_is_featured',true),
                'vendor'=>$vendor,'short_url'=>home_url('/'.$slug.'/v/'.$post->ID),
                'product_count'=>(int)get_post_meta($post->ID,'_zsu_product_count',true)];
        }
        wp_send_json_success(['shorts'=>$shorts]);
    }

    public static function render(array $atts): string {
        $atts=shortcode_atts([
            'count'         => 8,
            'title'         => '',
            'subtitle'      => '',
            'show_title'    => 1,
            'instagram_url' => '',
            'bg_image'      => '',   // optional: URL to background image
        ],$atts,'zsu_home_rail');

        self::enqueue();

        $uid        = 'zhr_'.uniqid();
        $title      = $atts['title']    ?: zsu_opt('zsu_home_rail_title','Top influencer picks');
        $subtitle   = $atts['subtitle'] ?: zsu_opt('zsu_home_rail_subtitle','Discover styles loved by influencers');
        $shorts_url = home_url('/'.zsu_opt('zsu_shorts_page_slug','shorts'));
        $ig_url     = $atts['instagram_url'] ?: zsu_opt('zsu_instagram_url','https://www.instagram.com/');

        // Background image: shortcode attr > plugin setting > zsu-bg.gif fallback
        $bg_img = $atts['bg_image'] ?: zsu_opt('zsu_home_rail_bg_image','');
        if(!$bg_img) $bg_img = content_url('/uploads/zsu-bg.gif');
        $has_bg = !empty($bg_img);

        ob_start(); ?>
<section class="zhr<?php echo $has_bg?' has-bg-img':''; ?>"
         id="<?php echo esc_attr($uid); ?>"
         data-count="<?php echo (int)$atts['count']; ?>"
         data-filter="trending">

    <?php if($has_bg): ?>
    <img class="zhr__bg-img" src="<?php echo esc_url($bg_img); ?>" alt="" aria-hidden="true" loading="lazy">
    <div class="zhr__bg-overlay"></div>
    <?php endif; ?>

    <?php if($atts['show_title']): ?>
    <div class="zhr__header">
        <div class="zhr__header-left">
            <div class="zhr__platform-badge">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                Instagram
            </div>
            <h2 class="zhr__title"><?php echo wp_kses_post($title); ?></h2>
            <p class="zhr__subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>
        <div class="zhr__header-right">
            <div class="zhr__filters" role="tablist">
                <button class="zhr__filter active" data-filter="trending" role="tab">Trending</button>
            </div>
            <?php if($ig_url): ?>
            <a class="zhr__follow-btn" href="<?php echo esc_url($ig_url); ?>" target="_blank" rel="noopener noreferrer">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                Follow Us
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- JS builds the 3D carousel here -->
    <div class="zhr__rail-inner"></div>

    <div class="zhr__cta-strip">
        <a class="zhr__cta-btn" href="<?php echo esc_url($shorts_url); ?>">
            <?php echo esc_html(zsu_opt('zsu_home_rail_cta_label','View All Shorts')); ?>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>

</section>
        <?php
        return ob_get_clean();
    }
}
