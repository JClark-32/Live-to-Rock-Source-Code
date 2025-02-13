<?php

use PHPUnit\Framework\TestCase;

final class GetVideoIDTest extends TestCase
{
    protected $wpdb;

    protected function setUp(): void {
        global $wpdb;
        
        // mock wpdb
        $this->wpdb = $this->createConfiguredMock(stdClass::class,
        [
            'prefix' => 'wp_',          // wpdb prefix
            'last_error' => '',         // initialize db error
        ]);

        $wpdb = $this->wpdb;
    }

    public function testNameHere(): void
    {
        
    }
}