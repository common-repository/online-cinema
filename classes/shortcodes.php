<?php
class WPOC_Shortcodes {
    function __construct() {
        // preview
        add_shortcode( 'preview', array( $this, 'wpoc_preview' ) );
    }
    function wpoc_preview( $atts ) {
        $atts = shortcode_atts( array(
            'title' => '',
            'post_type' => false,
            'link' => true,
            'count' => 3,
        ), $atts );
        $elem = array(
            'wrap_before' => '<div clas="preview">',
            'wrap_after' => '</div>',
            'item_wrap' => '',
            'item_before' => '<div class="item">',
            'item_after' => '</div>',
        );
        $elem = apply_filters( 'wpoc_preview_shortcode', $elem );
        $items = new WP_Query( array(
            'post_type' => $atts['post_type'],
            'posts_per_page' => $atts['count'],
        ) );
        if ( function_exists( 'online_cinema_shortcode' ) ) {
            return online_cinema_shortcode( $items );
        } else {
            $str = $elem['wrap_before'];
            $str .= ! empty( $atts['title'] ) ? '<h2>' . $atts['title'] . '</h2>' : '';
            if( $atts['link'] ) {
                $str .= '<div class="link"><a href="' . esc_url( get_post_type_archive_link( $atts['post_type'] ) ) . '">' . __( 'See all', 'oc' ) . '</a></div>';
                $str .= $elem['item_wrap'];
            }
            while ( $items->have_posts() ) {
                $items->the_post();
                $str .= $elem['item_before'];
                $src = get_post_meta( get_the_ID(), 'wpoc_posters', true );
                $img = wpoc_thumbnail( $src );
                $permalink = get_the_permalink();
                $item = '<a href="' . esc_html( $permalink ) . '">' . $img . '</a><a href="' . esc_url( $permalink ) . '"><h2>' . esc_html( get_the_title() ) . '</h2></a>';
                $str .= apply_filters( 'wpoc_shortcode_item', $item, $items->posts[ $items->current_post ] );
                $str .= $elem['item_after'];
            }
            $str .= $elem['wrap_after'];
            return $str;
        }
    }
}
new WPOC_Shortcodes;