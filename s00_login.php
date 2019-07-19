<?php
session_start(); 
session_unset(); 
error_log(__FILE__."::".session_id());

include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

// for teacher subject
$services['init'] = '_init';
function _init(){
    $anonymous = (file_exists("ANONYMOUS_USE"))? true : false;
    if (!$anonymous) {
        global $pname;
        $_SESSION["scope"] = $pname; 
    }        
    if(file_exists("/run/shm/participants.lst.freezing")){ 
        $data = array("freezing" => "점검중");
    }
    $data["name"] = isset($_COOKIE['NAME'])?$_COOKIE['NAME']:'';
    $data["code"] = isset($_COOKIE['CODE'])?$_COOKIE['CODE']:'';
    outputJSON($data, "success");
}


$services['login'] = '_login';
function _login(){
    $freespace = disk_free_space(".")/1024; // space value
    error_log("Start : " . __FUNCTION__ . " LINE : " .  __LINE__);
    //date_default_timezone_set('Asia/Seoul');
    $user_code = isset($_POST['user_code'])?trim($_POST['user_code']):"";
    $input_code = isset($_POST['input_code'])?trim($_POST['input_code']):"";
    error_log("------- user_code -[$user_code] -------access code-[$input_code]-------");
    $_SESSION['uselevel']=0;
    $destination="home.html";
    $cookie_expire = time()+60*60*24*365;
    if ( !isset($user_code) ) {
        $mesg = "사용자와 접속코드를 입력하세요!"."-- :)";
    } else if ( !isset($input_code) ) {
        $mesg = "접속코드를 입력하세요";
    } else if ( $_SESSION['access_code'] == $input_code ) {
        $_SESSION['uselxevel']=1;
        $destination="cardwork.html";
    } else if ( $_SESSION['sam_code'] == $input_code /* && !$anonymous */ ) {
        if (  $freespace < 16 ) @unlink ( "emergency.dumy" );
        $_SESSION['uselevel']=2;
    } else if ( $_SESSION["admin_code"] == $input_code ) {
        if (  $freespace < 16 ) @unlink ( "emergency.dumy" );
        $_SESSION['uselevel']=3;
    } else {
        $mesg = "접속코드를 확인해주세요!";
    }

    if ($_SESSION['uselevel']>0) {
        setrawcookie ("NAME", $user_code, $cookie_expire);
        setrawcookie ("CODE", $input_code, $cookie_expire);
        $data[] = array(
            "name" => "dst",
            "value" => $destination
        );
        $data[] = array(
            "name" => "user",
            "value" => $user_code
        );
        $arr = array("contents"=>$data, "location"=> "welcome.php", "mesg" => "login");
        outputJSON($arr, "success");
    }
    $mesg = array("mesg" => $mesg);
    outputJSON($mesg,  "success");
}

$services['headnote'] = '_headnote';
function _headnote(){
    error_log("Start : " . __FUNCTION__);
    $data=array(
        "title" => $_SESSION['title'],
        "subject" => $_SESSION['subject'],
        "owner" => $_SESSION['owner'],
        "photo" => $_SESSION['photo'],
        "footer" => $_SESSION['footnote']
        );
    outputJSON($data, "success");    
}

$services['disk'] = '_disk';
function _disk(){
    error_log("Start : " . __FUNCTION__ . " LINE : " .  __LINE__);
    $freespace = disk_free_space(".")/1024;
    $data = "none";
    if ( $freespace < 16 ) $data = "저장공간부족..!";
    outputJSON($data,"success");
}

$func= isset($_POST['func'])?$_POST["func"]:"test";
if (isset($services[$func])){
    try {
        call_user_func( $services[$func]);
        //s00_log2(4, print_r($services,true));
    } catch (Exception $e) {
        outputJSON($e->getLine().'@'.__FILE__."\n".$e->getMessage());
        s00_log(print_r($e->getTrace(),true));
    }
}
?>