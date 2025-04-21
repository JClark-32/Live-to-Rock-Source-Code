<?php
/**
 * Plugin Name: BackStagePass Plugin
 * Description: Adds podcast playlist integration
 * Authors: Live to Rock Capstone Team
 * Version: 0.1.0
 * Text Domain: ltr-podcast-plugin
 */

// Prevent public user access to .php file
if ( !defined('ABSPATH') ) { exit; }

Class BackStagePass{
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
    
        wp_redirect(admin_url('admin.php?page=back_stage_pass&updated=true'));
        exit;
    }
    
    public function podcast_id() {
        if (isset($_POST['ltr-playlist-url'])) {
            $playlist = sanitize_text_field($_POST['ltr-playlist-url']);
            update_option('ltr_playlist_url', $playlist);
        }
    }
    
    
    function wporg_options_page() {
        add_menu_page(
            'Back Stage Pass',
            'Back Stage Pass',
            'manage_options',
            'back_stage_pass',
            array($this, 'wporg_options_page_html'),
            'dashicons-microphone',
            null
        );
    }

    public function podcast_shortcode() {
        $current_page = isset($_GET['podcast_page']) ? max(1, intval($_GET['podcast_page'])) : 1;
        $videos_per_page = 10;
    
        $playlist = get_option('ltr_playlist_url', '');
        $api_key = 'AIzaSyA7ySihnnPwlkJh9H4azwqTpJsMM8Gs5AM';
        $videos = $this->get_youtube_playlist_videos($playlist, $api_key);
    
        if (is_wp_error($videos)) {
            return '<p>Error fetching playlist: ' . esc_html($videos->get_error_message()) . '</p>';
        }
    
        $total_videos = count($videos);
        $total_pages = ceil($total_videos / $videos_per_page);
        $offset = ($current_page - 1) * $videos_per_page;
        $paged_videos = array_slice($videos, $offset, $videos_per_page);
    
        ob_start();
        
        echo '<div align="center">';

        foreach ($paged_videos as $video) {
            $title = esc_html($video['snippet']['title']);
            $video_id = esc_attr($video['snippet']['resourceId']['videoId']);
    
            echo "<div style='margin-bottom:20px;'>";
            echo "<h4>$title</h4>";
            echo "<iframe loading='lazy' referrerpolicy='strict-origin-when-cross-origin' width='560' height='315' src='https://www.youtube-nocookie.com/embed/$video_id' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
            echo "</div>";
        }

        echo '</div>';
    
        echo '<div class="podcast-pagination" style="margin-top: 20px; text-align:center;">';
    
        $base_url = remove_query_arg('podcast_page');
        $connector = strpos($base_url, '?') !== false ? '&' : '?';
    
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            echo '<a href="' . esc_url($base_url . $connector . 'podcast_page=' . $prev_page) . '">← Previous</a> ';
        }
    
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i === $current_page) {
                echo '<strong style="margin: 0 5px;">' . $i . '</strong>';
            } else {
                echo '<a href="' . esc_url($base_url . $connector . 'podcast_page=' . $i) . '" style="margin: 0 5px;">' . $i . '</a>';
            }
        }
    
        if ($current_page < $total_pages) {
            $next_page = $current_page + 1;
            echo ' <a href="' . esc_url($base_url . $connector . 'podcast_page=' . $next_page) . '">Next →</a>';
        }
    
        echo '</div>';
    
        return ob_get_clean();
    }
    
    

    public function get_youtube_playlist_videos($playlist_id, $api_key) {
        $videos = [];
        $page_token = '';
        $base_url = 'https://www.googleapis.com/youtube/v3/playlistItems';
    
        if (strpos($playlist_id, 'list=') !== false) {
            parse_str(parse_url($playlist_id, PHP_URL_QUERY), $query_vars);
            $playlist_id = $query_vars['list'] ?? $playlist_id;
        }
    
        do {
            $response = wp_remote_get($base_url . '?' . http_build_query([
                'part'       => 'snippet',
                'playlistId' => $playlist_id,
                'maxResults' => 50,
                'pageToken'  => $page_token,
                'key'        => $api_key,
            ]));
    
            if (is_wp_error($response)) {
                return $response;
            }
    
            $body = json_decode(wp_remote_retrieve_body($response), true);
    
            if (!empty($body['items'])) {
                $videos = array_merge($videos, $body['items']);
            }
    
            $page_token = $body['nextPageToken'] ?? null;
    
        } while ($page_token);
    
        return $videos;
    }
    
    

}
new BackStagePass();