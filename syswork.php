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
    <title>System</title>
	<style>


	table {
		text-align : center;
	}
	table th{
		text-align : right;
	}
	table td{
		text-align : left;
	}

	.checkinput {
        width:1.2em;
		text-align:center;
		cursor:pointer;
		background-color:#444;
		color:#eee;
		margin-top:1px;
    }


	</style>
</head>
<body > 
    <div class="container">
        <div class="contents">
		
			<!-- head note -->
			<div class='headnote' onclick="javascript:document.location.href='home.html'">
				<img src="<?=$photo?>" alt="profile" style="height:15px;">
				<span class="input_title"><?=$subject?></span> 
				<span class="input_title"><?=$owner?></span>
			</div>
			<div style="margin-top:10px;"><!-- upper line feed --></div>
			
			<div style="text-align:center;font-weight:bold;color:red;">시스템 관리</div>

			<hr>
			<div style="">			
				<div style="margin-top:7px;"><!-- upper line feed --></div>
				<div id="set_diskpolicy" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					<div id="cleaning" style="text-align:left;margin-left:10px;">
						저장공간 관리 방법 : <br>
						<div style="text-align:left;margin-left:30px;">
							<input type="text" id='old_first'  name="rm_policy" class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 오래된 파일부터 삭제(적용예정),<br>
							<input type="text" id='rare_first' name="rm_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 적게 사용한 파일부터 삭제(적용예정),<br>
							<input type="text" id='hold_first'  name="rm_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 신규등록 제한(적용예정)<br>
							<input type="text" id='none_first'  name="rm_policy" class="checkinput" value='_' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;"> 삭제하지 않음(직접삭제가능)<br>
						</div>
					</div><br>
					
					<div id="margin" style="text-align:left;margin-left:10px;">
							최소 사용 가용 공간:
						<div style="text-align:left;margin-left:30px;">
							<input type="text" id='reserve_space' style="width:3em;text-align:center;font-weight:bold;color:red;" value="30" ><span style="font-weight:bold;color:blue;">MB.</span><span>(30MB 이상 필요)<span>
						</div>
					</div>
					<div id="resource" style="text-align:center;">
						저장공간:<input type="text" id='storage_total' style="width:3em; border:none;" >, 남은공간:<input type="text" id='storage_free' style="width:3em;  border:none; color:blue;" >사용중:<input type="text" id='storage_used' style="width:3em; border:none;" >
					</div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				저장장치 <input type="button" onclick='get_diskpolicy()' value="보기" > <input type="button" onclick='set_diskpolicy()' value="적용" >
				</div>
				<div style="margin-top:7px;"><!-- upper line feed --></div>
				<hr>
				
				<!-- 프로그램이 정상적으로 동작하지 않아 disable 하였음 2019. 02. 12
				<div id="network" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					<div style="margin:0 auto; width:350px; text-align:left;">
						<div style="margin-top:7px;"></div>
						<input type="text" id='ap_use'  class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;">
						(무선공유) SSID :<input type="text" id='ssid' style="width:8em;text-align:center;font-weight:bold;color:red;" value="" >
						<br>
						<input type="text" id='sec_use'  class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;">
						(무선비밀번호): <input type="text" id='password' style="width:8em; text-align:center; color:blue;font-weight:bold;" value=""> /W <input type="text" id='wpa' class="checkinput" style="width: 6em;" value="NONE" readonly onclick="{_clicked(this,'NONE','WPA','WPA2', 'WPA/WPA2'); var epw = document.getElementById('password'); epw.disabled = (this.value=='NONE')?true:false;}">
						
						<br>
						<input type="text" id='int_use'  class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;">
						(네트워크설정):
						<input type="text" id='interface' style="width:4em;text-align:center;" value="p2p1" readonly onclick="{_clicked(this,'p2p1','wlan1');}" >
						<input type="text" id='operation' class="checkinput" style="width: 4em;" value="dhcp" readonly onclick="{_clicked(this,'dhcp','static'); ni = document.getElementById('netinfo'); ni.style.display = this.value=='dhcp'?'none':'';}"><br>
						<table id="netinfo" style="margin:0 auto; width:300px;display:none;" ><tr><th>
							(IP)</th><td><input type="text" id='ip_address' style="width:8em;" value="" >
							</td></tr><tr><th>
							(MASK)</th><td><input type="text" id='ip_mask' style="width:8em;" value="" >
							</td></tr><tr><th>
							(G/W)</th><td><input type="text" id='ip_gate' style="width:8em;" value="" >
							</td></tr><tr><th>
							(DNS)</th><td><input type="text" id='ip_dns1' style="width:8em;" value="" >, <input type="text" id='ip_dns2' style="width:8em;" value="" >
						</td></tr></table>
						<input type="text" id='ext_use'  class="checkinput" value='&#10004' readonly onclick="_clicked(this,'_','✔')" style="background-color:#eee;color:#000;">(원격연결)<input type="text" id='external_ip' style="width:8em; text-align:center; " value="" > <input type="text" id='external_port' style="width:3em; text-align:center; " value="" >
					</div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				네트워크 <input type="button" onclick='javascript:get_netinfo()' value="보기" > 
				<input type="button" onclick='javascript:set_netinfo()' value="적용" >
				</div>
				-->

				<!-- samwork 를 참조하여 다음과 같이 수정하였음.. 2019. 02. 12 -->
				
				<div id="class" style="font-size:.8em; text-align:left; margin-left:10px;width: 50%; margin: 0 auto;min-width: 350px;">
					<div style="margin-top:10px;"><!-- upper line feed --></div>
					(접속코드) : [일반사용자:<input type="text" id='access_code' style="width : 50px; text-align:center; color:red; font-weight:bold;" value="<?=$access_code?>" >] 
					[관리자:<input type="text" id='sam_code' style="width : 80px; text-align:center; color:red; font-weight:bold;" value="<?=$sam_code?>" >] <br><br>
					(원격접속) : <input type="text" id='surprisebox_code' style="width : 7.5em; text-align:center; color:red; font-weight:bold;" 
					value="<?=$surprisebox_code?>" > #  
					<input type="text" id='gate_server' style="width : 7em; text-align:center;" value="<?=$gate_server?>" > 
					:  <input type="text" id='external_port' style="width : 2.5em; text-align:center;" value="<?=$external_port?>" > <br>
					(무선접속) : <input type="text" id='ssid' style="width : 10em; text-align:center; color:red; font-weight:bold;" value="<?=$ssid?>" > /pw  <input type="text" id='wifi_password' style="width : 6em; text-align:center;" value="<?=$wifi_password?>" onblur="check8(this.value);" > <br>
					<div style="margin-top:10px;"><!-- upper line feed --></div>
				</div>
				<div style="text-align:center; background-color:#eee;margin-top:10px;">
				네트워크 <input type="button" onclick='javascript:set_code()' value="설정" >
				<input type="button" onclick='javascript:get_code()' value="보기" >
				</div>
				<!-- html은 여기까지 수정! -->
				
				<div style="margin-top:7px;"><!-- upper line feed --></div>
				<hr>
