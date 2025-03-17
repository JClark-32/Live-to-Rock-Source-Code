<?php
    function create_db_tables(){
        global $wpdb;
        $table_name = $wpdb->prefix .'blog_post';
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
        . "    FOREIGN KEY(blog_id) REFERENCES $table_name(id)"
        . ");";

        $comments_table_name = $wpdb->prefix . 'blog_post_comments';

        $comments_sql = "CREATE TABLE IF NOT EXISTS $comments_table_name(\n"
        . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
        . "    user_commented VARCHAR(60) NOT NULL,\n"
        . "    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,\n"
        . "    blog_id INT(9),\n"
        . "    comment_text VARCHAR(280),\n"
        . "    PRIMARY KEY(id),\n"
        . "    FOREIGN KEY(blog_id) REFERENCES $table_name(id)"
        . ");";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql);
        dbDelta($likes_sql);
        dbDelta($comments_sql);
    }
?>