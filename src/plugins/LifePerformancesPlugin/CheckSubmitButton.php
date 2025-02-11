<?php
function check_for_post($submitIsPosting, $video_url) {
    if ($submitIsPosting) {
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $video_url, $matches);
        return $matches[1];
    } else {
        error_log("Submit button is not posting");
    }
}
?>