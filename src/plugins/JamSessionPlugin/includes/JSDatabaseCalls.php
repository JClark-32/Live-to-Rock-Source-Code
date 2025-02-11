<?php
    function pull_data($columnName, $tableName){
        global $wpdb;
        $return_value = $wpdb->get_col("
            SELECT $columnName
            FROM $tableName"
        );
        return $return_value;
    }

    function check_if_user_liked($blog_id){
        global $wpdb;
        $likes_table_name = $wpdb->prefix . 'blog_post_likes';
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        $checkQuery = "SELECT user_liked FROM $likes_table_name WHERE user_liked='$username' AND blog_id='$blog_id'";
        $results = $wpdb->query($checkQuery);
        return $results;
    }

    function insert_into_like_table($username, $blog_id){
        global $wpdb;
        $likes_table_name = $wpdb->prefix . 'blog_post_likes';
        $wpdb->insert(
            $likes_table_name,
            array(
                'user_liked' => $username,
                'blog_id' => $blog_id
            ),
            array(
                '%s', // blog_author
                '%d', // blog_title
            )
        );
    }

    function delete_from_like_table($username, $blog_id){
        global $wpdb;
        $likes_table_name = $wpdb->prefix . 'blog_post_likes';
        $deleteQuery = "DELETE FROM $likes_table_name WHERE user_liked='$username' AND blog_id='$blog_id'";
        $wpdb->query($deleteQuery);
    }

    function get_blog_comments($postID){
        global $wpdb;
        $comment_table_name = $wpdb->prefix .'blog_post_comments';
        
        $commentTextsQuery = "SELECT comment_text FROM $comment_table_name WHERE blog_id='$postID'";
        $commentUserNamesQuery = "SELECT user_commented FROM $comment_table_name WHERE blog_id='$postID'";
        $commentDatesPostedQuery = "SELECT date_posted FROM $comment_table_name WHERE blog_id='$postID'";
        $commentIdsQuery = "SELECT id FROM $comment_table_name WHERE blog_id='$postID'";

        $comment_texts = $wpdb->get_col($commentTextsQuery);
        $comment_user_names = $wpdb->get_col($commentUserNamesQuery);
        $comment_ids = $wpdb->get_col($commentIdsQuery);
        $comment_dates_posted = $wpdb->get_col($commentDatesPostedQuery);

        $comment_texts = array_reverse($comment_texts);
        $comment_user_names = array_reverse($comment_user_names);
        $comment_ids = array_reverse($comment_ids);
        $comment_dates_posted = array_reverse($comment_dates_posted);

        $comments = [
            "comment_ids" => $comment_ids,
            "comment_user_names" => $comment_user_names,
            "comment_dates_posted" => $comment_dates_posted,
            "comment_texts" => $comment_texts
        ];

        return $comments;
    }
    function insert_into_comment_table($comment, $blog_id){
        global $wpdb;
        $comment_table_name = $wpdb -> prefix . 'blog_post_comments';
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        $wpdb->insert(
            $comment_table_name,
            array(
                'user_commented' => $username,
                'blog_id' => $blog_id,
                'comment_text' => $comment
            ),
            array(
                '%s', // comment_author
                '%d', // blog_title
                '%s', // comment_text 
            )
        );
    }
?>