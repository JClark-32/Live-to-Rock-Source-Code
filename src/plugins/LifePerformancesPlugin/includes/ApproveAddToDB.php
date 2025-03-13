<?php
    function approve_to_db($table_name){
        global $wpdb;

        if (isset($_POST['ltr-approveBtn'])) {
            $video_id = sanitize_text_field($_POST['videoInput']);
            if ($video_id) {
                $wpdb->update(
                    $table_name,
                    array('approved' => 1),
                    array('submission_text' => $video_id),
                    array('%d'),
                    array('%s')
                );
                error_log("Video approved with ID: " . $video_id);
            }
        }
    }
?>