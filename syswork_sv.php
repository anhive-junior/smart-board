<?php
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

$trace=true;

function syswork_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
}
function make_folder($path) {
    if ( file_exists( $path) ) return;
    if ( ! file_exists( dirname($path) )) make_folder(dirname($path));
    mkdir ($path);
    //chmod($path, 0606);    
}

//////////////////////////////////////////////////////////////////////////
// service list
//        request                          function            auth:ACRUDS

		 
$services['test'] = array('fn' => '_test',         'auth' =>'ACRUDS');			   
function _test() { 
    syswork_log ("Start ".__FUNCTION__);
    throw new Exception ( __FILE__.'is Available.');
};


/////////////////////////////////////
//  저장장치 정보 수집
$services['get_resource'] = array('fn' => '_get_resource', 'auth' =>'ACRUDS');			   
function _get_resource() { 
    syswork_log ("Start ".__FUNCTION__);

	global $config_contents;
    
    $t = disk_total_space(".")/1073741824;
    $f = disk_free_space(".")/1073741824;
    $u = $t - $f;
    $m = shell_exec("du $config_contents -sm")/1024;
	error_log("du $config_contents -sm [$m]");
    //$g = shell_exec("du /var/log -sm")/1024;
   
    $arr = array ( 
        'total' => sprintf("%.1fG",$t)
       ,'free' => sprintf("%.1fG",$f)
       ,'used' => sprintf("%.1fG",$u)
       ,'media' => sprintf("%.1fG",$m)
       //,'garbage' => sprintf("%.1fG",$g)
       );
       
    return $arr;
};

	
/////////////////////////////////////
// 시스템 정보 수집
// 2014-10-01, by AnHive
$services['get_netinfo'] = array('fn' => '_get_netinfo', 'auth' =>'__RU__');
 function _get_netinfo() {
    error_log ("Start ".__FUNCTION__ );
	function x($a) {
	   foreach ($a as $k => $v) {
		   if ($v != "") return $v;
	   } 
	   return "";
	}
	//collection
	function gg($p, $t) {
		preg_match($p, $t, $m);
		//error_log($m[1].":".$p.$t);
		return isset($m[1])?$m[1]:null;
	}
	
	
	//AP 작동상태 점검
	if ( shell_exec("pgrep hostapd") > 0 ) $ap_use = true;
    $txt_file = str_replace("\r\n", "\n", 
		file_get_contents('/home/pi/ap/hostapd_sign.conf'));
    //$txt_file = str_replace("\r\n", "\n", 
	//	file_get_contents('/etc/hive/default/hostapd.conf'));
	//AP 기본 점검
	//echo $txt_file;
    //preg_match_all(
    //    "/"
	$t = $txt_file;
	$r['interface']    = gg("/interface=(?<interface>.*)\n/", $t);
	$r['driver']       = gg("/driver=(?<driver>.*)\n/", $t);
	$r['hw_mode']      = gg("/hw_mode=(?<hw_mode>.*)\n/", $t);
	$r['ieee80211n']   = gg("/ieee80211n=(?<ieee80211n>.*)\n/", $t);
	$r['wmm_enabled']  = gg("/wmm_enabled=(?<wmm_enabled>.*)\n/", $t);
	$r['channel']      = gg("/channel=(?<channel>.*)\n/", $t);
	$r['ssid']         = gg("/ssid=(?<ssid>.*)\n/", $t);
	$r['ignore_broadcast_ssid']    
			= gg("/ignore_broadcast_ssid=(?<ignore_broadcast_ssid>.*)\n/", $t);
	$r['wpa']          = gg("/wpa=(?<wpa>.*)\n/", $t);
	$r['wpa_passphrase']= gg("/wpa_passphrase=(?<wpa_passphrase>.*)\n/", $t);
	$r['wpa_key_mgmt'] = gg("/wpa_key_mgmt=(?<wpa_key_mgmt>.*)\n/", $t);
	$r['wpa_pairwise'] = gg("/wpa_pairwise=(?<wpa_pairwise>.*)\n/", $t);
	$r['rsn_pairwise'] = gg("/rsn_pairwise=(?<rsn_pairwise>.*)\n/", $t);

	
	//IP 정보 점검
    $arr['ap'] = array(
        'ap_use'      => $ap_use?1:0,
        'sec_use'     => $r['wpa']?1:0,
        'interface'   => $r['interface'     ],
        'driver'      => $r['driver'        ],
        'hw_mode'     => $r['hw_mode'       ],
        'ieee80211n'  => $r['ieee80211n'    ],
        'wmm_enabled' => $r['wmm_enabled'   ],
        'channel'     => $r['channel'       ],
        'ssid'        => $r['ssid'          ],
        'ignore_broadcast_ssid'=> $r['ignore_broadcast_ssid'],
        'wpa'                  => $r['wpa'                  ],
        'wpa_passphrase'       => $r['wpa_passphrase'       ],
        'wpa_key_mgmt'         => $r['wpa_key_mgmt'         ],
        'wpa_pairwise'         => $r['wpa_pairwise'         ],
        'rsn_pairwise'         => $r['rsn_pairwise'         ]
    );

	$r="";
	
	//인터넷 작동상태 점검
	 // $external_ip = wget_post( "http://thebolle.com/ip.php",  array("x"=>"x"));
	 //$external_ip = shell_exec ( "curl -s thebolle.com/ip.php;echo" );
	// error_log("_SESSION['external_port']=".$_SESSION['external_port']);
    
	// $external_port = $_SESSION['external_port'];

	
	//IP 정보 점검
    // $arr['it'] = array(
        // 'external_ip' => $external_ip,
		// 'ext_use'     => $external_port?1:0,
        // 'external_port' => $external_port
    // );
	//error_log(print_r($arr, true));

	
	//////////////////////
	//interface 정보

	$if_file="/etc/network/interfaces";
	$ifc = file_get_contents($if_file);
	$ifs = explode("auto", $ifc);
	//var_dump($ifs);

	$i=0;
	// $r['int_use']      = (strlen($external_ip)>0)?1:0;
	do {
		
		$t = trim($ifs[$i++])."\n";
		if (strpos($t, "#")!==false) {continue;}
		//var_dump($t);
		
		$r['interface']    = gg("/^(?<interface>.*)\n/", $t);
		if ($r['interface'] != "p2p1") {continue;}
		
		$r['device']       = gg("/\niface (?<device>.*).inet/", $t);
		$r['operation']    = gg("/inet (?<operation>.*)\n/", $t);
		$r['ip_address']   = gg("/\naddress (?<address>.*)\n/"		, $t);
		$r['ip_mask']      = gg("/\nnetmask (?<netmask>.*)\n/", $t);
		$r['ip_gate']      = gg("/\ngateway (?<gateway>.*)\n/", $t);
		$r['ip_dns1']      = gg("/\ndns-nameservers (?<dns-nameservers>.*) /", $t);
		$r['ip_dns2']      = gg("/\ndns-nameservers .* (?<dns-nameservers>.*)\n/", $t);
		$r['wpa_essid']    = gg("/\nwpa-essid (?<wpa_ssid>.*)\n/", $t);
		$r['wpa_key_mgmt'] = gg("/\nwpa-key-mgmt (?<wpa_key_mgmt>.*)\n/", $t);
		$r['wpa_group']    = gg("/\nwpa-group (?<wpa_group>.*)\n/", $t);
		$r['wpa_psk']      = gg("/\nwpa-psk (?<wpa_psk>.*)\n/", $t);
		 
		$arr['if'] = $r; 
		break;
	} while ( $i< sizeof($ifs) );	
	
	return $arr;
}

