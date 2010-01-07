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

def doLogin():
  return twitter.Api(username='yourbotname', password='yourbotpassword')
