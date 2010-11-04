<?php
#@author:minamimaster
#@description:用于minami系机器人批量通知Twitter用户
include_once("twitter_bot.php");

#Bot用户名
$user = "aono_bot";
#Bot用户数字
$botID = "";
#follow基准用户数字
$userID = "";
#Consumer key
$consumer_key = "";
#Consumer secret
$consumer_secret = "";
#Access Token
$access_token = "";
#Access Token Secret
$access_token_secret = "";

#生成对象
$Bot = new Twitter_Bot($user,$consumer_key,$consumer_secret,$access_token,$access_token_secret);

#随机unfollow掉自己的关注对象
// $myFollowers = $Bot->Get_Friends($botID);

// $r = 0;
// foreach($myFollowers as $numIdt){
//     $randUsert = Rrt($myFollowers);
//     $r++;
//     if ($r <= 5){
//         $resultt = $Bot->Follow($randUsert, false);
//         switch($resultt){
//             case "ok": $ttx = $randUsert." success unfo.<br />"; break;
//             case "already": $ttx = $randUser." already unfo.<br />"; break;
//             default: $ttx = $randUser." try again unfo.<br />";
//     }
//     echo $ttx;
//     } else {break;}
// }

#获取一个目标follow基准对象的所有follower
$userFollowers = $Bot->Get_Followers($userID);

$i = 0;
foreach($userFollowers as $numId){
    $randUser = Rrt($userFollowers);
    $i++;
    if ($i <= 30){
        $result = $Bot->Follow($randUser, true);
        switch($result){
            case "ok": $tx = $randUser." success followed.<br />"; break;
            case "already": $tx = $randUser." already followed.<br />"; break;
            default: $tx = $randUser." try again follow.<br />";
    }
    echo $tx;
    } else {break;}
}


?>