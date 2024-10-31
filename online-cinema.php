<?php
/*
Plugin Name: Online Cinema
Plugin URI: http://wp-dev.lazycrub.com/oc-plugin
Description: Plugin that will make with any WP-theme cinema.
Author: Cheater
Author URI: https://www.facebook.com/profile.php?id=100011067999022
Version: 1.2.1
Text Domain: oc
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
define( 'WPOC_URL', plugins_url( '/assets/', __FILE__ ) );
class WPOC_Main {
    function __construct() {
        // traslate
        add_action( 'init', array ( $this, 'wpoc_translate' ) );
        // including plugin classes
        require_once dirname( __FILE__ ) . '/classes/types.php';
        require_once dirname( __FILE__ ) . '/classes/taxonomy.php';
        require_once dirname( __FILE__ ) . '/classes/shortcodes.php';
        require_once dirname( __FILE__ ) . '/classes/render.php';
        require_once dirname( __FILE__ ) . '/classes/plugin-settings.php';
        require_once dirname( __FILE__ ) . '/classes/parser.php';
        require_once dirname( __FILE__ ) . '/classes/widgets/taxonomy.php';
        require_once dirname( __FILE__ ) . '/classes/widgets/related.php';
        require_once dirname( __FILE__ ) . '/functions.php';
        // add ajax function to admin-panel
        add_action( 'wp_ajax_parser', array( $this, 'wpoc_parser' ) );
        // including widgets
        add_action( 'widgets_init', array ( $this, 'wpoc_register_widgets' ) );
        // tmdb logo in footer
        add_action( 'wp_footer', 'wpoc_tmdb_logo' );
        //add json
        add_action( 'wp_footer', array( $this, 'wpoc_json_str' ) );
    }
    function wpoc_json_str() {
        global $post;
        if ( ! is_singular() ) {
            return;
        }
        if ( 'movies' === get_post_type( $post->ID ) ) {
            $player = get_post_meta( $post->ID, 'wpoc_studio_iframe', true );
        } elseif ( 'tvshows' === get_post_type( $post->ID ) ) {
            $player = get_post_meta( $post->ID, 'wpoc_tv_data', true );
        }
        $studios = get_post_meta( $post->ID, 'wpoc_studio_title', true );
        if ( isset( $player ) && $player ) {
            echo '<script>';
            echo 'var data = ' . wp_json_encode( maybe_unserialize( $player ) ) . ',';
            echo 'studios  = ' . wp_json_encode( maybe_unserialize( $studios ) ) . ';';
            echo '</script>';
        }   
    }
    function wpoc_parser() {
        $parse = new WPOC_Parser;
        $tax = new WPOC_Taxonomy;
        $types  = new WPOC_Types;
        $type = $_POST['type'];
        if ( 'tvshows' === $type ) {
            $type  = 'tv';
        }
        if ( 'movies' === $type ) {
            $type  = 'movie';
        }
        $tmdb_id = $_POST['tmdb_id'];
        /* Get data about media from https://api.themoviedb.org */
        $details = $parse->wpoc_get_data( $parse->wpoc_link( $type, $tmdb_id ) );
        $credits = $parse->wpoc_get_data( $parse->wpoc_link( $type, $tmdb_id, 'credits' ) );
        $videos = $parse->wpoc_get_data( $parse->wpoc_link( $type, $tmdb_id, 'videos' ) );
        $images = $parse->wpoc_get_data( $parse->wpoc_link( $type, $tmdb_id, 'images' ) );
        $personals = $parse->wpoc_credits_str( $credits, $tax );
        $companies = $parse->wpoc_companies_list( $details->production_companies, $tax );
        if ( 'tv' === $type ) {
            $title = $details->name;
            $original_title = $details->original_name;
            $release_date = $details->first_air_date;
            $runtime = $details->episode_run_time;
            $background = $parse->wpoc_tvimages( $details, $types, 'backdrop' );
            $poster = $parse->wpoc_tvimages( $details, $types, 'poster' );
            $creators = $parse->wpoc_creators( $details->created_by, $tax );
            foreach ( $details->seasons as $season ) {
                $episodes = $parse->wpoc_get_data( $parse->wpoc_link( $type, $tmdb_id, 'season/' . $season->season_number ) );
                $eps = array();
                foreach ( $episodes->episodes as $episode ) {
                    $eps[] = array(
                        'name' => $episode->name,
                        'date' => $episode->air_date,
                    );
                }
                $seas[] = array(
                    'name' => $season->name,
                    'episodes' => $eps,
                );
            }
            $calendar = $seas;
            $countries = '';
            foreach ( $details->origin_country as $country ) {
                $countries .= $tax->wpoc_input( 'countries', $country );
            }
        } else {
            $budget = $details->budget;
            $original_title = $details->original_title;
            $release_date = $details->release_date;
            $runtime = $details->runtime;
            $tagline = $details->tagline;
            $title = $details->title;
            $countries = $parse->wpoc_taxonomy( $details, $tax, 'production_countries' );
            $background = $parse->wpoc_images( $images, $types, 'backdrops' );
            $poster = $parse->wpoc_images( $images, $types, 'posters' );
            $creators = '';
        }
        $response = array(
            'budget' => $budget,
            'original_language' => $details->original_language,
            'original_title' => $original_title,
            'overview' => $details->overview,
            'release_date' => $release_date,
            'runtime' => $runtime,
            'tagline' => $tagline,
            'title' => $title,
            'rating' => $types->wpoc_input_ratings( 'value="TMDb"', 'value="' . $details->vote_average . '"' ),
            'creators' => $creators,
            'actors' => $personals['actors'],
            'crew' => $personals['crew'],
            'companies' => $companies,
            'genres' => $parse->wpoc_taxonomy( $details, $tax, 'genres' ),
            'countries' => $countries,
            'trailer' => $parse->wpoc_trailer( $videos->results ),
            'background' => $background,
            'poster' => $poster,
            'seasons' => $types->wpoc_tv_calendar_str( $calendar ),
        );
        wp_send_json( $response );
    }
    function wpoc_translate() {
        load_plugin_textdomain( 'oc', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }
    function wpoc_register_widgets() {
        register_widget( 'WPOC_Term_List_Widget' );
        register_widget( 'WPOC_Related_Posts_Widget' );
    }
}
new WPOC_Main;