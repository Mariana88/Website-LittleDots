<?php
	require_once("systemclasses/Class.dbconnect.php");
	require_once("frontfunc/Class.files_front.php");
	require_once("aidclasses/data/Class.Files.php");
	
	session_start();
	
	$path = files_front::get_dbfile_path(urldecode($_GET["file_id"]));
	$pathinfo = pathinfo($path);
	$name = $pathinfo["filename"];
	$extention = $pathinfo["extension"];
	
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
	
	if(substr($path, 0, 1) == '/')
		$path = substr($path, 1);
	readfile($path);
?>