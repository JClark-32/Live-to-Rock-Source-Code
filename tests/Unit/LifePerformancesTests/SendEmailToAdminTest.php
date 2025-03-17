<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('get_users')) {
    function get_users($args = []) {
        return $GLOBALS['test_admin_users'] ?? [(object)['user_email' => 'admin@example.com']];
    }
}

if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers) {
        $GLOBALS['wp_mail_params'][] = compact('to', 'subject', 'message', 'headers');
        return $GLOBALS['wp_mail_return'];
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value, $url) {
        return $url . '?' . $key . '=' . $value;
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value, $url) {
        return $url . '?' . $key . '=' . $value;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return $url;
    }
}

if (!function_exists('get_site_url')) {
    function get_site_url() {
        return 'http://example.com';
    }
}

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/SendEmailToAdmin.php';

class SendEmailToAdminTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['wp_mail_params'] = [];
        $GLOBALS['wp_mail_return'] = true;  // success by default
        unset($GLOBALS['test_admin_users']);
    }

    public function testEmailSentSuccessfullyToSingleAdmin() {
        // returns one admin with email admin@example.com
        send_video_submission_email('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'); // rick roll ;)

        // Assert wp_mail() was called exactly once.
        $this->assertCount(1, $GLOBALS['wp_mail_params']);

        $mail = $GLOBALS['wp_mail_params'][0];
        $this->assertEquals('admin@example.com', $mail['to']);
        $this->assertEquals('New Video Submitted for Life Performances', $mail['subject']);
        $this->assertStringContainsString('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $mail['message']);
        // Verify that the approve and delete links contain the query arguments.
        $this->assertStringContainsString('approve_video=dQw4w9WgXcQ', $mail['message']);
        $this->assertStringContainsString('delete_video=dQw4w9WgXcQ', $mail['message']);
        // Check that headers include the content type.
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $mail['headers']);
    }

    public function testEmailFailure() {
        // Simulate a failure by having wp_mail() return false.
        $GLOBALS['wp_mail_return'] = false;
        send_video_submission_email('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ');

        // wp_mail() should still be called.
        $this->assertNotEmpty($GLOBALS['wp_mail_params']);
        $mail = $GLOBALS['wp_mail_params'][0];
        $this->assertEquals('admin@example.com', $mail['to']);
        // (Since the function doesn't return a status, we only check that wp_mail() was invoked correctly.)
    }

    public function testEmailSentToMultipleAdmins() {
        // Override get_users to return two admin users.
        $GLOBALS['test_admin_users'] = [
            (object)['user_email' => 'admin1@example.com'],
            (object)['user_email' => 'admin2@example.com']
        ];

        send_video_submission_email('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ');

        // Assert that wp_mail() was called twice.
        $this->assertCount(2, $GLOBALS['wp_mail_params']);

        $mail1 = $GLOBALS['wp_mail_params'][0];
        $mail2 = $GLOBALS['wp_mail_params'][1];
        $this->assertEquals('admin1@example.com', $mail1['to']);
        $this->assertEquals('admin2@example.com', $mail2['to']);
    }
}