<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/plugins/LifePerformancesPlugin/InsertVideoIDData.php';

Class InsertVideoIDDataTest extends TestCase {
    function set_table(){
        global $wpdb;
        require __DIR__ . "/../ClearDatabase.php";
        require __DIR__ . "/../CreateTable.php";

        $table_name = $wpdb->prefix . 'video_submission_test';

        create_table($table_name);
        clear_table($table_name);
    }

    function testInsertVideo(){
        $video_id = "dQw4w9WgXcQ";

        $this->set_table();
        insert_data($table_name, $video_id);
        $this->assertTrue($wpdb->query($wpdb->prepare("SELECT * FROM $table_name WHERE $video_id")));
    }
    
}