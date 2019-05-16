<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");


unset($_SESSION['profile']);
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
			
			<div style="">
				<div id="profile" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					(소유) : <input type="text" id='title' style="width : 280px;text-align:center;" value="<?=$title?>" ><br>
					(제목) : <input type="text" id='subject' style="width : 280px;text-align:center;" value="<?=$subject?>" ><br>
					(주제) : <input type="text" id='owner' style="width : 280px;text-align:center;" value="<?=$owner?>" ><br>
					<div style="text-align:center;">
					<img class="img_shadow" id="photo" src="<?=$photo?>" alt="photo" style="width:60%;"  onclick="document.getElementById('nfile').click();">
					</div>
					<div style="display:none;">
					(사진) : <input id="nfile" name="nfile" type="file" style="width:300px;position:relative;"/>
					</div>
					(꼬리말) : <input type="text" id='footnote' style="width : 280px;text-align:center;" value="<?=$footnote?>" >
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
					기본정보 <input type="button" onclick='javascript:set_subject()' value="설정" > <input type="button" onclick='javascript:get_subject()' value="읽기" >
				</div>
				<hr> <!-- hr -->
				<div id="class" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					<div style="margin-top:10px;"><!-- upper line feed --></div>
					(접속코드) : [일반사용자:<input type="text" id='access_code' style="width : 50px; text-align:center; color:red; font-weight:bold;" value="<?=$access_code?>" >] 
					[관리자:<input type="text" id='sam_code' style="width : 80px; text-align:center; color:red; font-weight:bold;" value="<?=$sam_code?>" >] <br><br>
					(원격접속) : <input type="text" id='surprisebox_code' style="width : 7.5em; text-align:center; color:red; font-weight:bold;" value="<?=$surprisebox_code?>" > #  <input type="text" id='gate_server' style="width : 7em; text-align:center;" value="<?=$gate_server?>" > :  <input type="text" id='external_port' style="width : 2.5em; text-align:center;" value="<?=$external_port?>" > <br>
					(무선접속) : <input type="text" id='ssid' style="width : 10em; text-align:center; color:red; font-weight:bold;" value="<?=$ssid?>" > /pw  <input type="text" id='wifi_password' style="width : 6em; text-align:center;" value="<?=$wifi_password?>" onblur="check8(this.value);" > <br>
					<div style="margin-top:10px;"><!-- upper line feed --></div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				코드 <input type="button" onclick='javascript:set_code()' value="설정" >
				<input type="button" onclick='javascript:get_code()' value="보기" >
				</div>
				<div id="usbctl" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					<div style="margin-top:10px;"><!-- upper line feed --></div>
					(USB) : <input type="text" id='usb_name' name="targets" style="width : 180px; text-align:center; color:red; font-weight:bold;" value="" ><input type="button" id='get_usb' style=" color:red; font-weight:bold;" value="USB확인" onclick="getnextusb();"> <br>
					(폴더) : <input type="text" id='usb_path' name="targets" style="width : 220px; text-align:left; color:red; font-weight:bold;" value="" ><input type="button" id='get_usb' style=" color:red; font-weight:bold;" value="폴더확인" onclick="getnextpath();"> <br>
					
					(대상) : 
					<input type="text" id='image_cb'  name="targets" class="checkinput" value='✔' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 사진, 
					<input type="text" id='video_cb'  name="targets" class="checkinput" value='✔' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 동영상, 
					<input type="text" id='profile_cb'  name="targets" class="checkinput" value='✔' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 프로파일 
					<br>
					<div style="margin-top:10px;font-size:0.5em;"><!-- upper line feed --></div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				USB <input type="button" onclick='usb_backup()' value="백업" >	<input type="button" onclick='usb_restore()' value="복구" >
				</div>
				<hr style="line-color:green;"> <!-- hr -->			</div>
			
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
</body>

