<?php
function send_blog_submission_email($blog_title, $blog_url, $blog_author, $blog_text) {
    $admins = get_users( array( 'role' => 'administrator' ) );

    foreach ( $admins as $user ) {
        $to = $user->user_email;
        $subject = 'New Blog Request Submitted';

        $message = '<h2>New Blog Submission</h2>';

        if (!empty($blog_title)) {
            $message .= '<p><strong>Title:</strong> ' . esc_html($blog_title) . '</p>';
        }

        if (!empty($blog_url)) {
            $message .= '<p><strong>URL:</strong> <a href="' . esc_url($blog_url) . '">' . esc_html($blog_url) . '</a></p>';
        }

        if (!empty($blog_author)) {
            $message .= '<p><strong>Author:</strong> ' . esc_html($blog_author) . '</p>';
        }

        if (!empty($blog_text)) {
            $upload_dir = wp_upload_dir();
            $filename = 'blog-submission-' . time() . '.txt';
            $filepath = $upload_dir['path'] . '/' . $filename;
            $fileurl  = $upload_dir['url'] . '/' . $filename;

            file_put_contents($filepath, $blog_text);

            $message .= '<p><strong>Content:</strong> <a href="' . esc_url($fileurl) . '" target="_blank">View Submitted Blog</a></p>';
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $mail_sent = wp_mail($to, $subject, $message, $headers);
    }

    // Optional: return success/failure status
    if ($mail_sent) {
        $email_status = 'Email sent successfully!';
    } else {
        $email_status = 'Failed to send email.';
    }
}
?>