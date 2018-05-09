<?php
	debug::message($_GET["action"]);
	//check for rights
	if(!login::right("backpage_config_datadesc", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	
	switch($_GET["action"])
	{
		case "loadtable":
			$_REQUEST["output_html"] = true;
			echo '<div class="contentheader">
						<div class="divleft">Edit Table: ' . $_GET["table"] . '</div>
					</div>';
			echo '<div class="TabbedPanels" id="tp_datadesc">
					<ul class="TabbedPanelsTabGroup">
						<li class="TabbedPanelsTab" tabindex="1">Fields</li>
						<li class="TabbedPanelsTab" tabindex="2">Table / Grid Meta</li>
						<li class="TabbedPanelsTab" tabindex="3">Subdata</li>
						<li class="TabbedPanelsTab" tabindex="1">Rename Table</li>
					</ul>
					<div class="TabbedPanelsContentGroup">
						<div class="TabbedPanelsContent">';
			
			//FIELDS
			$dbedit = new dbeditor("datadesc_tableedit", 500, 500);
			$dbedit->set_createnew(false);
			$dbedit->set_table($_GET["table"]);
			$dbedit->set_useinputfield(false);
			$dbedit->publish(false);
			
			echo '</div>
					<div class="TabbedPanelsContent">';
			//TABLE META
			$res = DBConnect::query("SELECT * FROM sys_database_table WHERE `table`='" . addslashes($_GET["table"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			if(!$row)
				$row = array("table" => $_GET["table"]);
			echo	'<div class="savebutton" style="float:right;" id="button_dbtablemeta" onclick="window.sys_database_table_form.savebutton = $(this); window.sys_database_table_form.aftersave_success = \'dbeditor_aftertablesave\'; window.sys_database_table_form.aftersave_data = \'' . $_GET["table"] . '\'; window.sys_database_table_form.post();">Save</div><div style="clear:both"></div>';
			form::show_autoform_new("sys_database_table", $row);
				
			echo '</div>
						<div class="TabbedPanelsContent">';
						
			//SUB TABLES
			$de = new dataeditor("datadesc_subtable", 500, 500, "sys_database_subtable");
			$de->set_parent('sys_database_table', 'table_parent', $_GET["table"]);
			$de->publish(false);
			echo '</div>
						<div class="TabbedPanelsContent">';
			
			//TABLE RENAME
			echo '<div id="datadesc_rename_table_form">
					<input type="hidden" id="datadesc_rename_table_old" name="datadesc_rename_table_old" value="' . $_GET["table"]  . '"/>
					<label>New table name</label><input type="text" id="datadesc_rename_table" name="datadesc_rename_table" value="' . $_GET["table"]  . '"/>
					<br><div class="savebutton" style="float:right;" onclick="ajax_post_form(\'datadesc_rename_table_form\', \'/ajax.php?sessid=' . session_id() . '&page=datadesc&action=renametable\', dbtables_on_rename, false);">Save</div>
				</div>';
			echo '</div></div>
				</div>';
			echo '<script language="javascript">
					var tp_datadesc = new Spry.Widget.TabbedPanels("tp_datadesc", { defaultTab: 0 });
					//script dat velden hide of showt als het type dg veranderd
					$(\'select[name="sys_database_table\\.gridview"]\').change(function(){
						switch($(this).val())
						{
							case "list":
								$(\'[name="sys_database_table\\.gridpicture_html"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpicture_html_label"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpicture_placeholderstyle"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpicture_placeholderstyle_label"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_field"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_field_label"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_folder"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_folder_label"]\').css("display", "none");
							break;
							case "picture":
								$(\'[name="sys_database_table\\.gridpicture_html"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpicture_html_label"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpicture_placeholderstyle"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpicture_placeholderstyle_label"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpiccol_field"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_field_label"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_folder"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_folder_label"]\').css("display", "none");
							break;
							case "piccollection":
								$(\'[name="sys_database_table\\.gridpicture_html"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpicture_html_label"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpicture_placeholderstyle"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpicture_placeholderstyle_label"]\').css("display", "none");
								$(\'[name="sys_database_table\\.gridpiccol_field"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpiccol_field_label"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpiccol_folder"]\').css("display", "block");
								$(\'[name="sys_database_table\\.gridpiccol_folder_label"]\').css("display", "block");
							break;
						}
					});
					$(\'select[name="sys_database_table\\.gridview"]\').change();
				</script>';
			break;
		case "tablemeta":
			//var_dump($_POST);
			/*
			$errors = data_description::validate_post_db();
			
			if(count($errors) == 0)
			{
				//we save the data
				DBConnect::query(data_description::create_sql_from_post("sys_database_table", "id"), __FILE__, __LINE__);
				//we get the new id if there is one
				if(trim($_POST["sys_database_table.id"]) == "")
					data_description::output_save_xml($errors, "sys_database_table.id", DBConnect::get_last_inserted("sys_database_table", "id"));
				else
					data_description::output_save_xml($errors, "sys_database_table.id");*/
				
				dbeditor::update_table_after_lang_change($_GET["table"]);
			/*}
			else
			{
				data_description::output_save_xml($errors, "sys_database_table.id");
			}
			*/
			break;
		case "renametable":
			//var_dump($_POST);
			if(trim($_POST["datadesc_rename_table"]) == trim($_POST["datadesc_rename_table_old"]))
			{
				echo "NO";
				break;
			}
			if(trim($_POST["datadesc_rename_table"]) == "" || DBConnect::check_if_table_exists($_POST["datadesc_rename_table"]))
			{
				echo "NOK";
				break;
			}
			DBConnect::query("RENAME TABLE `" . $_POST["datadesc_rename_table_old"] . "` TO `" . $_POST["datadesc_rename_table"] . "`", __FILE__, __LINE__);
			//ook de meta aanpassen:
			DBConnect::query("UPDATE sys_database_meta SET `tablename`='" . $_POST["datadesc_rename_table"] . "' WHERE `tablename`='" . $_POST["datadesc_rename_table_old"] . "'", __FILE__, __LINE__);
			DBConnect::query("UPDATE sys_database_table SET `table`='" . $_POST["datadesc_rename_table"] . "' WHERE `table`='" . $_POST["datadesc_rename_table_old"] . "'", __FILE__, __LINE__);
			DBConnect::query("UPDATE sys_database_subtable SET `table_parent`='" . $_POST["datadesc_rename_table"] . "' WHERE `table_parent`='" . $_POST["datadesc_rename_table_old"] . "'", __FILE__, __LINE__);
			DBConnect::query("UPDATE sys_database_subtable SET `table_sub`='" . $_POST["datadesc_rename_table"] . "' WHERE `table_sub`='" . $_POST["datadesc_rename_table_old"] . "'", __FILE__, __LINE__);
			echo $_POST["datadesc_rename_table_old"] . '##splitter##' . $_POST["datadesc_rename_table"];
			break;
		case "newtable":
			if(isset($_POST["newtable.name"]))
			{
				if(trim($_POST["newtable.name"]) == "" || DBConnect::check_if_table_exists($_POST["newtable.name"]))
				{
					echo "NOK";
				}
				else
				{
					//we creëren de tabel
					//if($_POST["newtable.pageidfield"]>=1)
						DBConnect::query("CREATE TABLE `" . DBConnect::get_cfg("databasename") . "`.`" . $_POST["newtable.name"] . "` (`id` BIGINT NOT NULL AUTO_INCREMENT, PRIMARY KEY ( `id` )) ENGINE = InnoDB", __FILE__, __LINE__);
					//else
						//DBConnect::query("CREATE TABLE `cms2`.`" . $_POST["newtable.name"] . "` ENGINE = InnoDB", __FILE__, __LINE__);
						
					
					if($_POST["newtable.pageidfield"]>=1)
						dbeditor::change_db_field($_POST["newtable.name"], "" , "page_id", "Page id", "", "BIGINT", "", "", false);
						
					if($_POST["newtable.languagefield"]>=1)
						dbeditor::change_db_field($_POST["newtable.name"], "" , "lang", "Language", "", "VARCHAR", 32, "", false);
				
					echo $_POST["newtable.name"];
				}
				break;
			}
			$_REQUEST["output_html"] = true;
			echo '<div class="contentheader">
						<div class="divleft">Create New Table ' . $_GET["table"] . '</div>
					</div>
					<div class="contentcontent">';
			//we choën een formke
			echo '<div id="datadesc_newtableform">
					<label>Name:</label><input name="newtable.name" id="newtable.name" type="textfield"/><br>
					<label>Page id field</label><input type="checkbox" name="newtable.pageidfield"/><br>
					<label>Language field</label><input type="checkbox" name="newtable.languagefield"/><br>
					<div class="savebutton" onClick="ajax_post_form(\'datadesc_newtableform\', \'/ajax.php?sessid=' . session_id() . '&page=datadesc&action=newtable\', dbtables_on_save)">Create</div>
				</div>';
			echo '</div>';
			break;
		case "table_del":
			$_REQUEST["output_html"] = true;
			echo '<div class="contentheader">
						<div class="divleft">Delete Table: . ' . $_GET["table"] . '</div>
					</div>
					<div class="contentcontent" style="text-align:center;"><br><br><br>';
			if(isset($_GET["delbev"]))
			{
				DBConnect::query("DELETE FROM `sys_database_meta` WHERE `tablename`='" . $_GET["table"] . "'", __FILE__, __LINE__);
				DBConnect::query("DELETE FROM `sys_database_table` WHERE `table`='" . $_GET["table"] . "'", __FILE__, __LINE__);
				DBConnect::query("DELETE FROM `sys_database_subtable` WHERE `table_parent`='" . $_GET["table"] . "'", __FILE__, __LINE__);
				DBConnect::query("DELETE FROM `sys_database_subtable` WHERE `table_sub`='" . $_GET["table"] . "'", __FILE__, __LINE__);
				DBConnect::query("DROP TABLE `" . $_GET["table"] . "`", __FILE__, __LINE__);
				if(DBConnect::check_if_table_exists( $_GET["table"] . "_lang"))
					DBConnect::query("DROP TABLE `" . $_GET["table"] . "_lang`", __FILE__, __LINE__);
				//DBConnect::query("DROP TABLE `" . $_GET["table"] . "`", __FILE__, __LINE__);
				echo 'The table removed!';
				echo '<script>
						datadesc_removefromlist_table(\'' . $_GET["table"] . '\');
					</script>';
			}
			else
			{
				echo 'Are you sure you want to remove this table?<br><br><div  class="savebutton" onclick="datadesc_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=datadesc&action=table_del&table=' . $_GET["table"] . '&delbev=1\');">I\'m sure</div>';
			}
				
			echo '<br><br><br></div>';
			break;
		
	}
?>