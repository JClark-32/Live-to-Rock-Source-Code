<?php
use PHPUnit\Framework\TestCase;

// MOCKS
require_once __DIR__ . '/bootstrap.php';

if (! function_exists('get_option')) {
    function get_option($name, $default = null) {
        return $GLOBALS['test_option_ltr_playlist_url'] ?? $default;
    }
}

require_once __DIR__ . '/../../../src/plugins/BackStagePassPlugin/BackStagePassPlugin.php';

class PodcastShortcodeTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_GET['podcast_page'], $GLOBALS['test_option_ltr_playlist_url']);
        parent::tearDown();
    }

    public function testErrorPathReturnsWpErrorMessage()
    {
        $GLOBALS['test_option_ltr_playlist_url'] = 'dummy-playlist';
        $plugin = $this->getMockBuilder(BackStagePass::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['get_youtube_playlist_videos'])
                       ->getMock();
        $plugin->method('get_youtube_playlist_videos')
               ->willReturn(new WP_Error('code','BOOM!'));

        $html = $plugin->podcast_shortcode();

        $this->assertSame(
            '<p>Error fetching playlist: BOOM!</p>',
            $html
        );
    }

    public function testHappyPathSinglePageRendersTitlesAndIframesAndPager()
{
    $videos = [
        ['snippet'=>['title'=>'One','resourceId'=>['videoId'=>'AAA']]],
        ['snippet'=>['title'=>'Two','resourceId'=>['videoId'=>'BBB']]],
    ];

    $GLOBALS['test_option_ltr_playlist_url'] = 'dummy';
    $plugin = $this->getMockBuilder(BackStagePass::class)
                   ->disableOriginalConstructor()
                   ->onlyMethods(['get_youtube_playlist_videos'])
                   ->getMock();
    $plugin->method('get_youtube_playlist_videos')->willReturn($videos);

    unset($_GET['podcast_page']);
    $html = $plugin->podcast_shortcode();

    // each title
    $this->assertStringContainsString('<h4>One</h4>', $html);
    $this->assertStringContainsString('<h4>Two</h4>', $html);

    // each iframe src
    $this->assertStringContainsString(
        "src='https://www.youtube-nocookie.com/embed/AAA'", $html
    );
    $this->assertStringContainsString(
        "src='https://www.youtube-nocookie.com/embed/BBB'", $html
    );

    // no prev/next links
    $this->assertStringNotContainsString('← Previous', $html);
    $this->assertStringNotContainsString('Next →',      $html);

    // exactly one <strong ...>1</strong>
    $this->assertMatchesRegularExpression('/<strong[^>]*>1<\/strong>/', $html);
    $count = preg_match_all('/<strong[^>]*>1<\/strong>/', $html, $m);
    $this->assertSame(1, $count, 'There should be exactly one current-page marker');
}

    public function testPaginationBehaviorShowsPrevAndNext()
    {
        $videos = [];
        for ($i = 1; $i <= 35; $i++) {
            $videos[] = ['snippet'=>[
                'title'      => "T$i",
                'resourceId' => ['videoId'=>"ID$i"]
            ]];
        }

        $GLOBALS['test_option_ltr_playlist_url'] = 'dummy';
        $plugin = $this->getMockBuilder(BackStagePass::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['get_youtube_playlist_videos'])
                       ->getMock();
        $plugin->method('get_youtube_playlist_videos')->willReturn($videos);

        $_GET['podcast_page'] = '2';
        $html = $plugin->podcast_shortcode();

        // prev link to page=1
        $this->assertMatchesRegularExpression(
            '#<a[^>]+href="http://example\.test/current-page\?podcast_page=1"[^>]*>← Previous</a>#',
            $html
        );

        // next link to page=3
        $this->assertMatchesRegularExpression(
            '#<a[^>]+href="http://example\.test/current-page\?podcast_page=3"[^>]*>Next →</a>#',
            $html
        );

        // current page strong=2
        $this->assertMatchesRegularExpression('/<strong[^>]*>2<\/strong>/', $html);
    }
}