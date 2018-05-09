<?php
	require_once("aidclasses/Class.db_debug.php");
	require_once("aidclasses/Class.data.php");
	require_once("aidclasses/Class.video.php");
	require_once("systemclasses/Class.mainconfig.php");
	require_once("systemclasses/Class.dbconnect.php");
	require_once("systemclasses/Class.url.php");
	require_once("systemclasses/Class.login.php");
	require_once("systemclasses/Class.page.php");
	require_once("systemclasses/Class.management.php");
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
	require_once("popups/Class.emaillist.php");
	require_once("frontfunc/Class.page_front.php");
	require_once("frontfunc/functions.php");
	require_once("plugins/eigen/facebook/blicsm_facebook.php");
	require_once("plugins/eigen/browser/Class.newBrowse.php");
	require_once("plugins/ezc/Base/src/base.php"); // dependent on installation method, see below
	function __autoload( $className )
	{
		ezcBase::autoload( $className );
	}
	
	ini_set('memory_limit', '100M');
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	session_start();
	url::analyse_url();
	if(substr($_SERVER['DOCUMENT_ROOT'], (strlen($_SERVER['DOCUMENT_ROOT']) - 1), 1)!= '/')
		$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $_SERVER['HTTP_HOST'] . " Admin";?></title>
<link rel="icon" href="http://www.littledots.info/favicon.png">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="/css/back/css.css">
<!-- <link href="plugins/spry/widgets/htmlpanel/SpryHTMLPanel.css" rel="stylesheet" type="text/css" /> -->
<link href="/plugins/spry/widgets/collapsiblepanel/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="/plugins/spry/widgets/menubar/SpryMenuBarHorizontal.css">
<link href="/plugins/spry/widgets/accordion/SpryAccordion.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/plugins/thickbox/thickbox.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="/plugins/spry/widgets/tabbedpanels/SpryTabbedPanels.css">
<link type="text/css" href="/plugins/jquery/jquery-ui-1.9.2.custom/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" />
<!--<link type="text/css" href="/plugins/jquery/css/custom-theme/jquery-ui-1.8.5.custom.css" rel="stylesheet" />-->	
<link type="text/css" href="/plugins/jcrop/css/jquery.Jcrop.css" rel="stylesheet" />		
<link type="text/css" href="/plugins/autocomplete/jquery.autocomplete.css" rel="stylesheet" />	
<link type="text/css" href="/plugins/contextmenu/jquery.contextMenu.css" rel="stylesheet" />
<link type="text/css" href="/plugins/eigen/popup/blicsmPopup.css" rel="stylesheet" />	
<link type="text/css" href="/plugins/eigen/accordion/blicsmAccordion.css" rel="stylesheet" />

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
<script type="text/javascript" src="/js/emaillist.js"></script> 
<script src="/js/colpanel.js" type="text/javascript"></script>

<!--<script type="text/javascript" src="/plugins/jquery/js/jquery-1.4.2.min.js"></script>-->
<script type="text/javascript" src="/plugins/jquery/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="/plugins/jquery/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/plugins/jcrop/js/jquery.Jcrop.js"></script>
<script type="text/javascript" src="/plugins/autocomplete/jquery.autocomplete.min.js"></script>
<script type='text/javascript' src='/plugins/autocomplete/lib/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='/plugins/contextmenu/jquery.contextMenu.js'></script>

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
<script type="text/javascript" src="/plugins/tinymce/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="/js/tinymce_init.php"></script> 
<script type="text/javascript" src="/plugins/eigen/browser/jQuery.newBrowse.js"></script> 
<script type="text/javascript" src="/plugins/eigen/popup/jQuery.blicsmPopup.js"></script>
<script type="text/javascript" src="/plugins/eigen/accordion/jQuery.blicsmAccordion.js"></script> 
<script type="text/javascript" src="/plugins/eigen/colpanel/jQuery.blicsmColpanel.js"></script>
<script type="text/javascript" src="/plugins/eigen/formfield/jQuery.formField.js"></script>
<script type="text/javascript" src="/plugins/eigen/editable/jQuery.editable.js"></script>

