<?php
namespace FTF_TEmbeds;

$dir = plugin_dir_path( __FILE__ );

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Embed_Tweets {
    function __construct(){
        add_action('wp_ajax_ftf_embed_tweet', array($this, 'embed_tweet'), 1000);
        add_action('wp_ajax_nopriv_ftf_embed_tweet', array($this, 'embed_tweet'), 1000);
    }

    function create_bearer_token($twitter_api_consumer_key, $twitter_api_consumer_secret){
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($twitter_api_consumer_key . ':' . $twitter_api_consumer_secret)
           ),
            'body' => array('grant_type' => 'client_credentials')
       );

        $response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);

        return json_decode($response['body']);
    }

    function call_twitter_api($endpoint = 'account/verify_credentials', $data = null){
        $version = '2';
        $data = array();

        $twitter_api_consumer_key = get_option('ftf_alt_embed_tweet_twitter_api_consumer_key');
        $twitter_api_consumer_secret = get_option('ftf_alt_embed_tweet_twitter_api_consumer_secret');
        $twitter_api_oauth_access_token = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token');
        $twitter_api_oauth_access_token_secret = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret');

        $token = self::create_bearer_token($twitter_api_consumer_key, $twitter_api_consumer_secret);
        $api_endpoint = 'https://api.twitter.com/' . $version . '/' . $endpoint;

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

        return $response['body'];
    }

    function embed_tweet(){
        $twitter_api_consumer_key = get_option('ftf_alt_embed_tweet_twitter_api_consumer_key');
        $twitter_api_consumer_secret = get_option('ftf_alt_embed_tweet_twitter_api_consumer_secret');
        $twitter_api_oauth_access_token = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token');
        $twitter_api_oauth_access_token_secret = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret');

        $include_bootstrap_styles = get_option('ftf_alt_embed_tweet_include_bootstrap_styles');
        $show_metrics = get_option('ftf_alt_embed_tweet_show_metrics');
        $cache_expiration = get_option('ftf_alt_embed_cache_expiration');

        if (empty($cache_expiration)){
            $cache_expiration = 30;
        }        

        $data = array();

        if (!empty($twitter_api_consumer_key) && !empty($twitter_api_consumer_secret) && !empty($twitter_api_oauth_access_token) && !empty($twitter_api_oauth_access_token_secret)){

            $settings = array(
                'consumer_key' => $twitter_api_consumer_key,
                'consumer_secret' => $twitter_api_consumer_secret,
                'oauth_access_token' => $twitter_api_oauth_access_token,
                'oauth_access_token_secret' => $twitter_api_oauth_access_token_secret
           );

            if (array_key_exists('tweet_ids', $_POST)){
                $tweet_ids = sanitize_text_field($_POST[ 'tweet_ids' ]);
                $tweet_ids = explode(',', $tweet_ids);

                if (!empty($tweet_ids)){
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

                    $url = 'https://api.twitter.com/2/tweets';
                    $request_method = 'GET';

                    $post_fields = array(
                        'ids' => implode(',', $tweet_ids),
                        'expansions' => 'author_id,attachments.media_keys,referenced_tweets.id,attachments.poll_ids',
                        'tweet.fields' => 'attachments,entities,author_id,conversation_id,created_at,id,in_reply_to_user_id,lang,referenced_tweets,source,text,public_metrics',
                        'user.fields' => 'id,name,username,profile_image_url,verified',
                        'media.fields' => 'media_key,preview_image_url,variants,type,url,width,height,alt_text'
                   );

                    $response = self::call_twitter_api( 'tweets?' . str_replace('%2C', ',', http_build_query($post_fields)));

                    // error_log(print_r(array(
                    //     'Twitter API response' => $response
                    //), true));

                    $response_array = json_decode(rtrim($response, "\0"));
                    $tweet_data = array();

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
                        $tweet_data[] = $tweet;
                    }

                    $data = array_merge($data, $tweet_data);
                }
            }
        }
        // error_log(print_r($data, true));
        wp_send_json($data);
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
