<?php
/**
 * Plugin Name: Jam Session Plugin
 * Description: Adds blog posts
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: jam-session-plugin
 */

 // Prevent public user access to .php file
 if ( !defined('ABSPATH') ) { exit; }

 Class JamSession{
    public function __construct(){
        add_shortcode('ltr-blog', array( $this,'show_blogs') );
        #add_action('init', array( $this,'blog_id') );
    }

    public function show_blogs(){
    ob_start();
    ?>
        <div>
            <h2>test</h2>
        </div>
    <?php
    return ob_get_clean();
    }

    /*
    public function blog_id(){
        global $wpdb;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[''])){
        }
    }
    */
}
 new JamSession();