![Preview of multiple Tweets embedded with the Tweet Embeds plugin](./images/thumbnail/tweet-embeds-bw-tint.png)

# Tweet Embeds

Embed tweets without compromising your users' privacy and your site's performance.

Learn more [on fourtonfish.com](https://fourtonfish.com/project/tweet-embeds-wordpress-plugin/).

## How to use

1. [Install the plugin.](https://wordpress.org/plugins/tembeds)
2. [Create a new Twitter app](https://developer.twitter.com/en/dashboard) and get your API keys.
3. Go to the plugin's settings page and add your Twitter API keys.

If you don't provide the API keys, the plugin will still work, but some data will be missing (profile pictures, number of likes and retweets) and media (images, GIFs, videos) will not render.

## Technical details

### Process tweets manually

If you need to process tweets that are added to the page dynamically, use the `ftfHelpers.processTweets()` function. Be sure to check if the function exists before using it to avoid errors in your script.


### Wait for tweets to be processed

If you need to perform an action after all tweets on the page are processed, add a listener for the `tembeds_tweets_processed` event.

```js
document .addEventListener( 'tembeds_tweets_processed', function(){
  const tweets = document.querySelectorAll( '.twitter-tweet' );
  console.log( 'tweets are ready', tweets );    
} );
```

Here's an example using jQuery.

```js
$( document ).on( 'tembeds_tweets_processed', function(){
  const $tweets = $( '.twitter-tweet' );
  console.log( 'tweets are ready', $tweets );
} );
```

## Development

```sh
#install dependencies
npm install
# build front-end scripts and styles
gulp
```
