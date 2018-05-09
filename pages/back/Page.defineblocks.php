<?php
	if(!login::right("backpage_config_blocks", "view"))
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
	<div class="divleft">Blocks: define the site blocks</div>
</div>
<div class="contentcontent" style="padding-left:20px; padding-right:20px;" name="form_siteconfig" id="form_siteconfig">
<?php
	$de = new dataeditor("test_de", 500, 500, "site_blocks");
	$de->publish(false);

	echo '<div style="clear:both;"></div>';
?>
</div>
</div>
<?php
	}
?>