/////////////////////////////////////
// 시스템 정보 수집
// 2014-10-01, by AnHive
$services['set_netinfo'] = array('fn' => '_set_netinfo',         'auth' =>'A_____');
function _set_netinfo() {

	$ap_use          = isset($_POST['ap_use'        ])?$_POST['ap_use'        ]:""; 
    $ssid            = isset($_POST['ssid'          ])?$_POST['ssid'          ]:""; 
    $sec_use         = isset($_POST['sec_use'       ])?$_POST['sec_use'       ]:""; 
    $wpa_passphrase  = isset($_POST['wpa_passphrase'])?$_POST['wpa_passphrase']:""; 
    $wpa             = isset($_POST['wpa'           ])?$_POST['wpa'           ]:""; 
    $int_use         = isset($_POST['int_use'       ])?$_POST['int_use'       ]:""; 
    $interface       = isset($_POST['interface'     ])?$_POST['interface'     ]:""; 
    $operation       = isset($_POST['operation'     ])?$_POST['operation'     ]:""; 
    $ip_address      = isset($_POST['ip_address'    ])?$_POST['ip_address'    ]:""; 
    $ip_mask         = isset($_POST['ip_mask'       ])?$_POST['ip_mask'       ]:""; 
    $ip_gate         = isset($_POST['ip_gate'       ])?$_POST['ip_gate'       ]:""; 
    $ip_dns1         = isset($_POST['ip_dns1'       ])?$_POST['ip_dns1'       ]:""; 
    $ip_dns2         = isset($_POST['ip_dns2'       ])?$_POST['ip_dns2'       ]:""; 
    // $external_ip     = isset($_POST['external_ip'   ])?$_POST['external_ip'   ]:""; 
    // $external_port   = isset($_POST['external_port' ])?$_POST['external_port' ]:""; 
    // $ext_use         = isset($_POST['ext_use'       ])?$_POST['ext_use'       ]:""; 

	//write_ap --wpa_passphrase YONGSOO
	function change_ap($key, $value) {
		if ($key == "") return;
		$cmd = "/etc/hive/bin/write_ap --$key $value";
		shell_exec("sudo ".$cmd);
	}
	if ($ap_use) {
		change_ap('wpa', $wpa);
		change_ap('ssid', $ssid);
	}
	
	if ($sec_use) {
		if ( strlen($wpa_passphrase)<8 ) {
				outputJSON("WiFi 비밀번호는 최소 8자입니다");
		}
		change_ap('wpa', $wpa);
		change_ap('ssid', $ssid);
		change_ap('wpa_passphrase', $wpa_passphrase);
		change_ap('-u', "wpa");
		change_ap('-u', "wpa_passphrase");
		change_ap('-u', "wpa_key_mgmt");
		change_ap('-u', "wpa_pairwise");
		change_ap('-u', "rsn_pairwise");
		error_log ("enable security");
	} else {
		change_ap('-m', "wpa");
		change_ap('-m', "wpa_passphrase");
		change_ap('-m', "wpa_key_mgmt");
		change_ap('-m', "wpa_pairwise");
		change_ap('-m', "rsn_pairwise");
		error_log ("disable security");
	}

	// set_upnp(80, $external_port, $ext_use);


	// change /etc/network/interfaces
	function change_if($interface, $key, $value) {
		if ($key == "") return;
		$cmd = sprintf('/etc/hive/bin/write_if %s --%s %s', $interface, $key, $value);
		//error_log($cmd);
		shell_exec("sudo ".$cmd);
	}
	if ($int_use) {
		// set interface value
		//change_if('interface', $interface);
		if ($operation!="") change_if($interface, "iface", $operation);
		if ($ip_address!="") change_if($interface, 'address', $ip_address);
		if ($ip_mask!="") change_if($interface, 'netmask', $ip_mask);
		if ($ip_gate!="") change_if($interface, 'gateway', $ip_gate);
		
		// open network service 
		shell_exec("sudo /etc/hive/bin/shellcmd --direct /sbin/ifdown $interface ");
		shell_exec("sudo /etc/hive/bin/shellcmd --direct /sbin/ifup $interface");
		
	} else {
		// open network service 
		if ($operation!="") change_if($interface, "iface", $operation);
		if ($ip_address!="") change_if($interface, 'address', $ip_address);
		if ($ip_mask!="") change_if($interface, 'netmask', $ip_mask);
		if ($ip_gate!="") change_if($interface, 'gateway', $ip_gate);
		
		$r = shell_exec("sudo /etc/hive/bin/shellcmd --direct /sbin/ifdown $interface");
		//error_log($r);
	}
	
	return "Net information stored.";
}

