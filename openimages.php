<?php 
/**
 * AnHive Co., Ltd.
 * LICENSE: This source file is subject to version 0.1 of the AnHive license. 
 * If you did not receive a copy of the AnHive License and are unable to obtain it
 * please send a note to anhive@gmail.com so we can mail you a copy immediately.
 *
 * @author     AnHive Co., Ltd <anhive@gmail.com>
 * @copyright  2013-2015 AnHive Co., Ltd
 * @license    http://www.anhive.com/license/1_01.txt  AnHive License 1.01
 */
session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <script type="text/javascript" src="signage.base.js"></script>
	<link rel="stylesheet" type="text/css" href="signage.base.css">
    <title>AnHive, Show Video</title>
    <style>
       body { margin:0; height:100%;width:100%}
    </style>
</head>
<body>

<?php
    $name = $_GET["name"];
    if (strpos($name, 'http') !== false) {
        $uri = $name;
    } else {
        $filename = basename ($name);  //c
        $dir= isset($_GET['dir'])?$_GET['dir']:$config_slide;
        $script_path = dirname(trim($_SERVER['SCRIPT_NAME']));
        //if ($script_path != "") $dir = $script_path."/".$dir;
        $uri = $dir."/".$filename;
        $cap = $config_caption."/".$filename.'.txt';
    }
?>
	<div style="margin-top:10px;"><!-- upper line feed --></div>
	<div style="text-align:center;">
<?php if ($_SESSION['uselevel']>1) { ?>	
	<div><?php echo "파일: $filename<br>"; ?></div>
<?php } ?>
	<div><?php echo "등록일: ".date("Y/m/d H:i:s.", filemtime($uri)); ?></div>
	<div><?php echo "메모: ".(file_exists($cap)?file_get_contents($cap):""); ?></div><br>
    <img id="img" src="<?php echo $uri ?>" style="width:90%;height:90%;"/><br>
	<div style="margin-top:10px;"><!-- upper line feed --></div>
	<input type="button" onclick="history.go(-1);" value="돌아가기">
<?php if ($_SESSION['uselevel']>1) { ?>	
	<input type="button" onclick="rmcard('<?php echo $uri ?>');" value="사진삭제">
<?php } ?>
	</div>
	
<script>

var rmcard = function(fname){
	
	var data = new FormData();
	data.append('func', 'rmcard');
	data.append('card', fname);
	
	POST('s00_signage.php', data, 
		function (resp) {  
			console.log(resp.status + ': ' + resp.data);
			window.history.back();
	});
}

</script>
</body>
</html>