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
			<div style="margin-top:5px;"><!-- upper line feed --></div>
			<div style="text-align:center; margin: 0 auto;">
			
				<div class="tile" style="background-color:#afbf00;padding-top:5px;padding-bottom:5px;">
				
					<div class='progress_outer' style="border: 1px solid #CCC;margin:3px;">
					<div id="_prog" class='progress' style="text-align:center; background-color:#afbf00;margin-left:3px;margin-right:3px;padding-top:2px;padding-bottom:2px;">???</div>
					</div>
					
					<div style="width:100%;text-align:center; float:left; clear:both;display: table;display:inline-block;">
						<video id="video" width="95%" controls autoplay >
						  <source id="mp4_src" src="http://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
						Your browser does not support the video tag.
						</video>
					<textarea id="caption" style="width:80%;height:2em;" ></textarea><br>
					</div>
					<div class="button_base" style="text-align:center;background-color:#eee;">
						<span id="btn_get" class="button_span btn" style="width: 3.2em;" onclick="document.getElementById('_file').click();" >찾기</span>  
						<span id="btn_send" class="button_span btn" style="width: 3.2em;" onclick="upvideo();" >등록</span>  
<?php if($_SESSION['uselevel']>1) { ?>
						<span id="btn_cancel" class="button_span btn" style="width: 3.2em;" onclick="rmvideo();" >삭제</span>
<?php } ?>
						<span id="btn_list" class="button_span btn" style="width: 3.2em;" onclick="showvideos();" >목록</span>
					</div>
				</div>
                                <div style="margin-top:3px;"><!-- upper line feed --></div>
					
				<div class="tile" style="background-color:#afbf00;padding-top:2px;padding-bottom:2px;">
				
					<div id="vlog" style="text-align:center; background-color:#347235;margin:3px;padding-top:5px;padding-bottom:5px;"> 모니터 제어 </div>
				    <img class="img_btn" src="images/previous.png" onclick="control('seek -30 seconds');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/playhold.png" onclick="control('pause/resume');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/next.png" onclick="control('seek +30 seconds');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/reload.png" onclick="control('restart');" style="width:72px;" alt="b">
				<div style="margin-top:5px;"><!-- upper line feed --></div>
				    <img class="img_btn" src="images/louder.png" onclick="control('increase volume');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/quite.png" onclick="control('decrease volume');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/previous_o.png" onclick="getvideobyseq('previous');" style="width:72px;" alt="b">
				    <img class="img_btn" src="images/next_o.png" onclick="getvideobyseq('next');" style="width:72px;" alt="b">
				</div>
				<div style="display:none;">
					<input type='file' id='_file' class="medium-btn" style="width:70%;"accept="video/*">					
				</div>					
			</div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
</body>

<script>

    var _video = document.getElementById('video');
    var _fname = document.getElementById('_prog');
    var _caption = document.getElementById('caption');
	var _vlog = document.getElementById('vlog');

	_file = document.getElementById('_file');
	_file.onchange = function (event) {
		var URL = window.URL || window.webkitURL;
		_fname.innerHTML = this.value;
		_video.src = URL.createObjectURL(event.target.files[0]);
		_caption.value = "";
		_caption.placeholder = this.value;
	}

    var getvideo = function(video, source){
		
        var data = new FormData();
        data.append('func', 'getvideo');
        data.append('video', video);
        data.append('action', 'current');
        data.append('source', source);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				_video.src = resp.data.url;
				_video.name = video;  
				_caption.value = resp.data.caption; 
				_fname.innerHTML = resp.data.origin;
				if( resp.data.status == "new") control("restart");
		});
    }

    var getvideobyseq = function(action){
		
        var data = new FormData();
        data.append('func', 'getvideo');
        data.append('video', _video.name);
        data.append('action', action);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				_video.src = resp.data.url;
				_video.name = resp.data.video;  
				_caption.value = resp.data.caption; 
				_fname.innerHTML = resp.data.origin;
				if( resp.data.status == "new") control("restart");
		});
    }

	
	setvideo = function (src) {
		var URL = window.URL || window.webkitURL;
		_fname.innerHTML = this.value;
		_video.src = URL.createObjectURL(event.target.files[0]);
		_caption.value = "";
		_caption.placeholder = this.value;
	}
	
    var upvideo = function(){
		
		var background_org = "#afbf00";
		
        var data = new FormData();
        data.append('func', 'upvideo');
		data.append('video', _file.files[0]);
        data.append('caption', _caption.value);
		
		var loadingdiv = document.getElementById('video').appendChild(document.createElement("div"))
		loadingdiv.innerHTML = "등록중";
		loadingdiv.setAttribute("style", "position: absolute; top: "+(document.body.clientHeight - 60)/2+"px; left: "+(document.body.clientWidth - 300)/2+"px; width:300px; height:60px; color: red; background-color:yellow; font-size:3em;");
		
		var request = new XMLHttpRequest();
		request.onreadystatechange = function(){
			if(request.readyState == 4){
				try {
					//console.log(request.response);
					var resp = JSON.parse(request.response);
				} catch (e){
					var resp = {
						status: 'error',
						data: 'Unknown error occurred: [' + request.responseText + ']'
					};
				} 
				console.log(resp.status + ': ' + resp.data);
				if (resp.status == 'error') {
					alert(resp.data);
					setTimeout( loadingdiv.remove(), 1000);
					return;
				}

				loadingdiv.innerHTML = resp.data.msg;
				setTimeout( loadingdiv.remove(), 5000);
				
			   _vlog.innerHTML=resp.data.msg;
				getslide("first");
				_fname.style.background = background_org;
				//alert(resp.data.msg);
			}
		};
		
		_fname.style.background = "#50ad4e";
		request.upload.addEventListener('progress', function(e){
			_prog.style.width=Math.ceil(e.loaded/e.total* 100) + '%';
			_prog.innerHTML=Math.ceil(e.loaded/e.total* 100) + '%,' + Math.ceil(e.loaded/1024/1024) +"MB";
		}, false);
		
		request.open('POST', 's00_signage.php');
		request.send(data);
		return request;
    }

<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 2) { ?>
    var rmvideo = function(msg){
		
        var data = new FormData();
        data.append('func', 'rmvideo');
		data.append('video', _video.name);
		
		POST('s00_signage.php', data, 
			function (resp) {  
			    document.getElementById('vlog').innerHTML=resp.data;
				getslide("first");
		});
    }
<?php } ?>

	var videolist;
	var showvideos = function (usb) {
		if (typeof videolist !== 'undefined') videolist.close();
		if (usb == true) 
			videolist = window.open( "videolist.php?usb", "cc", "width=250px,height=320px");
		else 
			videolist = window.open( "videolist.php", "cc", "width=250px,height=320px");
	}
	
    var control = function(msg){
		
        var data = new FormData();
        data.append('func', 'video_control');
        data.append('ctrl', msg);
		data.append('video', _video.name);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML = resp.data;  
		});
    }
	
	getvideobyseq("first");
	
</script>
</html>
