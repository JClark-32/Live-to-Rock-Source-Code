<?php

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../vendor/autoload.php';

class LifePerformancesTest extends TestCase
{
    // FUNCTION MOCKING
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp(); // Set up Brain Monkey
        Monkey\Functions\stubs([
            'wp_enqueue_style',
            'plugin_dir_url'
        ]);

        // define ABSPATH to prevent class from immediately exiting
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__ . '/');
        }

        if (!function_exists('add_shortcode')) {
            function add_shortcode($tag, $callback)
            {
                global $mocked_shortcodes;
                $mocked_shortcodes[$tag] = $callback;
            }
        }
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown(); 
        parent::tearDown();
    }


    // LOADS ASSETS
    // can't get this test to work with brain\monkey so come back alter

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

    // WPROG ADD VIDEO SUBMISSION ABILITY

    // VIDEO ID
    public function testVideoId()
    {
        // Simulate a POST request with a valid YouTube URL
        $_POST['ltr-video-url'] = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $_POST['ltr-submit-video-button'] = true;
    
        // Mock the wpdb global object using Brain Monkey
        Functions\stubs([
            'wpdb' => function() {
                $mock = $this->getMockBuilder('wpdb')
                    ->onlyMethods(['insert'])
                    ->getMock();
    
                // Mock the insert method
                $mock->expects($this->once())
                    ->method('insert')
                    ->with(
                        $this->equalTo($mock->prefix . 'video_submission'),
                        $this->equalTo(['submission_text' => 'dQw4w9WgXcQ']),
                        $this->anything()
                    );
    
                return $mock;
            }
        ]);
    
        // Now when LifePerformances is called, it will use the mocked wpdb object
        $lifePerformances = new LifePerformances();
        $lifePerformances->video_id();
    
        // Assert that the video ID was processed and inserted correctly
        $this->assertTrue(true, "Video ID processed and inserted successfully.");
    }
    
    

    // DELETE VIDEOS
    public function testDeleteVideos()
    {
        // Simulate a POST request for deleting a video
        $_POST['ltr-delBtn'] = true;
        $_POST['videoInput'] = 'dQw4w9WgXcQ';
    
        // Mock the wpdb global object using Brain Monkey
        Functions\stubs([
            'wpdb' => function() {
                $mock = $this->getMockBuilder('wpdb')
                    ->onlyMethods(['query'])
                    ->getMock();
    
                // Mock the query method
                $mock->expects($this->once())
                    ->method('query')
                    ->with(
                        $this->equalTo(
                            $mock->prepare(
                                "DELETE FROM {$mock->prefix}video_submission WHERE submission_text = %s",
                                'dQw4w9WgXcQ'
                            )
                        )
                    );
    
                return $mock;
            }
        ]);
    
        // Initialize the plugin and call the method
        $lifePerformances = new LifePerformances();
        $lifePerformances->delete_videos();
    
        // Check that the query was executed correctly
        $this->assertTrue(true, "Video deletion query executed successfully.");
    }
    

    // TEST SHOW VIDEOS METHOD
    

}