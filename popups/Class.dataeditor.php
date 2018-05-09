<?php
	require_once('aidclasses/data/Class.Files.php');
	require_once('aidclasses/Class.Pictures.php');
	require_once('aidclasses/data/Class.datasource.php');
	
	class dataeditor extends popup
	{
		function __construct($id, $width, $height, $table = "")
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "dataeditor");
			if($table != "")
			{
				$this->set_config("table", $table);
				//laden van de properties
				$res = DBConnect::query("SELECT * FROM sys_database_table WHERE `table`='" . addslashes($table) . "'", __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					$this->set_config("lang_dep", (($row["lang_dep"]>0)?true:false));
					$this->set_config("view", $row["gridview"]);
					$this->set_config("list_fields", $row["gridlist_fields"]);
					$this->set_config("picture_html", $row["gridpicture_html"]);
					$this->set_config("picture_placeholder_style", $row["gridpicture_placeholderstyle"]);
					$this->set_config("sort_field", $row["grid_sort_field"]);
					$this->set_config("sort_order", $row["grid_sort_order"]);
					$this->set_config("order", $row["grid_order"]);
					$this->set_config("order_field", $row["grid_order_field"]);
					$this->set_config("order_newplace", $row["grid_order_newplace"]);
					$this->set_config("paging", $row["grid_paging"]);
					$this->set_config("perpage", $row["grid_perpage"]);
					$this->set_config("data_name", $row["table_name"]);
					$this->set_config("data_name_plural", $row["table_name_plural"]);
					$this->set_config("piccol_pic_field", $row["gridpiccol_field"]);
					$this->set_config("show_icon_bar", true);
					if($row["form_height"] > 0)
						$this->set_config("form_height", $row["form_height"]);
					else
						$this->set_config("form_height", 500);
					if($row["data_right"] > 0)
						$this->set_config("data_right", $row["data_right"]);
					else
						$this->set_config("data_right", 0);
				}
			}
			if(trim($this->get_config("perpage")) == "")
				$this->set_config("perpage", 30);
			if(trim($this->get_config("current_lang")) == "")
				$this->set_config("current_lang", mainconfig::$standardlanguage);
		}
		
		public function set_table($table){ $this->set_config("table", $table);}
		
		public function set_view($view)	{ $this->set_config("view", $view);}
		
		public function set_list_fields($list_fields)	{ $this->set_config("list_fields", $list_fields);}
		
		public function set_picture_html($picture_html)	{ $this->set_config("picture_html", $picture_html);}
		public function set_picture_placeholder_style($picture_placeholder_style)	{ $this->set_config("picture_placeholder_style", $picture_placeholder_style);}
		
		public function set_sort_field($fieldname, $asc)
		{
			$this->set_config("sort_field", $fieldname);
			$this->set_config("sort_order", $asc);
		}
		
		public function set_ordering($order, $order_field, $order_newplace)
		{
			$this->set_config("order", $order);
			$this->set_config("order_field", $order_field);
			$this->set_config("order_newplace", $order_newplace);
		}
		
		public function set_paging($paging)	{ $this->set_config("paging", $paging);}
		
		public function set_perpage($perpage){$this->set_config("perpage", $perpage);}
		
		public function set_parent($table, $id_field, $id_value, $new_per_lang = false)
		{
			$this->set_config("parent_table", $table);
			$this->set_config("parent_id_field", $id_field);
			$this->set_config("parent_id_value", $id_value);
			$this->set_config("parent_new_per_lang", $new_per_lang);
		}
		
		public function set_current_lang($current_lang){$this->set_config("current_lang", $current_lang);}
		
		public function set_piccol_field($piccol_pic_field){$this->set_config("piccol_pic_field", $piccol_pic_field);}
		
		public function set_title($title){$this->set_config("title", $title);}
		
		public function set_show_icon_bar($show_icon_bar){$this->set_config("show_icon_bar", $show_icon_bar);}
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			//$panelname = "popup_" . $this->id . "_html_panel";
			
			
			$autosortfield = $this->get_config("order_field");
			//WE DISPLAY THE DATAGRID
			$ds = new datasource();
			$ds->type = "DATABASE";
			$ds->db_table = $this->get_config("table");
			$ds->db_lang_dep = $this->get_config("lang_dep");
			$ds->db_lang_current = $this->get_config("current_lang");
			if($this->get_config("sort_field") != null)
				$ds->sort_field = $this->get_config("sort_field");
			if($this->get_config("sort_order") != null)
				$ds->sort_order = $this->get_config("sort_order");
			if(trim($this->get_config("order_field")) != "")
				$ds->sort_field = $this->get_config("order_field");
			
			if(trim($this->get_config("parent_id_field")) != "")
			{
				$ds->db_extra_where = " `" . $this->get_config("parent_id_field") . "`='" . addslashes($this->get_config("parent_id_value")) . "' ";
			}
			
			if($this->get_config("view") == "editable")
			{
				$this->publish_editable($ds);
				return;	
			}
			
			$this->publish_start($in_popup, $from_ajax);
			
			$dg = new datagridnew();
			$dg->checkbox = false;
			$dg->id = "dg_de_" . $this->id;
			$dg->datasource = $ds;
			$dg->id_field = "id";
			$dg->data_name = $this->get_config("data_name");
			$dg->data_name_plural = $this->get_config("data_name_plural");
			if($this->get_config("view") == "picture")
			{
				$dg->picture_view = true;
				$dg->picture_html = $this->get_config("picture_html");
				$dg->picture_placeholder_style = $this->get_config("picture_placeholder_style");
			}
			if(trim($this->get_config("order_field")) != "")
			{
				$dg->sort_field = $this->get_config("order_field");
				$dg->order_field = $this->get_config("order_field");
			}
			if(trim($this->get_config("title")) != "")
			{
				$dg->title = $this->get_config("title");
				$dg->show_title_bar = true;
			}
			else
				$dg->show_title_bar = false;
				
			if($this->get_config("show_icon_bar") == true)
				$dg->show_icon_bar = true;
			else
				$dg->show_icon_bar = false;

			$dg->rowdblclick = 'dataeditor_show_input_dialog(\'' . $this->id . '\', \'Edit ' . $this->get_config("data_name") . '\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=\' + dg_dg_de_' . $this->id . '.selected_id, \'' . $this->get_config("table") . '_form\', ' . $this->get_config("form_height") . ');';
			$dg->paging = ($this->get_config("paging") > 0);
			$dg->perpage = $this->get_config("perpage");
			//is er een img field gespecificeert dan adden we door pics toe te voegen
			if(trim($this->get_config("piccol_pic_field")) != "")
				$dg->addicon("new", 
							"/css/back/icon/twotone/plus.gif", 
							"/css/back/icon/twotone/gray/plus.gif", 
							'somewindow = window.open(\'http://' . $_SERVER['HTTP_HOST'] . '/browser.php?br_extentions=jpg_jpeg_gif_png&addbutton=yes\',\'\',\'width=1014,height=516,scrollbars=no,toolbar=no,location=no,resizable=no,status=no\'); somewindow.browser_piccol=\'' . $this->id . '\'; ',
							false, false, false);
			else
				$dg->addicon("new", 
							"/css/back/icon/twotone/plus.gif", 
							"/css/back/icon/twotone/gray/plus.gif", 
							'dataeditor_show_input_dialog(\'' . $this->id . '\', \'Add a new ' . $this->get_config("data_name") . '\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=new\', \'' . $this->get_config("table") . '_form\', ' . $this->get_config("form_height") . ');',
							false, false, false);
			$dg->addicon("edit", 
						"/css/back/icon/twotone/edit.gif", 
						"/css/back/icon/twotone/gray/edit.gif", 
						'dataeditor_show_input_dialog(\'' . $this->id . '\', \'Edit ' . $this->get_config("data_name") . '\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=\' + dg_dg_de_' . $this->id . '.selected_id, \'' . $this->get_config("table") . '_form\', ' . $this->get_config("form_height") . ');', 
						true, true, true);
			$dg->addicon("delete", 
						"/css/back/icon/twotone/trash.gif", 
						"/css/back/icon/twotone/gray/trash.gif", 
						 'cms2_show_question_message(\'Are you sure you want to delete the selected entries?\', \'Delete?\', function(){dataeditor_delete_accept(dg_dg_de_' . $this->id . '.selected_ids, \'' . $this->id . '\', \'dg_de_' . $this->id . '\', dg_dg_de_' . $this->id . '_html_panel);}, function(){$(this).dialog(\'close\');});', 
						true, true, true);
			if($this->get_config("data_right") > 0)
			{
				$resright = DBConnect::query("SELECT * FROM sys_rightrules WHERE `id`='" . $this->get_config("data_right") . "'", __FILE__, __LINE__);
				if($rowright = mysql_fetch_array($resright))
					$dg->addicon("Rights", 
								"/css/back/icon/twotone/shield.gif", 
								"/css/back/icon/twotone/gray/shield.gif", 
								 'cms2_show_right_form(\'Rights\', \'' . $rowright["name"] . '\', dg_dg_de_' . $this->id . '.selected_id);', 
								true, true, true);
			}
			$fields = array();
			if(trim($this->get_config("list_fields")) != "")
				$fields = explode(";", $this->get_config("list_fields"));
			else
			{
				$fields_in_meta = array();
				$resmeta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->get_config("table") . "' ORDER BY `ORDER`", __FILE__, __LINE__);
				while($rowmeta=mysql_fetch_array($resmeta))
				{
					$fields[] = $rowmeta["fieldname"];
					$fields_in_meta[] = $rowmeta["fieldname"];
				}
				
				//alle velden tonen die niet in de meta stonden
				$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $this->get_config("table") . "`", __FILE__, __LINE__);
				while($row_field = mysql_fetch_array($result_fields))
				{
					if(!in_array($row_field["Field"], $fields_in_meta))
						$fields[] = $row_field["Field"];
				}
				//nu alle velden tonen die niet in de meta stonden van de lang tabel
				if(DBConnect::check_if_table_exists($this->get_config("table") . '_lang') && $this->get_config("lang_dep"))
				{
					$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $this->get_config("table") . "_lang`", __FILE__, __LINE__);
					while($row_field = mysql_fetch_array($result_fields))
					{
						if(!in_array($row_field["Field"], $fields_in_meta))
							$fields[] = $row_field["Field"];
					}
				}
			}
				
			foreach($fields as $field)
			{
				//we get the label name
				$label = $field;
				$resmeta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->get_config("table") . "' AND `fieldname`='" . $field . "'", __FILE__, __LINE__);
				if($rowmeta = mysql_fetch_array($resmeta))
					$label = $rowmeta["fieldlabel"];
				$dg->addfield($field, $label, true, true, true, 0, false, $this->get_config("table") . "." . $field);
			}
				
			$dg->publish(false);
			
			$this->publish_end($in_popup, $from_ajax);
		}
		
		public function handle_ajax()
		{
			//for the browser we have to be logged in in the admin section
			if(!login::check_login())
				return "";
			if(isset($_GET["showform"]))
			{
				switch($_GET["showform"])
				{
					case "new": 
						$data = array();
						//WE HALEN DE STANDAARD WAARDEN OP
						$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $this->get_config("table") . "`", __FILE__, __LINE__);
						while($row_field = mysql_fetch_array($result_fields))
						{
							$res_finfo = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . addslashes($this->get_config("table")) . "' AND `fieldname`='" . $row_field["Field"] . "'", __FILE__, __LINE__);
							if($row_finfo = mysql_fetch_array($res_finfo))
							{
								if(trim($row_finfo["data_standaardwaarde"]) != "")
								$data[$row_field["Field"]] = stripslashes($row_finfo["data_standaardwaarde"]);
							}
						}
						if(trim($this->get_config("parent_id_field")) != "")
							$data[$this->get_config("parent_id_field")] = $this->get_config("parent_id_value");
						if($this->get_config("lang_dep"))
							$data["lang"] = $this->get_config("current_lang");
						form::show_autoform_new($this->get_config("table"), $data, $this->get_config("current_lang"));
						//var_dump($data);
						break;
					case "savedone":
						echo '';
						break;
					case "clear":
						echo '';
						break;
					
					default:
						$res_data = NULL;
						if($this->get_config("lang_dep"))
							$res_data = DBConnect::query("SELECT " . $this->get_config("table") . ".*, " . $this->get_config("table") . "_lang.* FROM `" . $this->get_config("table") . "`, `" . $this->get_config("table") . "_lang` WHERE " . $this->get_config("table") . ".id = " . $this->get_config("table") . "_lang.lang_parent_id AND " . $this->get_config("table") . "_lang.lang = '" . $this->get_config("current_lang") . "' AND `id`='" . addslashes($_GET["showform"]) . "'", __FILE__, __LINE__);
						else
							$res_data = DBConnect::query("SELECT * FROM `" . $this->get_config("table") . "` WHERE `id`='" . addslashes($_GET["showform"]) . "'", __FILE__, __LINE__);
						$row_data = mysql_fetch_array($res_data);
						form::show_autoform_new($this->get_config("table"), $row_data, $this->get_config("current_lang"));
						break;
				}
			}
			if(isset($_GET["delete"]))
			{
				$the_ids = explode("##", urldecode($_POST["delete"]));
				foreach($the_ids as $one_id)
				{
					data::delete($this->get_config("table"), $one_id, $this->get_config("order_field"), $this->get_config("parent_id_field"), $this->get_config("parent_id_value"));
				}
				page::last_update($_SESSION["CURRENT_PAGE_EDIT"]);
			}
			if(isset($_GET["changelang"]))
			{
				$dg = $_SESSION["datagrids"]["dg_de_" . $this->id];
				if($dg != NULL)
				{
					$this->set_config("current_lang", $_GET["changelang"]);
					//var_dump($this->get_config("parent_new_per_lang"));
					if($this->get_config("parent_new_per_lang"))
					{
						//zoeken naar het overeenkomstige id en de extra_where aanpassen van de datasource
						$res = DBConnect::query("SELECT lang_parent_id FROM `" . $this->get_config("parent_table") . "` WHERE `lang_id`='" . $this->get_config("parent_id_value") . "'", __FILE__, __LINE__);
						$row = mysql_fetch_array($res);
						$res = DBConnect::query("SELECT lang_id FROM `" . $this->get_config("parent_table") . "` WHERE `lang_parent_id`='" . $row["lang_parent_id"] . "' AND `lang`='" . $_GET["changelang"] . "'", __FILE__, __LINE__);
						$row = mysql_fetch_array($res);
						$this->set_config("parent_id_value", $row["lang_id"]);
						$dg->datasource->db_extra_where = " `" . $this->get_config("parent_id_field") . "`='" . $row["lang_id"] . "' ";
					}
					$dg->datasource->db_lang_current = $_GET["changelang"];
					$dg->publish(true);
				}
			}
			if(isset($_GET["addbypic"]))
			{
				$res = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $this->get_config("table") . "'", __FILE__, __LINE__);
				$row_table = mysql_fetch_array($res);
				header('Content-Type: text/xml');
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				echo '<dataeditor action="addbypic"><popup_id>' . $this->id . '</popup_id>';
				
				//CREATE PIC FORMATS
				$allfiles = explode("__splitter__", urldecode($_POST["picpaths"]));
				foreach($allfiles as $uploadFile)
				{
					$uploadFile = str_replace('//','/',$uploadFile);
					//echo $uploadFile;
					$res_picid = DBConnect::query("SELECT * FROM site_files WHERE `path`='" . str_replace($_SERVER['DOCUMENT_ROOT'], "/", $uploadFile) . "'", __FILE__, __LINE__);
					$row_picid = mysql_fetch_array($res_picid);
					$file_id = $row_picid["id"];
					$res_fromats = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->get_config("table") . "' AND `datadesc`='22'", __FILE__, __LINE__);
					$fieldtoedit = array();
					while($row_formats = mysql_fetch_array($res_fromats))
					{
						//zoeken naar de opties
						$options = data_description::options_convert_to_array($row_formats["datadesc"], $row_formats["data_options"]);
						if($options["master_pic_field"] == $this->get_config("piccol_pic_field"))
						{
							//zoeken naar pic format. Als niet bestaat: creëren
							$path = urldecode($uploadFile);
							$path = str_replace("userfiles/", "picformats/", $path);
							$path_parts = pathinfo($path);
							//plaatsen van suffix id_name
							$path_parts['filename'] = $path_parts['filename'] . '-' . $this->get_config("table") . '_' . $this->get_config("piccol_pic_field") . '-' . $row_formats["fieldname"];
							$path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '.' . $path_parts['extension'];
							if(!file_exists($path))
							{
								//creëren van thumb
								Pictures::create_thumb($uploadFile, $path, $options["format_x"],$options["format_y"], $options["watermark"]);
							}
							//zoeken naar thumb id
							$res_picid = DBConnect::query("SELECT * FROM site_files_derived WHERE `path`='" . str_replace($_SERVER['DOCUMENT_ROOT'], "/", $path) . "'", __FILE__, __LINE__);
							$row_picid = mysql_fetch_array($res_picid);
							if(!$row_picid)
							{
								//creëren van datarow
								DBConnect::query("INSERT INTO site_files_derived(`id`, `file_id`, `path`, `name`, `type`, `thumb_meta`) VALUES ('', '" . $file_id . "', '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], "/", $path)) . "', '" . $row_formats["fieldlabel"] . "', 'thumb', '" . $row_formats["id"] . "')", __FILE__, __LINE__);
								$res_picid = DBConnect::query("SELECT * FROM site_files_derived WHERE `path`='" . str_replace($_SERVER['DOCUMENT_ROOT'], "/", $path) . "'", __FILE__, __LINE__);
								$row_picid = mysql_fetch_array($res_picid);
							}
							$fieldtoedit[$row_formats["fieldname"]] = $row_picid["id"];
						}
					}
					
					//CREATE DATA ROW
					//order ophalen
					$sql = "SELECT `" . $this->get_config("order_field") . "` FROM `" . $this->get_config("table") . "`";
					if(trim($this->get_config("parent_id_field")) != "") 
						$sql .= " WHERE `" . $this->get_config("parent_id_field") . "`='" .$this->get_config("parent_id_value") . "'";
					$sql .= " ORDER BY `" . $this->get_config("order_field") . "` DESC LIMIT 0,1";
					$res = DBConnect::query($sql, __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$order = $row[$this->get_config("order_field")] + 1;
					
					//sql samenstellen
					$sql = "INSERT INTO `" . $this->get_config("table") . "` (`id`, `" . $this->get_config("order_field") . "`, `" . $this->get_config("piccol_pic_field") . "`";
					if(trim($this->get_config("parent_id_field")) != "") 
						$sql .= ", `" . $this->get_config("parent_id_field") . "`";
					foreach($fieldtoedit as $key => $path)
					{
						$sql .= ", `" . $key . "`";
					}
					
					$sql .= ") VALUES ('', '" . $order . "', '" . $file_id . "'";
					if(trim($this->get_config("parent_id_field")) != "") 
						$sql .= ", '" . $this->get_config("parent_id_value") . "'";
					foreach($fieldtoedit as $key => $path)
					{
						$sql .= ", '" . str_replace($_SERVER['DOCUMENT_ROOT'], '/', $path) . "'";
					}
					$sql .= ")";
					DBConnect::query($sql, __FILE__, __LINE__);
					$new_id =  DBConnect::get_last_inserted($this->get_config("table"), 'id');
					//nu nog de lang dependent data invullen
					if(DBConnect::check_if_table_exists($this->get_config("table") . "_lang"))
					{
						foreach(mainconfig::$languages as $abr => $lang)
						{
							DBConnect::query("INSERT INTO `" . $this->get_config("table") . "_lang` (`lang_id`, `lang_parent_id`, `lang`) VALUES ('', '" . $new_id . "', '" . $abr . "')", __FILE__, __LINE__);
						}
					}
				}
				echo '<dg_id>dg_de_' . $this->id . '</dg_id></dataeditor>';
			}
		}
		
		//------------------------------EDITABLE------------------------------
		public function publish_editable($datasource)
		{
			$data = $datasource->get_data();
			
			//we lopen de velden af
			$fields = array();
			$fields_raw = array();
			$fields_raw = explode(";", $this->get_config("list_fields"));
				
			foreach($fields_raw as $field)
			{
				//we get the label name
				$label = $field;
				$resmeta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->get_config("table") . "' AND `fieldname`='" . $field . "'", __FILE__, __LINE__);
				if($rowmeta = mysql_fetch_array($resmeta))
					$label = $rowmeta["fieldlabel"];
					
				//hidden	
				$hidden = false;
				if($rowmeta["datadesc"] == 5 || $rowmeta["datadesc"] == 6 || $rowmeta["datadesc"] == 21)
				{
					if($field != "order")
						$hidden = true;
				}
				
				//standaard -> als subdata dan hier instellen
				$standardwaarde = $rowmeta["data_standaardwaarde"];
				if($this->get_config("parent_id_field") == $field)
					$standardwaarde = $this->get_config("parent_id_value");
				
				
				$options = data_description::options_convert_to_array($rowmeta["datadesc"], $rowmeta["data_options"]);
				$fields[] = array("label" => $label, "name" => $field, "table" => $this->get_config("table"), "datadesc" => $rowmeta["datadesc"], "hidden" => $hidden, "lang_dep" => $rowmeta["lang_dep"], "obligated" => $rowmeta["obligated"], "options" => $options, "data_standaardwaarde" => $standardwaarde);
				
			}
			
			//nu de tabel aanmaken
			echo '<table class="editable" id="editable_' . $this->id . '"><tr>';
			foreach($fields as $field)
			{
				echo '<th ' . (($field["hidden"])?'style="display: none"':'') . '>' . $field["label"] . '</th>';
			}
			//buttons
			echo '<th></th>';
			
			echo '</tr>';
			
			//we maken één lege rij aan, als standaar waarde
			$legerij = array("emptytemplate" => true);
			foreach($fields as $field)
			{
				$legerij[$field["name"]] = $field["data_standaardwaarde"];
			}
			$data[] = $legerij;
			$addrow_html = "";
			
			foreach($data as $item)
			{
				if($item["emptytemplate"])
					ob_start();
				echo '<tr class="datarow">';
				foreach($fields as $field)
				{
					echo '<td ' . (($field["hidden"])?'style="display: none"':'') . '>';
					if($field["name"] == "order")
					{
						echo '<div class="editable_ordering btn"><img src="/css/back/icon/new/move_vertical.png"></img></div>';
					}
					$width = 120;
					if($field["datadesc"] == "2")
						$width = 300;
					
					formfield::publish($field["table"] . "." . $field["name"], $item[$field["name"]], $field["lang_dep"], $field["datadesc"], $field["options"], $field["obligated"], NULL, NULL, NULL, $width, 16, "", $this->get_config("current_lang"));
					
					echo '</td>';
				}
				//buttons
				echo '<td>';
				echo '<div class="editable_save btn" gridId="' . $this->id . '" tableName="' . $this->get_config('table') . '"><img src="/css/back/icon/new/save.png"></img></div>';
				echo '<div class="editable_remove btn" gridId="' . $this->id . '" tableName="' . $this->get_config('table') . '"><img src="/css/back/icon/new/trash.png"></img></div>';
				echo '</td>';
				echo '</tr>';
				if($item["emptytemplate"])
				{
					$addrow_html = ob_get_contents();
					ob_end_clean();
				}
			}
			echo '</table>';
			
			//add button
			echo '<div class="editable_add_' . $this->id . ' btn"><img src="/css/back/icon/new/plus.png"></img></div>';
			
			//JAVASCRIPT
			?>
            <script language="javascript">
				$("#editable_<?php echo $this->id; ?>").editable({"newRow": '<?php echo addslashes($addrow_html);?>', "newButton": $(".editable_add_<?php echo $this->id; ?>"), "id": '<?php echo $this->id; ?>', "ordering": <?php echo (($this->get_config("order"))?"true":"false")?>, "tableName": '<?php echo $this->get_config("table"); ?>'});
			</script>
            <?php
		}
		
		public function handle_ajax_editable()
		{
			switch($_GET["action"])
			{
				case 'save':
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					
					echo '<savereturn>';
					$doc = new SimpleXMLElement(urldecode(stripslashes($_GET["xml"])));
					
					$table = '';
					$fields = array();
					$errors = false;
					foreach($doc->field as $field)
					{
						$tmp = explode('.', $field["name"]);
						if($tmp[0] != $table) $table = $tmp[0];
						$fields[$tmp[1]] = $field[0];
						$validation = data_description::validate_db($field["name"], $field[0]);
						if(trim($validation) != "")
						{
							echo '<error field="' . $field["name"] . '"><[CDATA[' . $validation . ']]></error>';
							$errors = true;
						}
					}
					if(trim($doc->tmpid[0][0]) != "")
						echo '<tmpid>' . $doc->tmpid[0][0] . '</tmpid>';
					
					if(!$errors)
					{
						//zoeken naar order
						$fields["order"] = 1;
						if(trim($doc->afterRow[0][0]) != "" && isset($fields["order"]))
						{
							//eerst doen we een reorder -> anderl loopt het vaak mis
							$tmp = array();
							$res = DBConnect::query("SELECT * FROM `" . $table . "` WHERE `order`>='" . $fields["order"] . "'", __FILE__, __LINE__);
							$count = 1;
							while($row = mysql_fetch_array($res))
							{
								$tmp[$count] = $row["id"];
								$count++;
							}
							foreach($tmp as $order => $id)
							{
								DBConnect::query("UPDATE `" . $table . "` SET `order`='" . $order . "' WHERE `id`='" . $id . "'", __FILE__, __LINE__);
							}
							//eind initiele sort
							
							$res = DBConnect::query("SELECT * FROM `" . $table . "` WHERE `id`='" . $doc->afterRow[0][0] . "'", __FILE__, __LINE__);
							if($row = mysql_fetch_array($res))
								$fields["order"] = $row["order"] + 1;
								
							echo '<field name="' . $table . '.order">' . $fields["order"] . '</field>';
							
							//alle rijen met order >= Updaten (+1)
							$res = DBConnect::query("SELECT * FROM `" . $table . "` WHERE `order`>='" . $fields["order"] . "'", __FILE__, __LINE__);
							$count = $fields["order"] + 1;
							$tmp = array();
							while($row = mysql_fetch_array($res))
							{
								$tmp[$count] = $row["id"];
								$count++;
							}
							foreach($tmp as $order => $id)
							{
								DBConnect::query("UPDATE `" . $table . "` SET `order`='" . $order . "' WHERE `id`='" . $id . "'", __FILE__, __LINE__);
							}
						}
						
						//nu checken of de rij al bestaat
						$res = DBConnect::query("SELECT * FROM `" . $table . "` WHERE `id`='" . $fields["id"] . "'", __FILE__, __LINE__);
						if($row = mysql_fetch_array($res))
						{
							//rij updaten	
							$arr = array();
							foreach($fields as $name => $value)	
							{
								$arr[] = "`" . $name . "`='" . addslashes($value) . "'";
							}
							$sql = "UPDATE `" . $table . "` SET " . implode(', ', $arr) .  " WHERE `id`='" . $fields["id"] . "'";
							DBConnect::query($sql, __FILE__, __LINE__);
						}
						else
						{
							$names = array();
							$values = array();
							foreach($fields as $name => $value)	
							{
								$names[] = $name;
								$values[] = addslashes($value);
							}
							//rij invoegen
							$sql = "INSERT INTO `" . $table . "` (`" . implode("`, `", $names) . "`) VALUES ('" . implode("', '", $values) . "')";
							DBConnect::query($sql, __FILE__, __LINE__);
							
							$new_id = mysql_insert_id();
							echo '<field name="' . $table . '.id">' . $new_id . '</field>';
						}
					}
					
					echo '</savereturn>';
					break;
				case 'remove':
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					
					echo '<removereturn>';
					$doc = new SimpleXMLElement(urldecode(stripslashes($_GET["xml"])));
					
					DBConnect::query("DELETE FROM `" . $doc->id[0]["table"] . "` WHERE `id`='" . $doc->id[0][0] . "'", __FILE__, __LINE__);
					
					echo '<removereturn>';
					break;
			}
		}
	}
?>