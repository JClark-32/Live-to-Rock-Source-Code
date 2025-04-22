<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class GetLikeCountTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function testReturnsZeroWhenNoLikes()
    {
        global $wpdb;

        $capturedSql = [];
        $wpdb = new class($capturedSql) {
            public $prefix;
            private $captured;
            public function __construct(&$captured) { $this->captured = &$captured; }
            public function query($sql)
            {
                $this->captured[] = trim($sql);
                return 0;
            }
        };
        $wpdb->prefix = 'wp_';

        $result = get_like_count(17);

        $this->assertSame(0, $result);

        $this->assertCount(1, $capturedSql);
        $this->assertSame(
            "SELECT user_liked FROM wp_blog_post_likes WHERE blog_id='17'",
            $capturedSql[0]
        );
    }

    public function testReturnsPositiveCountWhenLikesExist()
    {
        global $wpdb;

        $capturedSql = [];
        $wpdb = new class($capturedSql) {
            public $prefix;
            private $captured;
            public function __construct(&$captured) { $this->captured = &$captured; }
            public function query($sql)
            {
                $this->captured[] = trim($sql);
                return 5;
            }
        };
        $wpdb->prefix = 'wp_';

        $result = get_like_count(99);

        $this->assertSame(5, $result);

        $this->assertCount(1, $capturedSql);
        $this->assertStringContainsString(
            "FROM wp_blog_post_likes WHERE blog_id='99'",
            $capturedSql[0]
        );
    }
}
