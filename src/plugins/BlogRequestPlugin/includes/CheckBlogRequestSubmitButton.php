<?php
function schedule_blog_check_for_post() {
    if (isset($_POST['ltr-submit-blog-button'])) {
        $blog_title = sanitize_text_field($_POST['ltr-blog-title']);
        $blog_url = sanitize_text_field($_POST['ltr-blog-url']);
        $blog_author = sanitize_text_field($_POST['ltr-blog-author']);
        $blog_text = sanitize_textarea_field($_POST['ltr-blog-text']);
        
        send_blog_submission_email($blog_title, $blog_url, $blog_author, $blog_text);

        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
}
add_action('init', 'schedule_blog_check_for_post');
