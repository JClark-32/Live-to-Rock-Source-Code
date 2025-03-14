<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/ApproveAddToDB.php';

Class ApproveAddToDBTest extends TestCase {
    function set_table(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'video_submission';
        clear_table($table_name);
    }

    function add_to_table(){
        
    }
}