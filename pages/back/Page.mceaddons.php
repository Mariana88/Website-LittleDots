<?php
	if(!login::right("backpage_management_labels", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>

<div id="superdiv">
<?php
	echo '<div class="contentheader">
				<div class="divleft">Tiny mce addons</div>
			</div>
			<div class="contentcontent" id="mcetypepost">';
	$de = new dataeditor("test_de", 500, 500, "site_mceaddon_type");
	$de->set_current_lang("NL");
	$de->publish(false);

	echo '<div style="clear:both;"></div>';
	echo '</div>';
?>
</div>
<?php
	}
?>
