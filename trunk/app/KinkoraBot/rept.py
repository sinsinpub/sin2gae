from google.appengine.ext import db
from google.appengine.api import users
from django.utils import simplejson
import string
import random
import time
import os
import re
import twitter

def ProcessReply(ti, tw, st, api):
#错别字
  if tw.text.find(u'金坷拉') != -1:
    api.PostUpdate(u'其实是垃不是拉系列', in_reply_to_status_id=tw.id)
#follow系的消息处理
  elif tw.text.find(u'followして') != -1:
    msg = [ \
      u'@%s %sさんフォローありです。これからもよろしくね', \
      u'@%s 新しいお客様ですか。%sさんよろしくね', \
      u'@%s いらっしゃいませ、%sさん。金コーラ要りますのか', \
      u'@%s おまえ（%s）、金コーラを買え！' \
    ]
    try:
      api.CreateFriendship(tw.user.id)
      api.PostUpdate(random.choice(msg)%(tw.user.screen_name, tw.user.name), in_reply_to_status_id=tw.id)
    except Exception:
      pass
  elif tw.text.find(u'followしない') != -1:
    try:
      api.DestroyFriendship(tw.user.id)
    except Exception:
      pass
  elif tw.text.find(u'follow') != -1 or tw.text.find(u'佛我') != -1:
    if tw.text.find(u'别') != -1 or tw.text.find(u'不要') != -1:
      try:
        api.DestroyFriendship(tw.user.id)
      except Exception:
        pass
    else:
      msg = [ \
        u'@%s 谢%s佛我，我佛你。本店专卖金坷垃，想要来点吗？', \
        u'@%s %s真是大好人（泪）……作为谢礼，这包金坷垃就送给你了！', \
        u'@%s 什么？%s说的金坷垃？我不知道啊哈哈哈哈哈哈哈', \
        u'@%s 非洲农业不发达，我要支援它。金坷垃不能给%s。（嘛……看在fo我的面上，fo你好了', \
        u'@%s 欢迎光临，%s～今天金坷垃大甩卖，请问%s需要吗？' \
      ]
      try:
        api.CreateFriendship(tw.user.screen_name)
        api.PostUpdate((random.choice(msg)%(tw.user.screen_name, tw.user.name)), in_reply_to_status_id=tw.id)
      except Exception:
        pass
#金坷垃销售系消息
  elif tw.text.find(u'金コーラ') != -1 and tw.text.find(u'ください') != -1:
    if st.KoraInBase == 0:
      msg = [ \
        u'@%s お客様、申し訳ございませんが、もう在庫切りになりました' \
      ]
      api.PostUpdate((random.choice(msg)%(tw.user.screen_name)), in_reply_to_status_id=tw.id)
    else:
      msg = [ \
        u'@%s あなたの買った金コーラは%dです。ありがとうございました、%d円いただきます。' \
      ]
      tobuy = random.randint(1, 20)
      if st.KoraInBase < tobuy:
        tobuy = st.KorraInBase
      cost = (tobuy * st.Price) * 13
      api.PostUpdate((random.choice(msg)%(tw.user.screen_name, tobuy, cost)), tw.id)
      st.KoraInBase = st.KoraInBase - tobuy
      st.HaveMoney = st.HaveMoney + cost
  elif tw.text.find(u'金坷垃') != -1 and ( tw.text.find(u'买') != -1 or tw.text.find(u'给我') != -1 or tw.text.find(u'我要') != -1):
    if st.KoraInBase == 0:
      msg = [ \
        u'@%s 抱歉，已经没有库存了，都卖光了。下次再来吧' \
      ]
      api.PostUpdate((random.choice(msg)%(tw.user.screen_name)), in_reply_to_status_id=tw.id)
    else:
      msg = [ \
        u'@%s 您买了%d个金坷垃。谢谢惠顾，收您%d元' \
      ]
      tobuy = random.randint(1, 20)
      if st.KoraInBase < tobuy:
        tobuy = st.KoraInBase
      cost = tobuy * st.Price
      api.PostUpdate((random.choice(msg)%(tw.user.screen_name, tobuy, cost)), tw.id)
      st.KoraInBase = st.KoraInBase - tobuy
      st.HaveMoney = st.HaveMoney + cost
  elif tw.text.find(u'金坷垃') != -1 and tw.text.find(u'试用') != -1:
    msg = [ \
      u'@%s 买几袋回去用吧，反正又不贵', \
      u'@%s 本公司不提供免费体验服务' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'金坷垃') != -1 and tw.text.find(u'库存') != -1:
    api.PostUpdate(u'@%s %d袋'%(tw.user.screen_name, st.KoraInBase), in_reply_to_status_id=tw.id)
  elif tw.text.find(u'金坷垃') != -1 and (tw.text.find(u'怎么卖') != -1 or tw.text.find(u'多少钱') != -1):
    api.PostUpdate(u'@%s 现在市场价是%d'%(tw.user.screen_name, st.Price), in_reply_to_status_id=tw.id)
#问候语处理
  elif tw.text.find(u'早上好') != -1 or tw.text.find(u'早安') != -1:
    msg = [ \
      u'@%s 早安', \
      u'@%s 早上好', \
      u'@%s 好你妹', \
      u'@%s 好你老木', \
      u'@%s 早……' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'おはよ') != -1:
    msg = [ \
      u'@%s おはようございます', \
      u'@%s おはよう', \
      u'@%s おはようっす', \
      u'@%s おはー' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'日安') != -1 or tw.text.find(u'午安') != -1 or tw.text.find(u'你好') != -1:
    msg = [ \
      u'@%s 你好', \
      u'@%s 日安', \
      u'哟，@%s', \
      u'@%s （假装没看到', \
      u'@%s 嗯，好……' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'こんにち') != -1:
    msg = [ \
      u'@%s こんにちは' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'晚上好') != -1:
    msg = [ \
      u'@%s 晚上好', \
      u'@%s 好', \
      u'@%s 嗯', \
      u'@%s （假装没看到' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'こんばんわ') != -1 or tw.text.find(u'こんばんは') != -1:
    msg = [ \
      u'@%s こんばんは' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'晚安') != -1 or tw.text.find(u'好梦') != -1:
    msg = [ \
      u'@%s 睡好', \
      u'@%s 要梦到金坷垃哦', \
      u'@%s 好梦' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'おやすみ') != -1 or tw.text.find(u'寝る') != -1:
    msg = [ \
      u'@%s ノシ', \
      u'@%s おやすみなさい', \
      u'@%s おやす金コーラー', \
      u'@%s おやすみー' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  elif tw.text.find(u'のし') != -1 or tw.text.find(u'ノシ') != -1:
    msg = [ \
      u'@%s ノシ', \
      u'@%s /~' \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
#其他
  elif tw.text.find(u'骗人') != -1:
    msg = [ \
      u'@%s 没骗你。我们还花了巨资聘请xp为我们专门打造了一片宣传碟拿到C77卖的', \
      u'@%s 用事实说话才是本公司的一贯作风', \
      u'@%s 不信拉倒……我为什么要骗你', \
    ]
    api.PostUpdate(random.choice(msg)%tw.user.screen_name, in_reply_to_status_id=tw.id)
  return st
