=== TEmbeds ===
Contributors: fourtonfish
Tags: twitter, tweets, embed
Requires at least: 5.0
Tested up to: 6.0.1
Stable tag: 1.0.0
Requires PHP: 5.6
License: MIT
License URI: https://opensource.org/licenses/MIT

Embed Tweets without compromising your users' privacy and your site's performance.

== Description ==

Embed Tweets without compromising your users' privacy and your site's performance. This plugin works with the [built-in Tweet Gutenberg block](https://neliosoftware.com/blog/how-to-embed-tweets-in-wordpress-with-gutenberg/) by removing Twitter's script and using Twitter's API to retrieve the data.

You can optionally provide Twitter API keys to show the number of likes and retweets and load images and other media in Tweets.

[Learn more](https://fourtonfish.com/project/tweet-embeds-wordpress-plugin/) | [View source](https://github.com/fourtonfish/tweet-embeds-wordpress-plugin)

== FAQ and Troubleshooting ==

**Tweets are not loading**

Please make sure your API keys are set up correctly and that you're using the v2 of Twitter's API. ([See the Get Started section.](https://developer.twitter.com/en/docs/twitter-api/migrate))

**I need to run a script after Tweets are processed**

You can use the `tembeds_tweets_processed` event. Using jQuery as an example, you could do the following:

`
$( document ).on( 'tembeds_tweets_processed', function(){
    $( '.twitter-tweet' ).each( function(){
        let $tweet = $( this );
        // Now you can do something with each Tweet.
    } );
} );

`