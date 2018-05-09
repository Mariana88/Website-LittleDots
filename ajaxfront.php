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
	
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	
	if(isset($_GET["page"]))
	{
		//opzoeken van de pagina
		$_SERVER["REQUEST_URI"] = 'http://www.deberengieren.be' . urldecode($_GET["page"]);
		url_front::analyse_url();
		$_GET["page"] = url_front::get_last_page_id();
		
		//ophalen van de pagina	
		$res = DBConnect::query("SELECT site_page.* FROM site_page WHERE site_page.id='" . $_GET["page"] . "'", __FILE__, __LINE__);
		$row_page = mysql_fetch_array($res);
		if($row_page["copyof"]<=0)
		{
			$res = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.id='" . $_GET["page"] . "' AND site_page_lang.lang='" . $_SESSION["LANGUAGE"] . "'", __FILE__, __LINE__);
			$row_page = mysql_fetch_array($res);
		}
		$res = DBConnect::query("SELECT * FROM site_pagetemplates WHERE id='" . $row_page["template_id"] . "'", __FILE__, __LINE__);
		$row_templ = mysql_fetch_array($res);
		include 'snippets/page/snippet.' . $row_templ["script"] . '.php';
		$tmp = ob_get_contents();
		echo '</div>';
	}
	
?>