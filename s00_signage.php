<?php 
/**

Digital Signage
Copyright 2016 @ AnHive Co., Ltd.

*/
session_start(); 
include_once("lib/get_config.php");
include_once("lib/get_resource.php");
include_once("lib/lib_common.php");


ini_set('max_execution_time', 3600);//?

$trace=true;

function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
}

/////////////////////////////////////
// null service
$services['test'] = '_test';
function _test() { 
    s00_log ("Start ".__FUNCTION__);
    throw new Exception ( __FILE__.'is Available.');
};
/////////////////////////////////////
// command to specific channel to control
function submit_RPi($msg) { 
    s00_log ("Start ".__FUNCTION__);

    if ($msg == "") return "";
    
    $host    = "127.0.0.1";
    $port    = file_get_contents("/etc/hive/.config/rinput.port");
    $message = $msg;
    //echo "Message To server :".$message;
    // create socket
    $socket = socket_create(AF_INET, SOCK_STREAM, 0) 
        or die("Could not create socket\n");
    // connect to server
    $result = socket_connect($socket, $host, $port) 
        or die("Could not connect to server[$socket]\n");  
    // send string to server
    socket_write($socket, $message, strlen($message)) 
        or die("Could not send data to server\n");
    // get server response
    $result = socket_read ($socket, 1024) 
        or die("Could not read server response\n");

    // close socket
    socket_close($socket);
    
    return $result;
}
/////////////////////////////////////
// submit control message on OpenBox of RPi
$services['command'] = '_command';
function _command() { 
    s00_log ("Start ".__FUNCTION__);

    $msg = $_POST['message'];
    $r = submit_RPi($msg);
    
    outputJSON("[$msg] proceed.", 'success');
};

/////////////////////////////////////
// control to slide show on running
$services['control'] = '_control';
function _control() { 
    s00_log ("Start ".__FUNCTION__);

    $ctrl = $_POST['ctrl'];
    
    $msg = getcommand($ctrl);
    if ($msg=="") outputJSON("[$ctrl] is not defined.");
    
    $r = submit_RPi($msg);
    
    outputJSON("[$ctrl] proceed", 'success');
};

/////////////////////////////////////
// command lists for control feh
function getcommand($ctrl) {
    s00_log ("Start ".__FUNCTION__);
    s00_log ("message [".$ctrl."]");

    //$msg["restart"]="q{USLEEP 1000000}[ENTER]<LEFTMETA_DOWN><LEFTMETA_HOLD>s<LEFTMETA_UP>";
    $msg["restart"]="[ENTER]<LEFTCTRL_DOWN><LEFTCTRL_HOLD><LEFTALT_DOWN><LEFTALT_HOLD>f<LEFTALT_UP><LEFTCTRL_UP>";
    
    
    $msg["previous"]="p";
    $msg["playhold"]="h";
    $msg["next"]="n";
    $msg["rotate_left"]="<LEFTSHIFT_DOWN>,";
    $msg["rotate_right"]=">";
    $msg["flipped"]="_";
    $msg["mirrored"]="|";
    $msg["larger"]="[UP]";
    $msg["smaller"]="[DOWN]";
    $msg["fittosize"]="[KPSLASH]";
    $msg["100p"]="[KPASTERISK]";
    $msg["upward"]="[KP8]";
    $msg["downward"]="[KP2]";
    $msg["leftward"]="[KP4]";
    $msg["rightward"]="[KP6]";

    return (isset($msg[$ctrl]))?$msg[$ctrl]:"";
}

/////////////////////////////////////
// set slide show options of feh
$services['setfeh'] = '_setfeh';
function _setfeh() { 
    s00_log ("Start ".__FUNCTION__);

    $fname="/etc/hive/signage/feh.sh";
    $msg = $_POST['message'];
    file_put_contents($fname, $msg);
    @chmod($fname, 0775);
    
    outputJSON("[$msg] proceed", 'success');
};

/////////////////////////////////////
// read slide show options
$services['getfeh'] = '_getfeh';
function _getfeh() { 
    s00_log ("Start ".__FUNCTION__);

    $fname="/etc/hive/signage/feh.sh";
    $msg = file_get_contents($fname);
    
    outputJSON($msg, 'success');
};

///////////////////////////////////////
// pick one from time ordering slide
function getfile($dir, $basephoto, $index) {

    date_default_timezone_set('Asia/Seoul');
    $files=scandir($dir);
    if (sizeof($files) ==0) return "";
    
    $fs1="";
    foreach($files as $fs){
        if (strpos(".;..;js;css;images;img;", $fs)!==false) continue;
        if (strpos("captions;filelist", $fs)!==false) continue;
        if (substr($fs,0,1)==".") continue;

        $df = $dir.'/'.$fs;
        $fs1.=filemtime($df).'#'.$fs
              .'#'.filesize($df).'#'.is_dir($df).'|';
        #error_log("=======".$df);
    }
    if ($fs1 == "") return "";
    
    $fs2=explode("|",$fs1);
    arsort($fs2);

    $searched = "";
    $firstfile = "";
    $lastfile = "";
    $nextphoto = "";
    $countdown = 2; // for prevent infinite loof
    while($countdown--) {
        foreach($fs2 as $fs3){

            $fs3 = trim($fs3);
            if ($fs3 == "") continue;
            list($mtime, $file, $size, $isdir) = explode("#", $fs3);
            
            //$mtime = date('Y-m-d', $mtime);
            if ($isdir == "1") continue;

            $df = $dir.'/'.$file;
            
            $fx = strtolower(pathinfo($df, PATHINFO_EXTENSION));
            if (strpos("jpg;gif;png;mp4;mov;mkv", $fx)===false) continue;

            //if matched
            //error_log(str_replace($dir.'/', "", ">>>b[$basephoto]$index:::c[$df]:::f[$firstfile],l[$lastfile],s[$searched]<<<" ));
            switch ($index) {
                case "current":
                    return $basephoto;
                case "previous":
                    //if not matched 
                    if ( $firstfile == "" && $basephoto == $file ) 
                        $index = "last";
                    elseif ( $basephoto == $file)
                        return $searched;
                    break;
                case "next":
                    if ( $basephoto == $file ) $index = "next_r";
                    break;
                case "first":
                    if ( $firstfile == "" )  return $file;
                    break;
                case "next_r":
                    return $file;
                case "last":
                    if ( $lastfile != "" ) return $lastfile;
                    break;
                default:
                    outputJSON("Undefined action work");
            }
            if ($firstfile =="") $firstfile = $file;
            $searched = $file;
        }
        $lastfile = $searched;
    }
    return "";
}

/////////////////////////////////////
// set slide position and caption
$services['getslide'] = '_getslide';
function _getslide() { 
    s00_log ("Start ".__FUNCTION__);

    global $config_playlist, $config_caption, $config_slide, 
           $config_info,$config_thumbs, $config_playlink ;

    $base = isset($_POST['photo'])?$_POST['photo']:"";
    $action = isset($_POST['action'])?$_POST['action']:"";
    //error_log("photo action [$action]");
    
    $info = "";
    error_log($config_playlist);
    $photo = getfile($config_playlist, $base, $action);
    error_log("[$action] of [$base] is [$photo]");
    if ($photo=="") {
        error_log("Set the first sample.");
        $photo="sample.jpg";
        $custom_path = 'custom/'.$_SESSION['profile'];
        copy ("$custom_path/sample_slide.jpg",      "$config_slide/$photo");
        copy ("$custom_path/sample_slide.jpg.json", "$config_info/$photo.json");
        make_thumb_from_image("$config_slide/sample.jpg", 
                        "$config_thumbs/$photo.png", 64,64);
        $info = json_decode(file_get_contents("$config_info/$photo.json"),true);
        $cfile = "$config_caption/$photo.txt";
        file_put_contents($cfile, $info['caption']);
        symlink( "$config_playlink/$photo", 
                    "$config_playlist/$photo"); 
        touch($cfile, $info['time']);
        symlink( "$config_playlink/$photo", 
                    "$config_playlist/$photo"); 
    }
   
    error_log($_SERVER['DOCUMENT_ROOT']); 
    error_log(__DIR__."||".__FILE__); 
    $caption = "sample 입니다";
    $url = $config_playlist.'/'.$photo;
    $cfile = $config_caption.'/'.$photo.'.txt';
    $caption = file_exists($cfile)?
                file_get_contents($cfile):$photo;

    error_log(realpath($cfile)); 


    date_default_timezone_set('Asia/Seoul');
    $mtime = date('Y-m-d H:m:s', filemtime($cfile));
    
    $arr = array (
        "photo" => $photo
      , "caption" => $caption
      , "mtime" => $mtime
      , "url" => $url
      , "server_ip" => $_SERVER['HTTP_HOST'] . substr($url, 2)
	  , "test" => $_SERVER['HTTP_HOST'].substr($url,2)
      );
    
    outputJSON($arr, 'success');
};

