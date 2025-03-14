<?php
function insert_data($table_name, $video_id) {
    global $wpdb;

    $wpdb->insert($table_name, array('submission_text' => $video_id), NULL);
}
?>