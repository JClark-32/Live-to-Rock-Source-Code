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
    }

    public function show_blogs(){
    ob_start();
    ?>
    
    <?php
    return ob_get_clean();
    }
}
 new JamSession();