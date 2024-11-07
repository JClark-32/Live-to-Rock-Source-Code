<?php
/**
 * Plugin Name: Life Performances Plugin
 * Description: Adds YT iframe embeds from user submissions on Life Performances page
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: life-performances-plugin
 */

 // Prevent public user access to .php file
 if ( !defined('ABSPATH') ) { exit; }

 class LifePerformances {

    // PROPERTIES
    
    // METHODS
    public function __construct() {
        // Load assets ----------
        # Action hook WP calls when its time to load scripts & styles for front end
        add_action('wp_enqueue_scripts', array( $this, 'load_assets' ) );

        // SHORTCODES ----------
        # Creates a form for submitting a YT video URL to
        add_shortcode('ltr-video-submission', array ( $this, 'load_video_submission') );
        # (previously (same) previously 'load_videosubmission')
        
        // Load js ----------
        # (add_action 'wp_footer', 'load_scripts' removed; unused, causes errors)

        # Nabs URL from submission and saves in database for later use
        add_action('init', array( $this, 'video_id' ) );

        #
        add_shortcode('ltr-videos', array ( $this, 'show_videos' ) );
            
    }

    // create_video_submission() removed (used for rest api but no ref in code)

    // Loads assets
    # Nothing in this function is ever really used because our .css and .js files are empty
    public function load_assets() {
        # Loads css file (empty)
        wp_enqueue_style(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/css/LifePerformancesPlugin.css',
            array(),
            1,
            'all'
        );

        # Loads fs file (empty)
        wp_enqueue_script(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/js/LifePerformancesPlugin.js',
            array(),
            1,
            'all'
        );
    }
    
    // Loads a submission form that a user can paste a YT url which will be stored in database
    public function load_video_submission() {
    ob_start();
    ?>
        <div id="ltr-video-submission">
            <h2>Post Your Life Performance?</h2>
            <p>Paste YouTube URL here:</p>
            <form id="ltr-video-link" method="post">
                <div class="input">
                    <input type="url" name="ltr-video-url" placeholder="YouTube URL" required>
                </div>
                <div id="ltr-submit">
                    <button type="submit" name="ltr-submit-video-button" class="submit-btn">Submit!</button>
                </div>
            </form>
        </div>
        <?php
    return ob_get_clean();
    }
    
    // test_table() removed

    public function video_id() {
        global $wpdb;

        // Checking to see if the request was made via post and if it was from the right spot
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ltr-video-url'])) {

            // Checking again for a post call
            if (isset($_POST['ltr-submit-video-button'])) {
                $video_url = sanitize_text_field($_POST['ltr-video-url']);
            } else {
                echo "Error";
            }

            preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $video_url, $matches);
            $video_id = $matches[1];

            if ($video_id) { # if true
                $table_name = $wpdb->prefix . 'video_submission';

                // Create db table if doesn't already exist
                $sql = "CREATE TABLE IF NOT EXISTS $table_name(\n"
                . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
                . "    submission_text VARCHAR(11) NOT NULL,\n"
                . "    PRIMARY KEY(id)\n"
                . ");";
                
                # ????
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);

                if (isset($_POST['ltr-submit-video-button'])) {
                    // Data is inserted into the created or existing table
                    $wpdb->insert(
                        $table_name,
                        array(
                            'submission_text' => $video_id,
                        ),
                        NULL
                    );
                }

                if ($wpdb->last_error) {
                    echo "\nError creating table: " . $wpdb->last_error . "\nContact admin.";
                } # Else statement removed so no text is outputted to site

            } else {
                echo "\nError: no video ID.";
            }

            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }
    public function show_videos() {
        global $wpdb;
        ob_start();
        $table_name = $wpdb->prefix . 'video_submission';

        $video_ids = $wpdb->get_col("
            SELECT submission_text
            FROM $table_name"
        );

        // TO-DO: CHANGE THIS! This is why it's just going to the top of the page!!

        # Opening tag -
        echo '<div id="ltr-videos-here">';
        # Middle bit -
        foreach ($video_ids as $index => $video_id) {
            echo "<div id='player$index' data-video-id='$video_id' class='youtube-player' loading='lazy'></div>";
        }
        # Closing tag -
        echo '</div>';

        // Pass the video IDs to JavaScript
        echo "<script> var videoIds = " . json_encode($video_ids) . "; </script>";
        
        # TO-DO: fix below code
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
                    // document.write == BAD >:( so commented out for now
                    // document.write(videoId + "<br>");
                });
            }
        </script>
        <?php
        return ob_get_clean();
    }
    
}

new LifePerformances();