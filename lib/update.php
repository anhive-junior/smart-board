<!--
/**
 * AnHive Co., Ltd.
 * LICENSE: This source file is subject to version 0.1 of the AnHive license. 
 * If you did not receive a copy of the AnHive License and are unable to obtain it
 * please send a note to anhive@gmail.com so we can mail you a copy immediately.
 *
 * @author     AnHive Co., Ltd <anhive@gmail.com>
 * @copyright  2013-2015 AnHive Co., Ltd
 * @license    http://www.anhive.com/license/1_01.txt  AnHive License 1.01
 */
-->
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>AnHive, Upload Media File</title>
</head>
<body>
<h2>파일 업데이트</h2>

    <div style="width:450px;"><hr>
<?php

set_time_limit(1800);

$dir = isset($_GET['dir'])?$_GET['dir']:".";

chdir($dir);
$files = scandir($dir);
error_log($dir);

$exclude=".;..;";
foreach ( $files as $f) {
    error_log($f);
    if ( strpos($exclude, $f.";")!==false ) continue;
    
    if ( is_dir ( $dir.'/'.$f )) continue;
    if ( strpos($f, "html") !== false ) continue;
    if ( strpos($f, "php")!==false ) continue;
    if ( strpos($f, ".backup")!==false ) continue;
?>    
    <div name="fn" id="<?=$f?>" onclick="toggle(this)"> <?="[".$f."]"?> </div>

<?php   } ?>
    <div name="fn" id="new" onclick="toggle(this)"> <?="[new]"?> </div><br>
    </div>    <br>

    <div style="width:450px;">
        <hr>
        <input type='file' id='_file' class="medium-btn" style="width:80%;">
        <input type='button' value=' Upload! ' onclick="upload()"><br>
        <hr>
        <div class='progress_outer' style="border: 1px solid #CCC;">
            <div id='_prog' class='progress' style="width:0%;background:#50ad4e;height:20px;"></div>
        </div>
    </div>    

    <script>

var //_submit = document.getElementById('_submit'), 
    _file = document.getElementById('_file'),
    _name = "N/A",
    _dir = "<?=getcwd()?>";
    console.log("dir .. " + _dir);

//******************************
// file upload
var upload = function(){
    if(_file.files.length === 0){return;}
    if (_name == "na") {alert("click the file to update"); return;}
    if (_name == "new") { _name = "N/A";}
    var data = new FormData();
    data.append('func', 'upload');
    data.append('dir', _dir);
    data.append('name', _name );
    data.append('SelectedFile', _file.files[0]);
    var req = new XMLHttpRequest();

    req.onreadystatechange = function(){
        if(req.readyState == 4){try {var resp = JSON.parse(req.response);} catch (e){var resp = {status: 'error',data: 'Unknown error occurred: [' + req.responseText + ']'};}console.log(resp.status + ': ' + resp.data);}};

    req.upload.addEventListener('progress', function(e){
        _prog.style.width=Math.ceil(e.loaded/e.total* 100) + '%';
        _prog.innerHTML=Math.ceil(e.loaded/e.total* 100) + '%';
    }, false);
    
    req.open('POST', '../../lib/s00_file.php');
    req.send(data);
}
//_submit.addEventListener('click', upload);    


var toggle = function(e){
    
    l = document.getElementsByName('fn');
    for (i=0; i<l.length;i++) {
        l[i].setAttribute("style", "color:black;");
    }
    if ( e.getAttribute("style") != "color:red;") {
        e.setAttribute("style", "color:red;");
        _name = e.id;
      
        //old_e.setAttribute("style", "color:black;");
        //old_e = e;
    } else { 
        e.setAttribute("style", "color:black;");
        _name = "N/A";
    }
    
}        
    </script>
</body>
</html>
