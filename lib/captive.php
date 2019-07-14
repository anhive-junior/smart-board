<?php
$capturelog = "/run/shm/participants.lst";
$freezingtag = "/run/shm/participants.lst.freezing";

function get_mac($ip) {
    //check IP Address and set to pass Server for 60 minutes
    $mac = NULL;
    try {
        if (!file_exists( "/usr/sbin/arp")) 
            throw new Exception("System is not ready to use [/usr/sbin/arp]"); 

        //get ip address and mac address.
        $mac = shell_exec("sudo /usr/sbin/arp -an $ip");
        preg_match('/..:..:..:..:..:../',$mac , $matches);
        $mac = @$matches[0];

    } catch (Exception $e) {
        throw $e;
    } 
    
    return $mac;
}
function capture_user($user, $ip, $mac) {
    global $capturelog;
    try {
        $list = explode("\n", get_userlist()); 
        $list_new = "";
        $exist = false;
        foreach ($list as $record) {
            //$user = ;
            if (trim($record) == "") continue;
            list( $user_f, $mac_f, $ip_f) = explode("\t", $record);
            $list_new .= ($mac.$ip != $mac_f.$ip_f)?"$record\n":"";
        }
        $list_new .= "$user\t$mac\t$ip\n";
        file_put_contents($capturelog, $list_new);
    } catch (Exception $e) {
        error_log( $e->getMessage() );
    }
}

function is_accessible($mac, $ip) {
    global $capturelog;
    return 
        (strpos( file_get_contents($capturelog), "$mac\t$ip")!==false)
            ?true:false;
}

function is_freezed($mac, $ip) {
    global $freezingtag;
    //error_log ( file_exists($freezingtag)?"freezing exist":"allow ..to accsss");
    error_log("!!!!!!!!!!!!!!!!!!!!!!!!!!!!! is freezed : $mac $ip");
    if ( file_exists($freezingtag) ){
        return true;
    } 
    return false;
}

function get_userlist() {
    global $capturelog;
    //check IP Address and set to pass Server for 60 minutes
    return file_get_contents($capturelog);
}    
    
?>