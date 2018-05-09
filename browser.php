<?php
	require_once("systemclasses/Class.mainconfig.php");
	require_once("systemclasses/Class.dbconnect.php");
	require_once("systemclasses/Class.url.php");
	require_once("systemclasses/Class.login.php");
	require_once("systemclasses/Class.page.php");
	require_once("components/Class.form.php");
	require_once("components/Class.formfield.php");
	require_once("components/Class.datagridnew.php");
	require_once("systemclasses/Class.popup.php");
	require_once("popups/Class.browser.php");
	require_once("popups/Class.dataeditor.php");
	require_once("popups/Class.tableeditor.php");

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
<link rel="stylesheet" type="text/css" href="css/back/css.css">
<!-- <link href="plugins/spry/widgets/htmlpanel/SpryHTMLPanel.css" rel="stylesheet" type="text/css" /> -->
<link href="plugins/spry/widgets/tooltip/SpryTooltip.css" rel="stylesheet" type="text/css" />
<link href="plugins/spry/widgets/collapsiblepanel/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="plugins/spry/widgets/menubar/SpryMenuBarHorizontal.css">
<link href="plugins/spry/widgets/accordion/SpryAccordion.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="plugins/thickbox/thickbox.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="plugins/spry/widgets/tabbedpanels/SpryTabbedPanels.css">
<link type="text/css" href="/plugins/jquery/css/custom-theme/jquery-ui-1.8.5.custom.css" rel="stylesheet" />	


<script type="text/javascript" src="/plugins/jquery/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/plugins/jquery/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" src="/js/simpletreemenu.js"></script>
<script type="text/javascript" src="/js/3statetree.js"></script>
<script type="text/javascript" src="/js/simpleajax.js"></script>
<script type="text/javascript" src="/js/datagrid.js"></script>
<script type="text/javascript" src="/js/piccollection.js"></script>
<script type="text/javascript" src="/js/browser.js"></script>
<script type="text/javascript" src="/js/dataeditor.js"></script>
<script type="text/javascript" src="/js/tableeditor.js"></script>
<script type="text/javascript" src="/js/infoblocks.js"></script>
<script type="text/javascript" src="/js/cms2.js"></script>
<script src="/plugins/spry/widgets/menubar/SpryMenuBar.js" type="text/javascript"></script>
<script src="/js/colpanel.js" type="text/javascript"></script>
<script type="text/javascript" src="/plugins/thickbox/thickbox.js"></script>
<script src="/plugins/spry/widgets/accordion/SpryAccordion.js" type="text/javascript"></script>
<script src="/plugins/spry/widgets/htmlpanel/SpryHTMLPanel.js" type="text/javascript"></script> 
<script src="/plugins/spry/widgets/tooltip/SpryTooltip.js" type="text/javascript"></script> 
<script src="/plugins/spry/widgets/collapsiblepanel/SpryCollapsiblePanel.js" type="text/javascript"></script> 
<script src="/plugins/spry/widgets/tabbedpanels/SpryTabbedPanels.js" type="text/javascript"></script> 
<script src="/plugins/swfupload/swfupload.js" type="text/javascript"></script>
<script src="/plugins/swfupload/js/fileprogress.js"></script>
<script src="/plugins/swfupload/js/handlers.js"></script>
<script src="/plugins/swfupload/js/swfupload.queue.js"></script>


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
		$browser = new browser("browsermain", 980, 800);
		if($_GET["addbutton"] == "yes")
			$browser->set_addbutton();
		$browser->publish(true);
	}
	else
	{
		echo "you do not have the rights to see this page";
	}
?>
</body>
</html>