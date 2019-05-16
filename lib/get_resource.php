<?php
if (!isset($_SESSION['resources'])) {
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/storage.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
	$storage = json_decode(file_get_contents($sfile), true);
	$storage = $storage[$pname];

	$_SESSION['reserve_space'] = $storage['reserve_space'];
	$_SESSION['resources'] = $pname;
} else {
	;
}
?>