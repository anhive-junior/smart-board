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

if((isset($_POST['init']) && $_POST['init'] == 1) && $_SESSION['uselevel']>1){
	$data=array(
	    "link" => "rmvideo()", 
	    "stringdata" => "삭제"
	    );
		outputJSON($data,"success");
} // session 값을 확인을 못함 .. ?? 

?>