<script>
	function g(code, v) {
		for(i=0;i<code.length;i++) {
			if (code[i] == v) return i;
		}
		return null;
	}
	var use = ['_','✔'];	
    
	var loadingdiv;
	function waiting(objectname, msg) {
		loadingdiv = document.getElementById(objectname).appendChild(document.createElement("div"));
		loadingdiv.innerHTML = msg;
		loadingdiv.setAttribute("style", "text-align:center; position: absolute; top: "+(document.body.clientHeight - 60)/2+"px; left: "+(document.body.clientWidth - 300)/2+"px; width:300px; height:60px; color: red; background-color:yellow; font-size:3em;");
	}
	function closing(time) {
		setTimeout( loadingdiv.remove(), time);
	}

    function check8(str) {
        if (str.length < 8) alert ("비밀번호는 8자리 이상입니다.");
    }        
	
	
	var _usb = document.getElementById("usb_name");
	var getnextusb = function() {
        var data = new FormData();
        data.append('func', 'get_nextusb');
        data.append('usb_name', _usb.value);

		POST('s00_signage.php', data, 
			function (resp) {
				_usb.value = resp.data.usb_name;
				return;	
			}
		);
	}

	var _folder = document.getElementById("usb_path");
	var getnextpath = function() {
        var data = new FormData();
        data.append('func', 'get_nextpath');
        data.append('usb_name', _usb.value);
        data.append('usb_path', _folder.value);
        
		POST('s00_signage.php', data, 
			function (resp) {
				_folder.value = resp.data.usb_path;
				return;	
			}
		);
	}
	
    var usb_backup = function(){
        
        var data = new FormData();
        data.append('func', 'usb_backup');
        
		waiting('class', "백업중");

        dd = document.getElementsByName("targets");
        for (i=0;i <dd.length;i++) {
            if (dd[i].nodeName !== "INPUT" || dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }
		POST('s00_signage.php', data, 
			function (resp) {
				closing(1000);
				return;	
			}
		);
    }

    var usb_restore = function(){
        
        var data = new FormData();
        data.append('func', 'usb_restore');
        
		waiting('class', "복구중");

        dd = document.getElementsByName("targets");
        for (i=0;i <dd.length;i++) {
            if (dd[i].nodeName !== "INPUT" || dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }
		POST('s00_signage.php', data, 
			function (resp) {
				closing(1000);
				return;	
			}
		);
    }
	
	
    var set_subject = function(profile){
        
        var data = new FormData();
        data.append('func', 'set_subject');
        
        dd = document.getElementById("profile").childNodes;
        for (i=0;i <dd.length;i++) {
            if (dd[i].nodeName !== "INPUT" || dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }
        data.append('nfile', document.getElementById('nfile').files[0] );
        
		POST('s00_signage.php', data, 
			function (resp) {
				return;	
			}
		);
    }
    
    var get_subject = function(profile){
        
        var data = new FormData();
        data.append('func', 'get_subject');
		if (typeof profile != 'undefined') data.append('profile', profile);
        
		POST('s00_signage.php', data, function (resp) {
                dd = document.getElementById("profile").childNodes;
                for (i=0;i <dd.length;i++) {
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
                document.getElementById('photo').src = resp.data.photo+"?"+Math.random();
        });
    }
    
    var set_code = function(){
        var data = new FormData();
        data.append('func', 'set_code');
        dd = document.getElementById("class").childNodes;
        for (i=0;i <dd.length;i++) {
            if (dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }        
        
		POST('s00_signage.php', data, 
			function (resp) {  return;  }
		);
    }
    
    var get_code = function(profile){
        
        var data = new FormData();
        data.append('func', 'get_code');
		if (typeof profile != 'undefined') data.append('profile', profile);

		POST('s00_signage.php', data, function (resp) {
                dd = document.getElementById("class").childNodes;
                for (i=0;i <dd.length;i++) {
                                 
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
        });

    }
    get_code();

	_file = document.getElementById('nfile');
	_photo = document.getElementById('photo');
	_file.onchange = function (event) {
		//_fname.innerHTML = this.value;
		_photo.src = URL.createObjectURL(event.target.files[0]);
		//_caption.value = "";
		//_caption.placeholder = this.value;
	}
	
</script>
</html>
