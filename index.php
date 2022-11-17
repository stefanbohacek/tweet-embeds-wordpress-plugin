<?php
/**
 * Plugin Name: TEmbeds
 * Plugin URI: https://github.com/fourtonfish/tweet-embeds-wordpress-plugin
 * Description: Embed Tweets without compromising your users' privacy and your site's performance.
 * Version: 1.3.0
 * Author: fourtonfish
 * Text Domain: tembeds
 *
 * @package ftf-alt-embed-tweet
 */


defined('ABSPATH') || exit;
require_once __DIR__ . '/vendor/autoload.php';

use FTF_TEmbeds\Cleanup;
use FTF_TEmbeds\Embed_Tweets;
use FTF_TEmbeds\Enqueue_Assets;
use FTF_TEmbeds\Media_Proxy;
use FTF_TEmbeds\Settings;
use FTF_TEmbeds\Site_Info;

$cleanup_init = new Cleanup();
$embed_tweets_init = new Embed_Tweets();
$enqueue_assets_init = new Enqueue_Assets();
$media_proxy_init = new Media_Proxy();
$settings_init = new Settings();
$site_info_init = new Site_Info();
