<?php
namespace FTF_TEmbeds;
use FTF_TEmbeds\Helpers;

$dir = plugin_dir_path(__FILE__);

if (!function_exists('file_get_html')){
    require_once $dir . 'simple_html_dom.php';
}

class Media_Proxy {
    protected $archival_mode;

    function __construct(){
        $this->archival_mode = get_option('ftf_alt_embed_tweet_archival_mode') === 'on' ? true : false;
        add_action('rest_api_init', array($this, 'register_media_proxy_endpoint'));
    }

    public function register_media_proxy_endpoint(/* $_REQUEST */) {
        register_rest_route('ftf', 'proxy-media', array(
            'methods' => \WP_REST_Server::READABLE,
            'permission_callback' => '__return_true',
            'callback' => array($this, 'proxy_media'),
      ));
    }

    protected function is_safe_file_name($file_name){
        return (bool) preg_match('/^[A-Za-z0-9_-][A-Za-z0-9._-]{0,120}\.(jpe?g|png|gif|webp|mp4|m3u8)(\?[A-Za-z0-9=&_.:-]{1,60})?$/i', $file_name);
    }

    protected function mime_for_name($file_name){
        $extension = strtolower(pathinfo(explode('?', $file_name)[0], PATHINFO_EXTENSION));
        $types = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'm3u8' => 'application/vnd.apple.mpegurl',
        );
        $mime = 'application/octet-stream';

        if (isset($types[$extension])){
            $mime = $types[$extension];
        }

        return $mime;
    }

    protected function validated_mime($body, $remote_type){
        $remote_type = strtolower(trim(explode(';', (string) $remote_type)[0]));
        $images = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif');
        $playlists = array('application/vnd.apple.mpegurl', 'application/x-mpegurl', 'audio/x-mpegurl');
        $mime = false;

        if (in_array($remote_type, $images, true)){
            $info = @getimagesizefromstring($body);

            if ($info && in_array($info['mime'], $images, true)){
                $mime = $info['mime'];
            }
        } elseif ($remote_type === 'video/mp4' && substr($body, 4, 4) === 'ftyp'){
            $mime = 'video/mp4';
        } elseif (in_array($remote_type, $playlists, true) && strpos($body, '#EXTM3U') === 0){
            $mime = 'application/vnd.apple.mpegurl';
        }

        return $mime;
    }

    protected function prepare_cache_folder($dir){
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
        }

        if (is_dir($dir) && !file_exists("$dir/.htaccess")){
            file_put_contents("$dir/.htaccess", "Require all denied\n");
        }
    }

    public function proxy_media(\WP_REST_Request $request){
        $url = wp_http_validate_url(esc_url_raw((string) $request['url'], array('http', 'https')));
        $status = 200;
        $mime = '';
        $body = '';

        if (!$url){
            $status = 400;
        } else {
            if (false !== strpos($url, 'profile_images')){
                $folder_name = 'profile_images';
            } else {
                $folder_name = 'media';
            }

            $file_name = basename($url);
            $can_archive = $this->archival_mode && $this->is_safe_file_name($file_name);
            $file_path = '';

            if ($can_archive){
                $dir = plugin_dir_path(__FILE__) . "../$folder_name";
                $file_path = "$dir/$file_name";
                $this->prepare_cache_folder($dir);
            }

            if ($can_archive && file_exists($file_path) && filesize($file_path) > 0){

                // Helpers::log_this('debug:proxy_media', array(
                //     'url' => $url,
                //     'file_name' => $file_name,
                //     'file_path' => $file_path,
                //     'file_exists' => 'true',
                // ));

                $mime = $this->mime_for_name($file_name);
                $body = file_get_contents($file_path);

            } else {
                $remote_response = wp_safe_remote_get($url, array(
                    'timeout' => 20,
                    'limit_response_size' => 64 * 1024 * 1024,
                ));

                // Helpers::log_this('debug:proxy_media', array(
                //     'url' => $url,
                //     'file_name' => $file_name,
                //     'remote_response' => $remote_response,
                // ));

                if (is_wp_error($remote_response) || 200 !== (int) wp_remote_retrieve_response_code($remote_response)){
                    $status = 404;
                } else {
                    $body = wp_remote_retrieve_body($remote_response);
                    $mime = $this->validated_mime($body, wp_remote_retrieve_header($remote_response, 'content-type'));

                    if (!$mime){
                        $status = 415;
                    } elseif ($can_archive){
                        file_put_contents($file_path, $body);
                    }
                }
            }
        }

        if ($status === 200){
            header('Content-Type: ' . $mime);
            header('X-Content-Type-Options: nosniff');
            header("Content-Security-Policy: default-src 'none'; sandbox");
            echo $body;
        } else {
            status_header($status);
        }

        exit();
    }
}
