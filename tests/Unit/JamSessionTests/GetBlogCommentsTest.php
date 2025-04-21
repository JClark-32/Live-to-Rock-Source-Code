<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class GetBlogCommentsTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function testGetBlogCommentsCapturesSqlAndReturnsReversedArrays()
    {
        global $wpdb;

        $texts     = ['first text', 'second text'];
        $usernames = ['alice', 'bob'];
        $ids        = ['10', '20'];
        $dates      = ['2025-04-01', '2025-03-01'];

        $capturedSql = [];

        $wpdb = new class($capturedSql, [$texts, $usernames, $ids, $dates]) {
            public $prefix;
            private $captured;
            private $columns;
            private $idx = 0;

            public function __construct(&$captured, $columns)
            {
                $this->captured = &$captured;
                $this->columns  = $columns;
            }

            public function get_col($sql)
            {
                $this->captured[] = trim($sql);
                return $this->columns[$this->idx++];
            }
        };
        $wpdb->prefix = 'wp_';

        $result = get_blog_comments(55);

        $this->assertCount(4, $capturedSql);

        $this->assertStringContainsString("SELECT comment_text FROM wp_blog_post_comments WHERE blog_id='55'", $capturedSql[0]);
        $this->assertStringContainsString("SELECT user_commented FROM wp_blog_post_comments WHERE blog_id='55'", $capturedSql[1]);
        $this->assertStringContainsString("SELECT id FROM wp_blog_post_comments WHERE blog_id='55'", $capturedSql[2]);
        $this->assertStringContainsString("SELECT date_posted FROM wp_blog_post_comments WHERE blog_id='55'", $capturedSql[3]);

        // arrays should be reversed
        $this->assertSame(['second text', 'first text'], $result['comment_texts']);
        $this->assertSame(['bob', 'alice'], $result['comment_user_names']);
        $this->assertSame(['20', '10'], $result['comment_ids']);
        $this->assertSame(['2025-03-01', '2025-04-01'], $result['comment_dates_posted']);
    }
}
