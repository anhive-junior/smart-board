<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
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
?>