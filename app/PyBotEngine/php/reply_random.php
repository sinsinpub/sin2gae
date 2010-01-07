<?php
//====================================================================
//TwitterBotPHP Version 1.42
//reply_random.php
//
//@でreplyをもらったときに、用意した文章からランダムに一行を取り出して返信するスクリプトです。
//これを使ったbotの例：@tentori_（点取り占いbot）
//by @pha

//====================================================================
//設定
//====================================================================

$username = "username";   //Twitterのユーザー名を書き込んでください
$password = "password";    //Twitterのパスワードを書き込んでください   
$file = "reply.txt";   //発言を書き込んだファイルの名前（変更可能）
$cron = 3; //cronなどでこのreply.phpをcronなどで実行する間隔を入力してください。単位は分です。

//====================================================================
//高度な設定
//====================================================================

$useReplyPattern = TRUE;   //特定の単語に対して決まった返事をする機能を使うときはTRUE, 使わないときはFALSEにしてください
$replyPatternFile = "reply_pattern.php"; //特定の単語に対して決まった返事をする時に使うファイルの名前（変更可能）
$resOnlyBegginingReply = FALSE; //TRUEだと文頭に自分あての@があったときのみ反応します FALSEだとそうでなくても反応します
$resOnlyNotRT = TRUE;  //TRUEだと文中にRTの文字があると反応しません FALSEだとRTでも反応します
$replyLoopLimit = 8; //何回連続でreplyを返されるとループを中断するかを設定します。余り大きすぎる数字にするとうまく動かないかもしれません


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
<title>reply_random.php</title>
</head>
<body>

<?php
//各種設定
chdir(dirname(__FILE__));
require_once("Services/Twitter.php");
require_once('Services/Twitter/Jsphon/Error.php');
require_once("Services/Twitter/Jsphon/Decoder.php");

$st =& new Services_Twitter($username, $password);
$replydata = $st->getReplies();
$json =& new Jsphon_Decoder();
$replydata = $json->decode($replydata);

$ver = preg_replace("@(\d)\.(\d)\.(\d).*@","$1$2$3",phpversion());
if($ver < 500){
}else{
    date_default_timezone_set("Asia/Tokyo");
}

//取得する時間の幅
$now = strtotime("now");
$limittime = $now - $cron * 60;
$loopCheckTime = $now - $cron * 60 * $replyLoopLimit;

//時間内の返信だけ取り出す
$replies = array();
$repliesLoopCheck = array();
$replyUserList = array();


foreach($replydata as $rdata){
    $time = strtotime($rdata["created_at"]);
    if($time === -1){
        $time = strtotime2($rdata["created_at"]);        
    }
    
    if($limittime <= $time){
        
        //一人に対しては一回しか返事しない
        //返事するリストに既に名前があったら返事しない
        if(!in_array($rdata["user"]["screen_name"],$replyUserList)){
            $re = array();
            $re["screen_name"] = $rdata["user"]["screen_name"];
            $re["name"] = $rdata["user"]["name"];
            $re["text"] = $rdata["text"];
            $re["id"] = $rdata["id"];
                        
            if($resOnlyBegginingReply){
                if(strpos($re["text"],"@".$username) === 0){ //発言の先頭に@があった場合のみ返答
                    $replies[] = $re;
                }                
            }else{
                if($resOnlyNotRT){
                    if(strpos($re["text"],"RT") === FALSE){ //RTの文字を含まないときのみ返答
                        $replies[] = $re;
                    }                                
                }else{
                    $replies[] = $re;                
                }
            }                                               
        }
        
        //返事する名前のリストを作る
        $replyUserList[] = $rdata["user"]["screen_name"];                
        $replyUserList = array_unique($replyUserList);
                        
    }
    //ループ制限用。指定回数のリプライを受け取っていたら返事しない
    if($loopCheckTime <= $time){
        $re = array();
        $re["screen_name"] = $rdata["user"]["screen_name"];            
        if($resOnlyBegginingReply){
            if(strpos($rdata["text"],"@".$username) === 0){
                $repliesLoopCheck[] = $re;
            }                
        }else{
            if($resOnlyNotRT){
                if(strpos($rdata["text"],"RT") === FALSE){
                    $repliesLoopCheck[] = $re;
                }                                
            }else{
                $repliesLoopCheck[] = $re;                
            }
        }            
    }else{
        break;
    }
}

