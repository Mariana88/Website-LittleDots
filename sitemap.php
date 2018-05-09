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
	require_once("aidclasses/Class.email.php");	
	require_once('frontfunc/functions.php');
	
	session_start();
	
	
	header ("Content-Type:text/xml");
	
	echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" 
  xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
  
  echo '<url><loc>http://' . $_SERVER['HTTP_HOST'] . '</loc></url>';
  //foreach(mainconfig::$languages as $lang => $langname)
  //{
	 	//$_SESSION["LANGUAGE"] = $lang;
	 	//echo '<url><loc>http://' . $_SERVER['HTTP_HOST'] . '/' . $lang . '</loc><changefreq>weekly</changefreq></url>';
		echo_children(0);
  //}
  echo '</urlset>';

	function echo_children($pageid)
	{
		$res_pages = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.id=site_page_lang.lang_parent_id AND site_page_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND site_page.parent_id='" . $pageid . "' AND site_page_lang.published>'0'", __FILE__, __LINE__);
		while($page = mysql_fetch_array($res_pages))
		{
			echo '<url><loc>http://' . $_SERVER['HTTP_HOST'] . url_front::create_url($page["id"]) . '</loc><changefreq>weekly</changefreq></url>';
			echo_children($page["id"]);
		}
	}
?>