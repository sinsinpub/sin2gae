<?php
$mtime1 = explode(" ", microtime());
$startTime = $mtime1[0] + $mtime1[1];
?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type' />
    <title>Themter</title>
    <link href='/main.css' media='screen' rel='stylesheet' type='text/css' />
  </head>
  <body>
    <div id='container'>

      <div id='header'>
        <div>
          <h1>Themter</h1>
        </div>
      </div>
      <div id='content'>
        <div id='nav'>
          <p>

            <a href='./'>Back</a>
          </p>
        </div>
        <h3>这是什么？</h3>
        <p>
         用来查看Twitter用户的主题颜色代码和图像。
        </p>

        <h3>输入一个ID以继续</h3>





<form id="themter" name="username" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?p=1';?>">
Twitter ID: <input type="text" name="username" id="username" size="20" maxlength="20" />
<input type="submit" value="查看" />  <span>*例如输入 shimada_minami<span/>
</form>
<?php
function fontbgcolor($hexColor) {
	$bgroundc = '<span style="background-color:#'.$hexColor.'">'.$hexColor.'</span>';
    return $bgroundc;
}
function checkusername ($name,$min=1,$max=15) {
  if(strlen($name)<$min or strlen($name)>$max)return false;
  $preg="/^[\w\d_]+$/";  //定义字符范围。
  if(!preg_match($preg,$name))return false;
 return true;
}

if (isset($_GET["p"]))
{if ($_GET["p"]==="1"){
if (checkusername($_POST['username'])){
echo "<p>";
@$json = file_get_contents("http://twitter.com/status/user_timeline/".$_POST['username'].".json?count=1", true)
	or exit("<br />找不到用户。<br />");
$decode = json_decode($json, true);
if ($decode != null){
echo "<a href=\"https://twitter.com/".$_POST['username']."\" target=\"_blank\"><img src=\"".$decode[0][user][profile_image_url]."\" /></a><br>"; //getting the profile image
echo "Name: ".$decode[0][user][name]."<br />"; //getting the username
echo "Screen name: ".$decode[0][user][screen_name]."<br />";
echo "Background color: ".fontbgcolor($decode[0][user][profile_background_color])."<br />"; 
echo "Text color: ".fontbgcolor($decode[0][user][profile_text_color])."<br />"; 
echo "Link color: ".fontbgcolor($decode[0][user][profile_link_color])."<br />"; 
echo "Sidebar fill color: ".fontbgcolor($decode[0][user][profile_sidebar_fill_color])."<br />"; 
echo "Sidebar border color: ".fontbgcolor($decode[0][user][profile_sidebar_border_color])."<br />"; 
$pbiu = $decode[0][user][profile_background_image_url];
echo "Background image: <a href=\"".$pbiu."\" target=\"_blank\">".$pbiu."</a><br />"; 
$piu = str_replace('_normal','',$decode[0][user][profile_image_url]);
echo "Icon image url: <a href=\"".$piu."\" target=\"_blank\">".$piu."</a><br />"; 
echo "</p>";}else{
	echo "<br />用户未设置。";
}
} else {
    echo '<br />输入的ID \''.$_POST['username'].'\' 格式不正确。<br />';
}
}
}
?>
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