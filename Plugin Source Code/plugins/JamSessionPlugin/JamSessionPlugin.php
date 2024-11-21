<?php
/**
 * Plugin Name: Jam Session Plugin
 * Description: Adds blog posts
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: jam-session-plugin
 */

 // Prevent public user access to .php file
 if ( !defined('ABSPATH') ) { exit; }

 Class JamSession{
    public function __construct(){
        add_shortcode('ltr-blog-submission', array( $this,'empty_blog_submission') );
        add_shortcode('ltr-blogs', array( $this,'show_blogs') );
        add_action('plugins_loaded', array( $this,'wporg_add_submit_post_ability') );
        #add_action('init', array( $this,'blog_id') );
    }

    public function empty_blog_submission(){
        ob_start();
        ?>
        <?php
        return ob_get_clean();
    }

    public function load_blog_submission(){
    ob_start();
    ?>
        <div id="ltr-blog-submission">
            <h2>Post a new blog?</h2>
            <p>Add blog text here</p>
            <form id="ltr-blog-post" method="post">
                <div class="input">
                    <div name="title">
                        <input type="text">
                    </div>
                    <textarea name="ltr-blog-text"placeholder="Enter Text" required cols="80" rows = "6"></textarea>
                </div>
                <div id="ltr-submit">
                    <button type="submit" name="ltr-post-blog-button" class="submit-btn">Post!</button>
                </div>
            </form>
        </div>
    <?php
    return ob_get_clean();
    }

    /*
    public function blog_id(){
        global $wpdb;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[''])){
        }
    }
    */

    #Adds the ability to see the video sumbission entry box to users that are above the editor user permissions
    function wporg_add_submit_post_ability() {
        if ( current_user_can('edit_others_posts')){
            remove_shortcode('ltr-blog-submission');
            add_shortcode('ltr-blog-submission', array( $this,'load_blog_submission') );
        }
    }


    public function show_blogs( ){
        global $wpdb;
        ob_start();
        $table_name = $wpdb->prefix .'blog_post';

        $blog_text = $wpdb->get_col("
            SELECT post_text
            FROM $table_name"
        );

        echo '<div id="ltr-blogs-here">';
        foreach ($blog_text as $index => $blog_text) {
            echo "<div id='post$index' data-blog-text='$blog_text' class='blog-post' loading='lazy'></div>";
            }
            # code...
            echo '</div>';
        return ob_get_clean();
    }

}
 new JamSession();