<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/EnterVideoData.php';

class EnterVideoDataTest extends TestCase {
    private $wpdb;
    private $errorMessage = '';

    protected function setUp(): void {
        parent::setUp();

        // wpdb mock
        $this->wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['insert'])
            ->getMock();

        // simulate successful database insert
        $this->wpdb->method('insert')->willReturn(true);

        // wpdb prefix
        $this->wpdb->prefix = 'wp_';

        $this->wpdb->last_error = '';

        global $wpdb;
        $wpdb = $this->wpdb;
    }

    public function test_does_not_insert_when_submitIsPosting_is_false() {
        global $wpdb;

        // Ensure insert_data() is NOT called
        $this->wpdb->expects($this->never())->method('insert');

        enter_data_if_able(false, 'video123');
    }

    public function test_inserts_when_submitIsPosting_is_true_and_no_error() {
        global $wpdb;

        // Ensure insert() is called once
        $this->wpdb->expects($this->once())->method('insert')
            ->with($this->stringContains('video_submission'), $this->anything());

        enter_data_if_able(true, 'video123');
    }

    public function test_logs_error_when_wpdb_has_error() {
        global $wpdb;

        $this->wpdb->last_error = 'Database connection failed';

        $this->wpdb->expects($this->once())->method('insert');

        $this->expectErrorMessage('Error creating table: Database connection failed Contact admin.');

        enter_data_if_able(true, 'video123');
    }

    protected function expectErrorMessage($expectedMessage) {
        // Override error_log function
        $GLOBALS['error_log_mock'] = function ($message) use ($expectedMessage) {
            TestCase::assertStringContainsString($expectedMessage, $message);
        };
    }
}

// Override global error_log function
if (!function_exists('error_log')) {
    function error_log($message) {
        if (isset($GLOBALS['error_log_mock'])) {
            call_user_func($GLOBALS['error_log_mock'], $message);
        }
    }
}