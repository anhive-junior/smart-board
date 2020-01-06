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

// hidden and visible
// image flickers when page loads
// this function is work to image flickers prevented
window.onload = function(){
    document.body.style.visibility = "hidden";
    setTimeout("document.body.style.visibility = \"visible\"", 200);
}

// level 값 가져오기
var show_level = function(func){
    var data = new FormData();
    data.append('func','show_level');
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
                return;
            }
            //console.log("level is " + resp.data.level);
            func(resp);
        }
    };
    request.open('POST', "s00_signage.php");
    request.send(data);
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
        var div = document.createElement("div");
        div.setAttribute("id", "alert");
        div.setAttribute("style", "visibility : hidden;");
        document.getElementById("prepend").appendChild(div);
        var div = document.getElementById("alert");
        div.innerHTML = "<h1><img src='./images/bell-solid.svg'></h1>";
        div.innerHTML += "<h2 style='margin-top:15px;' id='alert_inner'></h2>";
        div.innerHTML += "<input id=\"alert_exit\" type=\"submit\" value=\"확인\"/>";
        div.innerHTML += "<input id=\"alert_cancle\" type=submit value=\"취소\" onclick=alerted('cancle'); style='display:none;background-color: #e17055;'/>";
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
        document.getElementById("alert_exit").style.display='inline-block';
        document.getElementById("alert_exit").setAttribute("onclick", "alerted('exit', '" + func + "')"); 
    }
    else {
        document.getElementById("alert_cancle").style.display="none";
        document.getElementById("alert_exit").setAttribute("onclick", "alerted('exit')"); 
    }
    loader("exit");
}

// 로딩창 띄우기
function loader(msg){
    var load = document.getElementById("load");
    if(load == null){
        var div = document.createElement("div");
        div.setAttribute("id", "load");
        div.setAttribute("class", "bg");
        document.getElementById("prepend").appendChild(div);
        var div = document.getElementById("load");
        div.innerHTML = "<div class='loader_box'></div>";
        var div = document.getElementsByClassName("loader_box")[0];
        div.innerHTML += "<div class='loader'><p>🐝</p></div>";
        div.innerHTML += "<div id='load_txt'></div>";
    }
    if (typeof msg === "undefined"){
        msg = "Loading..";
        document.getElementById("load").style.display = "block";
        document.getElementById("load_txt").innerHTML = msg;
    }
    else if(msg == "exit"){
        document.getElementById("load").style.display = "none";
    } 
    else {
        document.getElementById("load").style.display = "block";
        document.getElementById("load_txt").innerHTML = msg;
    }
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
// setInterval("banned()", 1000);
// 퇴장기능 임시적으로 막아놓음,
