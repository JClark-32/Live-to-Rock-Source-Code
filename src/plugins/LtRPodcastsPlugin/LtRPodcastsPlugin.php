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
        //add_action('init', array( $this,'createAdminMenu') );
        add_shortcode('ltr-podcasts', array ( $this, 'podcast_shortcode') );
    }

    
    function wporg_options_page_html() {
        ?>
        <div class="wrap">
          <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
          <form action="options.php" method="post">
            <textarea>Enter Playlist Url</textarea>
            <button style="submit">Change</button>
          </form>
        </div>
        <?php
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
        ob_start();
        ?>
        <?php
        return ob_get_clean();
    }

}
new LtRPodcasts();