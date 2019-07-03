<?php
    $pname = file_get_contents("../custom/default");
    $sfile = "../custom/".$pname."/profile.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $config = json_decode(file_get_contents($sfile), true);
    $config = $config[$pname];

    $title = $_SESSION['title'] = $config['title'];
    $subject = $_SESSION['subject'] = $config['subject'];
    $owner = $_SESSION['owner'] = $config['owner'];
    $footnote = $_SESSION['footnote'] = $config['footnote'];;
    $photo = $_SESSION['photo'] = $config['photo'];
    $config_media = $_SESSION['media'] = $config['media'];
    $config_contents = $_SESSION['contents'] = $config['contents'];
    $config_slide = $_SESSION['slide'] = $config['slide'];
    $config_video = $_SESSION['video'] = $config['video'];
    $config_caption = $_SESSION['caption'] = $config['caption'];
    $config_playlist = $_SESSION['playlist'] = $config['playlist'];
    $config_playlink = $_SESSION['playlink'] = $config['playlink'];
    $config_thumbs = $_SESSION['thumbs'] = $config['thumbs'];
    
    $pname = file_get_contents("../custom/default");
    $sfile = "../custom/".$pname."/access.conf";
    if (!file_exists($sfile)) file_put_contents($sfile,"");
    $config = json_decode(file_get_contents($sfile), true);
    $config = $config[$pname];

    $surprisebox_code    = $_SESSION['surprisebox_code'] = $config['surprisebox_code'];
    $gate_server     = $_SESSION['gate_server'] = $config['gate_server'];
    $access_code     = $_SESSION['access_code'] = $config['access_code'];
    $admin_code      = $_SESSION['admin_code'] = $config['admin_code'];
    $sam_code        = $_SESSION['sam_code'] = $config['sam_code'];
    $ssid            = $_SESSION['ssid'] = $config['ssid'];
    $wifi_password   = $_SESSION['wifi_password'] = $config['wifi_password'];
    $_SESSION['access'] = $pname;
    

?>
<!DOCTYPE html >
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../favicon.ico" type="image/x-icon" />   
    <link rel="stylesheet" type="text/css" href="../signage.base.css">
    <script type="text/javascript" src="../signage.base.js"></script>
    <title>First Setup</title>
</head>
<body > 
    <div class="container">
        <div class="contents">
        
            <!-- head note -->
            <div class='headnote' ">
                <span class="input_title">System Setup</span> 
            </div>
            <div style="margin-top:10px;"><!-- upper line feed --></div>
            
            <div id="profile" style="font-size:.8em;width:100%;display:float; text-align:center;">
                <div id="class" style="width:370px;margin: 0 auto;text-align:left;">
                    <div style="margin-top:10px;"><!-- upper line feed --></div>
                <table ><tr><td>
                    일반사용자 접속코드:</td><td><input type="text" id='access_code' style="width : 8em; text-align:center; color:red; font-weight:bold;" value="<?=$access_code?>" >
                    <br> 외부에서 사진을 올릴때 사용하는 접속번호입니다.
                    </td></tr><tr><td>
                    액자관리자 접속코드:</td><td><input type="text" id='sam_code' style="width : 8em; text-align:center; color:red; font-weight:bold;" value="<?=$sam_code?>" > <br><br>
                    액자 사진을 지우거나 수정할 때 사용합니다.
                    </td></tr><tr><td>
                    원격접속 액자식별번호:</td><td><input type="text" id='surprisebox_code' style="width : 8em; text-align:center; color:red; font-weight:bold;" value="<?=$surprisebox_code?>" ><br>
                    인터넷으로 접속할 때 사용하는 액자 번호입니다.  전화번호를 사용하면 편리합니다.
                    </td></tr><tr><td>
                    원격접속 지원서버</td><td><input type="text" id='gate_server' style="width : 8em; text-align:center; color:red;" value="<?=$gate_server?>" > <br>
                    인터넷 접속을 지원하는 호스트 시스템입니다.
                    기본은 (thebolle.com)이고 개별적으로 개설하여 사용할 수 있습니다.
                    </td></tr><tr><td>
                    현장접속 (WiFi):</td><td>문선코드 <input type="text" id='ssid' style="width : 8em; text-align:center; color:red; font-weight:bold;" value="<?=$ssid?>" > <br>비밀번호  <input type="text" id='wifi_password' style="width : 8em; text-align:center; color:red;" value="<?=$wifi_password?>" > <br>
                    액자와 직접 통신하는 방법입니다. 인터넷과 연결되어 잇는 경우 무선공유기로도 사용가능합니다.
                    </td></tr>
                    </table>
                    <div style="text-align:center; background-color:#eee;margin-top:10px;">
                    코드 <input type="button" onclick='javascript:set_code()' value="설정" >
                    <input type="button" onclick='javascript:get_code()' value="보기" >
                    </div>
                    <div style="margin-top:10px;"><!-- upper line feed --></div>
                </div>
                <hr style="line-color:green;"> <!-- hr --->
            </div>
            
        </div>
    </div>
    <div class='footer' ><?=$footnote?></div>
</body>

<script>
    
    var set_subject = function(profile){
        
        var data = new FormData();
        data.append('func', 'set_subject');
        
        dd = document.getElementsByTagName("INPUT");
        for (i=0;i <dd.length;i++) {
            if (dd[i].nodeName !== "INPUT" || dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }
        data.append('nfile', document.getElementById('nfile').files[0] );
        
        POST('../s00_signage.php', data, 
            function (resp) {
                return;    
            }
        );
    }
    
    var get_subject = function(profile){
        
        var data = new FormData();
        data.append('func', 'get_subject');
        if (typeof profile != 'undefined') data.append('profile', profile);
        
        POST('../s00_signage.php', data, function (resp) {
                dd = document.getElementsByTagName("INPUT");
                for (i=0;i <dd.length;i++) {
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
                document.getElementById('photo').src = resp.data.photo+"?"+Math.random();
        });
    }
    
    var set_code = function(){
        var data = new FormData();
        data.append('func', 'set_code');
        dd = document.getElementsByTagName("INPUT");
        for (i=0;i <dd.length;i++) {
            if (dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }        
        
        POST('../s00_signage.php', data, 
            function (resp) {  return;  }
        );
    }
    
    var get_code = function(profile){
        
        var data = new FormData();
        data.append('func', 'get_code');
        if (typeof profile != 'undefined') data.append('profile', profile);

        POST('../s00_signage.php', data, function (resp) {
                dd = document.getElementsByTagName("INPUT");
                for (i=0;i <dd.length;i++) {
                                 
                    if (dd[i].type != "text") continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
        });

    }
    
</script>
</html>
