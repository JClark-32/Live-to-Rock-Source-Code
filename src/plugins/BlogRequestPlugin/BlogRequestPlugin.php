<?php
/**
 * Plugin Name: Blog Request Plugin
 * Description: Allows users to submit a blog link or content.
 * Version: 1.0.0
 * Author: Cleaned Version
 */

if (!defined('ABSPATH')) exit;

class BlogRequestPlugin {
    public function __construct() {
        require_once plugin_dir_path(__FILE__) . 'includes/BlogRequestSubmissionFields.php';
        require_once plugin_dir_path(__FILE__) . 'includes/CheckBlogRequestSubmitButton.php';
        require_once plugin_dir_path(__FILE__) . 'includes/GetBlogRequestID.php';
        require_once plugin_dir_path(__FILE__) . 'includes/SendBlogRequestEmailToAdmin.php';

        add_shortcode('ltr-blog-request-submission', array($this, 'blog_blank_shortcode'));
        add_action('plugins_loaded', array($this, 'wporg_add_blog_request_submission_ability'));
    }

    public function blog_blank_shortcode(){
        return '';
    }

    public function load_blog_request_submission(){
        ob_start();
        blog_submission_fields();
        return ob_get_clean();
    }

    public function wporg_add_blog_request_submission_ability(){
        if (current_user_can('edit_others_posts')) {
            remove_shortcode('ltr-blog-request-submission');
            add_shortcode('ltr-blog-request-submission', array($this, 'load_blog_request_submission'));
        }
    }
}

new BlogRequestPlugin();
