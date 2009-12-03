/**
 * jqTweet client service module
 *
 * $Revision: 1.1 $
 * $Date: 2009/12/03 12:59:06 $
 * $Author: sin_sin $
 *
 * Copyleft from the author of Chrome Bird.
 */
var TweetManagerConstants = {
  MAX_CACHED_ELEMENTS: 50,
  WAIT_TIME_UNTIL_REFETCH: 60000,
  TWEETS_PER_PAGE: 10,
  MAX_TWEET_SIZE: 140
};

var TweetManager = {
  tweetsCache: [],
  newTweetsCache: [],
  unreadTweets: {},
  timerId: null,
  currentError: null,
  currentPage: 1,
  currentIsLastPage: false,
  firstRun: true,
  newTweetsCallback: null,
  iconRed: new Image(),
  iconBlue: new Image(),
  //canvasContext: $("<canvas></canvas>")[0].getContext('2d'),

  setError: function(status) {
    this.currentError = status;
  },
  readTweet: function(id) {
    delete this.unreadTweets[id];
  },
  isTweetRead: function(id) {
    return !this.unreadTweets[id];
  },
  newTweetsCount: function() {
    return this.newTweetsCache.length;
  },
  notifyNewTweets: function(size) {
    var title = "(" + size + ")";
    var oldTitle = document.title.indexOf(")") >-1 ? document.title.substring(document.title.indexOf(")")+1) : document.title;
    //if(size > 1)
    //  title += "s";
    document.title = title + oldTitle;

    //this.canvasContext.drawImage(this.iconRed, 0, 0);
    //chrome.browserAction.setIcon({imageData: this.canvasContext.getImageData(0, 0, 19, 19)});
  },
  onFetchNew: function(success, tweets, status, context) {
    var _this = this;
    if(!success) {
      this.setError(status);
      this.timerId = setTimeout(function() { _this.fetchNewTweets.call(_this); }, TweetManagerConstants.WAIT_TIME_UNTIL_REFETCH);
      return;
    }
    this.setError(null);

    for(var i = tweets.length - 1; i >= 0; --i) {
      this.newTweetsCache.unshift(tweets[i]);
      this.unreadTweets[tweets[i].id] = true;
    }

    if(tweets.length > 0) {
      this.notifyNewTweets(this.newTweetsCache.length);
      try {
        // The callback might be invalid (popup not active), so let's ignore errors for now.
        this.newTweetsCallback(this.newTweetsCache.length);
      } catch(e) { /* ignoring */ }
    }
    this.timerId = setTimeout(function() { _this.fetchNewTweets.call(_this); }, TweetManagerConstants.WAIT_TIME_UNTIL_REFETCH);
  },
  onFetch: function(success, tweets, status, context) {
    if(!success) {
      this.setError(status);
      context._callback(null);
      this.setError(null);
      return;
    }
    this.setError(null);
    var i = 0;
    if(context.usingMaxId) {
      // If we're fetching using maxId, ignore the first one (we already have it)
      i = 1;
    }
    for(; i < tweets.length; ++i) {
      this.tweetsCache.push(tweets[i]);
    }
    var sliceStart = (context._page - 1) * context._count;
    var sliceEnd = context._page * context._count;
    context._callback(this.tweetsCache.slice(sliceStart, sliceEnd));

    if(this.firstRun) {
      //Start looking for new tweets
      this.fetchNewTweets();
      this.firstRun = false;
    }
  },
  updateNewTweets: function() {
    this.tweetsCache = this.newTweetsCache.concat(this.tweetsCache);
    this.newTweetsCache = [];
    //document.title = {title: "Check your tweets"};
    //this.canvasContext.drawImage(this.iconBlue, 0, 0);
    //chrome.browserAction.setIcon({imageData: this.canvasContext.getImageData(0, 0, 19, 19)});
  },
  giveMeTweets: function(callback, syncNew) {
    if(syncNew) {
      var oldNewTweetsCallback = this.newTweetsCallback;
      var _this = this;
      this.newTweetsCallback = function() {
        _this.updateNewTweets();
        _this.giveMeTweets(callback);
        _this.newTweetsCallback = oldNewTweetsCallback;
      }
      this.fetchNewTweets();
      return;
    }
    var page = this.currentPage;
    var count = TweetManagerConstants.TWEETS_PER_PAGE;
    if(this.tweetsCache.length >= (page * count) - 1) {
      //Everything is cached, return right away.
      callback(this.tweetsCache.slice((page - 1) * count, page * count));
    } else {
      //Hmm... There's something missing let's try to fetch what's missing.
      var missingCount = count * 2;
      var maxId = null;
      if(this.tweetsCache.length > 0) {
        maxId = this.tweetsCache[this.tweetsCache.length - 1].id;
        missingCount += 1;
      }
      var context = {
        _callback: callback,
        _page: page,
        _count: count,
        usingMaxId: !!maxId
      }
      var _this = this;
      twitterBackend.friendsTimeline(function(success, tweets, status, context) {
        _this.onFetch.call(_this, success, tweets, status, context);
      }, context, missingCount, null, null, maxId);
    }
  },
  clear: function() {
    if(this.timerId) {
      clearTimeout(this.timerId);
      this.timerId = null;
    }
    this.unreadTweets = {};
    this.tweetsCache = [];
    this.newTweetsCache = [];
    this.newTweetsCallback = null;
    this.firstRun = true;
    this.setError(null);
  },
  registerNewTweetsCallback: function(callback) {
    this.newTweetsCallback = callback;
  },
  fetchNewTweets: function() {
    if(this.timerId) {
      clearTimeout(this.timerId);
      this.timerId = null;
    }
    var lastId = null;
    if(this.newTweetsCache.length > 0) {
      lastId = this.newTweetsCache[0].id;
    } else if(this.tweetsCache.length > 0) {
      lastId = this.tweetsCache[0].id;
    }
    var _this = this;
    twitterBackend.friendsTimeline(function(success, tweets, status, context) {
      _this.onFetchNew.call(_this, success, tweets, status, context);
    }, null, null, null, lastId)
  }
};

