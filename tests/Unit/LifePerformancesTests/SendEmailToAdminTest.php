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
        send_video_submission_email('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'); // rick roll ;)

        $this->assertCount(1, $GLOBALS['wp_mail_params']);

        $mail = $GLOBALS['wp_mail_params'][0];
        $this->assertEquals('admin@example.com', $mail['to']);
        $this->assertEquals('New Video Submitted for Life Performances', $mail['subject']);
        $this->assertStringContainsString('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $mail['message']);

        $this->assertStringContainsString('approve_video=dQw4w9WgXcQ', $mail['message']);
        $this->assertStringContainsString('delete_video=dQw4w9WgXcQ', $mail['message']);

        $this->assertContains('Content-Type: text/html; charset=UTF-8', $mail['headers']);
    }

    public function testEmailFailure() {
        $GLOBALS['wp_mail_return'] = false;
        send_video_submission_email('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ');

        $this->assertNotEmpty($GLOBALS['wp_mail_params']);
        $mail = $GLOBALS['wp_mail_params'][0];
        $this->assertEquals('admin@example.com', $mail['to']);
    }

    public function testEmailSentToMultipleAdmins() {
        $GLOBALS['test_admin_users'] = [
            (object)['user_email' => 'admin1@example.com'],
            (object)['user_email' => 'admin2@example.com']
        ];

        send_video_submission_email('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ');

        $this->assertCount(2, $GLOBALS['wp_mail_params']);

        $mail1 = $GLOBALS['wp_mail_params'][0];
        $mail2 = $GLOBALS['wp_mail_params'][1];
        $this->assertEquals('admin1@example.com', $mail1['to']);
        $this->assertEquals('admin2@example.com', $mail2['to']);
    }
}