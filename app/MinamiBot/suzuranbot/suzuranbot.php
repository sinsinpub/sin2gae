<?php
// twitteroauth.phpを読み込む。パスはあなたが置いた適切な場所に変更してください
require_once("twitteroauth.php");
require_once("twitter_bot.php");
// Consumer keyの値
$consumer_key = "";
// Consumer secretの値
$consumer_secret = "";
// Access Tokenの値
$access_token = "";
// Access Token Secretの値
$access_token_secret = "";

// OAuthオブジェクト生成
$to = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);

// TwitterへPOSTする。パラメーターは配列に格納する
// in_reply_to_status_idを指定するのならば array("status"=>"@hogehoge reply","in_reply_to_status_id"=>"0000000000"); とする。
$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>Rrt(array(
// 总之活用各种编辑器、Office或者想办法填满这里吧，最后一个数组元素不写逗号也可以唷
"随机推内容1",
"随机推内容2",
"随机推内容3",
))));
// TwitterへPOSTするときのパラメーターなど詳しい情報はTwitterのAPI仕様書を参照してください

header("Content-Type: application/xml");
echo $req;
?>