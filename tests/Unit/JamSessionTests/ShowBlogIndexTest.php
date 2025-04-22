<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';


class ShowBlogIndexTest extends TestCase
{
    private int $initialObLevel;
    protected function setUp(): void
    {
        parent::setUp();

        $this->initialObLevel = ob_get_level();

        global $wpdb;
        $wpdb = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['get_var','prepare','get_results'])
                     ->getMock();
        $wpdb->prefix = 'wp_';
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->initialObLevel) {
            ob_end_clean();
        }

        global $wpdb;
        $wpdb = null;
        
        parent::tearDown();
    }

    public function testShowBlogIndexWithoutConstructorRunsCorrectly()
    {
        global $wpdb;

        $wpdb->expects($this->once())
             ->method('get_var')
             ->with($this->stringContains('SELECT COUNT(*)'))
             ->willReturn(2);

        $preparedSql =
          "SELECT id, blog_title, date_posted "
        . "FROM wp_blog_post "
        . "ORDER BY date_posted DESC "
        . "LIMIT 15 OFFSET 0";
        $wpdb->expects($this->once())
             ->method('prepare')
             ->with(
                 $this->stringContains('SELECT id, blog_title, date_posted'),
                 15, // posts_per_page
                 0   // offset
             )
             ->willReturn($preparedSql);

        $row1 = (object)[ 'id'=>42,'blog_title'=>'My First Post','date_posted'=>'2025-04-01 12:34:56' ];
        $row2 = (object)[ 'id'=>43,'blog_title'=>'Another Post','date_posted'=>'2025-03-31 08:00:00' ];
        $wpdb->expects($this->once())
             ->method('get_results')
             ->with($this->equalTo($preparedSql))
             ->willReturn([$row1,$row2]);

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
