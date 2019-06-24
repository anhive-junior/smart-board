<?php
session_start(); 
session_unset(); 
error_log(__FILE__."::".session_id());

include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

if (isset($_POST['init'])&& $_POST['init'] == 1){
	$data=array(
	    "title" => $title,
		"sodata" => array($subject,$owner),
		"photo" => array($photo, "profile","width:50%;"),
		"footer" => $footnote
		);
		outputJSON($data, "success");
}


   
?>