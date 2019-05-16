<!--
/**
 * AnHive Co., Ltd.
 * LICENSE: This source file is subject to version 0.1 of the AnHive license. 
 * If you did not receive a copy of the AnHive License and are unable to obtain it
 * please send a note to anhive@gmail.com so we can mail you a copy immediately.
 *
 * @author     AnHive Co., Ltd <anhive@gmail.com>
 * @copyright  2013-2015 AnHive Co., Ltd
 * @license    http://www.anhive.com/license/1_01.txt  AnHive License 1.01
 */
-->
<?php 
include_once("lib/get_config.php");

// Output JSON
function outputJSON($msg, $status = 'error'){
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

$dir= isset($_POST['dir'])?$_POST["dir"]:$config_contents;
$func= isset($_POST['func'])?$_POST["func"]:"upload";

if (strcmp($func, 'upload') == 0) {
    // Upload file
    // Check for errors
    if($_FILES['SelectedFile']['error'] > 0){
        outputJSON('An error occurred when uploading.');
    }

    // Check filesize
    if($_FILES['SelectedFile']['size'] > 2000000000){
        outputJSON('File uploaded exceeds maximum upload size.');
    }

	if (preg_match('/.mp4$|.mkv$|.mov$|.MOV$/', 
				$_FILES["SelectedFile"]["name"]) $dir = $config_video;
	else $dir = $config_slide;
				
    $file = $_FILES["SelectedFile"]["name"];
    $file = str_replace(" ","",$file);
    
    if(!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], 
            $dir .'/'. $file)){
        outputJSON('Error uploading file - check destination is writeable.');
    }

    // Success!
    outputJSON('File uploaded : [' . $dir .'/'. $file . '].', 'success');
} else if (strcmp($func, 'delete') == 0) {
    // Upload file
    // Check for errors

    $file= isset($_POST['name'])?$_POST["name"]:"";
	if ($file == "") outputJSON('File missing error : [' . $dir .'/'. $file . '].');
    
	error_log($dir .'/'. $file);
	unlink($dir .'/'. $file);
	
    // Success!
    outputJSON('File delete : [' . $dir .'/'. $file . '].', 'success');
} else{

    // Success!
    outputJSON('Undefined function[' . $func.'].');
}


?>
