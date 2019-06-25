<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/captive.php");
?>
<!DOCTYPE html >
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <script type="text/javascript" src="signage.base.js"></script>
	<link rel="stylesheet" type="text/css" href="signage.base.css">
    <title>MyHive</title>
    <style>

    table {
        text-align : center;
    }
    table tr:nth-child(even) {
        background-color: #fff;
    }
    table tr:nth-child(odd) {
        background-color: #eee;
    }
    table, th, td {
        border-bottom: 1px solid #eee;
        border-spacing: 0px;
		padding-buttom:3pt;padding-top:3pt;
        //border-collapse:: collapse;
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
			
			<div style="text-align:center;">
<?php if ($_SESSION['uselevel'] >= 2) { ?>
				(접속코드:<input type="text" id='access_code' style="width : 4em; text-align:center; color:red; font-weight:bold;" value="" >)
				<input type="button" value="공 개" onclick="set_clsss('open')" >
				<input type="button" value="폐 쇠" onclick="set_clsss('close')" >
				<br><br> 접속화면에 "점검중입니다." 메시지 표시함.<br>일반 사용자는 이용할 수 없음.
<?php } else { ?>
				(접속코드:<input type="text" id='access_code' style="width : 4em; text-align:center; color:red; font-weight:bold;font-size:1.2em" value="" disabled >)
<?php } ?>
			</div>
			<br>

			<div id="students" style="margin: 0 auto;  width:330px; " >
			</div>
			<br>
			<hr>
			<div class="button_base" style="text-align:center;">
				<span class="button_span" onclick="javascript:logout()" >로그아웃</span>
			</div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>

<script>
    
    var retrieve = function() {
        api = 'lib/captive_sv.php';
        data = new FormData();
        data.append('func', 'retrieve');
        
        function cb_(resp) {
            fdiv = document.getElementById('students');
            fdiv.innerHTML = "";
            ftable = document.createElement('table');
                ftable.setAttribute('style', 'width:100%;');
                ftr = document.createElement('tr');
                    ftr.setAttribute('style', "font-wight:bold;color:gray;");
<?php if ($_SESSION['uselevel'] >= 2) { ?>
                    rec = "<td style='width:30%;'>이름</td>"
                         +"<td style='width:40%;' >MAC(IP)</td>";
                    rec += "<td style='width:20%;'>강퇴</td>";
<?php } else { ?>
                    rec = "<td style='width:40%;'>이름</td>"
                         +"<td style='width:50%;' >MAC(IP) addr</td>";
<?php } ?>
                    ftr.innerHTML = rec;
                ftable.appendChild(ftr);
                for (i=0; i<resp.data.length; i++) {
                    ftr = document.createElement('tr');
                        rec = "<td>"+resp.data[i].user+"</td>"
                             +"<td>"+( (resp.data[i].mac!="")
                                       ?resp.data[i].mac
                                       :"("+resp.data[i].ip+")" )+"</td>";
<?php if ($_SESSION['uselevel'] >= 2) { ?>
                        rec += "<td><img src='images/cancel.png'"
                                    +" style='width:1.2em;height:1.2em;'"
                                    +" onclick='extract(\""+resp.data[i].mac+"\",\""+resp.data[i].ip+"\");'"
                                    +" alt=\""+resp.data[i].user+"\"></td>\n";
<?php } ?>
                        ftr.innerHTML = rec;
                    ftable.appendChild(ftr);
                }
            fdiv.appendChild(ftable);
        }
        POST(api, data, cb_);
    };
    
    retrieve();


    var get_code = function(){
        api = 's00_signage.php';
        data = new FormData();
        data.append('func', 'get_code');
        
        function cb_(resp) {
            document.getElementById('access_code').value 
                = resp.data.access_code;
        }
        POST(api, data, cb_);
    }
    get_code();
<?php if ($_SESSION['uselevel'] >= 2) { ?>

    
    interval_time = 30000;
    var timer = setTimeout(function (){
        retrieve();
        get_code();
        timer = setTimeout(arguments.callee, interval_time);
    }, interval_time);

    var extract = function( mac, ip){
        data = new FormData();
        data.append('func', 'extract');
        data.append('mac', mac);
        data.append('ip', ip);
        
        POST('lib/captive_sv.php', data, function (resp) { location.reload(); });
    }

	//수업진행 상태 설정 메뉴
    var set_clsss = function ( status ) {
        var data = new FormData();
        data.append('func', 'setstatus');
        data.append('status', status);
		
        POST('lib/captive_sv.php', data, function(resp) {return;});
    }
	// 학생접속코드 설정
    var set_code = function(){
        var data = new FormData();
        data.append('func', 'set_code');
        data.append("access_code", document.getElementById("access_code").value);
		
        POST('s00_signage.php', data, function(resp) {return;});
    }
<?php } ?>

	//자기접속 해제
    var logout = function(){
        var data = new FormData();
        data.append('func', 'logout');
        
        POST('s00_signage.php', data, function(resp) {
			location.href = ".";
		});
    }
</script>
</body>
</html>
