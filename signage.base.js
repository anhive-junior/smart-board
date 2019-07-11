var POST = function(api, data, func ){
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

var _getChild = function (obj, pro, value) {

	if (obj.getAttribute(pro) == value) return obj;
	var cur = obj;
	var sub=null;
	var result = null;
	do {
		sub= (sub==null)?cur.firstChild:sub.nextSibling;
		if ("INPUT:TEXTAREA:DIV".indexOf( sub.nodeName ) >= 0
			&& sub.getAttribute(pro) == value) return sub;
		if (sub.hasChildNodes()) {
			result = searchChild(sub, pro, value);
			if (result != null) break;
		}
	} while(sub != cur.lastChild);
	
	return result;
} 

var _setForm = function(target, formtype) {
	var t = document.getElementById(target);
		t.innerHTML = document.getElementById(formtype).innerHTML;
}

var _setNext = function(target, formtype) {
	// target is target object
	var fdiv = document.createElement('div');
		fdiv.setAttribute('style', 'padding:4px; margin-left:20px; background-color:#ddd;');
		fdiv.innerHTML = document.getElementById(formtype).innerHTML;
	target.parentNode.insertBefore(fdiv, target.nextSibling);
}

function setCookie(cookie_name, cookie_valie, cookie_delay){
	var expire = new Date();
	expire.setDate(expire.getDate() + cookie_delay);
	cookies = cookie_name + '=' + escape(cookie_valie) + '; path=/ '; 
	if(typeof cookie_delay != 'undefined') 
		cookies += ';expires=' + expire.toGMTString() + ';';
	document.cookie = cookies;
}
 
// 쿠키 가져오기
function getCookie(cookie_name) {
	cookie_name = cookie_name + '=';
	var cookieData = document.cookie;
	var start = cookieData.indexOf(cookie_name);
	var cookie_valie = '';
	if(start != -1){
		start += cookie_name.length;
		var end = cookieData.indexOf(';', start);
		if(end == -1)end = cookieData.length;
		cookie_valie = cookieData.substring(start, end);
	}
	return unescape(cookie_valie);
}

// 알림창 띄우기
function alerted(msg){ // alerte
    var alert = document.getElementById("alert");
    if(alert == null){
        var div = "<div id='alert' style='visibility : hidden;'>";
        div += "<h1><img src='./images/bullhorn.svg'></h1>";
        div += "<h2 style='margin-top:40px;' id='alert_inner'></h2>";
        div += "<input type=\"submit\" value=\"확인\" onclick=alerted('exit'); style='margin-top:10px;font-size:20px;'/></div>";
        document.body.innerHTML += div;
    }
    if(msg == "exit"){
        document.getElementById("alert").style.visibility = "hidden";
        return;
    }
    document.getElementById("alert").style.visibility = "visible";
    document.getElementById("alert_inner").innerHTML = msg;
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