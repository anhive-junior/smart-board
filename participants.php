<?php session_start(); 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/captive.php");
include_once("lib/lib_common.php"); // outputJSON();

$scripts="
var cb_ = function(resp) {
	fdiv = document.getElementById('students');
	fdiv.innerHTML = '';
	ftable = document.createElement('table');
	ftable.setAttribute('style', 'width:100%;');
	ftr = document.createElement('tr');
	ftr.setAttribute('style', \"font-wight:bold;color:gray;\");
";
if ($_SESSION['uselevel'] >= 2) {
	$scr_add = "var rec = \"<td style='width:30%;'>이름</td><td style='width:40%;' >MAC(IP)</td><td style='width:20%;'>강퇴</td>\";";
} else {
	$scr_add = "var rec = \"<td style='width:40%;'>이름</td><td style='width:50%;' >MAC(IP) addr</td>\";";
} $scripts.=$scr_add;
$scripts.="
	ftr.innerHTML += rec;
	ftable.appendChild(ftr);
	for (i=0; i<resp.data.length; i++) {
		ftr = document.createElement('tr');
		var rec = \"<td>\";
		rec += resp.data[i].user;
		rec += \"</td><td>\";
		rec += ((resp.data[i].mac!='')?resp.data[i].mac+'<br>'+'('+resp.data[i].ip+')':'('+resp.data[i].ip+')');
		rec += \"</td>\";
";
if ($_SESSION['uselevel'] >= 2) {
 $scr_add = "
	rec += \"<td><img src='images/cancel.png' style='width:1.2em;height:1.2em;' onclick=extract\" 
	+ \"('\" + resp.data[i].mac + \"','\";
	rec += resp.data[i].ip;
	rec += \"'\";
	rec += \"); alt='\"; 
	rec += resp.data[i].user;
	rec += \"'></td>\";
 ";
} $scripts.=$scr_add;
$scripts.="
	ftr.innerHTML += rec;
	ftable.appendChild(ftr); }
	fdiv.appendChild(ftable);
}
var ac_ = function(resp){
	document.getElementById('access_code').value = resp.data.access_code;
	document.getElementById('access_code').disabled = false;
}
";

if (isset($_POST['init']) && $_POST['init'] == 1){
	$data=array(
		"photo" => array($photo, "profile"),
		"sodata" => array($subject,$owner),
		"footer" => $footnote,
		"scripts" => $scripts
	);
	outputJSON($data, "success");
} else if (isset($_SESSION['uselevel'])){
	if ($_SESSION['uselevel'] >= 2) {
		$data=array(
		"oS" => "set_clsss('open')",
		"cS" => "set_clsss('close')",
		"mesg" => '접속화면에 "점검중입니다." 메시지 표시함.<br>일반 사용자는 이용할 수 없음.',
		"scripts" => "interval_time = 30000;
			var timer = setInterval(function (){
				retrieve();
				get_code();
			}, interval_time);

			var extract = function(mac, ip){
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
				data.append(\"access_code\", document.getElementById(\"access_code\").value);
				
				POST('s00_signage.php', data, function(resp) {return;});
			}"
		);
		outputJSON($data, "success");
	} else {
		outputJSON(1, "success"); // false
	}
}
?>