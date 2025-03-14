<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/SendEmailToAdmin.php';

Class SendEmailToAdminTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        // Mock WordPress functions
        if (!function_exists('add_query_arg')) {
            function add_query_arg($key, $value, $url) {
                return $url . '?' . $key . '=' . $value;
            }
        }

        if (!function_exists('get_site_url')) {
            function get_site_url() {
                return 'https://example.com';
            }
        }

        if (!function_exists('esc_url')) {
            function esc_url($url) {
                return $url;
            }
        }

        if (!function_exists('get_users')) {
            function get_users($args) {
                return [
                    (object)['user_email' => 'admin1@example.com'],
                    (object)['user_email' => 'admin2@example.com'],
                ];
            }
        }
    }

    public function testSendVideoSubmissionEmailCallsWpMail() {
        $video_url = 'https://youtube.com/watch?v=VsWPyq8KdGM';
        $video_id = 'VsWPyq8KdGM';

        // Mock wp_mail function
        $mockMailer = function ($to, $subject, $message, $headers) use ($video_url) {
            $this->assertContains($to, ['admin1@example.com', 'admin2@example.com']);
            $this->assertEquals('THIS IS A TEST APOLOGIZIES PLEASE IGNORE New Video Submitted for Life Performances', $subject);
            $this->assertStringContainsString($video_url, $message);
            $this->assertContains('Content-Type: text/html; charset=UTF-8', (array) $headers);
            return true; // Simulate successful email sending
        };

        $result = send_video_submission_email($video_url, $video_id, $mockMailer);

        $this->assertEquals('Email sent successfully!', $result);
    }

    public function testSendVideoSubmissionEmailHandlesFailedEmail() {
        $video_url = 'https://youtube.com/watch?v=VsWPyq8KdGM';
        $video_id = 'VsWPyq8KdGM';

        // Mock wp_mail returning false (email failed)
        $mockMailer = function () {
            return false;
        };

        $result = send_video_submission_email($video_url, $video_id, $mockMailer);

        $this->assertEquals('Failed to send email.', $result);
    }
}