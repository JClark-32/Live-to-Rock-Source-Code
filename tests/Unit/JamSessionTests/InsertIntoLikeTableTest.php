<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class InsertIntoLikeTableTest extends TestCase
{
    protected function tearDown(): void
    {

        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function testInsertIntoLikeTableCallsWpdbInsertWithCorrectArguments()
    {
        global $wpdb;


        $captured = [];
        $wpdb = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['insert'])
                     ->getMock();
        $wpdb->prefix = 'wp_';

        $wpdb->expects($this->once())
             ->method('insert')
             ->with(
                 $this->equalTo('wp_blog_post_likes'),              
                 $this->equalTo([
                     'user_liked' => 'bob',
                     'blog_id'    => 456,
                 ]),
                 $this->equalTo(['%s','%d'])
             );


        insert_into_like_table('bob', 456);
    }

    public function testInsertIntoLikeTableHandlesEmptyUsernameOrZeroBlogId()
    {
        global $wpdb;

        $wpdb = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['insert'])
                     ->getMock();
        $wpdb->prefix = 'wp_';

        $wpdb->expects($this->once())
             ->method('insert')
             ->with(
                 $this->equalTo('wp_blog_post_likes'),
                 $this->equalTo([
                     'user_liked' => '',
                     'blog_id'    => 0,
                 ]),
                 $this->equalTo(['%s','%d'])
             );

        insert_into_like_table('', 0);
    }
}
