const renderTweet = (data, container) => {
  // console.log('debug:', data);
  let tweetText = data.text,
  tweetUrl = `https://twitter.com/${ data.users[0].username }/status/${ data.id }`,
  entities = null,
  verifiedBadge = data.users[0].verified ? '<svg role="img" aria-label="Verified user" class="tweet-verified-user-badge" viewBox="0 0 24 24" aria-label="Verified account" class="r-13gxpu9 r-4qtqp9 r-yyyyoo r-1xvli5t r-9cviqr r-dnmrzs r-bnwqim r-1plcrui r-lrvibr"><g><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5s-2.816.917-3.437 2.25c-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.4-1.272.65-2.147 2.018-2.147 3.6 0 1.495.782 2.798 1.942 3.486-.02.17-.032.34-.032.514 0 2.21 1.708 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.512 0 2.818-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.174-.012-.344-.033-.513 1.158-.687 1.943-1.99 1.943-3.484zm-6.616-3.334l-4.334 6.5c-.145.217-.382.334-.625.334-.143 0-.288-.04-.416-.126l-.115-.094-2.415-2.415c-.293-.293-.293-.768 0-1.06s.768-.294 1.06 0l1.77 1.767 3.825-5.74c.23-.345.696-.436 1.04-.207.346.23.44.696.21 1.04z"></path></g></svg>' : '',
  renderedTweetHTML = `<div class="card w-100">
  <div class="tweet-body-wrapper card-body pt-4">
  <div class="card-text">
  <div class="row no-gutters mb-1">`;
  
  if (data.users[0].profile_image_url){
    renderedTweetHTML += `<div class="col-2 col-sm-1 col-md-1">
    <a href="https://twitter.com/${ data.users[0].username }" class="text-decoration-none"><img title="Profile image" alt="Profile image of @${ data.users[0].username }" loading="lazy" class="rounded-circle border" width="48" height="48" src="/wp-json/ftf/proxy-media?url=${ data.users[0].profile_image_url }"></a>
    </div>`;
  }
  
  renderedTweetHTML += `<div class="tweet-author ${ data.users[0].profile_image_url ? 'col-9 col-sm-10 col-md-10 pl-2' : 'col-11 col-sm-11 col-md-11' } pb-3">
  <p class="font-weight-bold mb-0 mt-0"><a class="text-dark text-decoration-none" href="https://twitter.com/${ data.users[0].username }">${ data.users[0].name }${ verifiedBadge }</a></p>
  <p class="mb-1 mb-md-2 mt-0"><a class="text-muted text-decoration-none" href="https://twitter.com/${ data.users[0].username }">@${ data.users[0].username }</a></p>
  </div>
  <div class="col-1 text-right">
  <a href="${ tweetUrl }" target="_blank"><svg role="img" aria-label="Twitter logo" style="width: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M512 97.248c-19.04 8.352-39.328 13.888-60.48 16.576 21.76-12.992 38.368-33.408 46.176-58.016-20.288 12.096-42.688 20.64-66.56 25.408C411.872 60.704 384.416 48 354.464 48c-58.112 0-104.896 47.168-104.896 104.992 0 8.32.704 16.32 2.432 23.936-87.264-4.256-164.48-46.08-216.352-109.792-9.056 15.712-14.368 33.696-14.368 53.056 0 36.352 18.72 68.576 46.624 87.232-16.864-.32-33.408-5.216-47.424-12.928v1.152c0 51.008 36.384 93.376 84.096 103.136-8.544 2.336-17.856 3.456-27.52 3.456-6.72 0-13.504-.384-19.872-1.792 13.6 41.568 52.192 72.128 98.08 73.12-35.712 27.936-81.056 44.768-130.144 44.768-8.608 0-16.864-.384-25.12-1.44C46.496 446.88 101.6 464 161.024 464c193.152 0 298.752-160 298.752-298.688 0-4.64-.16-9.12-.384-13.568 20.832-14.784 38.336-33.248 52.608-54.496z" fill="#03a9f4"/></svg></a>
  </div>
  </div>
  <div class="tweet-body">`;
  
  if (data.entities){
    if (data.entities.urls){
      data.entities.urls.forEach(function(url){
        if (url.display_url.indexOf('pic.twitter.com') === -1){
          tweetText = tweetText.replace(RegExp(url.url, 'ig'), `<a href="${ url.expanded_url }" target="_blank">${ url.display_url }</a>`)
        } else {
          tweetText = tweetText.replace(url.url, '');
        }
      });
    }
    
    if (data.entities.mentions){
      data.entities.mentions.forEach(function(mention){
        tweetText = tweetText.replace(RegExp(`@${ mention.username }`, 'ig'), `<a href="https://twitter.com/${ mention.username }" target="_blank">@${ mention.username }</a>`);
      });
    }
    
    if (data.entities.hashtags){
      data.entities.hashtags.forEach(function(hashtag){
        tweetText = tweetText.replace(RegExp(`#${ hashtag.tag }`, 'ig'), `<a href="https://twitter.com/hashtag/${ hashtag.tag }" target="_blank">#${ hashtag.tag }</a>`);
      });
    }
  }
  
  if (data.media && data.media.length){
    tweetText += `<div data-media-length="${ data.media.length }" class="tweet-media row mt-3 no-gutters">`;
    
    data.media.forEach(function(media, index){
      if (data.media.length === 1){
        tweetText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-12 col-lg-12">`;
      } else if (data.media.length === 3){
        if (index === 2){
          tweetText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-12 col-lg-12">`;
        } else {
          tweetText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-6 col-lg-6">`;
        }
      } else if (data.media.length > 1 && data.media.length < 5){
        tweetText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-6 col-lg-6">`;
      } else {
        tweetText += `<div data-media-type="${ media.type }" class="text-center col-sm-12 col-md-3 col-lg-3">`;
      }
      
      if (media.type === 'animated_gif'){
        tweetText += `<video class="w-100 mt-0" controls loop><source src="/wp-json/ftf/proxy-media?url=${ media.preview_image_url.replace('pbs.twimg.com/tweet_video_thumb', 'video.twimg.com/tweet_video').replace('.jpg', '.mp4').replace('.png', '.mp4') }" type="video/mp4"></video>`
      } else if (media.type === 'video'){
        if (media.variants){
          tweetText += `<video class="w-100 mt-0" controls loop><source src="/wp-json/ftf/proxy-media?url=${ media.variants[media.variants.length-1].url }" type="${ media.variants[media.variants.length-1].content_type }"></video>`
        } else {
          tweetText += `<a class="tweet-video-placeholder" href="${ tweetUrl }" target="_blank"><img alt="${ media.alt_text || 'Video preview image' }" loading="lazy" width="${ media.width }" height="${ media.height }" class="w-100 rounded border" src="/wp-json/ftf/proxy-media?url=${ media.preview_image_url }"></a>`;
        }
      } else if (media.type === 'photo'){
        tweetText += `<a href="${ tweetUrl }" target="_blank"><img alt="${ media.alt_text || '' }" loading="lazy" width="${ media.width }" height="${ media.height }" class="w-100 rounded border" src="/wp-json/ftf/proxy-media?url=${ media.url }"></a>`;
      }
      
      tweetText += '</div>';
    });
    
    tweetText += '</div>';
  }
  
  
  if (data.polls && data.polls.length){
    tweetText += '<div class="mt-0 row">';
    
    data.polls.forEach(function(poll){
      if (poll.options && poll.options.length){
        
        const voteCounts = poll.options.map(function(option){
          return option.votes;
        });
        
        const voteCountMax = Math.max(...voteCounts);
        const votesTotal = voteCounts.reduce(function(total, num){
          return total + num;
        });
        
        poll.options.forEach(function(option){
          const votesPortion = option.votes/votesTotal * 100;
          tweetText += `
          <div class="tweet-poll-results col-9" style="height:60px;">
          <div class="progress position-relative mt-n4 ${ option.votes === voteCountMax ? ' border border-primary ' : '' }" style="height:30px;">
          <div class="progress-bar" 
          role="progressbar" 
          style="width: ${ votesPortion }%" 
          aria-valuenow="${ votesPortion }" 
          aria-valuemin="0" 
          aria-valuemax="100"></div>
          <span class="pl-2 d-flex position-absolute w-100" style="font-size: 1rem; line-height: 30px;">${ option.label }</span>
          </div>
          </div>
          <div class="col-3 mt-n4 text-right" style="height:60px;">
          <span class="w-100">${ Math.round(option.votes/votesTotal * 100) }%</span>
          </div>`;
        });
        tweetText += `<div class="col-12 mt-3"><p class="text-muted">${ votesTotal.toLocaleString() } votes</p></div>`;
      }
    });
    
    tweetText += '</div>';
  }                    
  
  const tweetDate = new Date(data.created_at).toLocaleDateString(navigator.language, { month: 'long', year: 'numeric', day: 'numeric' });
  
  renderedTweetHTML += tweetText + `</div>
  </div>
  </div>
  <div class="card-footer">`;
  
  if (!container){
    if (ftf_aet.config.show_metrics && data.public_metrics){
      renderedTweetHTML += `
      <span class="tweet-icon" role="img" aria-label="Retweets">🔁</span><a class="text-muted" href="${ tweetUrl }" target="_blank">${ data.public_metrics.retweet_count.toLocaleString() }</a> |
      <span class="tweet-icon" role="img" aria-label="Likes">❤️</span> <a class="text-muted" href="${ tweetUrl }" target="_blank">${ data.public_metrics.like_count.toLocaleString() }</a> | `;
    }
    
    renderedTweetHTML += `<a class="text-muted" href="${ tweetUrl }" target="_blank">${ tweetDate }</a>`;
  }
  
  renderedTweetHTML += '</div></div>';
  
  let renderedTweet = document.createElement('div');
  renderedTweet.className = `twitter-tweet twitter-tweet-rendered w-100`;
  renderedTweet.innerHTML = renderedTweetHTML;
  
  let lastUrl = '';
  
  if (data.entities && data.entities.urls && data.entities.urls.length){
    lastUrl = data.entities.urls[data.entities.urls.length - 1];
  }
  
  if ((data.media && data.media.length) || data.extended_entities && data.extended_entities.media && data.extended_entities.media.length){
    lastUrl = '';
  }
  
  if (lastUrl){
    renderedTweet.dataset.urlAttachment = lastUrl.expanded_url;
    renderedTweet.dataset.urlAttachmentProcessed = 'false';
  }
  
  if (container){
    const tweetContainer = container.querySelector('.tweet-body a:last-of-type');
    console.log(tweetContainer);
    tweetContainer.parentNode.replaceChild(renderedTweet, tweetContainer);
    
    
  } else {
    const tweet = document.querySelector(`[data-tweet-id="${ data.id }"]`);
    tweet.parentNode.replaceChild(renderedTweet, tweet);
  }
  
  // if (data.id === '1214098949918875648'){
  // console.log(renderedTweet, data);
  // }
};

export { renderTweet };
