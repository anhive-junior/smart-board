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
    <script type="text/javascript" src="signage.base.js"></script>
	<link rel="stylesheet" type="text/css" href="signage.base.css">
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
			
			<div style="font-size:.8em;">
				<div style="text-align:center;">
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					Openbox cmd환경 설정</div>
					lxt start <input type="button" onclick="sample(document.getElementById('ltxopen').value);" value="실행">
					<input id="ltxopen" style="width:16em" value="{OPEN_TERMINAL}" disabled><br>
				</div>
				<hr style="line-color:green;"> <!-- hr -->
				
				<div style="text-align:center;">
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					슬라이드 쇼 제어</div>
					feh start <input type="button" onclick="sample(document.getElementById('slideopn').value);" value="실행">
					<input id="slideopn" style="width:16em;" value="<LEFTCTRL_DOWN><LEFTCTRL_HOLD><LEFTALT_DOWN><LEFTALT_HOLD>s<LEFTALT_UP><LEFTCTRL_UP>"><br>
					
					
					feh stop <input type="button" onclick="sample(document.getElementById('slideclose').value);" value="실행">
					<input id="slideclose" style="width:16em;" value="q"><br><br>
					<textarea id="fehset" style="width:24em;height:40px">feh -p -Y -x -q -D 5 -B black -F --zoom fill -R 3 -C ~pi/signage -e NanumGothic.woff/64 -K captions/ -r ~pi/media/playlist -nSmtime</textarea><br>
					 <input type="button" onclick="setfeh(document.getElementById('fehset').value);" value="슬라이드쇼설정"  style="width:12em;">
					 <input type="button" onclick="getfeh();" value="확인하기"  style="width:12em;"><br>
				</div>
				
				<hr style="line-color:green;"> <!-- hr -->
				<div style="text-align:center;">
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					비디오 재생 조정</div>
					Video <input type="button" onclick="sample(document.getElementById('videocmd').value);" value="실행"> 
					<textarea id="videocmd" style="width:16em;height:40px"><LEFTCTRL_DOWN><LEFTCTRL_HOLD><LEFTALT_DOWN><LEFTALT_HOLD>o<LEFTALT_UP><LEFTCTRL_UP>
					</textarea><br>
					Video <input type="button" onclick="sample(document.getElementById('videostop').value);" value="정지">
					<input id="videostop" style="width:16em;" value="[ESC]" disabled><br>					
				</div>
				<hr style="line-color:green;"> <!-- hr -->
				<div id="rpicontrol" style="text-align:center;">
					<div style="text-align:center; background-color:#ffbf00;margin:3px;padding-top:5px;padding-bottom:5px;">
					하위명령 통제</div>
					RPi 제어: 
					<input type="text" id="message" style="width:16em;" > 
					<input type="button" onclick="control();" value="요청"><br>
					<textarea style="width:24em;height:60px;background-color:#eee;">문자 KEY::::					'1','2','3','4','5', '6','7','8','9','0', '-','=','q','w','e', 'r','t','y','u','i', 'o','p','[',']','a', 's','d','f','g','h', 'j','k','l',';',''', '`','\','z','x','c', 'v','b','n','m',',', '.','/',' ','Q','W', 'E','R','T','Y','U', 'I','O','','P','A', 'S','D','F','G','H', 'J','K','L',' ','Z', 'X','C','V','B','N', 'M','!',' ','@', '#','$','%','^','&','*','(',')','_', '+','{','}',':', '"','?','>','<'
					</textarea><br>					
					<textarea style="width:24em;height:90px;background-color:#eee;">커멘트 KEYS :::: 			[RESERVED],[ESC],[1],[2],[3],[4],[5],[6],[7],[8],[9],[0],[MINUS],[EQUAL],[BACKSPACE],[TAB],[Q],[W],[E],[R],[T],[Y],[U],[I],[O],[P],[LEFTBRACE],[RIGHTBRACE],[ENTER],[LEFTCTRL],[A],[S],[D],[F],[G],[H],[J],[K],[L],[SEMICOLON],[APOSTROPHE],[GRAVE],[LEFTSHIFT],[BACKSLASH],[Z],[X],[C],[V],[B],[N],[M],[COMMA],[DOT],[SLASH],[RIGHTSHIFT],[KPASTERISK],[LEFTALT],[SPACE],[CAPSLOCK],[F1],[F2],[F3],[F4],[F5],[F6],[F7],[F8],[F9],[F10],[NUMLOCK],[SCROLLLOCK],[KP7],[KP8],[KP9],[KPMINUS],[KP4],[KP5],[KP6],[KPPLUS],[KP1],[KP2],[KP3],[KP0],[KPDOT],[ZENKAKUHANKAKU],[102ND],[F11],[F12],[RO],[KATAKANA],[HIRAGANA],[HENKAN],[KATAKANAHIRAGANA],[MUHENKAN],[KPJPCOMMA],[KPENTER],[RIGHTCTRL],[KPSLASH],[SYSRQ],[RIGHTALT],[LINEFEED],[HOME],[UP],[PAGEUP],[LEFT],[RIGHT],[END],[DOWN],[PAGEDOWN],[INSERT],[DELETE],[MACRO],[MUTE],[VOLUMEDOWN],[VOLUMEUP],[POWER],[KPEQUAL],[KPPLUSMINUS],[PAUSE],[SCALE],[KPCOMMA],[HANGEUL],[HANGUEL],[HANJA],[YEN],[LEFTMETA],[RIGHTMETA],[COMPOSE],[STOP],[AGAIN],[PROPS],[UNDO],[FRONT],[COPY],[OPEN],[PASTE],[FIND],[CUT],[HELP],[MENU],[CALC],[SETUP],[SLEEP],[WAKEUP],[FILE],[SENDFILE],[DELETEFILE],[XFER],[PROG1],[PROG2],[WWW],[MSDOS],[COFFEE],[SCREENLOCK],[DIRECTION],[CYCLEWINDOWS],[MAIL],[BOOKMARKS],[COMPUTER],[BACK],[FORWARD],[CLOSECD],[EJECTCD],[EJECTCLOSECD],[NEXTSONG],[PLAYPAUSE],[PREVIOUSSONG],[STOPCD],[RECORD],[REWIND],[PHONE],[ISO],[CONFIG],[HOMEPAGE],[REFRESH],[EXIT],[MOVE],[EDIT],[SCROLLUP],[SCROLLDOWN],[KPLEFTPAREN],[KPRIGHTPAREN],[NEW],[REDO],[F13],[F14],[F15],[F16],[F17],[F18],[F19],[F20],[F21],[F22],[F23],[F24],[PLAYCD],[PAUSECD],[PROG3],[PROG4],[DASHBOARD],[SUSPEND],[CLOSE],[PLAY],[FASTFORWARD],[BASSBOOST],[PRINT],[HP],[CAMERA],[SOUND],[QUESTION],[EMAIL],[CHAT],[SEARCH],[CONNECT],[FINANCE],[SPORT],[SHOP],[ALTERASE],[CANCEL],[BRIGHTNESSDOWN],[BRIGHTNESSUP],[MEDIA],[SWITCHVIDEOMODE],[KBDILLUMTOGGLE],[KBDILLUMDOWN],[KBDILLUMUP],[SEND],[REPLY],[FORWARDMAIL],[SAVE],[DOCUMENTS],[BATTERY],[BLUETOOTH],[WLAN],[UWB],[UNKNOWN],[VIDEO_NEXT],[VIDEO_PREV],[BRIGHTNESS_CYCLE],[BRIGHTNESS_ZERO],[DISPLAY_OFF],[WIMAX],[RFKILL],
					</textarea><br>
					<textarea style="width:24em;height:90px;background-color:#eee;">환경 CTL ::::
					&lt;LEFTCTRL_DOWN&gt;, &lt;LEFTCTRL_HOLD&gt;, &lt;LEFTCTRL_UP&gt;, &lt;RIGHTCTRL_DOWN&gt;, &lt;RIGHTCTRL_HOLD&gt;, &lt;RIGHTCTRL_UP&gt;, &lt;LEFTALT_DOWN&gt;, &lt;LEFTALT_HOLD&gt;, &lt;LEFTALT_UP&gt;, &lt;RIGHTALT_DOWN&gt;, &lt;RIGHTALT_HOLD&gt;, &lt;RIGHTALT_UP&gt;, &lt;LEFTSHIFT_DOWN&gt;, &lt;LEFTSHIFT_HOLD&gt;, &lt;LEFTSHIFT_UP&gt;, &lt;RIGHTSHIFT_DOWN&gt;, &lt;RIGHTSHIFT_HOLD&gt;, &lt;RIGHTSHIFT_UP&gt;
					</textarea><br>
					<textarea style="width:24em;height:4em;background-color:#eee;">실행 CTL ::::
					&#123;USLEEP nnnn&#125;&#123;OPEN_TERMINAL&#125;
					</textarea>					
				</div>
			</div>
			
		</div>
		<div id="vlog"></div>
	</div>
	<div class='footer' ><?=$footnote?></div>
</body>

<script>
	
    var control = function(){
		
        var data = new FormData();
        data.append('func', 'command');
        data.append('message', 
        document.getElementById("message").value);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML = resp.data;  
		});
    }

    var sample = function(msg){
		
        var data = new FormData();
        data.append('func', 'command');
        data.append('message', msg);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML = resp.data;  
		});
    }
    var setfeh = function(msg){
		
        var data = new FormData();
        data.append('func', 'setfeh');
        data.append('message', msg);
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('vlog').innerHTML = resp.data;  
		});
    }
	
    var getfeh = function(msg){
		
        var data = new FormData();
        data.append('func', 'getfeh');
		
		POST('s00_signage.php', data, 
			function (resp) {  
				document.getElementById('fehset').value = resp.data;  
		});
    }
	getfeh();
	
</script>
</html>
