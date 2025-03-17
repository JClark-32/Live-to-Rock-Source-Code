<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/GetVideoID.php';

final class GetVideoIDTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();

        // Define constant to prevent exit during testing.
        if (!defined('PHPUNIT_RUNNING')) {
            define('PHPUNIT_RUNNING', true);
        }

        // Set up the environment for a POST request.
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = 'http://example.com/test';  // Set to any valid URL

        // Set up a temporary error log file to capture error_log output.
        $this->logFile = tempnam(sys_get_temp_dir(), 'log');
        ini_set('error_log', $this->logFile);

        // mock $wpdb
        global $wpdb;
        $wpdb = $this->getMockBuilder('stdClass')
            ->addMethods(['get_results', 'query', 'last_error'])
            ->getMock();

            $wpdb->prefix = 'wp_';
            $wpdb->last_error = '';
        
        // mock dbDelta function
        if (!function_exists('dbDelta')) {
            function dbDelta($sql) {
                // Simulate dbDelta behavior
            }
        }

        // mock sanitize_text_field function
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field($data) {
                return $data;
            }
        }

        // mock error_log function
        if (!function_exists('error_log')) {
            function error_log($message) {
                // Simulated error log
            }
        }

        // mock wp_redirect function
        if (!function_exists('wp_redirect')) {
            function wp_redirect($url) {
                // Simulate wp_redirect
            }
        }

        // mock enter_data_if_able function
        if (!function_exists('enter_data_if_able')) {
            function enter_data_if_able($submit, $video_id) {
                // Simulate the data entry
            }
        }

        // mock send_video_submission_email function
        if (!function_exists('send_video_submission_email')) {
            function send_video_submission_email($video_url, $video_id) {
                // Simulate sending an email
            }
        }

        // mock check_for_post function
        if (!function_exists('check_for_post')) {
            function check_for_post($submitIsPosting, $video_url) {
                return preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/)([A-Za-z0-9_-]{11}))/', $video_url, $matches)
                    ? $matches[1] : null;
            }
        }

        // mock get_users function
        if (!function_exists('get_users')) {
            function get_users($args = []) {
            return [
                (object) ['user_email' => 'admin@example.com'],
                (object) ['user_email' => 'another.admin@example.com']
            ];
            }
        }
}
    
    // public function testGetVideoIdHandlesValidSubmission()

    public function testGetVideoIdHandlesInvalidUrl() {
        $_POST['ltr-video-url'] = 'https://www.invalid-url.com/';
        $_POST['ltr-submit-video-button'] = true;

        $this->expectOutputString('');
        error_log('Error: no video ID.');

        get_video_id();

        $this->assertTrue(true);
    }

    public function testGetVideoIdWhenNoPostData() {
        // this should do nothing
        $_POST = [];

        // no actual output
        $this->expectOutputString('');

        get_video_id();

        $this->assertTrue(true);
    }

    public function testGetVideoIdHandlesTableError() {
        $_POST['ltr-video-url'] = 'https://www.youtube.com/watch?v=TRg94OCjx3Y';
        $_POST['ltr-submit-video-button'] = true;

        global $wpdb;
        $wpdb->method('get_results')->willReturn([]);
        $wpdb->method('query')->willReturn(false);
        $wpdb->method('last_error')->willReturn('Database error occurred');

        $this->expectOutputString('');
        error_log("Error creating or altering table: Database error occurred");

        get_video_id();
    }

    public function testGetVideoIdHandlesVideoInsertion() {
        $_POST['ltr-video-url'] = 'https://www.youtube.com/watch?v=TRg94OCjx3Y';
        $_POST['ltr-submit-video-button'] = true;

        global $wpdb;
        $wpdb->method('get_results')->willReturn([]);
        $wpdb->method('query')->willReturn(true);
        $wpdb->method('last_error')->willReturn('');

        $this->expectOutputString('');
        // send_video_submission_email('https://www.youtube.com/watch?v=TRg94OCjx3Y', 'TRg94OCjx3Y');

        get_video_id();
    }
}