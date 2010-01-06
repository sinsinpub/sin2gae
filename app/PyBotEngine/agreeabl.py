# This Python file uses the following encoding: utf-8 
"""
This is agreeabl, a friendly little twitter bot
"""
import os
import urllib
import urllib2
import random
import datetime
import logging
import re
import types

import feedparser
import wsgiref.handlers

from dateutil.parser import parse
from google.appengine.ext.webapp import template
from google.appengine.ext import webapp, db
from google.appengine.api import urlfetch
from google.appengine.api import memcache
from django.utils import simplejson


class TwitterAccount(db.Model):
    """ 
    Username and password for the Twitter account associated with the bot
    """
    username = db.StringProperty()
    password = db.StringProperty()


class Tracker(db.Model):
    """
    Tracker simply stores the date time of the last successfully downloaded 
    message so we don't process messages twice. 
    """
    last_tweet = db.DateTimeProperty()


class ReplyMessage(db.Model):
	"""
	Messages that will be used to reply randomly.
	"""
	cond = db.StringProperty()
	msg = db.StringProperty()


twitter_account = db.GqlQuery("SELECT * FROM TwitterAccount").get()

if twitter_account == None or twitter_account.username == '':
    TwitterAccount(username='', password='').put()
    raise Exception("Please set up you twitter credentials in your datastore")
else:
    username = twitter_account.username
    password = twitter_account.password

mentions_url = 'http://%s:%s@twitter.com/statuses/mentions.atom' % \
(username, password)
status_url = 'http://twitter.com/statuses/update.xml'
friend_url = 'http://twitter.com/friendships/create.xml'
is_friend_url = 'http://twitter.com/friendships/exists.json'
user_profile_url = 'http://twitter.com/users/show/%s.json' % username
user_timeline_url = 'http://twitter.com/statuses/user_timeline/%s.json' % \
username
msg_url = 'http://twitter.com/statuses/show/%s.json'

msg_list = db.GqlQuery("SELECT * FROM ReplyMessage").get()
if msg_list == None:
    ReplyMessage(cond='', msg='').put()
    msg_list = [
        "%s that's what my mum always said and it's hard to argue with her.",
        "%s I feel your pain...",
        "%s you go girl!",
        "%s you say the smartest stuff sometimes.",
        "%s yeah, me too.",
        "%s I get like that sometimes too.",
        "%s good thinking!",
        "%s that deserves a hug.",
        "%s totally!",
        "%s my feelings exactly!",
        "%s that is very true",
        "%s so true, so true...",
        "%s you are so right...",
        "%s couldn't agree more.",
        "%s if only more people were as thoughtful as you.",
        "%s yeah for sure",
        "%s you know a tibetan monk once said the same thing to me and it \
        always stuck in my mind.",
        "%s those there are wise words. Wise words indeed.",
        "%s if more people thought like you we wouldn't need laws. Or taxes. \
        Or Conroy's clean feed.",
        "%s yup like I said before - you just can't live without fresh fruit \
        and clean water.",
        "%s yeah - it really is the way things are going these days.",
        "%s that sure sounds like fun"
        ]


class Index(webapp.RequestHandler):
    """
    Render the homepage. This looks similar to a regular twitter homepage and 
    shows recent conversations the bot has had.
    """
    def get(self):
        """ default action for / URL requests """

        user_profile = get_from_cache("user_profile", user_profile_url)
        user_profile = dict2class(user_profile)
        user_timeline = get_from_cache("user_timeline", user_timeline_url)

        user_timeline_formated = []

        i = 0
        for entry in user_timeline:
            entry = dict2class(entry)
            entry.user = dict2class(entry.user)
            entry.text = re.sub(r'(\A|\s)@(\w+)', \
            r'\1@<a href="http://www.twitter.com/\2">\2</a>', entry.text)
            entry.created_at = \
                parse(entry.created_at).strftime("%I:%M%p %A, %d %B %Y")
            if entry.user.screen_name == username and \
            entry.in_reply_to_status_id != None:
                try:
                    reply_msg = get_from_cache(str(entry.in_reply_to_status_id), \
                    msg_url % entry.in_reply_to_status_id)
                    user_timeline.insert(i + 1, dict2class(reply_msg))
                except IOError:
                    broken_url = msg_url % entry.in_reply_to_status_id
                    logging.warn("Oops. Couldn't fetch " + broken_url)
            user_timeline_formated.append(entry)
            i += 1

        template_values = {
                           "username": username,
                           "user_profile": user_profile,
                           "user_timeline": user_timeline_formated
            }

        path = os.path.join(os.path.dirname(__file__), 'index.html')
        self.response.out.write(template.render(path, template_values))


