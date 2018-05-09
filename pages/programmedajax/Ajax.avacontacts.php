<?php
	if(!login::right("backpage_avacontacts", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	
	switch($_GET["action"])
	{
		case "getcontacts":
			$res = NULL;
			if($_GET["lang"] == "ALL")
				$res = DBConnect::query("SELECT * FROM data_nieuwsbrief_contact WHERE confirmed='1' AND signedout='0'", __FILE__, __LINE__);
			else
				$res = DBConnect::query("SELECT * FROM data_nieuwsbrief_contact WHERE confirmed='1' AND signedout='0' AND `lang`='" . addslashes($_GET["lang"]) . "'", __FILE__, __LINE__);
			
			$counter = 0;
			while($row = mysql_fetch_array($res))
			{
				if($counter != 0)
					echo ', ';
				echo $row["email"];
				$counter++;
			}
			break;
	}
?>