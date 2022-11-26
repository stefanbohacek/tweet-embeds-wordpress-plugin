<?php
namespace FTF_TEmbeds;

use FTF_TEmbeds\Database;
use FTF_TEmbeds\Helpers;

$dir = plugin_dir_path(__FILE__);

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Embed_Tweets {
    protected $db;
    protected $twitter_credentials;
    protected $archival_mode;

    function __construct(){
        $this->archival_mode = get_option('ftf_alt_embed_tweet_archival_mode') === 'on' ? true : false; 
        $this->db = new Database();
        $this->twitter_credentials = array(
            'twitter_api_consumer_key' => get_option('ftf_alt_embed_tweet_twitter_api_consumer_key'),
            'twitter_api_consumer_secret' => get_option('ftf_alt_embed_tweet_twitter_api_consumer_secret'),
            'twitter_api_oauth_access_token' => get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token'),
            'twitter_api_oauth_access_token_secret' => get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret'),
       );

        add_action('wp_ajax_ftf_embed_tweet', array($this, 'embed_tweet'), 1000);
        add_action('wp_ajax_nopriv_ftf_embed_tweet', array($this, 'embed_tweet'), 1000);
    }

    function create_bearer_token(){
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->twitter_credentials['twitter_api_consumer_key'] . ':' . $this->twitter_credentials['twitter_api_consumer_secret'])
          ),
            'body' => array('grant_type' => 'client_credentials')
      );

        $response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);

        return json_decode($response['body']);
    }

    function call_twitter_api($endpoint = 'account/verify_credentials', $data = null){
        $resp_data = array();

        if (
            !empty($this->twitter_credentials['twitter_api_consumer_key']) &&
            !empty($this->twitter_credentials['twitter_api_consumer_secret']) &&
            !empty($this->twitter_credentials['twitter_api_oauth_access_token']) &&
            !empty($this->twitter_credentials['twitter_api_oauth_access_token_secret'])
       ){
            $version = '2';
            $api_endpoint = 'https://api.twitter.com/' . $version . '/' . $endpoint;
            $token = self::create_bearer_token();

            if (isset($token->token_type) && $token->token_type == 'bearer'){

                $args = array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $token->access_token
               )
           );

                $response = wp_remote_get($api_endpoint, $args);
                // error_log(print_r(array(
                //     'response' => $response['body']
                //), true));

            } else {
                // error_log(print_r(array(
                //     'token errors' => $token->errors
                //), true));
                $this->update_error_log($token->errors);
            }

            $resp_data = $response['body'];            
        }

        return $resp_data;
    }

    function get_tweets($tweet_ids){
        $tweet_data = array();
        $cache_expiration = get_option('ftf_alt_embed_cache_expiration');

        if (empty($cache_expiration)){
            $cache_expiration = 30;
        }

        foreach ($tweet_ids as $index => $tweet_id) {
            if (empty($tweet_id)){
                unset($tweet_ids[$index]);
            } else {
                $cache_key = "tweet_data:" . $tweet_id;
                $tweet_data = wp_cache_get($cache_key, 'ftf_alt_embed_tweet');

                if ($tweet_data !== false){
                    unset($tweet_ids[$index]);
                    $data[] = $tweet_data;
                }
            }
        }

        $post_fields = array(
            'ids' => implode(',', $tweet_ids),
            'expansions' => 'author_id,attachments.media_keys,referenced_tweets.id,attachments.poll_ids',
            'tweet.fields' => 'attachments,entities,author_id,conversation_id,created_at,id,in_reply_to_user_id,lang,referenced_tweets,source,text,public_metrics',
            'user.fields' => 'id,name,username,profile_image_url,verified',
            'media.fields' => 'media_key,preview_image_url,variants,type,url,width,height,alt_text'
       );

        $response = self::call_twitter_api('tweets?' . str_replace('%2C', ',', http_build_query($post_fields)));

        // Helpers::log_this('debug:get_tweets', array(
        //     'post_fields' => $post_fields,
        //     'response' => $response,
        //));

        $response_array = json_decode(rtrim($response, "\0"));

        if ($this->archival_mode){
            foreach ($response_array->errors as $error) {
                if ($error->title === 'Not Found Error'){
                    $tweet = $this->db->get_tweet($error->resource_id);
    
                    // Helpers::log_this('debug:tweet_not_found', array(
                    //     'tweet' => $tweet,
                    //));
    
                    if ($tweet){
                        $tweet_data[] = json_decode($tweet->tweet_data);
                    }
                }
            }
        }

        foreach ($response_array->data as $tweet) {
            $tweet->users = array();

            foreach($response_array->includes->users as $user){
                if ($tweet->author_id === $user->id){
                    $tweet->users[] = $user;
                }
            }
            
            $tweet->media = array();

            if (property_exists($tweet, 'attachments') && property_exists($tweet->attachments, 'media_keys')){
                foreach ($tweet->attachments->media_keys as $media_key) {
                    foreach($response_array->includes->media as $media){
                        if ($media_key === $media->media_key){
                            $tweet->media[] = $media;
                        }
                    }
                }
            }

            $tweet->polls = array();

            if (property_exists($tweet, 'attachments') && property_exists($tweet->attachments, 'poll_ids')){
                foreach ($tweet->attachments->poll_ids as $poll_id) {
                    foreach($response_array->includes->polls as $poll){
                        if ($poll_id === $poll->id){
                            $tweet->polls[] = $poll;
                        }
                    }
                }
            }

            $cache_key = "tweet_data:" . $tweet->id;
            wp_cache_set($cache_key, $tweet, 'ftf_alt_embed_tweet', ($cache_expiration * MINUTE_IN_SECONDS));

            if ($this->archival_mode){
                $this->db->add_tweet($tweet->conversation_id, json_encode($tweet));
            }

            $tweet_data[] = $tweet;
        }

        // Helpers::log_this('debug:tweet_data', array(
        //     'tweet_data' => $tweet_data,
        //));
        
        return $tweet_data;
    }

    function embed_tweet(){
        $tweet_data = array();

        if (array_key_exists('tweet_ids', $_POST)){
            $tweet_ids = sanitize_text_field($_POST[ 'tweet_ids' ]);
            $tweet_ids = explode(',', $tweet_ids);

            if (!empty($tweet_ids)){
                $tweet_data = $this->get_tweets($tweet_ids);
            }
        }
        wp_send_json($tweet_data);
    }

    function create_error_log_table(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'ftf_alt_embed_tweet_error_log';

        $sql = "CREATE TABLE `{$table_name}` (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            code varchar(10),
            message varchar(255),
            label varchar(255),
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id)
      ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        return empty($wpdb->last_error);
    }

    function update_error_log($errors){
        $this->create_error_log_table();
        global $wpdb;
        $table_name = $wpdb->prefix . 'ftf_alt_embed_tweet_error_log';

        foreach ($errors as $error) {
            $wpdb->insert($table_name, array(
                'code' => $error->code,
                'message' => $error->message,
                'label' => $error->label,
                'created_at' => current_time('mysql'),
          ), array(
                '%s',
                '%s',
                '%s',
                '%s'
          ));
        }

        $error_log = $wpdb->get_results("SELECT * FROM $table_name");

        // error_log(print_r(array(
        //     'error_log' => $error_log
        //), true));

        if (count($error_log) > 10){
            $sql = "";

            $wpdb->query(
                $wpdb->prepare(
                    "
                        DELETE FROM $table_name
                        WHERE id < %d
                    ",
                    $error_log[ count($error_log) - 10 ]->id
              )
          );
        }        
    }
}
