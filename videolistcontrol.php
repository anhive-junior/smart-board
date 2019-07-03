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
    <title>Video List</title>
    <style>
.videoitem {
    color:red; 
    padding: 3px;
    padding_top: 6px;
    padding_bottom: 6px;
    margin: 2px;
    background-color: lightgray;
    word-break: break-all;
}
    </style>
</head>
<body >
    <div class="container" >
        <div class="contents">
        
            <!-- head note -->
            <div class='headnote'>
                <span class="input_title">비디오목록</span>
            </div>
            <div style="margin-top:10px;"><!-- upper line feed --></div>

<?php 

$usb = isset($_GET['usb'])?true:false;
error_log("usb use : ". (isset($_GET['usb'])?"true":"false") );

$dir="";
if ($usb==true) {
    echo "<div>위치:[USB] or <input type='button' id='usb' onclick='useusb(false);' value='RPi(ToDo)'></div><br>";

    $dir= isset($_GET['dir'])? $_GET["dir"]: '/media';
    //$dir = ($dir=="")?'/media':$dir;
    error_log("dir is ".$dir);
    
} else {    
    echo "<div>위치:[".str_ireplace("/home/pi/","",getcwd())."] or <input type='button' id='usb' onclick='useusb(true);' value='USB(ToDo)'></div><br>";
    
    $dir= isset($_GET['dir'])? $_GET["dir"]: '../media/video';
}
$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.mp4|\.mov)/i';

$mcount=0;
$tcount=0;
date_default_timezone_set('Asia/Seoul');
$files=scandir($dir);
$fs1="";
foreach($files as $fs){
    if((substr($fs,0,1)!='.')&&($fs!='.')&&($fs!='..')
       &&($fs!='js')&&($fs!='css')
       &&($fs!='images')&&($fs!='img')){
        $df = $dir.'/'.$fs;
        $fs1.=filemtime($df).'#'.$fs.'#'.filesize($df).'#'.is_dir($df).'|';
    }
}
$fs2=explode("|",$fs1);
$time = time();
arsort($fs2);

foreach($fs2 as $fs3){

    //if ($tcount < $page*$batch) { $tcount++; continue;}

    $fs3 = trim($fs3);
    if ($fs3 == "") continue;
    list($mtime, $file, $size, $isdir) = explode("#", $fs3);

    $size = (integer) ($size/1024/1024);

    if ($isdir == "1") {
        $options=($usb?"usb=1":"")."&dir=$dir/$file&ext=$ext";
        echo "<a href=\"videolist.php?$options\">";
        echo "[DIRECTORY] $file";
        echo "</a>";
        echo "<br>";
    } else {
        if(preg_match($ext,$fs3,$matches)){
            
            if ($usb==true) {
                echo "<div class=\"videoitem\" id=\"".$file."\" onclick=\"opener.getvideo(this.id, '$dir');window.close();\"> ".$file."</div>" ;
            } else {
                //echo $file.':::../info/'.$dir.'/'.$file.'.json<br>';
                $json = $dir.'/../info/'.$file.'.json';
                if (file_exists($json)) {
                    $info = json_decode(file_get_contents(
                                $dir.'/../info/'.$file.'.json'), true);
                } else {
                    $info = array("origin"=> basename ($file));
                }
                    
                echo "<div class=\"videoitem\" id=\"".$file."\" onclick=\"opener.getvideo(this.id, '');window.close();\"> ".$info['origin']."</div>" ;
            }
            
            $mcount++;
        }
    }
}
?>
    </div>
    <div class='footer' ><?=$footnote?></div>
<script>
var useusb = function (usb) {
    opener.showvideos(usb);
}
</script>
</body>
</html>
