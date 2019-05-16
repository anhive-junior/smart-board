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
    <title>재생방법 설정</title>
	<style>
	table {	text-align : center;}
	table th{text-align : right;}
	table td{text-align : left;}
	.checkinput { width:1.2em;text-align:center;cursor:pointer;
		background-color:#444;color:#eee;margin-top:1px;   }
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
			
			<div style="text-align:center;font-weight:bold;color:red;">재생 방법 관리</div>
			<hr>
			
			<div id="playlist_policy" >
				<div id="curate_ploicy" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					재상목록 구성방법 : <br>
					<div style="text-align:left;margin-left:30px;">
						<input type="text" id='time_base'  name="list_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 시간기준:
						<input type="text" id='time_begin' style="width:2em; text-align:center; font-weight:bold; color:red;" value="0" >시간 ~ <input type="text" id='time_days' style="width:3em;text-align:center; font-weight:bold; color:red;" value="6" >일 <input type="text" id='time_hours' style="width:2em;text-align:center; font-weight:bold; color:red;" value="24" >시간 <br>
						
						<input type="text" id='count_base'  name="list_policy"  class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 건수기준: 
						<input type="text" id='count_max' style="width:2em;text-align:center; font-weight:bold; color:red;" value="30" >건 (최근 등록순으로)<br>
						<input type="text" id='curate_base'  name="list_policy"  class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 직접선택: (재생목록 에서) <br>
						<input type="text" id='media_base'  name="list_policy"  class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 등록파일 전체(직접선택 연계)<br>
					</div>
				</div><br>
				<div id="reload_plocy" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					재생목록 자동갱신 방법:
					<div style="text-align:left;margin-left:30px;">
						<input type="text" id='as_updated'  name="reload_policy" class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 신규 사진 등록시<br>

						<input type="text" id='as_powerup'  name="reload_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 장비를 켤 때 적용<br>
						
						<input type="text" id='as_interval'  name="reload_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 주기: 
						<input type="text" id='reload_interval' name="reload_policy" style="width:3em; text-align:center; font-weight:bold; color:red;" value="300" ><span style="font-weight:bold;color:blue;">초.</span><span>(자동 목록 구성시)<span><br>
					</div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				재상목록정책 <input type="button" onclick='set_playlistpolicy()' value="설정" >
				<input type="button" onclick='get_playlistpolicy()' value="보기" >
				</div>
			</div>
			
			<div style="margin-top:7px;"><!-- upper line feed --></div>
			<hr>
			<div id="slideplay_policy" >
				<div id="play_policy" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					재생 방식 : 
					<div style="text-align:left;margin-left:30px;">
						재생순서:<input type="text" id='squential_play'  name="hop_policy" class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 순차, 
						<input type="text" id='randon_play'  name="hop_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 무작위<br><br>
						재상시간간격: <input type="text" id='slide_interval' style="width:3em;text-align:center;font-weight:bold;color:red;" value="5" ><span style="font-weight:bold;color:blue;"> 초</span><span>(최소 3초이상)<span><br><br>
						화면확대: <input type="text" id='photo_zoom'  name="zoom_policy" class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 사진맞춤, 
						<input type="text" id='screen_zoom'  name="zoom_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 화면맞춤(줌)<br>
					</div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				재상방법 <input type="button" onclick='set_playmode()' value="설정" >
				<input type="button" onclick='get_playmode()' value="보기" >
				</div>				
			</div>
			<div style="margin-top:7px;"><!-- upper line feed --></div>
			<hr>
		</div>	
	</div> 
	<div class='footer' ><?=$footnote?></div>

<script>

	// 시스템 조회코드
	function g(code, v) {
		for(i=0;i<code.length;i++) {
			if (code[i] == v) return i;
		}
		return null;
	}
	var wpa = ['NONE','WPA','WPA2', 'WPA/WPA2'];
	var use = ['_','✔'];	

	var set_playlistpolicy = function(){
        var data = new FormData();
        data.append('func', 'set_playlistpolicy');
        //dd = document.getElementById("playlist_policy").childNodes;
		ele = document.getElementById("playlist_policy");
		dd = ele.getElementsByTagName("input");
        for (i=0;i <dd.length;i++) {
            if (dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }        
        
		POST('s00_signage.php', data, 
			function (resp) {  return;  }
		);
    }
    
    var get_playlistpolicy = function(profile){
        
        var data = new FormData();
        data.append('func', 'get_playlistpolicy');
		if (typeof profile != 'undefined') data.append('profile', profile);

		POST('s00_signage.php', data, function (resp) {
                //dd = document.getElementById("playlist_policy").childNodes;
				ele = document.getElementById("playlist_policy");
				dd = ele.getElementsByTagName("input");
				for (i=0;i <dd.length;i++) {
                                 
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
        });

    }
	get_playlistpolicy();
	

	var set_playmode = function(){
        var data = new FormData();
        data.append('func', 'set_playmode');
        //dd = document.getElementById("play_policy").childNodes;
		ele = document.getElementById("play_policy");
		dd = ele.getElementsByTagName("input");
        for (i=0;i <dd.length;i++) {
            if (dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }        
        
		POST('s00_signage.php', data, 
			function (resp) {  return;  }
		);
    }
    
    var get_playmode = function(profile){
        
        var data = new FormData();
        data.append('func', 'get_playmode');
		if (typeof profile != 'undefined') data.append('profile', profile);

		POST('s00_signage.php', data, function (resp) {
                //dd = document.getElementById("play_policy").childNodes;
				ele = document.getElementById("play_policy");
				dd = ele.getElementsByTagName("input");
				for (i=0;i <dd.length;i++) {
                                 
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
        });

    }
	get_playmode();
	
	
</script>   
	
</body>
</html>
