<?php
function clear_table(){
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM $table_name"));
}