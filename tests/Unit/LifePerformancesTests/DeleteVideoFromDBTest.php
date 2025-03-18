<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($data) {
        return $data;
    }
}

if (!function_exists('wp_redirect')) {
    function wp_redirect($url) {
        $GLOBALS['wp_redirect'] = $url;
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value, $url) {
        return $url . (strpos($url, '?') !== false ? "&" : "?") . $key . "=" . $value;
    }
}

if (!function_exists('wp_get_referer')) {
    function wp_get_referer() {
        return 'http://example.com/ref';
    }
}

if (!function_exists('check_for_sql_err')) {
    function check_for_sql_err($video_id) {
        $GLOBALS['check_for_sql_err_called'] = $video_id;
    }
}

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/DeleteVideoFromDB.php';

class DeleteVideoFromDBTest extends TestCase {
    protected $logFile;
    protected $intialObLevel;

    protected function setUp(): void {
        parent::setUp();

        $this->initialObLevel = ob_get_level();

        // error_log() output to temp file
        $this->logFile = tempnam(sys_get_temp_dir(), 'log');
        ini_set('error_log', $this->logFile);

        // reset
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $GLOBALS['wp_redirect'] = null;
        $GLOBALS['check_for_sql_err_called'] = null;

        global $wpdb;
        $wpdb = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['query', 'prepare'])
                     ->getMock();
        $wpdb->prefix = 'wp_';
    }

    protected function tearDown(): void {
        while (ob_get_level() > $this->initialObLevel) {
            ob_end_clean();
        }

        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        global $wpdb;
        $wpdb = null;
        parent::tearDown();
    }

    public function testDeleteVideoIdWithoutVideoInput() {
        global $wpdb;
        $_POST['ltr-delBtn'] = 'Delete';
        // do not set $_POST['videoInput']

        $wpdb->expects($this->never())->method('prepare');
        $wpdb->expects($this->never())->method('query');

        ob_start();
        delete_video_id();
        ob_end_clean();

        $logContents = file_get_contents($this->logFile);
        $this->assertStringContainsString("No videoInput received in the request.", $logContents);

        $expected_redirect = add_query_arg('message', 'video_deleted', wp_get_referer());
        $this->assertEquals($expected_redirect, $GLOBALS['wp_redirect']);

        $this->assertNull($GLOBALS['check_for_sql_err_called']);
    }

}