/////////////////////////////////////
// set slide position
$services['setslide'] = '_setslide';
function _setslide() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_slide;

    $slide = isset($_POST['slide'])?$_POST['slide']:"current";
    $photo = isset($_POST['photo'])?$_POST['photo']:"";
    $action = isset($_POST['action'])?$_POST['action']:"";

    $cmd = "";
    error_log("photo [$photo] will be [$action]");
    $photo_addr = $_SESSION['slide'].'/'.$photo;

    if (strpos($action, "rotate_left") !== false) {
        $cmd = "convert $photo_addr -rotate -90 $photo_addr";
    } else 
    if (strpos($action, "rotate_right") !== false) {
        $cmd = "convert $photo_addr -rotate 90 $photo_addr";
    } else 
    if (strpos($action, "flipped") !== false) {
        $cmd = "convert $photo_addr -flip $photo_addr";
    } else 
    if (strpos($action, "mirrored") !== false) {
        $cmd = "convert $photo_addr -flop $photo_addr";
    } else {
        outputJSON("Undefined work");
    }
    $mtime = filemtime($photo_addr);
    //error_log("$cmd at $mtime");
    $r = shell_exec($cmd);
    touch($photo_addr, $mtime);
    //error_log("result : [$r]");

    outputJSON($action.":".$photo, 'success');
};

/////////////////////////////////////
// set slide caption
$services['setcaption'] = '_setcaption';
function _setcaption() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_caption;

    $slide = isset($_POST['slide'])?$_POST['slide']:"current";
    $photo = isset($_POST['photo'])?$_POST['photo']:"";
    $caption = isset($_POST['caption'])?$_POST['caption']:"";

    if ($photo == "") outputJSON("File is not defined");
    error_log("file path:[$photo]");
    $cfile = $config_caption.'/'.basename($photo).".txt";
    file_put_contents($cfile, $caption);
    
    outputJSON($caption, 'success');
};

function get_userindex(){
    if (isset($_SESSION['user_md5'])) return $_SESSION['user_md5'];
    error_log("user is [".$_SESSION['user']);
    $user_md5 = md5 ($_SESSION['user']);
    //$user_md5 = substr(base_convert($user_md5, 16,32), 0, 12);
    //$user_md5 = substr(base_convert($user_md5, 16,32),0,12);
    $_SESSION['user_md5'] = $user_md5;
    return $user_md5;
}

function get_photomd5($file) {
        $photo_md5 = md5_file ($file);
        error_log("md5-16: $photo_md5");
        $photo_md5 = substr(base_convert($photo_md5, 16,32), 0, 12);
        //$photo_md5 = substr(base_convert($photo_md5, 16,32), 0, 12);
        return $photo_md5;
}

function get_videomd5($file) {
        $video_md5 = md5_file ($file);
        error_log("md5-16: $video_md5");
        $video_md5 = substr(base_convert($video_md5, 16,32), 0, 12);
        //$video_md5 = substr(base_convert($video_md5, 16,32), 0, 12);
        return $video_md5;
}


/////////////////////////////////////
// set slide caption
$services['sendcard'] = '_sendcard';
function _sendcard() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_slide, $config_playlist, $config_playlink, $config_caption, $config_thumbs, $config_info;
    // Upload file
    // Check for errors
    //prevent duplication
    $actionlog = "/run/shm/lastaction/".session_id ();
    $currentcard = md5(print_r($_POST, true));
    $lastcard = file_exists($actionlog)?file_get_contents($actionlog):"none";
    if ($currentcard == $lastcard) 
        if (time() - filemtime($actionlog)  < 2)
            outputJSON('Not allowed double actions');
    if (!file_exists($actionlog)) mkdir(dirname($actionlog), 0766, true);
    file_put_contents($actionlog, $currentcard);

    
    if(!isset($_FILES['card'])){
        outputJSON('File is not defined.');
    }
    //
    if($_FILES['card']['error'] > 0){
        outputJSON('An error occurred when uploading.');
    }
    
    $dfs = disk_free_space(".");
    if (($dfs - $_FILES['card']['size']) < 1048576 * $_SESSION['reserve_space']) 
    {
        outputJSON('Not enough disk free space.');
    }
    
    error_log($_SESSION['reserve_space'] . "MB is space remains ::::::::::::::::");
    
    // Check filesize
    if($_FILES['card']['size'] > 2000000000){
        outputJSON('File uploaded exceeds maximum upload size.');
    }

    $file = $_FILES["card"]["name"];
    $file = str_replace(" ","",$file);
    
    $fx = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($fx == "jpeg") $fx = "jpg"; 
    $photo_md5 = get_photomd5($_FILES['card']['tmp_name']);
    $photo_md5_name = "$photo_md5.$fx";
    
    error_log("file name [$photo_md5_name]");
    
    if(!move_uploaded_file($_FILES['card']['tmp_name'], 
            "$config_slide/". $photo_md5_name)){
        outputJSON('Error uploading file - check destination is writeable.');
    }
    
    if (!file_exists("$config_slide/". $photo_md5_name)) 
        error_log("$config_slide/". $photo_md5_name." is not SAVED");
    // 원본 파일 권한 설정 775
    chmod("$config_slide/".$photo_md5_name, 0775);    
    symlink( "$config_playlink/$photo_md5_name", 
                "$config_playlist/$photo_md5_name"); 
    error_log( "SLIDE: [$config_slide/$photo_md5_name], PLAYLIST[$config_playlist/$photo_md5_name]"); 
    
    $caption = isset($_POST['caption'])?$_POST['caption']:"";
    $cfile = "$config_caption/$photo_md5_name.txt";
    file_put_contents($cfile, $caption);
    make_thumb_from_image("$config_slide/$photo_md5_name", 
                "$config_thumbs/$photo_md5_name.png", 64,64);

    //set information
    $json_file = $config_info.'/'.$photo_md5_name.".json";
    $info = array("caption"=> $caption, 
                  "time" => time(),
                  "photo_md5"=>$photo_md5,
                  "user_md5"=>get_userindex(),
                  "thumbs"=>"thumbs",
                  "origin"=>$_FILES["card"]["name"]);        
    file_put_contents($json_file, json_encode($info));                
                
    if (! (isset($_POST['test']) && ($_POST['test']== "on") ) )
        submit_RPi(getcommand("restart"));
    
    // Success!
    outputJSON(array("msg" => "[$file] 등록완료", "file"=>$file, "photo"=>$photo_md5_name), 'success');
}    

/////////////////////////////////////
// set slide caption
$services['rmcard'] = '_rmcard';
function _rmcard() {
    if(!($_SESSION['uselevel']>1)) die("잘못된 접근입니다."); 
    global $config_slide, $config_playlist, $config_thumbs, $config_info, $config_captions;
    $rm_list=$_POST["rm_list"];
    if($rm_list == true){
        s00_log ("Start ".__FUNCTION__." - list ");
        $lst = $_REQUEST['lst'];
        error_log("rmlist :" .$lst);
        $lst = str_replace("|","\n",$lst);
        $list = preg_split('/\n/',$lst);
        
        foreach($list as $lst){
            $cfile = "$config_captions/".basename($lst).".txt";
            $pfile = "$config_playlist/".basename($lst);
            $sfile = "$config_slide/".basename($lst);
            $tfile = "$config_thumbs/".basename($lst).".png";
            $ifile = "$config_info/".basename($lst).".json";
            
            if (file_exists($tfile)) unlink($tfile);
            if (file_exists($cfile)) unlink($cfile);
            if (file_exists($pfile)) unlink($pfile);
            if (file_exists($ifile)) unlink($ifile);
            if (file_exists($sfile)) unlink($sfile);
        }
        // Success!
        outputJSON("Removed card : [checked card].", 'success');    
    }
    else{
        s00_log ("Start ".__FUNCTION__);
        // Upload file
        // Check for errors
        $card = isset($_POST['card'])?$_POST['card']:"";
        if ($card=="") outputJSON("File name is missing!");
        $cfile = "$config_captions/".basename($card).".txt";
        $pfile = "$config_playlist/".basename($card);
        $sfile = "$config_slide/".basename($card);
        $tfile = "$config_thumbs/".basename($card).".png";
        $ifile = "$config_info/".basename($card).".json";

        if (file_exists($tfile)) unlink($tfile);
        if (file_exists($cfile)) unlink($cfile);
        if (file_exists($pfile)) unlink($pfile);
        if (file_exists($ifile)) unlink($ifile);
        if (file_exists($sfile)) unlink($sfile);
        
        // Success!
        outputJSON("Removed card : [$card].", 'success');    
    }
}    

