<?php
	require_once("systemclasses/Class.dbconnect.php");
	require_once("frontfunc/Class.url_front.php");
	require_once("systemclasses/Class.login.php");
	require_once("systemclasses/Class.url.php");
	require_once("aidclasses/data/Class.Files.php");
	
	session_start();
	if(!login::check_login())
	{
		echo '<p style="text-align: center; margin-top: 50px">U hebt niet de rechten om dit bestand te bekijken</p>';
	}
	else
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . '/userfiles/protected/' . $_GET["q"];
		$name = Files::subtract_filename($path);
		$extention = Files::subtract_extention($path);
		
		if (strstr($HTTP_USER_AGENT,"MSIE"))
		{
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-type: application-download");
			header("Content-Length: " . filesize ($path));
			header('Content-Disposition: attachment; filename="' . $name . '"');
			header("Content-Transfer-Encoding: binary");
		}
		else
		{
			header('Content-type: ' . Files::$mime_types_app[strtolower($extention)]);
			header('Content-Disposition: attachment; filename="' . $name . '"');
		} 
		
		readfile($path);
	}
?>