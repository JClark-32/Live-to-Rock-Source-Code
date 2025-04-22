<?php
    function get_event_id(){
        global $wpdb;
        
        $urlIsPosting = isset($_POST['ltr-event-url']);
        $isRequesting = $_SERVER['REQUEST_METHOD'] === 'POST';
        
        if ($isRequesting && $urlIsPosting) {
            $submitIsPosting = isset($_POST['ltr-submit-event-button']);
            if ($submitIsPosting) {
                $event_url = sanitize_text_field($_POST['ltr-event-url']);
                $event_start_time = sanitize_text_field($_POST['ltr-event-start']);
                $event_end_time = sanitize_text_field($_POST['ltr-event-end']);
                $event_date = sanitize_text_field($_POST['ltr-event-date']);
                $event_title = sanitize_text_field($_POST['ltr-event-title']);
                $event_details = sanitize_text_field($_POST['ltr-event-details']);
            } else {
                error_log("Submit button is not posting");
            }
        

            send_event_submission_email($event_url, $event_start_time, $event_end_time, $event_date, $event_title, $event_details); // Send email
            
        
            wp_redirect($_SERVER['REQUEST_URI']);
            // for test functionality
            if (!defined('PHPUNIT_RUNNING')) {
                exit;
            }
        }
    }
?>