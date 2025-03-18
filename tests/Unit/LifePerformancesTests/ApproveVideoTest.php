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

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/ApproveVideo.php';

class ApproveVideoTest extends TestCase {
    protected $logFile;

    protected function setUp(): void {
        parent::setUp();

        $GLOBALS['wp_redirect'] = null;
         
        global $wpdb;
        $wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['update'])
            ->getMock();
        $wpdb->prefix = 'wp_';
        $wpdb->last_error = '';

        $wpdb->method('update')->willReturn(1); // one row updated
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = null; // reset

        parent::tearDown();
    }

    public function testApproveVideo() {
        global $wpdb;

        $wpdb->expects($this->once())
             ->method('update')
             ->with(
                 'wp_video_submission',
                 ['approved' => 1],
                 ['submission_text' => 'dQw4w9WgXcQ'],
                 ['%d'],
                 ['%s']
             )
             ->willReturn(1);

        // define PHPUNIT_RUNNING so test doesn't exit
        if (!defined('PHPUNIT_RUNNING')) {
            define('PHPUNIT_RUNNING', true);
        }

        approve_video('dQw4w9WgXcQ');

        $this->assertEquals('http://example.com/life-performances', $GLOBALS['wp_redirect']);
    }
}