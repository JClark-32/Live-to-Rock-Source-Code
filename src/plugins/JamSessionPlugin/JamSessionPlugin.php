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
        add_action('plugins_loaded', array( $this,'wporg_add_submit_post_ability') );
        add_action('init', array( $this,'blog_id') );
        //add_action('init', array( $this,'enqueue_database_calls'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_like_ajax_request', array($this,'like_ajax_request'));
        add_action('wp_ajax_comment_ajax_request', array($this,'comment_ajax_request'));
        add_action('wp_ajax_comments_clicked_ajax_request', array($this,'comments_clicked_ajax_request'));
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
            <form id="ltr-blog-post" method="post">
                <div class="input">
                    <div name="title">
                        <input name="ltr-title-text" type="text" placeholder="Enter Title">
                    </div>
                    <div name="author">
                        <input name="ltr-author-text" type="text" placeholder="Enter Author(s)">
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
    public function blog_id(){
        // check if request was made & if from correct spot
        if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['ltr-blog-text'])) {

            $post_blog_button = isset($_POST['ltr-post-blog-button']);

            // check again for post call
            if ($post_blog_button) {
                $blog_title = sanitize_text_field($_POST['ltr-title-text']);
                $blog_text = sanitize_text_field($_POST['ltr-blog-text']);
                $blog_author = sanitize_text_field($_POST['ltr-author-text']);
            } else {
                echo "Error";
            }
            
            create_db_tables();

            if ($post_blog_button) {
                // insert data
                insert_into_blog_table($blog_author,$blog_text,$blog_title);
            }

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
            createBlogElements();
        </script>
        <?php

        echo '</div>';
        return ob_get_clean();
    }
    public function comment_ajax_request(){

        if(isset($_REQUEST)){
            $blog_id=$_REQUEST['postID'];
            $comment=$_REQUEST['comment'];
        }

        insert_into_comment_table($comment, $blog_id);
        die();
    }

    public function comments_clicked_ajax_request(){
        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $comments = get_blog_comments($postID);
            echo json_encode($comments);
        }

        die();
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
        die();
    }
}
new JamSession();