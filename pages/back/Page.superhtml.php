<?php
	if(!login::right("backpage_config_superhtml", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<div id="superdiv">
<div class="contentheader">
	<div class="divleft">Super Htmls => main structures of front site</div>
</div>
<div class="contentcontent" style="padding-left:20px; padding-right:20px;" name="form_siteconfig" id="form_siteconfig">
<?php
	$de = new dataeditor("superhtml_de", 500, 500, "site_superhtml");
	$de->publish(false);

	echo '<div style="clear:both;"></div>';
?>
</div>
</div>
<?php
	}
?>