TweetManager.iconRed.src = "img/icon19_noalpha_red.png";
TweetManager.iconBlue.src = "img/icon19_noalpha.png";

var twitterBackend = null;
function getTwitterBackend() {
  return twitterBackend;
}

function doSignin(user, password) {
  twitterBackend = new TwitterLib(user, password);
}

function doSignout() {
  TweetManager.clear();
  twitterBackend = null;
}

if(localStorage && localStorage.remember && localStorage.logged) {
  //Initializing
  doSignin(localStorage.username, localStorage.password);
  TweetManager.giveMeTweets(function() {});
}

$.fn.hoverFor = function(time, mainCallback, startingCallback, abortCallback) {
  return this.each(function(){
    var _this = this, timeoutHandle, triggerFired = false;
    $(this).hover(
      function() {
        if(triggerFired)
          return;
        if(startingCallback)
          startingCallback.call(_this);
        timeoutHandle = setTimeout(function() {
          triggerFired = true;
          mainCallback.call(_this);
          timeoutHandle = null;
        }, time);
      },
      function() {
        if(triggerFired)
          return;
        if(timeoutHandle) {
          if(abortCallback)
            abortCallback.call(_this);
          clearTimeout(timeoutHandle);
          timeoutHandle = null;
        }
      }
    );
 });
};

var tweetManager = TweetManager;
var tweetManagerConst = TweetManagerConstants;

Paginator = {
  firstPage: function() {
    tweetManager.currentPage = 1;
    $('#previousPage').hide();
  },
  nextPage: function() {
    tweetManager.currentPage += 1;
    $('#previousPage').show();
    loadTimeline();
  },
  setLastPage: function() {
    tweetManager.currentIsLastPage = true;
    $('#nextPage').hide();
  },
  previousPage: function() {
    tweetManager.currentIsLastPage = false;
    $('#nextPage').show();
    if(tweetManager.currentPage == 1) return;
    tweetManager.currentPage -= 1;
    if(tweetManager.currentPage == 1) {
      $('#previousPage').hide();
    }
    loadTimeline();
  },
  initialize: function() {
    if(tweetManager.currentPage == 1) {
      $('#previousPage').hide();
    }
    if(tweetManager.currentIsLastPage) {
      $('#nextPage').hide();
    }
  }
}