/////////////////////////////////////
// set slide caption
$services['exclude'] = '_exclude';
function _exclude() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_slide, $config_playlist, $config_thumbs;
    // Upload file
    // Check for errors
    $card = isset($_POST['card'])?$_POST['card']:"";
    if ($card=="") outputJSON("File name is missing!");
    $pfile = "$config_playlist/".basename($card);

    if (file_exists($pfile)) unlink($pfile);
    
    // Success!
    outputJSON("Exclude card : [$card].", 'success');
}    


//////////////////////////////////////////////////
// get receive info
$services['system'] = '_system';
function _system() { 
    s00_log ("Start ".__FUNCTION__);
    
    $rt = check_accesscode($_POST['code']);
    if ( $rt != "success") outputJSON($rt, 'success');

    $mode = $_POST['mode'];

    if ($mode == 'reset') {
        sudo_exec("reboot");
    } else if ($mode == 'shutdown') {
        sudo_exec("shutodown -h 0");
    }
    
    outputJSON("$mode proceed", 'success');
};

//////////////////////////////////////////////////
// get receive info
function make_thumb_from_image($file, $thumb, $t_width,$t_height) 
{
  $source_image = imagecreatefromstring(file_get_contents($file)); //파일읽기
  $width = imagesx($source_image);
  $height = imagesy($source_image);

  $virtual_image = imagecreatetruecolor($t_width, $t_height); //가상 이미지 만들기

  imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $t_width, $t_height, $width, $height); //사이즈 변경하여 복사
  imagepng($virtual_image, $thumb); // png파일로 썸네일 생성
}

//////////////////////////////////////////////////
// get receive info
$services['buildthumbs'] = '_buildthumbs';
function _buildthumbs() {
    error_log ("Start ".__FUNCTION__);
    
    global $config_slide, $config_thumbs;
    
    $lst = $_REQUEST['lst'];
    if ($lst != "") {
        error_log("playlist :" .$lst);
        $lst = str_replace("|","\n",$lst);
        $fn = preg_split('/\n/',$lst);
    } else {
        $fn = scans("", 0, $config_slide);
    }
    
    $cnt = 0;
    foreach($fn as $f){
        //error_log("config_thumbs ..[$config_thumbs]... $f");
        $slide = $config_slide.'/'.$f;
        $thumb = $config_thumbs.'/'.$f.".png";

        if ( ( file_exists($thumb) )
            && ( filemtime($thumb) > filemtime($slide) 
            && ( filesize($thumb) > 0 ) ) ) {
                //error_log("slide: $slide @".filemtime($slide)."thumg: $thumb @".filemtime($thumb)."" );
                continue;
            }
        
        //$g = getimagesize($slide); 
        //error_log (print_r($g, true));
        //$i = is_array($g); 
        //error_log($i."---".$g[3]);
        //if(!$i) continue;
        //if ( ($lst == "") && file_exists($thumb)) continue;
        
        make_thumb_from_image($slide, $thumb, 64,64);
        $cnt++;
        //break;
    }
    outputJSON("Thumbnails : [$cnt]", "success");
}

//////////////////////////////////////////////////
// get receive info
$services['setplaylist'] = '_setplaylist';
function _setplaylist(){
    error_log ("Start ".__FUNCTION__);
    global $config_playlist;
    
    //remove symbolic link all
    cleardir($config_playlist, '/(captions|.hidden)/i');
    
    $lst = $_REQUEST['lst'];
    error_log("playlist :" .$lst);
    $lst = str_replace("|","\n",$lst);
    $list = preg_split('/\n/',$lst);
    
    $m = runplaylist($list);
    outputJSON($m, "success");
}

function runplaylist($list){
    error_log ("Start ".__FUNCTION__);
    global $config_slide, $config_playlist, $config_playlink;
    
    $m = "";
    $i = 0;
    foreach ($list as $slide){
        // set symbolic link
        if (file_exists("$config_playlist/$slide") ) continue;
        symlink( "$config_playlink/$slide", "$config_playlist/$slide"); 
        if (!file_exists("$config_playlist/$slide") ) continue;
        
        $i++;
    }
    $m .= submit_RPi(getcommand("restart"));
    $m .= "$i 건 목록 등록";
    
    return $m;
}


function cleardir($dir, $reserve) {
    error_log ("Start ".__FUNCTION__);

    $dir = ($dir=="")?".":$dir;
    $links = scandir($dir);
    foreach ($links as $l){
        //error_log($l);
        if ($l=='.' || $l == '..') continue;
        if (preg_match($reserve,$l,$matches)) continue;
        
        $link = "$dir/$l";
        if (is_dir($link)) cleardir($link, $reserve);
        
        //error_log("$link will remove");
        unlink($link);
    }
    return;
}

function scans($dir, $depth, $home="."){
    //error_log ("Start ".__FUNCTION__);

    $ignored = array('.', '..', '.svn', '.git', '.gitignore', '.htaccess', 'captions');
    
    $files = scandir("$home/$dir");
    //error_log(print_r($files, true));
    $dir = ($dir=="")?".":$dir;
    $path = ($dir==""||$dir==".")?"":$dir."/";
    $m = array();
    foreach ($files as $f) {
        //error_log("$f =@= $path");
        if (in_array($f, $ignored)) continue;
        //error_log($home.'/'.$path.$f);
        $m[$path.$f] = filemtime($home.'/'.$path.$f);
        if (is_dir($path.$f)){
            $n = scans($path.$f, $depth+1, $home);
            if (sizeof($n)) $m = array_merge($m, $n);
        }
    }
    //$m1 = array_flip($m);
    arsort($m);  //time ordering
    //$m = array_flip($m1);
    //error_log ($m);
    return $m;
}

//////////////////////////////////////////////////
// get receive info
$services['getslidelist'] = '_getslidelist';
function _getslidelist(){
    error_log ("Start ".__FUNCTION__);
    
    global $config_slide, $config_playlist, $config_thumbs;
    error_log("pwd : [".getcwd()."], slide : [$config_slide], list : [$config_playlist]");
    $section = isset($_POST['section'])?$_POST['section']:"true";
    
    $contents = isset($_POST['contents'])?$_POST['contents']:"";
    switch($contents) {
        case "video": 
            $list_path=$config_video;  
            $cont_path=$config_video; 
            break;
        case "slide": 
        case "": 
        default: 
            $list_path=$config_playlist;  
            $cont_path=$config_slide; 
            break;
    }
    //
    $plays = scans("", 0, $list_path);
    $slides = scans("", 0, $cont_path);

    $dir = "";
    $depth = 0;
    
    $time = time();
    $path = ($dir==""||$dir==".")?"":$dir."/";
    $t = 0;
    $arr = array();
    foreach ($slides as $p=>$s) {
        //error_log("$p=>$s");

        $e = $u = "";
        $interval = (integer) (($time - $s)/60);
        if ($interval < 60) {
            if ($t <1) { $u = "한 시간 이내"; $t = 1;}
            $e = $interval.' 분전';
        } elseif ( $interval < 1440 ) {
            if ($t <2) { $u = "하루 이내"; $t = 2;}
            $e = ((integer) ($interval/60)).' 시간';
        } elseif ( $interval < 10080 ) {
            if ($t <3) { $u = "일주 이내"; $t = 3;}
            $e = ((integer) ($interval/1440)).' 일';
        } elseif ( $interval < 43200 ) {
            if ($t <4) { $u = "한달 이내"; $t = 4;}
            $e = ((integer) ($interval/10080)).' 주';
        } else {
            if ($t <5) { $u = "그 외"; $t = 5;}
            $e = ((integer) ($interval/43200)).' 개월';
        }
        $e = date("Y/m/d", $s);

        $f = $cont_path.'/'.$p;
        $n = $config_thumbs.'/'.$p.".png";
        if (!file_exists($n))$n = "";
        $v =isset($plays[$p])?"V":" ";
        
        $u = ( $section != "true")?"":$u;
        
        $arr[] = array(
		           'term' => "TERM".$t,
		           'type' => is_file($f), 
				   'elapse' => $e, 
				   'block' => $u, 
				   'photo' => $p,
				   'file' => $f, 
				   'thumb' => $n, 
				   'view' => $v, 
				   'time' => $s,
				   'test_thumb' => $_SERVER['HTTP_HOST'].substr($n,2),
				   'test_file' => $_SERVER['HTTP_HOST'].substr($f,2)
				   );
        
    }
    outputJSON($arr, "success");
}

