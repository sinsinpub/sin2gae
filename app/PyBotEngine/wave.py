"""
Google wave implementation of the agreeabl bot. 
"""

import random

from waveapi import events
from waveapi import robot

msg_list = [
        "%s that's what my mum always said and it's hard to argue with her.",
        "%s I feel your pain...",
        "%s you go girl!",
        "%s you say the smartest stuff sometimes.",
        "%s yeah, me too.",
        "%s I get like that sometimes too.",
        "%s good thinking!",
        "%s that deserves a hug.",
        "%s totally!",
        "%s my feelings exactly!",
        "%s that is very true",
        "%s so true, so true...",
        "%s you are so right...",
        "%s couldn't agree more.",
        "%s if only more people were as thoughtful as you.",
        "%s yeah for sure",
        "%s you know a tibetan monk once said the same thing to me and it \
        always stuck in my mind.",
        "%s those there are wise words. Wise words indeed.",
        "%s if more people thought like you we wouldn't need laws. Or taxes. \
        Or Conroy's clean feed.",
        "%s yup like I said before - you just can't live without fresh fruit \
        and clean water.",
        "%s yeah - it really is the way things are going these days.",
        "%s that sure sounds like fun"
        ]

def OnBlipSubmitted(properties, context):
    """Invoked when a blip is added."""
    blip = context.GetBlipById(properties['blipId'])
    creator_id = blip.GetCreator()
    selected_msg = random.choice(msg_list)
    msg = selected_msg % creator_id
    blip.CreateChild().GetDocument().SetText(msg)

if __name__ == '__main__':
    my_robot = robot.Robot('agreeabl',
                          image_url='http://s3.amazonaws.com/twitter_production\
                          /profile_images/239457823/agreeabl-avatar_normal.jpg',
                          version='1',
                          profile_url='http://agreeabl.appspot.com/')
    my_robot.RegisterHandler(events.BLIP_SUBMITTED, OnBlipSubmitted)
    my_robot.Run()
