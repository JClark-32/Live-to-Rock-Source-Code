<?php
    function delete_video_id(){
        global $wpdb;
        $delBtnIsPosting = isset($_POST['ltr-delBtn']);
        $isRequesting = $_SERVER['REQUEST_METHOD'] === 'POST';
        ob_start();

        if ($isRequesting && $delBtnIsPosting) {
            $videoIsPosting = isset($_POST['videoInput']);
            if ($videoIsPosting) {
                $video_id = sanitize_text_field($_POST['videoInput']);
                error_log("Deleting video ID: " . $video_id);

                $table_name = $wpdb->prefix . 'video_submission';
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_name 
                    WHERE submission_text = %s",
                    $video_id
                ));
    
                // Check for SQL errors
                check_for_sql_err($video_id);
            } else {
                error_log("No videoInput received in the request.");
            }
            wp_redirect(add_query_arg('message', 'video_deleted', wp_get_referer()));
            
            if (!defined('PHPUNIT_RUNNING')) {
                exit; // should not affect code; prevents tests from exiting
            }
        }
        ob_get_clean();
    }
?>