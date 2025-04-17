<?php
use PHPUnit\Framework\TestCase;

if (!function_exists('add_shortcode')) {
    $GLOBALS['mocked_shortcodes'] = [];
    function add_shortcode($tag, $callback) {
        $GLOBALS['mocked_shortcodes'][$tag] = $callback;
    }
}

if (!function_exists('remove_shortcode')) {
    function remove_shortcode($tag, $callback) {
        // 
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback) {
        // 
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle = null, $src = null, $deps = [], $ver = false, $media = 'all') {
        // 
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://example.com/fake-plugin/';
    }
}

if (!function_exists('esc_html')) {
    function esc_html($string) {
        return $string;
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object)['user_login' => 'testuser'];
    }
}


require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';


class ShowBlogIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // mock global $wpdb
        global $wpdb;
        $wpdb = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['get_results'])
                     ->getMock();
        $wpdb->prefix = 'wp_';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        global $wpdb;
        $wpdb = null;
    }

    public function testShowBlogIndexWithoutConstructorRunsCorrectly()
    {
        global $wpdb;

        $row1 = (object)[
            'id'          => 42,
            'blog_title'  => 'My First Post',
            'date_posted' => '2025-04-01 12:34:56',
        ];
        $row2 = (object)[
            'id'          => 43,
            'blog_title'  => 'Another Post',
            'date_posted' => '2025-03-31 08:00:00',
        ];

        $expectedSql = "SELECT id, blog_title, date_posted FROM wp_blog_post ORDER BY date_posted DESC";
        $wpdb->expects($this->once())
             ->method('get_results')
             ->with($this->equalTo($expectedSql))
             ->willReturn([$row1, $row2]);

            
        // mock
        $plugin = $this->getMockBuilder(JamSession::class)
        ->disableOriginalConstructor()
        ->onlyMethods([])
        ->getMock();

        $html = $plugin->show_blog_index();

        $this->assertStringContainsString('<h2>Table of Contents</h2>', $html);
        $this->assertStringContainsString(
            '<a href="#blog-post42">My First Post</a> | 01 April 2025<br>',
            $html
        );
        $this->assertStringContainsString(
            '<a href="#blog-post43">Another Post</a> | 31 March 2025<br>',
            $html
        );
    }
}