//////////////////////////////////////////////////
// get receive info
$services['getplaylist'] = '_getplaylist';
function _getplaylist(){
    error_log ("Start ".__FUNCTION__);
    
    global $config_slide, $config_playlist, $config_thumbs, $config_video;
    error_log("pwd : [".getcwd()."], slide : [$config_slide], list : [$config_playlist]");

    $contents = isset($_POST['contents'])?$_POST['contents']:"";
    switch($contents) {
        case "slide": 
            $list_path=$config_slide; 
            $cont_path=$config_slide; 
            break;
        case "video": 
            $list_path=$config_video;  
            $cont_path=$config_video; 
            break;
        case "": 
        default: 
            $list_path=$config_playlist;  
            $cont_path=$config_slide; 
            break;
    }
    
    $plays = scans("", 0, $list_path);

    $dir = "";
    $depth = 0;
    
    $time = time();
    $path = ($dir==""||$dir==".")?"":$dir."/";
    $t = 0;
    $arr = array();
    foreach ($plays as $p=>$s) {
        //error_log("$p=>$s");

        $e = $u = "";
        $interval = (integer) (($time - $s)/60);
        if ($interval < 60) {
            if ($t <1) { $u = "한시간 인내"; $t = 1;}
            $e = $interval.' 분전';
        } elseif ( $interval < 1440 ) {
            if ($t <2) { $u = "하루 이내"; $t = 2;}
            $e = ((integer) ($interval/60)).' hours';
        } elseif ( $interval < 10080 ) {
            if ($t <3) { $u = "일주 이내"; $t = 3;}
            $e = ((integer) ($interval/1440)).' days';
        } elseif ( $interval < 43200 ) {
            if ($t <4) { $u = "한달 이내"; $t = 4;}
            $e = ((integer) ($interval/10080)).' weeks';
        } else {
            if ($t <5) { $u = "그외"; $t = 5;}
            $e = ((integer) ($interval/43200)).' months';
        }
        $e = date("Y/m/d", $s);

        
        $f = $contents_path.'/'.$p;
        $n = $config_thumbs.'/'.$p.".png";
        //$v =isset($plays[$p])?"V":" ";
        
        $arr[] = array('term' => $t, 'elapse' => $e, 'block' => $u, 'photo' => $p, 'file' => $f, 'thumb' => $n, 'time' => $s);
        
    }
    outputJSON($arr, "success");
}

/////////////////////////////////////////
// 현재기준 시작,종료시간과 건수를 기준으로 
// 목록에 등록된 건을 처리한다.
function update_playlist_w_term($lc) {
    error_log ("Start ".__FUNCTION__);
    //$time : 0 no limit in seconds
    //$conut : 0 no limit in seconds
    if ($lc["time_base"] == "_") return "not scheduled";

    global $config_playlist, $config_playlink, $config_slide;
    
    $stime = $lc["time_begin"]*3600;
    $etime = $lc["time_days"]*24*3600 + $lc["time_hours"]*3600;
    
    // 건수를 적용하는 경우 함께
    if ($lc["count_base"] != "_") $count = $lc["count_max"];
    //error_log("stime [$stime], etime [$etime], count [$count]");
                
    $slides = scans("", 0, $config_slide);
    $ignored = array('captures', '.hidden');
    
    $time = time();
    $cnt = $lcmt = $acnt = $rcnt = $lcnt = 0;
    foreach ($slides as $p=>$s) {
        $diff = $time - $s;
        //error_log("$p=>$s $stime [diff:$diff] $etime");
        if (in_array($p, $ignored)) continue;

        if (   ( $stime >= 0 && $stime > $diff) 
            || ( $etime >= 0 && $etime < $diff) ) 
        {
            // remove file from play list
            if (file_exists("$config_playlist/$p")) {
                unlink( "$config_playlist/$p"); 
                $rcnt ++;
            }
            $lcnt --;
        } else {
            // add file to play list
            if (!file_exists("$config_playlist/$p")) {
                symlink( "$config_playlink/$p", "$config_playlist/$p"); 
                $acnt ++;
            }
            $lcnt ++;
        }
        $cnt++;
    }
    $m = "total [$cnt], listed[$lcnt], added[$acnt], removed[$rcnt]";
    //변화량을 응답
    return $acnt + $rcnt;
}

function update_playlist_w_count($lc) {
    error_log ("Start ".__FUNCTION__);
    //$time : 0 no limit in seconds
    //$conut : 0 no limit in seconds
    
    if ($lc["count_base"] == "_") return "not scheduled";
    
    global $config_playlist, $config_playlink, $config_slide;

    $count = $lc["count_max"];
    error_log("count [$count]");
                
    $slides = scans("", 0, $config_slide);
    $ignored = array('captures', '.hidden');
    
    $cnt = $lcmt = $acnt = $rcnt = $lcnt = 0;
    foreach ($slides as $p=>$s) {

        if (in_array($p, $ignored)) continue;

        if (   ( $count != 0 && $count <= $cnt ) ) 
        {
            // remove file from play list
            if (file_exists("$config_playlist/$p")) {
                unlink( "$config_playlist/$p"); 
                $rcnt ++;
            }
            $lcnt --;
        } else {
            // add file to play list
            if (!file_exists("$config_playlist/$p")) {
                symlink( "$config_playlink/$p", "$config_playlist/$p"); 
                $acnt ++;
            }
            $lcnt ++;
        }
        $cnt++;
    }
    $m = "total [$cnt], listed[$lcnt], added[$acnt], removed[$rcnt]";
    //변화량을 응답
    return $acnt + $rcnt;
}

$services['renewal_playlist'] = '_renewal_playlist';
function _renewal_playlist() {
    error_log ("Start ".__FUNCTION__);
    $m = reload_playlist();
    outputJSON($m, 'success');
}

function reload_playlist() {
    error_log ("Start ".__FUNCTION__);
    global $config_playlist, $config_playlink, $config_slide;
    
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/playlist.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $listconf = json_decode(file_get_contents($sfile), true);
    $lc = $listconf[$pname];
    
    $m = 0;
    if ($lc["time_base"] != "_") $m += update_playlist_w_term($lc);
    error_log("update counts of playlist by term : $m");
    if ($lc["count_base"] != "_") $m += update_playlist_w_count($lc);
    error_log("update counts of playlist by count : $m");
    if ($m > 0)  submit_RPi(getcommand("restart"));
    return "수정된 목록항목 : $m";
}


/////////////////////////////////////
// 재상목록운영 정책 적용 및 정보 저장
$services['set_playlistpolicy'] = '_set_playlistpolicy';
function _set_playlistpolicy() { 
    error_log ("Start ".__FUNCTION__);
    global $config_slide;

    //update play policy
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/playlist.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $listconf = json_decode(file_get_contents($sfile), true);
    
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        $listconf[$pname][$k] = $v;
        //error_log( "$k => $v");
    }
    file_put_contents($sfile, json_encode($listconf));

    $plc = $listconf[$pname];
    $m = "";
    //일정기간 수신된 이미지를 보여주는 경우
    if ( ( $plc["time_base"] != "_") 
         || ($plc["count_base"] != "_") ) {
        $m .= reload_playlist();
        
        //restart system
        //$m .= "\n". submit_RPi(getcommand("restart"));
        
        //register
        $m .= "\n". enable_playlist_update( $plc["reload_interval"] );
    } else {
        $m .= "\n". disable_playlist_update();
    }

    if ($plc["media_base"] != "_") {
        $slides = scans("", 0, $config_slide);
        $list = array_flip($slides);
        $m = runplaylist($list);
    }
    //전체 파일을 등록하는 경우
    outputJSON($m, 'success');
};

