
MinamiBot在Sina App Engine的版本

注意事项：

在data.txt内每行写一句要发布到Twitter的内容，会随机发布其中一行内容
这个文件的编码必须为utf-8
文件最后需要EOF


1.将index.php第7行的user login name，user password改为你的

2.设置SAE config.yaml的cron任务（可自定义）：
      schedule: every 15 mins
