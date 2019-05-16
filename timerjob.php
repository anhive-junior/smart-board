<?php
include_once("lib/lib_common.php");

/////////////////////////////////////
// 인터넷 접속 지원
//간접 접속 지원
//직접 접속 지원
function set_internet_access() {
    error_log ("Start ".__FUNCTION__);

	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/access.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
    $access_org = json_decode(file_get_contents($sfile), true);
    $access = $access_org[$pname];
    
	//외부 접속 지원 여부 확인
    /*
	if (!is_rlogin_available()) {
		error_log("Warning: external access service is not available");
		return false;
	}
	error_log("===remote login available.");
    */
	
	//check_external_gate_available
	$external_ip=0;
	
	$public_ip = wget_post("http://thebolle.com/ip.php", array());
	$external_ip = get_router_external_ip();

	//internet access
	if ($public_ip != $external_ip) {
		error_log("Warning: Subnet is too deep to open to internet access");
		error_log("proxy will be used");
		//set 0 when it does not accessible direct from internet

		//유용한 Proxy 정보를 얻어 오자
		set_proxy_access();
	} else {
		// direct access prior to proxy access
		$ext_port = set_direct_access($access, $external_ip);
		if ($access_org[$pname]['external_port'] != $ext_port) {
			$access_org[$pname]['external_port'] = $ext_port;
			file_put_contents($sfile, json_encode($access_org));
		}
	}

	//wireless access
	set_wifiinfo($access['ssid'], 
				 $access['wifi_password']) ;
	return;
}

// upnp port를 활용하여 접속
function set_proxy_access($access) {
    error_log ("Start ".__FUNCTION__);

	//thebolle에 어떤 프락시를 사용해야하는지 확인하기
	$r = wget_post('http://'.$access['gate_server']
							.'/signgate/s00_signgate.php', 
				array("func" => "req_proxyaddr",
						"mbid" => $access['surprisebox_code'],
						"serial"=> file_get_contents("/etc/machine-id")
					 )
				 ); 
	error_log("proxy access request response :  $r");
	//프락시로부터 정보를 가져오기
	$rt = json_decode($r, true)['data'];

	//request to access proxy instead
	$r = wget_post('http://'.$access['gate_server']
							.'/signgate/s00_signgate.php', 
				array("func" => "set_mbaddress",
						"mbid" => $access['surprisebox_code'],
						"ip"   => $rt['ip'],
						"port" => $rt['port'],
						"path" => $rt['path'],
						"api" => $rt['api'], //"s00_access.php",
						"usrtag"=>$rt['usrtag'], //"user_code",
						"acctag"=>$rt['acctag'] //"input_code"
						)
				); 
	error_log("proxy access registration response :  $r");
	// execute collecting process automatically
	$con  = "#!/bin/bash\n";
	$con  .= "cd /crr\n";
	$con  .= "sudo rsync -avz -e \"ssh -p".$rt['port'].
			"\" anhive@".$rt['ip'].":/crr/".$rt['md5']."/* ./".$rt['md5']."\n";

	$fna = "/etc/hive/tasks/crr_download.sh";
	file_put_contents($fna, $con);
	$m = add_schedule($fna, 30);
	
	return true;
}

// upnp port를 활용하여 접속
function set_direct_access($access, $external_ip) {
    error_log ("Start ".__FUNCTION__);
	
	$changed = false;
	
	$external_port=0;
	if(!isset($access['external_port']	) 
		|| $access['external_port'] == 0 ) {
		//$external_port = get_upnp_port();
		while($external_port==0 || is_port_engaged($external_port)) {
			$external_port = 2012+rand(1,2000);
			error_log("===new port [$external_port] for internet.");
		}
		$access['external_port'] = $external_port;
		$changed = true;
	}
	set_upnp(80, $access['external_port'], true);
	error_log("===Service open a port["
				.$access['external_port']
				."] for internet.");
	
		//set external access;
	$r = wget_post('http://'.$access['gate_server']
							.'/signgate/s00_signgate.php', 
				array("func" => "set_mbaddress",
						"mbid" => $access['surprisebox_code'],
						"ip"   => $external_ip,
						"port" => $access['external_port'],
						"path" => "signage",
						"api"=>"s00_access.php",
						"usrtag"=>"user_code",
						"acctag"=>"input_code")); 
						
	error_log("remote access registration response :  $r");
    return $access['external_port'];
};

set_internet_access();

?>