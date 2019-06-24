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
include_once("lib/lib_common.php"); // outputJSON();

$name = $_POST["name"];
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

$id_date = date("Y/m/d H:i:s.", filemtime($uri));
$memo = "메모: ".(file_exists($cap)?file_get_contents($cap):"");
$img = $uri;

$data = array(
"file_name" => $filename,
"memo" => $memo,
"date" => $id_date,
"img" => $img
);

if(isset($_SESSION['uselevel']) && $_SESSION['uselevel']>1){ // rmcard(); privilege
	array_push($data["func"], "rmcard($img)");
	array_push($data["value"], "사진삭제");
	array_push($data["script"], "var rmcard = function(msg){
		var data = new FormData();
		data.append('func','rmcard');
		data.append('card', _photo.alt);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML=resp.data;
				getslide('first');
		});
	}");
}
outputJSON($data, "success");
?>
