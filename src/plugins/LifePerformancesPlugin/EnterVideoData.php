<?php
function enter_data_if_able($submitIsPosting, $video_id) {
    require_once 'InsertVideoIDData.php';

    global $wpdb;

    $table_name = $wpdb->prefix . 'video_submission';

    if ($submitIsPosting) {
        // Data is inserted into the created or existing table
        insert_data($table_name, $video_id);
    }
    if ($wpdb->last_error) {
        error_log("Error creating table: " . $wpdb->last_error . "Contact admin.");
    }
}
?>