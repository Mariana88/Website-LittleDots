<?php
	if(!login::right("backpage_newsletter_statistics", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<?php
	echo '<div id="superdiv"><div style="padding-left:20px; padding-right:20px;" name="form_siteconfig" id="form_siteconfig">statistieken</div></div>';
?>
<?php
	}
?>