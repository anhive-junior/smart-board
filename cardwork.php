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
	<style>
	.img_block{	position: relative;	
			width:100%;
			//height: 80px; 
			//font-size:1.2em;
			margin:0 auto;
			display:inline-block;
			padding-bottom:10px; 
	}
	.img_base{}
	.img_tag{position: absolute;top: 32px;right: 16px;width: 72px;
		color:green; }
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
			
		
			<div style="text-align:center; margin: 0 auto; ">

				<div style="background-color:#eee; padding-top:5px;padding-bottom:5px;">
				
					<div class='progress_outer' style="border: 1px solid #CCC;margin:3px;">
					<div id="_prog" class='progress' style="text-align:center; background-color:#afbf00;margin:3px;padding-top:5px;padding-bottom:5px;">???</div>
					</div><br>
				
					<div style="width:100%;text-align:center; float:left; clear:both;display: table;display:inline-block;">
						<div id="img_blk" class="img_block" style="">
							<figure class="img_base">
								<img class="img_shadow" id="photo" src="" alt="photo" style="width:100%;" onclick="document.getElementById('_file').click();">
								<figcaption>터치!, 새사진으로</figcaption>
							</figure>
							<img class="img_btn img_tag" src="images/rotate_90.png" onclick="setslide('rotate_right');" alt='rotate_right'>
						</div>

					</div><br><br>
					<textarea id="caption" style="width:80%;height:4em;" ></textarea><br>

				<div style="margin-top:10px;"><!-- upper line feed --></div>
					<div class="button_base" style="text-align:center;background-color:#eee;">
						<span id="btn_send" class="button_span btn" onclick="sendcard();" >보내기</span>  
<?php if($_SESSION['uselevel']>1) { ?>
						<span id="btn_cancel" class="button_span btn" onclick="rmcard();" >회 수</span>
<?php } ?>
						<span id="btn_list" class="button_span btn" onclick="shownails();" >재생목록</span>
					</div>

					<div style="visibility: hidden;">
						<input type='file' id='_file' class="medium-btn" style="width:70%;">					
					</div>
					<div id="vlog" style="text-align:center; background-color:#afbf00;margin:3px;padding-top:5px;padding-bottom:5px;">최근등록 파일</div>
				</div>
			</div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
</body>

<script>

    var _photo = document.getElementById('photo');
    var _fname = document.getElementById('_prog');
    var _caption = document.getElementById('caption');
	var _vlog = document.getElementById('vlog');
	
    var getslide = function(msg){
		
        var data = new FormData();
        data.append('func', 'getslide');
        data.append('photo', _photo.alt);
        data.append('action', msg);
		d = new Date();
		
		POST('s00_signage.php', data, 
			function (resp) {  
				_photo.src = resp.data.url;
				_photo.alt = resp.data.photo;  
				_caption.value = resp.data.caption; 
				_fname.innerHTML = resp.data.mtime;
		});
    }
	
	_file = document.getElementById('_file');
	_file.onchange = function (event) {
		var URL = window.URL || window.webkitURL;
		console.log(this.value);
		_fname.innerHTML = this.value;
		_photo.src = URL.createObjectURL(event.target.files[0]);
		_caption.value = "";
		_caption.placeholder = this.value;
	}

    var setslide = function(msg){

        var data = new FormData();
        data.append('func', 'setslide');
        data.append('action', msg);
        data.append('photo', _photo.alt);

		POST('s00_signage.php', data, 
			function (resp) {  
			    _vlog.innerHTML=resp.data;
				getslide("current");
		});
    }
	
    var sendcard = function(){
		
		//if(_file.files.length === 0){
		//	document.getElementById('vlog').innerHTML = 
		//	"새 사진등록은 사진 클릭!";
		//	return;
		//}
		var background_org = "#afbf00";
		
        var data = new FormData();
        data.append('func', 'sendcard');
		data.append('card', _file.files[0]);
        data.append('caption', _caption.value);
		
		var loadingdiv = document.getElementById('img_blk').appendChild(document.createElement("div"))
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
			_prog.innerHTML=Math.ceil(e.loaded/e.total* 100) + '%';
		}, false);
		
		request.open('POST', 's00_signage.php');
		request.send(data);
		return request;
    }

<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 2) { ?>
    var rmcard = function(msg){
		
        var data = new FormData();
        data.append('func', 'rmcard');
		data.append('card', _photo.alt);
		
		POST('s00_signage.php', data, 
			function (resp) {  
			    document.getElementById('vlog').innerHTML=resp.data;
				getslide("first");
		});
    }
<?php } ?>

	var shownails = function () {
		window.open( "nailwork.php", "_self");
	}
	
	getslide("first");
	

</script>
</html>
