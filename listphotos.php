<?php 

session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

$trace=true;

function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
}

$services['headnote'] = '_headnote';
function _headnote(){
	s00_log("Start ".__FUNCTION__);
	$data=array(
		"photo" => $_SESSION['photo'],
		"subject" => $_SESSION['subject'],
		"owner" => $_SESSION['owner'],
		"footer" => $_SESSION['footnote']
	);
	outputJSON($data, "success");
};

$dir= isset($_GET['dir'])? $_GET["dir"]: $_SESSION['slide'];
$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.jpg|\.png)/i';
$page= isset($_GET['page'])? $_GET["page"]: 0;	
$batch= isset($_GET['batch'])? $_GET["batch"]: 20;
	

$services['dircontent'] = '_dircontent';
function _dircontent(){
	s00_log("Start ".__FUNCTION__);
	$data = getcwd()."/[".$GLOBALS['dir']."]";
	outputJSON($data,"success");
};

$services['filecontent'] = '_filecontent';
function _filecontent(){
	s00_log("Start ".__FUNCTION__);
	$dir = $GLOBALS['dir'];
	$ext = $GLOBALS['ext'];
	$page = $GLOBALS['page'];
	$batch = $GLOBALS['batch'];
	$data = "";
	$mcount=0;
    $tcount=0;
	$arraycount = 0;
	$dcount = 0;
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
			$var = count($fs2);
			if(count($fs2) == $arraycount+1){
				$arr = array("contents"=>$data, "count"=>count($data));
				outputJSON($arr,"success");
			}
			if ($tcount < $page*$batch) { $tcount++; continue;}
			$fs3 = trim($fs3);
			if ($fs3 == "") continue;
			list($mtime, $file, $size, $isdir) = explode("#", $fs3);//echo "$file---$isdir<br>";
			
			$interval = (integer) (($time - $mtime)/60);
			$size = (integer) ($size/1024/1024);
			$mtime_s = date('Y-m-d', $mtime);
			if ($isdir == "1") {
				 $data[] = array(
				             "link" => "listphotos.html?dir=$dir/$file&ext=$ext",
						     "fileinfo" => $file,
                             "filetime" => $mtime_s
						 );
				  $dcount++;
			} else {
				//preg_match($ext,$fs3,$matches);
				//print_r($matches);
				//echo $ext;
				if(preg_match($ext,$fs3,$matches)){
					//echo "<a href=\"$dir/$file\">";
					 $data[] = array(
					             "dfile" =>"$dir/$file",
							     "link"  =>  "openimages.html?name=$dir/$file"
							 );
					$mcount++;
					if ($mcount >= $batch){
						$page++;
						$data[] = array(
						          "link" => "listphotos.html?dir=$dir&ext=$ext&page=$page"
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