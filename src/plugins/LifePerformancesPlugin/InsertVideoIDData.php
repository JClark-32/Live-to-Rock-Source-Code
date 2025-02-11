<?php
function insert_data($video_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'video_submission';
    $wpdb->insert($table_name, array('submission_text' => $video_id), NULL);
}
?>