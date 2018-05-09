<?php
	if(isset($_GET["uid"]))
	{
		DBConnect::query("UPDATE site_user SET receives_newsletter='0' WHERE `id`='" . addslashes($_GET["uid"]) . "'", __FILE__, __LINE__);
		echo '<div>U zal onze nieuwsbrief niet meer ontvangen.</div>';
	}
?>