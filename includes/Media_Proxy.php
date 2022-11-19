<?php
namespace FTF_TEmbeds;
use FTF_TEmbeds\Helpers;

$dir = plugin_dir_path( __FILE__ );

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Media_Proxy {
    protected $archival_mode;

    function __construct(){
        $this->archival_mode = get_option('ftf_alt_embed_tweet_archival_mode') === 'on' ? true : false; 
        add_action('rest_api_init', array($this, 'register_media_proxy_endpoint'));
        add_action('wp_ajax_nopriv_ftf_media_proxy', array($this, 'media_proxy'), 1000);
    }

    public function register_media_proxy_endpoint(/* $_REQUEST */) {
        register_rest_route('ftf', 'proxy-media', array(
            'methods' => \WP_REST_Server::READABLE,
            'permission_callback' => '__return_true',
            'callback' => array($this, 'proxy_media'),
       ));
    }

    public function proxy_media(\WP_REST_Request $request){
        $url = $request['url'];

        if (strpos($url, 'profile_images')){
            $folder_name = 'profile_images';
        } else {
            $folder_name = 'media';
        }

        if ($this->archival_mode){
            $dir = plugin_dir_path( __FILE__ ) . "../$folder_name";
            $file_name = basename($url);
            $file_path = "$dir/$file_name";
    
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }

        if ($this->archival_mode && file_exists($file_path)){
    
            // Helpers::log_this('debug:proxy_media', array(
            //     'url' => $url,
            //     'file_name' => $file_name,
            //     'file_path' => $file_path,
            //     'file_exists' => 'true',
            // ));
    
            $image_info = getimagesize($file_path);
            header("Content-type: {$image_info['mime']}");
            echo file_get_contents($file_path);

        } else {
            $remote_response = wp_remote_get($url);

            if ($this->archival_mode){
                file_put_contents($file_path, $remote_response['body']);
            }
    
            // Helpers::log_this('debug:proxy_media', array(
            //     'url' => $url,
            //     'file_name' => $file_name,
            //     'remote_response' => $remote_response,
            // ));
    
            header('Content-Type: ' . $remote_response['headers']['content-type']);
            echo $remote_response['body'];
        }

        exit();
    }    
}
