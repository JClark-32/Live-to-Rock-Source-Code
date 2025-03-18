<?php

use PHPUnit\Framework\TestCase;

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object)['user_login' => 'testuser'];
    }
}

require_once __DIR__ . '/../../../src/plugins/JamSessionPlugin/includes/JSDatabaseCalls.php';

class PullDataTest extends TestCase {
    protected $wpdb;

    protected function setUp(): void {
        global $wpdb;
        $this->wpdb = $this->getMockBuilder(stdClass::class)
            ->addMethods(['get_col'])
            ->getMock();
        $this->wpdb->prefix = 'wp_';
        $wpdb = $this->wpdb;
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = null;
    }
}