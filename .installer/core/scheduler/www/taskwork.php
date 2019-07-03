<?php if (session_status() == PHP_SESSION_NONE)  session_start();
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
    <h2  >Scheduler - Kinicare(<?=$_SERVER['SERVER_ADDR']?>)</h2>
    <div class="container">
        <div class="contents">
        
            <!-- head note -->
            <div class='headnote' onclick="javascript:document.location.href='home.php'">스케줄작업
            </div>
            <div style="margin-top:10px;"><!-- upper line feed --></div>
            
            <div style="font-size:1em;">
                <div id="profile" style="text-align:left;">
                    <span style="display:inline-block;width:60px;text-align:left;">SEQ</span><input type="text" id='id' style="font-size:1.0em;" value="" >일련번호<br>
                    <span style="display:inline-block;width:60px;text-align:left;">S-time</span><input type="text" id='stime' style="font-size:1.0em;" value="" >시작시간<br>
                    <span style="display:inline-block;width:60px;text-align:left;">E-time</span><input type="text" id='etime' style="font-size:1.0em;" value="" >종료시간<br>
                    <span style="display:inline-block;width:60px;text-align:left;">Interval</span><input type="text" id='int' style="font-size:1.0em;" value="" >반복주기<br>
                    <span style="display:inline-block;width:60px;text-align:left;">SHELL</span><input type="text" id='sh' style="width : 22em;font-size:1.0em;" value="" ><input type='button' onclick="window.open('taskshell.php?id='+document.getElementById('id').value)" value="Open"><br>
                    <div style="text-align:center; background-color:#eee;margin-top:10px;">
                    교육 <input type="button" onclick='set_task()' value="설정" > <input type="button" onclick='get_task()' value="읽기" > <input type="button" onclick='del_task()' value="삭제" > <input type="button" onclick='get_tasks()' value="목록" >
                    </div>
                </div>
                </div>
            </div>
            <hr style="line-color:green;"> <!-- hr --->
            <div id="list" style="">
        </div>
    </div>
    <div class='footer' >Powered by AnHive Co., Ltd</div>
    <div id="listform" style="display:none;" ><div id="" onclick="document.getElementById('mbid').value = thid.id; get_task();">
    </div></div>
</body>

<script>
    
    var set_task = function(profile){
        
        var data = new FormData();
        data.append('func', 'set_task');
        
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
    
    var get_task = function(){
        
        var data = new FormData();
        data.append('func', 'get_task');
        data.append('id', document.getElementById('id').value);
        
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
    
    var del_task = function(){
        
        var data = new FormData();
        data.append('func', 'del_task');
        data.append('id', document.getElementById('id').value);
        data.append('sh', document.getElementById('sh').value);
        
        POST('s00_taskwork.php', data, function (resp) {
             get_task();
        });
    }

    var get_tasks = function(){
        
        var data = new FormData();
        data.append('func', 'get_tasks');
        
        POST('s00_taskwork.php', data, function (resp) {
            flst = document.getElementById('list');
            flst.innerHTML = "";
            for (i=0;i <resp.data.length;i++) {
                dd = resp.data[i];
                fdiv =  document.createElement("DIV");
                    fdiv.id = dd.id;
                    fdiv.style = "padding:3px;";
                    fdiv.onmouseover =function(){this.style.color='red';};
                    fdiv.onmouseout=function(){this.style.color='';};
                    fdiv.onclick = function(){
                        document.getElementById('id').value = this.id; 
                        get_task();
                    };
                    fdiv.innerHTML = dd.id+": ["+dd.stime+"-"+dd.etime+":"+dd.int+"]"+dd.sh;
                flst.appendChild(fdiv);
            }
        });
    }
    get_tasks();
    
</script>
</html>
