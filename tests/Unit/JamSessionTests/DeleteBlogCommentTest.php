<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class DeleteBlogCommentTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function testDeleteBlogCommentIssuesCorrectSingleQuery()
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

        delete_blog_comment(221, 13);

        $this->assertCount(1, $calls, 'Expected exactly one call to $wpdb->query()');

        $expected = "DELETE FROM wp_blog_post_comments WHERE blog_id='221' and id='13'";
        $this->assertSame($expected, $calls[0]);
    }
}
