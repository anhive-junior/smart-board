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

$services['init'] = '_init';

function _init(){
	$name = $_POST["name"];
	error_log("name----------------------$name---------------------");
	if (strpos($name, 'http') !== false) {
		$uri = $name;
		}else{
			$filename = basename ($name);  //c
			error_log("filename----------------------$filename---------------------");
			$dir= isset($_GET['dir'])?$_GET['dir']:$_SESSION['slide'];
			error_log("dir---------------------$dir---------------------");
			$script_path = dirname(trim($_SERVER['SCRIPT_NAME']));//if ($script_path != "") $dir = $script_path."/".$dir;
			$uri = $dir."/".$filename;
			error_log("url----------------------$uri---------------------");
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
	      $data += [ "func" => "rmcard(\"$img\")" ];
		  $data += [ "value" => "사진삭제"];
	};
	outputJSON($data, "success");
};


$func= isset($_POST['func'])?$_POST["func"]:"test";

if (!isset($services[$func])) 
        outputJSON("Undefined service[$func].");
try {
    call_user_func( $services[$func]);
    //s00_log2(4, print_r($services,true));
} catch (Exception $e) {
    outputJSON($e->getLine().'@'.__FILE__."\n".$e->getMessage());
    s00_log(print_r($e->getTrace(),true));
}
?>