class Responder(webapp.RequestHandler):
    """
    Fetch all mentions from the twitter API and generate responses.
    """
    def get(self):
        """ default action for /request URL requests """

        tracker = db.GqlQuery("SELECT * FROM Tracker").get()

        if tracker == None:
            tracker = Tracker(last_tweet=datetime.datetime(1970, 1, 1, 0, 0, 0))

        prev_last_tweet = tracker.last_tweet

        mentions = feedparser.parse(mentions_url)
        logging.debug(mentions)

        for entry in mentions['entries']:

            p = entry.published_parsed
            pub_date = datetime.datetime(p[0], p[1], p[2], p[3], p[4], p[5])

            if prev_last_tweet < pub_date:
                # <title>User: @agreeabl geez I'd love some cookies</title>
                author = entry.title.split(": ")[0]
                tweet = entry.title.split(": ")[1]
                logging.debug(tweet)

                #<id>tag:twitter.com,200:http://twitter.com/User/statuses/1</id>
                msg_id = entry.id.split('/')[5]

                # load reply messages
                msgEntries = db.GqlQuery("SELECT * FROM ReplyMessage").fetch(limit=100)
                if msgEntries == None:
                    ReplyMessage(cond='', msg='').put()
                    msgList = msg_list
                else:
                    msgList = []
                    for repMsg in msgList:
                        msgList.append(repMsg['msg'])

                # choose and compile a message
                selected_msg = random.choice(msgList)
                msg = selected_msg % ('@' + author)

                # only process if this is a directed to the bot
                if tweet.split(' ')[0] == '@%s' % username:

                    if tracker.last_tweet < pub_date:
                        tracker.last_tweet = pub_date
                        tracker.put()

                    reply(msg, msg_id)
                    if is_friend(author) != 'true':
                        friend(author)

                logging.info('old_last_tweet: %s; new_last_tweet: %s; \
                pub_date: %s; msg_id: %s; author: %s; tweet: %s; msg: %s' % \
                (prev_last_tweet, tracker.last_tweet, pub_date, msg_id, \
                 author, tweet, msg))


def reply(msg, msg_id):
    """ Format a reply and post it to the Twitter API """
    
    form_fields = {
      "status": msg,
      "in_reply_to_status_id": msg_id
    }
    form_data = urllib.urlencode(form_fields)
    api_post(status_url, form_data)


def friend(author):
    """ Make the bot follow someone """
    
    form_fields = {
      "screen_name": author
    }
    form_data = urllib.urlencode(form_fields)
    api_post(friend_url, form_data)


def is_friend(author):
    """ Check if the bot is following someone """
    
    query_string = '?user_a=%s&user_b=%s' % (username, author)
    return api_get(is_friend_url, query_string).read()


def api_get(url, query_string=""):
    """Make a GET request against the twitter API, handle authentication"""

    password_mgr = urllib2.HTTPPasswordMgrWithDefaultRealm()
    password_mgr.add_password("Twitter API", "http://twitter.com/", username, \
    password)
    handler = urllib2.HTTPBasicAuthHandler(password_mgr)
    opener = urllib2.build_opener(urllib2.HTTPHandler, handler)
    urllib2.install_opener(opener)

    return urllib2.urlopen(url + query_string)


def api_post(url, form_data):
    """POST to the twitter API, handle authentication"""

    password_mgr = urllib2.HTTPPasswordMgrWithDefaultRealm()
    password_mgr.add_password("Twitter API", "http://twitter.com/", username, \
    password)
    handler = urllib2.HTTPBasicAuthHandler(password_mgr)
    opener = urllib2.build_opener(urllib2.HTTPHandler, handler)
    urllib2.install_opener(opener)

    return urllib2.urlopen(url, form_data)


def get_from_cache(key, url, query_string="", timeout=120):
    """ Grab a value from the cache, or go to the API if it's not found """
    
    value = memcache.get(key)
    if value is None:
        value = simplejson.load(api_get(url, query_string))
        memcache.add(key, value, timeout)
    return value


def dict2class(dic):
    """Return a class that has same attributes/values as dict key/value"""

    #see if it is indeed a dictionary
    if type(dic) != types.DictType:
        return dic

    #define a dummy class
    class Dummy:
        pass

    class_ = Dummy
    for elem in dic.keys():
        class_.__dict__[elem] = dic[elem]
    return class_


class ProxyGet(webapp.RequestHandler):
	def get(self):
		targetUrl = self.request.get('url')
		if targetUrl == '':
			self.response.out.write('I\'m in position')
		else:
			targetUrl = 'http://' + targetUrl
			result = urlfetch.fetch(url=targetUrl,method=urlfetch.GET,allow_truncated=True,follow_redirects=False)
			self.response.out.write(result.content);


def main():
    """ Handle requests, do CGI stuff """

    debug = False

    if os.environ['SERVER_NAME'] == 'localhost':
        logging.getLogger().setLevel(logging.DEBUG)
        debug = True

    application = webapp.WSGIApplication(
                                         [
                                          ('/', Index),
                                          ('/get', ProxyGet),
                                          ('/responder', Responder)
                                          ],
                                         debug=debug
                                         )
    wsgiref.handlers.CGIHandler().run(application)

if __name__ == "__main__":
    main()
