var POST = function(api, data, func){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            try {
                //console.log(request.response);
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            console.log(resp.status + ': ' + resp.data);
            if (resp.status == 'error') {
                //alert(resp.data);
                return;
            }
            func(resp);
        }
    };
    request.open('POST', api);
    request.send(data);
    return request;
}

var _key = function (event, id) {
    if (event.which == 13 || event.keyCode == 13) {
        //code to execute here
        elem = document.getElementById(id);
        if (typeof elem.onclick == "function") {
            elem.onclick.apply(elem);
            return false;
        }
    }
    return true;
};

// round robin click;
var _clicked = function () {
    obj = arguments[0];
    for (var i = 1; i< arguments.length-1; i++) {
        if ( obj.value==arguments[i]) {
            obj.value = arguments[i+1];
            return;
        }
    }
    obj.value = arguments[1];
} 

// 알림창 띄우기
function alerted(msg, func){ // alerte
    var alert = document.getElementById("alert");
    if ( func == undefined ){
        func = "default";
    }
    if(alert == null){
        var div = "<div id='alert' style='visibility : hidden;'>";
        div += "<h1><img src='./images/bullhorn.svg'></h1>";
        div += "<h2 style='margin-top:40px;' id='alert_inner'></h2>";
        div += "<input id=\"alert_exit\" type=\"submit\" value=\"확인\" style='margin-top:10px;font-size:20px;'/>";
        div += "<input id=\"alert_cancle\" type=submit value=\"취소\" onclick=alerted('cancle'); style='display:none;background-color: #e17055;font-size:20px;'/></div>";
        document.body.innerHTML += div;
    }
    if(msg == "exit"){
        document.getElementById("alert").style.visibility = "hidden";
        if ( !(func == "default") ) window[func]();
        return;
    } else if ( msg == "cancle" ){
        document.getElementById("alert").style.visibility = "hidden";
        return;
    }
    document.getElementById("alert").style.visibility = "visible";
    document.getElementById("alert_inner").innerHTML = msg;
    
    // !(func == "default") -> is function process
    if ( !(func == "default") ) {
        document.getElementById("alert_cancle").style.display='inline-block';
        document.getElementById("alert_cancle").style.width="45%";
        document.getElementById("alert_exit").style.display='inline-block';
        document.getElementById("alert_exit").style.width="45%";
        document.getElementById("alert_exit").setAttribute("onclick", "alerted('exit', '" + func + "')"); 
    }
    else {
        document.getElementById("alert_cancle").style.display="none";
        document.getElementById("alert_exit").style.width="55%";
        document.getElementById("alert_exit").setAttribute("onclick", "alerted('exit')"); 
    }
    loader("exit");
}

// 로딩창 띄우기
function loader(msg){
    var load = document.getElementById("load");
    if(load == null){
        var div = "<div id='load' class='bg'>";
        div += "<div class='loader_box'>";
        div += "<div class='loader'><p>🐝</p></div>";
        div += "<div id='load_txt'></div>";
        div += "</div>";
        div += "</div>";
        document.body.innerHTML += div;
    }
    if (msg == undefined){
        msg = "Loading..";
    }
    else if(msg == "exit"){
        document.getElementById("load").style.visibility = "hidden";
        return;
    }
    document.getElementById("load").style.visibility = "visible";
    document.getElementById("load_txt").innerHTML = msg;
}

// limit time 최대 접속시간,

function limit_time(){
    var time = 600000; // 10분
    setTimeout(function() {
      window.open("index.html?timeout", "_self");
    }, time);
}
// limit_time()

// 퇴장 기능
function banned(){
    var data = new FormData();
    data.append("func", "banned");
    POST("lib/captive_sv.php", data, function(resp){
        if( resp.data == "banned") {
            window.open("index.html?banned", "_self");
        }
    });
}
setInterval("banned()", 1000);
