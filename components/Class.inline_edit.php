<?php
	//Menu Component is a vertical or horizontal menu.
	// $_get["type"] = 'page', 'list', 'data', 
	class inline_edit
	{
		static function ajax()
		{
			switch($_GET["type"])
			{
				case "page":
					if(!login::right("backpage_content_pages", "view"))
					{
						echo "NORIGHTS:You don't have the permission to view or edit pages";
						exit();
					}
					switch($_GET["action"])
					{
						case "edit":
							echo '<script language="javascript" src="/js/page_content.js"></script>';
							$_GET["action"] = "loadpage";
							$_GET["edit_page_lang"] = $_SESSION["LANGUAGE"];
							$_GET["edit_page_id"] = $_GET["data1"];
							include ("pages/programmedajax/Ajax.content.php");
							break;
						case "del":
							$result = DBConnect::query("SELECT * FROM `site_page_lang` WHERE lang_parent_id='" . addslashes(urldecode($_GET["data1"])) . "' AND lang='" . $_SESSION["LANGUAGE"] . "'", __FILE__, __LINE__);
							$row_page_lang = mysql_fetch_array($result);
							echo '<div class="contentheader">
									<div class="divleft">Delete Page: ' . htmlentities(stripslashes($row_page_lang["name"])) . '</div>
								</div>
								<div class="contentcontent">';
							if($_GET["delconfirm"] == "delete")
							{
								 echo "real delete";
							}
							elseif($_GET["delconfirm"] == "cancel")
							{
								echo 'The page is not removed';
							}
							else
							{
								echo '<p style="text-align:center; padding: 50px;">
										Are you sure you want to remove this entire page. <b>You can also unpublish a page</b> so the user will not be able to see this, but the data is not lost.
										<br><br><input type="button" value="Remove page"/>&nbsp;<input type="button" value="Cancel"/>
									</p>';
							}
							echo '</div>';
							break;
						case "add":
							$new_info = page::create_page($_GET["data1"]);
							echo '<script language="javascript" src="/js/page_content.js"></script>';
							$_GET["action"] = "loadpage";
							$_GET["edit_page_lang"] = $_SESSION["LANGUAGE"];
							$_GET["edit_page_id"] = $new_info["id"];
							include ("pages/programmedajax/Ajax.content.php");
							break;
					}
					break;
				case "list":
					$extra = unserialize(stripslashes(str_replace('_dblquot_', '"', urldecode($_GET["data3"]))));
					var_dump( $extra);
					$result = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . addslashes(urldecode($_GET["data1"])) . "'", __FILE__, __LINE__);
					$row_table = mysql_fetch_array($result);
					echo '<div class="contentheader">
									<div class="divleft">Edit list: ' . htmlentities(stripslashes($row_table["table_name_plural"])) . '</div>
								</div>
								<div class="contentcontent">';
					$de = new dataeditor("inline_de_" . $row_table["table"], 500, 500, urldecode($_GET["data1"]));
					$de->set_current_lang($_SESSION["LANGUAGE"]);
					if(trim($extra["parent_id_field"] != "") && trim($extra["parent_table"] != "") && trim($extra["parent_id_value"] != ""))
					{
						$de->set_parent($extra["parent_id_table"],$extra["parent_id_field"], $extra["parent_id_value"]);
					}
					$de->publish(false);
					echo '</div>';
					break;
				case "data":
					$result = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . addslashes(urldecode($_GET["data1"])) . "'", __FILE__, __LINE__);
					$row_table = mysql_fetch_array($result);
					echo '<div class="contentheader">
									<div class="divleft">' . htmlentities(stripslashes($row_table["table_name"])) . '</div>';
					if($_GET["action"] == "edit" || $_GET["action"] == "add")
								echo '<div class="divright">
										<div class="savebutton" onclick="inline_edit_save_data_item(\'' . urldecode($_GET["data1"]) . '_form\');" id="inline_edit_data_savebutton">Save</div>
									</div>';
					echo '</div>
								<div class="contentcontent">';
					switch($_GET["action"])
					{
						case "edit":
							$res_data = NULL;
							
							if($row_table["lang_dep"] > 0)
								$res_data = DBConnect::query("SELECT " . addslashes(urldecode($_GET["data1"])) . ".*, " . addslashes(urldecode($_GET["data1"])) . "_lang.* FROM `" . addslashes(urldecode($_GET["data1"])) . "`, `" . addslashes(urldecode($_GET["data1"])) . "_lang` WHERE " . addslashes(urldecode($_GET["data1"])) . ".id = " . addslashes(urldecode($_GET["data1"])) . "_lang.lang_parent_id AND " . addslashes(urldecode($_GET["data1"])) . "_lang.lang = '" . $_SESSION["LANGUAGE"] . "' AND `id`='" . addslashes(urldecode($_GET["data2"])) . "'", __FILE__, __LINE__);
							else
								$res_data = DBConnect::query("SELECT * FROM `" . addslashes(urldecode($_GET["data1"])) . "` WHERE `id`='" . addslashes(urldecode($_GET["data2"])) . "'", __FILE__, __LINE__);
							$row_data = mysql_fetch_array($res_data);
							form::show_autoform_new(urldecode($_GET["data1"]), $row_data, $_SESSION["LANGUAGE"]);
							break;
						case "del":
							if($_GET["delconfirm"] == "delete")
							{
								$extra = unserialize(stripslashes(str_replace('_dblquot_', '"', urldecode($_GET["data3"]))));
								data::delete(urldecode($_GET["data1"]), urldecode($_GET["data2"]), $extra["order_field"], $extra["parent_id_field"], $extra["parent_id_value"]);
								echo '<p style="text-align:center; padding: 50px;">The data was <b>successfully</b> removed</p>
									<script language="javascript">inline_edit_after_save();</script>';
							}
							elseif($_GET["delconfirm"] == "cancel")
							{
								echo '<p style="text-align:center; padding: 50px;">The data was <b>not</b> removed</p>';
							}
							else
							{
								echo '<p style="text-align:center; padding: 50px;">
										Are you sure you want to remove this item?
										<br><br>
										<input type="button" value="Remove" onclick="$(\'#inline_edit_popup\').load(\'/ajax.php?sessid=' . session_id() . '&inline_edit=true&type=' . $_GET["type"] . '&action=' . $_GET["action"] . '&data1=' . $_GET["data1"] . '&data2=' . $_GET["data2"] . '&data3=' . str_replace('"', '_dblquot_', $_GET["data3"]) . '&delconfirm=delete\');"/>&nbsp;
										<input type="button" value="Cancel" onclick="$(\'#inline_edit_popup\').load(\'/ajax.php?sessid=' . session_id() . '&inline_edit=true&type=' . $_GET["type"] . '&action=' . $_GET["action"] . '&data1=' . $_GET["data1"] . '&data2=' . $_GET["data2"] . '&data3=' . str_replace('"', '_dblquot_', $_GET["data3"]) . '&delconfirm=cancel\');"/>
									</p>';
							}
							
							break;
						case "add":
							$data = array();
							//WE HALEN DE STANDAARD WAARDEN OP
							$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . addslashes(urldecode($_GET["data1"])) . "`", __FILE__, __LINE__);
							while($row_field = mysql_fetch_array($result_fields))
							{
								$res_finfo = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . addslashes(urldecode($_GET["data1"])) . "' AND `fieldname`='" . $row_field["Field"] . "'", __FILE__, __LINE__);
								if($row_finfo = mysql_fetch_array($res_finfo))
								{
									if(trim($row_finfo["data_standaardwaarde"]) != "")
									$data[$row_field["Field"]] = stripslashes($row_finfo["data_standaardwaarde"]);
								}
							}
							//standaardwaarden zitten in een serialized array in data3
							$standaard = unserialize(urldecode($_GET["data3"]));
							if(is_array($standaard))
							{
								foreach($standaard as $k => $v)
								{
									$data[$k] = $v;
								}
							}
							
							if($row_table["lang_dep"])
								$data["lang"] = $_SESSION["LANGUAGE"];
							form::show_autoform_new(urldecode($_GET["data1"]), $data, $_SESSION["LANGUAGE"]);
							break;
					}
					echo '</div>';
					break;
				case "field":
					//ophalen value
					if($_GET["fieldsave"] == "true")
					{
						$msg = data_description::validate_db(urldecode($_GET["table"]) . '.' . urldecode($_GET["field"]), $_POST[urldecode($_GET["table"]) . '.' . urldecode($_GET["field"])]);
						if($msg == "")
						{
							$value = data_description::convert_field_to_db($_POST[urldecode($_GET["table"]) . '.' . urldecode($_GET["field"])], urldecode($_GET["table"]) . '.' . urldecode($_GET["field"]));
							DBConnect::query("UPDATE `" . urldecode($_GET["table"]) . ((data_description::is_lang_dep(urldecode($_GET["table"]), urldecode($_GET["field"])))?'_lang':'') . "` SET `" . urldecode($_GET["field"]) . "`='" . $value . "' WHERE `" . urldecode($_GET["id_field"]) . "`='" . urldecode($_GET["id_value"]) . "'", __FILE__, __LINE__);
							//saven van eventuele pic formats
							$res_picform = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . urldecode($_GET["table"]) . "' AND `datadesc`='22'", __FILE__, __LINE__);
							while($row_picform = mysql_fetch_array($res_picform))
							{
								$options = data_description::options_convert_to_array($row_picform["datadesc"], $row_picform["data_options"]);
								if($options["master_pic_field"] == urldecode($_GET["field"]))
								{
									$value = data_description::convert_field_to_db($_POST[urldecode($_GET["table"]) . '.' . $row_picform["fieldname"]], urldecode($_GET["table"]) . '.' . $row_picform["fieldname"]);
									DBConnect::query("UPDATE `" . urldecode($_GET["table"]) . (($row_picform["lang_dep"]>0)?'_lang':'') . "` SET `" . $row_picform["fieldname"] . "`='" . $value . "' WHERE `" . urldecode($_GET["id_field"]) . "`='" . urldecode($_GET["id_value"]) . "'", __FILE__, __LINE__);

								}
							}
							echo "OK";
						}
						else
							echo $msg;
						//var_dump($_POST);
					}
					else
					{
						if(trim($_GET["link_clicked"] != "") && trim($_GET["link_clicked"] != "null"))
							echo '<div><b>Follow link:</b> <a href="' . urldecode($_GET["link_clicked"]) . '" target="' . urldecode($_GET["link_clicked_target"]) . '">' . urldecode($_GET["link_clicked"]) . '</a><br><b>Or edit:</b><br>';
						$res_data = DBConnect::query("SELECT * FROM `" . urldecode($_GET["table"]) . ((data_description::is_lang_dep(urldecode($_GET["table"]), urldecode($_GET["field"])))?'_lang':'') . "` WHERE `" . urldecode($_GET["id_field"]) . "`='" . urldecode($_GET["id_value"]) . "'", __FILE__, __LINE__);
						$row_data = mysql_fetch_array($res_data);
						formfield::publish_dbfield(urldecode($_GET["table"]) . "." . urldecode($_GET["field"]), $row_data[urldecode($_GET["field"])], $_SESSION["language"]);
						//ophalen van eventuele pic_formats
						$res_picform = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . urldecode($_GET["table"]) . "' AND `datadesc`='22'", __FILE__, __LINE__);
						while($row_picform = mysql_fetch_array($res_picform))
						{
							$options = data_description::options_convert_to_array($row_picform["datadesc"], $row_picform["data_options"]);
							if($options["master_pic_field"] == urldecode($_GET["field"]))
							{
								formfield::publish_dbfield(urldecode($_GET["table"]) . "." . $row_picform["fieldname"], $row_data[$row_picform["fieldname"]], $_SESSION["language"]);
							}
						}
						echo '<div style="height: 14px; overflow:hidden; margin-top:4px; padding-left: 4px;"><img src="/css/back/inline_edit/delete.gif" style="cursor:pointer;" onclick="$(\'#inline_edit_editinline\').css(\'display\', \'none\'); $(\'#inline_edit_editinline\').empty();"/>
								<img src="/css/back/inline_edit/save.gif" style="cursor:pointer;" onclick="ajax_post_form(\'inline_edit_editinline\', \'/ajax.php?sessid=' . session_id() . '&inline_edit=true&type=field&table=' . $_GET["table"] . '&field=' . $_GET["field"] . '&id_field=' . $_GET["id_field"] . '&id_value=' . $_GET["id_value"] . '&fieldsave=true\', inline_edit_after_fieldsave, false);"/></div>';
						echo '<script language="javascript">$("#inline_edit_editinline").find("label").css("display", "none")</script>';
					}
					break;
			}
		}
		
		//$buttons = array met knoppen: button=[del|edit|add], type=[page|field|block], data1, data2, data3, help
		static function display_toolbox($buttons, $position, $correction_left = NULL)
		{
			echo '<div inlinetoolbox="true" class="inline_edit" pos="' . $position . '" ' . (($correction_left)?'corr_left="' . $correction_left . '"':'corr_left="0"') . '>';
			foreach($buttons as $button)
			{
				$path = "";
				switch($button["button"])
				{
					case "del": $path = "/css/back/inline_edit/delete.gif"; break;
					case "edit": $path = "/css/back/inline_edit/edit.gif"; break;
					case "add": $path = "/css/back/inline_edit/add.gif"; break;
				}
				echo '<img src="' . $path . '" button="' . $button["button"] . '" type="' . $button["type"] . '" data1="' . str_replace('"', '_dblquot_', $button["data1"]) . '" data2="' . str_replace('"', '_dblquot_', $button["data2"]) . '" data3="' . str_replace('"', '_dblquot_', $button["data3"]) . '" help="' . str_replace('"', '_dblquot_', $button["help"]) . '"/>';
				
			}
			echo '<span></span></div>';
		}
	}
?>