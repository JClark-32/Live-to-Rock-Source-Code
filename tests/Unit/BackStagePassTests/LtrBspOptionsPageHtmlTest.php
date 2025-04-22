<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS STUBS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/BackStagePassPlugin/BackStagePassPlugin.php';

class LtrBspOptionsPageHtmlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($GLOBALS['test_option_ltr_playlist_url']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['test_option_ltr_playlist_url']);
        parent::tearDown();
    }

    public function testOutputsFormWithSavedOption()
    {
        $GLOBALS['test_option_ltr_playlist_url'] = 'https://yt.example/playlist?list=XYZ';

        $plugin = $this->getMockBuilder(BackStagePass::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([]) 
                       ->getMock();

        ob_start();
        $plugin->ltr_bsp_options_page_html();
        $html = ob_get_clean();

        $this->assertStringContainsString('<div class="wrap">', $html);
        $this->assertStringContainsString('<h1>My Options</h1>', $html);

        $this->assertStringContainsString(
            'action="http://example.test/wp-admin/admin-post.php"',
            $html,
            'The form action should point to admin-post.php under wp-admin'
        );

        $this->assertMatchesRegularExpression(
            '/<textarea[^>]+name="ltr-playlist-url"[^>]*>https:\/\/yt\.example\/playlist\?list=XYZ<\/textarea>/',
            $html
        );

        $this->assertMatchesRegularExpression(
            '/<input\s+type="hidden"\s+name="action"\s+value="save_ltr_playlist"\s*\/?>/',
            $html
        );

        $this->assertStringContainsString(
            '<button>Save Playlist URL</button>',
            $html
        );
    }

    public function testDefaultsToEmptyTextareaWhenNoOptionSaved()
    {
        $plugin = $this->getMockBuilder(BackStagePass::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([]) 
                       ->getMock();

        ob_start();
        $plugin->ltr_bsp_options_page_html();
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression(
            '/<textarea[^>]+name="ltr-playlist-url"[^>]*><\/textarea>/',
            $html
        );
    }
}
