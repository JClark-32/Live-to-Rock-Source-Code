<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../vendor/autoload.php';

class LifePerformancesTest extends TestCase
{
    public function testLoadVideoSubmission()
    {
        echo "Starting test...\n";
        // $this->assertTrue(class_exists('LifePerformances'), "The class 'LifePerformances' does not exist.");

        try {
            $lifePerformance = new LifePerformances();
            echo "Class instantiated...\n";
        } catch (Exception $e) {
            echo "Exception caught: " . $e->getMessage() . "\n";
        }

        $output = $lifePerformance->load_video_submission();
        echo "Method executed...\n";

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