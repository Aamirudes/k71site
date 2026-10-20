<?php
defined( 'ABSPATH' ) || exit;

/**
 * ZSU_SEO — outputs VideoObject JSON-LD schema on the /shorts page.
 */
class ZSU_SEO {

    public static function init(): void {
        if ( zsu_opt( 'zsu_seo_schema', 1 ) ) {
            add_action( 'wp_head', [ self::class, 'output_schema' ] );
        }
    }

    public static function output_schema(): void {
        if ( ! get_query_var( 'zsu_shorts_page' ) ) return;

        $shorts = get_posts( [
            'post_type'      => ZSU_CPT,
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        if ( empty( $shorts ) ) return;

        $items = [];
        foreach ( $shorts as $s ) {
            $meta = zsu_get_short_meta( $s->ID );
            if ( empty( $meta['video_url'] ) ) continue;

            $thumb = $meta['thumb_url'] ?: zsu_placeholder_thumb( $s->ID, $s->post_title );

            $items[] = [
                '@type'        => 'VideoObject',
                'name'         => get_the_title( $s ),
                'description'  => wp_trim_words( get_the_excerpt( $s ), 30 ),
                'thumbnailUrl' => $thumb,
                'contentUrl'   => $meta['video_url'],
                'uploadDate'   => get_the_date( 'c', $s ),
                'duration'     => $meta['duration'] ? 'PT' . $meta['duration'] . 'S' : null,
                'interactionStatistic' => [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/WatchAction',
                    'userInteractionCount' => $meta['views'],
                ],
            ];
        }

        if ( empty( $items ) ) return;

        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => $items,
        ];

        echo '<script type="application/ld+json">'
            . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
            . '</script>' . "\n";
    }
}