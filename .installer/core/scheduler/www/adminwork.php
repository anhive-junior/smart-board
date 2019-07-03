<?php if (session_status() == PHP_SESSION_NONE)  session_start(); 
if ($_SESSION['login']!='admin') { 
    session_unset(); 
    header("location: /taskwork"); 
}

?>
<!DOCTYPE html >
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />   
    <link rel="stylesheet" type="text/css" href="taskwork.base.css">
    <script type="text/javascript" src="taskwork.base.js"></script>
    <title>Registration of New Device</title>
</head>
<body > 
    <div class="container">
        <div class="contents">
        
            <!-- head note -->
            <div class='headnote' onclick="javascript:document.location.href='home.php'">
            </div>
            <div style="font-size:1.2em;">
                <div style="margin-top:10px;"><!-- upper line feed --></div>
                
                <a href="taskwork.php"> 1. 원격접속정보관리화면 </a><br>
                
                <div style="margin-top:10px;"><!-- upper line feed --></div>
                <a href="proxywork.php"> 2. 원격등록서비스관리화면 </a><br>
                
                <div style="margin-top:10px;"><!-- upper line feed --></div>
            </div>
        </div>
    </div>
    <div class='footer' >Powered by AnHive Co., Ltd</div>
    <div id="listform" style="display:none;" ><div id="" onclick="document.getElementById('mbid').value = thid.id; get_mbaddress();">
    </div></div>
</body>

<script>
    
    var set_mbaddress = function(profile){
        
        var data = new FormData();
        data.append('func', 'set_mbaddress');
        
        dd = document.getElementById("profile").childNodes;
        for (i=0;i <dd.length;i++) {
            if (dd[i].nodeName !== "INPUT" || dd[i].type != "text") continue;
            data.append(dd[i].id, dd[i].value);
        }
        
        POST('s00_taskwork.php', data, 
            function (resp) {
                return;    
            }
        );
    }
    
    var get_mbaddress = function(){
        
        var data = new FormData();
        data.append('func', 'get_mbaddress');
        data.append('mbid', document.getElementById('mbid').value);
        
        POST('s00_taskwork.php', data, function (resp) {
                dd = document.getElementById("profile").childNodes;
                for (i=0;i <dd.length;i++) {
                    if (dd[i].type != "text") continue;
                    if (typeof resp.data[dd[i].id] === 'undefined') continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
        });
    }
    
    var del_mbaddress = function(){
        
        var data = new FormData();
        data.append('func', 'del_mbaddress');
        data.append('mbid', document.getElementById('mbid').value);
        
        POST('s00_taskwork.php', data, function (resp) {
             get_mbaddress();
        });
    }

    var lst_mbaddress = function(){
        
        var data = new FormData();
        data.append('func', 'lst_mbaddress');
        
        POST('s00_taskwork.php', data, function (resp) {
            flst   = document.getElementById('list');
            flst.innerHTML = "";
            for (i=0;i <resp.data.length;i++) {
                dd = resp.data[i];
                fdiv =  document.createElement("DIV");
                    fdiv.id = dd.mbid;
                    fdiv.onclick = function(){document.getElementById('mbid').value = this.id; get_mbaddress();};
                    fdiv.innerHTML = dd.mbid+": [http://"+dd.ip+":"+dd.port+"/"+dd.url+"]";
                flst.appendChild(fdiv);
            }
        });
    }
    
</script>
</html>
