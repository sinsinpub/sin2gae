注意事项：

在data.txt内每行写一句要发布到Twitter的内容，会随机发布其中一行内容
这个文件的编码必须为utf-8
文件最后需要EOF

app.yaml中加入的login: admin为验证浏览者是否为拥有者，如果不需要安全性可以去掉这行
cron.yaml中的schedule: every 15 minutes为指定发推时间，可更改为
every 1 day 每天
every 30 mins 每30分钟
every 3 hours 每3小时
等

