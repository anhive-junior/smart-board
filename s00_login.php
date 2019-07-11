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
    if ( $user_code  == '' /* isset($_POST['user_code']) */ ) {
        $mesg = "사용자와 접속코드를 입력하세요!"."-- :)";
    } else if ( $input_code == '' /* isset($_POST['input_code']) */ ) {
        $mesg = "접속코드를 입력하세요";
    } else if ( $_SESSION['access_code'] == $input_code ) {
        $_SESSION['uselevel']=1;
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
<!DOCTYPE html >
<html> <!-- lang="ko" -->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <link rel="stylesheet" type="text/css" href="signage.base.css">
    <title>anhive</title>
<style>
html, body {
    padding : 0;
    margin : 0;
    height : 100%;
    overflow: hidden;
}
#photo {
    background-size: cover;
    background-position: center;
    height : 100%;
    width : 100%;
}

.login-box {
    opacity: 0.8;
    width : 90%;
    max-width : 450px;
    height : 400px;
    position: absolute;
    top : 45%;
    left: 50%;
    transform : translate(-50%, -50%);
    -webkit-transform: translate(-50%, -50%);
    color : black;
    background-color : white;
    padding : 0 0;
}
 .login-box:hover{
    opacity: 1;
 }

 .login-box h1 {
    /* 
    @media screen configuration -- bottom : 
    media screen 설정해놓음 스타일 태그 하단 참고
    */
    display : table;
    margin : auto;
    font-size: 4vw;

    border-bottom: 3px solid #ff8000;
    padding: 13px 0;
 } 

 
 .textbox {
    display : table;
    margin : auto;
    margin-top : 25px;
    max-width : 300px;
    width : 90%;
    overflow : hidden;
    font-size : 20px;
    padding : 8px 0;
    border-bottom : 1px solid #95a5a6;
 }
 
 .textbox input {
    border : none;
    outline : none;
    background: none;
 }
 .login-box .button {
    display: table;
    margin : auto;
    margin-top : 40px;
    max-width : 270px;
    width: 90%;
    background-color : #ff8000;
    color : white;
    padding : 15px;
    cursor: pointer;
    font-size : 18px;
    text-align: center;
    font-weight: bold;
 }
.login-box .button:hover {
    background-color: green;
 }
.login-header {
    color : black;
    text-align : center;
    font-weight: bold; 
    background-color : white;
    padding: 10px 10px;
 }

.login-footer {
    color : black; 
    text-align : center;
    font-weight: bold; 
    margin-top : 15%; 
    background-color : white;
    padding: 10px 10px;
 }

 @media screen and (min-width : 530px){
    .login-box h1 {
        font-size : 20px;
    }
 }
</style>
</head>
<body>
<div id="photo"></div>
<div class="login-box">
    <div class="login-header" id="title" style="margin-top:15px;"></div>
    <h1 style="margin-top:15px;">
    <span id="subject"></span>&nbsp;<span id="owner"></span>
    </h1>
    <div class="textbox">
    <i><img src="./images/user-solid.svg" title="사용자" alt="user"></i>&nbsp;&nbsp;
    <input type='text' id="ucode" name='user_code' size='7' style="font-size:1.2em;" autocomplete="off" placeholder="사용자">
    </div>
    <div class="textbox">
    <i><img src="./images/key-solid.svg" title="접속코드" alt="code"></i>&nbsp;&nbsp;
    <input type='password' id="icode" name='input_code' size='7' style="font-size:1.2em;" autocomplete="off" placeholder="접속코드">
    </div>
    <div class="button" onclick="loader('Login...');logged_in();" >로그인</div>
    <div class="login-footer" id="foot_note"></div>
</div>
<script>
function loader(msg){
    var load = document.getElementById("load");
    if(load == null){
        var div = "<div id='load' class='bg'>";
        div += "<div class='loader_box'>";
        div += "<div class='loader'><p>🐝</p></div>";
        div += "<div id='load_txt'></div>";
        div += "</div>";
        div += "</div>";
        document.body.innerHTML += div;
    }
    if (msg == undefined){
        msg = "Loading..";
    }
    else if(msg == "exit"){
        document.getElementById("load").style.visibility = "hidden";
        return;
    }
    document.getElementById("load").style.visibility = "visible";
    document.getElementById("load_txt").innerHTML = msg;
}

function alerted(msg){ // alerte
    var alert = document.getElementById("alert");
    if(alert == null){
        var div = "<div id='alert' style='visibility : hidden;'>";
        div += "<h1><img src='./images/bullhorn.svg'></h1>";
        div += "<h2 style='margin-top:40px;' id='alert_inner'></h2>";
        div += "<input type=\"submit\" value=\"확인\" onclick=alerted('exit'); style='margin-top:10px;font-size:20px;'/></div>";
        document.body.innerHTML += div;
    }
    if(msg == "exit"){
        document.getElementById("alert").style.visibility = "hidden";
        return;
    }
    document.getElementById("alert").style.visibility = "visible";
    document.getElementById("alert_inner").innerHTML = msg;
    loader("exit");
}

var init = function(){
    var data = new FormData();
    data.append("func", "init");
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            try {
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            document.getElementById('ucode').value = resp.data.name;
            document.getElementById('icode').value = resp.data.code;
            if ( "freezing" in resp.data && resp.data.freezing == "점검중" ) // 점검중 띄우기
                alerted("점검중입니다. <br> <h5 style='font-size : 15px; color : grey'>일반 사용자는 접속이 불가능합니다.</h5>");
        }
    };
    request.open("POST", window.location.href);
    request.send(data);
}

var logged_in = function(){
    var data = new FormData();
    data.append("func", "login");
    data.append("user_code", document.getElementById("ucode").value);
    data.append("input_code", document.getElementById("icode").value);
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            try {
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            // process
            if ( resp.data.mesg == "login"){
                var form = document.createElement('form');
                form.method = "GET";
                form.action = resp.data.location;

                for(var i=0; i<resp.data.contents.length; i++){
                    var form_code = document.createElement('input');
                    form_code.type = 'hidden';
                    form_code.name = resp.data.contents[i].name;
                    form_code.value = resp.data.contents[i].value;
                    form.appendChild(form_code);
                }
                document.body.appendChild(form);
                form.submit();
            }
            else {
                alerted(resp.data.mesg);
            }
        }
    };
    request.open("POST", window.location.href);
    request.send(data);
}

var headnote = function(){ // headnote에 대한 데이터를 가지고 옵니다.
    var data = new FormData(); 
    data.append("func", "headnote");
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            try {
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            console.log(resp.status + ':' + resp.data);
            document.getElementById("title").innerHTML = resp.data.title;
            document.title = resp.data.subject;
            document.getElementById("subject").innerHTML = resp.data.subject;
            document.getElementById("owner").innerHTML = resp.data.owner;
            document.getElementById("photo").style.backgroundImage = "url(" + resp.data.photo + ")";
            document.getElementById("foot_note").innerHTML=resp.data.footer;
        }
    }
    request.open("POST", window.location.href);
    request.send(data);
}

var disk_data = function(){
    var data = new FormData(); 
    data.append("func", "disk");
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){if(request.readyState == 4){
            try {
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            console.log(resp.status + ':' + resp.data);
            if( resp.data == "none"){
                return;
            }else{
                alerted("<h2 style='color : red'>" + resp.data + "</h2><h5 style='font-size : 15px; color : grey'>저장공간이 부족합니다.</h5>");
            }
        }
    }
    request.open("POST", window.location.href);
    request.send(data);
}
init();
headnote();
disk_data();
</script>
</body>
</html>
