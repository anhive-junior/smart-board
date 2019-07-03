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


<?php
include_once("lib/lib_common.php");
    $name = $_GET["name"];
    if (strpos($name, 'http') !== false) {
        $url = $name;
        outputJSON($url,"success");
    } else {
        //$filename = basename ($name);  //c
        $filename = $name;  //c
        $dir= isset($_GET['dir'])?$_GET['dir']:'';
        //$script_path = dirname(trim($_SERVER['SCRIPT_NAME']));
        //if ($script_path != "") $dir = $script_path."/".$dir;
        $uri = $dir."/".$filename;
        outputJSON($url,"success");
    }
?>
