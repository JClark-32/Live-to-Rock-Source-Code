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

class InsertIntoBlogTableTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb'], $GLOBALS['test_user_login']);
        parent::tearDown();
    }

    public function testInsertIntoBlogTableCallsWpdbInsertWithoutError()
    {
        global $wpdb;

        $GLOBALS['test_user_login'] = 'testuser';
        $captured = [];

        $wpdb = new class($captured) {
            public $prefix;
            public $last_error = '';  // no error path
            private $captured;
            public function __construct(&$captured) { $this->captured = &$captured; }
            public function insert($table, $data, $formats)
            {
                $this->captured['table']   = $table;
                $this->captured['data']    = $data;
                $this->captured['formats'] = $formats;
                return true;
            }
        };
        $wpdb->prefix = 'wp_';

        ob_start();
        insert_into_blog_table('Author A','Blog body','Blog Title');
        $output = ob_get_clean();

        $this->assertSame(
            'wp_blog_post',
            $captured['table'],
            'Should insert into wp_blog_post'
        );

        $this->assertSame(
            [
                'user_posted' => 'testuser',
                'blog_title'  => 'Blog Title',
                'blog_text'   => 'Blog body',
                'blog_author' => 'Author A'
            ],
            $captured['data']
        );

        $this->assertSame(
            ['%s','%s','%s','%s'],
            $captured['formats']
        );

        $this->assertSame('', $output, 'No error should be echoed when last_error is empty');
    }

    public function testInsertIntoBlogTableEchoesErrorMessageWhenLastErrorSet()
    {
        global $wpdb;

        $GLOBALS['test_user_login'] = 'frank';
        $captured = [];

        $wpdb = new class($captured) {
            public $prefix;
            public $last_error = 'Oops, DB failed';
            private $captured;
            public function __construct(&$captured) { $this->captured = &$captured; }
            public function insert($table, $data, $formats)
            {
                $this->captured['table']   = $table;
                $this->captured['data']    = $data;
                $this->captured['formats'] = $formats;
                return false;
            }
        };
        $wpdb->prefix = 'wp_';

        ob_start();
        insert_into_blog_table('Auth','Body','Title');
        $output = ob_get_clean();

        $this->assertSame('wp_blog_post', $captured['table']);

        $this->assertStringContainsString(
            "\nError creating table Oops, DB failed\nContact admin.",
            $output
        );
    }
}
