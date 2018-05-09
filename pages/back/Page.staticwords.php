<?php
	if(!login::right("backpage_staticwords", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<div id="superdiv">
<div id="content">


<?php
	echo '<div class="contentheader">
				<div class="divleft">Static words</div>
			</div>
			<div class="contentcontent">';
	$de = new dataeditor("staticwords_group_de", 500, 500, "site_static_words_group");
	$de->publish(false);
	$de = new dataeditor("staticwords_de", 500, 500, "site_static_words");
	$de->publish(false);
	echo '<div style="clear:both;"></div>';
	echo '</div>';
	
?>
</div>
</div>
<?php
	}
?>
