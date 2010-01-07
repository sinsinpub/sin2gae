<?php
//====================================================================
//TwitterBotPHP Version 1.42
//post_random.php
//
//用意した文章からランダムに一行を取り出してtwitterに投稿するスクリプトです。
//これを使ったbotの例：@takuboku（石川啄木の短歌をランダムに投稿するbot）
//名言botのようなものは大体このスクリプトを使えば作れます
//by @pha

//====================================================================
//設定
//====================================================================

$username = "username";   //Twitterのユーザー名を書き込んでください
$password = "password";    //Twitterのパスワードを書き込んでください   
$file = "tachibara.txt";       //発言を書き込んだファイルの名前（変更可能）
//====================================================================
//設定終わり
//ここから下は編集しないでください
//====================================================================
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="content-language" content="ja" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>post_random.php</title>
</head>
<body>

<?php
//各種設定
chdir(dirname(__FILE__));
require_once("Services/Twitter.php");
require_once('Services/Twitter/Jsphon/Error.php');
require_once("Services/Twitter/Jsphon/Decoder.php");
$st =& new Services_Twitter($username, $password);

$ver = preg_replace("@(\d)\.(\d)\.(\d).*@","$1$2$3",phpversion());
if($ver < 500){
}else{
    date_default_timezone_set("Asia/Tokyo");
}

//発言リストを読み込む
$tweets = file_get_contents($file);
$tweets = trim($tweets);
$tweets = preg_replace("@\n+@","\n",$tweets);
$tw = explode("\n", $tweets);

//発言をランダムに一つ選ぶ
$message = $tw[array_rand($tw)];

if(empty($message)){
    echo "投稿するメッセージがないようです。";
}else{            
    
    if(preg_match("@{.+?}@",$message) == 1){    
    
        //時間などを変換する
        require_once("Services/convert_text.php");
        $message = convert_text($message);
        
        //id、名前を変換する
        //タイムラインからランダムに最近発言した人のデータを取る
        if(preg_match("@{timeline_id}@",$message) === 1){
            $randomTweet = $st->getRandomTweet(20);
            $message = preg_replace("@{timeline_id}@u",$randomTweet["user"]["screen_name"],$message);        
        }
        if(preg_match("@{timeline_name}@",$message) === 1){
            $randomTweet = $st->getRandomTweet(20);
            $message = preg_replace("@{timeline_name}@u",$randomTweet["user"]["name"],$message);        
        }
            
        //ランダムな一人のfollowerデータを取る    
        if(preg_match("@{id}@",$message) === 1){
            $randomFollowersData = $st->getRandomfollowersData();
            $message = preg_replace("@{id}@u",$randomFollowersData["screen_name"],$message);        
        }
        if(preg_match("@{name}@",$message) === 1){
            $randomFollowersData = $st->getRandomfollowersData();
            $message = preg_replace("@{name}@u",$randomFollowersData["name"],$message);        
        }
        if(preg_match("@{follower_id}@",$message) === 1){
            $randomFollowersData = $st->getRandomfollowersData();
            $message = preg_replace("@{follower_id}@u",$randomFollowersData["screen_name"],$message);        
        }
        if(preg_match("@{follower_name}@",$message) === 1){
            $randomFollowersData = $st->getRandomfollowersData();
            $message = preg_replace("@{follower_name}@u",$randomFollowersData["name"],$message);        
        }
        
        //ランダムな一人のfollowingデータを取る    
        if(preg_match("@{following_id}@",$message) === 1){
            $randomFollowingsData = $st->getRandomFollowingsData();
            $message = preg_replace("@{following_id}@u",$randomFollowingsData["screen_name"],$message);        
        }
        if(preg_match("@{following_name}@",$message) === 1){
            $randomFollowingsData = $st->getRandomFollowingsData();
            $message = preg_replace("@{following_name}@u",$randomFollowingsData["name"],$message);        
        }
    }
       
    //twitterに投稿する
    $result = $st->setUpdate($message);
    
    //結果の表示
    if($result){
        echo "Twitterへの投稿に成功しました。<br />";
        echo "@<a href='http://twitter.com/{$username}' target='_blank'>{$username}</a>に投稿したメッセージ：{$message}";
    }else{
        echo "Twitterへの投稿に失敗しました。パスワードやユーザー名をもう一度チェックしてみてください。<br />";        
        echo "ユーザー名：@<a href='http://twitter.com/{$username}' target='_blank'>{$username}</a><br />";
        echo "投稿しようとしたメッセージ：{$message}";
    }
}

?>

</body>
</html>

<?php
/*
配布URL：http://pha22.net/text/twitterbot.html
作者：pha (pha.japan@gmail.com)

【利用条件について】
・このスクリプトはPHPライセンス3.01に基づいて公開されています。
・このスクリプトの使用は商用、非商用に関わらず一切自由です。著作権表示を消さない限り、スクリプトの改造・再配布も自由にしていただいて構いません。
*/

/**
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author    pha <pha.japan@gmail.com>
 * @copyright 2009 pha <pha.japan@gmail.com>
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      http://www.transrain.net/product/services_twitter/
 * This product includes PHP, freely available from http://www.php.net/
 */
?>