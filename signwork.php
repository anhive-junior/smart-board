<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");

include_once("lib/get_config.php");
include_once("lib/get_access.php");
?>
<!DOCTYPE html >
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
	<link rel="stylesheet" type="text/css" href="signage.base.css">
    <script type="text/javascript" src="signage.base.js"></script>
    <title><?=$_SESSION['owner']?></title>
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
			<div style="text-align:center; margin: 0 auto; ">
			
				<div class="tile" style="background-color:#ff9f00;padding-top:5px;padding-bottom:5px;">
				
					<div id="fname" style="text-align:center; background-color:#afbf00;margin:3px;padding-top:5px;padding-bottom:5px;">스라이드 쇼 조정</div>

					<div style="margin-top:10px;"><!-- upper line feed --></div>
				
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					
					슬라이드 쇼	</div>
				    <img class="img_btn" src="images/previous.png" onclick="control('previous');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/playhold.png" onclick="control('playhold');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/next.png" onclick="control('next');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/restart.png" onclick="control('restart');" style="width:72px;" alt="b">
				<br>
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					슬라이드 방향조정</div>
				    <img class="img_btn" src="images/rotate_270.png" onclick="control('rotate_left');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/rotate_90.png" onclick="control('rotate_right');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/flipped.png" onclick="control('flipped');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/mirrored.png" onclick="control('mirrored');" style="width:72px;" alt="b">
				<br>
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					슬라이드 크기조정</div>
				    <img class="img_btn" src="images/larger.png" onclick="control('larger');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/smaller.png" onclick="control('smaller');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/fittosize.png" onclick="control('fittosize');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/100p.png" onclick="control('100p');" style="width:72px;" alt="b">
				<br>
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					슬라이드 위치조정</div>
				    <img class="img_btn" src="images/upward.png" onclick="control('upward');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/downward.png" onclick="control('downward');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/leftward.png" onclick="control('leftward');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/rightward.png" onclick="control('rightward');" style="width:72px;" alt="b">
				<br>
					<div style="text-align:center; background-color:#afbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					<div id="vlog">&nbsp;</div></div>				
				</div><br>
			</div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
</body>

<script>

    var control = function(msg){
		
        var data = new FormData();
        data.append('func', 'control');
        data.append('ctrl', msg);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML = resp.data;  
		});
    }
	
</script>
</html>
