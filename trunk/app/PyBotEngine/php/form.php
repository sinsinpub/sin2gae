<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Twitter手動投稿用フォーム</title>
</head>
<body>
<center>
<p>Twitter手動投稿用フォーム</p>
<p>手動で何か投稿させたいときに使ってください。</p>
<form action="form.php" method="POST">
ユーザー名<br />
<input type="text" name="username" value="" /><br />
パスワード<br />
<input type="password" name="password" value="" /><br />
発言内容：<br />
<textarea name="message" cols="20" rows="4"></textarea><br />
<input type="submit" name="submit" value="post" /><br />
</form>

<?php
if(isset($_POST["submit"])){
    require_once("Services/Twitter.php");
    $username = $_POST["username"]; 
    $password = $_POST["password"];   
    $message = $_POST["message"];   
    
    $st =& new Services_Twitter($username, $password);
    $result = $st->setUpdate($message);
    if($result){
        echo "Twitterへのpostに成功しました。<br />";
        echo "@<a href='http://twitter.com/{$username}' target='_blank'>{$username}</a>に投稿したメッセージ：{$message}";
    }else{
        echo "Twitterへの投稿に失敗しました。パスワードやユーザー名をもう一度チェックしてみてください。<br />";        
        echo "ユーザー名：@<a href='http://twitter.com/{$username}' target='_blank'>{$username}</a><br />";
        echo "投稿しようとしたメッセージ：{$message}";
    }    
}

?>
</center>
</body>
</html>