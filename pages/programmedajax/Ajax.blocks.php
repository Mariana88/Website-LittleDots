<?php
	if(!login::right("backpage_content_blocks", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	
	switch($_GET["action"])
	{
		case "load":
			if(!login::right("block", "edit", $_GET["block"]))
			{
				echo '<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to edit this block<br><br><br><br></div>';
				break;
			}
			$res = DBConnect::query("SELECT * FROM `site_blocks` WHERE `id`='" . addslashes($_GET["block"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Edit Default data for block: ' . $row["name"] . '</div>';
			if(trim($row["table"]) != "")
			{
				echo '	<div class="divright">
							<div class="savebutton" onclick="window[\'' . $row["table"] . '_form\'].savebutton = $(this); window[\'' . $row["table"] . '_form\'].post();">Save</div>
						</div>';
			}
			echo '</div>
					<div class="contentcontent">';
			if(trim($row["table"]) == "")
			{
				echo "No data can be edited for this block";
			}
			else
			{
				$res_data = DBConnect::query("SELECT * FROM `" . $row["table"] . "` WHERE `page_id`='0'", __FILE__, __LINE__);
				$row_data = mysql_fetch_array($res_data);
				if(!$row_data)
				{
					DBConnect::query("INSERT INTO `" . $row["table"] . "`(`page_id`) VALUES('0')", __FILE__, __LINE__);
					$row_data = array();
					$row_data["page_id"] = "0";
				}
				form::show_autoform_new($row["table"], $row_data, mainconfig::$standardlanguage);
			}
			echo '</div>';
			break;
	}
?>