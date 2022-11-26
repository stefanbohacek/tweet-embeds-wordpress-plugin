import { fetchData } from "./fetchData.js";
import { getTweetId } from "./getTweetId.js";
import { renderTweet } from "./renderTweet.js";
import { dispatchEvent } from "./dispatchEvent.js";

const processTweets = (fn) => {
  const tweets = document.querySelectorAll('blockquote.twitter-tweet');
  let tweetIds = [];
  
  for (const tweet of tweets) {
    const anchors = tweet.querySelectorAll('a');
    const url = anchors[anchors.length - 1].href;
    const tweetId = getTweetId(url);
    tweetIds.push(tweetId);
    tweet.dataset.tweetId = tweetId;
  }
  
  // console.log('tweet IDs', tweetIds);
  
  if (tweetIds.length){
    if (ftf_aet.config.use_api){
      fetchData({
        action: 'ftf_embed_tweet',
        tweet_ids: tweetIds.join(',')
      }, function(response){
        if (response && response.length){
          response.forEach(function(data){
            renderTweet(data);
          });
          
          const tweetsWithAttachment = document.querySelectorAll('[data-url-attachment-processed="false"]');
          let tweetsWithAttachmentCount = tweetsWithAttachment.length;
          
          if (tweetsWithAttachmentCount === 0){
            dispatchEvent('tembeds_tweets_processed');
          }
          
          // console.log('tweetsWithAttachment', tweetsWithAttachment);
          
          for (const tweet of tweetsWithAttachment) {
            tweet.dataset.urlAttachmentProcessed = 'true';
            
            if (tweet.dataset.urlAttachment.indexOf('twitter.com/') > -1){
              console.log('rendering QT...', getTweetId(tweet.dataset.urlAttachment));
              fetchData({
                action: 'ftf_embed_tweet',
                tweet_ids: [getTweetId(tweet.dataset.urlAttachment)]
              }, function(response){
                console.log(response);
                if (response && response.length){
                  response.forEach(function(data){
                    renderTweet(data, tweet);
                  });
                }
              });
            } else {
              // noop
            }
            
            fetchData({
              action: 'ftf_get_site_info',
              url: tweet.dataset.urlAttachment
            }, function(data){
              if (data && data.image){
                let urlAttachmentPreview = document.createElement('div');
                urlAttachmentPreview.className = `tweet-attachment-preview card mt-4`;
                
                let tmpAnchor = document.createElement ('a');
                tmpAnchor.href = tweet.dataset.urlAttachment;
                
                let urlAttachmentPreviewHTML = '';
                console.log('debug:data.image', data.image);
                if (data.image){
                  urlAttachmentPreviewHTML += `<a href="${ tweet.dataset.urlAttachment }"><img loading="lazy" class="tweet-attachment-site-thumbnail card-img-top" src="/wp-json/ftf/proxy-media?url=${ encodeURI(data.image) }" alt="Preview image for ${tweet.dataset.urlAttachment}"></a>`;
                }
                
                urlAttachmentPreviewHTML += `<div class="card-body">`;
                urlAttachmentPreviewHTML += `<p class="card-text"><a class="stretched-link text-muted" href="${ tweet.dataset.urlAttachment }" target="_blank">${ tmpAnchor.hostname }</a></p>`;
                
                if (data.title){
                  urlAttachmentPreviewHTML += `<p class="card-title">${ data.title }</p>`;
                }
                
                if (data.description){
                  urlAttachmentPreviewHTML += `<p class="card-subtitle mb-2 text-muted">${ data.description }</p>`;
                }
                
                urlAttachmentPreviewHTML += `</div>`;
                
                urlAttachmentPreview.innerHTML = urlAttachmentPreviewHTML;
                tweet.querySelector('.tweet-body-wrapper').appendChild(urlAttachmentPreview);
              }
              
            }, function(){
              tweetsWithAttachmentCount--;
              // console.log('tweetsWithAttachmentCount', tweetsWithAttachmentCount);
              if (tweetsWithAttachmentCount === 0){
                dispatchEvent('tembeds_tweets_processed');
              }
            });                           
          }
        }
        
      });
    } else {
      
      for (const tweet of tweets) {
        // console.log('debug:tweet', tweet);
        // console.log('debug:childNodes', tweet.childNodes);
        
        let tweetAttribution = '', tweetDate = '';
        
        if (tweet.childNodes && tweet.childNodes.length){
          if (tweet.childNodes.length === 3){
            tweetDate = tweet.childNodes[2].textContent;
            for (let i = 0; i < tweet.childNodes.length; i++){
              let currentNode = tweet.childNodes[i];
              if (currentNode.nodeName === '#text') {
                tweetAttribution = currentNode.nodeValue;
                break;
              }
            }                        
          } else {
            tweetAttribution = tweet.childNodes[tweet.childNodes.length - 2].innerHTML;
            let tweetDateEl = document.createElement('div');
            tweetDateEl.innerHTML = tweetAttribution;
            tweetDate = tweetDateEl.querySelector('a').textContent;
          }
          
          const usernames = tweetAttribution.match(/@\w+/gi);
          let name = '', username = '';
          // console.log('debug:tweetDate', tweetDate);
          
          if (usernames && usernames[0]){
            username = usernames[0];
            const names = tweetAttribution.split(username);
            // console.log('debug:names', usernames);
            
            if (names && names[0]){
              name = names[0].replace('— ', '').replace(' (', '');
            }
          }
          
          renderTweet({
            'created_at': tweetDate,
            'text': tweet.querySelector('p').innerHTML,
            'id': tweet.dataset.tweetId,
            // 'author_id': '',
            'users': [
              {
                'name': name,
                'username': username.replace(/^@/, ''),
                // 'id': '',
                // 'profile_image_url': '',
                // 'verified': false
              }
            ]
          });                    
        }
      }
    }
  }
};

export { processTweets };
