<?php
namespace FTF_TEmbeds;

$dir = plugin_dir_path( __FILE__ );

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Media_Proxy {
    function __construct(){
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
        $data = $request->get_params();
        $url= $request['url'];
        $remote_response = wp_remote_get($url);
        header('Content-Type: ' . $remote_response['headers']['content-type']);
        echo $remote_response['body'];
        exit();
    }    
}
