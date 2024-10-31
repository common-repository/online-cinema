<?php
class WPOC_Term_List_Widget extends WP_Widget {
	/**
	 * Declares the Genres widget class.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'wpoc_taxonomy_widget',
			'description'                 => esc_html__( 'Sidebar Widget for Taxonomy', 'oc' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'wpoc_taxonomy_widget', esc_html__( 'Taxonomy Widget', 'oc' ), $widget_ops );
	}

	/**
	 * Displays the Widget
	 */
	public function widget( $args, $instance ) {
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
        $taxonomy = $instance['taxonomy'];
        $atts = array(
            'ul' => '',
            'li' => '',
            'a' => '',
        );
        $atts = apply_filters( 'wpoc_tax_widget_atts', $atts );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_attr( $title ) . $args['after_title'];
		}
        echo '<ul ' . $atts['ul'] . '>';
        $terms = get_terms( $taxonomy );
        $i = 0;
        $c = count( $terms );
        while ( $i < $c ) {
            echo '<li ' . $atts['li'] . '><a href="' . esc_url( get_term_link( $terms[ $i ]->term_id, $terms[ $i ]->taxonomy ) ) . '" ' . $atts['a'] . '>' . esc_html( $terms[ $i ]->name ) . '</a></li>';
            $i++;
        }
        echo '</ul>';
		echo $args['after_widget'];
	}

	/**
	 * Saves the widgets settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags( stripslashes( $new_instance['title'] ) );
		$instance['taxonomy'] = wp_strip_all_tags( stripslashes( $new_instance['taxonomy'] ) );

		return $instance;
	}

	/**
	 * Creates the edit form for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
            'title' => '',
            'taxonomy' => '',
        ) );
        $taxonomies = get_taxonomies( array( 'public' => true ) );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'oc' ); ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php echo __( 'Taxonomy', 'oc' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
            <?php foreach ( $taxonomies as $key => $value ) { ?>
                <option value="<?php echo esc_attr( $key ) ?>" <?php echo ( esc_attr( $key ) === $instance['taxonomy'] ) ? ' selected' : '';  ?>><?php echo esc_attr( $value ); ?></option>
            <?php } ?>
        </select></p>
        <?php
  }
}
