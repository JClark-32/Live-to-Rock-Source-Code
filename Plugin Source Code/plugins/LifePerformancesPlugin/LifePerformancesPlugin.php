<?php
/** 
 * Plugin Name: Life Performances Plugin
 * Description: Plugin for the LtR Life Performances Page
 * Authors:
 * Version: 0.1.0
 * Text Domain: life-performances-plugin
*/
if ( !defined('ABSPATH') ) 
{
    exit;
}

class LifePerformances{

    public function __construct(){

        // Load assets
        add_action('wp_enqueue_scripts', array( $this,'load_assets') );

        // Add shortcode
        add_shortcode('life-performance', array( $this,'load_shortcode') );

    }


    public function load_assets(){
        wp_enqueue_style(
            'LifePerformancesPlugin', 
            plugin_dir_url( __FILE__ ) . '/css/LifePerformancesPlugin.css',
            array(),
            1 ,
            'all'
        );

        wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', array(), null, true);

        wp_enqueue_script('bootstrap-min-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array(), null, true);

        wp_enqueue_script(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/js/LifePerformancesPlugin.js',
            array('jquery'),
            1 ,
            true
        );
    }

    public function load_shortcode()
    {?>
        <div id = "video-group"class = "form-group">
            <div>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/j_S0upmiG7Q?si=ayY4EpAj1hDs7z6v" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </div>
    <?php }
}

new LifePerformances();