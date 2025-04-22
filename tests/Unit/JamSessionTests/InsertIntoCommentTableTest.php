<?php
use PHPUnit\Framework\TestCase;

if (! function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object)[
            'user_login' => $GLOBALS['test_user_login'] ?? 'default_user'
        ];
    }
}

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class InsertIntoCommentTableTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb'], $GLOBALS['test_user_login']);
        parent::tearDown();
    }

    public function testInsertIntoCommentTableUsesCurrentUserAndCorrectArgs()
    {
        global $wpdb;

        $GLOBALS['test_user_login'] = 'testuser';
        $captured = [];

        $wpdb = new class($captured) {
            public $prefix;
            private $captured;
            public function __construct(&$captured)
            {
                $this->captured = &$captured;
            }
            public function insert($table, $data, $formats)
            {
                $this->captured['table']   = $table;
                $this->captured['data']    = $data;
                $this->captured['formats'] = $formats;
                return true;
            }
        };
        $wpdb->prefix = 'wp_';

        insert_into_comment_table('Nice post', 88);

        $this->assertSame(
            'wp_blog_post_comments',
            $captured['table'],
            'Should insert into the blog_post_comments table'
        );

        $this->assertSame(
            [
                'user_commented' => 'testuser',
                'blog_id'        => 88,
                'comment_text'   => 'Nice post'
            ],
            $captured['data']
        );

        $this->assertSame(
            ['%s', '%d', '%s'],
            $captured['formats']
        );
    }
}
