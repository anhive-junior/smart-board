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
    <title id = 'title1'></title>
</head>
<body>
    <div class="container">
        <div class="contents">
            <div class='headnote'></div>
            <br>
            <div style="text-align:center;">
                <span class="input_title"></span> 
                <span class="input_title"></span>
                <br>
                <br>
                <img class="img_shadow" id="photo">
            </div>
            <br>
            <div>
			<div id ="jum1"style='text-align:center;color:white;font-size:2em;margin: 0 auto; width:8em; background-color:orange;'></div>
			<div id ="disk1"style='text-align:center;color:white;font-size:2em;margin: 0 auto; width:8em; background-color:red;'></div>
            <form method='post' action='index.php' style="margin: 0 auto;  width:200px; ">
                <table>
				
                    <tr>
					<td style="text-align:right;" >사용자: </td>
					<td style="text-align:left;" >
                        <input type='text'id="ucode" name='user_code' size='7' value="<?php echo isset($_COOKIE['NAME'])?$_COOKIE['NAME']:''; ?>" style="font-size:1.2em;" autocomplete="off"><br>
                    </td>
					</tr>
					
                    <tr>
					<td style="text-align:right;" >접속코드:</td>
					<td style="text-align:left;" > 
                        <input type='text'id="icode" name='input_code' size='7'value="<?php echo isset($_COOKIE['CODE'])?$_COOKIE['CODE']:''; ?>" style="font-size:1.2em;" autocomplete="off" >
                    </td>
					</tr>
					
                    <tr>
					<td colspan=2 style="text-align:center;" >
                    <br>
                        <input id="submit" type='submit' value='로그인' style="display:none;">
						<div class="button_base" style="text-align:center;background-color:white">
							<span class="button_span" onclick="javascript:document.getElementById('submit').click()" >로그인</span>  
						</div>
                    </td>
					</tr>
                </table>
                
            </form>
            </div>
            <br>
            <div id="respdata1" style="text-align:center;font-size:0.8em;">
<?php

    //date_default_timezone_set('Asia/Seoul');
    //$anhive_code = "anhive";

    $user_code = isset($_POST['user_code'])?trim($_POST['user_code']):"";
    $input_code = isset($_POST['input_code'])?trim($_POST['input_code']):"";
	setrawcookie ("FAVORIT", 'norm1', time()+3); // homepage의 메뉴 자동선택

    error_log("------- user_code -[$user_code] -------access code-[$input_code]-------"); 
	$_SESSION['uselevel']=0;
	$destination="home.html";
	$cookie_expire = time()+60*60*24*365;
    if ( ! $user_code /* isset($_POST['user_code']) */ ) {
        $resp = "사용자와 접속코드를 입력하세요!"."-- :) <br>";
    } else if ( ! $input_code /* isset($_POST['input_code']) */ ) {
        $resp = "접속코드를 입력하세요<br>";
    } else if ( $access_code == $input_code ) {
		$_SESSION['uselevel']=1;
		$destination="cardwork.html";
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
    <div class='footer' ></div>
</body>
</html>

<script>

var headnote_data = function(){ // headnote에 대한 데이터를 가지고 옵니다.
	var data = new FormData(); 
	data.append("init", 1);
	var request = new XMLHttpRequest();
	request.onreadystatechange = function(){
		if(request.readyState == 4){
			try {
				var resp = JSON.parse(request.response);
			} catch(e){
				var resp = {
					status : 'error',
					data: 'Unknown error occurred : [' + request.responseText + ']'
				};
			}
			console.log(resp.status + ':' + resp.data);
			document.getElementsByClassName("headnote")[0].setAttribute("id","headnote1");
			document.getElementById("headnote1").innerHTML = resp.data.title;
			document.getElementById("title1").innerHTML = resp.data.sodata[0];
			
			for(var i = 0; i<resp.data.sodata.length; i++){
				document.getElementsByClassName("input_title")[i].setAttribute("id","input_title" + (i+1));
				document.getElementById("input_title" + (i+1)).innerHTML=resp.data.sodata[i];
			}
			document.getElementById("photo").setAttribute("src",resp.data.photo[0]);
			document.getElementById("photo").setAttribute("alt",resp.data.photo[1]);
			document.getElementById("photo").setAttribute("style",resp.data.photo[2]);
			
			document.getElementsByClassName("footer")[0].setAttribute("id","footer1");
			document.getElementById("footer1").innerHTML=resp.data.footer;
		}
	};
	request.open("POST", "indexcontroll.php");
	request.send(data);
}

var file_data = function(){ // headnote에 대한 데이터를 가지고 옵니다.
    var data = new FormData(); 
	data.append("file", 1);
	var request = new XMLHttpRequest();
	request.onreadystatechange = function(){
		if(request.readyState == 4){
			try {
				var resp = JSON.parse(request.response);
			} catch(e){
				var resp = {
					status : 'error',
					data: 'Unknown error occurred : [' + request.responseText + ']'
				};
			}
			console.log(resp.status + ':' + resp.data);
			if( resp.data == "0"){
				document.getElementById("jum1").style.display = "inline-block";
				
			}else{
				document.getElementById("jum1").innerHTML = resp.data;
				
			}
			
		}
	};
	request.open("POST", "indexcontroll.php");
	request.send(data);
}

var disk_data = function(){ // headnote에 대한 데이터를 가지고 옵니다.
    var data = new FormData(); 
	data.append("disk", 1);
	var request = new XMLHttpRequest();
	request.onreadystatechange = function(){
		if(request.readyState == 4){
			try {
				var resp = JSON.parse(request.response);
			} catch(e){
				var resp = {
					status : 'error',
					data: 'Unknown error occurred : [' + request.responseText + ']'
				};
			}
			console.log(resp.status + ':' + resp.data);
			if( resp.data == "0"){
				document.getElementById("disk1").style.display = "inline-block";
				
			}else{
				document.getElementById("disk1").innerHTML = resp.data;
				
			}
			
		}
	};
	request.open("POST", "indexcontroll.php");
	request.send(data);
}

headnote_data();
file_data();
disk_data();


</script>