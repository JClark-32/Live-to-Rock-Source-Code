<?php
/**
 * Plugin Name: Life Performances Plugin
 * Description: Adds YT iframe embeds from user submissions on Life Performances page
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: life-performances-plugin
 */

// Prevent public user access to .php file
if ( !defined('ABSPATH') ) { exit; }

class LifePerformances {

    // PROPERTIES
    private $email_status = ''; // To store email sending status

    // METHODS
    public function __construct() {

        require_once 'includes/InsertVideoIDData.php';
        require_once 'includes/CheckSubmitButton.php';
        require_once 'includes/EnterVideoData.php';
        require_once 'includes/CheckSQLError.php';
        require_once 'includes/SendEmailToAdmin.php';
        require_once 'includes/DeleteVideo.php';
        require_once 'includes/ApproveVideo.php';
        require_once 'includes/SubmissionFields.php';
        require_once 'includes/GetVideoID.php';
        require_once 'includes/DeleteVideoFromDB.php';
        require_once 'includes/HandleVideosFromEmail.php';
        require_once 'includes/CreateVideosSpace.php';
        require_once 'includes/ApproveAddToDB.php';
        require_once 'includes/PrepareVideosToShow.php';

        // Load assets ----------
        add_action('wp_enqueue_scripts', array( $this, 'load_assets' ) );

        // SHORTCODES ----------
        add_shortcode('ltr-video-submission', array ( $this, 'blank_shortcode') );
        add_action('init', array( $this, 'video_id' ) );
        add_shortcode('ltr-delete-video', array( $this, 'blank_shortcode' ) );
        add_shortcode('ltr-videos', array ( $this, 'show_videos' ) );
        add_shortcode('ltr-email-status', array( $this, 'email_status_shortcode' ) ); // New shortcode to display email status

        add_action('plugins_loaded', array( $this,'wporg_add_video_submission_ability') );

        // Handle approve and delete actions
        add_action('init', array( $this, 'handle_video_actions') );
    }

    // Loads assets
    public function load_assets() {
        wp_enqueue_style(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/css/LifePerformancesPlugin.css',
            [],
            1,
            'all'
        );
    }

    // Loads a blank section that users who do not have the permission to post videos see
    public function blank_shortcode(){
        ob_start();
        ob_get_clean();
    }

    // Loads a submission form that a user can paste a YT url, which will be stored in database
    public function load_video_submission() {
        ob_start();
        submission_fields();
        return ob_get_clean();
    }

    // Changes the shortcode to show the video submission if the user can edit posts, i.e., Editor and above
    function wporg_add_video_submission_ability() {
        if ( current_user_can('edit_others_posts')){
            remove_shortcode('ltr-delete-video');
            add_shortcode('ltr-delete-video', array($this, 'delete_videos'));
        }
        if ( current_user_can('read')){
            remove_shortcode('ltr-video-submission');
            add_shortcode('ltr-video-submission', array( $this,'load_video_submission') );
        }

        if ( current_user_can('read')) {
            remove_shortcode('ltr-delete-video');
            add_shortcode('ltr-video-submission', array( $this,'load_video_submission') );
        }
    }

    public function video_id() {
        get_video_id();
    }

    public function delete_videos() {
        delete_video_id();
    }

    public function show_videos() {
        prepare_to_show();
        return ob_get_clean();
    }

    public function handle_video_actions() {
        handle_videos_from_email();
    }
    
    // Display email status
    public function email_status_shortcode() {
        return '<div class="email-status">' . esc_html($this->email_status) . '</div>';
    }

}

new LifePerformances();