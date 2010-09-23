<?php
$mtime1 = explode(" ", microtime());
$startTime = $mtime1[0] + $mtime1[1];
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type' />
    <title>Dreamslope</title>
    <link href='/main.css' media='screen' rel='stylesheet' type='text/css' />
  </head>
  <body>
    <div id='container'>

      <div id='header'>
        <div>
          <h1>Dreamslope</h1>
        </div>
      </div>
      <div id='content'>
        <div id='nav'>
          <p>

            <a href='/'>Home</a>
          </p>
        </div>
        <h3>这是什么？</h3>
        <p>
         一个用来查看各种BOT的工作状态的页面。作者为MinamiMaster。
        </p>

        <h3>联系作者？</h3>
        <p>
         请发消息给 @Shimada_Minami。
        </p>


<h3>单词系</h3>
<p>
<?php
date_default_timezone_set('Asia/Chongqing');
function rtoctime($rtime) {
	$ctime = date('P区 Y年n月j日l G点i分s秒',strtotime($rtime));
    return $ctime;
}

$json = file_get_contents("http://twitter.com/status/user_timeline/shimada_minami.json?count=1", true);
$decode = json_decode($json, true);

echo "<a href=\"https://twitter.com/shimada_minami\" target=\"_blank\"><img src=\"".$decode[0][user][profile_image_url]."\" /></a><br>"; //getting the profile image
echo "Name: ".$decode[0][user][name]."<br />"; //getting the username
echo "Screen name: ".$decode[0][user][screen_name]."<br />";
echo "Web: ".$decode[0][user][url]."<br />"; //getting the web site address
echo "Location: ".$decode[0][user][location]."<br />"; //user location
echo "Updates: ".$decode[0][user][statuses_count]."<br />"; //number of updates
echo "Follower: ".$decode[0][user][followers_count]."<br />"; //number of followers
echo "Following: ".$decode[0][user][friends_count]."<br />"; // following
echo "Listed count: ".$decode[0][user][listed_count]."<br />"; // Listed count
echo "Description: ".$decode[0][user][description]."<br />"; //user description
echo "Latest tweet: ".$decode[0][text]."<br />"; //last tweet
echo "Latest tweet created at: ".rtoctime($decode[0][created_at])."<br />"; //when tweet
?>
</p>


<p>
<?php
$json = file_get_contents("http://twitter.com/status/user_timeline/himejimizuki.json?count=1", true);
$decode = json_decode($json, true);

echo "<a href=\"https://twitter.com/himejimizuki\" target=\"_blank\"><img src=\"".$decode[0][user][profile_image_url]."\" /></a><br>"; //getting the profile image
echo "Name: ".$decode[0][user][name]."<br />"; //getting the username
echo "Screen name: ".$decode[0][user][screen_name]."<br />";
echo "Web: ".$decode[0][user][url]."<br />"; //getting the web site address
echo "Location: ".$decode[0][user][location]."<br />"; //user location
echo "Updates: ".$decode[0][user][statuses_count]."<br />"; //number of updates
echo "Follower: ".$decode[0][user][followers_count]."<br />"; //number of followers
echo "Following: ".$decode[0][user][friends_count]."<br />"; // following
echo "Listed count: ".$decode[0][user][listed_count]."<br />"; // Listed count
echo "Description: ".$decode[0][user][description]."<br />"; //user description
echo "Latest tweet: ".$decode[0][text]."<br />"; //last tweet
echo "Latest tweet created at: ".rtoctime($decode[0][created_at])."<br />"; //when tweet
?>
</p>

<p>
<?php
$json = file_get_contents("http://twitter.com/status/user_timeline/suzuran_bot.json?count=1", true);
$decode = json_decode($json, true);

