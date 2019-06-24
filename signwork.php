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

?>
