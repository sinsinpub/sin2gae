<?php


$Users =array( 
// '****' => '****', 
// '****' => '****', 
'username' => 'password', 
); 

foreach ($Users as $username=>$password){

function do_post_request($url)
{
	$params = array('http' => array(
	'method' => 'POST',
	'content' => null));
 
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
}

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
	if (@simplexml_load_file("http://$username:$password@api2.fanfou.com/friendships/exists.xml?user_a=$usertounfollow&user_b=$username") == "true")
	{
		echo $usertounfollow ." 已经关注了你，不必移除\n";
	}
	else
	{
		$output = do_post_request("http://$username:$password@api2.fanfou.com/friendships/destroy.xml?id=$usertounfollow");
		$output = simplexml_load_string($output);
		echo '你刚才移除了: '. $usertounfollow ."\n";
	}
}

// 关注用户

$usertoprefollow0 = one_to_follow($username, $password, $username);

$userstofollow = simplexml_load_file("http://$username:$password@api2.fanfou.com/followers/ids/$usertoprefollow0.xml");
$userstofollow = (array)$userstofollow;
$userstofollow2 = array();
$userstofollow2 = convert_query($userstofollow);
if (count($userstofollow2) > 0)
    {
    $rand_keys = array_rand($userstofollow2, 100);
    for ($i=0; $i<=99; $i++)
    {
        $usertofollow = $userstofollow2[$rand_keys[$i]];
        if (@simplexml_load_file("http://$username:$password@api2.fanfou.com/friendships/exists.xml?user_a=$usertofollow&user_b=$username") == "true")
        {
            echo $usertofollow ." 已经关注了你，不必移除\n";
        }
        else
        {
            $output = do_post_request("http://$username:$password@api2.fanfou.com/friendships/create.xml?id=$usertofollow");
            $output = simplexml_load_string($output);
            echo '你刚刚关注了: '. $usertofollow ."\n";
        }
    }
}


}
?>