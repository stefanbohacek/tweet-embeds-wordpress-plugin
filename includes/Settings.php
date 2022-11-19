<?php
namespace FTF_TEmbeds;

class Settings {
    function __construct(){
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_filter('plugin_action_links_tembeds/index.php', array($this, 'settings_page_link'));
    }

    function add_settings_page(){
        add_options_page(
            'Settings for the Tweet Embeds plugin',
            'Tweet Embeds',
            'manage_options',
            'ftf-alt-embed-tweet',
            array($this, 'render_settings_page')
       );
    }

    function settings_init(){
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_twitter_api_consumer_key', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_twitter_api_consumer_secret', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_twitter_api_oauth_access_token', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_custom_styles', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_include_bootstrap_styles', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_archival_mode', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_tweet_show_metrics', 'esc_attr');
        register_setting('ftf_alt_embed_tweet', 'ftf_alt_embed_cache_expiration', 'esc_attr');

        add_settings_section(
            'ftf_alt_embed_tweet_settings', 
            __('', 'wordpress'), 
            array($this, 'render_settings_form'),
            'ftf_alt_embed_tweet'
       );
    }

    function render_settings_page(){ ?>
        <div class="wrap">
        <h1>Tweet Embeds</h1>

        <form action='options.php' method='post' >
            <?php
            settings_fields('ftf_alt_embed_tweet');
            do_settings_sections('ftf_alt_embed_tweet');
            submit_button();
            ?>
            </form>
        </div>
    <?php }

