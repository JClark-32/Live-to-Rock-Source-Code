<?php
    function get_video_id(){
        global $wpdb;
        $urlIsPosting = isset($_POST['ltr-video-url']);
        $isRequesting = $_SERVER['REQUEST_METHOD'] === 'POST';
        
        if ($isRequesting && $urlIsPosting) {
            $submitIsPosting = isset($_POST['ltr-submit-video-button']);
            if ($submitIsPosting) {
                $video_url = sanitize_text_field($_POST['ltr-video-url']);
            } else {
                error_log("Submit button is not posting");
            }
        
            // Get video ID from YouTube URL using regular expression
            $video_id = check_for_post($submitIsPosting, $video_url); 

            if ($video_id) {
                $table_name = $wpdb->prefix . 'video_submission';
                $sql = "CREATE TABLE IF NOT EXISTS $table_name (\n"
                    . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
                    . "    submission_text VARCHAR(11) NOT NULL,\n"
                    . "    approved TINYINT(1) DEFAULT 0,\n"
                    . "    PRIMARY KEY(id)\n"
                    . ");";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);

                $column_check = $wpdb->get_results("DESCRIBE $table_name approved");
                if (empty($column_check)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN approved TINYINT(1) DEFAULT 0");
                }

                if ($wpdb->last_error) {
                    error_log("Error creating or altering table: " . $wpdb->last_error . " Contact admin.");
                }

                // Insert data if able
                enter_data_if_able($submitIsPosting, $video_id);

                // Send email when a new video is submitted
                send_video_submission_email($video_url, $video_id); // Send email
            } else {
                error_log("Error: no video ID.");
            }
        
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }
?>