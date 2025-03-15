<?php
function send_video_submission_email($video_url, $video_id) {
    $admins = get_users( array( 'role' => 'administrator' ) );
    foreach ( $admins as $user ) {
        $to = $user->user_email;
        $subject = 'New Video Submitted for Life Performances';
        $approve_url = add_query_arg('approve_video', $video_id, get_site_url());
        $delete_url = add_query_arg('delete_video', $video_id, get_site_url());
        $message = 'A new video has been submitted for the Life Performances plugin. Here is the YouTube URL: ' . $video_url . '<br><br>';
        $message .= 'To approve the video, click <a href="' . esc_url($approve_url) . '">Approve</a><br>';
        $message .= 'To delete the video, click <a href="' . esc_url($delete_url) . '">Delete</a>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $mail_sent = wp_mail($to, $subject, $message, $headers);  
    }

    if ($mail_sent) {
        $email_status = 'Email sent successfully!';
    } else {
        $email_status = 'Failed to send email.';
    }
}