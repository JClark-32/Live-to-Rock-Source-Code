<?php
function approve_video($video_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'video_submission';
    
    // Check if the video exists and update the approval status to 1 (approved)
    $wpdb->update(
        $table_name,
        array('approved' => 1), // Set approved to 1
        array('submission_text' => $video_id),
        array('%d'),
        array('%s')
    );
    
    // Redirect to a page after approval, e.g., back to the videos page
    wp_redirect(get_site_url() . '/life-performances'); // Update this URL as necessary
    exit;
}
?>