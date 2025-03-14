<?php
    function prepare_to_show(){
        global $wpdb;
        ob_start();
        $table_name = $wpdb->prefix . 'video_submission';
        
        approve_to_db($table_name);

        $video_data = $wpdb->get_results("SELECT id, submission_text, approved FROM $table_name");

        create_video_space($video_data);

        ?>
        <script>
            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            function onYouTubeIframeAPIReady() {
                videoIds.forEach((vidData, index) => {
                    new YT.Player(`player${index}`, {
                        height: '313',
                        width: '556',
                        videoId: vidData.submission_text,
                        playerVars: {
                            'playsinline': 1
                        }
                    });
                });
            }
        </script>
        <?php

        ob_end_clean();
    }
?>