//////////////////////////////////////
// EBS 등 외부 서비스를 저장
/////////////////////////////////////////
$services['set_service'] = array('fn' => '_set_service',         'auth' =>'A_____');
function _set_service() { 
    syswork_log ("Start ".__FUNCTION__);
    
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/service.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $service = json_decode(file_get_contents($sfile), true);
	
	
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        $service[$pname][$k] = $v;
		//error_log( "$k => $v");
		
		$_SESSION[$k] = $v;
		
    }
    file_put_contents($sfile, json_encode($service));
	@unlink("/run/shm/service.conf");
	
	return "Service stored.";
};

$services['get_reserve'] = array('fn' => '_get_reserve',         'auth' =>'A_____');
function _get_reserve() { 
    syswork_log ("Start ".__FUNCTION__);
    
	$pname = isset($_POST['profile'])?$_POST['profile']
				:file_get_contents("custom/default");
	$sfile = "custom/".$pname."/service.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $service = json_decode(file_get_contents($sfile), true);
    $service = $service[$pname];
	
	
    $t = disk_total_space(".")/1073741824;
    $f = disk_free_space(".")/1073741824;
    $u = $t - $f;
    $m = shell_exec("du -sm")/1024; //가용량
	$service["storage_status"] 
		= "전체(".sprintf("%.1fG",$t)
			.")/자료(".sprintf("%.1fG",$m)
			.")/공간(".sprintf("%.1fG",$f).")";;

	return $service;
};

