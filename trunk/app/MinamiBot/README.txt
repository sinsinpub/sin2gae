简介：
PHP修改的用于学习的推特Bot。使用Oauth方式。
修改的初衷是用于学习外语和与同好交流。

使用说明：
执行cron定时任务之前，需要修改相应目录下[bot名].php中的
$user（只限于webliko）
$consumer_key
$consumer_secret
$access_token
$access_token_secret
数值为bot账户的值。可以在 http://dev.twitter.com/apps 建立和获取。

目录说明：
minamibot	定时发送随机的带weblio.jp链接的单词推
suzuranbot	定时发送随机的纯单词推
webliko	经weblio.jp查询日语单词（cron每2分钟执行）

文件说明：
MinamiBotChecker.php	一个页面完成查看minami系bot工作状态（bot是否在发推、是否被冻结账户等）的好办法……
themter.php	查看指定用户的主题、图片和配色方案（谜之声：主人用来盗取主题的……）
main.css	感谢@rainux同学提供的主题文件（上述两者共用）

Twitter账户：
@shimada_minami	日语N1+单词
@himejimizuki	TOFEL单词
@nodoka_bot	日语N1,N2语法
@webliko	weblio.jp日语单词查询（已暂时停止）
@suzuran_bot	日语常用汉字1900+
@aono_bot	暂未预定内容

联系我：
minamimaster@gmail.com