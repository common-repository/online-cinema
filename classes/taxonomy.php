<?php
class WPOC_Taxonomy {
    private $post;
    function __construct() {
        // add new taxonomies
        add_action( 'init', array ( $this, 'wpoc_add_taxonomies' ) );
        add_action( 'save_post', array( $this, 'wpoc_save_tax' ) );
        $taxonomies = $this->wpoc_taxonomy();
        foreach ( $taxonomies as $tax ) {
            if ( 3 === $tax['pos'] ) {
                $photo = $tax['tax'];
                // add form to taxonomy screen
                add_action( "{$photo}_add_form_fields", array ( $this, 'wpoc_add_tax_thumbnail' ) );
                add_action( "{$photo}_edit_form_fields", array ( $this, 'wpoc_edit_tax_thumbnail' ) );
                // save aditional taxonomy options
                add_action( "create_{$photo}", array ( $this, 'wpoc_save_tax_thumbnail' ) );
                add_action( "edited_{$photo}", array ( $this, 'wpoc_save_tax_thumbnail' ) );
                // add column
                add_filter( "manage_edit-{$photo}_columns" , array( $this, 'wpoc_tax_column_thumbnail' ) );
                // add column content
                add_filter( "manage_{$photo}_custom_column", array( $this, 'wpoc_tax_content_thumbnail' ), 10, 3 );
            }
        }
        // add taxonomies metaboxes
        add_action( 'add_meta_boxes', array( $this, 'wpoc_metabox_tax' ) );
        add_action( 'add_meta_boxes', array( $this, 'wpoc_metabox_tax_members' ) );
        //add scripts
        add_action( 'admin_footer-post-new.php', array( $this, 'wpoc_function' ) );
        add_action( 'admin_footer-post.php', array( $this, 'wpoc_function' ) );
        add_action( 'admin_footer-edit-tags.php', array( $this, 'wpoc_tax_script' ) );
        add_action( 'admin_footer-term.php', array( $this, 'wpoc_tax_script' ) );
        // media
        add_action( 'admin_enqueue_scripts', array( $this, 'wpoc_tax_media' ) );
    }
    function wpoc_taxonomy() {
        return array(
            array(
                'tax' => 'countries',
                'name' => __( 'Contries', 'oc' ),
                'pos' => 1,
            ),
            array(
                'tax' => 'genres',
                'name' => __( 'Genres', 'oc' ),
                'pos' => 1,
            ),
            array(
                'tax' => 'years',
                'name' => __( 'Years', 'oc' ),
                'pos' => 2,
            ),
            array(
                'tax' => 'creators',
                'name' => __( 'Created by', 'oc' ),
                'pos' => 3,
            ),
            array(
                'tax' => 'actors',
                'name' => __( 'Actors', 'oc' ),
                'pos' => 3,
            ),
            array(
                'tax' => 'crew',
                'name' => __( 'Crew', 'oc' ),
                'pos' => 3,
            ),
            array(
                'tax' => 'companies',
                'name' => __( 'Companies', 'oc' ),
                'pos' => 3,
            ),
        );
    }
    function wpoc_tax_array( $type ) {
        return array(
            'labels' => array(
                'name' => $type,
            ),
            'public' => true,
            'show_in_menu' => true,
            'has_archive' => true,
            'hierarchical' => false,
            'meta_box_cb' => false,
        );
    }
    function wpoc_metabox_tax() {
        add_meta_box( 'wpoc_tax', __( 'Taxonomies data', 'oc' ), array( $this, 'wpoc_html_tax_metabox' ), array( 'movies', 'tvshows' ) );
    }
    function wpoc_metabox_tax_members() {
        add_meta_box( 'wpoc_tax_members', __( 'Members data', 'oc' ), array( $this, 'wpoc_html_tax_members_metabox' ), array( 'movies', 'tvshows' ) );
    }
    function wpoc_html_tax_metabox( $post, $meta ) {
        $this->post = $post->ID;
        $taxes = $this->wpoc_taxonomy();
        ?>
        <table class="options-table-responsive">
            <?php foreach ( $taxes as $tax ) { ?>
                <?php if ( 1 === $tax['pos'] ) { ?>
                    <tr>
                        <td class="label"><label for="<?php echo esc_attr( $tax['tax'] ); ?>"><?php echo esc_attr( $tax['name'] ); ?></label></td>
                        <td class="field"><div id="input-<?php echo esc_attr( $tax['tax'] ); ?>"><?php $this->wpoc_input_array( $tax['tax'] ); ?></div><a href="#" id="add-<?php echo esc_attr( $tax['tax'] ); ?>"><?php echo __( sprintf( 'Add %s', $tax['tax'] ), 'oc' ); ?></a></td>
                    </tr>
                <?php } elseif ( 2 === $tax['pos'] ) { ?>
                    <?php if ( 'movies' === $post->post_type ) { ?>
                        <tr>
                            <td class="label"><label for="<?php echo esc_attr( $tax['tax'] ); ?>"><?php echo esc_attr( $tax['name'] ); ?></label></td>
                            <td class="field"><input type="text" name="wpoc_<?php echo esc_attr( $tax['tax'] ); ?>" id="<?php echo esc_attr( $tax['tax'] ); ?>" <?php echo $this->wpoc_value_tax( $tax['tax'] ); ?>/></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </table>
        <?php
        wp_nonce_field( 'wpoc_tax', 'wpoc_tax' );
    }
    function wpoc_html_tax_members_metabox( $post, $meta ) {
        $this->post = $post->ID;
        $taxes = $this->wpoc_taxonomy();
        ?>
        <table class="options-table-responsive">
            <?php foreach ( $taxes as $tax ) { ?>
                <?php if ( 3 == $tax['pos'] ) { ?>
                    <?php if ( 'movies' === get_post_type( $post->ID ) && 'creators' === $tax['tax'] ) { continue; } ?>
                    <tr>
                        <td class="label"><label for="<?php echo esc_attr( $tax['tax'] ); ?>"><?php echo esc_attr( $tax['name'] ); ?></label></td>
                        <td class="field"><div id="input-<?php echo esc_attr( $tax['tax'] ); ?>"><?php $this->wpoc_value_members( $tax['tax'] ); ?></div><a href="#" id="add-<?php echo $tax['tax']; ?>"><?php echo __( sprintf( 'Add %s', $tax['tax'] ), 'oc' ); ?></a></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </table>
        <?php
        wp_nonce_field( 'wpoc_tax_members', 'wpoc_tax_members' );
    }
    function wpoc_value_members( $tax ) {
        $data = wpoc_get_post_term( $tax, $this->post );
        if ( is_object( $data ) ) {
            return;
        }
        foreach ( $data as $d ){
            $value = array(
                'name' => $d->name,
                'adition' => get_term_meta( $d->term_id, 'wpoc_' . $this->post .'_meta', true ),
                'image' => get_term_meta( $d->term_id, 'wpoc_' . $tax .'_img', true )
            );
            $img = get_term_meta( $d->term_id, 'wpoc_thumbnail_id', true );
            $tmdb_img = get_term_meta( $d->term_id, 'wpoc_tax_img', true );
            if ( empty( $img ) ) {
                $value['image'] = $tmdb_img;
            } else {
                $value['image'] = $img;
            }
            echo $this->wpoc_input_members( $tax, $value );
        }
    }
    function wpoc_input_array( $tax ) {
        $args = array(
            'object_ids' => $this->post,
        );
        $terms = get_terms( $tax, $args );
        if ( ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                echo $this->wpoc_input( $term->taxonomy, $term->name );
            }
        }
    }
    function wpoc_value_tax( $tax ) {
        $term = wp_get_post_terms( $this->post, $tax );
        if ( empty( $term ) ) {
            return '';
        } else {
            return 'value="' . $term[0]->name . '"';
        }
    }
    function wpoc_input( $tax, $value = '' ) {
        if ( ! empty( $value ) || ! is_array( $value ) ) {
            $value = 'value="' . esc_attr( $value ) . '"';
        } else {
            $value = '';
        }
        if ( 'array' === getType( $tax ) ) {
            $tax = $tax['tax'];
        }
        if ( ! in_array( $tax, array( 'crew', 'actors', 'companies', 'creators' ) ) ) {
            return '<div class="item-' . $tax . '"><input type="text" name="wpoc_' . esc_attr( $tax ) . '[]" id="' . esc_attr( $tax ) . '" ' . $value . '/><a class="delete-' . esc_attr( $tax ) . '" href="#">' . __( 'Remove this', 'oc' ) . '</a><br></div>';
        } else {
            return $this->wpoc_input_members( $tax );
        }
    }
    function wpoc_input_members( $tax, $value = '' ) {
        if ( 'crew' === $tax ) {
            $adpl = __( 'Job', 'oc' );
        } else {
            $adpl = __( 'Character', 'oc' );
        }
        if ( ! empty( $value ) ) {
            if ( ! empty( $value['image'] ) ) {
                $image = '<img src="' . esc_url( $this->wpoc_tmdb_photo( $value['image'] ) ) . '" width="50px"><input type="hidden" name="wpoc_' . esc_attr( $tax ) . '_img[]" value="' . esc_attr( $value['image'] ) . '">';
            } else {
                $image = '';
            }
            if ( isset( $value['adition'] ) ) {
                $adition = 'value="' . esc_attr( $value['adition'] ) . '"';
            }
            $name= 'value="' . esc_attr( $value['name'] ) . '"';
        } else {
            $image = '';
            $name = '';
            $adition = '';
        }
        if ( 'companies' !== $tax && 'creators' !== $tax ) {
            $adition = '<input name="wpoc_' . esc_attr( $tax ) . '_addition[]" id="' . esc_attr( $tax ) . '-addition" ' . $adition . ' placeholder="' . $adpl . '">';
            $imgs = __( 'Set photo', 'oc' );
            $imgr = __( 'Remove photo', 'oc' );
        } else {
            $adition = '';
        }
        return '<div class="item-' . esc_attr( $tax ) . '">' . $image . '<input placeholder="' . __( 'Name', 'oc' ) . '" type="text" name="wpoc_' . esc_attr( $tax ) . '_name[]" id="' . esc_attr( $tax ) . '-name" ' . $name . '/>' . $adition . '<a class="delete-' . esc_attr( $tax ) . '" href="#">' . __( 'Remove this', 'oc' ) . '</a><br></div>';
    }
    function wpoc_tmdb_photo( $src ) {
        if ( empty( $src ) ) {
            return;
        }
        $srcth = wp_get_attachment_image_url( $src, 'full' );
        if ( $srcth ) {
            return $srcth;
        } else {
            return 'https://image.tmdb.org/t/p/w300/' . esc_attr( $src );
        }
    }   
    function wpoc_save_tax( $post_id ) {
        // nonce check
        if ( ! isset( $_POST['wpoc_tax'] ) || ! isset( $_POST['wpoc_tax_members'] ) || ! wp_verify_nonce( $_POST['wpoc_tax'], 'wpoc_tax' )  && ! wp_verify_nonce( $_POST['wpoc_tax_members'], 'wpoc_tax_members' ) ) {
            return;
        }
        // user can edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        $taxonomies = $this->wpoc_taxonomy();
        foreach ( $taxonomies as $taxx ) {
            $tax = $taxx['tax'];
            if ( 1 === $taxx['pos'] ) {
                wp_set_post_terms( $post_id, '', $tax );
                if( isset( $_POST['wpoc_' . $tax ] ) ) {
                    $taxs = $_POST['wpoc_' . $tax ];
                    foreach ( $taxs as $t ) {
                        $id = term_exists( $t, $tax );
                        if ( ! $id ) {
                            wp_insert_term( sanitize_text_field( $t ), $tax );
                        }
                        wp_set_post_terms( $post_id, sanitize_text_field( $t ), $tax, true );
                    }
                }
            }
            if ( 2 === $taxx['pos'] ) {
                if ( isset( $_POST['wpoc_' . $tax ] ) ) {
                    $taxy = sanitize_text_field( $_POST['wpoc_' . $tax ] );
                    $id = term_exists( $taxy, $tax );
                    if ( ! $id && ! empty( $taxy ) ) {
                        wp_insert_term( $taxy, $tax );                        
                    }
                    wp_set_post_terms( $post_id, $taxy, $tax );                    
                }
            }
            if ( 3 === $taxx['pos'] ) {
                $i = 0;
                wp_set_post_terms( $post_id, '', $tax );
                if ( isset( $_POST['wpoc_' . $tax . '_name'] ) ) {
                    $taxs = $_POST['wpoc_' . $tax . '_name'];
                    foreach ( $taxs as $t ) {
                        $id = term_exists( $t, $tax );
                        if ( ! $id ) {
                            $id = wp_insert_term( $t, $tax );
                        }
                        update_term_meta( $id['term_id'], 'wpoc_' . $post_id . '_order', $i );
                        if ( isset( $_POST['wpoc_' . $tax . '_addition'][ $i ] ) ) {
                            update_term_meta( $id['term_id'], 'wpoc_' . $post_id . '_meta', sanitize_text_field( $_POST['wpoc_' . $tax . '_addition'][ $i ] ) );
                        }
                        if ( isset( $_POST['wpoc_' . $tax . '_img'][ $i ] ) ) {
                            update_term_meta( $id['term_id'], 'wpoc_tax_img', sanitize_text_field( $_POST['wpoc_' . $tax . '_img'][ $i ] ) );
                        }
                        wp_set_post_terms( $post_id, sanitize_text_field( $t ), $tax, true );
                        $i++;
                    }
                }
            }
        }
    }
    function wpoc_function() {
        $doms = array(
            'wpoc_tax' => array( 1, 2 ),
            'wpoc_tax_members' => array( 3 ),
        );
        ?>
        <script type="text/javascript">
            jQuery ( function( $ ) {
                var taxBox;
                <?php foreach ( $doms as $key => $dom ) { ?>
                    taxBox = $( '#<?php echo esc_js( $key ); ?>' );
                    <?php foreach ( $this->wpoc_taxonomy() as $tax ) { ?>
                        <?php if ( in_array( $tax['pos'], $dom ) ) { ?>
                            input<?php echo esc_js( $tax['tax'] ) ?> = taxBox.find( '#input-<?php echo esc_js( $tax['tax'] ); ?>' );
                            add<?php echo esc_js( $tax['tax'] ) ?> = taxBox.find( '#add-<?php echo esc_js( $tax['tax'] ); ?>' );
                            remove<?php echo esc_js( $tax['tax'] ) ?> = taxBox.find( '#remove-<?php echo esc_js( $tax['tax'] ) ?>' );
                            add<?php echo esc_js( $tax['tax'] ) ?>.on( 'click', function( event ){
                                input<?php echo esc_js( $tax['tax'] ) ?>.append( '<?php echo $this->wpoc_input( $tax ); ?>' );
                                return false;
                            });
                            taxBox.on( 'click', '.delete-<?php echo esc_js( $tax['tax'] ); ?>', function( event ) {
                                var index = $( this ).parent().index();
                                $( '.item-<?php echo esc_js( $tax['tax'] ); ?>' ).eq( index ).remove();                    
                                return false;
                            } );
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            });
        </script>
        <?php
    }
    function wpoc_add_taxonomies() {
        $taxnomies = $this->wpoc_taxonomy();
        foreach ( $taxnomies as $taxnomy ) {
            if ( 'creators' === $taxnomy['tax'] ) {
                $screens = array( 'tvshows' );
            } else {
                $screens = array( 'movies', 'tvshows' );
            }
            register_taxonomy( $taxnomy['tax'], $screens, $this->wpoc_tax_array( $taxnomy['name'] ) );
        }
    }
    function wpoc_tax_column_thumbnail( $columns ) {
        $num = 1;
        $wpoc_column = array(
            'wpoc_column' => __( 'Photo', 'oc' ),
        );
        return array_slice( $columns, 0, $num ) + $wpoc_column + array_slice( $columns, $num );
    }
    function wpoc_tax_content_thumbnail( $content, $column_name, $term_id ) {
        if ( ! $this->wpoc_photo( $term_id ) ) {
            return;
        }
        return $this->wpoc_photo( $term_id );
    }
    function wpoc_add_tax_thumbnail() {
        ?>
        <div id="input-thumbnail" class="form-field term-oc-thumbnail-wrap hide-if-no-js">
            <label for="oc-thumbnail"><?php echo __( 'Choosen Photo', 'oc' ) ?></label>
            <div class="thumbnail"></div>
            <input class="custom-photo-id" type="hidden" name="wpoc_thumbnail_id" id="oc-thumbnail"/>
            <a class="set-thumbnail" href="<?php echo esc_url( admin_url( 'media-upload.php' ) ); ?>"><?php echo __( 'Set Photo', 'oc' ); ?></a>
            <a class="remove-thumbnail hidden" href="#"><?php echo __( 'Remove this photo', 'oc' ); ?></a>
        </div>
        <?php
    }
    function wpoc_tax_script() {
        wpoc_set_thuumbnail_script( 'thumbnail' );
    }
    function wpoc_tax_media( $hook ) {
        if ( 'edit-tags.php' === $hook || 'term.php' === $hook ) {
            wp_enqueue_media();
        }
    }
    function wpoc_edit_tax_thumbnail( $data ) {
        $img = $this->wpoc_photo( $data->term_id );
        if ( ! empty( $img ) ) {
            $set = 'hidden';
            $remove = '';
            $v = get_term_meta( $data->term_id, 'wpoc_thumbnail_id', true );
            $value = 'value="' . esc_attr( $v ) . '"';
        } else {
            $set = '';
            $remove = 'hidden';
            $value = '';
        }
        ?>
        <tr class="form-field term-thumbnail-wrap">
            <th class="row">
                <label for=""><?php echo __( 'Photo', 'oc' ); ?></label>
            </th>
            <td>
                <div id="input-thumbnail" class="form-field term-oc-thumbnail-wrap hide-if-no-js">
                    <div class="thumbnail"><?php echo $img; ?></div>
                    <input class="custom-photo-id" type="hidden" name="wpoc_thumbnail_id" id="oc-thumbnail" <?php echo $value; ?>/>
                    <a class="set-thumbnail <?php echo $set; ?>" href="<?php echo esc_url( admin_url( 'media-upload.php' ) ); ?>"><?php echo __( 'Set Photo', 'oc' ); ?></a>
                    <a class="remove-thumbnail <?php echo $remove; ?>" href="#"><?php echo __( 'Remove this photo', 'oc' ); ?></a>
                </div>            
            </td>
        </tr>
        <?php
    }
    function wpoc_photo( $term_id ) {
        $thumbnail_id = get_term_meta( $term_id, 'wpoc_thumbnail_id' );
        $tmdb_src = get_term_meta( $term_id, 'wpoc_tax_img', true );
        if ( ! empty( $thumbnail_id[0] ) ) {
            return sprintf( '<img src="%s">', esc_url( wp_get_attachment_image_url( $thumbnail_id[0] ) ) );
        } elseif ( ! empty( $tmdb_src ) ) {
            return '<img src="' . esc_url( $this->wpoc_tmdb_photo( $tmdb_src ) ) . '" width="150px">';
        } else {
            return;
        }
    }
    function wpoc_save_tax_thumbnail( $term_id ) {
        if ( ! isset( $_POST['wpoc_thumbnail_id'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], "update-tag_$term_id" ) &&
        ! wp_verify_nonce( $_POST['_wpnonce_add-tag'], "add-tag" ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_term', $term_id ) ) {
            return;
        }
        update_term_meta( $term_id, 'wpoc_thumbnail_id', $_POST['wpoc_thumbnail_id'] );
    }
}
new WPOC_Taxonomy;