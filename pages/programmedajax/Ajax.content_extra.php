<?php
	if(!login::right("backpage_content_extra", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	
	switch($_GET["action"])
	{
		case "locaties":
			
			echo '<div class="contentheader">
						<div class="divleft">Edit locaties</div>';
			echo '</div>
					<div class="contentcontent">';
			$de = new dataeditor("news_logo", 500, 500, "data_location");
			$de->publish(false);
			echo '</div>';

			break;
		case "instrumenten":
			
			echo '<div class="contentheader">
						<div class="divleft">Edit instrumenten</div>';
			echo '</div>
					<div class="contentcontent">';
			$de = new dataeditor("news_logo", 500, 500, "data_instrument");
			$de->publish(false);
			echo '</div>';

			break;
	}
?>