//リプライがあった場合のみここからの処理を行う
if(count($replies) != 0){    
        
    //リプライがループしている場合返答しない
    $replies2 = array();
    foreach($replies as $reply){
        $replyTimes = 0;
        //$reply["id"]が$replydataの$rdata["id"]と一致している回数がループ以下なら
        foreach($repliesLoopCheck as $rdata){            
            if($reply["screen_name"] === $rdata["screen_name"]){
                $replyTimes++;
            }
        }
        if($replyTimes > $replyLoopLimit){
            echo "<p>ここしばらくの間に".$reply["screen_name"]."からのreplyが".$replyTimes."回あり、設定されたリミットの".$replyLoopLimit."回を超えているため今回はリプライしません。</p>";
        }else{
            $replies2[] = $reply;
        }       
    }      
    $replies2 = array_reverse($replies2);
    
    //発言リストを読み込む
    $tweets = file_get_contents($file);
    $tweets = trim($tweets);
    $tweets = preg_replace("@\n+@","\n",$tweets);
    $tw = explode("\n", $tweets);
       
    //発言をリプライの数だけランダムに選ぶ
    $rand_keys = array();
    for($i=0;$i<count($replies2);$i++){
        $rand_keys[] = array_rand($tw);
    }
    
    //リプライの文章をつくる
    for($i=0;$i < count($replies2);$i++){    
        $message = "";        
        //リプライパターンと照合する
        if($useReplyPattern === TRUE){
            require_once($replyPatternFile);
            foreach($reply_pattern as $pattern => $res){
                $pattern = preg_replace("@\@@","\@",$pattern);
                if(preg_match("@".$pattern."@",$replies2[$i]["text"]) === 1){                                        
                    $message = $res[array_rand($res)];
                    break;
                }
            }            
        }             
        if(empty($message)){
            $message = $tw[$rand_keys[$i]];                            
        }        
        if(empty($message)){
            echo "投稿するメッセージがないようです。";
        }elseif($message == "[[END]]"){
            echo "会話終了の合図が来たので返答をしません。";
        }else{
            
            if(preg_match("@{.+?}@",$message) == 1){    
        
                //時間などを変換する                
                require_once("Services/convert_text.php");
                $message = convert_text($message);
                    
                //idや名前を変換する
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
                            
                //idや名前を変換する
                $message = preg_replace("@{name}@u",$replies2[$i]["name"],$message);
                $message = preg_replace("@{id}@u",$replies2[$i]["screen_name"],$message);
                
                //相手の発言を取得する
                $tweet = preg_replace("@\.?\@[a-zA-Z0-9-_]+\s@u","",$replies2[$i]["text"]);            
                $message = preg_replace("@{tweet}@u",$tweet,$message);                        
            }            
                        
            //ここで投稿するメッセージが完成
            $message = "@".$replies2[$i]["screen_name"]." ".$message;
            
            //投稿する
            $in_reply_to_status_id = $replies2[$i]["id"];                    
            $result = $st->setUpdate(array('status'=>$message,'in_reply_to_status_id'=>$in_reply_to_status_id));        
            
            if($result){
                echo "Twitterへの投稿に成功しました。<br />";
                echo "@<a href='http://twitter.com/{$username}' target='_blank'>{$username}</a>に投稿したメッセージ：{$message}<br />";
            }else{
                echo "Twitterへの投稿に失敗しました。パスワードやユーザー名をもう一度チェックしてみてください。<br />";        
                echo "ユーザー名：@<a href='http://twitter.com/{$username}' target='_blank'>{$username}</a><br />";
                echo "投稿しようとしたメッセージ：{$message}<br />";
            }            
        }
    }

}else{
    echo $cron."分以内に受け取った@はないようです。<br />";    
}

function strtotime2($time){
    $time2 = preg_replace("@\+([0-9]{4})\s@","",$time);
    $time2 = strtotime($time2) + 32400;
    return($time2);    
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