<?php
$capturelog = "/run/shm/participants.lst";
$freezingtag = "/run/shm/participants.lst.freezing";
function bypass_captive($ip) {
    //check IP Address and set to pass Server for 60 minutes
    $mac = NULL;
    try {
        if (!file_exists( "/usr/sbin/arp")) 
            throw new Exception("System is not ready to use [/usr/sbin/arp]"); 
        if (!file_exists( "/etc/hive/bin/rmtrack")) 
            throw new Exception ("System is not ready to use [/etc/hive/bin/rmtrack]"); 
        if (!file_exists( "/etc/hive/bin/cutconnect")) 
            throw new Exception ("System is not ready to use [/etc/hive/bin/cutconnect]"); 

        //get ip address and mac address.
        $mac = shell_exec("sudo /usr/sbin/arp -an $ip");
        preg_match('/..:..:..:..:..:../',$mac , $matches);
        $mac = @$matches[0];
        if ($mac !== NULL) {
        
            //allow bypassing firewall for mac address
            exec("sudo iptables -I internet 1 -t mangle -m mac --mac-source ".$mac." -j RETURN");
            exec("sudo /etc/hive/bin/rmtrack ".$ip);

            //limit to time for bypassing
            $timelimit = 60;
            exec("/etc/hive/bin/cutconnect ".$mac." ".$timelimit);
        }

    } catch (Exception $e) {
        throw $e;
    } 
    
    return $mac;
}

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
    if ( file_exists($freezingtag) ) {
        // if ( strpos(get_userlist(), $mac) !== false
             // || strpos(get_userlist(), $ip) !== false) { return false; } 
             //점검중 띄우고 level 1일때 들어가면 안되는데, 이 부분 때문에 정상 동작을 안하네요. 저번에도 제가 물어봤었는데 까먹었네요. 이거 같이 검토하셔야할 것 같습니다.
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