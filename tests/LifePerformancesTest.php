<?php

use PHPUnit\Framework\TestCase;

require_once 'src/bootstrap.php';
require_once 'src/plugins/LifePerformancesPlugin/LifePerformancesPlugin.php';

class LifePerformancesTest extends TestCase
{
    public function testLoadVideoSubmission()
    {
        $lifePerformance = new LifePerformances();

        $output = $lifePerformance->load_video_submission();

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