<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';

class LoadBlogSubmissionTest extends TestCase
{
    private int $initialObLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initialObLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->initialObLevel) {
            ob_end_clean();
        }
        parent::tearDown();
    }

    public function testLoadBlogSubmissionOutputsTheCorrectFormMarkup()
    {
        // create plugin without running its constructor
        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([]) 
                       ->getMock();

        $html = $plugin->load_blog_submission();

        // wrapping div and form tag
        $this->assertStringContainsString('<div id="ltr-blog-submission">', $html);
        $this->assertStringContainsString('<form action="" id="ltr-blog-post" method="post"', $html);

        // title input
        $this->assertStringContainsString(
            'input name="ltr-title-text"',
            $html,
            'Expected a title text input'
        );

        // author input
        $this->assertStringContainsString(
            'input name="ltr-author-text"',
            $html,
            'Expected an author text input'
        );

        // wp_editor stub produced a <textarea>
        $this->assertStringContainsString(
            '<textarea id="ltr-blog-text">',
            $html,
            'Expected the wp_editor textarea placeholder'
        );

        // submit button
        $this->assertStringContainsString(
            '<button type="submit" id="ltr-post-blog-button"',
            $html,
            'Expected the submit button'
        );

        // closing tags
        $this->assertStringContainsString('</form>', $html);
        $this->assertStringContainsString('</div>',  $html);
    }
}