<?php if (false) { ?>								
				<div style="text-align:center;font-weight:bold;color:green;">홈 사이니지(관리용)
					<div style="margin-top:7px;"><!-- upper line feed --></div>
					<div style="color:blue;">AHHS-RP-3, v1.0</div>
					<div style="margin-top:7px;"><!-- upper line feed --></div>
					<div style="text-align:center;">
						<table style="margin:0 auto; width:200px; "><tr><td style="text-align : center;">
						<span id="reboot" onclick="javascript:reboot();"  ><img src="images/reset.png" style="width:4em;"></span>
						</td><td style="text-align : center;">
						<span id="shutdown" onclick="javascript:shutdown();" ><img src="images/shutdown.png" style="width:4em;"></span>
						</td></tr><tr><td style="text-align : center;">	Reset	</td><td style="text-align : center;"> Shutdown
						</td></tr></table>
					</div>
				</div>
<?php } ?>
			</div>
		</div>	
	</div> 
	<div class='footer' ><?=$footnote?></div>

<script>

	var
	 _status = document.getElementById('_status')
	,_reboot = document.getElementById('_reboot')
	,_shutdown = document.getElementById('_shutdown')        
	;

	//******************************
	// reboot the system
	var set_diskpolicy = function(){
        var data = new FormData();
        data.append('func', 'set_diskpolicy');
        //dd = document.getElementById("play_policy").childNodes;
		ele = document.getElementById("set_diskpolicy");
		dd = ele.getElementsByTagName("input");
        for (i=0;i <dd.length;i++) {
            if (dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }        
        
		POST('syswork_sv.php', data, 
			function (resp) {  
				return;  
			}
		);
    }
    
    var get_diskpolicy = function(){ 
        
        var data = new FormData();
        data.append('func', 'get_diskpolicy');

		POST('syswork_sv.php', data, function (resp) {
				ele = document.getElementById("set_diskpolicy");
				dd = ele.getElementsByTagName("input");
				for (i=0;i <dd.length;i++) {
                                 
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
        });

    }
	get_diskpolicy();

	
	//******************************
	// reboot the system
	var get_resource = function(){
		var data = new FormData();
		data.append('func', 'get_resource');

		POST('syswork_sv.php', data, function(resp) {
				document.getElementById('storage_total').value = resp.data.total;
				document.getElementById('storage_free').value = resp.data.free;
				document.getElementById('storage_used').value = resp.data.used;
				//document.getElementById('storage_media').value = resp.data.media;
				//document.getElementById('storage_garbage').value = resp.data.garbage;
		});
	}
	get_resource(); 

	// 시스템 조회코드
	function g(code, v) {
		for(i=0;i<code.length;i++) {
			if (code[i] == v) return i;
		}
		return null;
	}
	var wpa = ['NONE','WPA','WPA2', 'WPA/WPA2'];
	var use = ['_','✔'];	

	var get_netinfo = function(){
		var data = new FormData();
		data.append('func', 'get_netinfo');

		POST('syswork_sv.php', data, function(resp) {
				document.getElementById('ap_use').value = use[resp.data.ap.ap_use];
				document.getElementById('ssid').value = resp.data.ap.ssid;
				document.getElementById('sec_use').value = use[resp.data.ap.sec_use];
				document.getElementById('password').value = resp.data.ap.wpa_passphrase;
				document.getElementById('wpa').value = wpa[resp.data.ap.wpa];
				// document.getElementById('external_ip').value = resp.data.it.external_ip;
				// document.getElementById('external_port').value = resp.data.it.external_port;
		});
	}
	get_netinfo();

	var set_netinfo = function(){
		var data = new FormData();
		data.append('func', 'set_netinfo');
		
		data.append('ap_use', 		g(use, 	document.getElementById('ap_use').value)	);
		data.append('ssid', 				document.getElementById('ssid').value		);
		data.append('sec_use', 		g(use, 	document.getElementById('sec_use').value)	);;
		data.append('wpa_passphrase', 		document.getElementById('password').value	);
		data.append('wpa', 			g(wpa, 	document.getElementById('wpa').value)		);
		data.append('int_use', 		g(use, 	document.getElementById('int_use').value)	);
		data.append('interface', 	        document.getElementById('interface').value);
		data.append('operation', 	        document.getElementById('operation').value);
		data.append('ip_address', 	        document.getElementById('ip_address').value);
		data.append('ip_mask', 		        document.getElementById('ip_mask').value);
		data.append('ip_gate', 		        document.getElementById('ip_gate').value);
		data.append('ip_dns1', 		        document.getElementById('ip_dns1').value);
		data.append('ip_dns2', 		        document.getElementById('ip_dns2').value);
		data.append('ext_use', 		g(use, 	document.getElementById('ext_use').value)	);
		// data.append('external_ip', 			document.getElementById('external_ip').value);
		// data.append('external_port', 		document.getElementById('external_port').value);

		POST('syswork_sv.php', data, function(resp) {
				return;
		});
	}
	
	//******************************
	// reboot the system
	var reboot = function(){

		if ( !confirm("시스템을 재 실행합니다") ) return;
		before_down = true;
		
		document.getElementById('reboot').innerHTML="진 행 중"; 
		loadingimg = document.getElementById('reboot').appendChild(document.createElement("img"))
		loadingimg.src = "images/loading.gif";
		loadingimg.setAttribute("style", "position: absolute; top: "+(document.body.clientHeight - loadingimg.clientHeight)/2+"px; left: "+(document.body.clientWidth - loadingimg.clientWidth)/2+"px;");

		var data = new FormData();
		data.append('func', 'reboot');

		POST('syswork_sv.php', data, function(resp) {

			interval_time = 5000;
			var reboot = document.getElementById('reboot');
			var testimg = reboot.parentNode.appendChild(document.createElement("img"));
			testimg.style.display="none";

			var timer = setInterval(function (){
				testimg.onload = function() {
					if (before_down) {
						reboot.innerHTML += "/정";
					} else {
						reboot.innerHTML = "재시작 완료";
						clearInterval(timer);
						testimg.remove();
						loadingimg.remove();
					}
				};
				testimg.onerror = function() {
					before_down = false;
					//reboot.innerHTML += "/진";
				};
				testimg.src = "favicon.ico?"+Math.random();
			}, interval_time);
		});  
		
	} 
	//_reboot.addEventListener('click', reboot);				
	
	<!-- 수정  2018. 02. 12-->
	    function check8(str) {
        if (str.length < 8) alert ("비밀번호는 8자리 이상입니다.");
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
	<!-- 여기까지 -->
	
</script>   
	
</body>
</html>
