# -*- coding: utf-8 -*-
# Copyright under  the latest Apache License 2.0

import wsgiref.handlers, urlparse, base64, logging
from cgi import parse_qsl
from google.appengine.ext import webapp
from google.appengine.api import urlfetch, urlfetch_errors
from wsgiref.util import is_hop_by_hop

import oauth_dev

gtap_version = '0.4custom'
gtap_path = '/gto'

CONSUMER_KEY = 'oK636XG8RSMsETSj9X37wQ'
CONSUMER_SECRET = '2yerLtgZu05M7HLxygHI43X8b6ztziF4qWuNxZEOmc'

gtap_message = """
    <html>
        <head>
        <title>GAE Twitter API Proxy</title>
        <link href='https://appengine.google.com/favicon.ico' rel='shortcut icon' type='image/x-icon' />
        <style>body { padding: 20px 40px; font-family: Verdana, Helvetica, Sans-Serif; font-size: medium; }</style>
        </head>
        <body><h2>GTAP v#gtap_version# is running!</h2></p>
        <p><a href='oauth/session'><img src='/static/sign-in-with-twitter.png' border='0'></a> <== Need to scale the wall first!!</p>
        <p>This is a simple solution on Google Appengine which can proxy the HTTP request to twitter's official REST API url.</p>
        <p><font color='red'><b>Don't forget the \"/\" at the end of your api proxy address!!!.</b></font></p>
    </body></html>
    """

def success_output(handler, content, content_type='text/html'):
    handler.response.status = '200 OK'
    handler.response.headers.add_header('GTAP-Version', gtap_version)
    handler.response.headers.add_header('Content-Type', content_type)
    handler.response.out.write(content)

def error_output(handler, content, content_type='text/html', status=503):
    handler.response.set_status(503)
    handler.response.headers.add_header('GTAP-Version', gtap_version)
    handler.response.headers.add_header('Content-Type', content_type)
    handler.response.out.write("Gtap Server Error:<br />")
    return handler.response.out.write(content)



class MainPage(webapp.RequestHandler):

    def conver_url(self, orig_url):
        (scm, netloc, path, params, query, _) = urlparse.urlparse(orig_url)
        
        path_parts = path.split('/')
        
        if path_parts[2] == 'api' or path_parts[2] == 'search':
            sub_head = path_parts[2]
            path_parts = path_parts[3:]
            path_parts.insert(0,'')
            new_path = '/'.join(path_parts).replace('//','/')
            new_netloc = sub_head + '.twitter.com'
        else:
            new_path = '/'.join(path_parts[2:])
            new_netloc = 'twitter.com'

        new_url = urlparse.urlunparse((scm, new_netloc, new_path.replace('//','/'), params, query, ''))
        return new_url, new_path

    def parse_auth_header(self, headers):
        username = None
        password = None
        
        if 'Authorization' in headers :
            auth_header = headers['Authorization']
            auth_parts = auth_header.split(' ')
            user_pass_parts = base64.b64decode(auth_parts[1]).split(':')
            username = user_pass_parts[0]
            password = user_pass_parts[1]
    
        return username, password

    def do_proxy(self, method):
        orig_url = self.request.url
        orig_body = self.request.body
        #logging.debug(orig_url)

        new_url,new_path = self.conver_url(orig_url)
        #logging.debug(new_url)
        #logging.debug(new_path)

        if new_path == '/' or new_path == '':
            global gtap_message
            gtap_message = gtap_message.replace('#gtap_version#', gtap_version)
            return success_output(self, gtap_message )
        
        username, password = self.parse_auth_header(self.request.headers)
        user_access_token = None
        
        callback_url = ("%s" + gtap_path + "/oauth/verify") % self.request.host_url
        client = oauth_dev.TwitterClient(CONSUMER_KEY, CONSUMER_SECRET, callback_url)

        if username is None :
            protected=False
        else:
            protected=True
            user_access_token  = client.get_access_token_from_db(username)
            if user_access_token is None :
                return error_output(self, 'Can not find this user from db')
        
        additional_params = dict([(k,v) for k,v in parse_qsl(orig_body)])

        userpassword = client.get_secret_access_password_from_db(username)
        if userpassword is None or password != userpassword:
            user_access_secret = password
        else:
            user_access_secret = client.get_user_access_secret_from_db(username)
        use_method = urlfetch.GET if method=='GET' else urlfetch.POST

        try :
            data = client.make_request(url=new_url, token=user_access_token, secret=user_access_secret, 
                                   method=use_method, protected=protected, 
                                   additional_params = additional_params)
        except Exception,error_message:
            logging.debug( error_message )
            error_output(self, content=error_message)
        else :
            self.response.headers.add_header('GTAP-Version', gtap_version)
            for res_name, res_value in data.headers.items():
                if is_hop_by_hop(res_name) is False and res_name!='status':
                    res_name = res_name \
                    .replace('x-transaction', 'X-Transaction') \
                    .replace('x-ratelimit-limit', 'X-RateLimit-Limit') \
                    .replace('x-ratelimit-remaining', 'X-RateLimit-Remaining') \
                    .replace('x-runtime', 'X-Runtime') \
                    .replace('x-ratelimit-class', 'X-RateLimit-Class') \
                    .replace('x-revision', 'X-Revision') \
                    .replace('x-ratelimit-reset', 'X-RateLimit-Reset')
                    self.response.headers.add_header(res_name, res_value)
            self.response.out.write(data.content)

    def post(self):
        self.do_proxy('POST')
    
    def get(self):
        self.do_proxy('GET')


class OauthPage(webapp.RequestHandler):
    def get(self, mode=""):
        callback_url = ("%s" + gtap_path + "/oauth/verify") % self.request.host_url
        logging.debug(callback_url)
        client = oauth_dev.TwitterClient(CONSUMER_KEY, CONSUMER_SECRET, callback_url)
        
        if mode=='session':
            # step C Consumer Direct User to Service Provider
            try:
                url = client.get_authorization_url()
                logging.debug(url)
                self.redirect(url)
            except Exception,error_message:
                self.response.out.write( error_message )

        if mode=='verify':
            # step D Service Provider Directs User to Consumer
            auth_token = self.request.get("oauth_token")
            auth_verifier = self.request.get("oauth_verifier")
            logging.debug("oauth_token:" + auth_token)
            logging.debug("oauth_verifier:" + auth_verifier)
            # step E Consumer Request Access Token 
            # step F Service Provider Grants Access Token
            try:
                access_token, access_secret, screen_name = client.get_access_token(auth_token, auth_verifier)

                # Save the auth token and secret in our database.
                client.save_user_info_into_db(username=screen_name,token=access_token, secret=access_secret)

                self.response.out.write( 'Your access secret key is : %s' % access_secret )
            except Exception,error_message:
                self.response.out.write( error_message )


def main():
    application = webapp.WSGIApplication( [
        (r'/gto/oauth/(.*)',   OauthPage),
        (r'/gto/.*',         MainPage)
        ], debug=True)
    wsgiref.handlers.CGIHandler().run(application)
    
if __name__ == "__main__":
  main()
