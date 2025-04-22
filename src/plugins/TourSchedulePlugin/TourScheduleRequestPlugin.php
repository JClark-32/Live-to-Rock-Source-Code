<?php
/**
 * Plugin Name: Tour Schedule Request Plugin
 * Description: Adds user submission for requesting events.
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: tour-schedule-request-plugin
 */

// Prevent public user access to .php file
if ( !defined('ABSPATH') ) { exit; }

class TourScheduleRequest {

    // PROPERTIES
    private $email_status = ''; // To store email sending status

    // METHODS
    public function __construct() {


        require_once 'includes/CheckTourScheduleSubmitButton.php';
        require_once 'includes/SendTourScheduleEmailToAdmin.php';
        require_once 'includes/GetTourScheduleID.php';
        require_once 'includes/TourScheduleSubmissionFields.php';


        // SHORTCODES ----------
        add_shortcode('ltr-event-submission', array ( $this, 'blank_shortcode') );

        add_action('init', array( $this, 'event_id' ) );


        add_action('plugins_loaded', array( $this,'wporg_add_event_submission_ability') );
        add_shortcode('ltr-email-status', array( $this, 'email_status_shortcode' ) ); // New shortcode to display email status


    }



    public function blank_shortcode(){
        ob_start();
        ob_get_clean();
    }

    // Loads a submission form that a user can paste a YT url, which will be stored in database
    public function load_event_submission() {
        ob_start();
        event_submission_fields();
        return ob_get_clean();
    }

    // Changes the shortcode to show the event submission if the user can edit posts, i.e., Editor and above
    function wporg_add_event_submission_ability() {
        if ( current_user_can('edit_others_posts')){
            remove_shortcode('ltr-event-submission');
            add_shortcode('ltr-event-submission', array( $this,'load_event_submission') );
        }
    }
    
    public function event_id() {
        get_event_id();
    }

    // Display email status
    public function email_status_shortcode() {
        return '<div class="email-status">' . esc_html($this->email_status) . '</div>';
    }

}

new TourScheduleRequest();