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

    }


    public function load_assets(){
        wp_enqueue_style(
            'LifePerformancesPlugin', 
            plugins_dir_url( __FILE__ ) . '/css/LifePerformancesPlugin.css',
            array(),
            1 ,
            'all'
        );
    }

}

new LifePerformances();