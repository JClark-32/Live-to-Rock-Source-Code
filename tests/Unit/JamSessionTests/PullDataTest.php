<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class PullDataTest extends TestCase {
    protected $wpdb;

    protected function setUp(): void {
        global $wpdb;
        $this->wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['get_col'])
            ->getMock();
        $this->wpdb->prefix = 'wp_';
        $wpdb = $this->wpdb;
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = null;
    }

    public function testPullDataReturnsExpectedResults() {
        $expected = ['value1', 'value2'];
        $this->wpdb->expects($this->once())
            ->method('get_col')
            ->with($this->callback(function($sql) {
                // normalize to lowercase and no whitespace
                $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $sql)));
                return $normalized === 'select column_name from wp_blog_post';
            }))
            ->willReturn($expected);

        $result = pull_data('column_name');

        $this->assertEquals($expected, $result);
    }
}