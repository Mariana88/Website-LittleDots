<?php
	// THIS FILE IS IMPORTANT FOR THE AJAX FUNCTIONALITY
	// WE have the comp_id or the page_id that can be set
	
	require_once("aidclasses/Class.db_debug.php");
	require_once("aidclasses/Class.data.php");
	require_once("aidclasses/Class.video.php");
	require_once("systemclasses/Class.mainconfig.php");
	require_once("systemclasses/Class.dbconnect.php");
	require_once("systemclasses/Class.url.php");
	require_once("systemclasses/Class.login.php");
	require_once("systemclasses/Class.page.php");
	require_once("systemclasses/Class.management.php");
	require_once("systemclasses/Class.popup.php");
	require_once("components/Class.form.php");
	require_once("components/Class.formfield.php");
	require_once("components/Class.piccropper.php");
	require_once("components/Class.datagridnew.php");
	require_once("components/Class.piccollection.php");
	require_once("components/Class.rightform.php");
	require_once("components/Class.fileoptions.php");
	require_once("components/Class.inline_edit.php");
	require_once("popups/Class.browser.php");
	require_once("popups/Class.minibrowser.php");
	require_once("popups/Class.dbeditor.php");
	require_once("popups/Class.dataeditor.php");
	require_once("popups/Class.tableeditor.php");
	require_once("popups/Class.emaillist.php");
	require_once("frontfunc/Class.page_front.php");
	require_once("frontfunc/Class.url_front.php");
	require_once("frontfunc/Class.files_front.php");
	require_once("frontfunc/functions.php");
	require_once("plugins/eigen/browser/Class.newBrowse.php");
	require_once("plugins/ezc/Base/src/base.php"); // dependent on installation method, see below
	function __autoload( $className )
	{
		ezcBase::autoload( $className );
	}
	ini_set('memory_limit', '100M');
	
	if(substr($_SERVER['DOCUMENT_ROOT'], (strlen($_SERVER['DOCUMENT_ROOT']) - 1), 1)!= '/')
		$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/';
	
	error_reporting(E_ALL ^ E_NOTICE);
	if(isset($_GET["sessid"]))
		session_id($_GET["sessid"]);
		
	//VOOR DE MULTIUPLOAD (die gebruikt PHPSESSID ipv sessid)
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	
	session_start();
	if(!login::check_login())
	{
		echo 'You Don\'t Have The Rights!s';
		exit();
	}
	
	//WE HAVE TO CHECK THE SECURITY
	//if there is a post we replace ## to a . in the variable names
	
	$tmp_post = array();
	foreach($_POST as $key => $value)
	{
		$tmp_post[str_replace("##", ".", $key)] = str_replace('_plus_', '+', str_replace('_ampersant_', '&', utf8_decode($value)));
	}
	$_POST = $tmp_post;
	
	
	if(isset($_GET["page"]))
	{
		include('pages/programmedajax/Ajax.' . $_GET["page"] . '.php');
	}
	
	if(isset($_GET["dg_id"]))
	{
		//we create the component and let him do the shit
		$comp = $_SESSION["datagrids"][$_GET["dg_id"]];
		if(isset($comp))
			$comp->handle_ajax();
	}
	
	if(isset($_GET["piccol_id"]))
	{
		//we create the component and let him do the shit
		$comp = $_SESSION["piccol"][$_GET["piccol_id"]];
		if(isset($comp))
			$comp->handle_ajax();
	}
	
	
	if(isset($_GET["popup_id"]))
	{
		$popup = popup::create_popup_from_id($_GET["popup_id"]);
		if(isset($popup))
		{
			$popup->handle_ajax();
		}
	}
	
	if(isset($_GET["dataeditor_editable"]))
	{
		$popup = popup::create_popup_from_id($_GET["dataeditor_editable"]);
		if(isset($popup))
		{
			$popup->handle_ajax_editable();
		}
	}
	
	if(isset($_GET["blicsmFormField"]))
	{
		
		formfield::handle_ajax_new($_GET["type"], $_GET["action"], $_GET["xml"]);	
	}
	
	if(isset($_GET["newBrowse"]))
	{
		$br = new newBrowse();
		$br->ajax();
	}
	
	if(isset($_GET["pageselectbox"]))
	{
		page::page_selectbox_item(0, $_GET["systemtemplate"], $_GET["veldid"], "", 1, NULL, $_GET["asparent"]);
	}
	
	if(isset($_GET["formfield"]))
	{
		formfield::handle_ajax($_GET["formfield"], $_GET["action"]);
	}
	
	if(isset($_GET["piccropper"]))
	{
		piccropper::ajax();
	}
	
	if(isset($_GET["form"]))
	{
		form::ajax();
	}
	
	if(isset($_GET["rightform"]))
	{
		rightform::ajax();
	}
	
	if(isset($_GET["fileoptions"]))
	{
		fileoptions::ajax();
	}
	
	if($_GET["inline_edit"] == "true")
	{
		inline_edit::ajax();
	}
	
	function page_tree_nodes($parent_id, $prefix)
	{
		$result = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page_lang.lang_parent_id = site_page.id AND site_page.parent_id='" . $parent_id . "' AND site_page_lang.lang='" . mainconfig::$standardlanguage . "'", __FILE__, __LINE__);
		while($row = mysql_fetch_array($result))
		{
			if(trim($_GET["lang"]) != "")
				echo '<option value="' . url_front::create_url($row["id"], $_GET["lang"]) . '">' . $prefix . htmlentities(stripslashes($row["name"])) . '</option>';
			else
				echo '<option value="' . url_front::create_url($row["id"], $_SESSION["CMS_EDIT_LANG"]) . '">' . $prefix . htmlentities(stripslashes($row["name"])) . '</option>';

			page_tree_nodes($row["id"], $prefix . htmlentities(stripslashes($row["menu_name"])) . ' > ');
		}
	}
	
	if($_REQUEST["output_html"] == true)
	{
		echo '<script>
				$(\'img[icontype="help"]\').tooltip({showURL: false});
				$(\'img[icontype="help"]\').css("margin-bottom", "-2px");
			</script>';
	}
?>