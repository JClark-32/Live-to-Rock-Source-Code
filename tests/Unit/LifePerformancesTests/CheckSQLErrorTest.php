<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/CheckSQLError.php';

class CheckSQLErrorTest extends TestCase {
    private $logFile;

    protected function setUp(): void {
        global $wpdb;

        // temporary file for error_log output
        $this->logFile = tempnam(sys_get_temp_dir(), 'log');
        ini_set('error_log', $this->logFile);

        $wpdb = new stdClass();
        $wpdb->last_error = '';
    }

    protected function tearDown(): void {
        parent::tearDown();

        // clean up and restore log file
        ini_restore('error_log');
        unlink($this->logFile);

        // Reset global variables between tests
        global $wpdb;
        $wpdb = null; // Clear mock object

        $_POST = [];   // Reset POST data
        $_SERVER = []; // Reset server variables
    }

    // no SQL errors
    public function testCheckForSqlErrSuccess() {
        global $wpdb;
        $wpdb = $this->createMock(stdClass::class);
        $wpdb->last_error = '';

        check_for_sql_err(123);

        $logContents = file_get_contents($this->logFile);

        $this->assertStringContainsString('Video successfully deleted with ID: 123', $logContents);
    }

    // SQL error
    public function testCheckForSqlErrError() {
        global $wpdb;
        $wpdb = $this->createMock(stdClass::class);
        $wpdb->last_error = 'Some SQL error occurred';

        check_for_sql_err(456);

        $logContents = file_get_contents($this->logFile);

        $this->assertStringContainsString('Error deleting video: Some SQL error occurred', $logContents);
    }
}
