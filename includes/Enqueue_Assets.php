<?php
namespace FTF_TEmbeds;

$dir = plugin_dir_path(__FILE__);

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Enqueue_Assets {
    function __construct(){
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('script_loader_tag', array($this, 'add_type_attribute'), 10, 3);
    }

    function enqueue_scripts(){
        $include_bootstrap_styles = get_option('ftf_alt_embed_tweet_include_bootstrap_styles', 'on');
        $show_metrics = get_option('ftf_alt_embed_tweet_show_metrics', 'on');

        $plugin_dir_url = plugin_dir_url(__FILE__);
        $plugin_dir_path = plugin_dir_path(__FILE__);

        $js_url = $plugin_dir_url . '../dist/js/scripts.js';
        $js_path = $plugin_dir_path . '../dist/js/scripts.js';

        $use_api = true;

        $twitter_api_consumer_key = get_option('ftf_alt_embed_tweet_twitter_api_consumer_key');
        $twitter_api_consumer_secret = get_option('ftf_alt_embed_tweet_twitter_api_consumer_secret');
        $twitter_api_oauth_access_token = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token');
        $twitter_api_oauth_access_token_secret = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret');


        if (empty($twitter_api_consumer_key) || empty($twitter_api_consumer_secret) || empty($twitter_api_oauth_access_token) || empty($twitter_api_oauth_access_token_secret)){
            $use_api = false;
        }

        wp_register_script('ftf-ate-frontend-js', $js_url, array(), filemtime($js_path), true);
        wp_localize_script('ftf-ate-frontend-js', 'ftf_aet', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'config' => array(
                'show_metrics' => $show_metrics === 'on',
                'use_api' => $use_api
          )
      ));

        wp_enqueue_script('ftf-ate-frontend-js');

        if ($include_bootstrap_styles === 'on'){
            $css_url = $plugin_dir_url . '../dist/css/styles-bs.min.css';
            $css_path = $plugin_dir_path . '../dist/css/styles-bs.min.css';
        } else {
            $css_url = $plugin_dir_url . '../dist/css/styles.min.css';
            $css_path = $plugin_dir_path . '../dist/css/styles.min.css';
        }

        wp_enqueue_style('ftf-ate-frontend-styles', $css_url, array(), filemtime($css_path));
    }

    function add_type_attribute($tag, $handle, $src){
        if ('ftf-ate-frontend-js' !== $handle) {
            return $tag;
        }
        $tag = '<script type="module" src="' . esc_url($src) . '" defer="defer"></script>';
        return $tag;
    }
}
