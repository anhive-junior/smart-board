<?php 
session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");


unset($_SESSION['profile']);
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");


if (isset($_POST['init']) && $_POST['init'] == 1){
	$data=array(
		"photo" => array($photo, "profile"),
		"sodata" => array($subject,$owner),
		"footer" => $footnote,
		"title" => $title,
		"samcode" => $sam_code,
		"accesscode" => $access_code,
		"supriseboxcode" => $surprisebox_code,
		"externalport" => $external_port,
		"gateserver" => $gate_server,
		"ssid" => $ssid,
		"wifi_password" => $wifi_password
	);
	outputJSON($data,"success");
}
?>