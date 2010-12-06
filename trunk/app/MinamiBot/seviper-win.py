#!/usr/bin/env python
#coding=UTF-8
#Seviper Version 0.0.3b
#By Zeray Rice(http://www.fanhe.org)
#一个在Windows命令提示符下使用的简易饭否客户端
#由minamimaster(http://fanfou.com/shimada_minami)修改
#如果使用Windows的命令提示符，本文件编码必须为系统默认编码

#希望加上显示主时间线的模块...

import urllib
import urllib2
import urllib2_file
import base64
import sys
import os
import xml.dom.minidom
import mimetypes

def CheckFanfou(key, usr): #添加一个usr参数 用来提示用户已经验证成功
	print '正在验证用户名和密码...'
	checkreq = urllib2.Request('http://api2.fanfou.com/statuses/user_timeline.xml?count=1')
	checkreq.add_header('Authorization',key)
	try:
		response = urllib2.urlopen(checkreq)
	except urllib2.URLError,e:
		if hasattr(e,'code'):
			if e.code == 401:
				print '用户名密码错误！'
				return 1
		else:
			print '未知错误！请检查网络连接！'
	else:
		print '用户 ' + usr + ' 验证成功！'
		return 0

def GetReply(dom):
	root = dom.documentElement
	replies = root.getElementsByTagName('text')
	senders = root.getElementsByTagName('name')
	if len(root.childNodes) > 1 :
		for reply,sender in zip(replies,senders) :
			print sender.childNodes[0].data + ': ' + reply.childNodes[0].data
		sid = root.getElementsByTagName('id')
		f = file(config,'w')
		f.write('key=' + key + '\n')
		f.write('lastreply=' + sid[0].childNodes[0].data + '\n')#这里的lastreply id 存储到了第二行？
		f.close()
	else:
		print '当前没有新回复！'

def CreatConf(): #改了一下这里的结构 大概是想写一个类似 do while 的结构吧
	while True:
		usr = raw_input('饭否用户名：')
		pwd = raw_input('饭否密码（明文）：')
		base64string = base64.encodestring(
			'%s:%s' % (usr,pwd))[:-1]
		key = "Basic %s" % base64string	
		if CheckFanfou(key, usr) == 0:
			break
	f = file(config,'w')
	f.write('key=' + key + '\n')
	f.close()
	return key


usr_home = os.path.expanduser('~')
config = usr_home + '/.fanfouconf'

if os.path.exists(config) == False :
	key = CreatConf()
else:
	f = file(config,'r')
	ary = f.readlines()
	f.close()
	key = ary[0][4:]
	key = key[:-1]
	if len(ary) > 1:
		lastreply = ary[1][10:]
		lastreply = lastreply[:-1]
	else:
		lastreply = ''

while True: #大循环
	r = raw_input('请输入命令：')
	if r == 'quit' or r == 'exit':
		sys.exit()
	elif r == 'reply':
		url = 'http://api2.fanfou.com/statuses/replies.xml?since_id=' + lastreply
		print url
		req = urllib2.Request(url)
		req.add_header('Authorization',key)
		response = urllib2.urlopen(req)
		reply = response.read()
		dom = xml.dom.minidom.parseString(reply)
		GetReply(dom)
		print '''=======================================================================
		'''
		continue
	elif r == 'new':
		CreatConf()
		continue
	elif r == 'post':
		postTweet = raw_input('请输入要发布的信息：')
		postdata = {
			'status' : ' ',
			'source' : 'seviper'
		}
		postdata['status'] = postTweet

		if len(postdata['status'])>140:
			postdata['status'] = postdata['status'][:137] + '...'
			print '超过140字了！系统会自动截断消息！'

		url = 'http://api2.fanfou.com/statuses/update.xml'
		data = urllib.urlencode(postdata)
		req = urllib2.Request(url,data)
		req.add_header('Authorization',key)

		try:
			response = urllib2.urlopen(req)
		except:
			print '发送失败,请检查网络连接！或者执行 new 来重新建立配置文件。'
		else:
			print '消息 ' + postdata['status'] + ' 发送成功！'