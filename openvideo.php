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
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <script type="text/javascript" src="signage.base.js"></script>
	<link rel="stylesheet" type="text/css" href="signage.base.css">
    <title>AnHive, Show Video</title>
    <style>
       video {top:0;left:0;width:100%;max-width:100%;height:auto;border:1px solid yellow; height:100%; position:absolute; }
       body { margin:0; height:100%;width:100%}
    </style>
</head>
<body>

<?php
    $name = $_GET["name"];
    if (strpos($name, 'http') !== false) {
        $uri = $name;
    } else {
        //$filename = basename ($name);  //c
		$filename = $name;  //c
        $dir= isset($_GET['dir'])?$_GET['dir']:'';
        //$script_path = dirname(trim($_SERVER['SCRIPT_NAME']));
        //if ($script_path != "") $dir = $script_path."/".$dir;
        $uri = $dir."/".$filename;
    }
?>
	<div style="text-align:center;">
    <video id="video1" controls autoplay >
     <source src=<?php echo $uri ?> type='video/mp4' />  
    </video>
	<div style="margin-top:10px;"><!-- upper line feed --></div>
	</div>

</body>
</html>
