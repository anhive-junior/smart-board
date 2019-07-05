<?php
session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

$dir= isset($_GET['dir'])? $_GET["dir"]: '../media/video';
$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.mp4|\.mov)/i';
$page= isset($_GET['page'])? $_GET["page"]: 0;
$batch= isset($_GET['batch'])? $_GET["batch"]: 10;

$services['headnote'] = '_headnote';
function _headnote(){
    $data=array(
        "photo" => $_SESSION['photo'],
        "subject" => $_SESSION['subject'],
        "owner" => $_SESSION['owner'],
        "footer" => $_SESSION['footnote']
    );
    outputJSON($data, "success");
}

$services['pathcheck'] = '_pathcheck';
function _pathcheck(){
	$data = array(
	         "path" => getcwd(),
			 "func" => "uploadphoto.html",
			 "directory" => $GLOBALS['dir']
			 );
    outputJSON($data,"success");
}



$services['fcheck'] = '_fcheck';
function _fcheck(){
	$dir=$GLOBALS['dir'];
    $ext=$GLOBALS['ext'];
    $page=$GLOBALS['page'];
    $batch=$GLOBALS['batch'];
	$arraycount = 0;
    $mcount=0;
    $tcount=0;
	$ftime="";
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
		
        if(count($fs2) == $arraycount + 1){
			
			$arr = array("contents"=>$data, "count"=>count($data));
			outputJSON($arr,"success");
		}
        if ($tcount < $page*$batch) { $tcount++; continue;}

        $fs3 = trim($fs3);
        if ($fs3 == "") continue;
        list($mtime, $file, $size, $isdir) = explode("#", $fs3);
        //echo "$file---$isdir<br>";

        $interval = (integer) (($time - $mtime)/60);
        $size = (integer) ($size/1024/1024);
        $mtime_s = date('Y-m-d', $mtime);
        $mtime_s = date('Y-m-d', $mtime);

        if ($isdir == "1") {
			$data[] = array(
			            "isdir" => "0",
			            "link" => "listvideos.html?dir=$dir/$file&ext=$ext",
						"fileinfo" => $file,
						"filetime" => $mtime_s
						);
        } else {
            //preg_match($ext,$fs3,$matches);
            //print_r($matches);
            //echo $ext;
            if(preg_match($ext,$fs3,$matches)){
                //echo "<a href=\"$dir/$file\">";
                //echo "<img src=\"$dir/$file.png\" style=\"width:80px;\">";
                if ($interval < 60) {
                    $ftime = $interval.' minutes ago';
							  
                } elseif ( $interval < 1440 ) {
				      $ftime = ((integer) ($interval/60)).' hours';
							   
                } elseif ( $interval < 43200 ) {
				      $ftime = ((integer) ($interval/1440)).' days';
								
                } else {
				      $ftime = ((integer) ($interval/43200)).' months';
								
                }
                    
                $data[] = array(
				            "link" => "openvideo.html?name=$dir/$file",
							"func" => "udelete('$dir/$file')",
							"fileinfo" => $file,
							"filesize" => $size,
							"ftime" => $ftime
							); 
                $mcount++;
                if ($mcount >= $batch)
                { 
                   $page++;
				   $data[] = array(
				              "link" => "listvideos.html?dir=$dir&ext=$ext&page=$page",
							  "ftime" => $ftime
							  
							  );
                
                   break; 
                }
            } 
        }
		$arraycount++;
    }
}

$func= isset($_POST['func'])?$_POST["func"]:"test";

if (!isset($services[$func])) 
        outputJSON("Undefined service[$func].");
try {
    call_user_func( $services[$func]);
    //s00_log2(4, print_r($services,true));
} catch (Exception $e) {
    outputJSON($e->getLine().'@'.__FILE__."\n".$e->getMessage());
    s00_log(print_r($e->getTrace(),true));
}
?>