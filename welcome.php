<?php 
if (isset($_GET['obj'])) session_id($_GET['obj']);
session_start(); 
error_log(__FILE__."::".session_id());

$http_host = $_SERVER["HTTP_HOST"];
$http_refe = $_SERVER["HTTP_REFERER"];

if(preg_match( "/$http_host.signage/", $http_refe ))
    error_log( "SUCCESS /$http_host.*signage/, $http_refe" );
else
    error_log( "FAIL /$http_host.*signage/, $http_refe" );

include_once("lib/captive.php");
    
//bypass captive portal
$ip = NULL;
$mac = NULL;
$user = NULL;
if (true) { 
    $ip = $_SERVER['REMOTE_ADDR'];
    $mac = bypass_captive($ip);
    error_log ("\$mac=".$mac);
    //$mac = get_mac($ip);
    $_SESSION["device"] = $_SERVER['REMOTE_ADDR'];  //set device info
    error_log ("\$_SESSION['device']=".$_SESSION["device"]);
}

if ($mac == NULL) {
    echo "Not available mac address\n";
}

//student should be hold after freezing.
if ( $_SESSION['uselevel'] < 2 && is_freezed($mac, $ip) ) {
    die(header("location: index.html"));
}

//dst should be describe for jumping go to page after this process
if (! isset( $_GET['dst'])) { 
    die ("System is not ready to get ['dst']"); 
}
$destination = $_GET['dst'];
$user = $_GET['user'];

$_SESSION["mac"] = $mac;
$_SESSION["ip"] = $ip;

//check user lists
capture_user($user, $ip, $mac);

header("location:$destination");
?>
