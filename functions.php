<?php
function wpoc_thumbnail( $src ) {
    $check = explode( '.', $src );
    if ( 1 === count( $check ) ) {
        $img = '<img src="' . esc_url( wp_get_attachment_image_url( $src, 'full' ) ) . '" width="300px" class="figure-img img-fluid"/>';
    } else {
        $img = '<img src="https://image.tmdb.org/t/p/w300' . esc_attr( $src ) . '" class="figure-img img-fluid"/>';
    }
    return $img;
}
function wpoc_tmdb_logo() {
    global $post;
    if ( is_singular() && get_post_meta( $post->ID, 'tmdb_imported', true ) ) {
        echo '<span class="alert"><img src="https://www.themoviedb.org/assets/2/v4/logos/primary-blue-40c00543e47b657e8e53a2f3e8650eb9de230316cf158965edb012d72ddca755.svg" width="20px" alt="TMDb logo">' . __( 'Metadata of this post was imported from TMDb' ) . '</span>';
    }
}
function wpoc_set_thuumbnail_script( $selector ) {
    ?>
    <script type="text/javascript">
        jQuery ( function( $ ) {
            var frame,
                set<?php echo esc_js( $selector ); ?> = $( '#input-<?php echo esc_js( $selector ); ?>' ),
                add<?php echo esc_js( $selector ); ?>Link = set<?php echo esc_js( $selector ); ?>.find( '.set-<?php echo esc_js( $selector ); ?>' ),
                del<?php echo esc_js( $selector ); ?>Link = set<?php echo esc_js( $selector ); ?>.find( '.remove-<?php echo esc_js( $selector ); ?>' ),
                <?php echo esc_js( $selector ); ?>Container = set<?php echo esc_js( $selector ); ?>.find( '.<?php echo esc_js( $selector ); ?>' ),
                <?php echo esc_js( $selector ); ?>IdInput = set<?php echo esc_js( $selector ); ?>.find( 'input' );
            // ADD IMAGE LINK
            add<?php echo esc_js( $selector ); ?>Link.on( 'click', function( event ){
                event.preventDefault();
                // If the media frame already exists, reopen it.
                if ( frame ) {
                    <?php echo esc_js( $selector ); ?>Frame.open();
                    return;
                }
                // Create a new media frame
                <?php echo esc_js( $selector ); ?>Frame = wp.media({
                    title: '<?php echo __( 'Select or Upload Media Of Your Chosen Persuasion', 'oc' ); ?>',
                    button: {
                        text: '<?php echo __( 'Use this media', 'oc' ); ?>'
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });
                // When an image is selected in the media frame...
                <?php echo esc_js( $selector ); ?>Frame.on( 'select', function() {
                    // Get media attachment details from the frame state
                    var attachment = <?php echo esc_js( $selector ); ?>Frame.state().get( 'selection' ).first().toJSON();
                    // Send the attachment URL to our custom image input field.
                    <?php echo esc_js( $selector ); ?>Container.append( '<img src="' + attachment.url + '" alt="" width="300px"/>' );
                    // Send the attachment id to our hidden input
                    <?php echo esc_js( $selector ); ?>IdInput.val( attachment.id );
                    // Hide the add image link
                    add<?php echo esc_js( $selector ); ?>Link.addClass( 'hidden' );
                    // Unhide the remove image link
                    del<?php echo esc_js( $selector ); ?>Link.removeClass( 'hidden' );
                });
                // Finally, open the modal on click
                <?php echo esc_js( $selector ); ?>Frame.open();
            });
            // DELETE IMAGE LINK
            del<?php echo esc_js( $selector ); ?>Link.on( 'click', function( event ) {
                event.preventDefault();
                // Clear out the preview image
                <?php echo esc_js( $selector ); ?>Container.html( '' );
                // Un-hide the add image link
                add<?php echo esc_js( $selector ); ?>Link.removeClass( 'hidden' );
                // Hide the delete image link
                del<?php echo esc_js( $selector ); ?>Link.addClass( 'hidden' );
                // Delete the image id from the hidden input
                <?php echo esc_js( $selector ); ?>IdInput.val( '' );
            });
        });
    </script>
    <?php
}
function wpoc_get_post_term( $tax, $post_id ) {
    $args = array(
        'object_ids' => $post_id,
        'orderby' => 'wpoc_' . $post_id . '_order',
        'order' => 'asc',
        'meta_key' => 'wpoc_' . $post_id . '_order',
    );
    $data = get_terms( $tax, $args );
    return $data;
}
function wpoc_term_thumbnail() {
    if ( is_tax() ) {
        global $term;
        global $taxonomy;
        $term_id = get_term_by( 'slug', $term, $taxonomy )->term_id;
        $id = get_term_meta( $term_id, 'wpoc_thumbnail_id', true );
        $src = get_term_meta( $term_id, 'wpoc_tax_img', true );
        if ( $id ) {
            $img = '<img src="' . esc_url( wp_get_attachment_image_url( $id, 'full' ) ) . '" width="300px"/>';
        } elseif ( $src ) {
            $img = '<img src="https://image.tmdb.org/t/p/original' . esc_attr( $src ) . '" width="300px"/>';
        } else {
            $img = '';
        }
        echo $img;
    }
}