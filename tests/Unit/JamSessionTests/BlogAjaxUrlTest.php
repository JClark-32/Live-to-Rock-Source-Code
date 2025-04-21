<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';

class BlogAjaxUrlTest extends TestCase
{
    public function testBlogAjaxurlOutputsCorrectScriptTag()
    {
        // Arrange: make a mock that keeps all real methods
        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([])  // <— keep every real method intact
                       ->getMock();

        // Act: capture its echo
        ob_start();
        $plugin->blog_ajaxurl();
        $output = ob_get_clean();

        // Assert: script tag
        $this->assertStringContainsString(
            '<script type="text/javascript">',
            $output
        );

        // Assert: correct ajaxurl assignment
        $this->assertStringContainsString(
            'var ajaxurl = "http://example.test/wp-admin/admin-ajax.php";',
            $output
        );

        // Assert: closing tag
        $this->assertStringContainsString(
            '</script>',
            $output
        );
    }
}