//////////////////////////////////////
// EBS 등 외부 서비스를 저장
/////////////////////////////////////////
$services['set_reserve'] = array('fn' => '_set_reserve',         'auth' =>'A_____');
function _set_reserve() { 
    syswork_log ("Start ".__FUNCTION__);
    
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/service.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $service = json_decode(file_get_contents($sfile), true);
	
	
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        $service[$pname][$k] = $v;
		//error_log( "$k => $v");
		
		$_SESSION[$k] = $v;
		
    }
    file_put_contents($sfile, json_encode($service));
	@unlink("/run/shm/service.conf");
	
	return "Service stored.";
};

$services['get_service'] = array('fn' => '_get_service',         'auth' =>'A_____');
function _get_service() { 
    syswork_log ("Start ".__FUNCTION__);
    
	$pname = isset($_POST['profile'])?$_POST['profile']
				:file_get_contents("custom/default");
	$sfile = "custom/".$pname."/service.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $service = json_decode(file_get_contents($sfile), true);
    $service = $service[$pname];

	return $service;
};

$services['reboot'] = array('fn' => '_reboot',         'auth' =>'A_____');
function _reboot () {
	if (!file_exists("/etc/hive/bin/shellcmd")) 
		return "Shell Command is not allowed to execute";
    exec("sudo /etc/hive/bin/shellcmd reboot");
    return 'Rebooting the device.';
}

$services['shutdown'] = array('fn' => '_shutdown',         'auth' =>'A_____');
function _shutdown() {
	if (!file_exists("/etc/hive/bin/shellcmd")) 
		return "Shell Command is not allowed to execute";
    exec("sudo /etc/hive/bin/shellcmd shutdown");
    return 'Shutting down the device.';
}

$services['command'] = array('fn' => '_command',         'auth' =>'A_____');
function _command() {
	if (!file_exists("/etc/hive/bin/shellcmd")) 
		return "Shell Command is not allowed to execute";
    $command= getPOST('command');
    $msg = shell_exec("sudo /etc/hive/bin/shellcmd $command");
	return $msg;
}

/////////////////////////////////////
// 디스크 관리 정책 설정
$services['set_diskpolicy'] = array('fn' => '_set_diskpolicy',         'auth' =>'A_____');
function _set_diskpolicy() { 
    error_log ("Start ".__FUNCTION__);
    
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/storage.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $storage = json_decode(file_get_contents($sfile), true);
	
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        $storage[$pname][$k] = $v;
		error_log( "$k => $v");
    }
    file_put_contents($sfile, json_encode($storage));
	
    outputJSON($sfile, 'success');
};

/////////////////////////////////////
// 디스크 관리 정책 설정
$services['get_diskpolicy'] = array('fn' => '_get_diskpolicy',         'auth' =>'A_____');
function _get_diskpolicy() { 
    error_log ("Start ".__FUNCTION__);
    
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/storage.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $storage = json_decode(file_get_contents($sfile), true);
    $storage = $storage[$pname];
    //file_put_contents($sfile, json_encode($storage));
	
    outputJSON($storage, 'success');
};

/////////////////////////////////////
// execute services
error_log($_POST['func']);
$func= isset($_POST['func'])?$_POST["func"]:"test";
if (!isset($services[$func]['fn'])) 
     outputJSON("Undefined service[$func].");
	
try {
	// check access right
	// return as web message
    $r = call_user_func( $services[$func]['fn']);
	if (!is_null($r)) {
		outputJSON($r, 'success');
	}
	
} catch (Exception $e) {
    outputJSON($e->getMessage());
}

?>

