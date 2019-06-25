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
$dir= isset($_GET['dir'])? $_GET["dir"]: $config_slide;
$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.jpg|\.png)/i';
$page= isset($_GET['page'])? $_GET["page"]: 0;
$batch= isset($_GET['batch'])? $_GET["batch"]: 20;
echo "현재 directory".getcwd()."/[".$dir."]<br><br>";
?>
    
<?php 
$mcount=0;
$tcount=0;
date_default_timezone_set('Asia/Seoul');
$files=scandir($dir);
$fs1="";
foreach($files as $fs){
    if(($fs!='.')&&($fs!='..')
       &&($fs!='js')&&($fs!='css')
       &&($fs!='images')&&($fs!='img')&&($fs!='captions')){
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
        echo "<a href=\"listphotos.php?dir=$dir/$file&ext=$ext\">";
        echo "[DIRECTORY] $file, $mtime_s";
        echo "</a>";
        echo "<br>";
    } else {
        //preg_match($ext,$fs3,$matches);
        //print_r($matches);
        //echo $ext;
        if(preg_match($ext,$fs3,$matches)){
            //echo "<a href=\"$dir/$file\">";
			echo "<img src=\"$dir/$file\" style=\"height:80px;\" onclick=\"window.open('openimages.html?name=$dir/$file', '_self');\"> ";

            $mcount++;
            if ($mcount >= $batch)
            { 
               $page++;
               echo "<a href=listphotos.php?dir=$dir&ext=$ext&page=$page >More videos</a>";
               break; 
            }
        } 
    }
}
?>
	</div>
	<div class='footer' ><br><?=$footnote?></div>

</body>
</html>