<?php
function send_event_submission_email($event_url, $event_start_time, $event_end_time, $event_date, $event_title, $event_details) {
    $admins = get_users( array( 'role' => 'administrator' ) );
    

    
    foreach ( $admins as $user ) {
        $to = $user->user_email;
        $subject = 'New Event Submitted for Back Stage Pass';

        $message = '
            <h2>New Event Submission</h2>
            <p><strong>Event Title:</strong> ' . esc_html($event_title) . '</p>
            <p><strong>Date:</strong> ' . esc_html($event_date) . '</p>
            <p><strong>Start Time:</strong> ' . esc_html((new DateTime($event_start_time))->format('g:i A')) . '</p>
            <p><strong>End Time:</strong> ' . esc_html((new DateTime($event_end_time))->format('g:i A')) . '</p>
            <p><strong>Event URL:</strong> <a href="' . esc_url($event_url) . '">' . esc_html($event_url) . '</a></p>
            <p><strong>Details:</strong><br>' . nl2br(esc_html($event_details)) . '</p>
        ';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $mail_sent = wp_mail($to, $subject, $message, $headers);
    }

    if ($mail_sent) {
        $email_status = 'Email sent successfully!';
    } else {
        $email_status = 'Failed to send email.';
    }
}