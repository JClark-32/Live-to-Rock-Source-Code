<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class DeleteBlogPostTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function testDeleteBlogPostIssuesThreeCorrectQueriesInOrder()
    {
        global $wpdb;

        $calls = [];

        $wpdb = new class($calls) {
            public $prefix;
            private $calls;
            public function __construct(&$calls)
            {
                $this->calls = &$calls;
            }
            public function query($sql)
            {
                $this->calls[] = $sql;
                return true;
            }
        };
        $wpdb->prefix = 'wp_';

        delete_blog_post(42);

        $this->assertCount(3, $calls);

        $this->assertSame(
            "DELETE FROM wp_blog_post_likes WHERE blog_id='42'",
            $calls[0]
        );
        $this->assertSame(
            "DELETE FROM wp_blog_post_comments WHERE blog_id = '42'",
            $calls[1]
        );
        $this->assertSame(
            "DELETE FROM wp_blog_post WHERE id = '42'",
            $calls[2]
        );
    }
}
