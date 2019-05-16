<?php 
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
session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");

$contents=isset($_GET['v'])?"video":"slide";

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <script type="text/javascript" src="signage.base.js"></script>
	<link rel="stylesheet" type="text/css" href="signage.base.css">
    <title>AnHive, List Media Files</title>
	<style>
	.img_block{	position: relative;	width: 80px; height: 80px;}
	.img_base{	width: 100%;height: 100%;background: blue;}
	.img_tag{position: absolute;bottom: 5px;right: 5px;	width: 24px;
		height: 24px;background: yellow;}
	.img_tag:active {background: red;}
	</style>	
</head>
<body >
    <div class="container">
        <div class="contents">
		
			<!-- head note -->
			<div class='headnote' onclick="javascript:document.location.href='home.php'">
				<img src="<?=$photo?>" alt="profile" style="height:15px;">
				<span class="input_title"><?=$subject?></span> 
				<span class="input_title"><?=$owner?></span>
			</div>
			<div style="margin-top:10px;"><!-- upper line feed --></div>

			<div id="packinglist" style > </div>
			
			<div style="display: inline-block;padding-top:10px;width:100%;text-align:center;">
	
			</div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
	<div id="html_data_model" style="display:none;">
		<div id="block">
			<div style="margin:10px;padding:2px;"><!-- upper line feed --></div>
			<div style="background-color:#ccc;">
				*0*block*0*<br>
			</div>
			<div style="margin:2px;padding:2px;"><!-- upper line feed --></div>
		</div>
		<div id="image">
			<div  style="display:inline-block;">
				<div class="img_block">
					<img class="img_base" src="*0*thumb*0*" onclick="window.open( 'openimages.php?name=*0*file*0*', '_self');" alt="thumbnail"> 
				</div> 
				<div style="font-size:0.7em;text-align:center;padding-buttom:10px;">
					<span style="">*0*elapse*0*</span>
				</div> 
			</div>
		</div>
	</div>
</body>
<script>

var getlist = function () {
	
	var data = new FormData();
	data.append('func', 'getplaylist');
	data.append('contents', '<?=$contents?>');
	var request = new XMLHttpRequest();
	request.onreadystatechange = function(){
		if(request.readyState == 4){
			try {
				var resp = JSON.parse(request.response);
			} catch (e){
				var resp = {
					status: 'error',
					data: 'Unknown error occurred: [' + request.responseText + ']'
				};
			}
			var fdiv = document.getElementById("packinglist");
			fdiv.innerHTML = "";
			block = document.getElementById("block").innerHTML;
			image = document.getElementById("image").innerHTML;
			innerhtml = "";
			for (i=0; i<resp.data.length; i++) {
				slide = resp.data[i];
				console.log (slide.term+slide.thumb);
				segment = (slide.block != "")?block:"";
				segment += image;
				segment = segment
						.replace("*0*term*0*"  , slide.term       )
						.replace("*0*block*0*"  , slide.block     )
						.replace("*0*thumb*0*"  , slide.thumb     )
						.replace("*0*file*0*"  , slide.file       )
						.replace("*0*elapse*0*"  , slide.elapse   );
				innerhtml += segment;
			}
			fdiv.innerHTML = innerhtml;
		}
	};
	request.open('POST', 's00_signage.php');
	request.send(data);
	return request;
}


getlist();
 
</script>
</html>