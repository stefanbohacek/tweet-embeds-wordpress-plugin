<?php
namespace FTF_TEmbeds;
use FTF_TEmbeds\Helpers;

class Database {
    protected $table_name;
    
    function __construct(){
        $this->table_name = 'ftf_tembeds';
        register_activation_hook(__FILE__, array($this, 'create_database'));
        // add_action('admin_menu', array($this, 'add_settings_page'));
        // add_filter('plugin_action_links_tembeds/index.php', array($this, 'settings_page_link'));
    }

    function create_database(){
        global $wpdb;
        $version = get_option('ftf_tembeds_version', '1.0');

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $this->table_name;
    
        $sql = "CREATE TABLE $table_name (
            `tweet_id` BIGINT UNIQUE,
            `tweet_data` NVARCHAR(5000)
       ) $charset_collate;";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function add_tweet($tweet_id, $tweet_data){
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $result = $wpdb->replace($table_name, array('tweet_id' => $tweet_id, 'tweet_data' => $tweet_data), array('%d', '%s'));

        Helpers::log_this('debug:saving tweet to DB', array(
            'result' => $result,
       ));

        if (!$result){
            // TODO: Check if the error is due to table not existing, otherwise this might create an infinite loop.
            $this->create_database();
            $this->add_tweet($tweet_id, $tweet_data);
        }
    }

    function get_tweet($tweet_id){
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $tweet_data = $wpdb->get_row("SELECT * FROM $table_name WHERE tweet_id = $tweet_id");

        Helpers::log_this('debug:loading tweet from DB', array(
            'tweet_data' => $tweet_data,
       ));

        return $tweet_data;
    }
}
