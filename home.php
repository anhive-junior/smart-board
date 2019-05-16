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
    <title>SweetHome</title>
	<style>

    table {
        text-align : center;
    }
	
    .tile {
        margin: 5px;
        padding-top: 15px;
        padding-bottom: 15px;
        cursor : pointer;
        border: solid 1px #e4e4e4;
		color:#fff;
        background: #efefef; 
        border-radius: 2px;
    }
	.tile:active { background:green; }
	
	.tiletitle{
        padding-left: 20px;
        padding-right: 50px;
        //border-bottom: dotted 1px #e4e4e4;
        font-size:1.6em;
        font-weight:bold; 
		color:#fff;
	}
	.tiletitle:active { background:green; }

    .instruction {
        padding-left: 50px;
        padding-right: 20px;
        color:#555555;
		font-size:1.2em;
        font-weight:bold; 
		padding-top:0.6em;
    }

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
			<div style="margin-top:5px;"><!-- upper line feed --></div>
			<div style="text-align:center; margin: 0 auto; ">

				<div id='norm1' class="tile" style="background-color:#f86924" onclick='javascript:window.open("cardwork.php", "_self");' >
					<div class="tiletitle" >사진(이미지) 보내기</div>
					<div class="instruction" >-----누구나 전송 가능</div>
				</div>
<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 2) { ?>
				<div  id='norm2' class="tile" style="background-color:#92ABDB" onclick='javascript:window.open( "slidework.php", "_self");' >
					<div class="tiletitle">받은사진 확인하기</div>
					<div class="instruction" >-----하나씩 사진 정리하기</div>
				</div>
				<div  id='norm3' class="tile" style="background-color:#A55FEB" onclick='javascript:window.open(
						"listwork.php", "_self");' >
					<div class="tiletitle" >재생목록 관리하기</div>
					<div class="instruction" >-----슬라이드 쇼 대상건 등록</div>
				</div>
				<div  id='norm4' class="tile" style="background-color:#ff9f00" onclick='javascript:window.open(
						"signwork.php", "_self");' >
					<div class="tiletitle" >액자 리모트 콘트롤</div>
					<div  class="instruction" >-----슬라이드쇼 관리하기</div>
				</div>
				<div  id='norm5' class="tile" style="background-color:#347235" onclick='javascript:window.open(
						"videowork.php", "_self");' >
					<div class="tiletitle" >비디오 콘트롤</div>
					<div  class="instruction" >-----비디오 재생관리</div>
				</div>
<?php } ?>
<?php 
    if (isset($_SESSION['uselevel']) 
            && $_SESSION['uselevel'] >= 2) { 
        
        if ($_SESSION['thema'] == 'notify' ) {
            include ("plugin/thema/notify/home_sub.php");
        }
    }
?>
				
			</div>
			<div style="margin-top:5px;"><!-- upper line feed --></div>
			<div class="button_base" style="text-align:center;">
<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 2) { ?>
				<span  id='b1' class="button_span" onclick="javascript:window.open('participants.php','_self')" >사용자</span>  
<?php } ?>
<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 2) { ?>
				<span  id='b2' class="button_span" onclick="javascript:window.open('samworks.php','_self')" >앨범관리</span>
				<span  id='b3' class="button_span" onclick="javascript:window.open('playwork.php','_self')" >재생관리</span>  
<?php } ?>      
<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 3) { ?>
				<span  id='b4' class="button_span"   onclick="javascript:window.open('syswork.php','_self')" >접속관리</span>
<?php } ?>
<?php if (isset($_SESSION['uselevel']) && $_SESSION['uselevel'] >= 4) { ?>
				<span  id='b5' class="button_span" onclick="javascript:window.open('testwork.php','_self')" >테스트</span>  
				<span  id='b6' class="button_span" onclick="javascript:window.open('validator/checker.php','_self')" >검증</span>  
<?php } ?>
			</div>
			<div style="margin-top:10px;"><!-- upper line feed --></div>
		</div>
	</div>
	<div class='footer' ><?=$footnote?></div>
<script>

//auto select menu which is set in reference page (ex, index.php)
var myVar = setInterval(function() {
         return;
	if (getCookie("FAVORIT")) {
		document.getElementById(getCookie("FAVORIT")).click();
	}
	clearInterval(myVar);
}, 1000);
	
</script>	
</body>
</html>
