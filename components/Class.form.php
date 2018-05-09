<?php
	//Menu Component is a vertical or horizontal menu.
	class form
	{
		static function load_data($table, $dataid)
		{
			$strout = "";
			$strout .= '<?xml version="1.0" encoding="utf-8"?><formdata><table id="' . $table . '">';
			//we halen de meta op van de tabel
			$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $table . "'", __FILE__, __LINE__);
			$row_meta = mysql_fetch_array($res_meta);
			
			//check for new or not
			$newrow = false;
			if(trim($dataid) == "")
				$newrow = true;
			$row_data = array();
			if(!$newrow)
			{
				$res_data = DBConnect::query("SELECT * FROM `" . $table . "` WHERE `id`='" . $dataid . "'", __FILE__, __LINE__);
				if(!$row_data = mysql_fetch_array($res_data))
					$newrow = true;
			}
			//we halen de standaardwaarde op voor elk veld
			if($newrow == true)
			{
				$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $table . "`", __FILE__, __LINE__);
				while($row_field = mysql_fetch_array($result_fields))
				{
					$res_field_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $table . "' AND `fieldname`='" . $row_field["Field"] . "'", __FILE__, __LINE__);
					if($row_field_meta = mysql_fetch_array($res_field_meta))
						$row_data[$row_field["Field"]] = $row_field_meta["data_standaardwaarde"];
					else
						$row_data[$row_field["Field"]] = "";
				}
			}
			$lang_parent_id = $row_data["id"];
			//schrijven van de fields
			foreach($row_data as $field => $value)
			{
				if(is_numeric($field)) continue;
				$strout .= '<field id="' . $table . '.' . $field . '">' . str_replace('&amp;#8364;', '&#8364;', str_replace("'", "__singlequod__", htmlspecialchars(data_description::convert_db_to_field($value, $table . '.' . $field)))) . '</field>';
			}
			//Checken voor lang
			if($row_meta["lang_dep"] >= 1)
			{
				foreach(mainconfig::$languages as $abr => $lang)
				{
					$strout .= '<lang id="' . $abr . '">';
					$row_data = array();
					//if(!$newrow)
					//{
						$res_data = DBConnect::query("SELECT * FROM `" . $table . "_lang` WHERE `lang_parent_id`='" . $dataid . "' AND `lang`='" . $abr . "'", __FILE__, __LINE__);
						if(!$row_data = mysql_fetch_array($res_data))
						{
							$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $table . "_lang`", __FILE__, __LINE__);
							while($row_field = mysql_fetch_array($result_fields))
							{
								$res_field_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $table . "' AND `fieldname`='" . $row_field["Field"] . "'", __FILE__, __LINE__);
								if($row_field_meta = mysql_fetch_array($res_field_meta))
									$row_data[$row_field["Field"]] = $row_field_meta["data_standaardwaarde"];
								else
									$row_data[$row_field["Field"]] = "";
							}
							$row_data["lang_parent_id"] = $lang_parent_id;
							$row_data["lang"] = $abr;
						}
					//}
					foreach($row_data as $field => $value)
					{
						if(is_numeric($field)) continue;
						$strout .= '<field id="' . $table . '.' . $field . '">' . str_replace('&amp;#8364;', '&#8364;', str_replace("'", "__singlequod__", htmlspecialchars(data_description::convert_db_to_field($value, $table . '.' . $field)))) . '</field>';
						/*if($field == 'ticket_price')
							debug::message('the value: ' . str_replace(chr(128), '&#8364;', htmlspecialchars($value));*/
					}
					$strout .= '</lang>';
				}
			}
			$strout .= '</table></formdata>';
			return $strout;
		}
		
		static function ajax($extra_errors = NULL, $nosave = array())
		{
			switch($_GET["action"])
			{
				case "load_data":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo form::load_data($_GET["table"], $_GET["data"]);
					break;
				case "save_data":
					$data = $_POST["data"];
					$data = urldecode($data);
					$data = str_replace("___EUR___", "€", $data);
					$data = utf8_encode($data);
					$data =  stripslashes($data);
					$data = str_replace("___AMP___", "&", $data);
					$data = str_replace("___QUEST___", "?", $data);
					$data = str_replace("___HEK___", "#", $data);
					$data = str_replace("___PLUS___", "+", $data);
					
					
					//echo $data;
					
					$doc = new SimpleXMLElement($data);
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<?xml version="1.0" encoding="utf-8"?>
							<formsave>';
						
					foreach($doc->table as $table)
					{
						echo '<table id="' . $table["id"] . '">';
						//we check every field within the table for errors
						$found_error = false;
						echo '<errors>';
						foreach($table->field as $field)
						{
							if(isset($nosave[(string)$field["id"]]))
								continue;
							$sErr = data_description::validate_db($field["id"], (string)$field);
							if(trim($sErr) != "")
							{
								echo '<error id="' . $field["id"] . '"><![CDATA[' . $sErr . ']]></error>';
								$found_error = true;
							}
							if(isset($extra_errors[(string)$field["id"]]))
							{
								echo '<error id="' . $field["id"] . '"><![CDATA[' . $extra_errors[(string)$field["id"]] . ']]></error>';
								$found_error = true;
							}
						}
						//nu ook de lang dep
						if((bool)$table->lang)
						{
							foreach($table->lang as $lang)
							{
								echo '<lang id="' . $lang["id"] . '">';
								foreach($lang->field as $field)
								{
									if(isset($nosave[(string)$field["id"]]))
										continue;
									$sErr = data_description::validate_db($field["id"], (string)$field);
									if(trim($sErr) != "")
									{
										echo '<error id="' . $field["id"] . '"><![CDATA[' . $sErr . ']]></error>';
										$found_error = true;
									}
									if(isset($extra_errors[(string)$lang["id"]][(string)$field["id"]]))
									{
										echo '<error id="' . $field["id"] . '"><![CDATA[' . $extra_errors[(string)$lang["id"]][(string)$field["id"]] . ']]></error>';
										$found_error = true;
									}
								}
								echo '</lang>';
							}
						}
						echo '</errors>';
						//als error dan niet saven, anders wel
						if(!$found_error)
						{
							echo '<savestatus>OK</savestatus>';
							//search id field
							$idfield = NULL;
							foreach($table->field as $field)
							{
								if($field["id"] == $table["id"] . ".id")
								{
									$idfield = (string)$field;
									break;
								}
							}
							if(trim($idfield) == "")
							{
								//insert
								echo '<savetype>insert</savetype>';
								//we save the normal data first
								$names = array();
								$values = array();
								foreach($table->field as $field)
								{
									if(isset($nosave[(string)$field["id"]]))
										continue;
									$tmp = explode(".", $field["id"]);
									//checken of het een password is
									if(data_description::get_field_type($field["id"]) != "PASSWORD" || trim((string)$field) != "")
									{
										$names[] = $tmp[1];
										$values[] = data_description::convert_field_to_db(trim((string)$field), $field["id"]);
									}
								}
								$sql = "INSERT INTO `" . $table["id"] . "` (`" . implode("`, `", $names) . "`) VALUES ('" . implode("', '", $values) . "')";
								//echo "<sql><![CDATA[" . $sql . "]]></sql>";
								//INVOEGEN EN NIEUW ID OPHALEN
								DBConnect::query($sql, __FILE__, __LINE__);
								$newid = DBConnect::get_last_inserted($table["id"], "id");
								echo '<newid id="nolang">' . $newid . '</newid>';
								if((bool)$table->lang)
								{
									foreach($table->lang as $lang)
									{
										$names = array();
										$values = array();
										foreach($lang->field as $field)
										{
											if(isset($nosave[(string)$field["id"]]))
												continue;
											$tmp = explode(".", $field["id"]);
											if(data_description::get_field_type($field["id"]) != "PASSWORD" || trim((string)$field) != "")
											{
												$names[] = $tmp[1];
												if($field["id"] == $table["id"] . ".lang_parent_id")
													$values[] = $newid;
												elseif($field["id"] == $table["id"] . ".lang")
													$values[] = $lang["id"];
												else
													$values[] = data_description::convert_field_to_db(trim((string)$field), $field["id"]);
											}
										}
										$sql = "INSERT INTO `" . $table["id"] . "_lang` (`" . implode("`, `", $names) . "`) VALUES ('" . implode("', '", $values) . "')";
										//echo "<sql><![CDATA[" . $sql . "]]></sql>";
										DBConnect::query($sql, __FILE__, __LINE__);
										$newid_lang = DBConnect::get_last_inserted($table["id"] . "_lang", "lang_id");
										echo '<newid id="' . $lang["id"] . '">' . $newid_lang . '</newid>';
									}
								}
								form::update_newrow_order($table["id"], $newid);
							}
							else
							{
								echo '<savetype>update</savetype>';
								//update
								$set = array();
								$id_value = "";
								foreach($table->field as $field)
								{
									if(isset($nosave[(string)$field["id"]]))
										continue;
									$tmp = explode(".", $field["id"]);
									if(data_description::get_field_type($field["id"]) != "PASSWORD" || trim((string)$field) != "")
									{
										$set[] = "`" . $tmp[1] . "`='" . data_description::convert_field_to_db(trim((string)$field), $field["id"]) . "'";
										if($field["id"] == $table["id"] . ".id")
											$id_value = (string)$field;
									}
								}
								$sql = "UPDATE `" . $table["id"] . "` SET " . implode(", ", $set) . " WHERE `id`='" . $id_value . "'";
								//debug::message($sql);
								//echo "<sql><![CDATA[" . $sql . "]]></sql>";
								DBConnect::query($sql, __FILE__, __LINE__);
								//Nu de lang dep
								if((bool)$table->lang)
								{
									foreach($table->lang as $lang)
									{
										$set = array();
										$id_value = "";
										$lang_value = "";
										foreach($lang->field as $field)
										{
											if(isset($nosave[(string)$field["id"]]))
												continue;
											$tmp = explode(".", $field["id"]);
											if(data_description::get_field_type($field["id"]) != "PASSWORD" || trim((string)$field) != "")
											{
												$set[] = "`" . $tmp[1] . "`='" . data_description::convert_field_to_db(trim((string)$field), $field["id"]) . "'";
												if($field["id"] == $table["id"] . ".lang_id")
													$id_value = (string)$field;
												if($field["id"] == $table["id"] . ".lang")
													$lang_value = (string)$field;
											}
										}
										$sql = "UPDATE `" . $table["id"] . "_lang` SET " . implode(", ", $set) . " WHERE `lang_id`='" . $id_value . "' AND `lang`='" . $lang_value . "'";
										//echo "<sql><![CDATA[" . $sql . "]]></sql>";
										DBConnect::query($sql, __FILE__, __LINE__);
									}
								}
							}
						}
						else
						{
							echo '<savestatus>NOK</savestatus>';
						}
						//nu nog checken of de tabel subdata heeft
						$res_sub = DBConnect::query("SELECT * FROM `sys_database_subtable` WHERE `table_parent`='" . $table["id"] . "'", __FILE__, __LINE__); 
						if(mysql_num_rows($res_sub) > 0)
							echo '<hassubdata>yes</hassubdata>';
						else
							echo '<hassubdata>no</hassubdata>';
						//nu nog de warning script runnen als dat er is
						$res_table = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . addslashes($table["id"]) . "'", __FILE__, __LINE__);
						$row_table = mysql_fetch_array($res_table);
						if(trim($row_table["warning_script"]) != "")
						{
							$checkdata = $table;
							$warning = "";
							include ("snippets/checks/snippet." . stripslashes($row_table["warning_script"]) . ".php");
							if($warning != "")
								echo '<warning>' . $warning . '</warning>';
						}
						echo '</table>';
					}
					echo '</formsave>';
					
					break;
				case "reload_form":
					form::show_autoform($_GET["rf_table"], $_GET["rf_id"], $_GET["rf_lang"], true);
					break;
			}
		}
		
		static function show_autoform($tablename, $id, $language = NULL, $fromreload = false, $showlanglinks = true)
		{
			//we doen hetzelfde als show_autoform_new, maar gaan zelf de data ophalen
			$res_table = DBConnect::query("SELECT * FROM sys_database_table WHERE `table`='" . $tablename . "'", __FILE__, __LINE__);
			$row_table = mysql_fetch_array($res_table);
			$sql = "";
			if($row_table["lang_dep"] > 0)
				$sql = "SELECT " . $tablename . ".*, " . $tablename . "_lang.* FROM " . $tablename . ", " . $tablename . "_lang WHERE " . $tablename . ".id = " . $tablename . "_lang.lang_parent_id AND " . $tablename . ".id='" . $id . "' AND " . $tablename . "_lang.lang='" . $language . "'";
			else
				$sql = "SELECT * FROM " . $tablename . " WHERE id = '" . $id . "'";
			$res = DBConnect::query($sql, __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			$values = array();
			if($row)
			{
				foreach($row as $key => $value)
				{
					if(!is_numeric($key))
						$values[$key] = $value;
				}
			}
			form::show_autoform_new($tablename, $values, $language, $fromreload, $showlanglinks);
		}
		
		static function show_autoform_new($tablename, $values, $language = NULL, $fromreload = false, $showlanglinks = true)
		{
			echo '<div id="' . $tablename . '_form">';
			//de lang links
			$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $tablename . "'", __FILE__, __LINE__);
			$row_meta = mysql_fetch_array($res_meta);
			if($row_meta["lang_dep"] > 0 && $showlanglinks)
			{
				echo '<div style="text-align: right; padding-right: 4px; padding-bottom:4px;" id="' . $tablename . '_form_langlinks">';
				foreach(mainconfig::$languages as $abr => $lang)
				{
					echo '<span lang="' . $abr . '" class="' . (($abr == $language)? 'form_lang_selector_selected':'form_lang_selector') . '" current="' . (($abr == $language)? 'yes':'no') . '" onclick="window.' . $tablename . '_form.changelang(\'' . $abr . '\', this);">' . $abr . '</span>';
				}
				echo '</div>';
			}
			//eerst alle velden die een description hebben + de placeholders
			$placeholders = array();
			$resplaceholders = DBConnect::query("SELECT * FROM `sys_database_placeholder` WHERE `table`='" . $tablename . "' ORDER BY `order`", __FILE__, __LINE__);
			while($row_ph = mysql_fetch_array($resplaceholders))
			{
				$placeholders[] = $row_ph;
			}
			$fields_in_meta = array();
			$resmeta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tablename . "' ORDER BY `order`", __FILE__, __LINE__);
			$showsed_subdata = array();
			while($rowmeta=mysql_fetch_array($resmeta))
			{
				//we echoën alle placeholders met een order kleiner dan field order
				$indexen = array();
				foreach($placeholders as $index => $placeholder)
				{
					if($rowmeta["order"] > $placeholder["order"])
					{
						form::show_placeholder($placeholder, $tablename, $language, $values["id"], $values["lang_id"]);
						$indexen[] = $index;
						if($placeholder["type"] == "subtable")
							$showsed_subdata[] = $placeholder["subtable"];
					}
				}
				//gevonden placeholders verwijderen
				foreach($indexen as $index)
					unset($placeholders[$index]);
				formfield::publish_dbfield($tablename . "." . $rowmeta["fieldname"], $values[$rowmeta["fieldname"]], $language);
				$fields_in_meta[] = $rowmeta["fieldname"];
			}
			//overige placeholders
			foreach($placeholders as $index => $placeholder)
			{
				form::show_placeholder($placeholder, $tablename, $language, $values["id"], $values["lang_id"]);
				if($placeholder["type"] == "subtable")
					$showsed_subdata[] = $placeholder["subtable"];
			}
			/*
			//alle velden tonen die niet in de meta stonden
			$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $tablename . "`", __FILE__, __LINE__);
			while($row_field = mysql_fetch_array($result_fields))
			{
				//var_dump(data_description::convert_db_to_field($values[$row_field["Field"]], $tablename . "." . $row_field["Field"]));
				if(!in_array($row_field["Field"], $fields_in_meta))
					formfield::publish_dbfield($tablename . "." . $row_field["Field"], data_description::convert_db_to_field($values[$row_field["Field"]], $tablename . "." . $row_field["Field"]), $language);
			}
			//nu alle velden tonen die niet in de meta stonden van de lang tabel
			if(DBConnect::check_if_table_exists($tablename . '_lang'))
			{
				$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $tablename . "_lang`", __FILE__, __LINE__);
				while($row_field = mysql_fetch_array($result_fields))
				{
					if(!in_array($row_field["Field"], $fields_in_meta))
						formfield::publish_dbfield($tablename . "." . $row_field["Field"], data_description::convert_db_to_field($values[$row_field["Field"]], $tablename . "." . $row_field["Field"]), $language);
				}
			}*/
			echo '</div>';
			
			if(!$fromreload)
			{
				$datastr = form::load_data($tablename, $values["id"]);
				//$datastr = nl2br($datastr);
				$datastr = str_replace("\n", "__newline__", $datastr);
				debug::message($datastr);
				//echo '<div>' . htmlentities($datastr) . '</div>';
				
				//var_dump($datastr);
				echo '<script language="javascript">
							datastr = \'' . addslashes($datastr) . '\';
							datastr = datastr.replace(/__newline__/g, "\n");
							datastr = datastr.replace(/__singlequod__/g, "\'");
							//alert(datastr);
							window.' . $tablename . '_form = new form(\'' . $tablename . '_form\', \'' . $tablename . '\', \'' . $values["id"] . '\', "") ;
							window.' . $tablename . '_form.currentlang = \'' . $language . '\';
							window.' . $tablename . '_form.dataDoc = $(cms2_string_to_xml(datastr));
							window.' . $tablename . '_form.clear_subdata();';
				$res_sub = DBConnect::query("SELECT * FROM sys_database_subtable WHERE `table_parent`='" . $tablename . "' ORDER BY `order`", __FILE__, __LINE__);
				while($row_sub = mysql_fetch_array($res_sub))
				{
					//we get the table meta
					$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $row_sub["table_sub"] . "'", __FILE__, __LINE__);
					$row_meta = mysql_fetch_array($res_meta);
					if($row_meta["lang_dep"] > 0 || $row_sub["new_list_per_lang"] > 0)
						echo 'window.' . $tablename . '_form.add_subdata(\'form_' . $tablename . '_' . $row_sub["table_sub"] . '\');';
				}
				
				echo 'initializeBlicsmFormFields();
					</script>';
			}
			
			//tonen van de overige data editors
			if(trim($values["id"]) != "")
			{
				$res_sub = DBConnect::query("SELECT * FROM sys_database_subtable WHERE `table_parent`='" . $tablename . "' ORDER BY `order`", __FILE__, __LINE__);
				
				while($row_sub = mysql_fetch_array($res_sub))
				{
					if(in_array($row_sub['table_sub'], $showsed_subdata))
						continue;
					//we get the table meta
					$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $row_sub["table_sub"] . "'", __FILE__, __LINE__);
					$row_meta = mysql_fetch_array($res_meta);
					$de = new dataeditor("form_" . $tablename . '_' . $row_sub["table_sub"], 500, 500, $row_sub["table_sub"]);
					if($row_sub["new_list_per_lang"] > 0)
						$de->set_parent($tablename . "_lang", $row_sub["foreign_key_field"], $values["lang_id"], true);
					else
						$de->set_parent($tablename, $row_sub["foreign_key_field"], $values["id"]);
					$de->set_title($row_meta["table_name_plural"]);
					if(trim($language) != "")
						$de->set_current_lang($language);
					$de->publish(false);
				}
			}
		}
		
		static function show_placeholder($row, $tablename, $language, $values_id, $values_lang_id)
		{
			if($row["type"] == "splitter")
			{
				echo '<div class="splitter"><span>' . stripslashes($row["name"]) . '</span></div>';
			}
			elseif($row["type"] == "subtable")
			{
				$res_sub = DBConnect::query("SELECT * FROM sys_database_subtable WHERE `table_parent`='" . $tablename . "' AND `table_sub`='" . $row["subtable"] . "' ORDER BY `order`", __FILE__, __LINE__);
				
				if($row_sub = mysql_fetch_array($res_sub))
				{
					//we get the table meta
					$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $row_sub["table_sub"] . "'", __FILE__, __LINE__);
					$row_meta = mysql_fetch_array($res_meta);
					$de = new dataeditor("form_" . $tablename . '_' . $row_sub["table_sub"], 500, 500, $row_sub["table_sub"]);
					if($row_sub["new_list_per_lang"] > 0)
						$de->set_parent($tablename . "_lang", $row_sub["foreign_key_field"], $values_lang_id, true);
					else
						$de->set_parent($tablename, $row_sub["foreign_key_field"], $values_id);
					$de->set_title($row_meta["table_name_plural"]);
					if(trim($language) != "")
						$de->set_current_lang($language);
					$de->publish(false);
				}
			}
			elseif($row["type"] == "code")
			{
				include("snippets/back/snippet." . $row["subtable"] . ".php");
			}
		}
		
		static function update_newrow_order($table, $newid)
		{
			//we gaan de table meta ophalen
			$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $table . "'", __FILE__, __LINE__);
			$row_meta = mysql_fetch_array($res_meta);
			if($row_meta && $row_meta['grid_order'] > 0)
			{
				//de data ophalen
				$res_data = DBConnect::query("SELECT * FROM `" . $table . "` WHERE `id`='" . $newid . "'", __FILE__, __LINE__);
				$row_data = mysql_fetch_array($res_data);
				//nu checken we of het een subtable is zoja, dan moeten groupen op het foreign_key_field
				$res_sub = DBConnect::query("SELECT * FROM `sys_database_subtable` WHERE `table_sub`='" . $table . "'", __FILE__, __LINE__);
				$row_sub = mysql_fetch_array($res_sub);
				//we doen een update
				if($row_meta["grid_order_newplace"] == "bottom")
				{
					$res = DBConnect::query("SELECT `" . $row_meta["grid_order_field"] . "` FROM `" . $table . "` " . (($row_sub)?" WHERE `" . $row_sub["foreign_key_field"] . "`='" . $row_data[$row_sub["foreign_key_field"]] . "'":"") . " ORDER BY `" . $row_meta["grid_order_field"] . "` DESC LIMIT 0,1", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					debug::message("SELECT `" . $row_meta["grid_order_field"] . "` FROM `" . $table . "` " . (($row_sub)?" WHERE `" . $row_sub["foreign_key_field"] . "`='" . $row_data[$row_sub["foreign_key_field"]] . "'":"") . " ORDER BY `" . $row_meta["grid_order_field"] . "` DESC LIMIT 0,1");
					DBConnect::query("UPDATE `" . $table . "` SET `" . $row_meta["grid_order_field"] . "`='" . ((int)$row[$row_meta["grid_order_field"]] + 1) . "' WHERE `id`='" . $newid . "'", __FILE__, __LINE__);
				}
				else
				{
					DBConnect::query("UPDATE `" . $table . "` SET `" . $row_meta["grid_order_field"] . "`=`" . $row_meta["grid_order_field"] . "` + 1 " . (($row_sub)?" WHERE `" . $row_sub["foreign_key_field"] . "`='" . $row_data[$row_sub["foreign_key_field"]] . "'":""), __FILE__, __LINE__);
					DBConnect::query("UPDATE `" . $table . "` SET `" . $row_meta["grid_order_field"] . "`='1' WHERE `id`='" . $newid . "'", __FILE__, __LINE__);
				}
			}
		}
	}
?>