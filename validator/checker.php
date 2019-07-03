<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../signage.base.css" />
    <title>테스트</title>
    <link rel="shortcut icon" href="../favicon.ico">
    <script type="text/javascript" src="../signage.base.js"></script>
</head>
<body >
    <div id="container">
<?php
function pass() {return "<span style='color:blue;'>PASS</span><br>";}
function fail() {return "<span style='color:red;'>WARNING</span><br>";}
function enabled() {return "<span style='color:blue;'>ENABLED</span><br>";}
function disabled() {return "<span style='color:red;'>DISABLED</span><br>";}
function avail() {return "<span style='color:blue;'>AVAILABLE</span><br>";}
function navail() {return "<span style='color:red;'>NOT AVAILABLE</span><br>";}

function changepath($contents) {
    if ( file_exists("/home/pi") )
        return str_replace("/home/anhive", "/home/pi", $contents);
    else 
        return str_replace("/home/pi", "/home/anhive", $contents);
}    
//-----------------------------------------------
echo "필요한 파일이 있는지 점검 Files required<br>";
echo "=================<br>";
echo check_files()."<br>";
function check_files() {
    chmod("files.lst", 0664);
    $lst = str_replace("\r", "", file_get_contents("files.lst"));
    $lst = changepath($lst);
    $files = preg_split('/\n/',$lst);

    $lst = $m = $x = "";
    $ok = $nk = 0;
    foreach( $files as $l) {
        if (strpos($l, "\t") !==false) list ($f, $md) = explode("\t",$l);
        else $f = $l;
        
        if ($f =="") {$lst .= "\n"; continue;}
        if (substr($f,1,1)=="#") {$lst .= "$f\n"; continue;}
        
        if (file_exists($f)) {
            $cmd5 = md5_file ($f);
            if ($md != "") {
                if ($md != $cmd5 ) $x .= "<span style='color:gray'>[$f]</span> changed.\n";
            } else {
                $x .= "$f new file.\n";
            }
            $ff = "$f\t$cmd5\n";
            $ok++;
        }else{
            $x .= "<span style='color:red'>[$f]</span>.. file not exist<br>";
            $ff = "$f\tNotExist\n";
            $nk++;
        }
        $lst .=  $ff;
    }
    $x .= "Good [$ok] files, <span style='color:red'>Bad [$nk]</span><br>";
    $x .= ($nk == 0) ? pass() : fail();
    $x .= "<br>";
    $m .= str_replace("\n", "<br>", $x)."<br>";
    file_put_contents("files.lst", $lst);
    return $m ;
}

echo "파일패스권한 점검<br>";
echo "=================<br>";
echo check_auth()."<br>";
function check_auth() {
    chmod("permission.lst", 0664);
    $lst = str_replace("\r", "", file_get_contents("permission.lst"));
    $lst = changepath($lst);
    $files = preg_split('/\n/',$lst);

    $x = $m = "";
    $ok = $nk = 0;
    foreach( $files as $l) {
        list ($f, $pm) = explode("\t",$l);
        
        if ($f =="") { continue;}
        if (substr($f,1,1)=="#") {continue;}
        if (!file_exists($f)) { 
            $x .= "<span style='color:gray'>[$f] is not exits... </span><br>";
            continue;
        }
        
        $pm = octdec($pm);
        $perms = fileperms($f) & 0777;
        if ($perms < $pm) {    
            $x .= sprintf( "<span style='color:red'>[$f] permission[%s] should be larger [%s] ... </span>", decoct ($perms), decoct ($pm))."\n";
            $nk++;
        } else {
            //$x .= sprintf( "[$f] permission[%s] is good [%s] ... ", decoct ($perms), decoct ($pm))."\n";
            $ok++;
        }
    }
    $x .= "Good [$ok] files, <span style='color:red'>Bad [$nk]</span><br>";
    $x .= ($nk == 0) ? pass() : fail();
    $x .= "<br>";
    $m .= str_replace("\n", "<br>", $x)."<br>";
    return $m ;
    
    $m .= str_replace("\n", "<br>", $x)."<br>";
    return $m ;
}


//-----------------------------------------------
echo "인터넷 액자에 사용하는 프로그램 실행 상태 점검, Services required<br>";
echo "=================<br>";
echo check_exec();
function check_exec() {
    chmod("execs.lst", 0664);
    $lst = str_replace("\r", "", file_get_contents("execs.lst"));
    $lst = changepath($lst);
    $exec =  preg_split('/\n/',$lst);
    $m = "";
    foreach($exec as $e) {
        $m .= "Process : $e<br>";
        $x = shell_exec("ps -ef | grep $e | grep -v grep | grep -v tail")."";
        $c = shell_exec("ps -ef | grep $e | grep -v grep | grep -v tail| wc -l");
        $x .= ($c > 0) ? avail() : navail();
        $m .= str_replace("\n", "<br>", $x)."<br>";
    }
    return $m;
}


