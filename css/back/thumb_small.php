<?php
	require_once("../../aidclasses/Class.Pictures.php");
	$path = $_SERVER['DOCUMENT_ROOT'] . '/' . urldecode($_GET["path"]);
	$img = NULL;
	
	Pictures::output_resized_pic($path, 100, 70, false);
	
	/*$tmp = pathinfo($path);
	switch(strtolower($tmp["extension"]))
	{
		case "jpg":
		case "jpeg":
			$img = imagecreatefromjpeg($path);
			break;
		case "gif":
			$img = imagecreatefromgif($path);
			break;
		case "png":
			$img = imagecreatefrompng($path);
			break;
	}
	$img = Pictures::resize_picobject($img, 100, 70, false);
	switch(strtolower($tmp["extension"]))
	{
		case "jpg":
		case "jpeg":
			header('Content-type: image/jpeg');
			imagejpeg($img);
			break;
		case "gif":
			header('Content-type: image/gif');
			imagegif($img);
			break;
		case "png":
			header('Content-type: image/png');
			imagepng($img);
			break;
	}
	imagedestroy($img);*/
?>