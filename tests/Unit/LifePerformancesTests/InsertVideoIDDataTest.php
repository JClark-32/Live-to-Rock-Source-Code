<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/InsertVideoIDData.php';

Class InsertVideoIDDataTest extends TestCase {

    protected function setUp(): void {
        global $wpdb;
        $this->wpdb = $this->getMockBuilder(stdClass::class)
                           ->addMethods(['insert'])
                           ->getMock();

        // Mock $wpdb->prefix to simulate WordPress database table prefix
        $this->wpdb->prefix = 'wp_';

        // Assign the mock object to the global $wpdb
        $wpdb = $this->wpdb;
    }

    public function testInsertDataCallsWpdbInsert() {
        $video_id = 'abc123';
        $table_name = 'wp_video_submission';

        // Expect `insert` to be called once with expected parameters
        $this->wpdb->expects($this->once())
            ->method('insert')
            ->with(
                $this->equalTo($table_name),
                $this->equalTo(['submission_text' => $video_id]),
                $this->equalTo(null)
            )
            ->willReturn(1); // Simulate successful insert

        // Call function
        insert_data($video_id);
    }
}
