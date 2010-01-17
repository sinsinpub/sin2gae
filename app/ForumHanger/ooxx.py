#!/usr/bin/python2.5
import urllib
from google.appengine.api import users
from google.appengine.api import urlfetch
from google.appengine.ext import db
from google.appengine.ext import webapp
from google.appengine.ext.webapp.util import run_wsgi_app

class Forums(db.Model):
	bbsId = db.StringProperty(required=True)
	bbsType = db.StringProperty()
	baseUrl = db.StringProperty(required=True)
	loginPhp = db.StringProperty(required=True)
	indexPhp = db.StringProperty(required=True)
	registerPhp = db.StringProperty(required=True)
	userId = db.StringProperty(required=True)
	passWd = db.StringProperty(required=True)
	cookie = db.StringProperty()

returnButton = "<p><input type='button' onclick='history.go(-1)' value='Back'/></p>"

class MainPage(webapp.RequestHandler):
	def get(self):
		self.response.out.write("<html><style>body {font-family: Verdana; font-size: 12px}</style><body>")
		self.response.out.write("<p align=center><strong>Discuz Forum Auto Refresher v4</strong></p><p align=center>")
		self.response.out.write("<a href='/viewlogin'>View forums data</a>")
		self.response.out.write("</p><p align=center>Sources from Riatre(258921)<br/>Modified by sin_sin, LCK<br/>Powered by Google AppEngine</p>")
		self.response.out.write("</body></html>")

class Relogin(webapp.RequestHandler):
	def get(self):
		try:
			bbs = self.request.get('bbs')
		except (TypeError, ValueError):
			self.response.out.write("Required parameter missing")
		if bbs == '':
			self.response.out.write("Required parameter missing")
		else:
			bbs_query = db.GqlQuery("SELECT * FROM Forums WHERE bbsId=:1 LIMIT 1",bbs)
			
			if bbs_query.count() == 0:
				self.response.out.write("No forum id={"+bbs+"} found")
			else:
				for b in bbs_query:
					bbsurl = b.baseUrl
					bbstype = b.bbsType
					login = b.loginPhp
					index = b.indexPhp
					register = b.registerPhp
					userid = b.userId
					password = b.passWd
					cookie = b.cookie;

				if bbstype == 'pw':
					form_data = {
						"forward": "",
						"jumpurl": index,
						"step": "2",
						"lgt": "0",
						"pwuser": userid,
						"pwpwd": password,
						"hideid": "0",
						"cktime": "31536000"
					}
				else:
					form_data = {
						"formhash": "7b4cf3bb",
						"referer": index,
						"loginfield": "username",
						"username": userid,
						"password": password,
						"questionid": "0"
					}

				reqHeader = {
					"Cookie": cookie,
					"User-Agent": "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/532.2 (KHTML, like Gecko) Chrome/4.0.221.6 Safari/532.2"
				}

				trying = True
				while trying:
					try:
						result = urlfetch.fetch(url=bbsurl+index,method=urlfetch.GET,headers=reqHeader,allow_truncated=True,follow_redirects=False)
						trying = False
					except:
						trying = True

				if result.content.find(register.encode('utf_8')) > 0:
					self.response.out.write('---- Login has expired ----<br/>')
					if bbstype == 'pw':
						loginUri = login
					else:
						loginUri = login+"?action=login&loginsubmit=yes"
						trying = True
						while trying:
							try:
								loginpage = urlfetch.fetch(url=bbsurl+login+"?action=login",method=urlfetch.GET,allow_truncated=True,follow_redirects=False)
								trying = False
							except:
								trying = True
						formhash = loginpage.content[loginpage.content.find('formhash" value="')+17:loginpage.content.find('formhash" value="')+25]
						form_data["formhash"] = formhash

					form = urllib.urlencode(form_data)
					trying = True
					while trying:
						try:
							result = urlfetch.fetch(url=bbsurl+loginUri,method=urlfetch.POST,payload=form,allow_truncated=True,follow_redirects=False)
							trying = False
						except:
							trying = True

					if bbstype == 'pw':
						sid_start = result.headers['Set-Cookie'].find('_ck_info=')
						sid_end = result.headers['Set-Cookie'][sid_start:].find(';')
						sid = result.headers['Set-Cookie'][sid_start-5:sid_start+sid_end]
						auth_start = result.headers['Set-Cookie'].find('_winduser=')
						auth_end = result.headers['Set-Cookie'][auth_start:].find(';')
						auth = result.headers['Set-Cookie'][auth_start-5:auth_start+auth_end]
						cookie = auth+'; '+sid
					else:
						sid_start = result.headers['Set-Cookie'].find('_sid=')
						sid_end = result.headers['Set-Cookie'].find(';')
						sid = result.headers['Set-Cookie'][sid_start-3:sid_end]
						auth_start = result.headers['Set-Cookie'].find('_auth=')
						auth_end = result.headers['Set-Cookie'][auth_start:].find(';')
						auth = result.headers['Set-Cookie'][auth_start-3:auth_start+auth_end]
						cookie = sid+'; '+auth

					b.cookie = cookie
					b.put()
					self.response.out.write(cookie+"<br/>")
					reqHeader["Cookie"] = cookie
					trying = True
					while trying:
						try:
							result = urlfetch.fetch(url=bbsurl+index,method=urlfetch.GET,headers=reqHeader,allow_truncated=True,follow_redirects=False)
							trying = False
						except:
							trying = True

				self.response.out.write('---- Index fetch OK ----<br/>')
		self.response.out.write(returnButton)

