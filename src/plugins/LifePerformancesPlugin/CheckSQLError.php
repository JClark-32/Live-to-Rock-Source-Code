<?php
function check_for_sql_err($video_id) {
    global $wpdb;

    // Check for any SQL errors
    if ($wpdb->last_error) {
        error_log("Error deleting video: " . $wpdb->last_error);
    } else {
        error_log("Video successfully deleted with ID: " . $video_id);
    }
}
?>