echo "<a href=\"https://twitter.com/suzuran_bot\" target=\"_blank\"><img src=\"".$decode[0][user][profile_image_url]."\" /></a><br>"; //getting the profile image
echo "Name: ".$decode[0][user][name]."<br />"; //getting the username
echo "Screen name: ".$decode[0][user][screen_name]."<br />";
echo "Web: ".$decode[0][user][url]."<br />"; //getting the web site address
echo "Location: ".$decode[0][user][location]."<br />"; //user location
echo "Updates: ".$decode[0][user][statuses_count]."<br />"; //number of updates
echo "Follower: ".$decode[0][user][followers_count]."<br />"; //number of followers
echo "Following: ".$decode[0][user][friends_count]."<br />"; // following
echo "Listed count: ".$decode[0][user][listed_count]."<br />"; // Listed count
echo "Description: ".$decode[0][user][description]."<br />"; //user description
echo "Latest tweet: ".$decode[0][text]."<br />"; //last tweet
echo "Latest tweet created at: ".rtoctime($decode[0][created_at])."<br />"; //when tweet
?>
</p>


<h3>语法系</h3>

<p>
<?php
$json = file_get_contents("http://twitter.com/status/user_timeline/nodoka_bot.json?count=1", true);
$decode = json_decode($json, true);

echo "<a href=\"https://twitter.com/nodoka_bot\" target=\"_blank\"><img src=\"".$decode[0][user][profile_image_url]."\" /></a><br>"; //getting the profile image
echo "Name: ".$decode[0][user][name]."<br />"; //getting the username
echo "Screen name: ".$decode[0][user][screen_name]."<br />";
echo "Web: ".$decode[0][user][url]."<br />"; //getting the web site address
echo "Location: ".$decode[0][user][location]."<br />"; //user location
echo "Updates: ".$decode[0][user][statuses_count]."<br />"; //number of updates
echo "Follower: ".$decode[0][user][followers_count]."<br />"; //number of followers
echo "Following: ".$decode[0][user][friends_count]."<br />"; // following
echo "Listed count: ".$decode[0][user][listed_count]."<br />"; // Listed count
echo "Description: ".$decode[0][user][description]."<br />"; //user description
echo "Latest tweet: ".$decode[0][text]."<br />"; //last tweet
echo "Latest tweet created at: ".rtoctime($decode[0][created_at])."<br />"; //when tweet
?>
</p>
<h3>查询系</h3>
<p>
<?php
$json = file_get_contents("http://twitter.com/status/user_timeline/webliko.json?count=1", true);
$decode = json_decode($json, true);

echo "<a href=\"https://twitter.com/webliko\" target=\"_blank\"><img src=\"".$decode[0][user][profile_image_url]."\" /></a><br>"; //getting the profile image
echo "Name: ".$decode[0][user][name]."<br />"; //getting the username
echo "Screen name: ".$decode[0][user][screen_name]."<br />";
echo "Web: ".$decode[0][user][url]."<br />"; //getting the web site address
echo "Location: ".$decode[0][user][location]."<br />"; //user location
echo "Updates: ".$decode[0][user][statuses_count]."<br />"; //number of updates
echo "Follower: ".$decode[0][user][followers_count]."<br />"; //number of followers
echo "Following: ".$decode[0][user][friends_count]."<br />"; // following
echo "Listed count: ".$decode[0][user][listed_count]."<br />"; // Listed count
echo "Description: ".$decode[0][user][description]."<br />"; //user description
echo "Latest tweet: ".$decode[0][text]."<br />"; //last tweet
echo "Latest tweet created at: ".rtoctime($decode[0][created_at])."<br />"; //when tweet
?>
</p>

        <h3>工具</h3>
        <ul>
          <li>
            <a href='/themter.php' target="_blank">Themter</a>
          </li>
        </ul>
      </div>

      <div id='footer'>
        <small>
          Since 2010 &copy; MinamiMaster.Theme by <a href="https://twitter.com/rainux">Rainux</a>.
            <?php
            $mtime1 = explode(" ", microtime());
            $endTime = $mtime1[0] + $mtime1[1];
            printf ("Page execution time:%.6fs.",$endTime-$startTime);
            ?>
        </small>
      </div>
    </div>
</body>
</html>