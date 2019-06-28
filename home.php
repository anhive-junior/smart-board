<?php 
session_start();
 
if (!isset($_SESSION["ip"])) header("location:.");
include_once("lib/get_config.php");
include_once("lib/get_access.php");
include_once("lib/lib_common.php");

//////////
$trace=true;

function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
}
////////////////////////////////////////////////
/////////
$services['headnote'] = '_headnote';
function _headnote(){
	s00_log("Start ".__FUNCTION__);
	$data=array(
		"photo" => $_SESSION['photo'],
		"subject" => $_SESSION['subject'],
		"owner" => $_SESSION['owner'],
		"footer" => $_SESSION['footnote']
	);
	outputJSON($data, "success");
};
////////////////////////////////////////////////
/////////
$services['bottom_button'] = '_bottom_button';
function _bottom_button(){
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
};
// contents send
$services['level_contents'] = '_level_contents';
function _level_contents(){
	//entry criteria.. check condition, constraints  준비과정
	if ( !isset($_SESSION['uselevel']) ) outputJSON("error : uselevel is not defined - line :  __LINE__", "error");
	
	
	//task default process 기본 작업과정
	
	$data[]=array(
				"link" => "cardwork.html",
				"tiletitle" => "사진(이미지) 보내기",
				"ins" => "누구나 전송 가능",
				"color" => "#f86924"
	);
	
	//task extended process 확장 작업과정
	if ($_SESSION['uselevel'] >= 2){

		$data[]=array(
						"link" => "slidework.html",
						"tiletitle" =>"받은 사진 확인하기",
						"ins" =>  "하나씩 사진 정리하기",
						"color" =>"#92ABDB");
						
		$data[]=array(
						"link" => "listwork.html",
						"tiletitle" =>"재생 목록 관리하기",
						"ins" =>  "슬라이드 쇼 대상건 등록",
						"color" =>"#A55FEB");
		$data[]=array(
						"link" => "signwork.html",
						"tiletitle" =>"액자 리모트 컨트롤",
						"ins" =>  "슬라이드 쇼 관리하기",
						"color" =>"#ff9f00");
						
		$data[]=array(
						"link" => "videowork.html",
						"tiletitle" =>"비디오 컨트롤",
						"ins" =>  "비디오 재생관리",
						"color" =>"#347235");	
				
	} 
	$arr = array("contents"=>$data, "count"=>count($data));
	//validation 검증과정
	
	//exit criteria,, return 끝
	outputJSON($arr, "success");
};

$func= isset($_POST['func'])?$_POST["func"]:"test";


if (!isset($services[$func])) 
        outputJSON("Undefined service[$func].");
try {
    call_user_func( $services[$func]);
    //s00_log2(4, print_r($services,true));
} catch (Exception $e) {
    outputJSON($e->getLine().'@'.__FILE__."\n".$e->getMessage());
    s00_log(print_r($e->getTrace(),true));
}
?>