function showError(msg, tryAgainFunction) {
  $("#error").show();
  $("#error").text(msg);
  if(tryAgainFunction) {
    $("#error").append(' <a href="#" onclick="' + tryAgainFunction + '(); $(\'#error\').hide();">重试</a>');
  }
}

function onTimelineRetrieved(tweets) {
  $("#loading").hide();
  if(tweets) {
    if(tweets.length == 0) {
      Paginator.previousPage();
    } else {
      assemblyTweets(tweets);
    }
    if(tweets.length < tweetManagerConst.TWEETS_PER_PAGE) {
      Paginator.setLastPage();
    }
  } else {
    showError('连接时发生意外错误 "' + TweetManager.currentError + '".', 'loadTimeline');
  }
}

function openTab(tabUrl) {
  window.open(tabUrl);
}

function renderTweet(tweet) {
  var str = '<div id="tweet_' + tweet.id + '" class="tweet">';
  str += '<img src="' + tweet.user.profile_image_url + '" onclick="openTab(\'' + TwitterLib.URLS.BASE + tweet.user.screen_name + '\')"></img>';
  str += '<a href="#" class="user" onclick="openTab(\'' + TwitterLib.URLS.BASE + tweet.user.screen_name + '\')">' + tweet.user.screen_name + '</a>';
  str += '<div class="text">' + tweet.text + '</div>';

  str += '<div class="footer">';
  str += '<div class="timestamp">' + tweet.user.name+' '+Date.parse(tweet.created_at.replace('+0000','GMT')).toString('MM月dd日 HH:mm:ss')+' 通过 '+tweet.source+(tweet.in_reply_to_screen_name ? ' 对 '+tweet.in_reply_to_screen_name+' 的回复' : '') + '</div>';
  str += '<div class="actions"><a href="#" onclick="retweet(this.parentNode.parentNode.parentNode)">锐推</a> <a href="#" onclick="reply(this.parentNode.parentNode.parentNode)">回复</a></div>'
  str += '</div>';

  str += '<div style="clear: both;"></div>'
  str += '</div>'
  return str;
}

