<?php 
include_once("lib/lib_common.php");
include_once("lib/get_config.php");

$usb = isset($_GET['usb'])?true:false;
$dir="";
$ext="";
error_log(print_r($_POST,true));
if($_POST['func'] == "usbcheck"){
    error_log("usb use : ". (isset($_GET['usb'])?"true":"false") );
    $data = "";
    if ($usb==true) {
        $data = array(
             "func" => "useusb(false)", 
             "value" => "RPi(ToDo)"
             );
        $dir= isset($_GET['dir'])? $_GET["dir"]: '/media';//$dir = ($dir=="")?'/media':$dir;
        error_log("dir is ".$dir);
    }else {
        $data =  array(
             "position" => str_ireplace("/home/pi/","",getcwd()),
             "func" => "useusb(true)",
             "value"=> "USB(ToDo)",
             "footer" => $footnote
             );
        $dir= isset($_GET['dir'])? $_GET["dir"]: '../media/video';
    }
    $ext= isset($_GET['ext'])? $_GET["ext"]: '/(\.mp4|\.mov)/i';
    outputJSON($data,"success");
}

else if($_POST['func'] == "filecheck"){
    $mcount=0;
    $tcount=0;
    $data = "";
    date_default_timezone_set('Asia/Seoul');
    $dir= isset($_GET['dir'])? $_GET["dir"]: '/media';//$dir = ($dir=="")?'/media':$dir;
    error_log("dirname----------------$dir----------------------");
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
        $fs3 = trim($fs3);
        if ($fs3 == "") continue;
        list($mtime, $file, $size, $isdir) = explode("#", $fs3);
        error_log("file-----------------$file-------------------");
        $size = (integer) ($size/1024/1024);
        
        if ($isdir == "1") {
            $options=($usb?"usb=1":"")."&dir=$dir/$file&ext=$ext";
            error_log("option-----------------$options-------------------");
            $data = array(
                 "link" =>"videolist.php?".$options,
                 "position" => $file
                 
                 );
                 outputJSON($data,"success");
            } else {
                if(preg_match($ext,$fs3,$matches)){
                    if ($usb==true) {
                        $data = array(
                            "link" => "",
                            "position" => "",
                            "id" => $file,
                            "func" => "opener.getvideo(this.id,".$dir.")",
                            "value" => $file
                            );
                            outputJSON($data,"success");
                        } else {
                            //echo $file.':::../info/'.$dir.'/'.$file.'.json<br>';
                            $json = $dir.'/../info/'.$file.'.json';
                            if (file_exists($json)) {
                                $info = json_decode(file_get_contents(
                                $dir.'/../info/'.$file.'.json'), true);
                                } else {
                                    $info = array("origin"=> basename ($file));
                                  }
                                $data = array(
                                     "id" =>  $file,
                                     "footer" => $footnote,
                                     "func" => "opener.getvideo(this.id, ''); window.close();",
                                     "info" =>  $info['origin']
                                     );
                                     outputJSON($data,"success");
                            }
                            $mcount++;
                    }
                 }
        }
}
?>

</script>
</body>
</html>
