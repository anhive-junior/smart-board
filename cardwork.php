<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php"); // outputJSON();

if (isset($_POST['init']) && $_POST['init'] == 1){
	$data=array(
		"photo" => array($photo, "profile"),
		"subject" => $subject,
		"owner" => $owner,
		"footer" => $footnote
	);
	outputJSON($data, "success");
} 

if(isset($_SESSION['uselevel']) && $_SESSION['uselevel']>1){ // rmcard(); privilege
	$data=array(
		"func" => "rmcard()",
		"spanInner" => "회수",
		"script" => "var rmcard = function(msg){
		
        var data = new FormData();
        data.append('func','rmcard');
		data.append('card', _photo.alt);
		
		POST('s00_signage.php', data, 
			function (resp) {  
			    document.getElementById('vlog').innerHTML=resp.data;
				getslide('first');
		});
    }"
	);
	outputJSON($data, "success");
}

?>