function assemblyTweets(tweets) {
  $("#timeline").html('');
  for(var i = 0; i < tweets.length; ++i) {
    $("#timeline").append(renderTweet(tweets[i]));
  }

  var transformList = [
    {
      //create links
      'expression': /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig,
      'replacement': "<a href='$1' onclick=\"openTab('$1')\">$1</a>"
    },
    {
      //create hash search links
      'expression': /(#\w*)/ig,
      'replacement': function(matchedValue) {
        var linkHref = TwitterLib.URLS.SEARCH + escape(matchedValue);
        return "<a href='" + linkHref + "' onclick=\"openTab('" + linkHref + "')\">" + matchedValue + "</a>"
      }
    },
    {
      //create users links
      'expression': /(@)(\w*)/ig,
      'replacement': "$1<a href='" + TwitterLib.URLS.BASE + "$2' onclick=\"openTab('" + TwitterLib.URLS.BASE + "$2')\">$2</a>"
    }
  ]

  for(var i = 0; i < transformList.length; ++i) {
    $("#timeline .tweet .text").replaceHtml(transformList[i].expression, transformList[i].replacement);
  }

  for(var unreadId in tweetManager.unreadTweets) {
    $("#tweet_" + unreadId).addClass('unread');
  }
  $(".tweet.unread").hoverFor(2000,
    function() {
      //Hovering for <time> seconds, let's read it.
      tweetManager.readTweet(this.id.split('_')[1]);
      $(this).removeClass('unread');
    },
    function() {
      //Starting countdown to read
      $(this).animate({
        backgroundColor: '#ee7'
      }, 2000);
    },
    function() {
      //Countdown aborted
      $(this).stop();
      $(this).css('background-color', '#eed077');
    }
  );
}

function loadTimeline(force) {
  $("#loading").show();
  if(force) {
    Paginator.firstPage();
  }
  tweetManager.giveMeTweets(onTimelineRetrieved, force);
}

function sendTweet() {
  $("#loading").show();
  $("#compose_tweet_area input[type='button']").attr("disabled", "disabled");
  $("#compose_tweet_area textarea").attr("disabled", "disabled");
  //var twitterBackend = this;
  twitterBackend.tweet(function(success, data, status) {
    $("#loading").hide();
    $("#compose_tweet_area input[type='button']").removeAttr("disabled");
    $("#compose_tweet_area textarea").removeAttr("disabled");
    if(success) {
      $("#compose_tweet_area textarea").val("");
      textareaChanged();
      showComposeArea();
      loadTimeline(true);
    } else {
    }
  }, $("#compose_tweet_area textarea").val());
}

function clearUserData() {
  if(!$("input[name='remember']").is(":checked")) {
    localStorage.removeItem('remember');
    localStorage.removeItem('username');
    localStorage.removeItem('password');
    localStorage.removeItem('logged');
  }
}

function signin() {
  if($("input[name='remember']").is(":checked")) {
    localStorage.remember = true;
    localStorage.username = $("input[name='user']").val();
    localStorage.password = $("input[name='password']").val();
    localStorage.logged = true;
  }
  doSignin($("input[name='user']").val(), $("input[name='password']").val());
  loadTimeline();

  $("#signin").hide();
  $("#workspace").show();
}

function signout() {
  localStorage.removeItem('logged');
  doSignout();
  window.close();
}

function retweet(node) {
  showComposeArea(true);
  var el = $("#compose_tweet_area textarea");
  var user = $(".user", node).text();
  var msg = $(".text", node).text();
  el.val("RT @" + user + ": " + msg);
  textareaChanged();
}

function reply(node) {
  showComposeArea(true);
  var el = $("#compose_tweet_area textarea");
  var user = $(".user", node).text();
  el.val("@" + user + " ");
  textareaChanged();
}

function showComposeArea(showOnly) {
  if(!$("#compose_tweet_area").is(':visible')) {
    $("#compose_tweet_area").show('blind', { direction: "vertical" });
    $("#compose_tweet img").attr('src', 'img/arrow_up.gif');
    $("#compose_tweet_area textarea").focus();
  } else if(!showOnly) {
    $("#compose_tweet_area").hide('blind', { direction: "vertical" });
    $("#compose_tweet img").attr('src', 'img/arrow_down.gif');
  }
}

function textareaChanged() {
  var el = $("#compose_tweet_area textarea");
  var availableChars = tweetManagerConst.MAX_TWEET_SIZE - el.val().length;
  var charsLeftEl = $("#compose_tweet_area .chars_left");
  charsLeftEl.text(availableChars);
  if(availableChars < 0) {
    charsLeftEl.css('color', 'red');
    $("#compose_tweet_area input[type='button']").attr("disabled", "disabled");
  } else {
    charsLeftEl.css('color', 'black');
    $("#compose_tweet_area input[type='button']").removeAttr("disabled");
  }
}

function newTweetsAvailable(count) {
  var text = "又有" + count + "条新推了";
  //if(count > 1)
  //  text += "s";
  text += "。立即更新!";
  $("#update_tweets").text(text);
  $("#update_tweets").fadeIn();
}

function loadNewTweets() {
  Paginator.firstPage();
  tweetManager.updateNewTweets();
  $("#update_tweets").fadeOut();
  loadTimeline();
}

$(function() {
  //var twitterBackend = this;
  if(!twitterBackend) {
    $("#signin").show();
    $("input[name='user']").focus();

    if(localStorage && localStorage.remember) {
      $("input[name='user']").val(localStorage.username);
      $("input[name='password']").val(localStorage.password);
      $("input[name='remember']").attr('checked', 'checked');
      if(localStorage.logged) {
        signin();
      }
    }
  } else {
    $("#workspace").show();
    if(tweetManager.newTweetsCount() > 0) {
      if(tweetManager.currentPage == 1) {
        tweetManager.updateNewTweets();
      } else {
        newTweetsAvailable(tweetManager.newTweetsCount());
      }
    }
    loadTimeline();
  }
  tweetManager.registerNewTweetsCallback(newTweetsAvailable);
  Paginator.initialize();
  textareaChanged();
});