//-----------------------------------------------
echo "인터넷 접속 가능성 참조, Network required<br>";
echo "=================<br>";
echo check_network()."<br>";
function check_network() {
    $m = "";
    $x = "External ip: ".shell_exec("/usr/bin/wget http://thebolle.com/ip.php -qO- ")."<br>";
    $x .= (preg_match('/\d+.\d+.\d+.\d+/',$x,$p) > 0) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br>";
    
    $x = "Internal ip: ".shell_exec("hostname -I");
    $x .= (preg_match('/\d+.\d+.\d+.\d+/',$x,$p) > 0) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br>";
    return $m;
}

//-----------------------------------------------
echo "메디어 정보가 있는지 확인, Media files exists<br>";
echo "=================<br>";
echo check_media()."<br>";
function check_media() {
    $x = shell_exec("ls -lt ../../media/slide | grep -v total | head -5")."";
    $cnt = preg_split('/\n/',$x);
    $x = "count ".count($cnt)."<br>";
    $x .= (count($cnt) >= 1) ? pass() : fail();
    $m = str_replace("\n", "<br>", $x)."<br>";
    return $m;
}

//-----------------------------------------------
echo "이미지 업로드와 다운로드 확인,Photo upload<br>";
echo "=================<br>";
echo check_upload()."<br>";
function check_upload() {
    
    $f = 'blackscreen.jpg';
    $c = 'blackscreen test';
    if (! file_exists($f)) return "[$f] is not ready <br>";

    
    $m = "1.Upload ----<br>";
    $x = shell_exec("curl  -F'func=sendcard'  -F'card=@$f' -F'caption=$c' -F'test=on' localhost/signage/s00_signage.php")."<br>";
    $p = preg_match('/photo\":\"(\w*.\w*)\"/', $x, $mc);
    $photo = (isset($mc[1])?$mc[1]:"");
    $x .= (strpos($x, $photo)!==false) ? pass() : fail();
    $m .= $x."<br>";
    $m .= "....Registered file : [$photo].<br><br>";

    $m .= "2.Retrieve uploaded information ----<br>";
    $x  = "...".shell_exec("wget --post-data 'func=getslide&photo=2gcm0ipgcbrg.jpg&action=first' localhost/signage/s00_signage.php -qO-")."<br><br>";
    $x .= (strpos($x, $c)!==false) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br>";

    
    $m .= "3.Check (double) with playlist ----<br>";
    $x = shell_exec("ls -lt ../../media/playlist | head -5");
    $m .= str_replace("\n", "<br>", $x)."<br>";
    $m .= "..Check (double) with captions ----<br>";
    $x = shell_exec("ls -lt ../../media/captions | head -5");
    $x .= (strpos($x, $photo)!==false) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br>";

    $m .= "4.Remove the file from the playlist ----<br>";
    $x  = shell_exec("wget --post-data 'func=rmcard&card=$photo' localhost/signage/s00_signage.php -qO-")."<br>";
    $x .= (strpos($x, $photo)!==false) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br><br>";
        
    $m .= "5.Check removed file from the current file list  ----<br>";
    $x = shell_exec("ls -lt ../../media/playlist | head -3");
    $x .= (strpos($x, $photo)===false) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br>";

    //$m = str_replace("\n", "<br>", $m)."<br>";
    return $m;
}

//-----------------------------------------------
echo "무선상태 점검, Wifi status<br>";
echo "=================<br>";
echo check_wifi()."<br>";
function check_wifi() {
    $m = "";
    $x = '<pre>'.shell_exec("/sbin/ifconfig wlan0").'</pre>';
    preg_match('/addr:(\d+.\d+.\d+.\d+)/',$x,$ip);
    $x .= "WiFi IP : ".(isset($ip[1])?$ip[1]:"N/A")."<br>";
    $x .= (isset($ip[1])) ? enabled() : disabled();
    $m .= str_replace("\n", "<br>", $x)."<br>";
    return $m;
}

//-----------------------------------------------
echo "저장장치 상태 점검(30MB 여유공간 경보), Disk remain spaces<br>";
echo "=================<br>";
echo check_disk()."<br>";
function check_disk() {
    $m = "";
    $x = "Disk free space [" . (int)(disk_free_space(".")/1024/1024) . "] MB<br>";
    $x .= "Disk total space [" . (int)(disk_total_space(".")/1024/1024) . "] MB<br>";
    $x .= (disk_free_space(".") > 30*1024*1024 ) ? pass() : fail();
    $m .= str_replace("\n", "<br>", $x)."<br>";
    return $m;
}

//-----------------------------------------------

?>
    </div><br>
    
</body>
<script>


</script>
</html>