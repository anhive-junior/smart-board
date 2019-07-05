<?php
include_once("lib/lib_common.php");
$trace=true;

function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
}

$services['showvideo'] = '_showvideo';
function _showvideo(){
	s00_log("Start ".__FUNCTION__);
    $name = $_GET["name"];
    if (strpos($name, 'http') !== false) {
        $url = $name;
        outputJSON($url,"success");
    } else {
        //$filename = basename ($name);  //c
        $filename = $name;  //c
        $dir= isset($_GET['dir'])?$_GET['dir']:'';
        //$script_path = dirname(trim($_SERVER['SCRIPT_NAME']));
        //if ($script_path != "") $dir = $script_path."/".$dir;
        $url = $dir."/".$filename;
        outputJSON($url,"success");
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
