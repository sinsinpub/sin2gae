<?php

$username = '';
$password = '';
$filename = 'data.txt';

if ($fh = fopen($filename, "r")) {
        $putfile = file($filename);
        $randpost = array_rand($putfile);
        $postTweet = str_replace("\r\n","",$putfile[$randpost]);
        fclose($fh);
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