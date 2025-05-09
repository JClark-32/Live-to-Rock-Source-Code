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

    //Constructer classes that initialize functions
    public function __construct(){
        require_once 'includes/JSDatabaseCalls.php';
        require_once 'includes/JSDatabaseTableCreation.php';

        add_shortcode('ltr-blog-submission', array( $this,'empty_shortcode') );
        add_shortcode('ltr-blogs', array( $this,'show_blogs') );
        add_shortcode('ltr-blog-index', array($this, 'show_blog_index'));

        add_action('plugins_loaded', array( $this,'wporg_add_submit_post_ability') );
        add_action('init', array( $this,'blog_id') );
        //add_action('init', array( $this,'enqueue_database_calls'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_like_ajax_request', array($this,'like_ajax_request'));
        add_action('wp_ajax_like_count_ajax_request', array($this,'like_count_ajax_request'));
        add_action('wp_ajax_comment_ajax_request', array($this,'comment_ajax_request'));
        add_action('wp_ajax_comments_clicked_ajax_request', array($this,'comments_clicked_ajax_request'));
        add_action('wp_ajax_add_delete_button_ajax', array($this,'add_delete_button_ajax'));
        add_action('wp_ajax_delete_button_pressed_ajax', array($this,'delete_button_pressed_ajax'));
        add_action('wp_ajax_comment_delete_ajax_request', array($this,'comment_delete_ajax_request'));
        add_action('wp_head',array($this,'blog_ajaxurl'));
    }
    public function enqueue_scripts(){
        wp_enqueue_script('jquery');
        wp_enqueue_script('display-elements-js',plugin_dir_url(__FILE__).'js/display-elements.js');
    }
    public function blog_ajaxurl(){
        echo'<script type="text/javascript">
                var ajaxurl = "' . admin_url('admin-ajax.php').'";
                </script>
        ';
    }

    //Blank shortcode used for users without posting permissions
    public function empty_shortcode(){
        ob_start();
        ?>
        <?php
        return ob_get_clean();
    }

    //Loads the entry form for blog posting

    public function load_blog_submission(){
    ob_start();
    ?>
        <div id="ltr-blog-submission">
            <h2>Post a new blog?</h2>
            <p>Add blog text here</p>
            <form action="" id="ltr-blog-post" method="post" onsubmit="document.getElementById('ltr-post-blog-button').disabled = true;">
                <div class="input">
                    <div name="title" style="padding:5px;">
                        <input name="ltr-title-text" type="text" placeholder="Enter Title">
                    </div>
                    <div name="author" style="padding:5px;">
                        <input name="ltr-author-text" type="text" placeholder="Enter Author(s)">
                    </div>
                    <div style="padding:5px;">
                        <?php
                            $settings = array( 'textarea_name' => 'ltr-blog-text' );
                            wp_editor( "Enter Text", "ltr-blog-text", $settings);
                        ?>
                    </div>
                </div>
                <div id="ltr-submit" style="padding:5px;">
                    <button type="submit" id="ltr-post-blog-button" name="ltr-post-blog-button" class="submit-btn">Post!</button>
                </div>
            </form>
        </div>
    <?php
    
    return ob_get_clean();
    
    }
    public function blog_id(){
        // check if request was made & if from correct spot
        if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['ltr-blog-text'])) {

            create_db_tables();

            $blog_title = sanitize_text_field($_POST['ltr-title-text']);
            $blog_text = wp_kses_post($_POST['ltr-blog-text']);
            //$blog_text = sanitize_text_field($_POST['ltr-blog-text']);
            $blog_author = sanitize_text_field($_POST['ltr-author-text']);
                
            insert_into_blog_table($blog_author,$blog_text,$blog_title); 

            wp_redirect($_SERVER['REQUEST_URI']);
            exit;

        }
    }

    //Removes the empty shortcode and replaces it with the blog-submission for users with editing permissions and above
    function wporg_add_submit_post_ability() {
        if ( current_user_can('edit_others_posts')){
            remove_shortcode('ltr-blog-submission');
            add_shortcode('ltr-blog-submission', array( $this,'load_blog_submission') );
        }
    }

  
    public function show_blogs( ){
        ob_start();

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        //Get the entries for text, title, user, and date
        $blog_texts = pull_data("blog_text");
        $blog_titles = pull_data("blog_title");
        $blog_ids = pull_data("id");
        //$user_names = pull_data("user_posted");
        $blog_authors = pull_data("blog_author");
        $dates_posted = pull_data("date_posted");
        $blog_likes = [];

        $blog_ids = array_map('wp_unslash', $blog_ids);
        $blog_texts = array_map('wp_unslash', $blog_texts);
        $blog_titles = array_map('wp_unslash', $blog_titles);
        $blog_authors = array_map('wp_unslash', $blog_authors);
        $dates_posted = array_map('wp_unslash', $dates_posted);
        $blog_likes = array_map('wp_unslash', $blog_likes);

        
        foreach ($blog_ids as $value) {
            $results = get_like_count($value);
            array_push($blog_likes, $results);
        };

        echo '<div id="ltr-blogs-here">';

        ?>
        <script>
            //Transfers the entries into javascript arrays
            blogIds = <?php echo json_encode($blog_ids) ?> ;
            blogTexts = <?php echo json_encode($blog_texts) ?> ;
            blogTitles = <?php echo json_encode($blog_titles) ?> ;
            blogAuthors = <?php echo json_encode($blog_authors) ?> ;
            datesPosted = <?php echo json_encode($dates_posted) ?> ;
            blogLikes = <?php echo json_encode($blog_likes) ?> ;
            currentUser = <?php echo json_encode($user_id) ?>;
            
            reverseArrays();
            
            if (currentUser == 0){
                createBlogElements();
            }
        </script>
        <?php

        echo '</div>';
        return ob_get_clean();
    }

    public function show_blog_index() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blog_post';
    
        $posts_per_page = 15;
        $current_page = isset($_GET['blog_page']) ? max(1, intval($_GET['blog_page'])) : 1;
        $offset = ($current_page - 1) * $posts_per_page;
    
        // Total post count
        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total_posts / $posts_per_page);
    
        // Fetch only the current page of posts
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, blog_title, date_posted FROM $table_name ORDER BY date_posted DESC LIMIT %d OFFSET %d",
                $posts_per_page,
                $offset
            )
        );
    
        ob_start();
        echo '<div class="ltr-blog-index" style="margin:5px;">';
        echo '<h2>Table of Contents</h2>';
        echo '<div style="width:100%;height:200px;overflow:auto;">';
        echo '<ul style=" list-style-type: square;margin: 10px 0;">';
    
        foreach ($results as $row) {
            $formatted_date = date("d F Y", strtotime($row->date_posted));
            $clean_title = wp_unslash($row->blog_title);
            echo '<li>';
            echo '<a href="#blog-post' . esc_html($row->id) .'">';
            echo esc_html($clean_title) . '</a> | ';
            echo esc_html($formatted_date) . '<br>';
            echo '</li>';
        }
    
        echo '</ul>';
        echo '</div>';
    
        // Page navigation
        echo '<div class="blog-page-menu" style="margin-top: 20px; text-align:center;">';
        $base_url = remove_query_arg('blog_page');
        $connector = strpos($base_url, '?') !== false ? '&' : '?';
    
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            echo '<a href="' . esc_url($base_url . $connector . 'blog_page=' . $prev_page) . '">← Previous</a> ';
        }
    
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i === $current_page) {
                echo '<strong style="margin: 0 5px;">' . $i . '</strong>';
            } else {
                echo '<a href="' . esc_url($base_url . $connector . 'blog_page=' . $i) . '" style="margin: 0 5px;">' . $i . '</a>';
            }
        }
    
        if ($current_page < $total_pages) {
            $next_page = $current_page + 1;
            echo ' <a href="' . esc_url($base_url . $connector . 'blog_page=' . $next_page) . '">Next →</a>';
        }
    
        echo '</div>';
        echo '</div>';
    
        return ob_get_clean();
    }
    

    
    public function add_delete_button_ajax(){
        if (current_user_can('edit_others_posts')){
            echo("yes");
        }
        else{
            echo("no");
        }
    }

    public function delete_button_pressed_ajax(){
        
        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $blog_id = $postID;
        }

        delete_blog_post($blog_id);
        
        if (!defined('PHPUNIT_RUNNING')) {
            die();
        }
    }
    

    public function comment_ajax_request(){
        global $wpdb;
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
        
        if(isset($_REQUEST)){
            $blog_id=$_REQUEST['postID'];
            $comment=$_REQUEST['comment'];
        }

        insert_into_comment_table($comment, $blog_id);
        echo $username;

        if (!defined('PHPUNIT_RUNNING')) {
            die();
        }
    }
    

    public function comment_delete_ajax_request(){
        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $commentID=$_REQUEST['commentID'];
        }
        delete_blog_comment($postID, $commentID);

        if (!defined('PHPUNIT_RUNNING')) {
            die();
        }
    }

    public function comments_clicked_ajax_request(){
        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $comments = get_blog_comments($postID);
            echo json_encode($comments);
        }

        if (!defined('PHPUNIT_RUNNING')) {
            die();
        }
    }

    public function like_ajax_request(){
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $blog_id = $postID;
        }

        $results = check_if_user_liked($blog_id);
        
        if ($results==0){
            echo "liked";
            insert_into_like_table($username, $blog_id);
        }
        else{
            echo "unliked";
            delete_from_like_table($username, $blog_id);
        }
        
        if (!defined('PHPUNIT_RUNNING')) {
            die();
        }
    }

    public function like_count_ajax_request(){
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        
        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $blog_id = $postID;
        }
        

        $results = check_if_user_liked($blog_id);
        
        if ($results==0){
            echo "unliked";
        }
        else{
            echo "liked";
        }
        
        if (!defined('PHPUNIT_RUNNING')) {
            die();
        }
    }

}

if ( ! defined('PHPUNIT_RUNNING') ) {
    new JamSession();
}