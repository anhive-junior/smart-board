<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php"); // outputJSON();


$services['headnote'] = '_headnote';
function _headnote(){
	$data=array(
		"photo" => $_SESSION['photo'],
		"profile" => "profile",
		"subject" => $_SESSION['subject'],
		"owner" => $_SESSION['owner'],
		"footer" => $_SESSION['footnote']
	);
	outputJSON($data, "success");
};

$service['privilege_rmcard'] = '_privilege_rmcard';
function _privilege_rmcard(){
	if(isset($_SESSION['uselevel']) && $_SESSION['uselevel']>1){ // rmcard(); privilege
	    $data=array(
		   "func" => "rmcard()",
		   "spanInner" => "회수"
		 );
		 outputJSON($data, "success");
	 }
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