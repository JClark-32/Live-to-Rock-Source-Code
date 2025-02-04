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
    private $email_status = ''; // To store email sending status

    // METHODS
    public function __construct() {
        // Load assets ----------
        add_action('wp_enqueue_scripts', array( $this, 'load_assets' ) );

        // SHORTCODES ----------
        add_shortcode('ltr-video-submission', array ( $this, 'blank_shortcode') );
        add_action('init', array( $this, 'video_id' ) );
        add_shortcode('ltr-delete-video', array( $this, 'blank_shortcode' ) );
        add_shortcode('ltr-videos', array ( $this, 'show_videos' ) );
        add_shortcode('ltr-email-status', array( $this, 'email_status_shortcode' ) ); // New shortcode to display email status

        add_action('plugins_loaded', array( $this,'wporg_add_video_submission_ability') );

        // Handle approve and delete actions
        add_action('init', array( $this, 'handle_video_actions') );
    }

    // Loads assets
    public function load_assets() {
        wp_enqueue_style(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/css/LifePerformancesPlugin.css',
            array(),
            1,
            'all'
        );
    }

    // Loads a blank section that users who do not have the permission to post videos see
    public function blank_shortcode(){
        ob_start();
        ob_get_clean();
    }

    // Loads a submission form that a user can paste a YT url, which will be stored in database
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

    // Changes the shortcode to show the video submission if the user can edit posts, i.e., Editor and above
    function wporg_add_video_submission_ability() {
        if ( current_user_can('edit_others_posts')){
            remove_shortcode('ltr-delete-video');
            add_shortcode('ltr-delete-video', array($this, 'delete_videos'));
            remove_shortcode('ltr-video-submission');
            add_shortcode('ltr-video-submission', array( $this,'load_video_submission') );
        }
    }

    public function video_id() {
        global $wpdb;
        $urlIsPosting = isset($_POST['ltr-video-url']);
        $isRequesting = $_SERVER['REQUEST_METHOD'] === 'POST';
        
        if ($isRequesting && $urlIsPosting) {
            $submitIsPosting = isset($_POST['ltr-submit-video-button']);
            if ($submitIsPosting) {
                $video_url = sanitize_text_field($_POST['ltr-video-url']);
            } else {
                error_log("Submit button is not posting");
            }
        
            // Get video ID from YouTube URL using regular expression
            $video_id = $this->check_for_post($submitIsPosting, $video_url); 

            if ($video_id) {
                $table_name = $wpdb->prefix . 'video_submission';
                $sql = "CREATE TABLE IF NOT EXISTS $table_name (\n"
                    . "    id INT(9) NOT NULL AUTO_INCREMENT,\n"
                    . "    submission_text VARCHAR(11) NOT NULL,\n"
                    . "    approved TINYINT(1) DEFAULT 0,\n"
                    . "    PRIMARY KEY(id)\n"
                    . ");";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);

                $column_check = $wpdb->get_results("DESCRIBE $table_name approved");
                if (empty($column_check)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN approved TINYINT(1) DEFAULT 0");
                }

                if ($wpdb->last_error) {
                    error_log("Error creating or altering table: " . $wpdb->last_error . " Contact admin.");
                }

                // Insert data if able
                $this->enter_data_if_able($submitIsPosting, $table_name, $video_id);

                // Send email when a new video is submitted
                $this->send_video_submission_email($video_url, $video_id); // Send email
            } else {
                error_log("Error: no video ID.");
            }
        
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }

    // Insert data into the database
    function insert_data($table_name, $video_id) {
        global $wpdb;
        $wpdb->insert($table_name, array('submission_text' => $video_id), NULL);
    }

    // Check if submit is posting
    function check_for_post($submitIsPosting, $video_url) {
        if ($submitIsPosting) {
            preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $video_url, $matches);
            return $matches[1];
        } else {
            error_log("Submit button is not posting");
        }
    }

    // Enter data into the database if able
    function enter_data_if_able($submitIsPosting, $table_name, $video_id) {
        global $wpdb;

        if ($submitIsPosting) {
            // Data is inserted into the created or existing table
            $this->insert_data($table_name, $video_id);
        }
        if ($wpdb->last_error) {
            error_log("Error creating table: " . $wpdb->last_error . "Contact admin.");
        }
    }

    public function delete_videos() {
        global $wpdb;
        $delBtnIsPosting = isset($_POST['ltr-delBtn']);
        $isRequesting = $_SERVER['REQUEST_METHOD'] === 'POST';
        ob_start();

        if ($isRequesting && $delBtnIsPosting) {
            $videoIsPosting = isset($_POST['videoInput']);
            if ($videoIsPosting) {
                $video_id = sanitize_text_field($_POST['videoInput']);
                error_log("Deleting video ID: " . $video_id);

                $table_name = $wpdb->prefix . 'video_submission';
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_name 
                    WHERE submission_text = %s",
                    $video_id
                ));
    
                // Check for SQL errors
                $this->check_for_sql_err($video_id);
            } else {
                error_log("No videoInput received in the request.");
            }
            wp_redirect(add_query_arg('message', 'video_deleted', wp_get_referer()));
            exit;
        }
        ob_get_clean();
    }

    // Check for SQL errors
    function check_for_sql_err($video_id) {
        global $wpdb;

        // Check for any SQL errors
        if ($wpdb->last_error) {
            error_log("Error deleting video: " . $wpdb->last_error);
        } else {
            error_log("Video successfully deleted with ID: " . $video_id);
        }
    }

    public function show_videos() {
        global $wpdb;
        ob_start();
        $table_name = $wpdb->prefix . 'video_submission';
        
        if (isset($_POST['ltr-approveBtn'])) {
            $video_id = sanitize_text_field($_POST['videoInput']);
            if ($video_id) {
                $wpdb->update(
                    $table_name,
                    array('approved' => 1),
                    array('submission_text' => $video_id),
                    array('%d'),
                    array('%s')
                );
                error_log("Video approved with ID: " . $video_id);
            }
        }

        $video_data = $wpdb->get_results("SELECT id, submission_text, approved FROM $table_name");

        echo '<div id="ltr-videos-here">';

        if (empty($video_data)) {
            echo '<p>No videos available</p>';
        }

        foreach ($video_data as $index => $video) {
            $video_id = $video->submission_text;
            $approved = $video->approved;
            echo "<form id='video-posted$index' method='POST'>";
            echo "<input type='hidden' name='videoInput' value='$video_id'>";
            wp_nonce_field('approve_video_nonce', 'approve_video_nonce');
            echo "<div id='player$index' class='youtube-player' loading='lazy'></div>";
            echo "<br>";
            if (!$approved && !current_user_can('edit_others_posts')) {
                echo "<style>#video-posted$index { display: none; }</style>";
            }
            if (!current_user_can('edit_others_posts')) {
                echo "<style>#deleteButton$index { display: none; }</style>";
            }
            if (!$approved && current_user_can('edit_others_posts')) {
                echo "<button id='approveButton$index' type='submit' name='ltr-approveBtn' class='approveBtn'>Approve</button>";
            }
            echo "<button id='deleteButton$index' type='submit' name='ltr-delBtn' class='deleteBtn'>Delete?</button>";
            echo "<br>";
            echo "</form>";
        }

        echo '</div>';

        echo "<script> var videoIds = " . json_encode($video_data) . "; </script>";

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
        return ob_get_clean();
    }

    // Send email when a new video is submitted
    public function send_video_submission_email($video_url, $video_id) {
        $to = 'kcweaver2@bsu.edu';
        $subject = 'New Video Submitted for Life Performances';
        $approve_url = add_query_arg('approve_video', $video_id, get_site_url());
        $delete_url = add_query_arg('delete_video', $video_id, get_site_url());
        $message = 'A new video has been submitted for the Life Performances plugin. Here is the YouTube URL: ' . $video_url . '<br><br>';
        $message .= 'To approve the video, click <a href="' . esc_url($approve_url) . '">Approve</a><br>';
        $message .= 'To delete the video, click <a href="' . esc_url($delete_url) . '">Delete</a>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $mail_sent = wp_mail($to, $subject, $message, $headers);

        if ($mail_sent) {
            $this->email_status = 'Email sent successfully!';
        } else {
            $this->email_status = 'Failed to send email.';
        }
    }

