<?php
	require_once('../../systemclasses/Class.dbconnect.php');
	header("Content-type: image/png");
	$im = @imagecreate(1, 1)
    or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, 255, 255, 255);
	imagepng($im);
	imagedestroy($im);
	if(isset($_GET["uid"]) && isset($_GET["mailid"]))
		DBConnect::query("UPDATE site_newsletter_tracking SET `red`='1', `red_date`='" . time() . "' WHERE uid='" . addslashes($_GET["uid"]) . "' AND newsletter_id='" . addslashes($_GET["mailid"]) . "'", __FILE__, __LINE__);
?>