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
	if(!check_inline())
		exit();
	$_SESSION["LANGUAGE"] = "EN";
	url_front::analyse_url();
	
	$res_home = DBConnect::query("SELECT site_homecfg.*, site_homecfg_lang.* FROM site_homecfg, site_homecfg_lang WHERE site_homecfg.id=site_homecfg_lang.lang_parent_id AND site_homecfg_lang.lang='" . $_SESSION["LANGUAGE"] . "'", __FILE__, __LINE__);
	$row_home = mysql_fetch_array($res_home);
	
	if($row_home["under_cons"] > 0 && $_SESSION["underconstruction_login"] != "true")
	{
		if(md5($_POST["underconstruction_login"]) == $row_home["under_cons_pwd"])
		{
			$_SESSION["underconstruction_login"] = "true";
			page_front::publish(url_front::get_last_page_id(), $_SESSION["LANGUAGE"]);
		}
		else
		{
			include("under_construction.php");
		}
	}
	else
		page_front::publish(url_front::get_last_page_id(), $_SESSION["LANGUAGE"]);
	//main div for refresh
?>