from google.appengine.ext import db
from google.appengine.api import users
from django.utils import simplejson
import string
import random
import time
import os
import re
import twitter

#特定时间消息
def ProcessTimeTrigger(ti, st, api):
  if ti.tm_hour == 22 and ti.tm_min == 30:
    msg = [ \
      u'差不多该去进货了……', \
      u'新的一天又开始了，再去倒一点金坷垃来买', \
      u'今天再去去买一些嗯', \
      u'今天也要一鼓作气卖光光哦～', \
      u'进货去了……'
    ]
    api.PostUpdate(random.choice(msg))
  elif ti.tm_hour == 22 and ti.tm_min == 45:
    if st.KinkoraInBase > 200:
      msg = [ \
        u'库存还有%d……今天还是不进货了', \
        u'还有%d袋都卖不掉。果断不敢进货了' \
      ]
      api.PostUpdate(random.choice(msg)%st.KoraInBase)
    else:
      msg = [ \
        u'入手%d个金坷垃，花了%d元。今天的市场价是%d' \
      ]
      incoming = random.randint(50, 150)
      st.Price = random.randint(10, 30)
      cost = incoming * 10;
      st.KoraInBase = st.KoraInBase + incoming
      st.HaveMoney = st.HaveMoney - cost
      api.PostUpdate(random.choice(msg)%(incoming, cost, st.Price))
      if st.HaveMoney < 0:
        api.PostUpdate(u'该死……没钱了，今天只能先赊账了')
        st.HaveMoney = 0
  elif ti.tm_hour == 23 and ti.tm_min == 45:
    msg = [ \
      u'油条配豆浆，有名的传统早餐', \
      u'油饼配豆浆，有名的传统早餐', \
      u'稀饭萝卜干，有名的传统早餐', \
      u'啃面包喝牛奶中……', \
      u'坷垃进食中……', \
      u'Now eating...', \
      u'看什么看，没见过本大人吃早饭么', \
      u'唔……今天的金坷垃饼真香……开吃', \
      u'早餐吃金坷垃馒头很不错，真的', \
      u'もぐもぐ……金坷垃果然是美国圣地亚哥传统早餐无误', \
      u'群众早餐太简陋，我们需要金坷垃。大家都来吃吧～坷垃粥煮好了', \
      u'金坷垃，早上吃一包，精神一上午～'
    ]
    api.PostUpdate(random.choice(msg))
  elif ti.tm_hour == 4 and ti.tm_min == 0:
    msg = [ \
      u'午饭时间到了。开动～', \
      u'今天中午去找蓝蓝路解决午餐好了', \
      u'呜——教祖样今天也这样精神啊。刚买了2个板烧', \
      u'今天的午饭是金坷垃粥，炒化肥，坷垃化肥汤。いただきます！', \
      u'……（压缩饼干咀嚼中', \
      u'坷垃汤配化肥包子，无上享受啊哈哈', \
      u'亲手烤出来的金坷垃饼，在撒上化肥粉，果然和外面买来的没得比的好啊！' \
    ]
    api.PostUpdate(random.choice(msg))
  elif ti.tm_hour == 10 and ti.tm_min == 0:
    msg = [ \
      u'电饭煲煲出来的金坷垃还真不赖，いただきます！。', \
      u'晚上吃干饭……', \
      u'晚上吃稀饭……', \
      u'晚上吃面条……', \
      u'晚上吃坷垃馅饺子……', \
      u'晚上吃坷垃包……' \
    ]
    api.PostUpdate(random.choice(msg))
  elif ti.tm_hour == 15 and ti.tm_min == 59:
    msg = [ \
      u'睡觉去了。目前库存还有%d袋金坷垃，手头资金%d元' \
    ]
    api.PostUpdate(random.choice(msg)%(st.KoraInBase, st.HaveMoney))

  return st
