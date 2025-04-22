<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS MOCKS
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../global_bootstrap.php'; // for current user can

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/JamSessionPlugin.php';

class AddDeleteButtonAjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($GLOBALS['test_can_edit']);
    }

    public function testEchoesNoWhenUserCannotEditOthersPosts()
    {
        $GLOBALS['test_can_edit'] = false;

        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([])  // keep real method
                       ->getMock();

        ob_start();
        $plugin->add_delete_button_ajax();
        $output = ob_get_clean();

        $this->assertSame('no', $output);
    }

    public function testEchoesYesWhenUserCanEditOthersPosts()
    {
        $GLOBALS['test_can_edit'] = true;

        $plugin = $this->getMockBuilder(JamSession::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods([])
                       ->getMock();

        ob_start();
        $plugin->add_delete_button_ajax();
        $output = ob_get_clean();

        $this->assertSame('yes', $output);
    }
}
