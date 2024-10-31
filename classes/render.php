<?php
class WPOC_Render {
    function __construct() {
        // background
        add_action( 'wp_head', array( $this, 'wpoc_background' ) );
        // plugin style
        add_action( 'wp_head', array( $this, 'wpoc_front_style' ) );
        //add script
        add_action( 'wp_footer', array( $this, 'wpoc_script' ) );
        // filter of content
        add_filter( 'the_content', array( $this, 'wpoc_single_post_filter' ) );
        // poster in archive
        add_filter( 'post_thumbnail_html', array( $this, 'wpoc_poster' ), 10, 3 );
    }
    function wpoc_front_style() {
        wp_enqueue_style( 'oc-front-style', WPOC_URL . 'css/style.css' );
    }
    function wpoc_background() {
        global $post;
        if ( is_singular( array( 'movies', 'tvshows' ) ) ) {
            $src  = get_post_meta( $post->ID, 'wpoc_backdrops', true );;
        } else {
            $src  = '';
        }
        if ( ! empty( $src ) ) {
            $style = 'body { background: url(https://image.tmdb.org/t/p/original' . esc_attr( $src ) . ') no-repeat;background-size: 100%;background-attachment: fixed;}';
            $style = apply_filters( 'wpoc_addition_style', $style );
            echo '<style>';
            echo esc_attr( $style );
            echo '</style>';
        }
    }
    function wpoc_single_post_filter( $content ) {
        global $post;
        if ( is_singular( array( 'tvshows', 'movies' ) ) ) {
            return $this->wpoc_post_wrap();
        }
        return $content;
    }
    function wpoc_post_wrap() {
        if ( function_exists( 'online_cinema_post_singular' ) ) {
            return online_cinema_post_singular();
        }
        $args = array(
            'before' => '<div class="column-flex">',
            'after' => '</div>',
        );
        $args = apply_filters( 'wpoc_post_wrap', $args );
        return $args['before'] . $this->wpoc_post_data() . $args['after'];
    }
    function wpoc_post_data() {
        global $post;
        $src = get_post_meta( $post->ID, 'wpoc_posters', true );
        $img = wpoc_thumbnail( $src );
        $pargs = array(
            'before' => '<div class="poster">',
            'after' => '</div>',
        );
        $pargs = apply_filters( 'wpoc_poster', $pargs );
        $targs = array(
            'before' => '<div class="content-table">',
            'after' => '</div>',
        );
        $targs = apply_filters( 'wpoc_table', $targs );
        $cargs = array(
            'before' => '<div class="table">',
            'after' => '</div>',
        );
        $cargs = apply_filters( 'wpoc_content_data', $cargs );
        $crargs = array(
            'before' => '<div class="credits" id="credits-tabs">',
            'after' => '</div>',
        );
        $crargs = apply_filters( 'wpoc_credits_wrap', $crargs );
        $fargs = array(
            'before' => '<div class="iframes" id="iframes-tabs">',
            'after' => '</div>',
        );
        $crargs = apply_filters( 'wpoc_iframes_wrap', $fargs );
        return $cargs['before'] .
            $pargs['before'] .
            $img .
            $pargs['after'] .
            $targs['before'] .
            $this->wpoc_table_content() .
            $targs['after'] .
            $crargs['before'] .
            $this->wpoc_credits() .
            $crargs['after'] .
            $fargs['before'] .
            $this->wpoc_iframes() .
            $fargs['after'] .
            $cargs['after'];
    }
    function wpoc_table_content() {
        global $post;
        $iargs = array(
            'before_label' => '<div class="flex"><div class="label">',
            'after_label' => '</div>',
            'before_content' => '<div class="table-content">',
            'after_content' => '</div></div>',
        );
        $iargs = apply_filters( 'wpoc_item', $iargs );
        $metakeys = array(
            array(
                'label' => __( 'Genres', 'oc' ),
                'taxonomy' => 'genres',
            ),
            array(
                'label' => __( 'Original language', 'oc' ),
                'key' => 'wpoc_language',
            ),
            array(
                'label' => __( 'Original title', 'oc' ),
                'key' => 'wpoc_title',
            ),
            array(
                'label' => __( 'Countries', 'oc' ),
                'taxonomy' => 'countries',
            ),
            array(
                'label' => __( 'Tagline', 'oc' ),
                'key' => 'wpoc_tagline',
            ),
            array(
                'label' => __( 'Premiere date', 'oc' ),
                'key' => 'wpoc_premiere',
            ),
            array(
                'label' => __( 'Runtime', 'oc' ),
                'key' => 'wpoc_time',
            ),
            array(
                'label' => __( 'Budget', 'oc' ),
                'key' => 'wpoc_budget',
            ),
        );
        $i = 0;
        $c = count( $metakeys );
        $str = $iargs['before_label'] .
            __( 'Ratings', 'oc' ) .
            $iargs['after_label'] .
            $iargs['before_content'] .
            $this->wpoc_ratings( $post->ID ) .
            $iargs['after_content'] .
            $iargs['before_label'] .
            __( 'Overview', 'oc' ) .
            $iargs['after_label'] .
            $iargs['before_content'] .
            get_the_content( $post->ID ) .
            $iargs['after_content'];
            while ( $i < $c ) {
                if ( isset( $metakeys[ $i ]['key'] ) ) {
                    $meta = get_post_meta( $post->ID, $metakeys[ $i ]['key'], true );
                } else {
                    $datata = wp_get_post_terms( $post->ID, $metakeys[ $i ]['taxonomy'] );
                    $r = 0;
                    $k = count( $datata );
                    $meta = '';
                    while ( $r < $k ) {
                        $meta .= '<a href="' . esc_url( get_term_link( $datata[ $r ]->term_id, $datata[ $r ]->taxonomy ) ) . '">' . esc_html( $datata[ $r ]->name ) . '</a>';
                        $r++;
                    }
                }
                if ( isset( $meta ) && ! empty( $meta ) ) {
                    $str .= $iargs['before_label'] .
                        $metakeys[ $i ]['label'] .
                        $iargs['after_label'] .
                        $iargs['before_content'] .
                        $meta .
                        $iargs['after_content'];
                }
                $i++;
            }
            return $str;
    }
    function wpoc_ratings( $post_id ) {
        $rating_title = maybe_unserialize( get_post_meta( $post_id, 'wpoc_rating_title', true ) );
        $rating_value = maybe_unserialize( get_post_meta( $post_id, 'wpoc_rating_value', true ) );
        $str = '';
        $i = 0;
        $atts = array(
            'div' => '',
            'wrap' => '',
        );
        $atts = apply_filters( 'wpoc_ratings_atts', $atts );
        $n = count( $rating_title );
        while ( $i < $n ) {
            $str .= '<div ' . $atts['div'] . '>' . esc_html( $rating_title[ $i ] . ' - ' . $rating_value[ $i ] ) . '</div>';
            $i++;
        }
        return '<div ' . $atts['wrap'] . '>' . $str . '</div>';
    }
    function wpoc_credits() {
        global $post;
        $term = array(
            array(
                'label' => __( 'Actors', 'oc' ),
                'taxonomy' => 'actors',
            ),
            array(
                'label' => __( 'Creators', 'oc' ),
                'taxonomy' => 'creators',
            ),
            array(
                'label' => __( 'Crew', 'oc' ),
                'taxonomy' => 'crew',
            ),
            array(
                'label' => __( 'Companies', 'oc' ),
                'taxonomy' => 'companies',
            ),
        );
        $atts = array(
            'ul' => 'class="flex"',
            'li' => 'class="label"',
            'a' => '',
            'dv_ul' => 'class="scroller"',
            'div_li' => '',
            'img' => 'width="100px"',
        );
        $atts = apply_filters( 'wpoc_credits_atts', $atts );
        $credits = '<ul ' . $atts['ul'] . '>';
        $i = 0;
        while ( $i < 4 ) {
            if ( ! ( 'movies' === get_post_type( $post->ID ) && 'creators' === $term[ $i ]['taxonomy'] ) ) {
                $credits .= '<li ' . $atts['li'] . '><a ' . $atts['a'] . ' href="#tab-' . esc_attr( $i ) . '">' . esc_html( $term[ $i ]['label'] ) . '</a></li>';
            }
            $i++;
        }
        $credits = $credits . '</ul>';
        $i = 0;
        while ( $i < 4 ) {
            $args = array(
                'object_ids' => $post->ID,
                'orderby' => 'wpoc_' . $post->ID . '_order',
                'order' => 'asc',
                'meta_key' => 'wpoc_' . $post->ID . '_order',
            );
            $taxonomy = get_terms( $term[ $i ]['taxonomy'], $args );
            if ( ! empty( $taxonomy ) ) {
                $credits .= '<div id="tab-' . esc_attr( $i ) . '"><ul ' . $atts['div_ul'] . '>';
                $c = count( $taxonomy );
                $k = 0;
                while ( $k < $c ) {
                    $term_id = $taxonomy[ $k ]->term_id;
                    $src = get_term_meta( $term_id, 'wpoc_tax_img', true );
                    $thum = get_term_meta( $term_id, 'wpoc_thumbnail_id', true );
                    $meta = get_term_meta( $term_id, 'wpoc_' . $post->ID . '_meta', true );
                    if ( ! empty( $thum ) ) {
                        $img = '<img src="' . esc_url( wp_get_attachment_image_url( $src, 'full' ) ) . '" ' . $atts['img'] . '/>';
                    } elseif ( ! empty( $src ) ) {
                        $img = '<img src="https://image.tmdb.org/t/p/original' . esc_attr( $src ) . '" ' . $atts['img'] . '/>';
                    } else {
                        $img = '<img src="" alt="No photo" ' . $atts['img'] . '>';
                    }
                    $credits .= '<li ' . $atts['div_li'] . '><a href="' . esc_url( get_term_link( $term_id, $taxonomy[ $k ]->taxonomy ) ) . '">' . $img . '</a></li>';
                    $k++;
                }
                $credits .= '</ul></div>';
            }
            $i++;
        }
        $credits = apply_filters( 'wpoc_credits', $credits );
        return $credits;
    }
    function wpoc_iframes() {
        global $post;
        $atts = array(
            'ul' => 'class="flex"',
            'li' => 'class="label"',
            'a' => '',
        );
        $atts = apply_filters( 'wpoc_iframes_atts', $atts );
        $args = array(
            'width' => '720',
        );
        $trailer = get_post_meta( $post->ID, 'wpoc_link', true );
        $studios = maybe_unserialize( get_post_meta( $post->ID, 'wpoc_studio_title', true ) );
        if ( 'movies' === get_post_type( $post->ID ) ) {
            $player = maybe_unserialize( get_post_meta( $post->ID, 'wpoc_studio_iframe', true ) );
        } else {
            $player = maybe_unserialize( get_post_meta( $post->ID, 'wpoc_tv_data', true ) );
        }
        $str = '<ul ' . $atts['ul'] . '>';
        $str .= '<li ' . $atts['li'] . '><a href="#player" ' . $atts['a'] . '>' . __( 'Player', 'oc' ) . '</a></li>';
        $str .= '<li ' . $atts['li'] . '><a href="#trailer" ' . $atts['a'] . '>' . __( 'Trailer', 'oc' ) . '</a></li>';
        $str .= '</ul>';
        if ( ( ! isset( $player[0]['episodes'][0]['url'] ) && 'tvshows' === get_post_type( $post->ID ) ) ||
                ( ! isset ( $player[0] ) && 'movies' === get_post_type( $post->ID ) ) ) {
            return;
        }
        $str .= '<div id="player">' . $this->wpoc_player_iframes( $player, $studios, $args, $post->ID ) . '</div>';
        $str .= '<div id="trailer">' . wp_oembed_get( 'https://youtu.be/' . $trailer, $args ) . '</div>';
        $str = apply_filters( 'wpoc_iframes', $str );
        return $str;
    }
    function wpoc_player_iframes( $player, $studios, $args, $id ) {
        $player = maybe_unserialize( $player );
        $c = count( $studios );
        $s = count( $player );
        $i = 0;
        $type = get_post_type( $id );
        if ( 'movies' === $type ) {
            $str =  '<form><select name="studio" class="studio">';
            while ( $i < $c ) {
                $str .= '<option value="' . esc_attr( $i ) . '">' . esc_html( $studios[ $i ] ) . '</option>';
                $i++;
            }
            $str .= '</select></form>';
            $str .= '<iframe src="' . esc_url( $player[0] ) . '" width="720px" height="400px" allowfullscreen frameborder="0"></iframe>';
            $str = apply_filters( 'wpoc_movie_player', $str );
        } elseif ( 'tvshows' === $type ) {
            $e = count( $player[0]['episodes'] );
            $str = '<form><select name="season" class="season">';
            while ( $i < $s ) {
                $str .= '<option value="' . esc_attr( $i ) . '">' . esc_html( $player[ $i ]['name'] ) . '</option>';
                $i++;
            }
            $i = 0;
            $str .= '</select>';
            $str .= '<select name="episode" class="episode">';
            while ( $i < $s ) {
                $str .= '<option value="' . esc_attr( $i ) . '">' . esc_html( $player[0]['episodes'][ $i ]['name'] ) . '</option>';
                $i++;
            }
            $i = 0;
            $str .= '</select>';
            $str .=  '<select name="studio" class="studio">';
            while ( $i < $e ) {
                if ( ! empty( $player[0]['episodes'][0]['url'][ $i ] ) ) {
                    $str .= '<option value="' . esc_attr( $i ) . '">' . esc_html( $studios[ $i ] ) . '</option>';
                }
                $i++;
            }
            $str .= '</select></form>';
            $str .= '<iframe src="' . esc_url( $player[0]['episodes'][0]['url'][0] ) . '" width="720px" height="400px" allowfullscreen frameborder="0"></iframe>';
            $str = apply_filters( 'wpoc_tvshow_player', $str );
        }
        return $str;
    }
    function wpoc_poster( $html, $id, $thumbnail_id ) {
        if ( is_singular() || ( ( 'movies' || 'tvshows' ) !== get_post_type( $id ) ) ) {
            return $html;
        }
        $src = get_post_meta( $id, 'wpoc_posters', true );echo $id;
        if ( $src ) {
            $c = count( explode( '.', $src ) );
            if ( 1 === $c ) {
                $src = wp_get_attachment_image_url( $src, 'full' );
            } else {
                $src = 'https://image.tmdb.org/t/p/original' . $src;
            }
            $parts = explode( 'src="', $html );
            $p = explode( '"', $html, 2 );
            $html = $parts[0] . 'src="' . esc_url( $src ) . '"' . $p[1];
        }
        return $html;
    }
    function wpoc_script() {
        wp_enqueue_script( 'jquery-ui-tabs', array( 'jquery' ) );
        wp_enqueue_script( 'oc-mw', WPOC_URL . 'js/jquery.mousewheel.js' , array( 'jquery' ) );
        wp_enqueue_script( 'oc-init', WPOC_URL . 'js/init.js', array( 'jquery' ) );
    }
}
new WPOC_Render;