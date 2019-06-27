<?php
if (!isset($_SESSION['profile'])) {
	$pname = file_get_contents("custom/default");
	$sfile = "custom/".$pname."/profile.conf";
	if (!file_exists($sfile)) file_put_contents($sfile,"");
	$config = json_decode(file_get_contents($sfile), true);
	$config = $config[$pname];

	$title = $_SESSION['title'] = $config['title'];
	$subject = $_SESSION['subject'] = $config['subject'];
	$owner = $_SESSION['owner'] = $config['owner'];
	$footnote = $_SESSION['footnote'] = $config['footnote'];;
	$photo = $_SESSION['photo'] = $config['photo'];
	$config_media = $_SESSION['media'] = $config['media'];
	$config_contents = $_SESSION['contents'] = $config['contents'];
	$config_slide = $_SESSION['slide'] = $config['slide'];
	$config_video = $_SESSION['video'] = $config['video'];
	$config_caption = $_SESSION['caption'] = $config['caption'];
	$config_playlist = $_SESSION['playlist'] = $config['playlist'];
	$config_playlink = $_SESSION['playlink'] = $config['playlink'];
	$config_thumbs = $_SESSION['thumbs'] = $config['thumbs'];
	$config_info = $_SESSION['info'] = $config['info'];
	$config_thema = $_SESSION['thema'] = $config['thema'];
	//error_log("config_thumbs[$config_thumbs]"); 
	$_SESSION['profile'] = $pname;
} else {
	$title = $_SESSION['title'];
	$subject = $_SESSION['subject'];
	$owner = $_SESSION['owner'];
	$footnote = $_SESSION['footnote'];
	$photo = $_SESSION['photo'];
	$config_media = $_SESSION['media'];
	$config_contents = $_SESSION['contents'];
	$config_slide = $_SESSION['slide'];
	$config_video = $_SESSION['video'];
	$config_caption = $_SESSION['caption'];
	$config_playlist = $_SESSION['playlist'];
	$config_playlink = $_SESSION['playlink'];
	$config_thumbs = $_SESSION['thumbs'];
	$config_info = $_SESSION['info'];
	$config_thema = $_SESSION['thema'];
}

?>
