<?php
	class rightform
	{
		public static function publish($name, $data_id = NULL, $group_id = NULL)
		{
			$res = NULL;
			if($data_id)
				$res = DBConnect::query("SELECT * FROM site_rights WHERE `name`='" . addslashes($name) . "' AND `data_id`='" . $data_id . "'" . ((isset($group_id))?" AND usergroup='" . $group_id . "'":""), __FILE__, __LINE__);
			else
				$res = DBConnect::query("SELECT * FROM site_rights WHERE `name`='" . addslashes($name) . "'" . ((isset($group_id))?" AND usergroup='" . $group_id . "'":""), __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			$data = array();
			if($row)
				$data = unserialize($row["rules"]);
			$res_rules = DBConnect::query("SELECT * FROM sys_rightrules WHERE `name`='" . addslashes($name) . "'", __FILE__, __LINE__);
			$row_rules = mysql_fetch_array($res_rules);
			
			$rules = explode(";", $row_rules["rules"]);
			foreach($rules as $rule)
			{
				echo '<label>' . $rule .  '</label><input type="checkbox" ' . (($data[$rule])?'checked':'') . ' onclick="send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&rightform=1&action=saverule&r_name=' . $name . '&r_data_id=' . trim($data_id) . '&r_rule=' . $rule . '&r_group=' . $group_id . '&r_value=\' + ((this.checked)?\'true\':\'false\'), \'\', null)"/><br>';
			}
		}
		
		public static function ajax()
		{
			switch($_GET["action"])
			{
				case "saverule":
					$res = NULL;
					if(trim($_GET["r_data_id"]) != "")
						$res = DBConnect::query("SELECT * FROM site_rights WHERE `name`='" . addslashes($_GET["r_name"]) . "' AND `data_id`='" . addslashes($_GET["r_data_id"]) . "' AND usergroup='" . addslashes($_GET["r_group"]) . "'", __FILE__, __LINE__);
					else
						$res = DBConnect::query("SELECT * FROM site_rights WHERE `name`='" . addslashes($_GET["r_name"]) . "' AND usergroup='" . addslashes($_GET["r_group"]) . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$data = array();
					$new_data = array();
					if($row)
						$data = unserialize($row["rules"]);
					$res_rules = DBConnect::query("SELECT * FROM sys_rightrules WHERE `name`='" . addslashes($_GET["r_name"]) . "'", __FILE__, __LINE__);
					$row_rules = mysql_fetch_array($res_rules);
					$rules = explode(";", $row_rules["rules"]);
					foreach($rules as $rule)
						$new_data[$rule] = ((isset($data[$rule]))?$data[$rule]:false);
					
					$new_data[$_GET["r_rule"]] = (($_GET["r_value"] == "true")?true:false);
					if($row)
						DBConnect::query("UPDATE site_rights SET `rules`='" . serialize($new_data) . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
					else
						DBConnect::query("INSERT INTO site_rights (`name`, `data_id`, `rules`, `usergroup`) VALUES ('" . addslashes($_GET["r_name"]) . "', '" . addslashes($_GET["r_data_id"]) . "', '" . serialize($new_data) . "', '" . addslashes($_GET["r_group"]) . "')", __FILE__, __LINE__);
					break;
				case "publish":
					rightform::publish($_GET["r_name"], $_GET["r_data_id"], $_GET["r_group"]);
					break;
			}
		}
	}
?>