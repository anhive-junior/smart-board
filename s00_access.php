<?php
session_start(); 
include_once("lib/get_access.php");

if (isset($_GET['test'])) die($_GET['test'].":OK");
if (!isset($_POST['album_code'])) { 
    error_code("album code is not defined"); 
    die ('fail'); 
}
if ( $surprisebox_code != $_POST['album_code']) { 
    error_code("[$surprisebox_code] is not matched [".$_POST['album_code']."]"); 
    die ('fail'); 
}

if (!isset($_POST['input_code'])) { 
    error_code("input code is not defined"); 
    die ('fail'); 
}
$input_code =  $_POST['input_code'];
$check_code = "#$access_code;#$admin_code;#$sam_code;";
error_log( "received [$check_code]:".$_POST['input_code']);

if (! preg_match( '/#'.$input_code.';/', $check_code)) die ('fail');

$user_code = isset($_POST['user_code'])?trim($_POST['user_code']):"";
setrawcookie ("FAVORIT", 'norm1', time()+3); // homepage의 메뉴 자동선택

error_log("------- $user_code --------$input_code-----------------"); 
$_SESSION['uselevel']=0;
$cookie_expire = time()+60*60*24*365;
if ( $access_code == $input_code ) {
    // move to home.php over welcome.php
    $_SESSION['uselevel']=1;
    $url = "welcome.php?dst=cardwork.php&user=".$user_code;
} else if ( $sam_code == $input_code /* && !$anonymous */ ) {
    $_SESSION['uselevel']=2;
    $url = "welcome.php?dst=home.php&user=".$user_code;
} else if ( $admin_code == $input_code ) {
    $_SESSION['uselevel']=3;
    $url = "welcome.php?dst=home.php&user=".$user_code;
} else if ( $anhive_code == $input_code ) {
    $_SESSION['uselevel']=4;
    $url = "welcome.php?dst=home.php&user=".$user_code;
}

if ($_SESSION['uselevel']>0) {
    setrawcookie ("NAME", $user_code, $cookie_expire);
    setrawcookie ("CODE", $input_code, $cookie_expire);
    $key = session_id();
    die ($url."&obj=$key");
}
die ('fail');
?>
