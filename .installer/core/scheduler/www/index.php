<?php session_start(); 
session_unset(); 

function outputJSON($msg, $status = 'error'){

    if ($status == 'error') error_log (print_r($msg, true)." in ".__FILE__); 
    
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}
function wget_post( $url,  $data) {
    // use key 'http' even if you send the request to https://...
    //if ($data == null) $data = array(""=>"");
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

function getserver($album_code) {
    
    $md5_id = md5($album_code); // generalize name of it to ascii code
    $sfile = ".db/systems/$md5_id/device.conf";
    if (!file_exists(dirname($sfile))) 
        throw new Exception("등록되지 않은 장비(액자)입니다.");
    if (!file_exists($sfile)) 
        throw new Exception("등록된 장비(액자)정보가 없습니다.");

    return json_decode(file_get_contents($sfile), true);
}
    
$echomessage =  "정확한 접속코드, 사용자이름 입력하세요";
$resp = "taskwork.php";
if (isset($_POST['user_code'])) {
    //date_default_timezone_set('Asia/Seoul');
    //$album_code = isset($_POST['album_code'])?trim($_POST['album_code']):"";
    $user_code = isset($_POST['user_code'])?trim($_POST['user_code']):"";
    $input_code = isset($_POST['input_code'])?trim($_POST['input_code']):"";

    
    if ( ($user_code == "AnHive") && ( $input_code == "2062.2065" ) ) {
        $_SESSION['login']='admin';
        header("location:adminwork.php");
        die();
    }
    
    $password=file_get_contents(".password");
    if ( preg_match ('/'.$user_code.":".$input_code.";/", $password) ) {
        //check system access previlegi and return a url to redirect
        try {
            setrawcookie ("USER", $user_code, time()+60*60*24*365);
            setrawcookie ("ACCESS", $input_code, time()+60*60*24*365);
            //$resp= $server.'/'.$resp; 
            //$echomessage = $resp;
            header("location: $resp");

        }catch (Exception $e) {
            $echomessage = $e->getMessage();
        }
    }        
}

?>
<!DOCTYPE html >
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <link rel="stylesheet" type="text/css" href="taskwork.base.css">
    <title>SlowGate</title>
    <style>

    </style>
</head>
<body>
    <div class="container">
        <div class="contents">
            <div class='headnote'>스케쥴 작업관리하면</div>
            <br>
            <div style="text-align:center;">
                <span class="input_title">접속 번호를 입력하세요</span>
                <br>
            </div>
            <br>
            <div>
           
            <form method='post' action='index.php' style="margin: 0 auto;  width:250px; ">
                <table>
                    <tr><td style="text-align:right;" >
                    사용자명: 
                    </td><td style="text-align:left;" >
                        <input type='text' name='user_code' value="<?php echo isset($_COOKIE['USER'])?$_COOKIE['USER']:''; ?>" style="font-size:1.2em;width:8em;" autocomplete="off"><br>
                    </td></tr>
                    <tr><td style="text-align:right;" >
                    접속코드: 
                    </td><td style="text-align:left;" > 
                        <input type='text' name='input_code' style="font-size:1.2em;width:8em;" autocomplete="off" value="<?php echo isset($_COOKIE['ACCESS'])?$_COOKIE['ACCESS']:''; ?>">
                    </td></tr>
                    <tr><td colspan=2 style="text-align:center;" >
                    <br>
                        <input id="submit" type='submit' value='접속' style="display:none;">
                        <div class="button_base" style="text-align:center;background-color:white">
                            <span class="button_span" onclick="javascript:document.getElementById('submit').click()" >접속</span>  
                        </div>
                    </td></tr>
                </table>
                
            </form>
            </div>
            <br>
            <div style="text-align:center;font-size:0.8em;">
            <?php  echo $echomessage; ?>
            </div>
        </div>
    </div>
    <div class='footer' >Powered by AnHive Co., Ltd</div>
</body>
</html>
