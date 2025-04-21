<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';

class EmptyShortcodeTest extends TestCase
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

    public function testEmptyShortcodeReturnsOnlyWhitespace()
    {
        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([])
                       ->getMock();

        $output = $plugin->empty_shortcode();

        $this->assertSame(
            '',
            trim($output),
            'empty_shortcode() should return only whitespace or nothing when trimmed'
        );
    }

    public function testEmptyShortcodeDoesNotLeakOutputBuffers()
    {
        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([])
                       ->getMock();

        $plugin->empty_shortcode();

        $this->assertSame(
            $this->initialObLevel,
            ob_get_level(),
            'empty_shortcode() should not leave any output buffers open'
        );
    }
}
