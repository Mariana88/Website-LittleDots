<?php
	//Menu Component is a vertical or horizontal menu.
	class fileoptions
	{
		static function ajax()
		{
			switch($_GET["action"])
			{
				case "load":
					//opzoeken van de file info
					//als deze niet bestaat dan maken we die aan als het path tenminste is meegegeven
					$res = NULL;
					$found = false;
					if(isset($_GET["file_id"]))
					{
						$res = DBConnect::query("SELECT * FROM `site_files` WHERE `id`='" . addslashes($_GET["file_id"]) . "'", __FILE__, __LINE__);
						if(mysql_num_rows($res) > 0)
							$found = true;
					}
					elseif(isset($_GET["file_path"]))
					{
						$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_GET["file_path"]);
						$path = str_replace('//', '/', $path);
						$res = DBConnect::query("SELECT * FROM `site_files` WHERE `path`='" . addslashes($path) . "'", __FILE__, __LINE__);
						if(mysql_num_rows($res) <= 0)
						{
							DBConnect::query("INSERT INTO `site_files` (`id`, `path`) VALUES ('', '" . addslashes($path) . "')", __FILE__, __LINE__);
							$res = DBConnect::query("SELECT * FROM `site_files` WHERE `path`='" . addslashes($path) . "'", __FILE__, __LINE__);
							$found = true;
						}
						else
							$found = true;
					}
					
					if(!$found)
					{
						echo '<div>The file info could not be found</div>';
						break;
					}
					$row_file = mysql_fetch_array($res);
					
					//DOWNLAD
					$path_parts = pathinfo(stripslashes($row_file["path"]));
					echo '<div><span style="font-weight:bold;font-size:13px;cursor:pointer;" onclick="cms2_open_file(\'' . ((substr($row_file["path"], 0, 1) == '/')?stripslashes(substr($row_file["path"],1)):stripslashes($row_file["path"])) . '\', \'' . $path_parts['extension'] . '\', null);">' . str_replace('userfiles', 'root', stripslashes($row_file["path"])) . '</span><br>&nbsp;</div>';
					//FORM
					echo '<div id="file_info_form">';
					formfield::publish_dbfield("site_files.id", $row_file["id"]);
					//echo '<label>File Path:</label><span>' . str_replace('userfiles', 'root', stripslashes($row_file["path"])) . '</span><br>';
					formfield::publish_dbfield("site_files.copyright", $row_file["copyright"]);
					formfield::publish_dbfield("site_files.description", $row_file["description"]);
					echo '</div>';
					
					//FORMATS
					$res_formats = DBConnect::query("SELECT * FROM `site_files_derived` WHERE `file_id`='" . $row_file["id"] . "' AND `type`='thumb'", __FILE__, __LINE__);
					if(mysql_num_rows($res_formats) > 0)
					{
						echo '<div class="splitter"><span>Picture Formats:</span></div>';
						while($row_format = mysql_fetch_array($res_formats))
						{
							//ophalen van de meta info
							$res_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `id`='" . $row_format["thumb_meta"] . "'", __FILE__, __LINE__);
							$row_meta = mysql_fetch_array($res_meta);
							$options = data_description::options_convert_to_array($row_meta["datadesc"], $row_meta["data_options"]);
							//echo van de pic
							echo '<b>' . stripslashes($row_format["name"]) . '</b>&nbsp;&nbsp;
								<a href="javascript:cms2_open_pic_edit_new(\'' . $row_format["id"] . '\', \'fileinfo_img_' . $row_format["id"] . '\')">Edit</a><br><br>
								<img src="' . stripslashes($row_format["path"]) . '?rnd=' . time() . '" id="fileinfo_img_' . $row_format["id"] . '"/><br><br>';
						}
					}
					break;
				case "save":
					DBConnect::query("UPDATE `site_files` SET `copyright`='" . addslashes($_POST["site_files.copyright"]) . "', `description`='" . addslashes($_POST["site_files.description"]) . "' WHERE `id`='" . addslashes($_POST["site_files.id"]) . "'", __FILE__, __LINE__);
					break;
			}
		}
	}
?>