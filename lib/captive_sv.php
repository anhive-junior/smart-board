<?php session_start();?>
<?php 
include_once("captive.php");

$func = "";
$func= isset($_POST['func'])?$_POST["func"]:"test";
if ($func === "") die ("Require the name of service.");

function outputJSON($msg, $status = 'error'){
    header('Content-Type: application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

function _test() { 
    error_log ("Start ".__FUNCTION__);
    try {
        // Success!
        outputJSON( __FILE__.'is Available.', 'success');
     } catch (Exception $e) {
        error_log ($e->getMessage());
        outputJSON($e->getMessage());
     }
};

//////////////////////////////////////////
// 
function _extract() {

    global $capturelog;
    try {
        
        $mac=(isset($_POST['mac'])&&strlen($_POST['mac'])>0)?$_POST['mac']:"";
        $ip=(isset($_POST['ip'])&&strlen($_POST['ip'])>0)?$_POST['ip']:"";
        if ($mac.$ip == "")  outputJSON( "mac address 또는 ip가 필요합니다.");

        $list = explode("\n", get_userlist()); 
        $list_new = "";
        foreach ($list as $user) {
            //$user = ;
            if (trim($user) == "") continue;
            list( $user_f, $mac_f, $ip_f) = explode("\t", $user);
            //error_log($mac."/".$mac_f);
            if ($mac.$ip != $mac_f.$ip_f) {
                $list_new .= $user."\n";
            } else {
                exec("sudo iptables -D internet -t mangle -m mac --mac-source $mac -j RETURN");
            }
        }
        //echo $list_new;
        file_put_contents($capturelog, $list_new);
        
        outputJSON( "removed the mac[$mac],ip[$ip]", "success");
    } catch (Exception $e) {
        error_log( $e->getMessage() );
        outputJSON( $e->getMessage() );
    }
}

//////////////////////////////////////////
// 
function _retrieve() {

    global $capturelog;
    try {
        $list = explode("\n", get_userlist()); 
        $list_new = "";
        $arr1 = array();
        foreach ($list as $user) {
            if (trim($user) == "") continue;
            list( $user_f, $mac_f, $ip_f) = explode("\t", $user);
            if ( strlen($user_f) > 16) $user_f = substr($user_f, 0, 15)."...";
            array_push($arr1,
                array( 'user' => $user_f,'mac' => $mac_f,'ip' => $ip_f));
        }
        
        outputJSON( $arr1, 'success');
    } catch (Exception $e) {
        error_log( $e->getMessage() );
        outputJSON( $e->getMessage() );
    }
}

//////////////////////////////////////////
// 
function _setstatus() {

    global $freezingtag;

    try {
    
        if (!isset($_POST['status'])) {
            outputJSON( "status should defined to process of lest" );
        }
        $status = $_POST['status'];
        
        $r = null;
        switch ($status) {
            case "close":
                 file_put_contents($freezingtag, "");
                break;
            case "open":
                @unlink($freezingtag);
                break;
            default:
                break;
        }
        
        outputJSON( "set to '$status'", 'success');
    } catch (Exception $e) {
        error_log( $e->getMessage() );
        outputJSON( $e->getMessage() );
    }
}


/////////////////////////////////////
// service list
$services = array_merge(
            !isset($services)?array('null'=>'null'):$services, 
            array  ('extract'=>'_extract'
                   ,'retrieve'=>'_retrieve'
                   ,'setstatus'=>'_setstatus'
                   ,'test'=>'_test' ));


/////////////////////////////////////
// execute services
if ( isset ($services[$func]) ) {
    //error_log ("Start func [$func]");
    call_user_func( $services[$func]);
} else {
    outputJSON('Undefined service[$func].');
}

?>
