<?php
    function create_video_space($video_data){
        echo '<div id="ltr-videos-here">';

        if (empty($video_data)) {
            echo '<p>No videos available</p>';
        }

        foreach ($video_data as $index => $video) {
            $video_id = $video->submission_text;
            $approved = $video->approved;
            echo "<form id='video-posted$index' method='POST'>";
            echo "<input type='hidden' name='videoInput' value='$video_id'>";
            wp_nonce_field('approve_video_nonce', 'approve_video_nonce');
            echo "<div id='player$index' class='youtube-player' loading='lazy'></div>";
            echo "<br>";
            if (!$approved && !current_user_can('edit_others_posts')) {
                echo "<style>#video-posted$index { display: none; }</style>";
            }
            if (!current_user_can('edit_others_posts')) {
                echo "<style>#deleteButton$index { display: none; }</style>";
            }
            if (!$approved && current_user_can('edit_others_posts')) {
                echo "<button id='approveButton$index' type='submit' name='ltr-approveBtn' class='approveBtn'>Approve</button>";
            }
            echo "<button id='deleteButton$index' type='submit' name='ltr-delBtn' class='deleteBtn'>Delete?</button>";
            echo "<br>";
            echo "</form>";
        }

        echo '</div>';

        echo "<script> var videoIds = " . json_encode($video_data) . "; </script>";
    }
?>