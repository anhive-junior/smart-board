<?php 
session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");

include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

if (isset($_POST['init']) && $_POST['init'] == 1){
	$data=array(
		"photo" => array($photo, "profile"),
		"sodata" => array($subject,$owner),
		"footer" => $footnote
		
	);
	outputJSON($data, "success");
	

}

if((isset($_POST['level']) && $_POST['level'] == 1) && $_SESSION['uselevel']>1){
	$data=array( 
	     "func" =>"rmvideo()",
	     "value" => "삭제",
	     "script" => "var rmvideo = function(msg){
		var data = new FormData();
		data.append('func','rmvideo');
		data.append('video', _video.name);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML=resp.data;
				getslide('first');
		});
	}");
	outputJSON($data, "success");
}else{
	$data = "0";
	outputJSON($data,"success");
}
