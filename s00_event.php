<?php 
include_once("lib/get_config.php");

// Output JSON
function output($msg){
    header('Content-Type: text/htm');
    die ($msg);
}

function wget_post( $url,  $data) {
    // use key 'http' even if you send the request to https://...
    //if ($data == null) $data = array(""=>"");
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            //'header'=>  "Content-Type: application/json\r\n" .
            //            "Accept: application/json\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

$func= isset($_GET['func'])?$_GET["func"]:"none";

if (strcmp($func, 'gen_playlist') == 0) {
    error_log ("Start ".__FUNCTION__);
    //$time : 0 no limit in seconds
    //$conut : 0 no limit in seconds

    $m = wget_post("http://localhost/signage/s00_signage.php", 
                    array("func"    => "renewal_playlist"));
    output($m);
} else
if (strcmp($func, 'none') == 0) {
    output("Func is not defined!");
} else{
    // Success!
    output('Undefined function[' . $func.'].');
}


?>
