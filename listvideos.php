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
    <title>AnHive, List Media Files</title>
</head>
<body >
    <div class="container">
        <div class="contents">
		
			<!-- head note -->
			<div class='headnote' onclick="javascript:document.location.href='home.php'">
				<img src="<?=$photo?>" alt="profile" style="height:15px;">
				<span class="input_title"><?=$subject?></span> 
				<span class="input_title"><?=$owner?></span>
			</div>
			<div style="margin-top:10px;"><!-- upper line feed --></div>

<?php 
$dir= isset($_GET['dir'])? $_GET["dir"]: '../media/video';
$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.mp4|\.mov)/i';
$page= isset($_GET['page'])? $_GET["page"]: 0;
$batch= isset($_GET['batch'])? $_GET["batch"]: 10;

echo "<div>현재 path:[".getcwd()."] </div>";
echo "<a href=uploadphoto.html>Upload to path[".$dir."]</a><br>";

$mcount=0;
$tcount=0;
date_default_timezone_set('Asia/Seoul');
$files=scandir($dir);
$fs1="";
foreach($files as $fs){
    if(($fs!='.')&&($fs!='..')
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

    if ($tcount < $page*$batch) { $tcount++; continue;}

    $fs3 = trim($fs3);
    if ($fs3 == "") continue;
    list($mtime, $file, $size, $isdir) = explode("#", $fs3);
	//echo "$file---$isdir<br>";

    $interval = (integer) (($time - $mtime)/60);
    $size = (integer) ($size/1024/1024);
    $mtime_s = date('Y-m-d', $mtime);

    if ($isdir == "1") {
        echo "<a href=\"listvideos.php?dir=$dir/$file&ext=$ext\">";
        echo "[DIRECTORY] $file, $mtime_s";
        echo "</a>";
        echo "<br>";
    } else {
        //preg_match($ext,$fs3,$matches);
        //print_r($matches);
        //echo $ext;
        if(preg_match($ext,$fs3,$matches)){
            //echo "<a href=\"$dir/$file\">";
			//echo "<img src=\"$dir/$file.png\" style=\"width:80px;\">";
            echo "<a href=\"openvideo.php?name=$dir/$file\">";
            echo "[FILE] $file,  $size MB, ";
            if ($interval < 60) {
               echo $interval.' minutes ago';
            } elseif ( $interval < 1440 ) {
               echo ((integer) ($interval/60)).' hours';
            } elseif ( $interval < 43200 ) {
               echo ((integer) ($interval/1440)).' days';
            } else {
               echo ((integer) ($interval/43200)).' months';
            }
            echo "</a>";
			
            echo " <input type=\"button\" onclick=\"udelete('$dir/$file');\" value=\"삭제\">";	
	
            echo "<br>";
            $mcount++;
            if ($mcount >= $batch)
            { 
               $page++;
               echo "<a href=listvideos.php?dir=$dir&ext=$ext&page=$page>more videos</a>";
               break; 
            }
        } 
    }
}
?>
	</div>
	<div class='footer' ><?=$footnote?></div>

<script>
var udelete = function(fname){

    var data = new FormData();
    data.append('func', 'delete');
    data.append('name', fname);
    data.append('dir', "");
    var req = new XMLHttpRequest();
	
	console.log("---");

    req.onreadystatechange = function(){
        if(req.readyState == 4){
			try {
				var resp = JSON.parse(req.response);
			} catch (e){
				var resp = {status: 'error',data: 'Unknown error occurred: [' + req.responseText + ']'};
			}
			console.log(resp.status + ': ' + resp.data);
			location.reload();
	}};

    req.open('POST', 's00_file.php');
    req.send(data);
}
</script>
</body>
</html>