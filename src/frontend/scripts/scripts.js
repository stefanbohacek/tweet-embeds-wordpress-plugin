'use strict';
import { ready } from "./ready.js";
import { processTweets } from "./processTweets.js";

ready(function(){
  processTweets();
});