function enable_playlist_update($interval){
    error_log ("Start >>>>>".__FUNCTION__);
    
    $con = "/usr/bin/wget -q http://localhost/signage/s00_event.php?func=gen_playlist -o /run/shm/s00_event.out -O /run/shm/s00_event.rt \n";
    $fna = "/etc/hive/tasks/gen_playlist.sh";
    file_put_contents($fna, $con);
    
    $m = add_schedule($fna, $interval);
    return $m;
}

function disable_playlist_update() {
    error_log ("Start ".__FUNCTION__);
    $con = "exit 0";
    $fna = "/etc/hive/tasks/gen_playlist.sh";
    file_put_contents($fna, $con);
    chmod ( $fna, 0644 );
    return  cancle_scheduler($fna);
}

/////////////////////////////////////
// 재상목록운영 정책정보 보기
$services['get_playlistpolicy'] = '_get_playlistpolicy';
function _get_playlistpolicy() { 
    error_log ("Start ".__FUNCTION__);
    
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/playlist.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $listconf = json_decode(file_get_contents($sfile), true);
    $listconf = $listconf[$pname];
    //file_put_contents($sfile, json_encode($listconf));
    
    outputJSON($listconf, 'success');
};

/////////////////////////////////////
// 재상방법 설정
$services['set_playmode'] = '_set_playmode';
function _set_playmode() { 
    error_log ("Start ".__FUNCTION__);
    
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/playmode.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $playmode = json_decode(file_get_contents($sfile), true);
    
    $fname="/etc/hive/signage/feh.sh";
    $msg = file_get_contents($fname);
    
        //$msg = "feh -p -Y -x -q -D 5 -d -B black -F --zoom fill  -R 3 -C /var/www/signage -e NanumGothic.woff/64 -K captions/ -r /var/www/media/playlist -nS mtime";
        $msg = trim(str_replace("  ", " " , $msg))." ";
    
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        $playmode[$pname][$k] = $v;
        //error_log( "$k => $v");
        
        
        switch($k) {
            case "squential_play":
                if ($v == "_") break;
                $msg = str_replace(" -z ", " " , $msg);
                break;
            case "randon_play":
                if ($v == "_") break;
                $msg = str_replace(" -z ", " " , $msg);
                $msg .= " -z";
                break;
            case "slide_interval":
                if ($v == "") $v = 5;
                $msg = preg_replace('/ -D (-*\d+) /'," -D $v " , $msg);
                break;
            case "screen_zoom":
                if ($v == "_") break;
                $msg = str_replace(" --zoom fill "," " , $msg);
                break;
            case "photo_zoom":
                if ($v == "_") break;
                $msg = str_replace(" --zoom fill"," " , $msg);
                $msg = str_replace(' -F ',' -F --zoom fill ' , $msg);
                break;
        }
        $msg = trim(str_replace("  ", " " , $msg));
        
    }
    file_put_contents($fname, $msg);
    file_put_contents($sfile, json_encode($playmode));
    
    $r = submit_RPi(getcommand("restart"));
    
    outputJSON($sfile, 'success');
};

/////////////////////////////////////
// 재생방법 보기
$services['get_playmode'] = '_get_playmode';
function _get_playmode() { 
    error_log ("Start ".__FUNCTION__);
    
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/playmode.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $playmode = json_decode(file_get_contents($sfile), true);
    $playmode = $playmode[$pname];
    //file_put_contents($sfile, json_encode($playmode));
    
    outputJSON($playmode, 'success');
};

/////////////////////////////////////
// subject 설정
$services['set_subject'] = '_set_subject';
function _set_subject() { 
    s00_log ("Start ".__FUNCTION__);
    //$subjects = $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/contents/ebs/L3subject1.lst';
   
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/profile.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $profile = json_decode(file_get_contents($sfile), true);
    
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        $profile[$pname][$k] = $v;
        error_log( "$k => $v");
    }
    
    if(isset($_FILES['nfile']) && ($_FILES['nfile']['error'] == 0)) {
            // 파일처리 
            // 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
            $path = dirname($sfile);
            if (! file_exists($path) ) mkdir($path, 0664, true);
            
            $tmp_file  = $_FILES['nfile']['tmp_name'];
            $fileext   = get_extension($_FILES['nfile']['type']);
            
            $dest_file = $path."/profile.".$fileext;
            error_log($dest_file);
            $error_code = move_uploaded_file($tmp_file, $dest_file) 
                            or outputJSON( $_FILES['nfile']['error']);
            
            // 파일의 퍼미션을 변경합니다.
            chmod($dest_file, 0660);
            error_log("org = ".$_FILES['nfile']['name'].", saved to = $dest_file");
            $profile[$pname]["photo"] = $dest_file;
    }
    
    file_put_contents($sfile, json_encode($profile));
    
    outputJSON($sfile, 'success');
};

/////////////////////////////////////
// subject 설정
$services['get_subject'] = '_get_subject';
function _get_subject() { 
    s00_log ("Start ".__FUNCTION__);
    
    $pname = isset($_POST['profile'])?$_POST['profile']
                :file_get_contents("custom/default");
    $sfile = "custom/".$pname."/profile.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $profile = json_decode(file_get_contents($sfile), true);
    $profile = $profile[$pname];
    //file_put_contents($sfile, json_encode($profile));
    
    outputJSON($profile, 'success');
};

/////////////////////////////////////
// 접속코드 설정
$services['set_code'] = '_set_code';
function _set_code() { 
    s00_log ("Start ".__FUNCTION__);
    
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/access.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $access = json_decode(file_get_contents($sfile), true);
    
    $changed = false;
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        if ($access[$pname][$k] != $v) {
            $access[$pname][$k] = $v;
            $changed = true;
        }
        //error_log( "$k => $v");
        
        if (preg_match ("/access_code|sam_code|admin_code/", $k)) {
            $_SESSION[$k] = $v;
        }
    }
    error_log("$accees[$pname]['wifi_password']");
    // if (strlen($access[$pname]['wifi_password']) <= 7){
        // outputJSON("TEST", 'success');
    // }
    
    if ($changed)
        set_wifiinfo($access[$pname]['ssid'], 
                    $access[$pname]['wifi_password']) ;
    
    if ($changed)
        file_put_contents($sfile, json_encode($access));
    
    //
    //set network accessibility timely
    // set fro active or positive access
    // at the timerjob.php
    $con = "#!/bin/bash\n";
    $con .= "cd /var/www/signage\n";
    $con .= "/usr/bin/php /var/www/signage/timerjob.php";
    $fna = "/etc/hive/tasks/signage_net_initialze.sh";
    file_put_contents($fna, $con);
    $m = add_schedule($fna, 60);

    unset($_SESSION['access']);
    
    outputJSON($sfile, 'success'); 
};

////////////////////////////////////////////////////////////////
//네트워크 정보 관리

/////////////////////////////////////
// 접속코드 보기
$services['get_code'] = '_get_code';
function _get_code() { 
    s00_log ("Start ".__FUNCTION__);
    
    $pname = isset($_POST['profile'])?$_POST['profile']
                :file_get_contents("custom/default");
    $sfile = "custom/".$pname."/access.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $profile = json_decode(file_get_contents($sfile), true);
    $profile = $profile[$pname];
    //file_put_contents($sfile, json_encode($profile));

    $tt = get_wifiinfo();
    error_log("wifi info:".print_r($tt,true));
    foreach ($tt as $k => $v) {
        if ('wpa_passphrase'==$k) $k='wifi_password';
        $profile[$k] = $v;
    }
    
    outputJSON($profile, 'success');
};

////////////////////////////////////////////////////////////
//

