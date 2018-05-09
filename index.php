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
	require_once("aidclasses/Class.video.php");	
	require_once("aidclasses/Class.Pictures.php");	
	include ('frontfunc/functions.php');
	include('plugins/bandpage/bandpage-sdk.php');
	session_start();
	
	if(!isset($_SESSION["LANGUAGE"]))
	{
		$_SESSION["LANGUAGE"] = autodetect_language();
	}
	
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	url_front::analyse_url();
	$_SESSION["LANGUAGE"] = "EN";
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="icon" href="http://www.littledots.info/favicon.png">
<link rel="stylesheet" type="text/css" href="/css/front/styles.css">
<link rel="stylesheet" href="/plugins/prettyphoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="/plugins/fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
<script type="text/javascript" src="/plugins/fancybox/source/jquery.fancybox.js?v=2.1.5"></script>
<script async src="//www.bandpage.com/extensionsdk"></script>
<script type="text/javascript" src="http://www.youtube.com/player_api"></script>
<script src="http://a.vimeocdn.com/js/froogaloop2.min.js"></script>
<script type="text/javascript" src="/js/front.js"></script>
<?php
	if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad'))
	{
		echo '<script src="/plugins/flowplayer/example/flowplayer.ipad-3.2.13.js"></script>';
		echo '<script language="javascript">
				window.macdevice = true;
			</script>';
	}
	else
		echo '<script src="/plugins/flowplayer/example/flowplayer-3.2.6.min.js"></script>';
?>
<script src="/plugins/flowplayer/example/flowplayer-3.2.6.min.js"></script>
<script src="/plugins/flowplayer/example/flowplayer.playlist-3.0.8.min.js"></script>

<script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<?php
	$seo = page_front::seo(url_front::get_last_page_id(), $_SESSION["LANGUAGE"]);
	echo '<TITLE>' . $seo["title"] . '</TITLE>
			<META NAME="description" LANG="' . $_SESSION["LANGUAGE"] . '" CONTENT="' . $seo["description"] . '">
			<META NAME="keywords" LANG="' . $_SESSION["LANGUAGE"] . '" CONTENT="' . $seo["keywords"] . '">';
?>

<!--VOOR FACEBOOK
OPEN GRAPH TAGS-->
<meta property="og:title" content="<?php echo $seo["title"]; ?>"/>
<meta property="og:url" content="http://www.littledots.info"/>
<meta property="og:image" content="http://www.littedots.info/css/front/background.jpg"/>
<meta property="og:site_name" content="Little Dots Official Website"/>
<meta property="og:description" content="<?php echo $seo["description"]; ?>"/>
</head>
<body>
<?php
	
	$res_home = DBConnect::query("SELECT site_homecfg.*, site_homecfg_lang.* FROM site_homecfg, site_homecfg_lang WHERE site_homecfg.id=site_homecfg_lang.lang_parent_id AND site_homecfg_lang.lang='" . $_SESSION["LANGUAGE"] . "'", __FILE__, __LINE__);
	$row_home = mysql_fetch_array($res_home);
	//$_SESSION["underconstruction_login"] = "false";
	if($row_home["under_cons"] > 0 && $_SESSION["underconstruction_login"] != "true")
	{
		//if(md5($_POST["underconstruction_login"]) == $row_home["under_cons_pwd"])
		if(md5(substr(url_front::$url_extra, 1)) == $row_home["under_cons_pwd"])
		{
			
			$_SESSION["underconstruction_login"] = "true";
			page_front::publish(((url_front::get_last_page_id())?url_front::get_last_page_id():$row_home["homepage"]), $_SESSION["LANGUAGE"]);
			
			
		}
		else
		{
			include("under_construction.php");
		}
	}
	else
	{
		page_front::publish(((url_front::get_last_page_id())?url_front::get_last_page_id():$row_home["homepage"]), $_SESSION["LANGUAGE"]);
	}
	
?>
<div style="clear: both; height: 50px;">&nbsp;</div>
</body>
</html>

<script language="javascript">
var hrefsplit = document.location.href.split('#!');
if(hrefsplit[1] !== undefined)
{
	//laden van de ajaxpagina
	loadpage(document.location.href);
}
$("#site_background").load(function(){
	//background
	//$(this).attr("original_height", $(this).height());
	//$(this).attr("original_width", $(this).width());
	background_position();
	
	if($(this).attr("donthideloader") == "true")
		$(this).attr("donthideloader", "false");
	else
		hide_loader();
});
$(window).ready(function() {
    initpage();
});
$(window).resize(function(){
	background_position();					  
});
</script>