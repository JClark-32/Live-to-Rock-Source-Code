<?php
/** 
 * Plugin Name: Life Performances Plugin
 * Description: Plugin for the LtR Life Performances Page
 * Authors:
 * Version: 0.1.0
 * Text Domain: life-performances-plugin
*/
if ( !defined('ABSPATH') ) 
{
    exit;
}

class LifePerformances{

    public function __construct(){

        // Load assets
        add_action('wp_enqueue_scripts', array( $this,'load_assets') );

        // Add shortcode
        add_shortcode('life-performance', array( $this,'load_shortcode') );

        // Add submission shortcode
        add_shortcode('video-submission', array( $this, 'load_videosubmission') );

        //load javascript
        add_action('wp_footer', array($this, 'load_scripts'));

        add_action('init', array($this,'video_id'));

        add_action('init', array($this, 'show_videos'));


    }

    public function create_video_submission(){
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'pulicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'Life Performance',
                'singular_name' => 'Share Your Music'
            ),
        );
        register_post_type('video_submission_form', $args);
    }

    public function load_assets(){
        wp_enqueue_style(
            'LifePerformancesPlugin', 
            plugin_dir_url( __FILE__ ) . '/css/LifePerformancesPlugin.css',
            array(),
            1 ,
            'all'
        );

        wp_enqueue_script(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/js/LifePerformancesPlugin.js',
            array(),
            1 ,
            'all'
        );
    }

    public function load_videosubmission()
    {?>
        <div class="wp-block-uagb-container uagb-block-dc229f8f alignfull uagb-is-root-container"><div class="uagb-container-inner-blocks-wrap">
            <div class="video-submission">
                <h2>Post Your Life Performance?</h2>
                <p>Paste a link to your video here</p>

                <form id="video-link" method="post">
                    <div class="input">
                        <input type="link" name="video-url" placeholder="YouTube link here" required>
                    </div>
                    <div class="submit">
                        <button type="submit" name="submit-video" class="submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div></div>
    <?php }
    
    public function load_shortcode()
    {?>
        <div class = "form-group">
            <div>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/j_S0upmiG7Q?si=ayY4EpAj1hDs7z6v" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </div>
    <?php }

    //headers nodderrs

    public function test_table(){
        if (isset($_POST['submit-video'])){
            Global $wpdb;
            $table_name = "testing";
            $charset_collate = $wpdb->get_charset_collate();


            $sql = "CREATE TABLE IF NOT EXISTS video_submission(\n"

                . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"

                . "    submission_text TEXT NOT NULL,\n"

                . "    PRIMARY KEY(id)\n"

                . ");";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta($sql);

            if ($wpdb->last_error) {
                echo "Error creating table: " . $wpdb->last_error;
            } else {
                echo "Table created successfully.";
            }
        }
    }

    public function video_id()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video-url'])) {
            
            if (isset($_POST['submit-video'])){
                    $video_url = sanitize_text_field($_POST['video-url']);
                }else{
                    echo "No";
                };

                preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $video_url, $matches);
                $video_id = $matches[1];

                global $wpdb;

                if ($video_id){
                    $table_name = $wpdb->prefix . 'video_submission';

                    //$charset_collate = $wpdb->get_charset_collate();

                    $sql = "CREATE TABLE IF NOT EXISTS $table_name(\n"

                    . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"

                    . "    submission_text TEXT NOT NULL,\n"

                    . "    PRIMARY KEY(id)\n"

                    . ");";

                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

                    dbDelta( $sql );
                    if (isset($_POST['submit-video'])){
                    // Insert data into the table
                    $wpdb->insert(
                        $table_name,
                        array(
                            'submission_text' => $video_id,
                        ),
                        NULL
                    );
                }
                    if ($wpdb->last_error) {
                        echo "Error creating table: " . $wpdb->last_error;
                    } else {
                        echo "Table created successfully.";
                    }
                }
                else{
                    echo "Error, no video_id";
                }
                wp_redirect($_SERVER['REQUEST_URI']);
                exit;
            }
    }

    public function show_videos()
    {
        Global $wpdb;
        $table_name = $wpdb->prefix . 'video_submission';
        
        $video_ids = $wpdb->get_col("SELECT submission_text FROM $table_name");

        echo '<div id="videos-here">';
    
        // div for each player
        foreach ($video_ids as $index => $video_id) {
            echo "<div id='player$index' data-video-id='$video_id' class='youtube-player' loading='lazy'></div>";
        }

        echo '</div>';

        // Pass the video IDs to JavaScript
        echo "<script>
        var videoIds = " . json_encode($video_ids) . ";
        </script>";
        ?>
        <script>
            // Load the IFrame Player API asynchronously
            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            // Creates an <iframe> (and YouTube player) for each video after the API code downloads
            function onYouTubeIframeAPIReady() {
                videoIds.forEach((vidId, index) => {
                    new YT.Player(`player${index}`, {
                        height: '390',
                        width: '640',
                        videoId: vidId,
                        playerVars: {
                            'playsinline': 1
                        }
                    });
                });
                videoIds.forEach(videoId => {
                    console.log(videoId);
                    document.write(videoId + "<br>");
                });
            }
        </script>
        <?php 
    }


}

new LifePerformances();