/////////////////////////////////////
// set slide caption
$services['upvideo'] = '_upvideo';
function _upvideo() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_video, $config_playlist, $config_playlink, $config_caption, $config_thumbs;
    // Upload file
    // Check for errors
    //prevent duplication
    $actionlog = "/run/shm/lastaction/".session_id ();
    $currentcard = md5(print_r($_POST, true));
    $lastcard = file_exists($actionlog)?file_get_contents($actionlog):"none";
    if ($currentcard == $lastcard) 
        if (time() - filemtime($actionlog)  < 2){
            error_log(time()."======".filemtime($actionlog));
            outputJSON('Not allowed double actions');
        }
    if (!file_exists($actionlog)) mkdir(dirname($actionlog), 0766, true);
    file_put_contents($actionlog, $currentcard);
    
    
    if(!isset($_FILES['video'])){
        outputJSON('File is not defined.');
    }
    $FILE = $_FILES['video'];
    //
    if($FILE['error'] > 0){
        outputJSON('An error occurred when uploading.');
    }

    $dfs = disk_free_space(".");
    if (($dfs - $FILE['size'])
         < 1048576 * $_SESSION['reserve_space']) 
    {
        outputJSON('Not enough disk free space.');
    }
    
    // Check filesize
    $fmem = get_free_mem();
    if($FILE['size'] > 1024*1024*1024 ) { //$fmem ){
        outputJSON('File uploaded exceeds maximum upload memory size['.$fmem.'].');
    }

    $file = $FILE["name"];
    $file = str_replace(" ","",$file);
    
    $fx = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($fx == "jpeg") $fx = "jpg"; 
    $video_md5 = get_videomd5($FILE['tmp_name']);
    $vodeo_md5_name = "$video_md5.$fx";
    
    error_log("file name [$vodeo_md5_name]");
    
    if(!move_uploaded_file($FILE['tmp_name'],  "$config_video/$vodeo_md5_name")){
        outputJSON('Error uploading file - check destination is writeable.');
    }
    
    if (!file_exists("$config_video/". $vodeo_md5_name)) 
        error_log("$config_video/". $vodeo_md5_name." is not SAVED");
    
    error_log( "VIDEO: [$config_video/$vodeo_md5_name]"); 
    
    $caption = isset($_POST['caption'])?$_POST['caption']:"";
    $cfile = "$config_caption/$vodeo_md5_name.txt";
    file_put_contents($cfile, $caption);
    
    //To-Do, change code
    make_thumb_from_video("images/video.png", 
                "$config_thumbs/$vodeo_md5_name.png", 64, 64);

    $ifile = $_SESSION['info']."/$vodeo_md5_name.json";
    $iinof = array("caption" =>$caption,
                   "time" =>time(),
                   "photo_md5" =>$video_md5,
                   "user_md5" =>"",
                   "thumbs" =>"thumbs",
                   "origin" =>$file);
    file_put_contents($ifile, json_encode($iinof));

    if ($ll = glob("$config_video/.omx_default.*")) {
        foreach($ll as $ff) unlink($ff);
    }
    symlink( "$vodeo_md5_name", 
                "$config_video/.omx_default.$fx"); 
                
    outputJSON(array("msg" => "[$file] 등록완료", "file"=>$file, "photo"=>$vodeo_md5_name), 'success');
    
}    

//////////////////////////////////////////////////
// get receive info
function make_thumb_from_video($file, $thumb, $t_width,$t_height) 
{
    $source_image = imagecreatefromstring(file_get_contents($file)); //파일읽기
    $width = imagesx($source_image);
    $height = imagesy($source_image);

    $virtual_image = imagecreatetruecolor($t_width, $t_height); //가상 이미지 만들기

    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $t_width, $t_height, $width, $height); //사이즈 변경하여 복사
    imagepng($virtual_image, $thumb); // png파일로 썸네일 생성
}

/////////////////////////////////////
// set slide caption
$services['rmvideo'] = '_rmvideo';
function _rmvideo() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_video, $config_playlist, $config_thumbs, $config_info, $config_captions;
    // Upload file
    // Check for errors
    $video = isset($_POST['video'])?$_POST['video']:"";
    
    error_log("remove file : ".$video);
    
    if ($video=="") outputJSON("File name is missing!");
    $cfile = "$config_captions/".basename($video).".txt";
    //$pfile = "$config_playlist/".basename($video);
    $tfile = "$config_thumbs/".basename($video).".png";
    $ifile = "$config_info/".basename($video).".json";
    $sfile = "$config_video/".basename($video);

    if (file_exists($tfile)) unlink($tfile);
    if (file_exists($cfile)) unlink($cfile);
    //if (file_exists($pfile)) unlink($pfile);
    if (file_exists($ifile)) unlink($ifile);
    if (file_exists($sfile)) unlink($sfile);
    
    // Success!
    outputJSON("Removed card : [$video].", 'success'); 
}    

// play video 
$services['getvideo'] = '_getvideo';
function _getvideo() { 
    s00_log ("Start ".__FUNCTION__);
    global $config_playlist, $config_caption, $config_slide, $config_info,$config_thumbs, $config_playlink, $config_video ;

    $base = isset($_POST['video'])?$_POST['video']:"";
    $action = isset($_POST['action'])?$_POST['action']:""; //current. next, ...
    $source = isset($_POST['source'])?$_POST['source']:"";
    
    $info = "";
    $video = "";
    // video is on usb drive
    if ($source != "") {
        //direct
        $video = $source.'/'.$base;
        date_default_timezone_set('Asia/Seoul');
        $mtime = date('Y-m-d H:m:s', filemtime($video));

        $status = "new";
        $fx = strtolower(pathinfo($video, PATHINFO_EXTENSION));
        $link = "$config_video/.omx_default.$fx";
        unlink($link);
        symlink( $video, $link); 
        
        $arr = array (
            "video" => $base
          , "caption" => $base
          , "mtime" => $mtime
          , "url" => $link
          , "origin" => $base
          , "status" => $status
          );
          
        outputJSON($arr, 'success');
    }
    
    $video = getfile($config_video, $base, $action);
    error_log("[$action] of [$base] is [$video]");
    if ($video=="") {
        error_log("Set the first sample.");
        $video_url="http://www.w3schools.com/html/mov_bbb.mp4";
        $video = "sample.mp4";
        shell_exec("wget $video_url -O $config_video/$video");
    }

    $url = $config_video.'/'.$video;
    $cfile = $config_caption.'/'.$video.'.txt';
    $caption = file_exists($cfile)?
                 file_get_contents($cfile):$video;

    $ifile = "$config_info/$video.json";
    $info = json_decode(file_get_contents($ifile),true);

    
    $status = "reserved";
    $fx = strtolower(pathinfo($video, PATHINFO_EXTENSION));
    $link = "$config_video/.omx_default.$fx";
    
    $target = readlink($link);
    if ( basename($target) != basename($video) ) {
        $status = "new";
        unlink($link);
        error_log("delete link :".$link);
    }
        
    if ($ll = glob("$config_video/.omx_default.*")) {
        foreach($ll as $ff) {
            if (!file_exists($ff)) error_log("EEEEEEEEEEERROR".$ff);
            unlink($ff);
        }
    }
    symlink( $video, $link); 
    
    date_default_timezone_set('Asia/Seoul');

    $arr = array (
        "video" => $video
      , "caption" => $caption
      , "mtime" => time()
      , "url" => $url
      , "origin" => $info['origin']
      , "status" => $status
      );
      
    outputJSON($arr, 'success');
};


/////////////////////////////////////
// control to slide show on running
$services['video_control'] = '_video_control';
function _video_control() { 
    s00_log ("Start ".__FUNCTION__);

    $ctrl = $_POST['ctrl'];
    
    $msg = get_omxcommand($ctrl);
    if ($msg=="") outputJSON("[$ctrl] is not defined.");
    
    $r = submit_RPi($msg);
    
    outputJSON("[$ctrl] proceed", 'success');
};

/////////////////////////////////////
// command lists for control fehF
function get_omxcommand($ctrl) {
    s00_log ("Start ".__FUNCTION__);
    s00_log ("message [".$ctrl."]");

    $msg["restart"]="q{USLEEP 1000000}[ENTER]<LEFTCTRL_DOWN><LEFTCTRL_HOLD><LEFTALT_DOWN><LEFTALT_HOLD>o<LEFTALT_UP><LEFTCTRL_UP>";
    $msg["decrease speed"]="1";
    $msg["increase speed"]="2";
    $msg["rewind"]="<LEFTSHIFT_DOWN><LEFTSHIFT_HOLD>[COMMA]<LEFTSHIFT_UP>";
    $msg["fast_forward"]="<LEFTSHIFT_DOWN><LEFTSHIFT_HOLD>[DOT]<LEFTSHIFT_UP>";
    $msg["show info"]="z";
    $msg["previous chapter"]="i";
    $msg["next chapter"]="o";
    $msg["toggle subtitles"]="s";
    $msg["show subtitles"]="w";
    $msg["hive subtitles"]="x";
    $msg["exit"]="q";
    $msg["pause/resume"]="p";
    $msg["decrease volume"]="[MINUS]";
    $msg["increase volume"]="[EQUAL]";
    $msg["seek -30 seconds"]="[KP4]";
    $msg["seek +30 seconds"]="[KP6]";
    $msg["seek -600 seconds"]="[KP2]";
    $msg["seek +600 seconds"]="[KP8]"; 

    return (isset($msg[$ctrl]))?$msg[$ctrl]:"";
}

