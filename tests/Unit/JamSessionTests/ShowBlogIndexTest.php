<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';


class ShowBlogIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
