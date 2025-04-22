<?php
/**
 * Plugin Name: LinerNotes Plugin
 * Description: Adds user submission for requesting links.
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: liner-notes-plugin
 */

// Prevent public user access to .php file
if ( !defined('ABSPATH') ) { exit; }

class LinerNotes {

    // PROPERTIES
    private $email_status = ''; // To store email sending status

    // METHODS
    public function __construct() {


        require_once 'includes/CheckLinerSubmitButton.php';
        require_once 'includes/SendLinerEmailToAdmin.php';
        require_once 'includes/GetLinerID.php';
        require_once 'includes/LinerSubmissionFields.php';


        // SHORTCODES ----------
        add_shortcode('ltr-liner-submission', array ( $this, 'blank_shortcode') );

        add_action('init', array( $this, 'liner_id' ) );


        add_action('plugins_loaded', array( $this,'wporg_add_liner_submission_ability') );
        add_shortcode('ltr-email-status', array( $this, 'email_status_shortcode' ) ); // New shortcode to display email status


    }



    public function blank_shortcode(){
        ob_start();
        ob_get_clean();
    }

    // Loads a submission form that a user can paste a YT url, which will be stored in database
    public function load_liner_submission() {
        ob_start();
        liner_submission_fields();
        return ob_get_clean();
    }

    // Changes the shortcode to show the liner submission if the user can edit posts, i.e., Editor and above
    function wporg_add_liner_submission_ability() {
        if ( current_user_can('read')){
            remove_shortcode('ltr-liner-submission');
            add_shortcode('ltr-liner-submission', array( $this,'load_liner_submission') );
        }
    }
    
    public function liner_id() {
        get_liner_id();
    }

    // Display email status
    public function email_status_shortcode() {
        return '<div class="email-status">' . esc_html($this->email_status) . '</div>';
    }

}

new LinerNotes();