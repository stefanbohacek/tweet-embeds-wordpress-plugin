function _createForOfIteratorHelper(o,allowArrayLike){var it="undefined"!=typeof Symbol&&o[Symbol.iterator]||o["@@iterator"];if(!it){if(Array.isArray(o)||(it=_unsupportedIterableToArray(o))||allowArrayLike&&o&&"number"==typeof o.length){it&&(o=it);var i=0,F=function(){};return{s:F,n:function n(){return i>=o.length?{done:!0}:{done:!1,value:o[i++]}},e:function e(_e){throw _e},f:F}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var err,normalCompletion=!0,didErr=!1;return{s:function s(){it=it.call(o)},n:function n(){var step=it.next();return normalCompletion=step.done,step},e:function e(_e2){didErr=!0,err=_e2},f:function f(){try{normalCompletion||null==it.return||it.return()}finally{if(didErr)throw err}}}}function _unsupportedIterableToArray(o,minLen){if(o){if("string"==typeof o)return _arrayLikeToArray(o,minLen);var n=Object.prototype.toString.call(o).slice(8,-1);return"Object"===n&&o.constructor&&(n=o.constructor.name),"Map"===n||"Set"===n?Array.from(o):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?_arrayLikeToArray(o,minLen):void 0}}function _arrayLikeToArray(arr,len){(null==len||len>arr.length)&&(len=arr.length);for(var i=0,arr2=Array(len);i<len;i++)arr2[i]=arr[i];return arr2}import{fetchData}from"./fetchData.js";import{getTweetId}from"./getTweetId.js";import{renderTweet}from"./renderTweet.js";import{dispatchEvent}from"./dispatchEvent.js";var processTweets=function(){var _step,tweets=document.querySelectorAll("blockquote.twitter-tweet"),tweetIds=[],_iterator=_createForOfIteratorHelper(tweets);try{for(_iterator.s();!(_step=_iterator.n()).done;){var _tweet=_step.value,anchors=_tweet.querySelectorAll("a"),url=anchors[anchors.length-1].href,tweetId=getTweetId(url);tweetIds.push(tweetId),_tweet.dataset.tweetId=tweetId}// console.log('tweet IDs', tweetIds);
}catch(err){_iterator.e(err)}finally{_iterator.f()}if(tweetIds.length)if(ftf_aet.config.use_api)fetchData({action:"ftf_embed_tweet",tweet_ids:tweetIds.join(",")},function(response){response&&response.length&&function(){response.forEach(function(data){renderTweet(data)});var tweetsWithAttachment=document.querySelectorAll("[data-url-attachment-processed=\"false\"]"),tweetsWithAttachmentCount=tweetsWithAttachment.length;0===tweetsWithAttachmentCount&&dispatchEvent("tembeds_tweets_processed");// console.log('tweetsWithAttachment', tweetsWithAttachment);
var _step2,_iterator2=_createForOfIteratorHelper(tweetsWithAttachment);try{var _loop=function _loop(){var tweet=_step2.value;tweet.dataset.urlAttachmentProcessed="true",-1<tweet.dataset.urlAttachment.indexOf("twitter.com/")&&(console.log("rendering QT...",getTweetId(tweet.dataset.urlAttachment)),fetchData({action:"ftf_embed_tweet",tweet_ids:[getTweetId(tweet.dataset.urlAttachment)]},function(response){console.log(response),response&&response.length&&response.forEach(function(data){renderTweet(data,tweet)})})),fetchData({action:"ftf_get_site_info",url:tweet.dataset.urlAttachment},function(data){if(data&&data.image){var urlAttachmentPreview=document.createElement("div");urlAttachmentPreview.className="tweet-attachment-preview card mt-4";var tmpAnchor=document.createElement("a");tmpAnchor.href=tweet.dataset.urlAttachment;var urlAttachmentPreviewHTML="";console.log("debug:data.image",data.image),data.image&&(urlAttachmentPreviewHTML+="<a href=\"".concat(tweet.dataset.urlAttachment,"\"><img loading=\"lazy\" class=\"tweet-attachment-site-thumbnail card-img-top\" src=\"/wp-json/ftf/proxy-media?url=").concat(encodeURI(data.image),"\" alt=\"Preview image for ").concat(tweet.dataset.urlAttachment,"\"></a>")),urlAttachmentPreviewHTML+="<div class=\"card-body\">",urlAttachmentPreviewHTML+="<p class=\"card-text\"><a class=\"stretched-link text-muted\" href=\"".concat(tweet.dataset.urlAttachment,"\" target=\"_blank\">").concat(tmpAnchor.hostname,"</a></p>"),data.title&&(urlAttachmentPreviewHTML+="<p class=\"card-title\">".concat(data.title,"</p>")),data.description&&(urlAttachmentPreviewHTML+="<p class=\"card-subtitle mb-2 text-muted\">".concat(data.description,"</p>")),urlAttachmentPreviewHTML+="</div>",urlAttachmentPreview.innerHTML=urlAttachmentPreviewHTML,tweet.querySelector(".tweet-body-wrapper").appendChild(urlAttachmentPreview)}},function(){tweetsWithAttachmentCount--,0===tweetsWithAttachmentCount&&dispatchEvent("tembeds_tweets_processed")})};for(_iterator2.s();!(_step2=_iterator2.n()).done;)_loop()}catch(err){_iterator2.e(err)}finally{_iterator2.f()}}()});else{var _step3,_iterator3=_createForOfIteratorHelper(tweets);try{for(_iterator3.s();!(_step3=_iterator3.n()).done;){var tweet=_step3.value,tweetAttribution="",tweetDate="";if(tweet.childNodes&&tweet.childNodes.length){if(3===tweet.childNodes.length){tweetDate=tweet.childNodes[2].textContent;for(var currentNode,i=0;i<tweet.childNodes.length;i++)if(currentNode=tweet.childNodes[i],"#text"===currentNode.nodeName){tweetAttribution=currentNode.nodeValue;break}}else{tweetAttribution=tweet.childNodes[tweet.childNodes.length-2].innerHTML;var tweetDateEl=document.createElement("div");tweetDateEl.innerHTML=tweetAttribution,tweetDate=tweetDateEl.querySelector("a").textContent}var usernames=tweetAttribution.match(/@\w+/gi),name="",username="";// console.log('debug:tweetDate', tweetDate);
if(usernames&&usernames[0]){username=usernames[0];var names=tweetAttribution.split(username);// console.log('debug:names', usernames);
names&&names[0]&&(name=names[0].replace("\u2014 ","").replace(" (",""))}renderTweet({created_at:tweetDate,text:tweet.querySelector("p").innerHTML,id:tweet.dataset.tweetId,// 'author_id': '',
users:[{name:name,username:username.replace(/^@/,"")// 'id': '',
// 'profile_image_url': '',
// 'verified': false
}]})}}}catch(err){_iterator3.e(err)}finally{_iterator3.f()}}};export{processTweets};
//# sourceMappingURL=processTweets.js.map
