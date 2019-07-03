<?php if (session_status() == PHP_SESSION_NONE)  session_start();

$trace=true;
function s00_log($msg) {
    global $trace;
    if ($trace) error_log($msg);
}

function outputJSON($msg, $status = 'error'){
    if ($status == 'error') error_log (print_r($msg, true)." in ".__FILE__); 
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

///////////////////////////////////////
//

//////////////////////////////////////////////////
// schedule job
//

function add_schedule($fna, $stime, $etime, $interval){
    error_log ("Start ".__FUNCTION__);
    chmod ( $fna, 0755 );
    
    $lastno = 0;
    $fns = "/etc/hive/tasks/schedule.tasks";
    $fnt = "/etc/hive/tasks/schedule.tasks.new";
    $source = fopen($fns, "r");
    $target = fopen($fnt, "w");
    if ($source) {
        while (($line = fgets($source)) !== false) {

            if (trim($line) == "") continue;
            if (strpos($line, $fna) !== false) {
                error_log("xxxxx:$line");
             // 기존 등록된 자료는 무시
            } else {
               fputs($target, $line);
            }
            // process the line read.
            list($ln, $stimet) = explode("\t", $line, 2);
            //error_log ("ln is :".$ln);
            $lastno = max($lastno, ($ln=="")?0:$ln);
        }
        //신규건 추가
        $lastno ++;
        //$stime = strtotime($stime);
        //$etime = strtotime($etime);
        fputs($target, "$lastno\t$stime\t$etime\t$interval\t$fna\n");
        
        fclose($target);
        fclose($source);
        rename($fnt, $fns );
    } else {
        // error opening the file.
    }     
    return "enable_schedule";
}


function cancle_scheduler($fna) {
    error_log ("Start ".__FUNCTION__);
    chmod ( $fna, 0644 );
    
    $fns = "/etc/hive/tasks/schedule.tasks";
    $fnt = "/etc/hive/tasks/schedule.tasks.new";
    $source = fopen($fns, "r");
    $target = fopen($fnt, "w");
    if ($source) {
        while (($line = fgets($source)) !== false) {
            
            if (trim($line) == "") continue;
            if (strpos($line, $fna) !== false) {
             // skip writing
            } else {
               fputs($target, $line);
            }
            // process the line read.
        }
        fclose($target);
        fclose($source);
        rename($fnt, $fns );
    } else {
        // error opening the file.
    }     
    return "disable_schedule";
}



/////////////////////////////////////
// null service
$services['test'] = '_test';
function _test() { 
    s00_log ("Start ".__FUNCTION__);
    throw new Exception ( __FILE__.' is Available.');
};

/////////////////////////////////////
// 접속정보  설정
// {"id":"23"
// ,"stime":"124564535"
// ,"etime":"124564535"
// ,"int":"02"
// ,"sh":"shell"

$services['set_task'] = '_set_task';
function _set_task() { 
    s00_log ("Start ".__FUNCTION__);

    if (!isset($_POST["id"])) 
        outputJSON("Undefined task id, it may be application error.");
    $id = $_POST["id"];
    if ($id == "") outputJSON("Empty task id is not allowed.");
    
    foreach ($_POST as $k => $v) {
        if ( preg_match("/func/", $k)!=0 ) continue;
        if ( preg_match("/time/", $k)!=0 ) {
            $v = strtotime($v);
        }
        
        $task[$k] = $v;
        //error_log( "$k => $v");
    }
    
    $spath = "/etc/hive/tasks";
    if (dirname($task['sh']) == ".") $task['sh']=$spath."/".$task['sh'];
    
    if (!isset($task['stime'])) $task['stime'] = time();
    if (!isset($task['etime'])) $task['etime'] = time()+3600*365*50;
    if (!isset($task['int'])) $task['int'] = 60;

    $r = add_schedule($task['sh'],$task['stime'],$task['etime'],$task['int']);
    
    outputJSON($r, 'success');
};

/////////////////////////////////////
// 접속코드 보기
$services['get_task'] = '_get_task';
function _get_task() { 
    s00_log ("Start ".__FUNCTION__);

    if (!isset($_POST["id"])) 
        outputJSON("Undefined task id, it may be application error.");
    $id = $_POST["id"];
    if ($id == "") outputJSON("Empty task id is not allowed.");
    
    $spath = "/etc/hive/tasks";
    if (!file_exists($spath)) outputJSON("System is not available for tasks.");

    $sfile = "$spath/schedule.tasks";
    $tasks = file_get_contents($sfile);
    $arr2="";
    $arr = explode("\n", $tasks);
    foreach($arr as $a) {
        //$a=str_replace("\n", "", $a);
        if (strlen($a)==0) continue;
        if (!preg_match("/$id\t/",$a)) continue;
        
        list($x['id'],$x['stime'],$x['etime']
                    ,$x['int'],$x['sh'])= explode("\t", $a);
        $x['stime']=date("Y/m/d H:i:s", $x['stime']);
        $x['etime']=date("Y/m/d H:i:s", $x['etime']);
        $x['script'] = file_get_contents($x['sh']);
        
        $arr2=$x;
        break;
    }
    if ($arr2=="") outputJSON("ID[$id] is not exist");

    error_log($tasks);
    error_log(print_r($arr, true));
    error_log(print_r($arr2, true));
        
    outputJSON($arr2, 'success');
};

/////////////////////////////////////
// 접속코드 삭제
$services['del_task'] = '_del_task';
function _del_task() { 
    s00_log ("Start ".__FUNCTION__);

    if (!isset($_POST["id"])) 
        outputJSON("Undefined task id, it may be application error.");
    $id = $_POST["id"];
    if ($id == "") outputJSON("Empty task id is not allowed.");
    
    $r = cancle_scheduler($_POST["sh"]);
    
    outputJSON($r, 'success');
};

/////////////////////////////////////
// 접속코드 보기
$services['get_tasks'] = '_get_tasks';
function _get_tasks() {
    s00_log ("Start ".__FUNCTION__);

    $spath = "/etc/hive/tasks";
    if (!file_exists($spath)) outputJSON("System is not available for tasks.");

    $sfile = "$spath/schedule.tasks";
    $tasks = file_get_contents($sfile);
    if (trim($tasks)=="") outputJSON(array(), 'success');
    $arr2="";
    $arr = explode("\n", $tasks);
    foreach($arr as $a) {
        //$a=str_replace("\n", "", $a);
        if (strlen($a)==0) continue;
        list($x['id'],$x['stime'],$x['etime']
                    ,$x['int'],$x['sh'])= explode("\t", $a);
        $arr2[]=$x;
    }
    
    error_log($tasks);
    error_log(print_r($arr, true));
    error_log(print_r($arr2, true));
    
    outputJSON($arr2, 'success');
};


/////////////////////////////////////
// 셀 삭재
$services['set_shell'] = '_set_shell';
function _set_shell() { 
    s00_log ("Start ".__FUNCTION__);
    
    $sh = $_POST['sh'];
    $spath = "/etc/hive/tasks";
    if (dirname($sh) == ".") $sh=$spath."/".$sh;
    
    $script = $_POST['script'];
    file_put_contents($sh, $script);
    chmod($sh, 0755);
    
    outputJSON($sh, 'success');
};

/////////////////////////////////////
// 셀 삭재
$services['del_shell'] = '_del_shell';
function _del_shell() { 
    s00_log ("Start ".__FUNCTION__);

    $sh = $_POST['sh'];
    $spath = "/etc/hive/tasks";
    if (dirname($sh) == ".") $sh=$spath."/".$sh;

    unlink($sh);
    
    outputJSON("delete: ".$sh, 'success');
};

/////////////////////////////////////
// execute services
$func= isset($_POST['func'])?$_POST["func"]:"test";

if (!isset($services[$func])) 
        outputJSON("Undefined service[$func].");
try {
    call_user_func( $services[$func]);
} catch (Exception $e) {
    outputJSON($e->getMessage());
}


?>