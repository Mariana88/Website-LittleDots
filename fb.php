<?php
	require 'frontfunc/Class.files_front.php';
	require 'plugins/facebook/blicsm_facebook.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="plugins/facebook/facebook.css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js" type="text/javascript"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
	$fb = new blicsmFB();
	$fb->enkelMsgVan = array('136077734894');
	$fb->display_posts(30);
?>
</body>
</html>