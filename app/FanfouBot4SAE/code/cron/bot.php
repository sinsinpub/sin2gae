<?php

$username = '';
$password = '';
$filename = 'data.txt';

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

if ($fh = fopen($filename, "r")) {
        $putfile = file($filename);
        $randpost = array_rand($putfile);
        $postTweet = str_replace("\r\n","",$putfile[$randpost]);
        fclose($fh);
        if (strlen_utf8($postTweet) > 140 )
        {
            $postTweet = utf8Substr($postTweet, 0, 137).'...';
        }
        $f = new SaeFetchurl();
        $f->setHttpAuth($username, $password);
        $f->setMethod('post');
        $f->setPostData( array('status' => $postTweet) );
        $ret = $f->fetch('http://api2.fanfou.com/statuses/update.xml', array('useragent'=>'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.8) Gecko/20100723 Ubuntu/10.04 (lucid) Firefox/3.6.8'));
        //抓取失败时输出错误码和错误信息
        if ($ret === false)
            var_dump($f->errno(), $f->errmsg());
} else {
        echo '<h1>File is not exist.</h1>';
}

?>