<?php 
include_once("lib/get_config.php");
include_once("lib/get_resource.php");

$trace=true;

function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
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

function fresh_upnp() {
	$r = wget_post("http://localhost/signage/s00_signage.php", 
					array("func" => "set_code")); 
}

fresh_upnp();

?>