<?php
// twitteroauth.phpを読み込む。パスはあなたが置いた適切な場所に変更してください
require_once("twitteroauth.php");
class Twitter_Bot{
	var $user;
	var $TO;
	var $times;
	function Twitter_Bot($usr,$consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret){
		$this->user = $usr;
		$this->TO = new TwitterOAuth($consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret);
		$this->times = array_sum(explode(" ",microtime()));
	}
	function Request($url,$method = "POST",$opt = array()){
		$req = $this->TO->OAuthRequest("https://twitter.com/".$url,$method,$opt);
		if($req){$result = $req;} else {$result = null;}
		return $result;
	}
	// データを読み込む。ここをSQLiteやMySQLなどにデータを保存するように書き換えてもいいかもしれない。
	function Get_data($type){
		$dat = "./".$this->user."_".$type.".dat";
		if(file_exists($dat)){
			touch($dat);
			chmod($dat,0666);
			return null;
		}
		return file($dat);
	}
	// データを書き込む。ここをSQLiteやMySQLなどにデータを保存するように書き換えてもいいかもしれない。
	function Save_data($type,$data){
		$dat = "./".$this->user."_".$type.".dat";
		if(file_exists($dat)){
			touch($dat);
			chmod($dat,0666);
		}
		$fdat = fopen($dat);
		flock($fdat,LOCK_EX);
		fputs($fdat,$data);
		flock($fdat,LOCK_UN);
		fclose($fdat);
	}
	// 呟きをPOSTする。$statusには発言内容。
	// $repは相手にリプライする場合にリプライ元の呟きのIDを指定する。リプライ元の呟きとリプライする相手のユーザー名が一致しないといけない。
	function Post($status,$rep = null){
		$opt = array();
		$opt['status'] = $status;
		if($rep){$opt['in_reply_to_status_id'] = $rep;}
		$req = $this->Request("statuses/update.xml","POST",$opt);
		if(!$req){die('Post(): $req is NULL');}
		$code = $req->Code;
		$xml = simplexml_load_string($req->Body) or die("Error: ".$code);
		if(($code == "200" || $code == "403") && $rep){$this->Save_data("Since",$rep); return;}
		if($xml->error){die($code.", ".$xml->error);}
	}
	// タイムラインなどを取得する。$typeにはhome_timeline、friends_timeline、mentionsなど。詳しくはAPI仕様書を。
	// $sidは呟きのID。$sidで指定した呟きのIDより後の呟きを取得するようにさせる。
	// $countは一度に呟きをどれだけ取得するか。最大200。
	function Get_TL($type,$sid = null,$count = 30){
		$opt = array();
		$opt['count'] = $count;
		if($sid){$opt['since_id'] = $sid;}
		$req = $this->Request("statuses/".$type.".json","GET",$opt);	// JSON形式の方がちょっと扱いやすい
		if($req){
			if($req->Code != "200"){die("Error: ".$req->Code);}
			$result = str_replace(":NULL,",':"NULL",',$req->Body);
		} else {die('Get_TL(): $req is NULL');}
		$result = json_decode($result);
		if($result->error){die($result->error);}
		return array_reverse($result);
	}
	// フォロー・リムーブする。$uidはフォロー、リムーブしたいユーザーナンバー又はユーザー名。$flgは「true」ならフォロー、「false」ならリムーブ。
	// 返り値は「ok」、「already」、「error」の3種類。「ok」は正常にフォロー、リムーブが完了。「already」は既にそのユーザーをフォロー、リムーブしている。「error」はTwitter側の何かしらのエラー。
	function Follow($uid,$flg = true){
		$result = "ok";
		$req = $this->Request("friendships/".($flg?"create":"destroy")."/".$uid.".xml");
		if($req){
			if($req->Code != "200"){$result = "error";}
			$xml = simplexml_load_string($req->Body);
			if($xml->error){$result = "already";}
		} else {$result = "error";}
		return $result;
	}
	// 呟きをお気に入りに追加する。$sidはお気に入りに追加したい呟きのID。
	function Favorite($sid){
		$req = $this->Request("favorites/create/".$sid.".xml");
		if(!$req){die('Favorite(): $req is NULL');}
		if($req->Code != "200"){die("Error: ".$req->Code);}
	}
	// 呟きを消す。$sidは消したい呟きのID。自分の呟き以外はエラーになります。
	function Delete($sid){
		$req = $this->Request("statuses/destroy/".$sid.".xml","DELETE");
		if($req){
			if($req->Code != "200"){die("Error: ".$req->Code);}
			$xml = simplexml_load_string($req->Body);
			if($xml->error){die($xml->error);}
		} else {die('Delete(): $req is NULL');}
	}
	// 呟きをRTする。$sidはRTしたい呟きのID。
	function RT($sid){
		$req = $this->Request("statuses/retweet/".$sid.".xml","POST");
		if($req){
			if($req->Code != "200"){die("Error: ".$req->Code);}
			$xml = simplexml_load_string($req->Body);
			if($xml->error){die($xml->error);}
		} else {die('RT(): $req is NULL');}
	}
	// DMを送る。$uidはDMを送りたいユーザーナンバー又はユーザー名。$textは本文。
	function DM($uid,$text){
		$req = $this->Request("direct_messages/new.xml","POST",array("user"=>$uid,"text"=>$text));
		if($req){
			if($req->Code != "200"){die("Error: ".$req->Code);}
			$xml = simplexml_load_string($req->Body);
			if($xml->error){die($xml->error);}
		} else {die('DM(): $req is NULL');}
	}
	// 終わりの処理
	function End($sid){
		$this->Save_data("Since",$sid);
		echo "Normal termination: ".sprintf("%0.4f",array_sum(explode(" ",microtime())) - $this->times)." sec, ".date("H:i:s");
	}
}
// 配列$arrからランダムに一つ取り出す
function Rrt($arr){
	$rand = array_rand($arr,1);
	return $arr[$rand];
}
?>