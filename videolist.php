<?php 
include_once("lib/lib_common.php");
include_once("lib/get_config.php");
session_start();

error_log(print_r($_POST,true));

$services['usbcheck'] = '_usbcheck';
function _usbcheck(){
	$usb = isset($_GET['usb'])?true:false;
    error_log("usb use : ". (isset($_GET['usb'])?"true":"false") );
    $data = "";
    if ($usb==true) {
        $data = array(
             "func" => "useusb(false)", 
             "value" => "RPi(ToDo)"
             );
        $_SESSION['dir']= isset($_GET['dir'])? $_GET["dir"]: '/media';//$dir = ($dir=="")?'/media':$dir;
        error_log("dir is ".$dir);
    }else {
        $data =  array(
             "position" => str_ireplace("/home/pi/","",getcwd()),
             "func" => "useusb(true)",
             "value"=> "USB(ToDo)",
             "footer" => $footnote
             );
        $_SESSION['dir']= isset($_GET['dir'])? $_GET["dir"]: '../media/video';
    }
 
    outputJSON($data,"success");
}

$services['filecheck'] = '_filecheck';
function _filecheck(){
	$usb = isset($_GET['usb'])?true:false;
	$ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.mp4|\.mov)/i';
    $mcount=0;
    $tcount=0;
    $data = "";
	$arraycount=0;
    date_default_timezone_set('Asia/Seoul');
    $dir= $_SESSION['dir'];
    $files=scandir($dir);
    $fs1="";
    foreach($files as $fs){
        if((substr($fs,0,1)!='.')&&($fs!='.')&&($fs!='..')&&($fs!='js')&&($fs!='css')&&($fs!='images')&&($fs!='img')){
            $df = $dir.'/'.$fs;
            $fs1.=filemtime($df).'#'.$fs.'#'.filesize($df).'#'.is_dir($df).'|';
            }
    }
    $fs2=explode("|",$fs1);
    $time = time();
    arsort($fs2);
    foreach($fs2 as $fs3){
        
        //if ($tcount < $page*$batch) { $tcount++; continue;}
		if(count($fs2) == $arraycount+1){
			$arr = array("contents"=>$data, "count"=>count($data));
			outputJSON($arr,"success");
		}
        $fs3 = trim($fs3);
        if ($fs3 == "") continue;
        list($mtime, $file, $size, $isdir) = explode("#", $fs3);
       
        $size = (integer) ($size/1024/1024);
        
        if ($isdir == "1") {
            $options=($usb?"usb=1":"")."&dir=$dir/$file&ext=$ext";
            $data[] = array(
                 "link" =>"videolist.html?".$options,
                 "position" => $file
                 );
               ;
            } else {
                if(preg_match($ext,$fs3,$matches)){
                    if ($usb==true) {
                        $data[] = array(
                            "id" => $file,
                            "func" => "opener.getvideo(this.id,".$dir.")",
                            "value" => $file
                            );
                         
                        } else {
                            //echo $file.':::../info/'.$dir.'/'.$file.'.json<br>';
                            $json = $dir.'/../info/'.$file.'.json';
                            if (file_exists($json)) {
                                $info = json_decode(file_get_contents(
                                $dir.'/../info/'.$file.'.json'), true);
                                } else {
                                    $info = array("origin"=> basename ($file));
                                  }
                                $data[] = array(
                                     "id" =>  $file,
                                     "func" => "opener.getvideo(this.id, '')",
                                     "info" =>  $info['origin']
                                     );
                                     
                            }
                            $mcount++;
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

</script>
</body>
</html>
