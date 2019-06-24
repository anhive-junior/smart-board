<?php
session_start(); 
session_unset(); 
error_log(__FILE__."::".session_id());

include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

    

if (isset($_POST['init'])&& $_POST['init'] == 1){
	$data=array(
	    "title" => $title,
		"sodata" => array($subject,$owner),
		"photo" => array($photo, "profile","width:50%;"),
		"footer" => $footnote
		);
		outputJSON($data, "success");
}

if(file_exists("/run/shm/participants.lst.freezing") && (isset($_POST['file']) && $_POST['file'] == 1)){ 
    $data = "점검중";
	outputJSON($data,"success");
}else{
	$data = "0";
	outputJSON($data,"success");
}

if(isset($_POST['disk']) && $_POST['disk'] == 1){
	$freespace = disk_free_space(".")/1024;
	if ( $freespace < 16 ) { 
	$data = "저장공간 부족";
	outputJSON($data,"success");
	}else{
		$data = "0";
		outputJSON($data,"success");
	}
}

	

			
?>

   
