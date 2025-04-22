<?php
function send_liner_submission_email($liner_url) {
    $admins = get_users( array( 'role' => 'administrator' ) );
    foreach ( $admins as $user ) {
        $to = $user->user_email;
        $subject = 'New URL Submitted for Liner Notes';

        $message = 'A new URL has been submitted for the Liner Notes plugin. Here is the URL: ' . $liner_url . '<br><br>';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $mail_sent = wp_mail($to, $subject, $message, $headers);  
    }

    if ($mail_sent) {
        $email_status = 'Email sent successfully!';
    } else {
        $email_status = 'Failed to send email.';
    }
}