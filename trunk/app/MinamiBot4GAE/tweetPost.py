# -*- coding: utf-8 -*-
# 
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
		f = open("data.txt", "r")
		list_of_all_the_lines = [l.strip() for l in f.readlines()]
		#确保正确关闭文件
		try:
			all_the_text = f.read()
		finally:
			f.close()
		postText = random.choice(list_of_all_the_lines)
		#~ postText2 = unicode(postText, "utf-8") #省略参数将用python默认的ASCII来解码
		postText3 = postText.decode("utf-8") #把str转换成unicode是decode，unicode函数作用与之相同
		if (len(postText3) > 140): #如果大于140个字长则裁切到140以内
			postText4 = postText.decode('utf8')[:137].encode('utf8') + '...'#先转换成unicode，再取子串，然后转换成utf-8
		else:
			postText4 = postText
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