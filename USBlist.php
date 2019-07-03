<!--
/**
 * AnHive Co., Ltd.
 * LICENSE: This source file is subject to version 0.1 of the AnHive license. 
 * If you did not receive a copy of the AnHive License and are unable to obtain it
 * please send a note to anhive@gmail.com so we can mail you a copy immediately.
 *
 * @author     AnHive Co., Ltd <anhive@gmail.com>
 * @copyright  2013-2015 AnHive Co., Ltd
 * @license    http://www.anhive.com/license/1_01.txt  AnHive License 1.01
 */
-->
<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <script type="text/javascript" src="signage.base.js"></script>
    <link rel="stylesheet" type="text/css" href="signage.base.css">
    <title>USB List</title>
    <style>
.USBitem {
    color:red; 
    padding: 3px;
    padding_top: 6px;
    padding_bottom: 6px;
    margin: 2px;
    background-color: lightgray;
    word-break: break-all;
}
.USB_copyButton {
    cursor: pointer;
    display:inline-block; width: 7.8em;
    
    text-align: center;//텍스트 가운데 정렬
    
    margin: 2px;
    padding:8px 8px 8px 8px;
    
    //font-family: NG, sans-serif; 
    font-size: 1.2em;
    //font-weight: bold;
    color:white; 
    
    border: solid 1px #ddd;
    background-color:#bbb; 
    border-radius: 6px;
    border-color:#ddd #ddd #444 #444
}
    </style>
</head>
<body >
    <div class="container" >
        <div class="contents">
        
            <!-- head note -->
            <div class='headnote'>
                <span class="input_title">USB 사진 업로드</span>
            </div>
            <div style="margin-top:10px;"><!-- upper line feed --></div>
            <div class="button_base">
            <center>
            <span class="USB_copyButton" onclick="check_all()">전체 선택</span>
            <span class="USB_copyButton" onclick="window.history.back();">뒤로 가기</span>
            </center>
            <div>
<?php 

$usb = isset($_GET['usb'])?true:false;
error_log("usb use : ". (isset($_GET['usb'])?"true":"false") );

$dir="";
if ($usb==true) { // 해당 if 문 필요없음..
    $dir= isset($_GET['dir'])? $_GET["dir"]: '/media';
    error_log("dir is ".$dir);
} else {
    $dir= isset($_GET['dir'])? $_GET["dir"]: '../media/slide';
}

if(!($dir == "/media")){
                echo"<div class=\"button_base\">
            <center><span class=\"USB_copyButton\" onclick=\"usbcopy()\">USB 파일 복사</span></center>
            </div>";
}

echo '<div style="margin-top:10px;"><!-- upper line feed --></div>';

$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.jpg|\.png)/i';

date_default_timezone_set('Asia/Seoul');
$files=scandir($dir);
$fs1="";
foreach($files as $fs){
    if(substr($fs,0,1)!='.'){
        $df = $dir.'/'.$fs;
        $fs1.=filemtime($df).'#'.$fs.'#'.filesize($df).'#'.is_dir($df).'|';
    }
}
$fs2=explode("|",$fs1);
$time = time();
arsort($fs2);

foreach($fs2 as $fs3){
    $fs3 = trim($fs3);
    if ($fs3 == "") continue;
    list($mtime, $file, $size, $isdir) = explode("#", $fs3);
    // $size = (integer) ($size/1024/1024);

    if ($isdir == "1") {
        $options=($usb?"usb=1":"")."&dir=$dir/$file&ext=$ext";
        echo "<a href=\"USBlist.php?$options\">";
        echo "[DIRECTORY] $file";
        echo "</a>";
        echo "<br>";
    } else if (!($dir=="/media")) { // /media에 있는 사진 파일들을 읽지 않음.
        if(preg_match($ext,$fs3,$matches)){
        echo "<div class=\"USBitem\">
            <form action='http://192.168.201.1/signage/USBlist.php?usb' method='post'>
            <input type='checkbox' name='files' id='file' value='$file'>$file<br>
            <input type='hidden' id='dir' value='$dir'>
            </div>
            ";
        }
    }
}

?>
    </div>
    <div id="class" class='footer' ><?=$footnote?></div>

<script>

var all_not=0;  
function check_all() {//checkbox 전체선택
  checkboxes = document.getElementsByName('files');
  if(all_not==0){
        for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = true;
        all_not=1;
        }
  }
  else{
        for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = false;
        all_not=0;
        }
 }
}

var usbcopy = function () {
    copyFiles = document.getElementsByName("files"); //files 라는 name을 가지고옴
    var noSelectFile = false;
    var noCheckedFile = 0;
        var data = new FormData();
        data.append('func', 'fileCopy');
        data.append('directory', document.getElementById("dir").value);
        data.append('length', copyFiles.length);
        for(i=0;i<copyFiles.length;i++){
        
        if(copyFiles[i].checked==true){
            
            data.append('fileSearch' + i, copyFiles[i].value);
            console.log('fileSearch' + i);
            
            }
        else if(copyFiles[i].checked==false){
                noCheckedFile++
            if(noCheckedFile==copyFiles.length){ // 전체 파일의 갯수와 선택하지 않은 파일들이 같을 때,
                alert("파일을 선택해주세요.");
                noSelectFile = true;
            }
        }
    }

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
            if(noSelectFile==false){//파일을 선택하지 않을시 복사 완료 메세지 x
        //php(서버)에서 데이터를 받음
        console.log(resp.data);
        opener.location.reload(); //부모 창 리로드 ( 새로고침 )
        }
    }
};
    request.open('POST', 's00_signage.php');
    request.send(data);
}    
</script>
</body>
</html>