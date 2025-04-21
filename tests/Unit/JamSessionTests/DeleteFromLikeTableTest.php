<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class DeleteFromLikeTableTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function testDeleteFromLikeTableIssuesCorrectQuery()
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

        delete_from_like_table('carol', 321);

        $this->assertCount(1, $calls, 'Expected one call to $wpdb->query()');

        $expected = "DELETE FROM wp_blog_post_likes WHERE user_liked='carol' AND blog_id='321'";
        $this->assertSame($expected, $calls[0]);
    }
}
