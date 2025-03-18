<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('wp_redirect')) {
    function wp_redirect($url) {
        $GLOBALS['wp_redirect'] = $url;
    }
}

if (!function_exists('get_site_url')) {
    function get_site_url() {
        return 'http://example.com';
    }
}

if (!defined('PHPUNIT_RUNNING')) { // this will bypass exit in the class function
    define('PHPUNIT_RUNNING', true);
}

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/DeleteVideo.php';

class DeleteVideoTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['wp_redirect'] = null;

        global $wpdb;
        $wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['query', 'prepare'])
            ->getMock();
        $wpdb->prefix = 'wp_';
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = null; // reset
    }

    public function testDeleteVideo() {
        global $wpdb;
        $video_id = '8ugK6BCZzyY';
        $table_name = 'wp_video_submission';

        // The expected SQL after prepare() is called.
        $expected_sql = "DELETE FROM $table_name WHERE submission_text = '8ugK6BCZzyY'";

        // Set expectation for prepare().
        $wpdb->expects($this->once())
            ->method('prepare')
            ->with(
                $this->equalTo("DELETE FROM $table_name WHERE submission_text = %s"),
                $this->equalTo($video_id)
            )
            ->willReturn($expected_sql);

        // Set expectation for query().
        $wpdb->expects($this->once())
            ->method('query')
            ->with($this->equalTo($expected_sql))
            ->willReturn(1);

        delete_video($video_id);
        
        $this->assertEquals('http://example.com/life-performances', $GLOBALS['wp_redirect']);
    }
}
