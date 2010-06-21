import wsgiref.handlers, urlparse, base64
from wsgiref.headers import Headers
from google.appengine.ext import webapp
from google.appengine.api import urlfetch
from google.appengine.api import urlfetch_errors
from wsgiref.util import is_hop_by_hop

import logging
from oauth import OAuthClient
from urlparse_custom import *
from urlparse import *

gtapVersion = '0.2.2_oauth_mod_by_SAPikachu'


class MainPage(webapp.RequestHandler):
    def myOutput(self, contentType, content):
        self.response.status = '200 OK'
        self.response.headers.add_header('GTAP-Version', gtapVersion)
        self.response.headers.add_header('Content-Type', contentType)
        self.response.out.write(content)

    def doProxy(self, method):
        origUrl = self.request.url
        origBody = self.request.body
        (scm, netloc, path, params, query, _) = urlparse(origUrl)
        if path == '/':
            self.myOutput('text/html', 'here is the proxy of \"twitter.com\" by GTAP %s !' % (gtapVersion))
        else:
            #logging.debug(self.request.headers)
            auth_header = None

            if 'Authorization' in self.request.headers :
                auth_header = self.request.headers['Authorization']
            elif 'X-Authorization' in self.request.headers :
                auth_header = self.request.headers['X-Authorization']

            headers = {}
            use_oauth = False
            if auth_header != None :
                #auth_header = self.request.headers['Authorization']
                auth_parts = auth_header.split(' ')
                if auth_parts[0].lower() == 'basic':
                    user_pass_parts = base64.b64decode(auth_parts[1]).split(':')
                    oauth_name = user_pass_parts[0]
                    oauth_key = user_pass_parts[1]
                    use_oauth = True

            path_parts = path.split('/')
            path_parts = path_parts[2:]
            path_parts.insert(0,'')
            #logging.debug(path_parts)
            path = '/'.join(path_parts).replace('//','/')
            if path_parts[1] == 'search':
                netloc = 'search.twitter.com'
                newpath = path[7:]
            elif path_parts[1] == 'api' and path_parts[2] == '1':
                netloc = 'api.twitter.com'
                newpath = path[4:]
            elif path_parts[1] == 'api':
                netloc = 'twitter.com'
                newpath = path[4:]
            elif path_parts[1] == '1':
                netloc = 'api.twitter.com'
                newpath = path
            else:
                netloc = 'twitter.com'
                newpath = path

            if newpath == '/' or newpath == '':
                self.myOutput('text/html', 'here is the proxy of \"'+ netloc + '\" by GTAP %s !' % (gtapVersion))
            else:
                if use_oauth:
                    newUrl = urlunparse(("https", netloc, newpath, params, '', ''))

                    client = OAuthClient('twitter', self)
                    client.key_name = oauth_key
                    client.specifier = oauth_name

                    #logging.info(('method',method))

                    if method.upper() == 'POST':
                        #logging.info(dict([(k,v) for k,v in parse_qsl(origBody)]))
                        data = client.post_raw(newUrl,method,**dict([(k,v) for k,v in parse_qsl(origBody)]))
                    else:
                        data = client.get_raw(newUrl,method,**dict([(k,v) for k,v in parse_qsl(query)]))
                else:
                    newUrl = urlunparse(("https", netloc, newpath, params, query, ''))
                    data = urlfetch.fetch(newUrl, payload=origBody, method=method, headers=headers)

                skipped_headers = ['status', 'via']

                for k in data.headers:
                    if is_hop_by_hop(k): 
                        continue
                    if k.lower() in skipped_headers:
                        continue
                    k = k \
                    .replace('x-transaction', 'X-Transaction') \
                    .replace('x-ratelimit-limit', 'X-RateLimit-Limit') \
                    .replace('x-ratelimit-remaining', 'X-RateLimit-Remaining') \
                    .replace('x-runtime', 'X-Runtime') \
                    .replace('x-ratelimit-class', 'X-RateLimit-Class') \
                    .replace('x-revision', 'X-Revision') \
                    .replace('x-ratelimit-reset', 'X-RateLimit-Reset')
                    del self.response.headers[k]
                    self.response.headers[k] = data.headers[k];

                self.response.set_status(data.status_code)

                #logging.debug(headers)
                #logging.debug(data.status_code)
                #logging.debug(data.headers)
                #logging.debug(self.response.headers)
                #logging.debug(data.content)
                self.response.out.write(data.content)


    def post(self):
        self.doProxy('post')
    
    def get(self):
        self.doProxy('get')

def main():
    application = webapp.WSGIApplication( [(r'/.*', MainPage)], debug=True)
    wsgiref.handlers.CGIHandler().run(application)

if __name__ == "__main__":
  main()
