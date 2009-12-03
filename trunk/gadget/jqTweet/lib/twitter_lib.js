/**
 * jqTweet twitter API library
 *
 * $Revision: 1.1 $
 * $Date: 2009/12/03 14:21:10 $
 * $Author: sin_sin $
 *
 * Copyleft from the author of Chrome Bird.
 */
function TwitterLib(username, password) {
  this.username = username;
  this.password = password;
  this.remainingHitsCount = 150;
  this.updateRemainingHits();
}
TwitterLib.URLS = {
  BASE: 'https://twitter.com/',
  SEARCH: 'https://twitter.com/search?q=',
  PROXY: 'http://nest.onedd.net/api/'
}
TwitterLib.prototype = {
  ajaxRequest: function(url, callback, context, params, httpMethod) {
    if(!httpMethod)
      httpMethod = "GET";
    var _this = this;
    try {
        netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
    } catch (e) {
        // no cross-domain supported?
        // Firefox need about:config set signed.applets.codebase_principal_support = true
    }
    $.ajax({
      type: httpMethod,
      url: TwitterLib.URLS.PROXY + url + ".json",
      data: params,
      dataType: "json",
      timeout: 6000,
      beforeSend: function(xhr) {
        var auth = $.base64.encode(_this.username + ":" + _this.password);
        xhr.setRequestHeader("Authorization", "Basic " + auth);
      },
      success: function(data, status) {
        callback(true, data, status, context);
      },
      error: function (request, status, error) {
        callback(false, null, status, context);
      }
    });
  },

  updateRemainingHits: function() {
    var _this = this;
    this.ajaxRequest("account/rate_limit_status", function(success, data) {
      if(success) {
        _this.remainingHitsCount = data.remaining_hits;
        //console.log(_this.remainingHitsCount);
      }
    });
  },

  remainingHits: function() {
    return this.remainingHitsCount;
  },

  tweet: function(callback, msg) {
    var params = { status: msg, source: "twitterfox" };
    this.ajaxRequest('statuses/update', callback, null, params, "POST");
  },

  friendsTimeline: function(callback, context, count, page, sinceId, maxId) {
    var params = {};
    if(count)
      params.count = count;
    if(page)
      params.page = page;
    if(sinceId)
      params.since_id = sinceId;
    if(maxId)
      params.max_id = maxId;

    this.ajaxRequest("statuses/friends_timeline", callback, context, params);
    this.updateRemainingHits();
  }
}
