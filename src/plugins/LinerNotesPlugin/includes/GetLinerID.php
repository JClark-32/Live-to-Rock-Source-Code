<?php
    function get_liner_id(){
        global $wpdb;
        
        $urlIsPosting = isset($_POST['ltr-liner-url']);
        $isRequesting = $_SERVER['REQUEST_METHOD'] === 'POST';
        
        if ($isRequesting && $urlIsPosting) {
            $submitIsPosting = isset($_POST['ltr-submit-liner-button']);
            if ($submitIsPosting) {
                $liner_url = sanitize_text_field($_POST['ltr-liner-url']);
            } else {
                error_log("Submit button is not posting");
            }
        

            send_liner_submission_email($liner_url); // Send email
            
        
            wp_redirect($_SERVER['REQUEST_URI']);
            // for test functionality
            if (!defined('PHPUNIT_RUNNING')) {
                exit;
            }
        }
    }
?>