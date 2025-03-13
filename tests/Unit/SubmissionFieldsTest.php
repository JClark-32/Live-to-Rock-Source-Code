<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/plugins/LifePerformancesPlugin/SubmissionFields.php';

class SubmissionFieldsTest extends TestCase
{
    public function testSubmissionFieldsOutput()
    {
        ob_start();
        
        try {
            submission_fields();
            $output = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        
        // Assert that the output contains expected HTML elements
        $this->assertStringContainsString('<div id="ltr-video-submission">', $output);
        $this->assertStringContainsString('<h2>Post Your Life Performance?</h2>', $output);
        $this->assertStringContainsString('<form id="ltr-video-link" method="post">', $output);
        $this->assertStringContainsString('<input type="url" name="ltr-video-url" placeholder="YouTube URL" required>', $output);
        $this->assertStringContainsString('<button type="submit" name="ltr-submit-video-button" class="submit-btn">Submit!</button>', $output);
    }
}
