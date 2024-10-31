<?php
class WPOC_Plugin_Settings {
    function __construct() {
        add_action( 'admin_menu', array( $this, 'wpoc_add_page_settings' ) );
    }
    function wpoc_add_page_settings() {
        add_options_page( __( 'Plugin Settings', 'oc' ), __( 'OC Settings', 'oc' ), 'manage_options', 'oc-settings', array( $this, 'wpoc_plugin_page' ) );
    }
    function wpoc_plugin_page() {
        ?>
        <div class="wrap">
            <h1><?php  echo __( 'Plugin Settings', 'oc' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'wpoc_settings', 'wpoc_settings' ); ?>
                <table class="form-table">
                    <tbody>
                        <?php $this->wpoc_api_key(); ?>
                    </tbody>
                </table>
                <?php submit_button( __( 'Save', 'oc' ) ); ?>
            </form>
        </div>
        <?php
        $this->wpoc_save_settings();
    }
    function wpoc_api_key() {
        $option = get_option( 'tmdb_api_key' );
        if ( isset( $option ) ) {
            $value = 'value="' . esc_attr( $option ) . '"';
        } else {
            $option = '';
        }
        ?>
        <tr>
            <th score="row"><label for="tmdb"><?php echo __( 'TMDb API key(<a href="https://www.themoviedb.org/">get it</a>)', 'oc' ); ?></label></th>
            <td><input type="text" name="tmdb_api_key" id="tmdb" <?php echo esc_attr( $value ); ?> class="regular-text"/></td>
        </tr>
        <?php
    }
    function wpoc_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['wpoc_settings'], 'wpoc_settings' ) ) {
            return;
        }
        if ( isset( $_POST['tmdb_api_key'] ) ) {
            $key = sanitize_text_field( $_POST['tmdb_api_key'] );
            update_option( 'tmdb_api_key', $key );
        }
    }
}
new WPOC_Plugin_Settings;