    function render_settings_form(){
        /* Twitter API keys */
        $twitter_api_consumer_key = get_option('ftf_alt_embed_tweet_twitter_api_consumer_key');
        $twitter_api_consumer_secret = get_option('ftf_alt_embed_tweet_twitter_api_consumer_secret');
        $twitter_api_oauth_access_token = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token');
        $twitter_api_oauth_access_token_secret = get_option('ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret');

        /* Customization */

        $archival_mode = get_option('ftf_alt_embed_tweet_archival_mode');
        $include_bootstrap_styles = get_option('ftf_alt_embed_tweet_include_bootstrap_styles', 'on');
        $show_metrics = get_option('ftf_alt_embed_tweet_show_metrics', 'on');
        $custom_styles = get_option('ftf_alt_embed_tweet_custom_styles');
        $cache_expiration = get_option('ftf_alt_embed_cache_expiration');

        if (empty($cache_expiration)){
            $cache_expiration = 30;
        }        

        ?>

        <h3 id="about">About the plugin</h3>
        <p>Embed Tweets on your WordPress website without 3rd party scripts, improving your site's performance and protecting your visitors' privacy.</p>
        <p>Please reach out with any questions <a href="mailto:stefan@fourtonfish.com?subject=Tweet Embeds WordPress Plugin">via email</a> or <a href="https://twitter.com/fourtonfish">Twitter</a>.</p>
        
        <p>
            <a class="button" href="https://fourtonfish.com/project/tweet-embeds-wordpress-plugin/" target="_blank">Learn more</a>
            <a class="button" href="https://github.com/fourtonfish/tweet-embeds-wordpress-plugin" target="_blank">View source</a>
        </p>

        <h3 id="settings-twitter-api-keys">Twitter API keys</h3>
        <?php if (empty($twitter_api_consumer_key) || empty($twitter_api_consumer_secret) || empty($twitter_api_oauth_access_token) || empty($twitter_api_oauth_access_token_secret)){ ?>
            <p>To show the number of likes and retweets and include images and GIFs in Tweets, you need to sign up for a Twitter developer account and add your API keys below. Be sure to use the v2 of the API. (<a target="_blank" href="https://developer.twitter.com/en/docs/twitter-api/migrate">See migration guide</a>.)</p>
            <!-- <p><a class="button" href="https://botwiki.org/tutorials/how-to-create-a-twitter-app/" target="_blank">See how</a></p> -->
            <p><a class="button" href="https://developer.twitter.com/en/apps" target="_blank">Open Twitter developer dashboard</a></p>
        <?php } else { ?>
            <p>Manage you API keys in the <a href="https://developer.twitter.com/en/apps" target="_blank">Twitter developer dashboard</a>.</p>
        <?php } ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-width-restriction">Your Twitter API keys</label>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-twitter-api-consumer_key">Consumer Key</label>
                    </th>
                    <td>
                        <input id="ftf-alt-embed-tweet-twitter-api-consumer_key"
                        type="password"
                        name="ftf_alt_embed_tweet_twitter_api_consumer_key"
                        value="<?php echo $twitter_api_consumer_key; ?>"
                        placeholder="***************">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-twitter-api-consumer_secret">Consumer Secret</label>
                    </th>
                    <td>
                        <input id="ftf-alt-embed-tweet-twitter-api-consumer_secret"
                        type="password"
                        name="ftf_alt_embed_tweet_twitter_api_consumer_secret"
                        value="<?php echo $twitter_api_consumer_secret; ?>"
                        placeholder="***************">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-twitter-api-oauth_access_token">Access Token</label>
                    </th>
                    <td>
                        <input id="ftf-alt-embed-tweet-twitter-api-oauth_access_token"
                        type="password"
                        name="ftf_alt_embed_tweet_twitter_api_oauth_access_token"
                        value="<?php echo $twitter_api_oauth_access_token; ?>"
                        placeholder="***************">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-twitter-api-oauth_access_token_secret">Access Token Secret</label>
                    </th>
                    <td>
                        <input id="ftf-alt-embed-tweet-twitter-api-oauth_access_token_secret"
                        type="password"
                        name="ftf_alt_embed_tweet_twitter_api_oauth_access_token_secret"
                        value="<?php echo $twitter_api_oauth_access_token_secret; ?>"
                        placeholder="***************">
                    </td>
                </tr>
            </tbody>
        </table>
        <h3 id="settings-customization">Customization</h3>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-cache_expiration">Cache Expiration (in minutes)</label>
                    </th>
                    <td>
                        <input id="ftf-alt-embed-tweet-cache_expiration"
                        type="number"
                        min="5"
                        name="ftf_alt_embed_cache_expiration"
                        value="<?php echo $cache_expiration; ?>"
                        placeholder="30">
                        <p class="description">
                            The Twitter API allows <a href="https://developer.twitter.com/en/docs/twitter-api/tweets/lookup/api-reference/get-tweets" target="_blank">900 requests per 15-minute window</a>. Based on your site's traffic and overall number of embedded Tweets you might want to increase how long the Twitter data should be cached to reduce the number of API calls. 
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-show-metrics">Show number of likes and retweets</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($show_metrics, 'on'); ?> name="ftf_alt_embed_tweet_show_metrics" id="ftf-alt-show-metrics">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-include-bootstrap-styles">Load necessary Bootstrap styles</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($include_bootstrap_styles, 'on'); ?> name="ftf_alt_embed_tweet_include_bootstrap_styles" id="ftf-alt-include-bootstrap-styles">
                        <p class="description">
                            If you use the full non-customized version of <a href="https://getbootstrap.com/" target="_blank">Bootstrap 4</a> on your site, you can uncheck this. Otherwise a slimmed-down version of the Bootstrap CSS library will be loaded and only applied to the embedded Tweets.
                        </p>
                    </td>
                </tr>
<!--
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-custom_styles">Additional CSS</label>
                    </th>
                    <td>
                        <textarea
                            id="ftf-alt-embed-tweet-custom_styles"
                            name="ftf_alt_embed_tweet_custom_styles"
                            rows="4"
                            cols="50"
                            style="font-family: monospace;"
                        ><?php echo $custom_styles; ?></textarea>
                        <p class="description">
                            Add additional CSS styles. <a href="https://jigsaw.w3.org/css-validator/#validate_by_input" target="_blank">Use the CSS validator</a> to make sure your CSS is valid.
                        </p>                        
                    </td>
                </tr>
-->
                <tr>
                    <th scope="row">
                        <label for="ftf-alt-embed-tweet-archival-mode">Archival Mode (Experimental)</label>
                    </th>
                    <td>
                        <input type="checkbox" <?php checked($archival_mode, 'on'); ?> name="ftf_alt_embed_tweet_archival_mode" id="ftf-alt-embed-tweet-archival-mode">
                        <p class="description">
                            Save data and images in case the original Tweet is deleted. This feature is currently under active development.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3 id="error-log">Error log</h3>
            <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'ftf_alt_embed_tweet_error_log';
                $error_log = $wpdb->get_results("SELECT * FROM $table_name");

                // error_log(print_r(array(
                //     'error_log' => $error_log
                //), true));

                if ($error_log){ ?>
                    <details>
                    <summary>Click to view error log</summary>
                    <table class="widefat">
                        <tr>
                            <th>Code</th>
                            <th>Message</th>
                            <th>Label</th>
                            <th>Date</th>
                        </tr>
                        <?php
                        foreach ($error_log as $log_item) { ?>
                            <tr>
                                <td><?php echo $log_item->code; ?></td>
                                <td><?php echo $log_item->message; ?></td>
                                <td><?php echo $log_item->label; ?></td>
                                <td><?php echo $log_item->created_at; ?></td>
                            </tr>
                        <?php } ?>
                        </table>
                    </details>
                <?php } else { ?>
                    <p>There are no errors to display.</p>
                <?php }
            ?>
    <?php }    

    function settings_page_link($links){
        $url = esc_url(add_query_arg(
            'page',
            'ftf-alt-embed-tweet',
            get_admin_url() . 'admin.php'
       ));
        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
        array_push(
            $links,
            $settings_link
       );
        return $links;
    }
}
