from google.appengine.ext import db
from google.appengine.api import users
from django.utils import simplejson
import string
import random
import time
import os
import re
import twitter

#随机消息
def PostRandomMsg(api):
  msg = [ \
    u'用了金坷垃，小麦亩产一千八！', \
    u'用了金坷垃，植物可以吸收10米以下的氮磷钾！', \
    u'用了金坷垃，再也不愁粮食产量不足了！', \
    u'肥料掺了金坷垃，一袋能顶两袋撒', \
    u'用了金坷垃，粮食再也不用进口啦！', \
    u'知道吗，我们公司为了拓展日本市场，特意在C77的时候出过专辑哦' \
  ]
  api.PostUpdate(random.choice(msg))