//////////////////////////////////////
//// USB 


/////////////////////////////////////
// get usb name
define("dUSBROOT", "/media");
$services['get_nextusb'] = '_get_nextusb';
function _get_nextusb() { 
    s00_log ("Start ".__FUNCTION__);

    $un = $_POST['usb_name'];
    $sun = "";
    
    //read and cleansing
    $fds = scandir(dUSBROOT);
    if ($fds==false) outputJSON(array('usb_name'=>'N/A'), 'success');
    $fds2 = "";
    foreach($fds as $i => $fd) {
        if ( ($fd == '.') || ($fd == '..') ) continue;
        $fds2[] = $fd;
    }
    $fds = $fds2;
    
    //search 
    if (sizeof($fds)==1) outputJSON(array('usb_name'=>$fds[0]), 'success');
    foreach($fds as $i => $fd) {
        if ( ($fd == $un) && isset($fds[$i+1]) ) {
            $sun = $fds[$i+1];
            break;
        }
    }
    if ($sun == "") outputJSON(array('usb_name'=>$fds[0]), 'success');
    outputJSON(array('usb_name'=>$sun), 'success');
};


/////////////////////////////////////
// get usb path
$services['get_nextpath'] = '_get_nextpath';
function _get_nextpath() { 
    s00_log ("Start ".__FUNCTION__);

    $un = $_POST['usb_name'];
    $up = $_POST['usb_path'];
    $sun = "";
    $usb_path = dUSBROOT."/$un";
    //error_log("usbroot is [$usb_path]}");
    
    //read and cleansing
    chdir("$usb_path");
    $fds = shell_exec("find . -type d");
    //error_log(print_r($fds , true));
    $fds2 = str_replace("\n","|",$fds);
    //error_log(print_r($fds2 , true));
    $fds = explode("|",$fds2);
    //error_log(print_r($fds , true));
    
    //search 
    if (sizeof($fds)==1) outputJSON(array('usb_path'=>$fds[0]), 'success');
    foreach($fds as $i => $fd) {
        //error_log($fd);
        if ( ($fd == $up) && isset($fds[$i+1]) ) {
            $sun = $fds[$i+1];
            break;
        }
    }
    if ($sun == "") outputJSON(array('usb_path'=>$fds[0]), 'success');
    outputJSON(array('usb_path'=>$sun), 'success');
};

/////////////////////////////////////
// backup content to usb
$services['usb_backup'] = '_usb_backup';
function _usb_backup() {
    s00_log ("Start ".__FUNCTION__);

    error_log(print_r($_POST , true));
    
    $image_cb = $_POST['image_cb']; //
    $video_cb = $_POST['video_cb']; //
    $profile_cb = $_POST['profile_cb'];
    $usb_name = $_POST['usb_name']; // 
    $usb_path = $_POST['usb_path']; //
    
    //read and cleansing
    date_default_timezone_set('Asia/Seoul');
    $mtime = date('Y-m-d.H-m-s', time());
    //dUSBROOT="/media"
    $usb_dest = "";
    if (strpos($usb_path, "AnHivePF")!==false){
        $usb_dest = dUSBROOT."/$usb_name/$usb_path";
        preg_match('/(?<path>^.*AnHivePF)/',$usb_dest,$m);
        $usb_dest = $m['path'];
    } else {
        $usb_dest = dUSBROOT."/$usb_name/$usb_path/AnHivePF";
    }
    if(!($profile_cb != "_" && $video_cb != "_" && $image_cb != "_")){
        outputJSON("백업 대상이 없음..", 'success');
    }
    if(!file_exists($usb_dest)) mkdir($usb_dest,0777, true);
    $cont_path = "/var/www/media";
    $cont_path_sign = "/var/www/signage";
    $image_cb=($image_cb != "_")?1:0;
    $video_cb=($video_cb != "_")?1:0;
    $profile_cb=($profile_cb)?1:0;
    //
    if(!file_exists($cont_path . "/backup")) mkdir($cont_path . "/backup", 0777, true); // backup 디렉토리 생성
    //chdir($media_path);
    chdir($cont_path);
    $rt = shell_exec("sudo /etc/hive/bin/usb_backup.sh backup $usb_dest $cont_path $cont_path_sign $image_cb $video_cb $profile_cb");
    error_log("end of backup");
    outputJSON("backup completed", 'success');
};

/////////////////////////////////////
// usb_restore
$services['usb_restore'] = '_usb_restore';
function _usb_restore() { 
    s00_log ("Start ".__FUNCTION__);

    error_log(print_r($_POST , true));
    
    $image_cb = $_POST['image_cb'];
    $video_cb = $_POST['video_cb'];
    $profile_cb = $_POST['profile_cb'];
    $usb_name = $_POST['usb_name'];
    $usb_path = $_POST['usb_path'];
    
    //free space check
    $dfs = disk_free_space(".");
    if (($dfs - $_FILES['card']['size']) < 1048576 * $_SESSION['reserve_space']) 
    {
        outputJSON('Not enough disk free space.');
    }
    
    //read and cleansing
    date_default_timezone_set('Asia/Seoul');
    $mtime = date('Y-m-d.H-m-s');
    
    $usb_dest = "";
    if (strpos($usb_path, "AnHivePF")!==false){
        $usb_dest = dUSBROOT."/$usb_name/$usb_path";
        preg_match('/(?<path>^.*AnHivePF)/',$usb_dest,$m);
        $usb_dest = $m['path'];
    } else {
        $usb_dest = dUSBROOT."/$usb_name/$usb_path/AnHivePF";
    }

    $cont_path = "/var/www/media";
    $cont_path_sign = "/var/www/signage";
    $image_cb=($image_cb != "_")?1:0;
    $video_cb=($video_cb != "_")?1:0;
    $profile_cb=($profile_cb)?1:0;
    if(file_exists($usb_dest . "/backup.tar.gz")){
        chdir($cont_path);
        $rt = shell_exec("sudo /etc/hive/bin/usb_backup.sh restore $usb_dest $cont_path $cont_path_sign $image_cb $video_cb $profile_cb");
    }
    
    error_log("end of restore");
    outputJSON("backup restore", 'success');
};
//////////////////////////////////
//////USB file copy for RPI
$services['fileCopy'] = '_fileCopy';
function _fileCopy(){
    error_log ("Start ".__FUNCTION__);
    
    global $config_playlist, $config_caption, $config_slide, $config_info,$config_thumbs, $config_playlink ;
    
    $fileslength = $_POST['length'];
    $directory = $_POST['directory'];
    
    for($i=0;$i<$fileslength;$i++){
        if(!(isset($_POST['fileSearch' . "$i"]))) { continue; }
        $file = $_POST['fileSearch' . "$i"];
        $file_md5=(pathinfo($file, PATHINFO_EXTENSION)=="jpg")?substr(md5($file),-14,12).".jpg":substr(md5($file),-14,12).".png";
        
   
        $usbfile = $directory."/$file"; // 원본파일 
        $slidefile = $config_slide."/$file_md5"; //  slide 복사파일 
        $thaumbsfile = $config_thumbs."/$file_md5.png"; // thaumbs 복사파일
        $captionfile = $config_caption."/$file_md5.txt"; //txt 파일
        $infofile = $config_info."/$file_md5.json"; // json 원본 파일
 
        // /home/pi/media/slide
        if(!copy($usbfile, $slidefile)) { 
            throw new Exception ("파일복사 실패[$usbfile => $slidefile]");
        } 
        chmod($slidefile, 0775);
        // /home/pi/media/thaumbs 썸네일 파일 생성
        make_thumb_from_image($slidefile, $thaumbsfile, 64,64);
                
        // ~/media/captions 확장자명 .txt
        file_put_contents($captionfile, "");            
        
        // ~/media/info 확장자명 .json
        $json_file = $config_info."/$file_md5.json";
        $info = array("caption"=> "", 
            "time" => time(),
            "photo_md5"=>$file_md5,
            "user_md5"=>get_userindex(),
            "thumbs"=>"thumbs",
            "origin"=>$file);        
        file_put_contents($json_file, json_encode($info));                
     }
    
      outputJSON( "파일을 복사하였습니다" , 'success');
};

