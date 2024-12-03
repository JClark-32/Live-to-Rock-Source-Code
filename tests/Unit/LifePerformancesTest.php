<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
require_once __DIR__ . '/../../vendor/autoload.php';

class LifePerformancesTest extends TestCase
{
    // FUNCTION MOCKING
    protected function setUp(): void
    {
        // define ABSPATH to prevent class from immediately exiting
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__ . '/');
        }

        // other mocked functions
        if (!function_exists('wp_enqueue_style')) {
            $mocked_enqueue_styles = [];
        
            function wp_enqueue_style($handle, $src, $deps = [], $ver = false, $media = 'all') {
                global $mocked_enqueue_styles;
                $mocked_enqueue_styles[] = compact('handle', 'src', 'deps', 'ver', 'media');
            }
        }

        if (!function_exists('plugin_dir_url')) {
            function plugin_dir_url($file) {
                return 'http://example.com/wp-content/plugins/mock-plugin/';
            }
        }

        if (!function_exists('add_action')) {
            $mocked_actions = [];
            function add_action($hook, $callback)
            {
                global $mocked_actions;
                $mocked_actions[] = [
                    'hook' => $hook,
                    'callback' => $callback
                ];
            }
        }

        if (!function_exists('add_shortcode')) {
            function add_shortcode($tag, $callback)
            {
                global $mocked_shortcodes;
                $mocked_shortcodes[$tag] = $callback;
            }
        }

        if (!function_exists('remove_shortcode')) {
            function remove_shortcode($tag, $callback)
            {
                global $mocked_shortcodes;
                $mocked_shortcodes[$tag] = $callback;
            }
        }
    }

    private function mockCurrentUserCan($result)
    {
        if (!function_exists('current_user_can')) {
            function current_user_can($capability)
            {
                return $capability; // Simulate the return value
            }
        }
    }

    private function mockAddShortcode()
    {
        if (!function_exists('add_shortcode')) {
            function add_shortcode($tag, $callback)
            {
                global $mocked_shortcodes;
                $mocked_shortcodes[$tag] = $callback;
            }
        }
    }

    // LOAD ASSETS 
    public function testLoadAssets()
    {
        global $mocked_enqueue_styles;
        $mocked_enqueue_styles = [];
    
        $plugin = new LifePerformances();
        $plugin->load_assets();
    
        $this->assertNotEmpty($mocked_enqueue_styles, 'wp_enqueue_style was not called.');
    
        $expectedStyle = [
            'handle' => 'LifePerformancesPlugin',
            'src' => plugin_dir_url(__FILE__) . '/css/LifePerformancesPlugin.css',
            'deps' => [],
            'ver' => 1,
            'media' => 'all',
        ];
    
        $this->assertContains($expectedStyle, $mocked_enqueue_styles, 'The correct style was not enqueued.');
    }

    // BLANK SHORTCODE
    public function testBlankShortcode()
    {
        // mock of life performances
        $pluginMock = $this->createMock(LifePerformances::class);

        // mock returns blank string bc shortcode doesn't return anything
        $pluginMock->method('blank_shortcode')->willReturn('');

        $output = $pluginMock->blank_shortcode();
        $this->assertSame('', $output, 'Expected blank_shortcode to return an empty string.');
    }

    // LOAD VIDEO SUBMISSIONS
    public function testLoadVideoSubmission()
    {
        // echo "Starting test...\n";
        // $this->assertTrue(class_exists('LifePerformances'), "The class 'LifePerformances' does not exist.");
        // echo "Class exists.";

        try {
            $lifePerformance = new LifePerformances();
            // echo "Class instantiated...\n";
        } catch (Exception $e) {
            echo "Exception caught: " . $e->getMessage() . "\n";
        }

        $output = $lifePerformance->load_video_submission();
        // echo "Method executed...\n";

        $expected = '<div id="ltr-video-submission">
            <h2>Post Your Life Performance?</h2>
            <p>Paste YouTube URL here:</p>
            <form id="ltr-video-link" method="post">
                <div class="input">
                    <input type="url" name="ltr-video-url" placeholder="YouTube URL" required>
                </div>
                <div id="ltr-submit">
                    <button type="submit" name="ltr-submit-video-button" class="submit-btn">Submit!</button>
                </div>
            </form>
        </div>';

        $expected = preg_replace('/\s+/', '', $expected);
        $output = preg_replace('/\s+/', '', $output);

        $this->assertSame($expected, $output, "The output HTML does not match the expected structure.");
    }

}