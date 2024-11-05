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

        add_action('rest_api_init', array($this, 'register_rest_api'));

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

        wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', array(), null, true);

        wp_enqueue_script('bootstrap-min-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array(), null, true);

        wp_enqueue_script(
            'LifePerformancesPlugin',
            plugin_dir_url( __FILE__ ) . '/js/LifePerformancesPlugin.js',
            array('jquery'),
            1 ,
            true
        );
    }

    public function load_videosubmission()
    {?>
        <div class="video-submission">
            <h2>Post Your Life Performance?</h2>
            <p>Paste a link to your video here</p>

            <form id="video-link">
                <div class="input">
                    <input type="link" placeholder="YouTube link here">
                </div>
                <div class="submit">
                    <button type="submit" class="submit btn">Submit</button>
                </div>
            </form>
        </div>
    <?php }

    public function load_shortcode()
    {?>
        <div id = "video-group"class = "form-group">
            <div>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/j_S0upmiG7Q?si=ayY4EpAj1hDs7z6v" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </div>
    <?php }


    public function load_scripts()
        {?>

            <script>

            var nonce = '<?php echo wp_create_nonce('wp_rest');?>';

                (function($){
                    $('video-submission').submit(function(event){
                    
                        event.preventDefault();
                        var form = $(this).serialize();

                        $.ajax({

                            method:"post",
                            url: '<?php echo get_rest_url(null, 'video_submission/v1/link');?>'
                            headers: {'X-WP-Nonce': nonce},
                            data: form


                        });
                
                });
                })(jQuery)
                
            </script>

        <?php }

    public function register_rest_api()
    {

        register_rest_route('video_submission/v1', 'link', array(

            'methods' => 'POST',
            'callback' => array($this, 'handle_links')

        ));

    }

    public function handle_links($data){
        $headers = $data->get_headers();
        $params = $data->get_params();
        $nonce = $headers['x_wp_nonce'][0];

        if(!wp_verify_nonce($nonce, 'wp_rest')){
            return new WP_REST_Response('Message not sent', 422);
        }

        $post_id = wp_insert_post([
            'post_type' => 'video_submission_form',
            'post_title' => 'Submission enquiry',
            'public_status' => 'Submit'
        ]);
        if ($post_id){
            return new WP_REST_Response('Thank you for your submission', 200);
        }
    }
}
new LifePerformances();