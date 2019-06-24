<?php 
session_start();
 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

if (isset($_POST['init']) && $_POST['init'] == 1){
	$data=array(
		"photo" => array($photo, "profile"),
		"sodata" => array($subject,$owner),
		"footer" => $footnote
	);
	outputJSON($data, "success");
} else if (isset($_POST['button']) && $_POST['button'] == 1){
	if(isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >=2){
		$data=array(
			"link" => array("participants.html", "samworks.html", "playwork.html"),
			"spanInner" => array("사용자", "앨범관리", "재생관리")
		);
		if(isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >=3){
			array_push($data["link"], "syswork.html");
			array_push($data["spanInner"], "접속관리");
			if(isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >=4){
				array_push($data["link"], "testwork.html");
				array_push($data["spanInner"], "테스트");
				array_push($data["link"], "validator/checker.html");
				array_push($data["spanInner"], "검증");
			}
		}
		outputJSON($data, "success");
	}
	outputJSON("0", "success");
}
// contents send
if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] == 1){
	$data=array(
			"link" => array("cardwork.html"),
			"tiletitle" => array("사진(이미지) 보내기"),
			"ins" => array("누구나 전송 가능"),
			"color" => array("#f86924")
	);
	outputJSON($data, "success");
}
else if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 2){
	$data=array(
		"link" => 
			array("cardwork.html", 
			"slidework.html", 
			"listwork.html", 
			"signwork.html",
			"videowork.html"),
		"tiletitle" => 
			array("사진(이미지) 보내기",
			 "받은 사진 확인하기",
			 "재생 목록 관리하기",
			 "액자 리모트 컨트롤",
			 "비디오 컨트롤"),
		"ins" => 
			array("누구나 전송 가능",
			 "하나씩 사진 정리하기",
			 "슬라이드 쇼 대상건 등록",
			 "슬라이드 쇼 관리하기",
			 "비디오 재생관리"),
		"color" =>
			array("#f86924",
			 "#92ABDB",
			 "#A55FEB",
			 "#ff9f00",
			 "#347235")
	);
	outputJSON($data, "success");
}
?>
