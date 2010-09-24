Minami系列BOT

简介：
由PHP修改的用于学习的推特BOT。使用Oauth方式发推。
灵感来源于雷鸣的金克拉BOT。修改PHP的初衷是用于学习外语和与同好交流。

使用说明：
执行cron定时任务之前，需要修改相应目录下[bot名].php中的
$user（只限于webliko）
$consumer_key
$consumer_secret
$access_token
$access_token_secret
的相应的值为bot账户的值。可以在 http://dev.twitter.com/apps 建立和获取。
注意，申请的app请设置为客户端应用。

目录说明：
minamibot/	定时发送随机的带weblio.jp链接的单词推
suzuranbot/	定时发送随机的纯单词推
webliko/	经weblio.jp查询日语单词，cron任务请设为每2分钟执行

文件说明：
MinamiBotChecker.php	查看minami系BOT工作状态的页面
themter.php	查看指定用户的主题、图片和配色方案
main.css	感谢@rainux同学提供的主题文件（共用）

Twitter账户：
@shimada_minami	日语N1+单词
@himejimizuki	TOFEL单词
@nodoka_bot	日语N1,N2语法
@webliko	weblio.jp日语单词查询
@suzuran_bot	日语常用汉字1900+

参考资料：
[1]http://www.sdn-project.net/labo/oauth.html
[2]http://www.sdn-project.net/labo/twitter_bot.html

联系我：
minamimaster@gmail.com