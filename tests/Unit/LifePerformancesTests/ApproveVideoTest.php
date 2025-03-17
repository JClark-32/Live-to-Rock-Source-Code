<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('wp_redirect')) {
    function wp_redirect($url) {
        $GLOBALS['wp_redirect'] = $url;
    }
}

if (!function_exists('get_site_url')) {
    function get_site_url() {
        return 'http://example.com';
    }
}

require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/includes/ApproveVideo.php';

class ApproveVideoTest extends TestCase {
    
}