<?php if (session_status() == PHP_SESSION_NONE)  session_start();
$id = $_GET['id'];

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
            <div class='headnote' onclick="javascript:document.location.href='home.php'">쉘편집
            </div>
            <div style="margin-top:10px;"><!-- upper line feed --></div>
            
            <div style="font-size:.8em;">
                <div id="profile" style="text-align:left;">
                    <input type="text" id='id' style="width : 2em;display:none;" value="<?=$id?>" ><br>
                    sh<input type="text" id='sh' style="width : 30em;" value="" ><br>
                    <textarea id='script' style="width : 360px; height:400px" ></textarea><br>
                    <div style="text-align:center; background-color:#eee;margin-top:10px;">
                    편집 <input type="button" onclick='set_shell()' value="저장" > <input type="button" onclick='get_shell()' value="읽기" > <input type="button" onclick='del_shell()' value="삭제" >
                    </div>
                </div>
                <hr style="line-color:green;"> <!-- hr --->
                <div id="list" style="">
                </div>
            </div>
        </div>
    </div>
    <div class='footer' >Powered by AnHive Co., Ltd</div>
    <div id="listform" style="display:none;" ><div id="" onclick="document.getElementById('mbid').value = thid.id; get_shell();">
    </div></div>
</body>

<script>
    
    var set_shell = function(profile){
        
        var data = new FormData();
        data.append('func', 'set_shell');
        data.append('sh', document.getElementById('sh').value);
        data.append('script', document.getElementById('script').value);
        
        POST('s00_taskwork.php', data, 
            function (resp) {
                return;    
            }
        );
    }
    
    var get_shell = function(){
        
        var data = new FormData();
        data.append('func', 'get_task');
        data.append('sh', document.getElementById('sh').value);
        data.append('id', document.getElementById('id').value);
        
        POST('s00_taskwork.php', data, function (resp) {
                dd = document.getElementById("profile").childNodes;
                for (i=0;i <dd.length;i++) {
                    if (dd[i].type != "text") continue;
                    if (typeof resp.data[dd[i].id] === 'undefined') continue;
                    dd[i].value = resp.data[dd[i].id];
                    data.append(dd[i].id, dd[i].value);
                }
                document.getElementById('script').value = resp.data['script'];
        });
    }
    
    var del_shell = function(){
        
        var data = new FormData();
        data.append('func', 'del_task');
        data.append('id', document.getElementById('id').value);
        data.append('sh', document.getElementById('sh').value);
        
        POST('s00_taskwork.php', data, function (resp) {
             ;
        });
    }

    get_shell();
    
</script>
</html>
