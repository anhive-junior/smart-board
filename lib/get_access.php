<?php
if (!isset($_SESSION['access'])) {
    $pname = file_get_contents("custom/default");
    $sfile = "custom/".$pname."/access.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $config = json_decode(file_get_contents($sfile), true);
    $config = $config[$pname];

    $access_code     = $_SESSION['access_code'] = $config['access_code'];
    $admin_code      = $_SESSION['admin_code'] = $config['admin_code'];
    $ssid            = $_SESSION['ssid'] = $config['ssid'];
    $wifi_password   = $_SESSION['wifi_password'] = $config['wifi_password'];
    $_SESSION['access'] = $pname;
} else {
    $access_code     = $_SESSION['access_code'];
    $admin_code      = $_SESSION['admin_code'];
    $ssid            = $_SESSION['ssid'];
    $wifi_password   = $_SESSION['wifi_password'];
}
?>