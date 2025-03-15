<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/CheckSubmitButton.php';

Class CheckSubmitButtonTest extends TestCase {
 
    private $log_file = 'error_log_test.txt';

    protected function setUp(): void {
        ini_set('error_log', $this->log_file);
    }

    protected function tearDown(): void {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }

    public function testCheckForPostNotPosting() {
        $video_url = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";

        check_for_post(false, $video_url);

        $log_contents = file_get_contents($this->log_file);

        $this->assertStringContainsString("Submit button is not posting", $log_contents);
    }

    public function testCheckForPostWithValidUrl() {
        $video_url = "https://www.youtube.com/watch?v=dQw4w9WgXcQ"; 
        $result = check_for_post(true, $video_url);

        $this->assertEquals("dQw4w9WgXcQ", $result); 
    }
}