<!-- FILE UPLOAD -->
<link rel="stylesheet" href="/plugins/fileUpload/css/jquery.fileupload.css">
<script src="/plugins/fileUpload/js/vendor/jquery.ui.widget.js"></script>
<script src="/plugins/fileUpload/js/jquery.iframe-transport.js"></script>
<script src="/plugins/fileUpload/js/jquery.fileupload.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</head>
<body>
<script type="text/javascript">
	Spry.Widget.HTMLPanel.evalScripts = true;
	window.session_id = '<?php echo session_id(); ?>';
	function dummy(){}
</script>
<!-- divs voor standaard popups -->
<div id="message_error" style="display:none">
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr><td><img src="/css/back/icon/message/error.gif"></td><td id="message_error_content" style="vetical-align: middle;"></td></tr>
	<tr><td colspan="2" style="vetical-align: middle; text-align:center;"><input type="submit" id="message_error_ok" value="&nbsp;&nbsp;Ok&nbsp;&nbsp;" onclick="tb_remove()" /></td></tr>
	</table>
</div>
<div id="message_question" style="display:none">
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr><td><img src="/css/back/icon/message/question.gif"></td><td id="message_question_content" style="vetical-align: middle;"></td></tr>
	<tr><td colspan="2" style="vetical-align: middle; text-align:center;"><input type="submit" id="message_question_yes" value="Yes"/><input style="margin-left:4px; " type="submit" id="message_question_no" value="No"/></td></tr>
	</table>
</div>

<div id="message_saving_content" style="display:none; text-align:center">
	<div style="color: #000000; font-weight: bold; font-size:12px; line-height:70px; text-align:center">... SAVING ...</div>
</div>
	

