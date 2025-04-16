<?php
/**
 * Plugin Name: LtR Podcasts Plugin
 * Description: Adds podcast playlist integration
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: ltr-podcast-plugin
 */

// Prevent public user access to .php file
if ( !defined('ABSPATH') ) { exit; }

Class LtRPodcasts{
    public function __construct(){
        add_action( 'admin_menu', array($this, 'wporg_options_page'));
        add_action('init', array( $this,'podcast_id') );
        add_action('admin_post_save_ltr_playlist', array($this, 'save_playlist_url'));
        //add_action('init', array($this, 'get_youtube_playlist_videos'));
        add_shortcode('ltr-podcasts', array ( $this, 'podcast_shortcode') );
    }

    
    function wporg_options_page_html() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                <textarea name="ltr-playlist-url" rows="5" cols="40"><?php echo esc_textarea(get_option('ltr_playlist_url', '')); ?></textarea>
                <input type="hidden" name="action" value="save_ltr_playlist">
                <?php submit_button('Save Playlist URL'); ?>
            </form>
        </div>
        <?php
    }

    public function save_playlist_url() {
        if (current_user_can('manage_options') && isset($_POST['ltr-playlist-url'])) {
            $playlist = sanitize_text_field($_POST['ltr-playlist-url']);
            update_option('ltr_playlist_url', $playlist);
        }
    
        // Redirect back to the settings page
        wp_redirect(admin_url('admin.php?page=ltr_podcasts&updated=true'));
        exit;
    }
    
    public function podcast_id() {
        if (isset($_POST['ltr-playlist-url'])) {
            // Sanitize and save the value
            $playlist = sanitize_text_field($_POST['ltr-playlist-url']);
            update_option('ltr_playlist_url', $playlist);
        }
    }
    
    
    function wporg_options_page() {
        add_menu_page(
            'LtR Podcasts',
            'LtR Podcasts',
            'manage_options',
            'ltr_podcasts',
            array($this, 'wporg_options_page_html'),
            'dashicons-microphone',
            null
        );
    }

    public function podcast_shortcode() {
        $playlist = get_option('ltr_playlist_url', '');
        $api_key = 'AIzaSyA7ySihnnPwlkJh9H4azwqTpJsMM8Gs5AM';
        $videos = $this->get_youtube_playlist_videos($playlist, $api_key);
        ob_start();
        foreach ( $videos as $video ) {
            $title = esc_html( $video['snippet']['title'] );
            $video_id = $video['snippet']['resourceId']['videoId'];
            //$thumbnail = esc_url( $video['snippet']['thumbnails']['medium']['url'] );
    
            echo "<div style='margin-bottom:20px;'>";
            echo "<h4>$title</h4>";
            echo "<iframe loading='lazy' referrerpolicy='strict-origin-when-cross-origin' width='560' height='315' 
            src='https://www.youtube-nocookie.com/embed/$video_id' 
            frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen>
            </iframe>";
            echo "</div>";
        }
        return ob_get_clean();
    }

    function get_youtube_playlist_videos( $playlist_url, $api_key ) {
        $url_parts = wp_parse_url( $playlist_url );
        if ( empty( $url_parts['query'] ) ) {
            return new WP_Error( 'invalid_url', 'Invalid YouTube playlist URL.' );
        }
    
        parse_str( $url_parts['query'], $query_params );
        if ( empty( $query_params['list'] ) ) {
            return new WP_Error( 'missing_playlist_id', 'No playlist ID found in URL.' );
        }
    
        $playlist_id = sanitize_text_field( $query_params['list'] );
    
        $api_url = add_query_arg( array(
            'part'       => 'snippet',
            'playlistId' => $playlist_id,
            'maxResults' => 20,
            'key'        => $api_key,
        ), 'https://www.googleapis.com/youtube/v3/playlistItems' );
    
        $response = wp_remote_get( $api_url );
    
        if ( is_wp_error( $response ) ) {
            return $response;
        }
    
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
    
        if ( isset( $data['items'] ) ) {
            return $data['items'];
        } else {
            return new WP_Error( 'api_error', 'Could not fetch playlist items.' );
        }
    }
    

}
new LtRPodcasts();