class Login(webapp.RequestHandler):
	def get(self):
		try:
			bbs = self.request.get('bbs')
		except (TypeError, ValueError):
			self.response.out.write("Required parameter missing")
		if bbs == '':
			self.response.out.write("Required parameter missing")
		else:
			bbs_query = db.GqlQuery("SELECT * FROM Forums WHERE bbsId=:1 LIMIT 1",bbs)
			
			if bbs_query.count() == 0:
				self.response.out.write("No forum id={"+bbs+"} found")
			else:
				for b in bbs_query:
					bbsurl = b.baseUrl
					bbstype = b.bbsType
					login = b.loginPhp
					index = b.indexPhp
					userid = b.userId
					password = b.passWd
					cookie = b.cookie;

				if bbstype == 'pw':
					form_data = {
						"forward": "",
						"jumpurl": index,
						"step": "2",
						"lgt": "0",
						"pwuser": userid,
						"pwpwd": password,
						"hideid": "0",
						"cktime": "31536000"
					}
				else:
					form_data = {
						"formhash": "7b4cf3bb",
						"referer": index,
						"loginfield": "username",
						"username": userid,
						"password": password,
						"questionid": "0"
					}
					loginpage = urlfetch.fetch(url=bbsurl+login+"?action=login",method=urlfetch.GET,allow_truncated=True,follow_redirects=False)
					formhash = loginpage.content[loginpage.content.find('formhash" value="')+17:loginpage.content.find('formhash" value="')+25]
					form_data["formhash"] = formhash

				form = urllib.urlencode(form_data)
				if bbstype == 'pw':
					loginUri = login
				else:
					loginUri = login+"?action=login&loginsubmit=yes"
				result = urlfetch.fetch(url=bbsurl+loginUri,method=urlfetch.POST,payload=form,allow_truncated=True,follow_redirects=False)
				self.response.out.write("<li>Responsed Set-Cookie:<br/>")
				self.response.out.write(result.headers['Set-Cookie'])
				self.response.out.write("<br/><li>Saved Cookie:<br/>")

				if bbstype == 'pw':
					sid_start = result.headers['Set-Cookie'].find('_ck_info=')
					sid_end = result.headers['Set-Cookie'][sid_start:].find(';')
					sid = result.headers['Set-Cookie'][sid_start-5:sid_start+sid_end]
					auth_start = result.headers['Set-Cookie'].find('_winduser=')
					auth_end = result.headers['Set-Cookie'][auth_start:].find(';')
					auth = result.headers['Set-Cookie'][auth_start-5:auth_start+auth_end]
					cookie = auth+'; '+sid
				else:
					sid_start = result.headers['Set-Cookie'].find('_sid=')
					sid_end = result.headers['Set-Cookie'].find(';')
					sid = result.headers['Set-Cookie'][sid_start-3:sid_end]
					auth_start = result.headers['Set-Cookie'].find('_auth=')
					auth_end = result.headers['Set-Cookie'][auth_start:].find(';')
					auth = result.headers['Set-Cookie'][auth_start-3:auth_start+auth_end]
					cookie = sid+'; '+auth

				b.cookie = cookie
				b.put()
				self.response.out.write(cookie)
		self.response.out.write(returnButton)

