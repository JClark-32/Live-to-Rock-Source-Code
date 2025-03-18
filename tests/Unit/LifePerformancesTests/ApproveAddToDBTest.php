<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($data) {
        return $data;
    }
}

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/ApproveAddToDB.php';

class ApproveAddToDBTest extends TestCase {
    protected $logFile;

    protected function setUp(): void {
        parent::setUp();

        $_POST = []; // clear out

        // error_log() output to temp file
        $this->logFile = tempnam(sys_get_temp_dir(), 'log');
        ini_set('error_log', $this->logFile);

        global $wpdb;
        $wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['update'])
            ->getMock();

        $wpdb->prefix = 'wp_';
        $wpdb->last_error = '';

        $wpdb->method('update')->willReturn(1); // one row updated
    }

    protected function tearDown(): void {
        $_POST = []; // reset
        global $wpdb;
        $wpdb = null; // reset

        if (file_exists($this->logFile)) { // remove logfile
            unlink($this->logFile);
        }

        parent::tearDown();
    }

    public function testDoesNothingWhenApproveButtonNotSet() {
        global $wpdb;

        $_POST['videoInput'] = 'dQw4w9WgXcQ'; // rick roll lol
        $wpdb->expects($this->never())->method('update');

        approve_to_db('wp_video_submission');

        // read the log file and assert that the approval message is NOT logged
        $logContents = file_get_contents($this->logFile);
        $this->assertStringNotContainsString("Video approved with ID: dQw4w9WgXcQ", $logContents);
    }

    public function testApprovesVideoWhenButtonIsSet() {
        global $wpdb;

        $_POST['ltr-approveBtn'] = '1';
        $_POST['videoInput'] = 'dQw4w9WgXcQ';

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

        approve_to_db('wp_video_submission');

        // check error_log() has expected msg
        $logContents = file_get_contents($this->logFile);
        $this->assertStringContainsString("Video approved with ID: dQw4w9WgXcQ", $logContents);
    }
}