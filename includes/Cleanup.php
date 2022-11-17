<?php
namespace FTF_TEmbeds;

$dir = plugin_dir_path( __FILE__ );

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Cleanup {
    function __construct(){
        add_action('render_block', array($this, 'remove_twitter_script_block'), 10, 2);
        add_filter('the_content', array($this, 'remove_twitter_script_content'));
    }

    function search_replace_twitter_script($content){
        $content = str_replace('<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>', '', $content);
        $content = str_replace('<script async src=\"https:\/\/platform.twitter.com\/widgets.js\" charset=\"utf-8\"><\/script>', '', $content);
        return $content;
    }

    function remove_twitter_script_block($block_content, $block) {
        if (strpos($block_content, 'platform.twitter.com/widgets.js') !== false) {
            $block_content = $this->search_replace_twitter_script($block_content);
        }

        return $block_content;
    }

    function remove_twitter_script_content($content) {
        if (strpos($content, 'platform.twitter.com/widgets.js') !== false) {
            $content = $this->search_replace_twitter_script($content);
        }
        return $content;
    }
}
