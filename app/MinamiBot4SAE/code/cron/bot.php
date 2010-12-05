<?php

include_once( 'weibo.sae.class.php' );

$w = new weibo( '********' );
$w->setUser( '********' , '********' );
$filename = 'data.txt';
if ($fh = fopen($filename, "r")) {
	$putfile = file($filename);
	$randpost = array_rand($putfile);
	$postTweet = str_replace("\r\n","",$putfile[$randpost]);
	fclose($fh);
} else {
	echo '<h1>Open is not exist.</h1>';
}
$w->update($postTweet);

?>