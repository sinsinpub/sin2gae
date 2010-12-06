<?php


$Users =array( 
// '****' => '****', 
// '****' => '****', 
'username' => 'password', 
); 

// 对多个机器人账户
foreach ($Users as $username=>$password){
// post函数
function do_post_request($url)
{
	$params = array('http' => array(
	'method' => 'POST',
	'content' => null));
 
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
}
// 把2维数组转换为1维
function convert_query($userstofollow)
{
    foreach ($userstofollow as $index=>$arr)
    {
        foreach ($arr as $i=>$value)
        {
            $userstofollow2[] = $value;
        }
    }
    return $userstofollow2;
}
// 随机获取一个ID的某一个foer或foing
function one_to_follow($username, $password, $target)
{
    $rand_num = rand(1, 2);
    if (rand_num == 1)
    {
        $foing_or_foer = 'followers';
    }
    else
    {
        $foing_or_foer = 'friends';
    }
    $userstoprefollow = simplexml_load_file("http://$username:$password@api2.fanfou.com/$foing_or_foer/ids/$target.xml");
    $userstoprefollow = (array)$userstoprefollow;
    $userstoprefollow2 = convert_query($userstoprefollow);
    $rand_key = array_rand($userstoprefollow2);
    $usertoprefollow = $userstoprefollow2[$rand_key];
    return $usertoprefollow;
}

// 移除关注
$userstounfollow = simplexml_load_file("http://$username:$password@api2.fanfou.com/friends/ids/$username.xml");
foreach ($userstounfollow as $usertounfollow)
{
    // 如果对方关注了我，则不移除
	if (@simplexml_load_file("http://$username:$password@api2.fanfou.com/friendships/exists.xml?user_a=$usertounfollow&user_b=$username") == "true")
	{
		echo $usertounfollow ." 已经关注了你，不必移除\n";
	}
	else
	{
        // 如果对方没有关注我，则移除
		$output = do_post_request("http://$username:$password@api2.fanfou.com/friendships/destroy.xml?id=$usertounfollow");
		$output = simplexml_load_string($output);
		echo '你刚才移除了: '. $usertounfollow ."\n";
	}
}

// 关注用户
$usertoprefollow0 = one_to_follow($username, $password, $username);

$rand_num = rand(1, 2);
if (rand_num == 1)
{
    $foing_or_foer = 'followers';
}
else
{
    $foing_or_foer = 'friends';
}
$userstofollow = simplexml_load_file("http://$username:$password@api2.fanfou.com/$foing_or_foer/ids/$usertoprefollow0.xml");
$userstofollow = (array)$userstofollow;
$userstofollow2 = array();
$userstofollow2 = convert_query($userstofollow);
if (count($userstofollow2) > 0) // 如果用户数不为0
{
    // 仅最多关注100个用户
    $rand_keys = array_rand($userstofollow2, 100);
    for ($i=0; $i<=99; $i++)
    {
        $usertofollow = $userstofollow2[$rand_keys[$i]];
        // 如果对方关注了我，则不关注
        if (@simplexml_load_file("http://$username:$password@api2.fanfou.com/friendships/exists.xml?user_a=$usertofollow&user_b=$username") == "true")
        {
            echo $usertofollow ." 已经关注了你，不必再关注\n";
        }
        else
        {
            // 如果对方没有关注我，则关注
            $output = do_post_request("http://$username:$password@api2.fanfou.com/friendships/create.xml?id=$usertofollow");
            $output = simplexml_load_string($output);
            echo '你刚刚关注了: '. $usertofollow ."\n";
        }
    }
}


}
?>