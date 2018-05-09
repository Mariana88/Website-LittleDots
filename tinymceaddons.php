<?php
	require_once("aidclasses/Class.db_debug.php");
	require_once("aidclasses/Class.data.php");
	//require_once("plugins/fckeditor/fckeditor.php");
	require_once("systemclasses/Class.mainconfig.php");
	require_once("systemclasses/Class.dbconnect.php");
	require_once("systemclasses/Class.url.php");
	require_once("systemclasses/Class.login.php");
	require_once("systemclasses/Class.page.php");
	require_once("components/Class.form.php");
	require_once("components/Class.formfield.php");
	require_once("components/Class.datagridnew.php");
	require_once("components/Class.piccollection.php");
	require_once("components/Class.rightform.php");
	require_once("components/Class.fileoptions.php");
	require_once("systemclasses/Class.popup.php");
	require_once("popups/Class.browser.php");
	require_once("popups/Class.minibrowser.php");
	require_once("popups/Class.dbeditor.php");
	require_once("popups/Class.dataeditor.php");
	require_once("popups/Class.tableeditor.php");
	require_once("frontfunc/Class.page_front.php");

	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	if(substr($_SERVER['DOCUMENT_ROOT'], (strlen($_SERVER['DOCUMENT_ROOT']) - 1), 1)!= '/')
		$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/';
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $_SERVER['HTTP_HOST'] . " Admin";?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="/css/back/css.css">
<!-- <link href="plugins/spry/widgets/htmlpanel/SpryHTMLPanel.css" rel="stylesheet" type="text/css" /> -->
<link href="/plugins/spry/widgets/tooltip/SpryTooltip.css" rel="stylesheet" type="text/css" />
<link href="/plugins/spry/widgets/collapsiblepanel/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="/plugins/spry/widgets/menubar/SpryMenuBarHorizontal.css">
<link href="/plugins/spry/widgets/accordion/SpryAccordion.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/plugins/thickbox/thickbox.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="/plugins/spry/widgets/tabbedpanels/SpryTabbedPanels.css">
<link type="text/css" href="/plugins/jquery/css/custom-theme/jquery-ui-1.8.5.custom.css" rel="stylesheet" />	


<script type="text/javascript" src="/js/simpletreemenu.js"></script>
<script type="text/javascript" src="/js/3statetree.js"></script>
<script type="text/javascript" src="/js/simpleajax.js"></script>
<script type="text/javascript" src="/js/datagrid.js"></script>
<script type="text/javascript" src="/js/piccollection.js"></script>
<script type="text/javascript" src="/js/browser.js"></script>
<script type="text/javascript" src="/js/dbeditor.js"></script>
<script type="text/javascript" src="/js/dataeditor.js"></script>
<script type="text/javascript" src="/js/tableeditor.js"></script>
<script type="text/javascript" src="/js/infoblocks.js"></script>
<script type="text/javascript" src="/js/cms2.js"></script>
<script type="text/javascript" src="/js/draganddrop.js"></script>
<script type="text/javascript" src="/js/sitespecific.js"></script>
<script type="text/javascript" src="/js/form.js"></script> 
<script type="text/javascript" src="/js/effects.js"></script> 
<script src="/js/colpanel.js" type="text/javascript"></script>

<script type="text/javascript" src="/plugins/jquery/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/plugins/jquery/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" src="/plugins/jcrop/js/jquery.Jcrop.min.js"></script>
<script type="text/javascript" src="/plugins/tooltip/jquery.tooltip.min.js"></script>
<script type="text/javascript" src="/plugins/corner/jquery.corner.js"></script>
<script type="text/javascript" src="/plugins/autocomplete/jquery.autocomplete.min.js"></script>
<script type='text/javascript' src='/plugins/autocomplete/lib/jquery.ajaxQueue.js'></script>


<script type="text/javascript" src="/plugins/thickbox/thickbox.js"></script>

<script src="/plugins/spry/widgets/menubar/SpryMenuBar.js" type="text/javascript"></script>
<script src="/plugins/spry/widgets/accordion/SpryAccordion.js" type="text/javascript"></script>
<script src="/plugins/spry/widgets/htmlpanel/SpryHTMLPanel.js" type="text/javascript"></script> 
<script src="/plugins/spry/widgets/tooltip/SpryTooltip.js" type="text/javascript"></script> 
<script src="/plugins/spry/widgets/collapsiblepanel/SpryCollapsiblePanel.js" type="text/javascript"></script> 
<script src="/plugins/spry/widgets/tabbedpanels/SpryTabbedPanels.js" type="text/javascript"></script> 
<script src="/plugins/swfupload/swfupload.js" type="text/javascript"></script>
<script src="/plugins/swfupload/js/fileprogress.js"></script>
<script src="/plugins/swfupload/js/handlers.js"></script>
<script src="/plugins/swfupload/js/swfupload.queue.js"></script>

