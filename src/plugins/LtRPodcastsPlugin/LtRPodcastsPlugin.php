<?php
/**
 * Plugin Name: LtR Podcasts Plugin
 * Description: Adds podcast playlist integration
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: ltr-podcast-plugin
 */

// Prevent public user access to .php file
if ( !defined('ABSPATH') ) { exit; }

Class LtRPodcasts{
    public function __construct(){
        add_action( 'admin_menu', array($this, 'wporg_options_page'));
        add_action('init', array( $this,'podcast_id') );
        add_action('admin_post_save_ltr_playlist', array($this, 'save_playlist_url'));
        add_shortcode('ltr-podcasts', array ( $this, 'podcast_shortcode') );
    }

    
    function wporg_options_page_html() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                <textarea name="ltr-playlist-url" rows="5" cols="40"><?php echo esc_textarea(get_option('ltr_playlist_url', '')); ?></textarea>
                <input type="hidden" name="action" value="save_ltr_playlist">
                <?php submit_button('Save Playlist URL'); ?>
            </form>
        </div>
        <?php
    }

    public function save_playlist_url() {
        if (current_user_can('manage_options') && isset($_POST['ltr-playlist-url'])) {
            $playlist = sanitize_text_field($_POST['ltr-playlist-url']);
            update_option('ltr_playlist_url', $playlist);
        }
    
        // Redirect back to the settings page
        wp_redirect(admin_url('admin.php?page=ltr_podcasts&updated=true'));
        exit;
    }
    
    public function podcast_id() {
        if (isset($_POST['ltr-playlist-url'])) {
            // Sanitize and save the value
            $playlist = sanitize_text_field($_POST['ltr-playlist-url']);
            update_option('ltr_playlist_url', $playlist);
        }
    }
    
    
    function wporg_options_page() {
        add_menu_page(
            'LtR Podcasts',
            'LtR Podcasts',
            'manage_options',
            'ltr_podcasts',
            array($this, 'wporg_options_page_html'),
            'dashicons-microphone',
            null
        );
    }

    public function podcast_shortcode() {
        $playlist = get_option('ltr_playlist_url', '');
        ob_start();
        ?>
        <p>
            <?php echo esc_html($playlist); ?>
        </p>
        <?php
        return ob_get_clean();
    }

}
new LtRPodcasts();