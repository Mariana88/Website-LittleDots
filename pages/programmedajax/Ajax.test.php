<?php
	if(!login::right("backpage_config_test", "view"))
	{
		echo "NORIGHTS";
		exit();
	}

	switch($_GET["action"])
	{
		case "post": 
			$errors = data_description::validate_post_db();
			if(count($errors) == 0)
			{
				//we save the data
				DBConnect::query(data_description::create_sql_from_post("abcd_test", "id"), __FILE__, __LINE__);
				//we get the new id if there is one
				if(trim($_POST["abcd_test.id"]) == "")
					data_description::output_save_xml($errors, "abcd_test.id", DBConnect::get_last_inserted("abcd_test", "id"));
				else
					data_description::output_save_xml($errors, "abcd_test.id");
			}
			else
			{
				data_description::output_save_xml($errors, "abcd_test.id");
			}
			break;
	}
?>