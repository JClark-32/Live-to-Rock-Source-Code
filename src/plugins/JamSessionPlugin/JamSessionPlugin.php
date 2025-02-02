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
        add_action('wp_enqueue_scripts', array($this, 'load_jquery'));
        add_action('wp_ajax_like_ajax_request', array($this,'like_ajax_request'));
        add_action('wp_ajax_comment_ajax_request', array($this,'comment_ajax_request'));
        add_action('wp_head',array($this,'blog_ajaxurl'));
    }
    public function load_jquery(){
        wp_enqueue_script('jquery');
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
        global $wpdb;

        // check if request was made & if from correct spot
        if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['ltr-blog-text'])) {

            // check again for post call
            if (isset($_POST['ltr-post-blog-button'])) {
                $blog_title = sanitize_text_field($_POST['ltr-title-text']);
                $blog_text = sanitize_text_field($_POST['ltr-blog-text']);
                $blog_author = sanitize_text_field($_POST['ltr-author-text']);
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
            . "    blog_author TEXT NOT NULL,\n"
            . "    PRIMARY KEY(id)"
            . ");";

            $likes_table_name = $wpdb->prefix . 'blog_post_likes';

            $likes_sql = "CREATE TABLE IF NOT EXISTS $likes_table_name(\n"
            . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
            . "    user_liked VARCHAR(60) NOT NULL,\n"
            . "    blog_id INT(9),\n"
            . "    PRIMARY KEY(id),\n"
            . "    FOREIGN KEY(blog_id) REFERENCES wp_blog_post(id)"
            . ");";

            $comments_table_name = $wpdb->prefix . 'blog_post_comments';

            $comments_sql = "CREATE TABLE IF NOT EXISTS $comments_table_name(\n"
            . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
            . "    user_commented VARCHAR(60) NOT NULL,\n"
            . "    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,\n"
            . "    blog_id INT(9),\n"
            . "    comment_text TEXT,\n"
            . "    PRIMARY KEY(id),\n"
            . "    FOREIGN KEY(blog_id) REFERENCES wp_blog_post(id)"
            . ");";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
           
            dbDelta($sql);
            dbDelta($likes_sql);
            dbDelta($comments_sql);

            $current_user = wp_get_current_user();
            $username = $current_user->user_login;

            if (isset($_POST['ltr-post-blog-button'])) {
                // insert data
                $wpdb->insert(
                    $table_name,
                    array(
                        'user_posted' => $username,
                        'blog_title' => $blog_title,
                        'blog_text' => $blog_text,
                        'blog_author' => $blog_author
                    ),
                    array(
                        '%s', // user_posted
                        '%s', // blog_author
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
        $like_table_name = $wpdb->prefix . 'blog_post_likes';

        //Get the entries for text, title, user, and date
        $blog_texts = $this->pull_data("blog_text", $table_name);
        $blog_titles = $this->pull_data("blog_title", $table_name);
        $blog_ids = $this->pull_data("id",$table_name);
        //$user_names = $this->pull_data("user_posted", $table_name);
        $blog_authors = $this->pull_data("blog_author",$table_name);
        $dates_posted = $this->pull_data("date_posted", $table_name);
        $blog_likes = [];
        
        foreach ($blog_ids as $value) {
            $LikeCountQuery = "SELECT user_liked FROM wp_blog_post_likes WHERE blog_id='$value'";
            $results = $wpdb->query($LikeCountQuery);
            array_push($blog_likes, $results);
        };

        echo '<div id="ltr-blogs-here">';

        ?>
        <script>
            //Transfers the entries into arrays
            var blogIds = <?php echo json_encode($blog_ids) ?> ;
            var blogTexts = <?php echo json_encode($blog_texts) ?> ;
            var blogTitles = <?php echo json_encode($blog_titles) ?> ;
            var blogAuthors = <?php echo json_encode($blog_authors) ?> ;
            var datesPosted = <?php echo json_encode($dates_posted) ?> ;
            var blogLikes = <?php echo json_encode($blog_likes) ?> ;

            //Reverses the entries of the arrays
            blogIds.reverse();
            blogTexts.reverse();
            blogTitles.reverse();
            blogAuthors.reverse();
            datesPosted.reverse();
            blogLikes.reverse();

            const blogContainer = document.getElementById("ltr-blogs-here");

            blogTexts.forEach((blogText, index) => {
                const postDiv = document.createElement("div");
                postDiv.classList.add("blog-post"+blogIds[index]);
                
                const hr = document.createElement("hr");

                const title = document.createElement("h2");
                title.textContent = blogTitles[index];
                
                const authorLabel = document.createElement("label");
                authorLabel.textContent = blogAuthors[index];
                
                const datePara = document.createElement("p");
                datePara.style.color = "gray";
                datePara.innerHTML = `<small>${datesPosted[index]}</small>`;
                
                const textPara = document.createElement("p");
                textPara.textContent = blogText;

                const likeButton = document.createElement("button");
                likeButton.type = "button";
                likeButton.textContent = "Like";
                likeButton.name = "blog-likeBtn";
                likeButton.onclick = likeClick;

                const likeCount = document.createElement("span");
                likeClick.name = "likeCount";
                likeCount.textContent = blogLikes[index];

                const commentButton = document.createElement("button");
                commentButton.type = "button";
                commentButton.textContent = "Comment";
                commentButton.name = "blog-commentBtn";
                commentButton.onclick = commentClick;

                postDiv.appendChild(hr);
                postDiv.appendChild(title);
                postDiv.appendChild(authorLabel);
                postDiv.appendChild(datePara);
                postDiv.appendChild(textPara);
                postDiv.appendChild(likeButton);
                postDiv.appendChild(likeCount);
                postDiv.appendChild(commentButton);

                blogContainer.appendChild(postDiv);

                function likeClick(){
                    jQuery(document).ready(function($){
                        var postId = blogIds[index];
                        $.ajax({
                            url:ajaxurl,
                            data:{
                                'action':'like_ajax_request',
                                'postID' :postId
                            },
                            success:function(data){
                                if(data == "liked"){
                                    likeCount.textContent=parseInt(likeCount.textContent)+1;
                                }
                                else if(data == "unliked"){
                                    likeCount.textContent=parseInt(likeCount.textContent)-1;
                                }
                            },
                            error:function(errorThrown){
                                window.alert("errorThrown");
                            }
                        })
                    })
                    var blogPostId = blogIds[index];
                    console.log(blogPostId);
                }

                var boxExists;

                function commentClick(){
                    var commentsDiv = document.createElement("div");
                    commentsDiv.id = "blog-comments"+blogIds[index];

                    var input = document.createElement("input");
                    input.type="text";
                    input.id = "blog-comment-input";
                    input.name="blog-commentInput";
                    
                    input.addEventListener("keydown", function(event) {
                        if (event.key === "Enter") {
                            submitComment(input.value);
                            input.value = "";
                        }
                    });

                    commentsDiv.append(input);

                    var currentCommentsDiv = document.getElementById("blog-comments"+blogIds[index]);

                    if (boxExists == true) {
                        boxExists = false;
                        currentCommentsDiv.remove();

                    }
                    else{
                        boxExists = true;
                        postDiv.appendChild(commentsDiv);
                    }
                }
                function submitComment(comment) {
                    jQuery(document).ready(function($){
                        var postId = blogIds[index];
                        $.ajax({
                            url:ajaxurl,
                            data:{
                                'action':'comment_ajax_request',
                                'postID' :postId,
                                'comment':comment
                            },
                            success:function(data){
                                if(data == "success"){
                                    alert("success");
                                }
                                else if(data == "error"){
                                    alert("broke");
                                }
                            },
                            error:function(errorThrown){
                                window.alert("errorThrown");
                            }
                        })
                    })
                    var blogPostId = blogIds[index];
                    console.log(blogPostId);
                    console.log("Comment submitted:", comment);
                }
                
            })
        </script>
        <?php

        echo '</div>';
        return ob_get_clean();
    }
    public function comment_ajax_request(){
        global $wpdb;

        $comment_table_name = $wpdb -> prefix . 'blog_post_comments';
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        if(isset($_REQUEST)){
            $blog_id=$_REQUEST['postID'];
            $comment=$_REQUEST['comment'];
        }

        $wpdb->insert(
            $comment_table_name,
            array(
                'user_commented' => $username,
                'blog_id' => $blog_id,
                'comment_text' => $comment,
            ),
            array(
                '%s', // comment_author
                '%d', // blog_title
                '%s', // comment_text 
            )
        );

        if ($comment == 'test'){
            echo"success";
        }
        else{
            echo"error";
        }

        die();
    }

    public function like_ajax_request(){
        global $wpdb;

        $like_table_name = $wpdb -> prefix . 'blog_post_likes';
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        if(isset($_REQUEST)){
            $postID=$_REQUEST['postID'];
            $blog_id = $postID;
        }

        $checkQuery = "SELECT user_liked FROM wp_blog_post_likes WHERE user_liked='$username' AND blog_id='$blog_id'";
        $results = $wpdb->query($checkQuery);
        
        if ($results==0){
            echo "liked";
            $wpdb->insert(
                $like_table_name,
                array(
                    'user_liked' => $username,
                    'blog_id' => $blog_id,
                ),
                array(
                    '%s', // blog_author
                    '%d', // blog_title
                )
            );
        }
        else{
            echo "unliked";
            $deleteQuery = "DELETE FROM wp_blog_post_likes WHERE user_liked='$username' AND blog_id='$blog_id'";
            $wpdb->query($deleteQuery);
        }
        die();
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