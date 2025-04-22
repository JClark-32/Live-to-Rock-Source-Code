<?php
function schedule_check_for_post() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ltr-submit-event-button'])) {
        $event_url     = esc_url_raw($_POST['ltr-event-url']);
        $event_title   = sanitize_text_field($_POST['ltr-event-title']);
        $event_date    = sanitize_text_field($_POST['ltr-event-date']);
        $event_start   = sanitize_text_field($_POST['ltr-event-start']);
        $event_end     = sanitize_text_field($_POST['ltr-event-end']);
        $event_details = sanitize_textarea_field($_POST['ltr-event-details']);

        send_event_submission_email(
            $event_url,
            $event_start,
            $event_end,
            $event_date,
            $event_title,
            $event_details
        );

        wp_redirect(add_query_arg('submitted', 'true', $_SERVER['REQUEST_URI']));
        exit;
    }
}
add_action('init', 'schedule_check_for_post');