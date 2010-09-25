# -*- coding: utf-8 -*-

import os
import oauth
import random
from google.appengine.ext import webapp
from google.appengine.ext.webapp import template, util

TWITTER_CONSUMER_KEY = ''
TWITTER_CONSUMER_SECRET = ''
TWITTER_ACCESS_TOKEN = ''
TWITTER_ACCESS_TOKEN_SECRET =  ''

# 将文本文件中的一行随机发布到推特
class TweetPost(webapp.RequestHandler):
	def get(self):
		f = file('data.txt')
		list_of_all_the_lines = list(f) # 读取文件的所有内容到一个列表
		postText = random.choice(list_of_all_the_lines)
		try:
			all_the_text = f.read()
		finally:
			f.close()
		client = oauth.TwitterClient(TWITTER_CONSUMER_KEY,
							TWITTER_CONSUMER_SECRET, None)
		param = {'status': postText}
		client.make_request('http://twitter.com/statuses/update.json',
						token=TWITTER_ACCESS_TOKEN,
						secret=TWITTER_ACCESS_TOKEN_SECRET,
						additional_params=param,
						protected=True,
						method='POST')
		self.response.out.write('ok')