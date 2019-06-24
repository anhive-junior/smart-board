<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php"); // outputJSON();

if (isset($_POST['init']) && $_POST['init'] == 1){
	$data=array(
		"photo" => array($photo, "profile"),
		"subject" => $subject,
		"owner" => $owner,
		"footer" => $footnote
	);
	outputJSON($data, "success");
} 

if(isset($_SESSION['uselevel']) && $_SESSION['uselevel']>1){ // rmcard(); privilege
	$data=array(
		"id" => "btn_cancel",
		"func" => "rmcard()",
		"spanInner" => "회수"
	);
	outputJSON($data, "success");
}

?>