<?php
function liner_check_for_post($submitIsPosting, $liner_url) {
    if ($submitIsPosting) {
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $liner_url, $matches);
        return $matches[1];
    } else {
        error_log("Submit button is not posting");
    }
}
?>