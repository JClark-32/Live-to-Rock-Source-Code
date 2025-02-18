<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
require_once __DIR__ . '/../../vendor/autoload.php';

final class GetVideoIDTest extends TestCase
{
    protected $wpdb;

    public function setUp(): void {
        global $wpdb;
        
        // Initialize Brain Monkey to mock WordPress functions
        parent::setUp();
        Brain\Monkey\setUp();

        // Mock the wpdb object using PHPUnit's createMock()
        $this->$wpdb = $this->createMock(\wpdb::class);

        // Set the global wpdb to the mocked wpdb
        $wpdb = $this->$wpdb;

        // Mock $_POST superglobal
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['ltr-video-url'] = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';  // rick roll URL
        $_POST['ltr-submit-video-button'] = 'Submit';
    }

    public function tearDown(): void
    {
        Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_get_vid_id_valid_post_data(): void
    {
        global $wpdb;

        // mocked variables
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['ltr-video-url'] = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';    // rick roll
        $_POST['ltr-submit-video-button'] = 'Submit';

        // function should attempt to interact w db
        $this->wpdb->expects($this->once())
            ->method('query')
            ->willReturn(true);
    

        // mock functions
        function sanitize_text_field($string) {
            return filter_var($string, FILTER_SANITIZE_STRING);
        }   // mocked to return sanitized string

        function check_for_post($submitIsPosting, $video_url) {
            return 'dQw4w9WgXcQ';
        }   // extracts the yt video id (from rick roll)

        function enter_data_if_able($submitIsPosting, $video_id) {
            return true;
        }   // mocked to always succeed/ return true for testing

        function send_video_submission_email($video_url, $video_id) {
            return true;
        }   // mocked to always succeed/ return true for testing

        // Handle the redirect
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Redirected to " . $_SERVER['REQUEST_URI ']
        );  // function should try to redirect to the right URL
            // if wp_redirect() not called test will FAIL

        get_video_id();
    }

    // public function test_get_vid_id_no_vid_url()
    // {
        
    // }
}