<?php
	//this class contains information about a certain data field.
	//the data field can be linked with Filesystem, Database, xml and csv files.
	//the data_description also contains validation functions.
	//datagrids, fields, forms, ... can use this datadescription to display or save a certain data-item
	class data_description
	{
		//give the fieldname = dbname.fieldname and the value to validate
		static function validate_db($name, $value)
		{
			$chomps = explode(".", $name);
			//we zoeken de db_meta
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
				return data_description::validate($value, $row["datadesc"], $options, $name, $row["fieldlabel"], $row["obligated"]);
			}
			else
				return data_description::validate($value, 1, array("length" => "255"), $name, $chomps[count($chomps)-1], 0);
		}
		
		static function validate($value, $desc_id, $options, $fieldname, $label, $obligated)
		{
			$message = "";
			if($obligated && trim($value) == "")
			{
				$message = "This must be filled";
				return $message;
			}
			//get the description
			$res_desc = DBConnect::query("SELECT * FROM `sys_datadescriptions` WHERE `id`='" . $desc_id . "'", __FILE__, __LINE__);
			$row_desc = mysql_fetch_array($res_desc);
			switch($row_desc["name"])
			{
				case "VARCHAR":
					if(trim($options["length"]) != "" && $options["length"] < strlen(trim($value)))
						$message = 'Cannot be longer then ' . $options["length"] . ' characters';
					break;
				case "TEXT":
					break;
				case "HTML BASIC":
					break;
				case "HTML FULL":
					break;
				case "HIDDEN ID":
					break;
				case "HIDDEN VARCHAR":
					break;
				case "HIDDEN NUMERIC":
					break;
				case "LINK":
					if(trim($value) != "")
					{
						if(substr($value, 0, 1) != '/' && substr($value, 0, 7) != 'http://')
						{
							$message = 'An extern link schould start with http://';
						}
					}
					break;
				case "DATE":
					$timestamp = $value;
					if(!is_numeric($value))
						$timestamp = DateAndTime::check_date("d/m/Y", $value);
					
					if(strlen(trim($value)) > 0 && $timestamp <= 0)
						$message = 'Is not a correct date (format: d/m/Y)';
					elseif(trim($options["max"]) != "" && strlen(trim($value)) > 0 && $timestamp > $options["max"])
						$message = 'Must be earlier than ' . date("d/m/Y", $options["max"]);
					elseif(trim($options["min"]) != "" && strlen(trim($value)) > 0 && $timestamp < $options["min"])
						$message = 'Must be later than ' . date("d/m/Y", $options["min"]);
					break;
				case "TIME":
					$timestamp = $value;
					if(!is_numeric($value))
						$timestamp = DateAndTime::check_time($value, $options["hours"], $options["minutes"], $options["seconds"]);
					if(strlen(trim($value)) > 0 && $timestamp < 0)
						$message = 'Is not a correct time value (format: ' . ($options["hours"]?'h':'') . (($options["hours"] && ($options["minutes"] || $options["seconds"]))?':':'') . ($options["minutes"]?'m':'') . ((($options["hours"] || $options["minutes"]) && $options["seconds"])?':':'') . ($options["seconds"]?'s':'') . ')';
					elseif(trim($options["max"]) != "" && strlen(trim($value)) > 0 && $timestamp > $options["max"])
						$message = 'Must be earlier than ' . date("H:i:s", $options["max"]);
					elseif(trim($options["min"]) != "" && strlen(trim($value)) > 0 && $timestamp < $options["min"])
						$message = 'Must be later than ' . date("H:i:s", $options["min"]);
					break;
				case "YESNO":
					break;
				case "NUMERIC":
					if(strlen(trim($value)) > 0 && !is_numeric($value))
						$message = 'Must contain a numeric value';
					elseif(trim($options["min"]) != "" && $options["min"] > $value && strlen(trim($value)) > 0)
						$message = 'Must be greater than ' . $options["min"];
					elseif(trim($options["max"]) != "" && $options["max"] < $value && strlen(trim($value)) > 0)
						$message = 'Must be greater than ' . $options["max"];
					break;
				case "EMAIL":
					if(!email::checkemail($value) && strlen(trim($value)) > 0)
						$message = 'Is not a valid email adress';
					break;
				case "FILE":
				case "PICTURE":
				case "VIDEO":
				case "AUDIO":
					if(trim($value) != "")
					{
						$defaultextentions = array();
						if($row_desc["name"] == "PICTURE") $defaultextentions = array("jpg", "jpeg", "gif", "png");
						if($row_desc["name"] == "VIDEO") $defaultextentions = array("mov", "mp4", "avi");
						if($row_desc["name"] == "AUDIO") $defaultextentions = array("mp3");
						// eerst de default extentsions checken dan de eventueel gespecificeerde
						//de file opzoeken in de database
						$res_file = DBConnect::query("SELECT * FROM `site_files` WHERE `id`='" . addslashes($value) . "'");
						if($row_file = mysql_fetch_array($res_file))
						{
							$path_parts = pathinfo($row_file["path"]);
							if(count($defaultextentions) > 0 && !in_array(strtolower($path_parts["extension"]), $defaultextentions))
								$message = "The file type is not allowed (alowed types: " . implode(';', $defaultextentions) . ")";
							if(trim($options["extentions"]) != "")
							{
								$extentions = explode(";", $options["extentions"]);
								if(!in_array(strtolower($path_parts["extension"]), $extentions))
									$message = "The file type is not allowed (alowed types: " . $options["extentions"] . ")";
							}
						}
					}
					break;
				case "PICTURE FORMAT":
					break;
				case "ENUM LANGUAGE":
					break;
				case "ENUM FROM TABLE":
					break;
				case "ENUM PAGES FROM TEMPLATE":
					break;
				case "ENUM PAGES FROM PARENT":
					break;
				case "ENUM STATIC":
					break;
				case "PASSWORD":
					//moet minstens 6 tekens zijn
					if($options["encrypted"])
					{
						if(strlen($value) < 6 && strlen($value) > 0)
							$message = "A password must be at least 6 characters long";
					}
					else
					{
						if(strlen($value) < 6)
							$message = "A password must be at least 6 characters long";
					}
					break;
				case "AUTOCOMPLETE":
					break;
				case "WORDLIST":
					break;
				case "WORDDATALIST":
					break;
			}
			return $message;
		}
		
		static function validate_post_db()
		{
			$messages = array();
			foreach($_POST as $name => $value)
			{
				$tmpmsg = data_description::validate_db($name, $value);
				if($tmpmsg != "")
					$messages[$name] = $tmpmsg;
			}
			return $messages;
		}
		
		
		static function convert_from_post($value, $postname)
		{
			$chomps = explode(".", $postname);
			//we zoeken de db_meta
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			$res_desc = DBConnect::query("SELECT * FROM `sys_datadescriptions` WHERE `id`='" . $row["datadesc"] . "'", __FILE__, __LINE__);
			$row_desc = mysql_fetch_array($res_desc);
			$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
			switch($row_desc["name"])
			{
				case "DATE":
					return DateAndTime::check_date("d/m/Y", $value);
					break;
				case "TIME":
					return DateAndTime::check_time($value, $options["hours"], $options["minutes"], $options["seconds"]);
					break;
				default: return $value;
					break;
			}
		}
		
		static function convert_field_to_db($value, $postname)
		{
			$chomps = explode(".", $postname);
			//we zoeken de db_meta
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			$res_desc = DBConnect::query("SELECT * FROM `sys_datadescriptions` WHERE `id`='" . $row["datadesc"] . "'", __FILE__, __LINE__);
			$row_desc = mysql_fetch_array($res_desc);
			$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
			switch($row_desc["name"])
			{
				case "DATE":
					return DateAndTime::check_date("d/m/Y", $value);
					break;
				case "TIME":
					return DateAndTime::check_time($value, $options["hours"], $options["minutes"], $options["seconds"]);
					break;
				case "VARCHAR":
				case "TEXT":
				case "HTML BASIC":
				case "HTML FULL":
				case "AUTOCOMPLETE":
				case "WORDLIST":
					return str_replace('â‚¬', '€', addslashes(utf8_decode($value)));
				case "PASSWORD":
					if($options["encrypted"] > 0)
						return md5($value);
					else
						return stripslashes($value);
					break;
				default: return addslashes($value);
					break;
			}
		}
		
		static function convert_db_to_field($value, $postname)
		{
			$chomps = explode(".", $postname);
			//we zoeken de db_meta
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			if(!$row)
				return stripslashes($value);
			$res_desc = DBConnect::query("SELECT * FROM `sys_datadescriptions` WHERE `id`='" . $row["datadesc"] . "'", __FILE__, __LINE__);
			$row_desc = mysql_fetch_array($res_desc);
			$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
			switch($row_desc["name"])
			{
				case "DATE":
					return ((!is_numeric($value))? $value: (($value <= 0)?'':date("d/m/Y" , $value)));
					break;
				case "TIME":
					return DateAndTime::format_time($value, $options["hours"], $options["minutes"], $options["seconds"]);
					break;
				case "HIDDEN VARCHAR":
				case "VARCHAR":
				case "TEXT":
				case "HTML BASIC":
				case "HTML FULL":
				case "AUTOCOMPLETE":
				case "WORDLIST":
					return utf8_encode(stripslashes(str_replace(chr(128), '&#8364;', $value)));
					break;
				case "PASSWORD":
					/*if($options["encrypted"] > 0)
						return "";
					else*/
						return stripslashes($value);
					break;
				case "VARCHAR":
				case "EMAIL":
					return stripslashes($value);
					break;
				default: return stripslashes($value);
					break;
			}
		}
		
		static function create_sql_from_post($table, $id_field)
		{
			$lang_dep = false;
			//als de tabel lang dependent is dan moeten we 2 statements maken
			$res_table = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $table . "'", __FILE__, __LINE__);
			if($row_table = mysql_fetch_array($res_table))
				$lang_dep = (($row_table["lang_dep"]>0)?true:false);
			
			if(trim($_POST[$table . "." . $id_field]) == "")
			{
				//create an insert sql
				$new_id = DBConnect::get_last_inserted($table, $id_field) + 1;
				$names = "";
				$values = "";
				$names_lang = "";
				$values_lang = "";
				foreach($_POST as $key => $value)
				{
					$value = data_description::convert_from_post($value, $key);
					$tmp_expl = explode(".", $key);
					
					if(count($tmp_expl) > 1 && $tmp_expl[0] == $table && $id_field != $tmp_expl[1] && $value!="null")
					{
						$lang_dep_field = false;
						if($lang_dep)
						{
							$res_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tmp_expl[0] . "' AND `fieldname`='" . $tmp_expl[1] . "'", __FILE__, __LINE__);
							if($row_meta = mysql_fetch_array($res_meta))
								$lang_dep_field = (($row_meta["lang_dep"]>0)?true:false);
						}
						if($lang_dep_field)
						{
							if($names_lang == "")
								$names_lang = "(`" . $tmp_expl[1] . "`";
							else
								$names_lang .= ", `" . $tmp_expl[1] . "`";
							if($tmp_expl[1] == 'lang_parent_id')
							{
								//anticiperen op het nieuwe id.
								if($values_lang == "")
									$values_lang = "('" . $new_id . "'";
								else
									$values_lang .= ", '" . $new_id . "'";
							}
							else
							{
								if($values_lang == "")
									$values_lang = "('" . trim(addslashes($value_lang)) . "'";
								else
									$values_lang .= ", '" . trim(addslashes($value_lang)) . "'";
							}
						}
						else
						{
							if($names == "")
								$names = "(`" . $tmp_expl[1] . "`";
							else
								$names .= ", `" . $tmp_expl[1] . "`";
							if($values == "")
								$values = "('" . trim(addslashes($value)) . "'";
							else
								$values .= ", '" . trim(addslashes($value)) . "'";
						}
					}
				}
				
				if($names == "")
					return "";
				
				$names .= ")";
				$values .= ")";
				$names_lang .= ")";
				$values_lang .= ")";
				
				$sql = "INSERT INTO `" . $table . "` " . $names . " VALUES" . $values;
				if($lang_dep)
				{
					//we adden de language die we nu posten + alle ontbrekende languages
					$sql .= "; INSERT INTO `" . $table . "_lang` "  . $names_lang . " VALUES" . $values_lang;
					foreach(mainconfig::$languages as $key => $language)
					{
						if($key != $_POST[$table . ".lang"])
						{
							$sql .= "; INSERT INTO `" . $table . "_lang` (`id`, `lang_parent_id`, `lang`) VALUES('', '" . $new_id . "', '" . $key . "')";
						}
					}
				}
					
				return $sql;
			}
			else
			{
				//create an update sql
				$set = "";
				$set_lang = "";
				foreach($_POST as $key => $value)
				{
					$value = data_description::convert_from_post($value, $key);

					$tmp_expl = explode(".", $key);
					if(count($tmp_expl) > 1 && $tmp_expl[0] == $table && $id_field != $tmp_expl[1] && $value!="null")
					{
						$lang_dep_field = false;
						if($lang_dep)
						{
							$res_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tmp_expl[0] . "' AND `fieldname`='" . $tmp_expl[1] . "'", __FILE__, __LINE__);
							if($row_meta = mysql_fetch_array($res_meta))
								$lang_dep_field = (($row_meta["lang_dep"]>0)?true:false);
						}
						if($lang_dep_field)
						{
							if($set_lang == "")
								$set_lang = " `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
							else
								$set_lang .= ", `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
						}
						else
						{
							if($set == "")
								$set = " `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
							else
								$set .= ", `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
						}
					}
				}
				if($set == "")
					return "";
				$sql = "UPDATE `" . $table . "` SET " . $set . " WHERE `" . $id_field . "`='" . trim(addslashes($_POST[$table . "." . $id_field])) . "'";
				if($lang_dep)
					$sql .= "; UPDATE `" . $table . "_lang` SET "  . $set_lang . " WHERE `lang_parent_id`='" . trim(addslashes($_POST[$table . "." . $id_field])) . "' AND `lang`='" . trim(addslashes($_POST[$table . ".lang"])) . "'";
					
				return $sql;
			}
		}
		
		static function save_from_post($table, $id_field)
		{
			//echo "test___________";
			$lang_dep = false;
			//als de tabel lang dependent is dan moeten we 2 statements maken
			$res_table = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $table . "'", __FILE__, __LINE__);
			if($row_table = mysql_fetch_array($res_table))
				$lang_dep = (($row_table["lang_dep"]>0)?true:false);
			
			if(trim($_POST[$table . "." . $id_field]) == "")
			{
				//create an insert sql
				$names = "";
				$values = "";
				$names_lang = "";
				$values_lang = "";
				foreach($_POST as $key => $value)
				{
					$value = data_description::convert_from_post($value, $key);
					$tmp_expl = explode(".", $key);
					
					//echo $table;
					if(count($tmp_expl) > 1 && $tmp_expl[0] == $table && $id_field != $tmp_expl[1] && $value!="null")
					{
						
						$lang_dep_field = false;
						if($lang_dep)
						{
							$res_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tmp_expl[0] . "' AND `fieldname`='" . $tmp_expl[1] . "'", __FILE__, __LINE__);
							if($row_meta = mysql_fetch_array($res_meta))
								$lang_dep_field = (($row_meta["lang_dep"]>0)?true:false);
						}
						if($lang_dep_field)
						{
							if($names_lang == "")
								$names_lang = "(`" . $tmp_expl[1] . "`";
							else
								$names_lang .= ", `" . $tmp_expl[1] . "`";
							if($tmp_expl[1] == 'lang_parent_id')
							{
								//anticiperen op het nieuwe id.
								if($values_lang == "")
									$values_lang = "('[newid]'";
								else
									$values_lang .= ", '[newid]'";
							}
							else
							{
								if($values_lang == "")
									$values_lang = "('" . trim(addslashes($value)) . "'";
								else
									$values_lang .= ", '" . trim(addslashes($value)) . "'";
							}
						}
						else
						{
							if($names == "")
								$names = "(`" . $tmp_expl[1] . "`";
							else
								$names .= ", `" . $tmp_expl[1] . "`";
							if($values == "")
								$values = "('" . trim(addslashes($value)) . "'";
							else
								$values .= ", '" . trim(addslashes($value)) . "'";
						}
					}
				}
				
				
				if($names == "")
					return "";
				
				$names .= ")";
				$values .= ")";
				$names_lang .= ")";
				$values_lang .= ")";
				
				$sql = "INSERT INTO `" . $table . "` " . $names . " VALUES" . $values;
				DBConnect::query($sql, __FILE__, __LINE__);
				if($lang_dep)
				{
					//we adden de language die we nu posten + alle ontbrekende languages
					$newid = DBConnect::get_last_inserted($table, $id_field);
					$sql = "INSERT INTO `" . $table . "_lang` "  . $names_lang . " VALUES" . $values_lang;
					$sql = str_replace('[newid]', $newid, $sql);
					//echo $sql;
					DBConnect::query($sql, __FILE__, __LINE__);
					foreach(mainconfig::$languages as $key => $language)
					{
						if($key != $_POST[$table . ".lang"])
						{
							DBConnect::query("INSERT INTO `" . $table . "_lang` (`lang_id`, `lang_parent_id`, `lang`) VALUES('', '" . $newid . "', '" . $key . "')", __FILE__, __LINE__);
						}
					}
				}
			}
			else
			{
				//create an update sql
				$set = "";
				$set_lang = "";
				foreach($_POST as $key => $value)
				{
					$value = data_description::convert_from_post($value, $key);

					$tmp_expl = explode(".", $key);
					if(count($tmp_expl) > 1 && $tmp_expl[0] == $table && $id_field != $tmp_expl[1] && $value!="null")
					{
						$lang_dep_field = false;
						if($lang_dep)
						{
							$res_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tmp_expl[0] . "' AND `fieldname`='" . $tmp_expl[1] . "'", __FILE__, __LINE__);
							if($row_meta = mysql_fetch_array($res_meta))
								$lang_dep_field = (($row_meta["lang_dep"]>0)?true:false);
						}
						if($lang_dep_field)
						{
							if($set_lang == "")
								$set_lang = " `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
							else
								$set_lang .= ", `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
						}
						else
						{
							if($set == "")
								$set = " `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
							else
								$set .= ", `" . $tmp_expl[1] . "`='" . trim(addslashes($value)) . "'";
						}
					}
				}
				
				if($set == "")
					return "";
				$sql = "UPDATE `" . $table . "` SET " . $set . " WHERE `" . $id_field . "`='" . trim(addslashes($_POST[$table . "." . $id_field])) . "'";
				DBConnect::query($sql, __FILE__, __LINE__);
				if($lang_dep)
				{
					$sql = "UPDATE `" . $table . "_lang` SET "  . $set_lang . " WHERE `lang_parent_id`='" . trim(addslashes($_POST[$table . "." . $id_field])) . "' AND `lang`='" . trim(addslashes($_POST[$table . ".lang"])) . "'";
					DBConnect::query($sql, __FILE__, __LINE__);
				}
			}
		}
		
		static function options_getdef($desc_id)
		{
			$res = DBConnect::query("SELECT * FROM sys_datadescriptions WHERE `id`='" . $desc_id . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			if(!$row)
				return array();
			$optionsdef = explode('#', $row["options"]);
			$return = array();
			foreach($optionsdef as $tmp)
			{
				$chomps = explode(':', $tmp);
				if(trim($chomps[0]) != "")
					$return[] = array("name" => $chomps[0], "DATATYPE" => $chomps[1]);
			}
			return $return;
		}
		
		static function options_convert_to_array($desc_id, $str_options)
		{
			$defs = data_description::options_getdef($desc_id);
			$chomps = explode('|', $str_options);
			$return = array();
			$counter = 0;
			foreach($defs as $def)
			{
				$return[$def["name"]] = $chomps[$counter];
				$counter++;
			}
			
			return $return;
		}
		
		static function options_convert_to_string($desc_id, $arr_options)
		{
			$defs = data_description::options_getdef($desc_id);
			$return = "";
			$counter = 0;
			foreach($defs as $def)
			{
				if($counter != 0) $return .= "|";
				$return .= $arr_options[$def["name"]];
			}
			return $return;
		}
		
		static function convert_for_grid($table, $data, $lang = NULL)
		{
			//first we get all the datadescriptions and options
			if(count($data) == 0)
				return $data;
			$tmp = $data[0];
			$desc = array();
			foreach($tmp as $fieldname => $value)
			{
				$metaarray = array();
				$res_desc = DBConnect::query("SELECT sys_database_meta.*, sys_datadescriptions.name FROM sys_database_meta, sys_datadescriptions WHERE sys_database_meta.datadesc=sys_datadescriptions.id AND `tablename`='" . addslashes($table) . "' AND `fieldname`='" . addslashes($fieldname) . "'");
				$row_desc = mysql_fetch_array($res_desc);
				
				$metaarray["desc"] = $row_desc["name"];
				$metaarray["lang_dep"] = $row_desc["lang_dep"];
				$metaarray["options"] = data_description::options_convert_to_array($row_desc["datadesc"], $row_desc["data_options"]);
				//als het enums zijn éénmaal de data ophalen
				if($row_desc["name"] == "ENUM LANGUAGE")
				{
					$enumdata = array();
					foreach(mainconfig::$languages as $key => $one_value)
						$enumdata[$key] = $one_value;
					$metaarray["enumdata"] = $enumdata;
				}
				if($row_desc["name"] == "ENUM FROM TABLE")
				{
					$enumdata = array();
					$res_enum = DBConnect::query($metaarray["options"]["sql"], __FILE__, __LINE__);
					while($row_enum = mysql_fetch_array($res_enum))
						$enumdata[$row_enum[0]] = htmlentities((($row_enum[1] != NULL)?$row_enum[1]:$row_enum[0]));
					$metaarray["enumdata"] = $enumdata;
				}
				if($row_desc["name"] == "ENUM PAGES FROM TEMPLATE")
				{
					$enumdata = array();
					$items = formfield::enum_pages_from_template(0, 0, $metaarray["options"]["template_id"], ((trim($metaarray["options"]["min_level"]) != '')?$metaarray["options"]["min_level"]:NULL), ((trim($metaarray["options"]["max_level"]) != '')?$metaarray["options"]["max_level"]:NULL), "");
					foreach($items as $item)
						$enumdata[$item[0]] = htmlentities($item[1]);
					$metaarray["enumdata"] = $enumdata;
				}
				if($row_desc["name"] == "ENUM STATIC")
				{
					$enumdata = array();
					$items = explode("#", $metaarray["options"]["values"]);
					foreach($items as $item)
					{
						$chomps = explode(":", $item);
						$enumdata[$chomps[0]] = htmlentities($chomps[1]);
					}
					$metaarray["enumdata"] = $enumdata;
				}
				$desc[$fieldname] = $metaarray;
			}
			//print_r($desc);
			
			$datacopy = array();
			//rijen overlopen
			foreach($data as $row)
			{
				$rowcopy = $row;
				//overlopen colom
				foreach($row as $fieldname => $value)
				{
					switch($desc[$fieldname]["desc"])
					{
						case "VARCHAR":
						case "AUTOCOMPLETE":
						case "WORDLIST":
							$rowcopy[$fieldname] = htmlentities($value);
							break;
						case "TEXT":
							if(strlen($value) > 50)
								$rowcopy[$fieldname] = substr($value, 0, 50) . "...";
							$rowcopy[$fieldname] = htmlentities($rowcopy[$fieldname]);
							break;
						case "HTML BASIC":
						case "HTML FULL":
							$tmp = strip_tags($value);
							if(strlen($tmp) > 50)
								$tmp = substr($tmp, 0, 50) . "...";
							//$tmp = htmlspecialchars($tmp);
							$rowcopy[$fieldname] = $tmp;
							break;
						case "LINK":
							$rowcopy[$fieldname] = '<a href="' . $value . '" target="_blank">' . $value . '</a>';
							break;
						case "DATE":
							if(is_numeric($value))
								$rowcopy[$fieldname] = (($value <= 0)?"&nbsp;":date("d/m/Y", $value));
							break;
						case "TIME":
							if(is_numeric($value))
								$rowcopy[$fieldname] = DateAndTime::format_time($value, $desc[$fieldname]["options"]["hours"], $desc[$fieldname]["options"]["minutes"], $desc[$fieldname]["options"]["seconds"]);
							break;
						case "YESNO":
							$rowcopy[$fieldname] = (($value>0)?"yes":"no");
							break;
						case "FILE":
						case "PICTURE":
						case "PICTURE FORMAT":
						case "VIDEO":
						case "AUDIO":
							if(trim($value) != "")
							{
								$res_file = NULL;
								if($desc[$fieldname]["desc"] == "PICTURE FORMAT")
									$res_file = DBConnect::query("SELECT * FROM `site_files_derived` WHERE `id`='" . addslashes($value) . "'");
								else
									$res_file = DBConnect::query("SELECT * FROM `site_files` WHERE `id`='" . addslashes($value) . "'");
								$row_file = mysql_fetch_array($res_file);
								$tmp = pathinfo($row_file["path"]);
								$path = $row_file["path"];
								if(substr($path, 0, 1) == '/')
									$path = substr($path, 1);
								$rowcopy[$fieldname] = '<a href="javascript:cms2_open_file(\'' . $path . '\', \'' . $tmp["extension"] . '\', null);">' . $tmp["filename"] . '.' . $tmp["extension"] . '</a>';
							}
							break;
						case "ENUM LANGUAGE":
						case "ENUM FROM TABLE":
						case "ENUM PAGES FROM TEMPLATE":
						case "ENUM PAGES FROM PARENT":
						case "ENUM STATIC":
							$rowcopy[$fieldname] = $desc[$fieldname]["enumdata"][$value];
							break;
						case "WORDDATALIST":
							//we halen de data op
							if(trim($value) != "")
							{
								$sql = $desc[$fieldname]["options"]["sql"];
								$tmp = explode("WHERE", $sql);
								if(count($tmp) > 1)
								{
									$tmp2 = explode("ORDER BY", $tmp[1]);
									$sql = $tmp[0] . " WHERE " . $tmp2[0] . " AND `" . $desc[$fieldname]["options"]["idfield"] . "` IN ('" . implode("','",explode(";", $value)) . "')";
									if(count($tmp2) > 1)
										 $sql .= " ORDER BY " . $tmp2[1];
								}
								else
								{
									$tmp2 = explode("ORDER BY", $sql);
									$sql = $tmp2[0] . " WHERE `" . $desc[$fieldname]["options"]["idfield"] . "` IN ('" . implode("','",explode(";", $value)) . "')";
									if(count($tmp2) > 1)
										 $sql .= " ORDER BY " . $tmp2[1];
								}
								if($desc[$fieldname]["lang_dep"] > 0)
									$sql = str_replace("[LANG]", $lang, $sql);
								$res = DBConnect::query($sql, __FILE__, __LINE__);
								$str = "";
								while($row = mysql_fetch_array($res))
								{
									if($str != "") $str .= ", ";
									$str .= $row[2];
								}
								$rowcopy[$fieldname] = htmlentities($str);
							}
							else
								$rowcopy[$fieldname] = "";
							break;
						default:
							break;
					}
					if(trim($rowcopy[$fieldname]) == "")
						$rowcopy[$fieldname] = "&nbsp;";
				}
				$datacopy[] = $rowcopy;
			}
			return $datacopy;
			
		}
		
		static function output_save_xml($errormsgs, $id_field, $newid = NULL, $extraxml = "")
		{
				header('Content-Type: text/xml');
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				echo '<?xml version="1.0" encoding="ISO-8859-1"?>
					<savestat>
						<status>' . ((count($errormsgs) == 0)?'OK':'NOK') . '</status>
						<idfield>' . $id_field . '</idfield>
						' . (($newid != NULL)?'<newid>' . $newid . '</newid>':'') . '
					<errors>';
				foreach($errormsgs as $name => $error)
					echo '<error><error_fieldname>' . $name . '</error_fieldname><message>' . $error . '</message></error>';
				echo '</errors>';
				echo $extraxml;
				echo '</savestat>';
		}
		
		static function get_field_type($postname)
		{
			$chomps = explode(".", $postname);
			//we zoeken de db_meta
			$res = DBConnect::query("SELECT sys_datadescriptions.name FROM `sys_database_meta`, `sys_datadescriptions` WHERE sys_database_meta.datadesc = sys_datadescriptions.id AND `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			return $row["name"];
		}
		
		static function is_lang_dep($table, $field)
		{
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $table . "' AND `fieldname`='" . $field . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			if($row["lang_dep"] > 0)
				return true;
			else
				return false;
		}
		
	}
?>