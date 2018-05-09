<?php
	if(!login::right("backpage_content_files", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>

<?php
	$browser = new browser("browsermain", 980, 800);
	$browser->publish(false);
?>

<?php
	}
?>