<script type="text/javascript" src="/plugins/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>

<script type="text/javascript" src="/js/tinymce_init.php"></script> 

</head>

<body>
<script type="text/javascript">
	Spry.Widget.HTMLPanel.evalScripts = true;
	window.session_id = '<?php echo session_id(); ?>';
	function dummy(){}
</script>

<?php
	if(login::check_login())
	{
		$res_type = DBConnect::query("SELECT * FROM site_mceaddon_type WHERE `id`='" . addslashes($_GET["mcetype"]) . "'", __FILE__, __LINE__);
		$row_type = mysql_fetch_array($res_type);
	
?>
<div id="superdiv">
<div class="contentheader">
						<div class="divleft">Create/edit <?php echo $row_type["title"];?></div>
						<div class="divright">
							<div class="savebutton" style="float:right;" onclick="window['<?php echo $row_type["table"];?>_form'].aftersave_success = 'mceaddon_savesucces'; window['<?php echo $row_type["table"];?>_form'].savebutton = $(this); window['<?php echo $row_type["table"];?>_form'].post();">Save &amp; Update</div><div class="savebutton" onclick="window.close();" style="float:right; margin-right: 4px;">Cancel</div>
						</div>
					</div>
<div class="contentcontent" style="padding-left:20px; padding-right:20px;" name="form_siteconfig" id="form_siteconfig">
<?php
	
		
		$mceid = $_GET["mceid"];
		//We halen de info op
		if($_GET["mceid"] <= 0 || trim($_GET["mceid"]) == "")
		{
			//create new
			DBConnect::query("INSERT INTO `site_mceaddon` (`id`, `type_id`) VALUES ('', '" . addslashes($_GET["mcetype"]) . "')", __FILE__, __LINE__);
			$mceid = mysql_insert_id();
		}
		//checken of de rij bestaat in de table
		$res_addon = DBConnect::query("SELECT * FROM `" . $row_type["table"] . "` WHERE `addon_id`='" . $mceid . "'", __FILE__, __LINE__);
		if(mysql_num_rows($res_addon) <= 0)
		{
			//inserten
			DBConnect::query("INSERT INTO `" . $row_type["table"] . "` (`id`, `addon_id`) VALUES ('', '" . $mceid . "')", __FILE__, __LINE__);
			$res_addon = DBConnect::query("SELECT * FROM `" . $row_type["table"] . "` WHERE `addon_id`='" . $mceid . "'", __FILE__, __LINE__);
		}
		$row_addon = mysql_fetch_array($res_addon);
		//Tonen form
		form::show_autoform_new($row_type["table"], $row_addon, mainconfig::$standardlanguage);
		
		
		
		
		echo '</div></div>';
		/*switch($_GET["type"])
		{
			case "images":	include("tinymceaddons/images.php");
				break;	
			case "video":	include("tinymceaddons/video.php");
				break;	
			case "audio":	include("tinymceaddons/audio.php");
				break;	
		}*/
	?>
<script language="javascript">
	function mceaddon_savesucces()
	{
		//ophalen van de html
		send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id();?>&mceaddoncreate=1&mceid=<?php echo $mceid; ?>", '', mceaddon_insert)
	}
	function mceaddon_insert(xmlHttp)
	{
		var alldivs = window.tinymce_editor.dom.select('div');
		for(var i = 0 ; i < alldivs.length ; i++)
		{
			if($(alldivs[i]).attr("id") == '<?php echo $mceid; ?>')
				window.tinymce_editor.selection.select(alldivs[i]);
		}
		window.tinymce_editor.selection.setContent(xmlHttp.responseText);
		window.close();
	}
</script>   
    <?php
    }
	else
	{
		echo '<div id="superdiv"><div class="contentcontent" style="padding-left:20px; padding-right:20px;" name="form_siteconfig" id="form_siteconfig">you do not have the rights to see this page</div></div>';
	}
?>
</body>
</html>