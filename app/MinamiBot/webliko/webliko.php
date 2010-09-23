<?php
// 先ほどのtwitter_bot.phpを読み込む。パスはあなたが置いた適切な場所に変更してください
include_once("twitter_bot.php");

function get_unu_url($url)
{
    $url = 'http://is.gd/api.php?longurl='.urlencode($url);
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $url = curl_exec($ch); 
    curl_close($ch);
    return trim($url);
}

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

function utf8Substr($str, $from, $len){
    return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
                       '$1',$str);
}

function strlen_utf8($str)

{

$i = 0;

$count = 0;

$len = strlen ($str);

while ($i < $len)

{

$chr = ord ($str[$i]);

$count++;

$i++;

if($i >= $len)

break;

 

if($chr & 0x80)

{

$chr <<= 1;

while ($chr & 0x80)

{

$i++;

$chr <<= 1;

}

}

}

return $count;

}

// Botのユーザー名
$user = "webliko";
// Consumer keyの値
$consumer_key = "";
// Consumer secretの値
$consumer_secret = "";
// Access Tokenの値
$access_token = "";
// Access Token Secretの値
$access_token_secret = "";

// オブジェクト生成
$Bot = new Twitter_Bot($user,$consumer_key,$consumer_secret,$access_token,$access_token_secret);


$since_id_mentions = $Bot->Get_data("Mentions"); // 最後に取得したリプライのID
$since_id_rped = $Bot->Get_data("rped"); 
$mentions = $Bot->Get_TL("mentions",$since_id_mentions,"1"); // Bot宛てのリプライ取得
foreach($mentions as $reply){
      $tx = null;
      $sid = $reply->id; // 呟きのID
      $uid = $reply->user->id; // ユーザーナンバー
      $screen_name = $reply->user->screen_name; // ユーザーID
      $name = $reply->user->name; // ユーザー名


      // 呟き内容。余分なスペースを消して、半角カナを全角カナに、全角英数を半角英数に変換。
      $text = mb_convert_kana(trim($reply->text),"rnKHV","utf-8");
      if($screen_name == $user || preg_match("/(R|Q)T( |:)/",$text) || strlen_utf8($text) > 19 || $since_id_rped == $sid){
            continue;
      }
      if(stristr($text,"@".$user)){
      $pieces = str_replace( "　", "",trim(str_replace("@".$user,"",$text)));
      $orgContent = get_webli_url($pieces);
      $pos = strpos($orgContent, 'ID=nrCntTH');
      if ($pos === false) {
      	  $short_url = get_unu_url('http://www.weblio.jp/content/'.$pieces);
      	  if (preg_match('/meta name="description.*。/',$orgContent,$matches)){
      	  	  if (strpos($orgContent,'この記事の内容に関する文献や情報源が必要です')){
      	  	  	  $tx = $pieces." ".$short_url;
      	  	  }else{
      	  	  $towa = str_replace('meta name="description" content="',"",$matches[0]);
      	  	  if (strlen_utf8($towa) > 118) {
      	  	  	  $towa = utf8Substr($towa,0,118);
      	  	  }
      	  	  $tx = $towa." ".$short_url;
      	  	  }
      	  }
          else {
          $tx = $pieces." ".$short_url;
          }
      }else{
           $Bot->Save_data("rped",$sid);
      }
      }

      // $txが空でないのならPOST
      if($tx){$Bot->Post("@".$screen_name." ".$tx,$sid);
          $Bot->Save_data("rped",$sid);
          }
}
// 次の呟き取得のために最後に取得した呟きを保存する
$Bot->Save_data("Mentions",$sid);
$Bot->End($sid);




?>