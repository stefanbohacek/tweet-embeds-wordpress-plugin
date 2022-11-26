<?php
namespace FTF_TEmbeds;

$dir = plugin_dir_path(__FILE__);

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Helpers {
    public static function log_this($title, $data = false){
        if (func_num_args() === 0){
            return false;
        }
        elseif (func_num_args() === 1){
            $data = $title;
            $title = "LOG";
        }
    
        $border_char = "/";
        $border_length_bottom = 100;
        $border_length_top = $border_length_bottom - strlen($title) - 2;
    
        $border_length_top_left = floor($border_length_top/2);
        $border_length_top_right = ceil($border_length_top/2);
    
        $border_top_left = str_pad("", $border_length_top_left, $border_char);
        $border_top_right = str_pad("", $border_length_top_right, $border_char);  
    
        error_log("\n\n");
        error_log("$border_top_left $title $border_top_right");
    
        if (is_array($data) || is_object($data)){
            error_log(print_r($data, true));
        }
        else{
            error_log("");
            error_log($data);
            error_log("");
        }
        error_log(str_pad("", $border_length_bottom, $border_char) . "\n");
    }    
}
