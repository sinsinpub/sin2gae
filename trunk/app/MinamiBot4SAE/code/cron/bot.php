<?php

include_once( 'weibo.sae.class.php' );

function utf8Substr($str, $from, $len){
    return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
                       '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
                       '$1',$str);
}

function strlen_utf8($str) {
    $i = 0;
    $count = 0;
    $len = strlen ($str);
    while ($i < $len) {
        $chr = ord ($str[$i]);
        $count++;
        $i++;
        if($i >= $len) break;
        if($chr & 0x80) {
            $chr <<= 1;
            while ($chr & 0x80) {
                $i++;
                $chr <<= 1;
            }
        }
    }
    return $count;
}

$w = new weibo( '********' );
$w->setUser( '********' , '********' );
$filename = 'data.txt';
if ($fh = fopen($filename, "r")) {
	$putfile = file($filename);
	$randpost = array_rand($putfile);
	$postTweet = str_replace("\r\n","",$putfile[$randpost]);
	fclose($fh);
    if (strlen_utf8($postTweet) > 140 )
    {
        $postTweet = utf8Substr($postTweet, 0, 137).'...';
    }
} else {
	echo '<h1>Open is not exist.</h1>';
}
$w->update($postTweet);

?>