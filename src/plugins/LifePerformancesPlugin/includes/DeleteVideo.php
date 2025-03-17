<?php
function delete_video($video_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'video_submission';
    
    // Check if the video exists and delete it
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name WHERE submission_text = %s",
        $video_id
    ));

    // Redirect to a page after deletion, e.g., back to the videos page
    wp_redirect(get_site_url() . '/life-performances'); // Update this URL as necessary
  
    if (!defined('PHPUNIT_RUNNING')) {
        exit; // should not affect code; prevents tests from exiting
    }
}
?>