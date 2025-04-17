<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

// WORDPRESS MOCKS
require_once __DIR__ . '/Bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class CheckIfUserLikedTest extends TestCase {
    protected $wpdb;

    protected function setUp(): void {
        global $wpdb;
        $this->wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['query'])
            ->getMock();
        $this->wpdb->prefix = 'wp_';
        $GLOBALS['wpdb'] = $this->wpdb;
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = null; // reset
    }

    public function testCheckIfUserLikedReturnsExpectedResult() {
        global $wpdb;
        $blog_id = 123;
        $expectedQuery = "SELECT user_liked FROM wp_blog_post_likes WHERE user_liked='testuser' AND blog_id='$blog_id'";

        $this->wpdb->expects($this->once())
            ->method('query')
            ->with($this->equalTo($expectedQuery))
            ->willReturn(5);

        $result = check_if_user_liked($blog_id);

        $this->assertEquals(5, $result);
    }
}