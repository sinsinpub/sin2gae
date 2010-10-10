<?php


include_once( 'weibo.sae.class.php' );

$w = new weibo( 'sina open app key' );
$w->setUser( 'user login name' , 'user password' );
$filename = 'data.txt';
if ($fh = fopen($filename, "r")) {
	$putfile = file($filename);
	$randpost = array_rand($putfile);
	$postTweet = str_replace("\r\n","",$putfile[$randpost]);
	fclose($fh);
} else {
	echo '<h1>File is not exist.</h1>';
}
$w->update($postTweet);

?>

<hr /><a href="http://sae.sina.com.cn"><img src="http://sae.sina.com.cn/static/image/poweredby.png" title="Powered by Sina App Engine" /></a>