/////////////////////////////////////
// get usb name
$services['display'] = '_display';
function _display() { 
    s00_log ("Start ".__FUNCTION__);
    error_log(print_r($_POST , true));
    
    $rt ="";
    $power = $_POST['power'];
    if ($power == "on") {
        $rt = shell_exec("vcgencmd display_power 1");
    } else if ($power == "off") {
        $rt = shell_exec("vcgencmd display_power 0");
    } else {
        $rt = shell_exec("vcgencmd display_power");
    }
    $rt = trim($rt);
    error_log($rt);
    if ($rt == 'display_power=1') $rt = 'on';
    else if ($rt == 'display_power=0') $rt = 'off';
    error_log($rt);
    
    outputJSON($rt, 'success');
};

////////////////////////////////////////////
////// headnote
$services['headnote'] = '_headnote';
function _headnote(){
    s00_log("Start ".__FUNCTION__);
    $data=array(
        "photo" => $_SESSION['photo'],
        "subject" => $_SESSION['subject'],
        "owner" => $_SESSION['owner'],
        "footer" => $_SESSION['footnote']
    );
    if(@$_POST["samwork"] === "true" || @$_POST["syswork"] === "true"){
          $data += ["title" => $_SESSION['title']];
          $data += ["samcode" => $_SESSION['sam_code']];
          $data += ["accesscode" => $_SESSION['access_code']];
          $data += ["supriseboxcode" => $_SESSION['surprisebox_code']];
          $data += ["externalport" => $_SESSION['external_port']];
          $data += ["gateserver" => $_SESSION['gate_server']];
          $data += ["ssid" => $_SESSION['ssid']];
          $data += ["wifi_password" => $_SESSION['wifi_password']];
    }
    outputJSON($data, "success");
};

////////////////////////////////////////////////
////////////////////////////////////////////////
///////// home.html 서비스
$services['home_bottom_button'] = '_home_bottom_button';
function _home_bottom_button(){
    s00_log("Start ".__FUNCTION__);
    //entry criteria.. check condition, constraints  준비과정
    if ( !isset($_SESSION['uselevel']) || ($_SESSION['uselevel'] < 2) )
        outputJSON("error : uselevel is not defined or level is not privilige - line :  __LINE__", "error");
    
    // task
    $data[] = array(
        "link" => "participants.html",
        "spanInner" => "사용자"
        );
    $data[] = array(
        "link" => "samworks.html",
        "spanInner" => "앨범관리"
    );
    $data[] = array(
        "link" => "playwork.html",
        "spanInner" => "재생관리"
    );

    //exteneded task
    if(isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >=3){
        $data[] = array(
            "link" => "syswork.html",
            "spanInner" => "접속관리"
        );
    }
    $arr = array("contents"=>$data, "count"=>count($data));
    //validation 검증과정
        
    //exit criteria, return
    outputJSON($arr, "success");
};

// contents send
$services['home_level_contents'] = '_home_level_contents';
function _home_level_contents(){
    s00_log("Start ".__FUNCTION__);
    //entry criteria.. check condition, constraints  준비과정
    if ( !isset($_SESSION['uselevel']) ) outputJSON("error : uselevel is not defined - line :  __LINE__", "error");
    
    
    //task default process 기본 작업과정
    
    $data[]=array(
                "link" => "cardwork.html",
                "tiletitle" => "사진 전송",
                "color" => "#ff7979"
    );
    
    //task extended process 확장 작업과정
    if ($_SESSION['uselevel'] >= 2){

        $data[]=array(
                        "link" => "slidework.html",
                        "tiletitle" =>"앨범 보기",
                        "color" =>"#f9ca24");
                        
        $data[]=array(
                        "link" => "listwork.html",
                        "tiletitle" =>"목록 편집",
                        "color" =>"#a29bfe");
        $data[]=array(
                        "link" => "signwork.html",
                        "tiletitle" =>"리모트 관리",
                        "color" =>"#4cd137");
                        
        $data[]=array(
                        "link" => "videowork.html",
                        "tiletitle" =>"영상 관리",
                        "color" =>"#1e90ff");    
                
    } 
    $arr = array("contents"=>$data, "count"=>count($data));
    //validation 검증과정
    
    //exit criteria,, return 끝
    outputJSON($arr, "success");
};
////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////
///////// show_level - 레벨 값을 불러옴
$services['show_level'] = '_show_level';
function _show_level(){
    s00_log("Start ".__FUNCTION__);
    if(!isset($_SESSION["uselevel"])){
        $data = array("level" => 0);
        outputJSON($data, 'success');
    }
    $data = array("level" => $_SESSION["uselevel"] );
    outputJSON($data, "success");
}
////////////////////////////////////////////////////////////////////////////
////////// openimages - openimages.html에서 사용함.

$services['openimages'] = '_openimages';
function _openimages(){
    s00_log("Start ".__FUNCTION__);
    if ( !isset($_SESSION['uselevel']) ) 
        outputJSON("error : uselevel is not defined - line :  __LINE__", "error");
    
    $name = $_POST["name"];
    if (strpos($name, 'http') !== false) {
    $uri = $name;
    } else {
        $filename = basename ($name);  //c
        $dir= isset($_GET['dir'])?$_GET['dir']:$_SESSION['slide'];
        $script_path = dirname(trim($_SERVER['SCRIPT_NAME']));//if ($script_path != "") $dir = $script_path."/".$dir;
        $uri = $dir."/".$filename;
        $cap = $config_caption."/".$filename.'.txt';
    }
    $id_date = date("Y/m/d H:i:s.", filemtime($uri));
    $memo = "메모: ".(file_exists($cap)?file_get_contents($cap):"");
    $img = $uri;
    $data = array(
         "file_name" => $filename,
         "memo" => $memo,
         "date" => $id_date,
         "img" => $img
         );
    if(isset($_SESSION['uselevel']) && $_SESSION['uselevel']>1){ // rmcard(); privilege
          $data += [ "func" => "rmcard(\"$img\")" ];
          $data += [ "value" => "사진삭제"];
    };
    outputJSON($data, "success");
};

/////////////////////////////////////
//// videolist

$services['usbcheck'] = '_usbcheck';
function _usbcheck(){
    s00_log("Start ".__FUNCTION__);
    $usb = isset($_GET['usb'])?true:false;
    error_log("usb use : ". (isset($_GET['usb'])?"true":"false") );
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
             "footer" => $_SESSION['footnote']
             );
        $_SESSION['dir']= isset($_GET['dir'])? $_GET["dir"]: '../media/video';
    }
 
    outputJSON($data,"success");
}

$services['filecheck'] = '_filecheck';
function _filecheck(){
    s00_log("Start ".__FUNCTION__);
    $usb = isset($_GET['usb'])?true:false;
    $ext = isset($_GET['ext'])? $_GET["ext"]: '/(\.mp4|\.mov)/i';
    $mcount = 0;
    $tcount = 0;
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
                 "position" => $file );
            } 
        else {
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
                        $info = json_decode(file_get_contents($dir.'/../info/'.$file.'.json'), true);
                    } else {
                            $info = array("origin"=> basename($file));
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

/////////////////////////////////////
///// participants
$services['parti_level_contents'] = '_parti_level_contents';
function _parti_level_contents(){
    s00_log("Start ".__FUNCTION__);
    if (!isset($_SESSION['uselevel']) || !$_SESSION['uselevel'] >= 2) outputJSON("uselevel error, or you have not privilige this function LINE : __LINE__", "error");
    $data=array(
        "openSmart" => "set_clsss('open')",
        "closeSmart" => "set_clsss('close')",
        "mesg" => '접속화면에 "점검중입니다." 메시지 표시함.<br>일반 사용자는 이용할 수 없음.',
        );
    outputJSON($data, "success");
}

/////////////////////////////////////
// execute services
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
