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
        add_shortcode('ltr-blog-submission', array( $this,'empty_shortcode') );
        add_shortcode('ltr-blogs', array( $this,'show_blogs') );
        add_action('plugins_loaded', array( $this,'wporg_add_submit_post_ability') );
        add_action('init', array( $this,'blog_id') );
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
        global $wpdb;

        // check if request was made & if from correct spot
        if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['ltr-blog-text'])) {

            // check again for post call
            if (isset($_POST['ltr-post-blog-button'])) {
                $blog_title = sanitize_text_field($_POST['ltr-title-text']);
                $blog_text = sanitize_text_field($_POST['ltr-blog-text']);
            } else {
                echo "Error";
            }

            $table_name = $wpdb->prefix . 'blog_post';

            $sql = "CREATE TABLE IF NOT EXISTS $table_name(\n"
            . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
            . "    user_posted VARCHAR(60) NOT NULL,\n"
            . "    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,\n"
            . "    blog_title TEXT NOT NULL,\n"
            . "    blog_text TEXT NOT NULL,\n"
            . "    PRIMARY KEY(id)"
            . ");";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            $current_user = wp_get_current_user();
            $username = $current_user->user_login;

            if (isset($_POST['ltr-post-blog-button'])) {
                // insert data
                $wpdb->insert(
                    $table_name,
                    array(
                        'user_posted' => $username,
                        'blog_title' => $blog_title,
                        'blog_text' => $blog_text
                    ),
                    array(
                        '%s', // user_posted
                        '%s', // blog_title
                        '%s'  // blog_text
                    )
                );
            }

            if ($wpdb->last_error) {
                echo "\nError creating table " . $wpdb->last_error . "\nContact admin.";
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
        global $wpdb;
        ob_start();
        $table_name = $wpdb->prefix .'blog_post';

        //Get the entries for text, title, user, and date
        $blog_texts = $this->pull_data("blog_text", $table_name);
        $blog_titles = $this->pull_data("blog_title", $table_name);
        $user_names = $this->pull_data("user_posted", $table_name);
        $dates_posted = $this->pull_data("date_posted", $table_name);

        echo '<div id="ltr-blogs-here">';

        //Transfers the entries into arrays
        echo "<script> var blogTexts = " . json_encode($blog_texts) . "; </script>";
        echo "<script> var blogTitles = " . json_encode($blog_titles) . "; </script>";
        echo "<script> var userNames = " . json_encode($user_names) . "; </script>";
        echo "<script> var datesPosted = " . json_encode($dates_posted) . "; </script>";

        ?>
        <script>
            //reverse the arrays so that the most recently posted entries are first
            blogTexts.reverse()
            blogTitles.reverse()
            userNames.reverse()
            datesPosted.reverse()

            //Create elements on the html webpage for each entry
            blogTexts.forEach(blogText => {
                var blogTitle = blogTitles[blogTexts.indexOf(blogText)]
                var userName = userNames[blogTexts.indexOf(blogText)]
                var datePosted = datesPosted[blogTexts.indexOf(blogText)]

                document.write("<hr>")
                document.write("<div>")
                document.write("<h2>" + blogTitle + " </h2>")
                document.write("<label>" + userName +"</label>")
                document.write("<p style='color:gray'><small>"+datePosted+"</small></p>")
                document.write("<p>" + blogText + "</p>")
                document.write("</div>")
                
                console.log(blogTitle);
                
            });
        </script>
        <?php

        echo '</div>';
        return ob_get_clean();
    }

    private function pull_data($columnName, $tableName){
        global $wpdb;
        $return_value = $wpdb->get_col("
            SELECT $columnName
            FROM $tableName"
        );
        return $return_value;
    }

}
new JamSession();