public function handle_video_actions() {
    if (isset($_GET['approve_video']) && !isset($_GET['delete_video'])) {
        // Approve video if 'approve_video' query parameter is set, and 'delete_video' is not present
        $video_id = sanitize_text_field($_GET['approve_video']);
        $this->approve_video($video_id);
    } elseif (isset($_GET['delete_video']) && !isset($_GET['approve_video'])) {
        // Delete video if 'delete_video' query parameter is set, and 'approve_video' is not present
        $video_id = sanitize_text_field($_GET['delete_video']);
        $this->delete_video($video_id);
    }
}

    
    // Approve the video
    public function approve_video($video_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'video_submission';
        
        // Check if the video exists and update the approval status to 1 (approved)
        $wpdb->update(
            $table_name,
            array('approved' => 1), // Set approved to 1
            array('submission_text' => $video_id),
            array('%d'),
            array('%s')
        );
        
        // Redirect to a page after approval, e.g., back to the videos page
        wp_redirect(get_site_url() . '/life-performances'); // Update this URL as necessary
        exit;
    }
    
    // Delete the video
    public function delete_video($video_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'video_submission';
        
        // Check if the video exists and delete it
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE submission_text = %s",
            $video_id
        ));
    
        // Redirect to a page after deletion, e.g., back to the videos page
        wp_redirect(get_site_url() . '/life-performances'); // Update this URL as necessary
        exit;
    }
    
    // Display email status
    public function email_status_shortcode() {
        return '<div class="email-status">' . esc_html($this->email_status) . '</div>';
    }

}

new LifePerformances();
