<?php 
session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");

include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

$services['level'] = '_level';
function _level(){
    if($_SESSION['uselevel']<=1 || !isset($_SESSION['uselevel'])){
        $data = "0";
        outputJSON($data,"success");
    }
    $data=array(     
       "func" =>"rmvideo()",
       "value" => "삭제"
    );
    outputJSON($data, "success");
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