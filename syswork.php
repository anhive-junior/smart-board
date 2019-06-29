<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");


include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");


$services['headnote'] = '_headnote';
function _headnote(){
	$data=array(
		"photo" => $_SESSION['photo'],
		"subject" => $_SESSION['subject'],
		"owner" => $_SESSION['owner'],
		"footer" => $_SESSION['footnote'],
		"title" => $_SESSION['title'],
		"samcode" => $_SESSION['sam_code'],
		"accesscode" => $_SESSION['access_code'],
		"supriseboxcode" => $_SESSION['surprisebox_code'],
		"externalport" => $_SESSION['external_port'],
		"gateserver" => $_SESSION['gate_server'],
		"ssid" => $_SESSION['ssid'],
		"wifi_password" => $_SESSION['wifi_password']
	);
	outputJSON($data,"success");
}



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