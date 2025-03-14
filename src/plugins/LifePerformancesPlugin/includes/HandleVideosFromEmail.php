<?php
    function handle_videos_from_email(){
        if (isset($_GET['approve_video']) && !isset($_GET['delete_video'])) {
            // Approve video if 'approve_video' query parameter is set, and 'delete_video' is not present
            $video_id = sanitize_text_field($_GET['approve_video']);
            approve_video($video_id);
        } elseif (isset($_GET['delete_video']) && !isset($_GET['approve_video'])) {
            // Delete video if 'delete_video' query parameter is set, and 'approve_video' is not present
            $video_id = sanitize_text_field($_GET['delete_video']);
            delete_video($video_id);
        }
    }
?>