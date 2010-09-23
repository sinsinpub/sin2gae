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

function get_webli_url($url)
{
    $url = 'http://www.weblio.jp/content/'.urlencode($url);
    $ch = curl_init();
    $timeout = 15;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $url = curl_exec($ch); 
    curl_close($ch);
    return trim($url);
}

function get_unu_url($url)
{
    $url = 'http://tinyurl.com/api-create.php?url='.urlencode($url);
    $ch = curl_init();
    $timeout = 10;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $url = curl_exec($ch); 
    curl_close($ch);
    return trim($url);
}

// OAuthオブジェクト生成
$to = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
$twilist = array(
// 总之活用各种编辑器、Office或者想办法填满这里吧，最后一个数组元素不写逗号也可以唷
// 这里[单词]的位置之后必须要留有一个半角空格，用于传递单词令牌以便在weblio.jp获取单词的链接。
// 如果没有空格，链接地址会错
"单词 随机推内容1",
"单词 随机推内容2",
"单词 随机推内容3",
);
$twiresu = Rrt($twilist);
$resu = NULL;
$danci = strtok($twiresu, ' ');
$getwe = get_webli_url($danci);
$getpos = strpos($getwe, 'ID=NF');
if ($getpos === false) {
    $resu = ' '.get_unu_url('http://www.weblio.jp/content/'.$danci);
}

// TwitterへPOSTする。パラメーターは配列に格納する
// in_reply_to_status_idを指定するのならば array("status"=>"@hogehoge reply","in_reply_to_status_id"=>"0000000000"); とする。
$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>$twiresu.$resu));
// TwitterへPOSTするときのパラメーターなど詳しい情報はTwitterのAPI仕様書を参照してください

header("Content-Type: application/xml");
echo $req;
?>