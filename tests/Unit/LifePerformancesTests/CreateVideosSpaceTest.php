<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action, $name) {
        echo "<input type='hidden' name='{$name}' value='nonce_value' />";
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return isset($GLOBALS['current_user_can']) ? $GLOBALS['current_user_can'] : false;
    }
}

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/CreateVideosSpace.php';

Class CreateVideosSpaceTest extends TestCase {
    protected $initialObLevel;

    protected function setUp(): void {
        parent::setUp();

        $this->initialObLevel = ob_get_level();

        while (ob_get_level() > $this->initialObLevel) { // clear any buffers left over from other tests
            ob_end_clean();
        }

        unset($GLOBALS['current_user_can']); // reset globals
    }

    protected function tearDown(): void {
        while (ob_get_level() > $this->initialObLevel) {
            ob_end_clean();
        }

        parent::tearDown();
    }

    public function testEmptyVideoData() {
        ob_start();
        create_video_space([]);
        $output = ob_get_clean();

        $this->assertStringContainsString('<div id="ltr-videos-here">', $output);
        $this->assertStringContainsString('<p>No videos available</p>', $output);
        $this->assertStringContainsString('<script> var videoIds = []', $output);
    }


 public function testVideoDataWithoutPrivileges() {
        $GLOBALS['current_user_can'] = false;

        // sample video objects
        $video1 = new stdClass();
        $video1->submission_text = 'dQw4w9WgXcQ';
        $video1->approved = false;

        $video2 = new stdClass();
        $video2->submission_text = '8ugK6BCZzyY';
        $video2->approved = true;

        $video_data = [$video1, $video2];

        ob_start();
        create_video_space($video_data);
        $output = ob_get_clean();

        // form created?
        $this->assertStringContainsString("id='video-posted0'", $output);
        $this->assertStringContainsString("id='video-posted1'", $output);

        // hidden input has correct values?
        $this->assertStringContainsString("name='videoInput' value='dQw4w9WgXcQ'", $output);
        $this->assertStringContainsString("name='videoInput' value='8ugK6BCZzyY'", $output);

        // delete button hidden?
        $this->assertStringContainsString("<style>#video-posted0 { display: none; }</style>", $output);
        $this->assertStringContainsString("<style>#deleteButton0 { display: none; }</style>", $output);
        $this->assertStringContainsString("<style>#deleteButton1 { display: none; }</style>", $output);

        // no approve button?
        $this->assertStringNotContainsString("approveButton0", $output);

        // nonce field
        $this->assertStringContainsString("name='approve_video_nonce'", $output);

        // check script for JSON
        $json = json_encode($video_data);
        $this->assertStringContainsString("<script> var videoIds = " . $json . "; </script>", $output);
    }

    public function testVideoDataWithPrivileges() {
        $GLOBALS['current_user_can'] = true;

        $video = new stdClass();
        $video->submission_text = 'video789';
        $video->approved = false;
        $video_data = [$video];

        ob_start();
        create_video_space($video_data);
        $output = ob_get_clean();

        // form not hidden
        $this->assertStringNotContainsString("<style>#video-posted0 { display: none; }</style>", $output);
        $this->assertStringNotContainsString("<style>#deleteButton0 { display: none; }</style>", $output);
        
        // approve button?
        $this->assertStringContainsString("id='approveButton0'", $output);
        $this->assertStringContainsString("name='approve_video_nonce'", $output);

        $json = json_encode($video_data);
        $this->assertStringContainsString("<script> var videoIds = " . $json . "; </script>", $output);
    }
}