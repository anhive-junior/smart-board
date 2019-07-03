<?php
if (!isset($_SESSION['access'])) {
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/access.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
	$config = json_decode(file_get_contents($sfile), true);
	$config = $config[$pname];

	$surprisebox_code    = $_SESSION['surprisebox_code'] = $config['surprisebox_code'];
	$gate_server     = $_SESSION['gate_server'] = $config['gate_server'];
	//$external_ip     = $_SESSION['external_ip'] = $config['external_ip'];
	$external_port   = $_SESSION['external_port'] = $config['external_port'];
	$access_code     = $_SESSION['access_code'] = $config['access_code'];
	$admin_code      = $_SESSION['admin_code'] = $config['admin_code'];
	$sam_code        = $_SESSION['sam_code'] = $config['sam_code'];
	$factory_code     = $_SESSION['factory_code'] = $config['factory_code'];
	$ssid            = $_SESSION['ssid'] = $config['ssid'];
	$wifi_password   = $_SESSION['wifi_password'] = $config['wifi_password'];
	$_SESSION['access'] = $pname;
} else {
	$surprisebox_code    = $_SESSION['surprisebox_code'];
	$gate_server     = $_SESSION['gate_server'];
	//$external_ip     = $_SESSION['external_ip'];
	$external_port   = $_SESSION['external_port'];
	$access_code     = $_SESSION['access_code'];
	$admin_code      = $_SESSION['admin_code'];
	$sam_code        = $_SESSION['sam_code'];
	$factory_code     = $_SESSION['factory_code'];
	$ssid            = $_SESSION['ssid'];
	$wifi_password   = $_SESSION['wifi_password'];
}
?>