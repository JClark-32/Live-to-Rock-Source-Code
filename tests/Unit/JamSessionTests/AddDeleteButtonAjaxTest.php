<?php
use PHPUnit\Framework\TestCase;


// if (! defined('PHPUNIT_RUNNING')) {
//     define('PHPUNIT_RUNNING', true);
// }

// if (! defined('ABSPATH')) {
//     define('ABSPATH', __DIR__ . '/');
// }


// if (! function_exists('current_user_can')) {
//     function current_user_can($capability) {
//         return ($GLOBALS['can_edit'] ?? false) && $capability === 'edit_others_posts';
//     }
// }


// if (! function_exists('add_action')) {
//     function add_action($hook, $callback) {}
// }
// if (! function_exists('add_shortcode')) {
//     function add_shortcode($tag, $callback) {}
// }

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