class AddLogin(webapp.RequestHandler):
	def get(self):
		bbs = self.request.get('bbs')
		type = self.request.get('type')
		url = self.request.get('url')
		login = self.request.get('lo')
		index = self.request.get('hp')
		reg = self.request.get('reg')
		user = self.request.get('id')
		pwd = self.request.get('pwd')
		if bbs=='' or url=='' or login=='' or index=='' or reg=='' or user=='' or pwd=='':
			self.response.out.write('Required parameter missing')
		else:
			forum = Forums(key_name=bbs,bbsId=bbs,bbsType=type,baseUrl=url,loginPhp=login,indexPhp=index,registerPhp=reg,userId=user,passWd=pwd)
			forum.put()
			self.response.out.write(bbs+' has been added')
		self.response.out.write(returnButton)

class DeleteLogin(webapp.RequestHandler):
	def get(self):
		bbs = self.request.get('bbs')
		if bbs == '':
			self.response.out.write("Required parameter missing")
		else:
			bbs_query = db.GqlQuery("SELECT * FROM Forums WHERE bbsId=:1",bbs)
			if bbs_query.count() == 0:
				self.response.out.write(bbs+' not found')
			else:
				for b in bbs_query:
					b.delete()
				self.response.out.write(bbs+' has been deleted')
		self.response.out.write(returnButton)

class ViewLogin(webapp.RequestHandler):
	def get(self):
		bbs = self.request.get('bbs')
		if bbs == '':
			bbs_query = db.GqlQuery("SELECT * FROM Forums")
		else:
			bbs_query = db.GqlQuery("SELECT * FROM Forums WHERE bbsId=:1",bbs)
		for b in bbs_query:
			text = '<li>Forums:['+b.bbsId
			if b.bbsType:
				text += ','+b.bbsType
			else:
				text += ',&lt;null&gt;'
			text += ','+b.baseUrl+','+b.loginPhp+','+b.indexPhp+','+b.registerPhp+','+b.userId+','+b.passWd
			if b.cookie:
				text += ','+b.cookie
			text += ']<br/>'
			self.response.out.write(text)
		self.response.out.write(returnButton)

class ProxyGet(webapp.RequestHandler):
	def get(self):
		targetUrl = self.request.get('url')
		if targetUrl == '':
			self.response.out.write('I\'m in position')
		else:
			targetUrl = 'http://' + targetUrl
			result = urlfetch.fetch(url=targetUrl,method=urlfetch.GET,allow_truncated=True,follow_redirects=False)
			self.response.out.write(result.content);

application = webapp.WSGIApplication([('/', MainPage),
                                      ('/login', Relogin),
                                      ('/testlogin', Login),
                                      ('/addlogin', AddLogin),
                                      ('/deletelogin', DeleteLogin),
                                      ('/viewlogin', ViewLogin),
                                      ('/get', ProxyGet),
                                     ],
                                     debug=True)

def main():
	run_wsgi_app(application)

if __name__ == '__main__':
	main()
