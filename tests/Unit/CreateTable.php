<?php
function create_table(){
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (\n"
                    . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
                    . "    submission_text VARCHAR(11) NOT NULL,\n"
                    . "    approved TINYINT(1) DEFAULT 0,\n"
                    . "    PRIMARY KEY(id)\n"
                    . ");";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}