<?php
	if(login::check_login())
	{
		echo '<div id="header">
			<div style="float:right; margin-right: 20px;"><img src="/css/back/icon/new/user_C.png" style="float: left; margin: 7px;"/><div style="float:left">' . $_SESSION["login_username"] . ' | </div><a href="/cms/logout"><img src="/css/back/icon/new/logout_C.png" style="float: left; margin: 7px;"/><div style="float:left">Logout</a> | </div><a href="http://' . $_SERVER['HTTP_HOST'] . '" target="_blank"><img src="/css/back/icon/new/world_C.png" style="float: left; margin: 7px;"/><div style="float:left;">' . $_SERVER['HTTP_HOST'] . '</a></div></div>' ;
?>
    <div id="nav">
		<ul id="mainmenu" class="MenuBarHorizontal">
<?php		
		if(login::right("backpage_content_pages", "view") || login::right("backpage_content_blocks", "view") || login::right("backpage_content_files", "view") || login::right("backpage_content_extra", "view"))
		{
			echo '<li><a class="MenuBarItemSubmenu" ' . ((url::$page=="content" || url::$page=="blocks" || url::$page=="browser" || url::$page=="content_extra")?' style=""':'') . 'href="/cms/content">Site Content</a>
		  		<ul>';

				if(login::right("backpage_content_pages", "view"))
					echo '<li><a href="/cms/content">Pages</a></li>';
				if(login::right("backpage_content_blocks", "view"))
					echo '<li><a href="/cms/blocks">Blocks</a></li>';
				if(login::right("backpage_content_files", "view"))
					echo '<li><a href="/cms/browser">Files</a></li>';
				if(login::right("backpage_content_extra", "view"))
					echo '<li><a href="/cms/content_extra">Extra</a></li>';
			 echo ' </ul>
			</li>';
		}
		if(login::right("backpage_management_dashboard", "view"))
		{
			echo '<li><a ' . ((url::$page=="management" || url::$page=="management_concerten" || url::$page=="management_labels" || url::$page=="management_todo")?' style=""': '') . ' href="/cms/management">Management</a></li>';
		}
		/*
		if(login::right("backpage_newsletter_contacts", "view") || login::right("backpage_newsletter_new", "view") || login::right("backpage_newsletter_templates", "view") || login::right("backpage_newsletter_statistics", "view"))
		{
			echo '<li><a class="MenuBarItemSubmenu" ' .((url::$page=="newsletter_new" || url::$page=="newsletter_contacts" || url::$page=="newsletter_templates" || url::$page=="newsletter_statistics")?' style=""': '') . ' href="/cms/newsletter_new">Newsletter</a>
			  <ul>';
			if(login::right("backpage_newsletter_new", "view"))
				echo '<li><a href="/cms/newsletter_new">Send New</a></li>';
			if(login::right("backpage_newsletter_contacts", "view"))
				echo '<li><a href="/cms/newsletter_contacts">Contacts</a></li>';
			if(login::right("backpage_newsletter_templates", "view"))
				echo '<li><a href="/cms/newsletter_templates">Templates</a></li>';
			if(login::right("backpage_newsletter_statistics", "view"))
				echo '<li><a href="/cms/newsletter_statistics">Statistics</a></li>';
			 echo '</ul>
		  </li>';
		}*/
		if(login::right("backpage_config_datadesc", "view") || login::right("backpage_config_superhtml", "view") || login::right("backpage_config_templates", "view") || login::right("backpage_config_blocks", "view") || login::right("backpage_config_test", "view") || login::right("backpage_staticwords", "view"))
		{
			echo '<li><a class="MenuBarItemSubmenu" ' .((url::$page=="datadesc" || url::$page=="superhtml" || url::$page=="templates" || url::$page=="defineblocks" || url::$page=="test" || url::$page=="staticwords")?' style=""': '') . ' href="/cms/templates">Site Configuration</a>
		  	<ul>';
				
			if(login::right("backpage_config_datadesc", "view"))
				echo '<li><a href="/cms/datadesc">Data descriptions</a></li>';
			if(login::right("backpage_config_superhtml", "view"))
				echo '<li><a href="/cms/superhtml">Super htmls</a></li>';
			if(login::right("backpage_config_templates", "view"))
				echo '<li><a href="/cms/templates">Page templates</a></li>';
			if(login::right("backpage_config_blocks", "view"))
				echo '<li><a href="/cms/defineblocks">Blocks</a></li>';
			if(login::right("backpage_staticwords", "view"))
				echo '<li><a href="/cms/staticwords">Static words</a></li>';
			if(login::right("backpage_config_mceaddons", "view"))
				echo '<li><a href="/cms/mceaddons">Tiny mce addons</a></li>';
			if(login::right("backpage_config_test", "view"))
				echo '<li><a href="/cms/test">test</a></li>';	
			echo '</ul>
		  		</li>';
		}
		if(login::right("backpage_security_frontusers", "view") || login::right("backpage_security_adminusers", "view") || login::right("backpage_security_rightrules", "view"))
		{
			echo '<li><a class="MenuBarItemSubmenu" ' .((url::$page=="securityfront" || url::$page=="securityback" || url::$page=="rightrules")?' style=""': '') . ' href="/cms/securityfront">Security</a>
		  	<ul>';
			if(login::right("backpage_security_frontusers", "view"))
				echo '<li><a href="/cms/securityfront">Front users</a></li>';
			if(login::right("backpage_security_adminusers", "view"))
				echo '<li><a href="/cms/securityback">Admin users</a></li>';
			if(login::right("backpage_security_rightrules", "view"))
				echo '<li><a href="/cms/rightrules">Right Rules</a></li>';
			echo '</ul>
		 	 </li>';
		}
		//if(login::right("backpage_statistics", "view"))
		//  	echo '<li><a href="/cms/statistics">Statistics</a></li>';
?>
		</ul>

		<script type="text/javascript">
			var mainmenu = new Spry.Widget.MenuBar("mainmenu", {});
		</script>
	</div>
    </div>
<?php
	}
?>
<div style="clear:both; "></div>
<?php
	if(!login::check_login())
	{
		Login::display_form();
	}
	else
	{
		//echo 'pages/back/Page.' . url::$page . '.php';
		include 'pages/back/Page.' . url::$page . '.php';
	}
?>
<script language="javascript">
	$(window).resize(function()
	{
		$(".sidebar_inner").css("height", ($(window).height() - 60) + "px");
	});
	$(document).ready(function()
	{
		$(".sidebar_inner").css("height", ($(window).height() - 60) + "px");
	});
</script>
</body>
</html>