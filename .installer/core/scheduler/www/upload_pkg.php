<?php
/**
 * Generates media file down loader and distributes 
 *
 * AnHive Contents Server for EBS
 *
 * LICENSE: This source file is subject to version 1.01 of the AnHive license
 * that is available through the world-wide-web at the following URI:
 * http://www.thebolle.com/license/1_0.txt.  If you did not receive a copy of
 * the AnHive License and are unable to obtain it through the web, please
 * send a note to anhive@gmail.com so we can mail you a copy immediately.
 *
 * @category   Contents Collector
 * @package    EBS_cc  
 * @author     AnHive Co., Ltd <anhive@gmail.com>
 * @copyright  2013-2015 AnHive Co., Ltd
 * @license    http://www.anhive.com/license/1_0.txt  AnHive License 1.0
 * @since      1.0
 *
 * @require    sqlite3 for apache2
 */
?>
<?php
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

function outputJSON($msg, $status = 'error'){

    if ($status == 'error') error_log ($msg." in ".__FILE__); 
    
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

function thebolle_upload($file="",
             $op=array('func'=>'upload', 'dir'=>'../archive', 'fcode'=>'upfile'), 
             $service="http://thebolle.com/hive/upload.php") {

    $curl =  "/usr/bin/curl ";
    $cont =  (isset($file) && $file != "") 
            ? (" -F\"upfile=@".$file."\"") : "";
    $post = "";
    foreach($op as $k => $v) { $post .= " -F\"$k=$v\"";   }
    
    $shell = $curl.$cont.$post.' '.$service. " 2> /dev/null"; 
    //$r = $shell."<br>";
    $r = shell_exec($shell);
    //error_log($r);
    return $r;
}

function upload () {
    $stm="";
    //'samples'
    $pkgname = (basename(getcwd())) 
             .(isset($_REQUEST['note'])&&$_REQUEST['note']!=""
             ?".".$_REQUEST['note']:"");
    $pkgname .= ".tar.gz";
    $m = getcwd();
    $m .=  "<br>"
            .shell_exec("tar -czpf $pkgname -T upload_pkg.lst"); 
    $m .=  "<br>"
            .thebolle_upload($pkgname);
    if (!isset($_REQUEST['note']) || !$_REQUEST['note']!="")         unlink($pkgname);
    
    $m .=  "<br><br>INSTALL INSTRUCTION<br>"
          ." download: <span style='color:blue;'>wget thebolle.com/archive/$pkgname -O $pkgname</span>"
          ."<br>"
          ." install : <span style='color:blue;'>sudo tar -xvzpf  $pkgname</span>"
          ."<br><br>"
          ."Created by AnHive.Co., Ltd.";        
    
    outputJSON($m, "success");
}

function change(){
    $lst = $_REQUEST['lst'];
    if (trim($lst)=="") outputJSON("<br><font color='red'>지정된 파일이 없습니다</font>", "success");
    $lst = str_replace("|","\n",$lst);
    file_put_contents("upload_pkg.lst", $lst);
    $m = file_get_contents("upload_pkg.lst");
    $m=str_replace("\n", "<br>", $m);
    outputJSON($m, "success");
}

function scans($dir, $depth){
    $m = "";
    $lst = str_replace("\r", "", file_get_contents("upload_pkg.lst"));
    $ch = array_flip(preg_split('/\n/',$lst));
    //echo print_r($ch, true);
    $files = scandir($dir);
    $space=""; 
    $path = ($dir==""||$dir==".")?"":$dir."/";
    for($i=0; $i<$depth; $i++) $space .= ">";
    foreach ($files as $f) {
        if ($f=="." || $f=="..") continue;
        if ($f==".git" || $f==".gitignore") continue;
        $v =isset($ch[$path.$f])?"V":" ";
        $m .= '<input type="button" value="'.$v.'" name="'.$path.$f.'" style="width:2em;" onclick="clicked(this, \'V\',\' \');changelist();"> '.
            $space.$path.$f."($depth)"."<br>\n";
        if (is_dir($path.$f)){
            $m .= scans($path.$f, $depth+1);
        }
    }
    return $m;
}

function pkglist() {
    $m = scans(".", 0);
    outputJSON($m, "success");
}
$mode = (isset($_REQUEST['m'])?$_REQUEST['m']:'--');
if ($mode=='up') upload();
else if ($mode=='ch') change();
else if ($mode=='lst') pkglist();

?>
<!DOCTYPE html >
<html>
<head>
   <meta charset="UTF-8">
   <title>AnHive</title>
</head>
<body style="width:100%;">


<div id="packinglist">
<?php
echo scans(".", 0);
?>
</div>

<h2>Welcome, <br><span style="color:blue;">Home Signage</span></h2>
<div id='msg' style="backgroud-colur:gray;"></div>
<div style="font-size:1.2em;">
<br>
<input type='text' id='note' value='' style="width:20em;">
<input type='button' value='PKG 등록' onclick="upload()"><br>
<input type='text' id='lst' value='' style="width:20em;">
<input type='button' value='목록수정' onclick="change()">
<input type='button' value='목록보기' onclick="getlist()">
</div>
</body>
<script>
var getlist = function () {
    
        var data = new FormData();
        data.append('m', 'lst');
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
                document.getElementById("packinglist").innerHTML = resp.data;
            }
        };
        request.open('POST', 'upload_pkg.php');
        request.send(data);
        return request;
}

var changelist = function() {
    finput = document.getElementsByTagName("input");
    val = "";
    for (var i = 0;i< finput.length; i++) {
        if (finput[i].type != 'button') continue;
        if (finput[i].value != 'V') continue;
        val += ((val.length>0)?"|":"")+finput[i].name;
    }
    document.getElementById("lst").value = val;
}
changelist();
// round robin click;
var clicked = function () {
    obj = arguments[0];
    for (var i = 1; i< arguments.length-1; i++) {
        if ( obj.value==arguments[i]) {
            obj.value = arguments[i+1];
            return;
        }
    }
    obj.value = arguments[1];
}

var change = function () {
    
        var data = new FormData();
        data.append('m', 'ch');
        changelist();
        data.append('lst', document.getElementById("lst").value);
        
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
                document.getElementById("packinglist").innerHTML = resp.data;
            }
        };
        request.open('POST', 'upload_pkg.php');
        request.send(data);
        return request;
}

var upload = function () {
    
        var data = new FormData();
        data.append('m', 'up');
        data.append('note', document.getElementById("note").value);
        
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
                    
                    console.log(e.stack);
                }
                document.getElementById("packinglist").innerHTML = resp.data;
            }
        };
        request.open('POST', 'upload_pkg.php');
        request.send(data);
        return request;
}
 
</script>

</html>