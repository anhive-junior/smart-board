<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");

include_once("lib/get_config.php");
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

				<div class="tile" style="background-color:#92ABDB; padding-top:5px;padding-bottom:5px;">
				
					<div id="fname" style="text-align:center; background-color:#afbf00;margin:3px;padding-top:5px;padding-bottom:5px;">xxxxxxx</div>

					<div style="width:100%;height:330px; text-align:center; float:left; clear:both;display: table;">
						<div style="width:12%;margin:0 auto;display: inline-block;display: table-cell;vertical-align: middle;">
							<img class="img_btn" src="images/left_next.png" onclick="getslide('previous');" style="width:32px;" alt="previous">
						</div>
						<div style="width:55%;margin:0 auto;display:inline-block;  display: table-cell; vertical-align: middle;">
							<img class="img_angle" id="photo" src="" alt="photo" style="width:100%;" >
						</div>
						<div style="width:12%;margin:0 auto;display: inline-block;display: table-cell;vertical-align: middle;">
							<img class="img_btn" src="images/right_next.png" onclick="getslide('next');" style="width:32px;" alt="next">
						</div>
					</div><br><br>
					<hr/>
				    <img id="rotate_270" class="img_btn" src="images/rotate_270.png" onclick="setslide('rotate_left');" style="width:72px;" alt='rotate_left'>
				    <img id="rotate_90" class="img_btn" src="images/rotate_90.png" onclick="setslide('rotate_right');" style="width:72px;" alt='rotate_right'>
				    <img id="exclude_img"  class="img_btn" src="images/exclude.png" onclick="exclude(_photo.alt);" style="width:72px;" alt='exclude'>
				    <img id="playlist_id" class="img_btn" src="images/playlist.png" onclick="window.open( 'listwork.php', '_self');" style="width:72px;" alt='playlist'>

					<br>
					<div style="width:100%;text-align:center;">
					<textarea id="caption" style="width:75%;height:4em;font-size:1.2em;position: relative;display: inline-block;" ></textarea>
					<input type="button" onclick="setcaption(document.getElementById('caption').value);" value="저장" style="height:4em;position: relative;display: inline-block;vertical-align:top;"><br>
					</div>

					<div id="vlog" style="text-align:center; background-color:#afbf00;margin:3px;padding-top:5px;padding-bottom:5px;">?</div>

				</div>
			</div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
</body>

<script>

    var _photo = document.getElementById('photo');
    var _fname = document.getElementById('fname');
    var _caption = document.getElementById('caption');
    var _vlog = document.getElementById('vlog');
	var _body = document.getElementsByTagName ('body')[0];
	
    var getslide = function(msg){
		
        var data = new FormData();
        data.append('func', 'getslide');
        data.append('photo', _photo.alt);
        data.append('action', msg);
		d = new Date();
		
		POST('s00_signage.php', data, 
			function (resp) {  
				_photo.src = resp.data.url+"?"+Math.random();
				_photo.alt = resp.data.photo;  
				_caption.value = resp.data.caption; 
				_fname.innerHTML = resp.data.mtime;
				_vlog.innerHTML = msg+" card.";
		});
    }
	
    var setslide = function(msg){

		_body.className = 'wait';
	
        var data = new FormData();
        data.append('func', 'setslide');
        data.append('action', msg);
        data.append('photo', _photo.alt);

		
		POST('s00_signage.php', data, 
			function (resp) {  
			    _vlog.innerHTML=resp.data;
				getslide("current");
				_body.className = '';
		});
    }

    var setcaption = function(msg){
		
        var data = new FormData();
        data.append('func', 'setcaption');
        data.append('caption', msg);
        data.append('photo', _photo.alt);
		
		POST('s00_signage.php', data, 
			function (resp) {  
			    _vlog.innerHTML=resp.data;
				getslide("current");
		});
    }
	
	var rmcard = function(fname){

		getslide("next");
	
		var data = new FormData();
		data.append('func', 'rmcard');
		data.append('card', fname);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				_vlog.innerHTML = resp.data;
				//getslide("next");
		});
	}

	var exclude = function(fname){

		getslide("next");
	
		var data = new FormData();
		data.append('func', 'exclude');
		data.append('card', fname);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				_vlog.innerHTML = resp.data;
				//getslide("next");
		});
	}
	getslide("first");

</script>
</html>
