<?php
use PHPUnit\Framework\TestCase;

// WORDPRESS STUBS
require_once __DIR__ . '/bootstrap.php';

if (! function_exists('add_menu_page')) {
    function add_menu_page(
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback,
        $icon_url = '',
        $position = null
    ) {
        $GLOBALS['add_menu_page_args'][] = compact(
            'page_title',
            'menu_title',
            'capability',
            'menu_slug',
            'callback',
            'icon_url',
            'position'
        );
    }
}

require_once __DIR__ . '/../../../src/plugins/BackStagePassPlugin/BackStagePassPlugin.php';

class AdminMenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($GLOBALS['add_menu_page_args']);
    }

    public function testAddsAdminMenuItem()
    {
        $plugin = new BackStagePass();

        $plugin->ltr_bsp_options_page();

        $this->assertArrayHasKey(
            'add_menu_page_args',
            $GLOBALS,
            'add_menu_page() was never called'
        );

        $calls = $GLOBALS['add_menu_page_args'];
        $this->assertCount(1, $calls, 'Expected exactly one add_menu_page() call');

        $args = $calls[0];

        $this->assertSame('Back Stage Pass',    $args['page_title']);
        $this->assertSame('Back Stage Pass',    $args['menu_title']);
        $this->assertSame('manage_options',     $args['capability']);
        $this->assertSame('back_stage_pass',    $args['menu_slug']);
        $this->assertSame('dashicons-microphone', $args['icon_url']);
        $this->assertNull(      $args['position']);

        $this->assertIsArray($args['callback']);
        $this->assertCount(2, $args['callback']);
        $this->assertSame($plugin,                $args['callback'][0]);
        $this->assertSame('ltr_bsp_options_page_html', $args['callback'][1]);
        $this->assertTrue(is_callable($args['callback']));
    }
}
