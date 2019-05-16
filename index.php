<?php session_start(); 
session_unset(); 
error_log(__FILE__."::".session_id());

include_once("lib/get_config.php");
include_once("lib/get_access.php");
	
// for teacher subject
$anonymous = (file_exists("ANONYMOUS_USE"))? true : false;
if (!$anonymous) $_SESSION["scope"] = $pname;
    
?>
<!DOCTYPE html >
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <link rel="stylesheet" type="text/css" href="signage.base.css">
    <title><?=$subject?></title>
</head>
<body>
    <div class="container">
        <div class="contents">
            <div class='headnote'><?=$title?></div>
            <br>
            <div style="text-align:center;">
                <span class="input_title"><?=$subject?></span> 
                <span class="input_title"><?=$owner?></span>
                <br>
                <br>
                <img class="img_shadow" id="photo" src="<?=$photo?>" alt="photo" style="width:50%;">
            </div>
            <br>
            <div>
<?php 
      if ( file_exists("/run/shm/participants.lst.freezing") ) { 
         echo "<div style='text-align:center;color:white;font-size:2em;margin: 0 auto; width:8em; background-color:orange;' >점 검 중</div>";
      } 
	  $freespace = disk_free_space(".")/1024;
      if (  $freespace < 16 ) { 
         echo "<div style='text-align:center;color:white;font-size:2em;margin: 0 auto; width:8em; background-color:red;' >저장공간 부족.</div>";
      } 
?>
            
            <form method='post' action='index.php' style="margin: 0 auto;  width:200px; ">
                <table>
                    <tr><td style="text-align:right;" >
                    사용자: 
                    </td><td style="text-align:left;" >
                        <input type='text' name='user_code' size='7' value="<?php echo isset($_COOKIE['NAME'])?$_COOKIE['NAME']:''; ?>" style="font-size:1.2em;" autocomplete="off"><br>
                    </td></tr>
                    <tr><td style="text-align:right;" >
                    접속코드: 
                    </td><td style="text-align:left;" > 
                        <input type='text' name='input_code' size='7' style="font-size:1.2em;" autocomplete="off" value="<?php echo isset($_COOKIE['CODE'])?$_COOKIE['CODE']:''; ?>">
                    </td></tr>
                    <tr><td colspan=2 style="text-align:center;" >
                    <br>
                        <input id="submit" type='submit' value='로그인' style="display:none;">
						<div class="button_base" style="text-align:center;background-color:white">
							<span class="button_span" onclick="javascript:document.getElementById('submit').click()" >로그인</span>  
						</div>
                    </td></tr>
                </table>
                
            </form>
            </div>
            <br>
            <div style="text-align:center;font-size:0.8em;">
<?php

    //date_default_timezone_set('Asia/Seoul');
    //$anhive_code = "anhive";

    $user_code = isset($_POST['user_code'])?trim($_POST['user_code']):"";
    $input_code = isset($_POST['input_code'])?trim($_POST['input_code']):"";
	setrawcookie ("FAVORIT", 'norm1', time()+3); // homepage의 메뉴 자동선택

    error_log("------- user_code -[$user_code] -------access code-[$input_code]-------"); 
	$_SESSION['uselevel']=0;
	$destination="home.php";
	$cookie_expire = time()+60*60*24*365;
    if ( ! $user_code /* isset($_POST['user_code']) */ ) {
        $resp = "사용자와 접속코드를 입력하세요!"."-- :) <br>";
    } else if ( ! $input_code /* isset($_POST['input_code']) */ ) {
        $resp = "접속코드를 입력하세요<br>";
    } else if ( $access_code == $input_code ) {
		$_SESSION['uselevel']=1;
		$destination="cardwork.php";
    } else if ( $sam_code == $input_code /* && !$anonymous */ ) {
		if (  $freespace < 16 ) @unlink ( "emergency.dumy" );
		$_SESSION['uselevel']=2;
    } else if ( $admin_code == $input_code ) {
		if (  $freespace < 16 ) @unlink ( "emergency.dumy" );
		$_SESSION['uselevel']=3;
    } else if ( $anhive_code == $input_code ) {
		if (  $freespace < 16 ) @unlink ( "emergency.dumy" );
		$_SESSION['uselevel']=4;
    } else {
        $resp = "접속코드를 확인해 주세요!";
    }

	if ($_SESSION['uselevel']>0) {
		setrawcookie ("NAME", $user_code, $cookie_expire);
		setrawcookie ("CODE", $input_code, $cookie_expire);
        header("location:welcome.php?dst=$destination&user=$user_code");
	} else {
		echo $resp;
	}
	
?>

            </div>
        </div>
    </div>
    <div class='footer' ><?=$footnote?></div>
</body>
</html>
