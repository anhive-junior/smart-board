<?php
// technical common functions

function outputJSON($msg, $status = 'error'){

    if ($status == 'error') error_log (print_r($msg, true)." in ".__FILE__); 
    
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

function wget_post( $url,  $data) {
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

function fopen_post( $url,  $data) {
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options);
    return fopen($url, 'r', false, $context);
}

//////////////////////////////////////////////////
// schedule job
//

function add_schedule($fna, $interval){
    error_log ("Start ".__FUNCTION__);
	chmod ( $fna, 0755 );
	
	$lastno = 0;
	$fns = "/etc/hive/tasks/schedule.tasks";
	$fnt = "/etc/hive/tasks/schedule.tasks.new";
	$source = fopen($fns, "r");
	$target = fopen($fnt, "w");
	if ($source) {
		while (($line = fgets($source)) !== false) {

			if (trim($line) == "") continue;
			if (strpos($line, $fna) !== false) {
				error_log("xxxxx:$line");
			 // 기존 등록된 자료는 무시
			} else {
			   fputs($target, $line);
			}
			// process the line read.
			list($ln, $stime) = explode("\t", $line, 2);
			//error_log ("ln is :".$ln);
			$lastno = max($lastno, ($ln=="")?0:$ln);
		}
		//신규건 추가
		$lastno ++;
		$stime = time();
		$etime = time() + 5*365*24*3600; // 5years
	    fputs($target, "$lastno\t$stime\t$etime\t$interval\t$fna\n");
		
		fclose($target);
		fclose($source);
		rename($fnt, $fns );
	} else {
		// error opening the file.
	} 	
	return "enable_schedule";
}

function cancle_scheduler($fna) {
    error_log ("Start ".__FUNCTION__);
	chmod ( $fna, 0644 );
	
	$fns = "/etc/hive/tasks/schedule.tasks";
	$fnt = "/etc/hive/tasks/schedule.tasks.new";
	$source = fopen($fns, "r");
	$target = fopen($fnt, "w");
	if ($source) {
		while (($line = fgets($source)) !== false) {
			
			if (trim($line) == "") continue;
			if (strpos($line, $fna) !== false) {
			 // skip writing
			} else {
			   fputs($target, $line);
			}
			// process the line read.
		}
		fclose($target);
		fclose($source);
		rename($fnt, $fns );
	} else {
		// error opening the file.
	} 	
	return "disable_schedule";
}


///////////////////////////////////////////////
// wifi

function get_wifiinfo() {
    error_log ("Start ".__FUNCTION__ );
	$hostapconf = '/etc/hive/default/hostapd.conf';
	if (!file_exists($hostapconf)) {
		error_log("ERROR(".__FUNCTION__."): hostapd is not defined");
		return;
	}

	//collection
	function gg($p, $t) {
		preg_match($p, $t, $m);
		//error_log($m[1].":".$p.$t);
		return isset($m[1])?$m[1]:null;
	}

    $txt_file = str_replace("\r\n", "\n", file_get_contents($hostapconf));

	$r['ssid']         = gg("/\n\s*ssid=(?<ssid>.*)\n/", $txt_file);
	$r['wpa_passphrase']= gg("/\n\s*wpa_passphrase=(?<wpa_passphrase>.*)\n/", $txt_file);

	//IP 정보 점검
	return $r;
}

function set_wifiinfo($ssid, $pass) {
    error_log ("Start ".__FUNCTION__ );
	$hostapconf = '/etc/hive/default/hostapd.conf';
	if (!file_exists($hostapconf)) {
		error_log("hostapd is not defined");
		return;
	}
	$rl = readlink($hostapconf);
	
	if (substr($rl,0,1)=="/") {
		$hostapconf = $rl;
	} else {
		$hostapconf = dirname($hostapconf).'/'.$rl;
	}
	error_log("hostapd is [$hostapconf]");
	
	function gg($p, $t) {
		preg_match($p, $t, $m);
		//error_log($m[1].":".$p.$t);
		return isset($m[1])?$m[1]:null;
	}
	
    $txt_file = str_replace("\r\n", "\n", file_get_contents($hostapconf));

	$c_ssid = gg("/\n\s*ssid=(?<ssid>.*)\n/", $txt_file);
	$c_pass = gg("/\n\s*wpa_passphrase=(?<wpa_passphrase>.*)\n/", $txt_file);

	error_log ("READ: $c_ssid, $c_pass");
	//if data same as it is
	if ($ssid.';'.$pass == $c_ssid.';'.$c_pass) return true;

	$temp = "/run/shm/temp.conf";
	$cmd[] = "sed \"s/ssid=$c_ssid/ssid=$ssid/g\" $hostapconf > $temp";
	$cmd[] = "sed \"s/wpa_passphrase=$c_pass/wpa_passphrase=$pass/g\" $temp > $hostapconf";
	foreach($cmd as $c) {
		error_log($c);
		error_log(shell_exec($c));
	}
}


//////////////////////////////////////////////////
// upnp
//
function clear_upnp_except_for($excp_port) {
	$internal_ip = $_SERVER['SERVER_ADDR']; 
	$shcmd = "/usr/bin/upnpc";
	$ret = split("\n",shell_exec("$shcmd -l"));
 
	foreach ($ret as $r) {
		//echo $r;
		preg_match ("/TCP\s*(?<port>\d*)->$internal_ip:80/", $r, $m);
		if (!isset($m['port'])) continue;
		if ($excp_port == $m['port']) continue;
		set_upnp(80, $m['port'], false);
	}
}

function is_port_engaged($port) {
	$shcmd = "/usr/bin/upnpc";
	$ret = split("\n",shell_exec("$shcmd -l"));
 
	foreach ($ret as $r) {
		preg_match ("/(TCP|UDP)\s*(?<port>\d*)->/", $r, $m);
		if (!isset($m['port'])) continue;
		if ($port == $m['port']) return true;
	}
	return false;
}

function get_router_external_ip() {
	$shcmd = "/usr/bin/upnpc";
	$ret = split("\n",shell_exec("$shcmd -l"));
 
	foreach ($ret as $r) {
		//echo $r."<br>";
		preg_match ("/ExternalIPAddress = (?<ip>.*)$/", $r, $m);
		if (!isset($m['ip'])) continue;
		return $m['ip'];
		//echo "=====>".$m['ip']."<br>";
	}
	return "";
}

function get_local_ip() {
	$shcmd = "/usr/bin/upnpc";
	$ret = explode("\n",shell_exec("$shcmd -l"));
 
	foreach ($ret as $r) {
		//echo $r."<br>";
		preg_match ("/Local LAN ip address : (?<ip>.*)$/", $r, $m);
		if (!isset($m['ip'])) continue;
		return $m['ip'];
		//echo "=====>".$m['ip']."<br>";
	}
	return "";
}


function is_rlogin_available() {
	$test="TEST";
	$lt = wget_post("http://localhost/signproxy/s00_access.php?test=$test", 
						array());
	return ($lt == "$test:OK")?true:false;
}

//외부적용을 확인한다.
function set_upnp($int_port, $ext_port, $status) {
	$internal_ip = get_local_ip(); 
	$shcmd = "/usr/bin/upnpc";
	if (! file_exists($shcmd)) return;
	if ($status) $cmd = "$shcmd -a $internal_ip $int_port $ext_port TCP";
	else         $cmd = "$shcmd -d $ext_port TCP";
	error_log($cmd);
	
	$ret = shell_exec($cmd);
}

function get_upnp_port($external_port) {
    
    while($external_port==0 || is_port_engaged($external_port)) {
        $external_port = 2012+rand(1,2000);
        error_log("===new port [$external_port] for internet.");
    }
    
    return $external_port;
}	

/////////////////////////////////////
// 시스템 중단
$services['logout'] = '_logout';
function _logout() {
    error_log ("Start ".__FUNCTION__ );

	// remove all session variables
	session_unset(); 

	// destroy the session 
	session_destroy(); 
    // Success!
    outputJSON('logged out', 'success');
}

function get_extension($imagetype)   {
   if(empty($imagetype)) return false;
   switch($imagetype)
   {
	   case 'image/bmp': return 'bmp';
	   case 'image/cis-cod': return 'cod';
	   case 'image/gif': return 'gif';
	   case 'image/ief': return 'ief';
	   case 'image/jpeg': return 'jpg';
	   case 'image/pipeg': return 'jfif';
	   case 'image/tiff': return 'tif';
	   case 'image/x-cmu-raster': return 'ras';
	   case 'image/x-cmx': return 'cmx';
	   case 'image/x-icon': return 'ico';
	   case 'image/x-portable-anymap': return 'pnm';
	   case 'image/x-portable-bitmap': return 'pbm';
	   case 'image/x-portable-graymap': return 'pgm';
	   case 'image/x-portable-pixmap': return 'ppm';
	   case 'image/x-rgb': return 'rgb';
	   case 'image/x-xbitmap': return 'xbm';
	   case 'image/x-xpixmap': return 'xpm';
	   case 'image/x-xwindowdump': return 'xwd';
	   case 'image/png': return 'png';
	   case 'image/x-jps': return 'jps';
	   case 'image/x-freehand': return 'fh';
	   default: return false;
   }
}

function get_free_mem(){
	$mem = file_get_contents("/proc/meminfo");
	$list = preg_split('/\n/',$mem);
	$sum = 0;
	foreach($list as $l) {
		//echo $l."===>";
		if (!preg_match("/Free:\s*(?<size>\d*)/", $l, $m)) continue;
		//echo print_r( $m,true)."<br>";
		$sum += $m['size'];
		//echo $sum;
	}
	return $sum;
}
?>