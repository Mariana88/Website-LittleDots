<?php
	require_once("systemclasses/Class.mainconfig.php");
	require_once("systemclasses/Class.login.php");
	require_once("systemclasses/Class.dbconnect.php");
	require_once("frontfunc/Class.url_front.php");
	require_once("systemclasses/Class.url.php");
	require_once("frontfunc/Class.login_front.php");
	require_once("frontfunc/Class.page_front.php");
	require_once("frontfunc/Class.files_front.php");
	require_once("aidclasses/data/Class.data_description.php");	
	require_once("components/Class.inline_edit.php");
	session_start();
	
	if(!isset($_SESSION["LANGUAGE"]))
	{
		if(ereg("nl", $_SERVER["HTTP_ACCEPT_LANGUAGE"]))
			$_SESSION["LANGUAGE"] = "NL";
		else
			$_SESSION["LANGUAGE"] = "EN";
	}
	
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	include ('frontfunc/functions.php');
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel="icon" href="http://www.deberengieren.be/favicon.ico">
<link rel="stylesheet" type="text/css" href="/css/front/styles.css">
<link rel="stylesheet" href="/plugins/prettyphoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<link href="/plugins/scrollbar/jquery.mCustomScrollbar.css" rel="stylesheet" type="text/css" />

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js" type="text/javascript"></script>
<!--<script type="text/javascript" src="/plugins/jquery/js/jquery-1.4.2.min.js"></script> -->
<script type="text/javascript" src="/js/front.js"></script>
<script src="/plugins/prettyphoto/js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script src="/plugins/scrollbar/jquery.easing.1.3.js" type="text/javascript"></script>
<script src="/plugins/scrollbar/jquery.mousewheel.min.js" type="text/javascript"></script>
<script src="/plugins/scrollbar/jquery.mCustomScrollbar.js" type="text/javascript"></script>
<script src="/plugins/flowplayer/example/flowplayer-3.2.6.min.js"></script>
<script src="/plugins/flowplayer/example/flowplayer.playlist-3.0.8.min.js"></script>
</head>
<body style="background-color:#999;">
	<div style="width: 400px; background-color:#666; border-radius: 20px; margin: 100px auto; padding: 20px; font-family:Georgia, 'Times New Roman', Times, serif; font-size: 14px; color:#FFF;">
    <a href="http://www.deberengieren.be"><img src="/newsl/img/2/logo_black.jpg" style="float: left;"></a>
    	<div style="float:right; width: 260px; margin-top: 10px;">
			<?php
                //checken of het een bestaande email is 
                $res_contact = DBConnect::query("SELECT * FROM man_contact WHERE `id`='" . addslashes($_GET["con_id"]) . "'", __FILE__, __LINE__);
                if($row = fetch_db($res_contact))
                {
                   	if($_GET["bev"] == "true")
					{
						DBConnect::query("UPDATE man_contact SET `newsletter`='0' WHERE `id`='" . addslashes($_GET["con_id"]) . "'", __FILE__, __LINE__);
						echo '<span style="font-size: 16px;">Hi ' . $row["name"] . '</span><br><br>You won\'t receive our newsletter again. If you change your mind, you can resubscibe on our homepage <a href="http://www.deberengieren.be/EN">www.deberengieren.be</a>';
					}
					else
					{
						echo '<span style="font-size: 20px;">Hi ' . $row["name"] . ',</span><br><br>Are you sure you don\'t want to receive the next newsletter?
								<br><a href="/newsletter_unsubscribe.php?con_id=' . $_GET["con_id"] . '&bev=true"><div style="float:left; clear:both; padding: 5px 10px 5px 10px; border-radius: 5px; background-color: #999999; text-decoration: none; margin-top: 20px;">Yes, unsubscribe</div></a>';
					}
                }
                else
                {
                    echo 'Your contact was not found in our database';	
                }
            ?>
        </div>
        <div style="clear:both; height: 0px;"></div>
    </div>
</body>
</html>