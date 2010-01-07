#!/usr/bin/env python

from google.appengine.ext import db
from google.appengine.api import users
from django.utils import simplejson
import string
import random
import time
import os
import re
import twitter
import timet
import rept
import randt
import dologin

class Status(db.Model):
  LastMsgId = db.IntegerProperty()
  LastReplyId = db.IntegerProperty()
  KoraInBase = db.IntegerProperty()
  HaveMoney = db.IntegerProperty()
  Price = db.IntegerProperty()
  UpdateLeft = db.IntegerProperty()

def getStatus():
  stat = Status.all()
  res = stat.fetch(limit=1)
  for s in stat:
    return s
  return Status(LastMsgId=0, LastReplyId=0, KoraInBase=100, HaveMoney=0, Price=10, UpdateLeft=5)

def ProcessMsg(ti, tw, st, api):
  return st

def main():
  random.seed(time.gmtime())
  stat = getStatus()
  tapi = doLogin()
  currenttime = time.gmtime()
  
#只有在 6:00 ~ 24:00 活动
  if currenttime.tm_hour >= 21 or currenttime.tm_hour <= 15:
#处理回复消息
    LastReplyId = 0;
    if stat.LastReplyId == 0:
      Replies = tapi.GetReplies()
    else:
      Replies = tapi.GetReplies(since_id=stat.LastReplyId)

    for Reply in Replies:
      stat = rept.ProcessReply(currenttime, Reply, stat, tapi)
      if LastReplyId < Reply.id:
        LastReplyId = Reply.id
      stat.LastReplyId = LastReplyId

#处理时间线消息
#    LastMsgId = 0
#    if stat.LastMsgId == 0:
#      Twitters = tapi.GetFriendTimeline()
#    else
#      Twitters = tapi.GetFriendTimeline(since_id=stat.LastMsgId)
#
#    for Twitter in Twittrs:
#      stat = ProcessMsg(currenttime, Twitter, stat, tapi)
#      if LastMsgId < Twitter.id:
#        LastMsgId = Twitter.id
#        stat.LastMsgId = LastMsgId
#处理时间触发
    stat = timet.ProcessTimeTrigger( currenttime, stat, tapi )

#随机发信息
    stat.UpdateLeft = stat.UpdateLeft - 1
    if stat.UpdateLeft == 0:
      randt.PostRandomMsg(tapi)
      stat.UpdateLeft = random.randint(45, 90)
  
  stat.put()


if __name__ == '__main__':
  main()


