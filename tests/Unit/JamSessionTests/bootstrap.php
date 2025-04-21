<?php

if (! defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

if (!function_exists('add_shortcode')) {
    $GLOBALS['mocked_shortcodes'] = [];
    function add_shortcode($tag, $callback) {
        $GLOBALS['mocked_shortcodes'][$tag] = $callback;
    }
}

if (!function_exists('remove_shortcode')) {
    function remove_shortcode($tag, $callback) {
        // 
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback) {
        // 
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle = null, $src = null, $deps = [], $ver = false, $media = 'all') {
        // 
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://example.com/fake-plugin/';
    }
}

if (!function_exists('esc_html')) {
    function esc_html($string) {
        return $string;
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object)['user_login' => 'testuser'];
    }
}

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('admin_url')) {
    function admin_url($path = '') {
        return 'http://example.test/wp-admin/' . ltrim($path, '/');
    }
}

if (! function_exists('wp_editor')) {
    function wp_editor($content, $editor_id, $settings) {
        echo "<textarea id=\"{$editor_id}\">{$content}</textarea>";
    }
}