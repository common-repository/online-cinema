<?php
class WPOC_Types {
    private $studios;
    function __construct() {
        // add new post types
        add_action( 'after_setup_theme', array ( $this, 'wpoc_add_post_types' ) );
        // add metaboxes
        add_action( 'add_meta_boxes', array( $this, 'wpoc_add_metaboxes' ) );
        // save metabox
        add_action( 'save_post', array( $this, 'wpoc_metaboxes_save' ) );
        // add style
        add_action( 'admin_enqueue_scripts', array( $this, 'wpoc_types_script' ) );
        //add scripts
        add_action( 'admin_footer-post-new.php', array( $this, 'wpoc_add_admin_scripts' ) );
        add_action( 'admin_footer-post.php', array( $this, 'wpoc_add_admin_scripts' ) );
        // media
        add_action( 'admin_enqueue_scripts', array( $this, 'wpoc_post_media' ) );
    }
    function wpoc_post_types() {
        return $types = array(
            array(
                'name' => __( 'TV Shows', 'oc' ),
                'type' => 'tvshows',
            ),
            array(
                'name' => __( 'Movies', 'oc' ),
                'type' => 'movies',
            ),
        );
    }
    function wpoc_post_array( $type ) {
        return array(
                'labels' => array(
                'name' => $type['name'],
            ),
            'public' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-editor-video',
            'has_archive' => true,
            'show_ui'  => true,
            'supports' => array( 'title', 'editor', 'comments' ),
        );
    }
    function wpoc_add_post_types() {
        $types = $this->wpoc_post_types();
        foreach ( $types as $type ) {
            register_post_type( $type['type'], $this->wpoc_post_array( $type ) );
        }
    }
    function wpoc_html_metabox( $post, $meta ) {
        $data = $this->wpoc_get_data( $post->ID );
        $type = $post->post_type;
        ?>
        <table class="options-table-responsive">
            <tr>
                <td class="label"><label for="id"><?php echo __( 'TMDb id', 'oc' ); ?></label></td>
                <td class="field"><input type="text" name="wpoc_id" id="id" value="<?php echo esc_attr( $data['id'] ); ?>"/><a href="#" id="tmbd-import"><?php echo __( 'Import from TMDb', 'oc' ); ?></a></td>
            </tr>
            <tr>
                <td class="label"><label for="language"><?php echo __( 'Original language', 'oc' ); ?></label></td>
                <td class="field"><input type="text" name="wpoc_language" id="language" value="<?php echo esc_attr( $data['language'] ); ?>"/></td>
            </tr>
            <tr>
                <td class="label"><label for="original-title"><?php echo __( 'Original title', 'oc' ); ?></label></td>
                <td class="field"><input type="text" name="wpoc_title" id="original-title" value="<?php echo esc_attr( $data['title'] ); ?>"/></td>
            </tr>
            <tr>
                <td class="label"><label for="premiere"><?php echo __( 'Premiere date', 'oc' ); ?></label></td>
                <td class="field"><input type="text" name="wpoc_premiere" id="premiere" value="<?php echo esc_attr( $data['premiere'] ); ?>"/></td>
            </tr>
            <tr>
                <td class="label"><label for="time"><?php echo __( 'Runtime', 'oc' ); ?></label></td>
                <td class="field"><input type="text" name="wpoc_time" id="time" value="<?php echo esc_attr( $data['time'] ); ?>"/></td>
            </tr>
            <tr>
                <td class="label"><label for="link"><?php echo __( 'Trailer link(on youtube)', 'oc' ); ?></label></td>
                <td class="field"><input type="text" name="wpoc_link" id="link" value="<?php echo esc_attr( $data['link'] ); ?>"/></td>
            </tr>
            <tr>
                <td class="label"><label for="ratings"><?php echo __( 'Ratings', 'oc' ); ?></label></td>
                <td class="field"><div id="input-ratings"><?php $this->wpoc_ratings_array( $post->ID ); ?></div><a href="#" id="ratings"><?php echo __( 'Add rating', 'oc' ); ?></a></td>
            </tr>
            <?php if ( 'movies' === $type ) { ?>
                <tr>
                    <td class="label"><label for="tagline"><?php echo __( 'Tagline', 'oc' ); ?></label></td>
                    <td class="field"><input type="text" name="wpoc_tagline" id="tagline" value="<?php echo esc_attr( $data['tagline'] ); ?>"/></td>
                </tr>
                <tr>
                    <td class="label"><label for="budget"><?php echo __( 'Budget', 'oc' ); ?></label></td>
                    <td class="field"><input type="text" name="wpoc_budget" id="budget" value="<?php echo esc_attr( $data['budget'] ); ?>"/></td>
                </tr>
                <tr>
                    <td class="label"><label for="studios"><?php echo __( 'Studios', 'oc' ); ?></label></td>
                    <td class="field"><div id="input-studios"><?php $this->wpoc_movie_studios_array( $post->ID ); ?></div><a href="#" id="studios"><?php echo __( 'Add studio', 'oc' ); ?></a></td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td class="label"><label for="studios"><?php echo __( 'Studios', 'oc' ); ?></label></td>
                    <td class="field"><div id="input-studios"><?php $this->wpoc_tv_studios_array( $post->ID ); ?></div><a href="#" id="studios"><?php echo __( 'Add studio', 'oc' ); ?></a></td>
               </tr>
                <tr>
                    <td class="label"><label for="calendar"><?php echo __( 'Calendar', 'oc' ); ?></label></td>
                    <td class="field"><div id="input-calendar"><?php $this->wpoc_tv_calendar_array( $post->ID ); ?></div><a href="#" id="season-calendar"><?php echo __( 'Add season', 'oc' ); ?></a></td>
                </tr>
            <?php } ?>
            <tr>
                <td class="label"><label for="background"><?php echo __( 'Background', 'oc' ); ?></label></td>
                <td class="field"><div id="input-backdrops"><?php echo $this->wpoc_table_section( 'backdrops', $data['back'] ); ?></div></td>
            </tr>
            <tr>
                <td class="label"><label for="poster"><?php echo __( 'Poster', 'oc' ); ?></label></td>
                <td class="field"><div id="input-posters"><?php echo $this->wpoc_table_section( 'posters', $data['poster'] ); ?></div></td>
            </tr>
        </table>
        <?php
        wp_nonce_field( $type, 'wpoc_nonce_' . $type );
    }
    function wpoc_add_admin_scripts() {
        $this->wpoc_metabox_script();
        wpoc_set_thuumbnail_script( 'posters' );
        wpoc_set_thuumbnail_script( 'backdrops' );
        wp_enqueue_media();
    }
    function wpoc_post_media( $hook ) {
        if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
            wp_enqueue_media();
        }
    }
    function wpoc_add_metaboxes() {
        $types = $this->wpoc_post_types();
        foreach ( $types as $type ) {
            add_meta_box( 'wpoc_' . $type['type'], $type['name'], array( $this, 'wpoc_html_metabox' ), array( $type ) );
        }
    }
    function wpoc_metaboxes_save( $post_id ) {
        // nonce check
        if ( empty( $_POST['wpoc_nonce_' . get_post_type( $post_id ) ] ) || ! wp_verify_nonce( $_POST['wpoc_nonce_' . get_post_type( $post_id ) ], get_post_type( $post_id ) ) ) {
            return;
        }
        // autosave
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }
        // user can edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        //saved
        if ( isset( $_POST['wpoc_id'] ) ) {
            $id = sanitize_text_field( $_POST['wpoc_id'] );
            update_post_meta( $post_id, 'wpoc_id', $id );
        }
        if ( isset( $_POST['wpoc_language'] ) ) {
            $language = sanitize_text_field( $_POST['wpoc_language'] );
            update_post_meta( $post_id, 'wpoc_language', $language );
        }
        if ( isset( $_POST['wpoc_title'] ) ) {
            $title = sanitize_text_field( $_POST['wpoc_title'] );
            update_post_meta( $post_id, 'wpoc_title', $title );
        }
        if ( isset( $_POST['wpoc_tagline'] ) ) {
            $title = sanitize_text_field( $_POST['wpoc_tagline'] );
            update_post_meta( $post_id, 'wpoc_tagline', $title );
        }
        if ( isset( $_POST['wpoc_premiere'] ) ) {
            $premiere = sanitize_text_field( $_POST['wpoc_premiere'] );
            update_post_meta( $post_id, 'wpoc_premiere', $premiere );
        }
        if ( isset( $_POST['wpoc_time'] ) ) {
            $time = sanitize_text_field( $_POST['wpoc_time'] );
            update_post_meta( $post_id, 'wpoc_time', $time );
        }
        if ( isset( $_POST['wpoc_budget'] ) ) {
            $time = sanitize_text_field( $_POST['wpoc_budget'] );
            update_post_meta( $post_id, 'wpoc_budget', $time );
        }
        if ( isset( $_POST['wpoc_link'] ) ) {
            $link = sanitize_text_field( $_POST['wpoc_link'] );
            update_post_meta( $post_id, 'wpoc_link', $link );
        }
        update_post_meta( $post_id, 'wpoc_rating_title', '' );
        if ( isset( $_POST['wpoc_rating_title'][0] ) ) {
            $rating_title = sanitize_text_field( maybe_serialize( $_POST['wpoc_rating_title'] ) );
            update_post_meta( $post_id, 'wpoc_rating_title', $rating_title );
        }
        update_post_meta( $post_id, 'wpoc_rating_value', '' );
        if ( isset( $_POST['wpoc_rating_value'][0] ) ) {
            $rating_value = sanitize_text_field( maybe_serialize( $_POST['wpoc_rating_value'] ) );
            update_post_meta( $post_id, 'wpoc_rating_value', $rating_value );
        }
        update_post_meta( $post_id, 'wpoc_studio_title', '' );
        if ( isset( $_POST['wpoc_studio_title'][0] ) ) {
            $link = sanitize_text_field( maybe_serialize( $_POST['wpoc_studio_title'] ) );
            update_post_meta( $post_id, 'wpoc_studio_title', $link );
        }
        update_post_meta( $post_id, 'wpoc_studio_iframe', '' );
        if ( isset( $_POST['wpoc_studio_iframe'][0] ) ) {
            $studio_iframe = sanitize_text_field( maybe_serialize( $_POST['wpoc_studio_iframe'] ) );
            update_post_meta( $post_id, 'wpoc_studio_iframe', $studio_iframe );
        }
        if ( isset( $_POST['wpoc_backdrops'] ) ) {
            $back = sanitize_text_field( $_POST['wpoc_backdrops'] );
            update_post_meta( $post_id, 'wpoc_backdrops', $back );
        }
        if ( isset( $_POST['wpoc_posters'] ) ) {
            $poster = sanitize_text_field( $_POST['wpoc_posters'] );
            update_post_meta( $post_id, 'wpoc_posters', $poster );
        }
        update_post_meta( $post_id, 'wpoc_tv_data', '' );
        if ( isset( $_POST['wpoc_season'] ) && is_array( $_POST['wpoc_season'] ) ) {
            update_post_meta( $post_id, 'wpoc_tv_data',  sanitize_text_field( maybe_serialize( $_POST['wpoc_season'] ) ) );
        }
        if ( isset( $_POST['tmdb_imported'] )  ) {
            update_post_meta( $post_id, 'tmdb_imported',  sanitize_text_field( maybe_serialize( $_POST['tmdb_imported'] ) ) );
        }
    }
    function wpoc_types_script( $hook ) {
        if ( 'post-new.php' !== $hook && 'post.php' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'wpoc-types', WPOC_URL . 'css/type.css' );
    }
    function wpoc_table_section( $label, $src ) {
        if ( ! empty( $src ) ) {
            $img = wpoc_thumbnail( $src );
            $addclass = ' hidden';
            $removeclass = '';
        } else {
            $img = '';
            $addclass = '';
            $removeclass = ' hidden';
        }
        $hidden = '<input type="hidden" name="wpoc_' . esc_attr( $label ) . '" value="' . esc_attr( $src ) . '"/>';
        $str = '<div class="' . esc_attr( $label ) . '">' . $img . '</div>' . $hidden .'<a href="#" class="remove-' . esc_attr( $label ) . $removeclass . '">' . __( 'Remove this', 'oc' ) . '</a><a href="' . esc_url( admin_url( 'media-upload.php' ) ) . '" class="set-' . esc_attr( $label ) . $addclass . '">' . __( 'Set photo', 'oc' ) . '</a>';
        return $str;
    }
    function wpoc_metabox_script() {
        ?>
        <script type="text/javascript">
            jQuery ( function( $ ) {
                var metaBox = $( '#wpoc_' + typenow ),
                    addRating = metaBox.find( '#ratings' ),
                    inputRatings = metaBox.find( '#input-ratings' ),
                    addStudio = metaBox.find( '#studios' ),
                    inputStudios = metaBox.find( '#input-studios' ),
                    importTMDb = metaBox.find( '#tmbd-import' ),
                    addSeason = metaBox.find( '#season-calendar' ),
                    inputSeason = metaBox.find( '#input-calendar' );
                importTMDb.on( 'click', function( event ) {
                    idTMDb = metaBox.find( '#id' ).attr( 'value' );
                    data = {
                        'type': typenow,
                        'tmdb_id': idTMDb,
                        'action': 'parser',
                    };
                    importTMDb.html( '<?php echo __( 'Loading...', 'oc' ); ?>' );
                    jQuery.post( ajaxurl, data, function( response ) {
                        importTMDb.html( '<?php echo __( 'Import from TMDb', 'oc' ); ?>' );
                        $( '#title' ).val( response.title );
                        $( '#content' ).html( response.overview );
                        $( '#input-ratings' ).html( response.rating );
                        $( '#language' ).val( response.original_language );
                        $( '#original-title' ).val( response.original_title );
                        $( '#time' ).val( response.runtime );
                        $( '#link' ).val( response.trailer );
                        $( '#input-actors' ).append( response.actors );
                        $( '#input-crew' ).append( response.crew );
                        $( '#input-companies' ).append( response.companies );
                        $( '#input-genres' ).append( response.genres );
                        $( '#input-countries' ).append( response.countries );
                        $( '#input-posters' ).html( response.poster );
                        $( '#input-backdrops' ).html( response.background );
                        $( '#premiere' ).val( response.release_date );
                        metaBox.append( '<input type="hidden" name="tmdb_imported" value="1"/>' );
                        if ( 'tvshows' === typenow ) {
                            $( '#input-creators' ).append( response.creators );
                            $( '#input-calendar' ).append( response.seasons );
                        }
                        if ( 'movies' === typenow ) {
                            $( '#budget' ).val( response.budget );
                            $( '#tagline' ).val( response.tagline );
                            $( '#years' ).val( response.release_date.substr( 0, 4 ) );
                        }
                    });
                    return false;
                });
                addRating.on( 'click', function( event ) {
                    inputRatings.append( '<?php echo $this->wpoc_input_ratings(); ?>' );
                    return false;
                });
                metaBox.on( 'click', '.delete-rating', function( event ) {
                    var index = $( this ).parent().index();
                    $( '.item-rating' ).eq( index ).remove();                    
                    return false;
                } );
                if ( 'movies' === typenow ) {
                    metaBox.on( 'click', '.delete-studio', function( event ) {
                        var index = $( this ).parent().index();
                        $( '.item-studio' ).eq( index ).remove();                    
                        return false;
                    } );
                    addStudio.on( 'click', function( event ) {
                        inputStudios.append( '<?php echo $this->wpoc_input_movie_studios(); ?>' );
                        return false;
                    });
                } else {
                    metaBox.on( 'click', '.delete-studio', function( event ) {
                        var index = $( this ).parent().index();
                        $( '.item-studio' ).eq( index ).remove();                    
                        return false;
                    } );
                    addStudio.on( 'click', function( event ) {
                        inputStudios.append( '<?php echo $this->wpoc_input_tv_studios(); ?>' );
                        return false;
                    });
                    metaBox.on( 'click', '.delete-season', function( event ) {
                        var index = $( this ).parent().index();
                        $( '.season-list' ).eq( index ).remove();                    
                        return false;
                    } );
                    addSeason.on( 'click', function( event ) {
                        inputSeason.append( '<?php echo $this->wpoc_input_series_list(); ?>' );
                        sename = inputSeason.find( '.season-name' );
                        n = sename.length - 1;
                        for ( i = n; i > -1; i++ ) {
                            if ( -1 === inArray( 'season-name-' + i, sename ) ) {
                                sename.eq( n ).attr( 'name', 'wpoc_season[' + i + '][name]' );
                                sename.eq( n ).attr( 'id', 'season-name-' + i );
                                return false;
                            };
                        }
                    });
                    metaBox.on( 'click', '.add-episode', function( event ) {
                        var index = $( this ).parent().index(),
                            name = [],
                            el = $( '.episodes' ).eq( index ),
                            a = el.append( '<?php echo $this->wpoc_input_episode(); ?>' ),
                            epname = $( '.season-list' ).eq( index ).find( '.episode-name' ),
                            name = $( '.season-list' ).eq( index ).find( '.season-name' ).attr( 'name' ),
                            name = name.split( '[name]' ),
                            id = $( '.season-list' ).eq( index ).find( '.season-name' ).attr( 'id' ),
                            n = epname.length - 1;
                        for ( i = n; i > -1; i++ ) {
                            if ( -1 === inArray( id + 'episode-name-' + i, epname ) ) {
                                el.find( '.episode-name' ).eq( n ).attr( 'name', name[0] +  '[episodes][' + i + '][name]' );
                                el.find( '.episode-date' ).eq( n ).attr( 'name', name[0] +  '[episodes][' + i + '][date]' );
                                el.find( '.episode-name' ).eq( n ).attr( 'id', id + '-episode-name-' + i );
                                el.find( '.episode-date' ).eq( n ).attr( 'id', id + '-episode-date-' + i );
                                return false;
                            }
                        }
                    } );
                    metaBox.on( 'click', '.remove-episode', function( event ) {
                        var index = $( this ).parent().index();
                        $( '.episode' ).eq( index ).remove();
                        return false;
                    } );
                    metaBox.on( 'click', '.links-block', function( event ) {
                        var senum = $( this ).parents( '.season-list' ).index(),
                            name  = [];
                            epnum = $( this ).parents( '.episode' ).index(),
                            name = $( this ).parents( '.episode' ),
                            name = name.find( '.episode-name' ).attr( 'name' ),
                            name = name.split( '[name]' ),
                            se = metaBox.find( '.season-list' ).eq( senum ),
                            ep = se.find( '.episode' ).eq( epnum ),
                            block = ep.find( '.episode-link' ),
                            obj = $( '.studio-name' );
                        if ( 0 === block.find( '.episode-url' ).length ) {
                            for ( i = 0; i < obj.length; i++ ) {
                                block.append( '<?php echo $this->wpoc_url_block(); ?>' );
                                label = block.find( '.studio-url-label' ).eq( i );
                                block.find( '.episode-url' ).eq( i ).attr( 'name', name[0] + '[url][' + i + ']' );                            
                                label.text( obj.eq( i ).val() );
                            }
                        } else {
                            block.removeClass( ' hidden' );
                        }
                        ep.find( '.links-block' ).html( '<?php echo __( 'Hide', 'oc' ); ?>' );
                        ep.find( '.links-block' ).addClass( ' show-links' );
                        ep.find( '.links-block' ).removeClass( 'links-block' );
                        return false;
                    } );
                    metaBox.on( 'click', '.show-links', function( event ) {
                        index = $( '.show-links' ).index( this );
                        el = $( '.show-links' ).eq( index );
                        ep = el.parents( '.episode' );
                        el.addClass( ' links-block' );
                        el.removeClass( 'show-links' );
                        el.html( '<?php echo __( 'Show links', 'oc' ); ?>' );
                        ep.find( '.episode-link' ).addClass( ' hidden' );
                        return false;
                    });
                    function inArray( a, b ) {
                        var arr = [];
                        if ( 1 === b.length ) {
                            return -1;
                        }
                        for ( m = 0; m < b.length; m++ ) {
                            arr[ m ] = b.eq( m ).attr( 'id' );
                        }
                        return $.inArray( a, arr );
                    }
                }
            });
        </script>
        <?php
    }
    function wpoc_input_ratings( $a = '', $b = '' ) { __( 'Movies', 'oc' );
        return '<div class="item-rating"><input type="text" name="wpoc_rating_title[]" placeholder="' . __( 'Title', 'oc' ) . '" ' . $a . '/>:<input type="text" name="wpoc_rating_value[]" placeholder="' . __( 'Value', 'oc' ) . '" ' . $b . '/><a class="delete-rating" href="#">' . __( 'Remove this', 'oc' ) . '</a><br></div>';
    }
    function wpoc_input_movie_studios( $a = '', $b = '' ) {
        return '<div class="item-studio"><input type="text" name="wpoc_studio_title[]" class="s-title" placeholder="' . __( 'Title', 'oc' ) . '" ' . $a . '/>:<input class="s-link" type="text" name="wpoc_studio_iframe[]" placeholder="' . __( 'Link', 'oc' ) . '" ' . $b . '/><a class="delete-studio" href="#">' . __( 'Remove this', 'oc' ) . '</a><br></div>';
    }
    function wpoc_input_tv_studios( $a = '' ) {
        return '<div class="item-studio"><input type="text" class="studio-name" name="wpoc_studio_title[]" placeholder="' . __( 'Title', 'oc' ) . '" ' . $a . '/><a href="#" class="delete-studio">' . __( 'Remove this', 'oc' ) . '</a><br></div>';
    }
    function wpoc_input_series_list( $senum = '', $data = '' ) {
        if ( ! empty( $data ) ) {
            $str = 'value="' . esc_attr( $data['name'] ) . '" name="wpoc_season[' . esc_attr( $senum ) . '][name]" id="season-name-' . esc_attr( $senum ) . '"';
            $se = $this->wpoc_episode_str( $data['episodes'], $senum );
        } else {
            $str = '';
            $se = '';
        }
        return '<div class="season-list"><input type="text" class="season-name" placeholder="' .  __( 'Season name', 'oc' ) . '" ' . $str . '/><a href="#" class="delete-season">' . __( 'Remove this', 'oc' ) . '</a><ul class="episodes">' . $se . '</ul><a href="#" class="add-episode">' . __( 'Add episode' ) . '</a></div>';
    }
    function wpoc_episode_str( $data, $senum ) {
        $str = '';
        foreach ( $data as $key => $value ) {
            $str .= $this->wpoc_input_episode( $key, $value, $senum );
        }
        return $str;
    }
    function wpoc_src_str( $data = '', $epnum = '', $senum = '' ) {
        global $post;
        $str = '';
        $studios = maybe_unserialize( get_post_meta( $post->ID, 'wpoc_studio_title', true ) );
        if ( empty( $studios ) ) {
            return;
        }
        $count = count( $studios );
        $i = 0;
        while ( $i < $count ) {
            if ( isset( $data[ $i ] ) ) {
                $url = $data[ $i ];
            } else {
                $url = '';
            }
            $str .= $this->wpoc_url_block( $url, $i, $senum, $epnum, $studios[ $i ] );
            $i++;
        }
        return $str;
    }
    function wpoc_input_episode( $epnum = '', $data = '', $senum = '' ) {
        if ( ! empty( $data ) ) {
            $str = 'value="' . esc_attr( $data['name'] ) . '" name="wpoc_season[' . esc_attr( $senum ) . '][episodes][' . esc_attr( $epnum ) . '][name]"';
            $v = 'value="' . esc_attr( $data['date'] ) . '" name="wpoc_season[' . esc_attr( $senum ) . '][episodes][' . esc_attr( $epnum ) . '][date]"';
            if ( isset( $data['url'] ) && ! empty( $data['url'] ) ) {
                $link = $this->wpoc_src_str( $data['url'], $epnum, $senum );
                $class = 'hidden';
            } else {
                $class = '';
                $link = '';
            }
        } else {
            $str = '';
            $v = '';
            $link = '';
            $class  = '';
        }
        return '<li class="episode"><input type="text" class="episode-name title-40" placeholder="' . __( 'Name', 'oc' ) . '" ' . $str . '/><input class="episode-date title-15" type="text" placeholder="' . __( 'Air date', 'oc' ) . '" ' . $v . '/><a href="#" class="remove-episode" ' . $v . '>' . __( 'Remove this', 'oc' ) . '</a><ul class="episode-link ' . $class . '">' . $link . '</ul><br><a href="#" class="links-block">' . __( 'Show links', 'oc' ) . '</a></li>';
    }
    function wpoc_url_block( $url = '', $num = '', $senum = '', $epnum = '', $label = '' ) {
        $str = '';
        if ( ! empty( $url ) ) {
            $studios = $this->studios;
            $str = 'value="' . esc_attr( $url ) . '" ';
        }
        $str .= 'name="wpoc_season[' . esc_attr( $senum ) . '][episodes][' . esc_attr( $epnum ) . '][url][' . esc_attr( $num ) . ']"';
        return '<li><label class="studio-url-label title-15">' . esc_html( $label ) . '</label><input type="text" ' . $str . ' class="episode-url title-40" placeholder="' . __( 'URL', 'oc' ) . '"/></li>';
    }
    function wpoc_get_data( $post_id ) {
       $id =  get_post_meta( $post_id, 'wpoc_id', true );
       $language =  get_post_meta( $post_id, 'wpoc_language', true );
       $title =  get_post_meta( $post_id, 'wpoc_title', true );
       $tagline =  get_post_meta( $post_id, 'wpoc_tagline', true );
       $premiere =  get_post_meta( $post_id, 'wpoc_premiere', true );
       $time =  get_post_meta( $post_id, 'wpoc_time', true );
       $budget =  get_post_meta( $post_id, 'wpoc_budget', true );
       $link =  get_post_meta( $post_id, 'wpoc_link', true );
        $rating_title = maybe_unserialize( get_post_meta( $post_id, 'wpoc_rating_title', true ) );
        $rating_value = maybe_unserialize( get_post_meta( $post_id, 'wpoc_rating_value', true ) );
        if ( ! empty( $rating_title ) ) {
            foreach ( $rating_title as $key => $rtitle ) {
                $rating_title = 'value="' . esc_attr( $rtitle ) . '"';
                $ratingvalue = 'value="' . esc_attr( $rating_value[ $key ] ) . '"';
                $ratings[] = array(
                    $rating_title => $ratingvalue,
                );
            }
        } else {
            $ratings = '';
        }
        $studio_title = maybe_unserialize( get_post_meta( $post_id, 'wpoc_studio_title', true ) );
        $studio_iframe = maybe_unserialize( get_post_meta( $post_id, 'wpoc_studio_iframe', true ) );
        if ( ! empty( $studio_title ) && 'movies' === get_post_type( $post_id ) ) {
            foreach ( $studio_title as $key => $stitle ) {
                $studio_title = 'value="' . esc_attr( $stitle ) . '"';
                $studioiframe = 'value="' . esc_attr( $studio_iframe[ $key ] ) . '"';
                $studios[] = array(
                    $studio_title => $studioiframe,
                );
            }
        } elseif ( ! empty( $studio_title[0] ) && 'tvshows' === get_post_type( $post_id ) ) {
            foreach ( $studio_title as $studio ) {
                $studios[] = $studio;
            }
        } else {
            $studios = '';
        }
        if ( 'tvshows' === get_post_type( $post_id ) ) {
            $this->studios = $studios;
        }
        $back = get_post_meta( $post_id, 'wpoc_backdrops', true );
        $poster = get_post_meta( $post_id, 'wpoc_posters', true );
        if ( 'tvshows' === get_post_type( $post_id ) ) {
            $seasons = get_post_meta( $post_id, 'wpoc_tv_data', true );
        } else {
            $seasons = '';
        }
        return array(
            'id' => $id,
            'language' => $language,
            'title' => $title,
            'tagline' => $tagline,
            'premiere' => $premiere,
            'time' => $time,
            'budget' => $budget,
            'link' => $link,
            'ratings' => $ratings,
            'studios' => $studios,
            'back' => $back,
            'poster' => $poster,
            'calendar' => $seasons,
        );
    }
    function wpoc_ratings_array( $post_id ) {
        $data = $this->wpoc_get_data( $post_id );
        if ( ! empty( $data['ratings'] ) ) {
            foreach ( $data['ratings'] as $rating ){
                foreach ( $rating as $key => $value ) {
                    echo $this->wpoc_input_ratings( $key, $value );
                }
            }
        } else {
            return;
        }
    }
    function wpoc_movie_studios_array( $post_id ) {
        $data = $this->wpoc_get_data( $post_id );
        if ( ! empty( $data['studios'] ) ) {
            foreach ( $data['studios'] as $studios ){
                foreach ( $studios as $key => $value ) {
                    echo $this->wpoc_input_movie_studios( $key, $value );
                }
            }
        } else {
            return;
        }
    }
    function wpoc_tv_studios_array( $post_id ) {
        $data = $this->wpoc_get_data( $post_id );
        if ( ! empty( $data['studios'] ) ) {
            foreach ( $data['studios'] as $studio ){
                $value = 'value="' . $studio . '"';
                echo $this->wpoc_input_tv_studios( $value );
            }
        } else {
            return;
        }
    }
    function wpoc_tv_calendar_array( $post_id ) {
        $data = $this->wpoc_get_data( $post_id );
        echo $this->wpoc_tv_calendar_str( $data['calendar'] );
    }
    function wpoc_tv_calendar_str( $data ) {
        $str = '';
        if ( ! empty( $data ) ) {print_r($data);
            $data = maybe_unserialize( $data );print_r($data);
            foreach ( $data as $key => $value ) {
                $str .= $this->wpoc_input_series_list( $key, $value );
            }
        } else {
            $str = '';
        }
        return $str;
    }
    function wpoc_tv_episodes_array( $post_id ) {
        $data = $this->wpoc_get_data( $post_id );
        if ( ! empty( $data['studios'] ) ) {
            foreach ( $data['studios'] as $studios ){
                foreach ( $studios as $key => $value ) {
                    echo $this->wpoc_input_movie_studios( $key, $value );
                }
            }
        } else {
            return;
        }
    }
}
new WPOC_Types;