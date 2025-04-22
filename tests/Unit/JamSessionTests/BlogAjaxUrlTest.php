<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';

class BlogAjaxUrlTest extends TestCase
{
    public function testBlogAjaxurlOutputsCorrectScriptTag()
    {
        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([]) 
                       ->getMock();

        ob_start();
        $plugin->blog_ajaxurl();
        $output = ob_get_clean();

        $this->assertStringContainsString(
            '<script type="text/javascript">',
            $output
        );

        $this->assertStringContainsString(
            'var ajaxurl = "http://example.test/wp-admin/admin-ajax.php";',
            $output
        );

        $this->assertStringContainsString(
            '</script>',
            $output
        );
    }
}
