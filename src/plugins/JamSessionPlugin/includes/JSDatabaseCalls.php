<?php
    function pull_data($columnName, $tableName){
        global $wpdb;
        $return_value = $wpdb->get_col("
            SELECT $columnName
            FROM $tableName"
        );
        return $return_value;
    }
?>