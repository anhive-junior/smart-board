<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");


include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

?>