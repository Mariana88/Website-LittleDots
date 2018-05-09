<?php
	class dbeditor extends popup
	{
		function __construct($id, $width, $height)
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "dbeditor");
			$this->set_config("useinputfield", true);
		}
		
		//voor een post als er een databasetabel moet geselecteerd worden
		public function set_dbtablefieldname($dbtablefieldname)
		{
			$this->set_config("dbtablefieldname", $dbtablefieldname);
		}
		
		//de tabel die wordt geladen
		public function set_table($table)
		{
			$this->set_config("table", $table);
		}
		
		public function set_createnew($createnew)
		{
			$this->set_config("createnew", $createnew);
		}
		
		public function set_useinputfield($useinputfield)
		{
			$this->set_config("useinputfield", $useinputfield);
		}
		
		public function add_createfieldonnew($field)
		{
			if(is_array($this->get_config("createfieldonnew")))
			{
				$createfieldonnew = $this->get_config("createfieldonnew");
				$createfieldonnew[] = $field;
				$this->set_config("createfieldonnew", $createfieldonnew);
			}
			else
				$this->set_config("createfieldonnew", array($field));
		}
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			$panelname = "popup_" . $this->id . "_html_panel";
			$this->publish_start($in_popup, $from_ajax);
			
			//TONEN VAN DE SELECT VAN DE TABEL
			if($this->get_config("useinputfield"))
			echo '<input type="text" id="' . $this->get_config("dbtablefieldname") . '" name="' . $this->get_config("dbtablefieldname") . '" value="' . $this->get_config("table") . '" onchange="dbedit_' . $this->id . '_tablegrid_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&action=loadtable&tablename=\' + this.value)"/><br>';
			
			//DE TABEL GRID:
			echo '<div id="dbedit_' . $this->id . '_tablegrid">';
			echo '<div id="dbedit_' . $this->id . '_desc_options"></div>';
			if(trim($this->get_config("table")) != "")
				$this->showgrid($this->get_config("table"));
			echo '</div>';
			echo '<script> 
					window.dbedit_' . $this->id . '_tablegrid_panel = new Spry.Widget.HTMLPanel("dbedit_' . $this->id . '_tablegrid",{evalScripts:true});
					window.dbedit_' . $this->id . '_desc_options_panel = new Spry.Widget.HTMLPanel("dbedit_' . $this->id . '_desc_options",{evalScripts:true});
					$(function() {
						$(\'#dbedit_' . $this->id . '_desc_options\').dialog({
						autoOpen: false,
						height: 600,
						width: 780,
						show: \'fade\',
						title: \'Add or edit a data entry\',
						modal: true,
						maxHeight: 600,
						buttons: {
							"Ok": function() { 
									dbeditor_options_ok(\'dbedit_' . $this->id . '_desc_options\');
									$(this).dialog("close"); 
								}, 
								"Cancel": function() { 
									$(this).dialog("close"); 
								} 
							}
						});
					});
				</script>';
			
			$this->publish_end($in_popup, $from_ajax);
		}
		
		public function handle_ajax()
		{
			if(!login::check_login())
				return "";
				
			switch($_GET["action"])
			{
				case "loadtable":
					if(DBConnect::check_if_table_exists($_GET["tablename"]))
					{
						$this->set_config("table", $_GET["tablename"]);
						$this->showgrid($_GET["tablename"]);
					}
					else
					{
						echo 'Table "' . $_GET["tablename"] . '" does not exists in the Database. ';
						if($this->get_config("createnew"))
							echo '<span style="cursor: pointer;" onclick="dbedit_' . $this->id . '_tablegrid_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&action=createtable&tablename=' . $_GET["tablename"] . '\')">Klick here</span> to create the table';
					}
					
					break;
				case "createtable":
					if($this->get_config("createnew") && trim($_GET["tablename"]) != "")
					{
						DBConnect::query("CREATE TABLE `" . DBConnect::get_cfg("databasename") . "`.`sys_database_meta` (`id` BIGINT NOT NULL AUTO_INCREMENT, PRIMARY KEY ( `id` )) ENGINE = InnoDB", __FILE__, __LINE__);
						$autocreatefields = $this->get_config("createfieldonnew");
						if(is_array($autocreatefields))
						{
							foreach($autocreatefields as $createfield)
							{
								dbeditor::change_db_field($_GET["tablename"], "" , $createfield["fieldname"], $createfield["$fieldlabel"], $createfield["$datadesc"], $createfield["$data_options"], $createfield["data_length"], $createfield["data_standaardwaarde"], $createfield["data_help"], $createfield["land_dep"]);
							}
						}
						$this->set_config("table", $_GET["tablename"]);
						$this->showgrid($_GET["tablename"]);
					}
					else
					{
						echo 'it\'s not possible to create a table here';
					}
					break;
				case "saveplaceholder":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<?xml version="1.0" encoding="ISO-8859-1"?>
						<ajaxreturn>
							<gridid>dbedit_grid_' . $this->id . '</gridid><id>' . $_POST['id'] . '</id></ajaxreturn>';
					$sql = "UPDATE `sys_database_placeholder` SET `name`='" . addslashes($_POST["name"]) . "', `type`='" . addslashes($_POST["type"]) . "', `subtable`='" . addslashes($_POST["subtable"]) . "' WHERE `id`='" . addslashes($_POST["id"]) . "'";
					DBConnect::query($sql, __FILE__, __LINE__);
					break;
				case "savefield":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<?xml version="1.0" encoding="ISO-8859-1"?>
						<ajaxreturn>
							<gridid>dbedit_grid_' . $this->id . '</gridid>
							<oldname>' . $_POST["oldname"] . '</oldname>';
					//controle op de velden
					//als VARCHAR dan ook lengte ingevuld --> anders lengte verwijderen
					//veldnaam mag niet leeg zijn
					$errors = array();
					if(trim($_POST["fieldname"]) == "")
						$errors[] = "fieldname";
					if(count($errors) > 0)
					{
						echo '<status>NOK</status>
							<errors>';
						foreach($errors as $error)
							echo '<error>' . $error . '</error>';
						echo '</errors>';
						echo '</ajaxreturn>';
						break;
					}
					//controleren of de nieuwe veldnaam wel kan (bevat hij spaties, bestaat het al in db)
					if(trim(strtolower($_POST["fieldname"])) != trim(strtolower($_POST["oldname"])) && DBConnect::check_if_column_exists($this->get_config("table"), $_POST["fieldname"]))
					{
						echo '<status>NOK</status>
							<errors>';
						echo '<error>fieldname</error>';
						echo '</errors>';
						echo '</ajaxreturn>';
						break;
					}
					
					//dan maar saven nu
					dbeditor::change_db_field($this->get_config("table"), $_POST["oldname"] , $_POST["fieldname"], $_POST["fieldlabel"], $_POST["datadesc"], $_POST["data_options"], urldecode($_POST["data_standaardwaarde"]), urldecode($_POST["data_help"]), $_POST["data_obligated"], $_POST["lang_dep"]);
					echo '<status>OK</status>
						</ajaxreturn>';
					break;
				case "emptyrowcode";
					//first we echo the grit div id + splitter + $rowid + splitter
					$rowcount = $this->get_config("rowsshowed") + 1;
					$this->set_config("rowsshowed", $rowcount);
					echo 'dbedit_grid_' . $this->id . '##splitter##' . $this->id . '_row_' . $rowcount . '##splitter##';
					//now the html
					echo '<div style="float:left; height:25px;">
							<input id="' . $this->id . '_field_oldname_' . $rowcount . '" name="oldname" type="hidden" value=""/>
							<input id="' . $this->id . '_field_order_' . $rowcount . '" name="order" type="hidden" value="' . $rowcount . '"/>
							<input onclick="this.focus();" id="' . $this->id . '_field_fieldname_' . $rowcount . '" name="fieldname" style="width:110px;" type="text" value="newfield"/></div>
						<div style="float:left; height:25px;"><input onclick="this.focus();" id="' . $this->id . '_field_fieldlabel_' . $rowcount . '" name="fieldlabel" style="width:110px;" type="text" value=""/></div>
						<div style="float:left; height:25px;">';
					$this->display_datadesc_dropdown("", $this->id . '_field_datadesc_' . $rowcount);
					echo '</div>
						<div style="float:left; height:25px;">
							<input id="' . $this->id . '_field_options_' . $rowcount . ' type="text" name="data_options" style="width:145px;" onClick="dbeditor_open_options(\'' . $this->id . '\', this, dbedit_' . $this->id . '_desc_options_panel, \'dbedit_' . $this->id . '_desc_options\');"/>
						</div>
						<div style="float:left; height:25px;"><input onclick="this.focus();" id="' . $this->id . '_field_datastandaardwaarde_' . $rowcount . '" name="data_standaardwaarde" style="width:45px;" type="text" value=""/></div>
						<div style="float:left; height:25px;"><input onclick="this.focus();" id="' . $this->id . '_field_help_' . $rowcount . '" name="data_help" style="width:45px;" type="text" value=""/></div>
						<div style="float:left; height:25px; margin-left:10px;"><input id="' . $this->id . '_field_obligated_' . $rowcount . '" name="data_obligated" type="checkbox"/></div>
						<div style="float:left; height:25px; margin-left:10px;"><input id="' . $this->id . '_field_lang_dep_' . $rowcount . '" name="lang_dep" type="checkbox"/></div>
						<div style="float:left; width: 60px; height:25px;">
							<img style="float:left; margin-left:5px; cursor:pointer;" src="/css/back/icon/twotone/save.gif" onclick="dbeditor_sendfield(\'' . $this->id . '\', this);"/><img style="float:left; margin-left:5px; cursor:pointer;" src="/css/back/icon/twotone/trash.gif" onclick="dbeditor_deletefield(\'' . $this->id . '\', this);"/>
						</div>';
					break;
				case "deletefield":
					if(trim($_POST["oldname"]) != "" && DBConnect::check_if_column_exists($this->get_config("table"), $_POST["oldname"]))
					{
						//verwijderen uit de db
						DBConnect::query("ALTER TABLE `" . $this->get_config("table") . "` DROP `" . $_POST["oldname"] . "`", __FILE__, __LINE__);
						DBConnect::query("DELETE FROM `sys_database_meta` WHERE `tablename`='" . $this->get_config("table") . "' AND `fieldname`='" . $_POST["oldname"] . "'");
						echo 'dbedit_grid_' . $this->id . '##splitter##' . $_POST["oldname"];
					}
					else
					{
						if(trim($_POST["oldname"]) == "")
							echo 'dbedit_grid_' . $this->id . '##splitter##' . $_POST["oldname"];
						else
							echo 'NOK';
					}
					break;
				case "deleteplaceholder":
					DBConnect::query("DELETE FROM `sys_database_placeholder` WHERE `id`='" . addslashes($_POST["placeholder_id"]) . "'", __FILE__, __LINE__);
					echo 'dbedit_grid_' . $this->id . '##splitter##' . $_POST["placeholder_id"];
					break;
				case "optionform":
					//we creëren een optionform voor een bepaalde description
					$options = data_description::options_getdef($_GET["desc_id"]);
					$values_arr = data_description::options_convert_to_array($_GET["desc_id"], $_GET["options_str"]);
					
					if(count($options) == 0)
					{
						echo '<div style="text-align:center;">No options available</div>';
						break;
					}
					foreach($options as $option)
					{
						echo '<label>' . str_replace('_', ' ', $option["name"]) . '</label>';
						switch($option["DATATYPE"])
						{
							case "":
								break;
							case "YESNO": echo '<input type="checkbox" ' . (($values_arr[$option["name"]] > 0)? ' checked':'') . '><br>';
								break;
							case "TEXT": echo '<textarea>' . $values_arr[$option["name"]] . '</textarea><br>';
								break;
							default: echo '<input type="text" value="' . $values_arr[$option["name"]] . '"/><br>';
								break;
						}
					}
					break;
				case "order":
					$fieldnames = explode(";", urldecode($_POST["fieldnames"]));
					$counter = 1;
					foreach($fieldnames as $fieldname)
					{
						$chomps = explode(":", $fieldname);
						if($chomps[0] == "f")
							DBConnect::query("UPDATE `sys_database_meta` SET `order`='" . $counter . "' WHERE `tablename`='" . $this->get_config("table") . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
						elseif($chomps[0] == "p")
							DBConnect::query("UPDATE `sys_database_placeholder` SET `order`='" . $counter . "' WHERE `table`='" . $this->get_config("table") . "' AND `id`='" . $chomps[1] . "'", __FILE__, __LINE__);
						$counter++;
					}
					break;
				case "newplaceholder":
					//op zoek naar de order
					$res = DBConnect::query("SELECT `order` FROM `sys_database_meta`  WHERE `tablename`='" . $this->get_config("table") . "' ORDER BY `order` DESC LIMIT 0,1", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$order = $row["order"]; 
					$res = DBConnect::query("SELECT `order` FROM `sys_database_placeholder`  WHERE `table`='" . $this->get_config("table") . "' ORDER BY `order` DESC LIMIT 0,1", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					if($row['order'] > $order)
						$order = $row["order"];
					$order++;
					//Creëren van de data
					DBConnect::query("INSERT INTO `sys_database_placeholder`(`id`, `table`, `order`)  VALUES ('', '" . $this->get_config("table") . "', '" . $order . "')", __FILE__, __LINE__);
					$res = DBConnect::query("SELECT * FROM `sys_database_placeholder`  WHERE `table`='" . $this->get_config("table") . "' ORDER BY `id` DESC LIMIT 0,1", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<?xml version="1.0" encoding="ISO-8859-1"?>
						<ajaxreturn><table>dbedit_grid_' . $this->id . '</table><html><![CDATA[';
					$rowcount = $this->get_config("rowsshowed") + 1;
					$this->set_config("rowsshowed", $rowcount);
					$this->show_grid_row_placeholder($row, $rowcount);
					echo ']]></html></ajaxreturn>';
					break;
			}
		}
		
		private function showgrid($tablename)
		{
			$datatypes = array("VARCHAR", "TINYINT", "TEXT", "INT", "BIGINT", "ENUM");
			
			echo '<div id="dbedit_grid_' . $this->id . '">
					<div>
						<div style="float:left; width: 115px; height:20px; font-weight:bold; margin-top:3px;">&nbsp;db name</div>
						<div style="float:left; width: 115px; height:20px; font-weight:bold; margin-top:3px; ">&nbsp;label</div>
						<div style="float:left; width: 127px; height:20px; font-weight:bold; margin-top:3px; ">&nbsp;description</div>
						<div style="float:left; width: 150px; height:20px; font-weight:bold; margin-top:3px; ">&nbsp;options</div>
						<div style="float:left; width: 60px; height:20px; font-weight:bold; margin-top:3px; ">&nbsp;stand.</div>
						<div style="float:left; width: 60px; height:20px; font-weight:bold; margin-top:3px; ">&nbsp;help</div>
						<div style="float:left; width: 65px; height:20px; font-weight:bold; margin-top:3px; ">Obl. Lang.</div>
					</div>';
			$rowcount = 1;
			//eerste alle velden tonen die in sys_database_meta zitten + splitters
			$fields_in_meta = array();
			$resmeta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tablename . "' ORDER BY `order`", __FILE__, __LINE__);
			$resplaceholder = DBConnect::query("SELECT * FROM `sys_database_placeholder` WHERE `table`='" . $tablename . "' ORDER BY `order`", __FILE__, __LINE__);
			$placeholders = array();
			while($rowplaceholder=mysql_fetch_array($resplaceholder))
			{
				$placeholders[] = $rowplaceholder;
			}
			while($rowmeta=mysql_fetch_array($resmeta))
			{
				$res = DBConnect::query("SELECT * FROM information_schema.columns WHERE table_schema = '" . DBConnect::get_cfg("databasename") . "' AND (table_name = '" . $tablename . "' OR table_name = '" . $tablename . "_lang') AND column_name='" . $rowmeta["fieldname"] . "'" , __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					//eerst checken of we één of meerdere placeholders vinden met een kleinere order
					$indexen = array();
					foreach($placeholders as $index => $placeholder)
					{
						if($rowmeta["order"] > $placeholder["order"])
						{
							$this->show_grid_row_placeholder($placeholder, $rowcount);
							$rowcount++;
							$indexen[] = $index;
						}
					}
					//gevonden placeholders verwijderen
					foreach($indexen as $index)
						unset($placeholders[$index]);
					
					$this->show_grid_row($row, $rowmeta, $rowcount);
					$fields_in_meta[] = $rowmeta["fieldname"];
					$rowcount++;
				}
			}
			//zijn er nog placeholders over?
			foreach($placeholders as $index => $placeholder)
			{
				$this->show_grid_row_placeholder($placeholder, $rowcount);
				$rowcount++;
			}
			//nu alle velden tonen die niet in de meta stonden
			$res = DBConnect::get_colums($tablename);
			while($row = mysql_fetch_array($res))
			{
				if(!in_array($row["COLUMN_NAME"], $fields_in_meta))
				{
					$this->show_grid_row($row, NULL, $rowcount);
					$rowcount++;
				}
			}
			//nu alle velden tonen die niet in de meta stonden van de lang tabel
			if(DBConnect::check_if_table_exists($tablename . '_lang'))
			{
				$res = DBConnect::get_colums($tablename . '_lang');
				while($row = mysql_fetch_array($res))
				{
					if(!in_array($row["COLUMN_NAME"], $fields_in_meta))
					{
						$this->show_grid_row($row, NULL, $rowcount);
						$rowcount++;
					}
				}
			}
			$this->set_config("rowsshowed", $rowcount);
			echo '</div>';
			echo '<style>
					#dbedit_grid_' . $this->id . ' {}
					#dbedit_grid_' . $this->id . ' div {height: 26px; }
					</style>
				<script>
				$(function() {
					$( "#dbedit_grid_' . $this->id . '" ).sortable({
						   update: function(event, ui) { dbeditor_sortfields("' . $this->id . '"); }
						});
					$( "#dbedit_grid_' . $this->id . '" ).disableSelection();
				});
				</script>';

			echo '<input type="button" value="Add field" onclick="dbeditor_addemptyrow(\'' . $this->id . '\');"/>';
			echo '<input type="button" value="Add placeholder" onclick="dbeditor_addplaceholder(\'' . $this->id . '\');"/>';
		}
		
		private function show_grid_row($row, $rowmeta, $rowcount)
		{
			echo '<div rowdiv="true" id="' . $this->id . '_row_' . $rowcount . '">
					<div style="float:left; height:25px;">
						<input id="' . $this->id . '_field_oldname_' . $rowcount . '" name="oldname" type="hidden" value="' . $row["COLUMN_NAME"] . '"/>
						<input id="' . $this->id . '_field_order_' . $rowcount . '" name="order" type="hidden" value="' . $rowmeta["order"] . '"/>
						<input onclick="this.focus();" id="' . $this->id . '_field_fieldname_' . $rowcount . '" name="fieldname" style="width:110px;" type="text" value="' . $row["COLUMN_NAME"] . '"/></div>
					<div style="float:left; height:25px;"><input onclick="this.focus();" id="' . $this->id . '_field_fieldlabel_' . $rowcount . '" name="fieldlabel" style="width:110px;" type="text" value="' . $rowmeta["fieldlabel"] . '"/></div>
					<div style="float:left; height:25px;">';
			$this->display_datadesc_dropdown($rowmeta["datadesc"], $this->id . '_field_datadesc_' . $rowcount);
			echo '</div>
					<div style="float:left; height:25px;">
						<input id="' . $this->id . '_field_options_' . $rowcount . '" name="data_options" style="width:145px;" type="text" value="' . $rowmeta["data_options"] . '" onClick="dbeditor_open_options(\'' . $this->id . '\', this, dbedit_' . $this->id . '_desc_options_panel, \'dbedit_' . $this->id . '_desc_options\');"/>
					</div>
					<div style="float:left; height:25px;"><input onclick="this.focus();" id="' . $this->id . '_field_datastandaardwaarde_' . $rowcount . '" name="data_standaardwaarde" style="width:45px;" type="text" value="' . ((trim($rowmeta["data_standaardwaarde"])=="")?$row["COLUMN_DEFAULT"]:$rowmeta["data_standaardwaarde"]) . '"/></div>
					<div style="float:left; height:25px;"><input onclick="this.focus();" id="' . $this->id . '_field_help_' . $rowcount . '" name="data_help" style="width:45px;" type="text" value="' . $rowmeta["data_help"] . '"/></div>
					<div style="float:left; height:25px; margin-left:10px;"><input id="' . $this->id . '_field_obligated_' . $rowcount . '" name="data_obligated" type="checkbox"' . (($rowmeta["obligated"]>0)?' checked':'') . '/></div>
					<div style="float:left; height:25px; margin-left:10px;"><input id="' . $this->id . '_field_lang_dep_' . $rowcount . '" name="lang_dep" type="checkbox"' . (($rowmeta["lang_dep"]>0)?' checked':'') . '/></div>
					<div style="float:left; width: 60px; height:25px;">
						<img style="float:left; margin-left:5px; cursor:pointer;" src="/css/back/icon/twotone/save.gif" onclick="dbeditor_sendfield(\'' . $this->id . '\', this);"/><img style="float:left; margin-left:5px; cursor:pointer;" src="/css/back/icon/twotone/trash.gif" onclick="dbeditor_deletefield(\'' . $this->id . '\', this);"/>
					</div>
				</div>';
		}
		
		private function show_grid_row_placeholder($row, $rowcount)
		{
			echo '<div rowdiv="true" id="' . $this->id . '_row_' . $rowcount . '" type="placeholder" style="background-color:#CCCCCC;">
					<div style="float:left; height:25px; line-height:25px; font-weight:bold;">PH: </div>
					<div style="float:left; height:25px;">
						<input id="' . $this->id . '_placeholder_id_' . $rowcount . '" name="id" type="hidden" value="' . $row["id"] . '"/>
						<input id="' . $this->id . '_placeholder_order_' . $rowcount . '" name="order" type="hidden" value="' . $row["order"] . '"/>
						&nbsp;&nbsp;Name:&nbsp;
						<input onclick="this.focus();" id="' . $this->id . '_placeholder_name_' . $rowcount . '" name="name" style="width:110px;" type="text" value="' . $row["name"] . '"/></div>
					<div style="float:left; height:25px;">&nbsp;&nbsp;Type:&nbsp;
						<select onclick="this.focus();" id="' . $this->id . '_placeholder_type_' . $rowcount . '" name="type" style="width:110px;" value="' . $row["fieldlabel"] . '">
							<option value="splitter" ' . (($row["type"]=="splitter")?'selected="selected"':'') . '>splitter</option>
							<option value="subtable" ' . (($row["type"]=="subtable")?'selected="selected"':'') . '>subtable</option>
							<option value="code" ' . (($row["type"]=="code")?'selected="selected"':'') . '>code</option>
						</select>
					</div>
					<div style="float:left; height:25px;">&nbsp;&nbsp;Subtable:&nbsp;<input onclick="this.focus();" id="' . $this->id . '_placeholder_subtable_' . $rowcount . '" name="subtable" style="width:45px;" type="text" value="' . $row["subtable"] . '"/></div>
					<div style="float:left; width: 60px; height:25px;">
						<img style="float:left; margin-left:5px; cursor:pointer;" src="/css/back/icon/twotone/save.gif" onclick="dbeditor_sendplaceholder(\'' . $this->id . '\', this);"/><img style="float:left; margin-left:5px; cursor:pointer;" src="/css/back/icon/twotone/trash.gif" onclick="dbeditor_deleteplaceholder(\'' . $this->id . '\', this);"/>
					</div>
				</div>';
		}
		
		static function change_db_field($tablename, $fieldnameold , $fieldname, $fieldlabel, $datadesc, $data_options, $data_standaardwaarde, $data_help, $obligated, $lang_dep)
		{
			//we checken of de tabel lang dependent is
			$tablenameiflang = $tablename;
			$res = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $tablename . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				if($row["lang_dep"] > 0)
				{
					if($lang_dep)
					{
						//checken of het veld bestaat in originele table, zoja, droppen
						if(DBConnect::check_if_column_exists($tablename, $fieldnameold))
							DBConnect::query("ALTER TABLE `" . $tablename . "` DROP `" . $fieldnameold . "`", __FILE__, __LINE__);
					}
					else
					{
						//checken of het veld bestaat in lang table, zoja, droppen
						if(DBConnect::check_if_column_exists($tablename . "_lang", $fieldnameold))
							DBConnect::query("ALTER TABLE `" . $tablename . "_lang` DROP `" . $fieldnameold . "`", __FILE__, __LINE__);
					}
					if($lang_dep)
						$tablenameiflang = $tablename . '_lang';
				}
			}
			//eerst de database zelf aanpassen
			$sql = "ALTER TABLE `" . $tablenameiflang . "` ";
			//checken of het veld al bestaat
			if($fieldnameold != "" && DBConnect::check_if_column_exists($tablenameiflang, $fieldnameold))
				$sql .= "CHANGE `" . $fieldnameold . "` `" . $fieldname . "` ";
			else
				$sql .= "ADD `" . $fieldname . "` ";
				
			//we zoeken naar het data type
			$res = DBConnect::query("SELECT * FROM sys_datadescriptions WHERE `id`='" . $datadesc . "'", __FILE__, __LINE__);
			$row_desc = mysql_fetch_array($res);
			
			if($row_desc["datatype"] == "VARCHAR")
			{
				//bij VARCHAR kan je zelf de lengte instellen
				if($row_desc["name"] == "VARCHAR")
				{
					$option_arr = data_description::options_convert_to_array($datadesc, $data_options);
					if(trim($option_arr["length"]) != "")
						$sql .= "VARCHAR(" . $option_arr["length"] . ")";
					else
						$sql .= "VARCHAR(255)";
				}
				else
					$sql .= "VARCHAR(255)";
			}
			elseif($row_desc["name"] == "HIDDEN ID")
			{
				//toevoegen van autoincrement
				$sql .= $row_desc["datatype"] . " NOT NULL AUTO_INCREMENT";
			}
			else
				$sql .= $row_desc["datatype"];
			$sql .= " NOT NULL ";
			if(trim($data_standaardwaarde) != "")
				$sql .= " DEFAULT '" . $data_standaardwaarde . "'";
			DBConnect::query($sql, __FILE__, __LINE__);
			
			//sys_database_meta aanpassen
			//checken of het veld al bestaat in de database
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $tablename . "' AND `fieldname`='" . $fieldnameold . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				//UPDATE
				DBConnect::query("UPDATE `sys_database_meta` SET `fieldname`='" . $fieldname . "',
																`fieldlabel`='" . addslashes($fieldlabel) . "',
																`datadesc`='" . $datadesc . "',
																`data_options`='" . $data_options . "',
																`data_standaardwaarde`='" . $data_standaardwaarde . "',
																`data_help`='" . addslashes($data_help) . "',
																`obligated`='" . $obligated . "',
																`lang_dep`='" . $lang_dep . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
			}
			else
			{
				//INSERT
				DBConnect::query("INSERT INTO `sys_database_meta` (`tablename`, `fieldname`, `fieldlabel`, `datadesc`, `data_options`, `data_standaardwaarde`, `data_help`, `obligated`, `lang_dep`)
																VALUES ('" . $tablename . "', '" . $fieldname . "', '" . addslashes($fieldlabel) . "', '" . $datadesc . "', '" . $data_options . "', '" . addslashes($data_standaardwaarde) . "', '" . addslashes($data_help) . "', '" . $obligated . "', '" . $lang_dep . "')", __FILE__, __LINE__);
			}
		}
		
		static function update_table_after_lang_change($table)
		{
			//we checken of het lang dependent is of niet
			$res = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $table . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				if($row["lang_dep"] > 0)
				{
					//we checken of de lang table bestaat
					if(!DBConnect::check_if_table_exists($table . '_lang'))
						DBConnect::query(" CREATE TABLE `" . DBConnect::get_cfg("databasename") . "`.`" . $table . "_lang` (
											`lang_id` BIGINT NOT NULL AUTO_INCREMENT ,
											`lang_parent_id` BIGINT NOT NULL ,
											`lang` VARCHAR( 16 ) NOT NULL ,
											PRIMARY KEY ( `lang_id` )
											) ENGINE = InnoDB ", __FILE__, __LINE__);
					
				}
				else
				{
					//we checken of de lang table bestaat zo ja, droppen
					if(DBConnect::check_if_table_exists($table . '_lang'))
						DBConnect::query("DROP TABLE  `" . $table . "_lang`", __FILE__, __LINE__);
				}
			}
			//nu elk veld van de tabel ophalen en opnieuw opslaan
			$res = DBConnect::query("SELECT * FROM sys_database_meta WHERE `tablename`='" . $table . "'", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
				dbeditor::change_db_field($table, $row["fieldname"] , $row["fieldname"], $row["fieldlabel"], $row["datadesc"], $row["data_options"], $row["data_standaardwaarde"], $row["data_help"], $row["obligated"], $row["lang_dep"]);
		}
		
		private function display_datadesc_dropdown($value, $id)
		{
			$res = DBConnect::query("SELECT `id`, `name` FROM `sys_datadescriptions`", __FILE__, __LINE__);
			echo '<select id="' . $id . '" name="datadesc" style="width:122px;" onChange="dbeditor_typechange(this);">';
			while($row = mysql_fetch_array($res))
			{
				echo '<option value="' . $row["id"] . '"' . (($value==$row["id"])?'selected="selected"':'') . '>' . $row["name"] . '</option>';
			}
			echo '</select>';
		}
	}
?>