<?php
class WPOC_Related_Posts_Widget extends WP_Widget {
	/**
	 * Declares the Genres widget class.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'wpoc_related_widget',
			'description'                 => esc_html__( 'Sidebar Widget for Related Posts', 'oc' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'wpoc_related_widget', esc_html__( 'Related Widget', 'oc' ), $widget_ops );
	}

	/**
	 * Displays the Widget
	 */
	public function widget( $args, $instance ) {
	   global $post;
       $id  = $post->ID;
       
	   if ( is_singular() ) {
    		$title = empty( $instance['title'] ) ? '' : $instance['title'];
            $count = $instance['count'];
            $atts = array(
                'div' => '',
                'div2' => '',
                'a' => '',
            );
            $atts = apply_filters( 'wpoc_related_widget_atts', $atts );
            $t = get_the_terms( $post->ID,'genres' );
            if ( empty( $t ) ) return;
            $tax_query['relation'] = 'OR';
            foreach ( $t as $k ) {
                $tax_query[] = array(
                    'taxonomy' => 'genres',
                    'field' => 'slug',
                    'terms' => $k->slug,
                );
            }
            $p = new WP_Query( array(
                'post_type' => array( 'tvshows', 'movies' ),
                'posts_per_page' => $count,
                'tax_query' => $tax_query,
            ) );

    		echo $args['before_widget'];
    		if ( ! empty( $title ) ) {
    			echo $args['before_title'] . esc_attr( $title ) . $args['after_title'];
    		}
            ?>
            <div  <?php echo $atts['div']; ?>>
                <?php
                while ( $p->have_posts() ) {
                    $p->the_post();
                    if ( get_the_ID() === $id ) continue;
                    ?>
                    <div <?php echo $atts['div2']; ?>>
                        <a href="<?php the_permalink(); ?>"><?php lumy_post_thumbnail() . the_title( '<p>', '</p>' ); ?></a>
                    </div>
                    <?php
                    }
            echo '</div>';
    		echo $args['after_widget'];
        }
	}

	/**
	 * Saves the widgets settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags( stripslashes( $new_instance['title'] ) );
		$instance['count'] = wp_strip_all_tags( stripslashes( $new_instance['count'] ) );

		return $instance;
	}

	/**
	 * Creates the edit form for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
            'title' => '',
            'count' => '',
        ) );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'oc' ); ?></label><input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php echo __( 'Count', 'oc' ); ?></label><input type="number" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>"/></p>
        <?php
  }
}
