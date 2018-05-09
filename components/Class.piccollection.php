<?php
	class piccollection
	{
		public $id;					//the datagrid id
		public $datasource;			//the datasource to get the data
		public $order_field;
		public $picfield;
		public $data_name;
		public $data_name_plural;
		public $parent_id_field;
		public $parent_id_value;
		
		function __construct() 
		{
       		//we set the default vars
			$this->id = "temp";
			$this->datasource = NULL;
			$this->order_field = "";
			$this->picfield = "";
			$this->data_name = "";
			$this->data_name_plural = "";
			$this->parent_id_field = "";
			$this->parent_id_value = "";
		}
		
		//Displays the component
		function publish($fromajax)
		{
			$_SESSION["piccol"][$this->id] = $this;
			//we maken eerst en vooral dat het javascript datagrid object bestaat
			if($fromajax == false)
			{
				echo '<script>
						window.piccol_' . $this->id . ' = new piccollection("' . $this->id . '");';
				echo '</script>';
				//upload functie
				echo '<div>';
				$str_extentions = "*.jpg; *.jpeg; *.png; *.gif";
?>
			<form id="form1" action="index.php" method="post" enctype="multipart/form-data">
				<span class="contentheader" id="<?php echo $this->id . '_upload'; ?>fsUploadProgress"></span>
				<div style="clear:both; lin-height: 28px; height: 28px; overflow:hidden;">
					<div style="float:left;">
						<span id="<?php echo $this->id . '_upload'; ?>spanButtonPlaceHolder" style="height: 28px; width:100px;"></span>
					</div>
					<div style="float:left;">
						<input id="<?php echo $this->id . '_upload'; ?>btnCancel" type="button" value="Cancel Upload" onclick="<?php echo $this->id . '_upload'; ?>swfu.cancelQueue();" style="width:100px; margin-left:4px; margin-top:0px; " />
					</div>
					<div style="float:left;">
						<input style="margin-left:4px; margin-top:0px; " value="Open full browser" type="button" onclick="somewindow = window.open('http://<?php echo $_SERVER['HTTP_HOST']; ?>/browser.php?br_extentions=jpg_jpeg_png_gif&addbutton=yes','','width=1024,height=516,scrollbars=no,toolbar=no,location=no,resizable=no,status=no'); somewindow.piccol=window.piccol_<?php echo $this->id; ?>;">
					</div>
				<div style="clear:both; "></div>
				</div>
			</form>
			<script type="text/javascript">
				var <?php echo $field_id . '_'; ?>swfu;
				new function(){
					var <?php echo $this->id . '_upload_swfu_'; ?>settings = {
						flash_url : "/plugins/swfupload/swfupload.swf",
						upload_url: "/ajax.php?sessid=<?php echo session_id();?>&piccol_id=<?php echo $this->id;?>&action=fileupload",
						post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
						file_size_limit : "100 MB",
						file_types : "<?php echo $str_extentions; ?>",
						file_types_description : "Picture Files",
						file_upload_limit : 100,
						file_queue_limit : 0,
						custom_settings : {
							progressTarget : "<?php echo $this->id . '_upload'; ?>fsUploadProgress",
							cancelButtonId : "<?php echo $this->id . '_upload'; ?>btnCancel"
						},
						debug: false,
			
						// Button settings
						button_image_url: "/plugins/swfupload/Button 120x20.png",
						button_width: "100",
						button_height: "27",
						button_placeholder_id: "<?php echo $this->id . '_upload'; ?>spanButtonPlaceHolder",
						button_text: '<span class="theFont">Upload File</span>',
						button_text_style: ".theFont { font-size: 11; font-family: verdana; color: #FFFFFF; font-weight: bold;}",
						button_text_left_padding: 10,
						button_text_top_padding: 4,
						button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE, 
						button_disabled : false, 
						button_cursor : SWFUpload.CURSOR.HAND, 
						// The event handler functions are defined in handlers.js
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : uploadSuccessPiccollection,
						upload_complete_handler : uploadComplete,
						queue_complete_handler : queueComplete	// Queue plugin event
					};
			
					<?php echo $this->id . '_upload'; ?>swfu = new SWFUpload(<?php echo $this->id . '_upload_swfu_'; ?>settings);
					<?php echo $this->id . '_upload'; ?>swfu.piccol_id = "<?php echo $this->id; ?>";
				};
			</script>
<?php
				echo '</div>';
				echo '<div id="piccol_' . $this->id . '" name="piccol_' . $this->id . '" onselectstart="return false;" style="padding-top:4px;">';
			}
			//get the table fields
			$fields = array();
			$res = DBConnect::query("SELECT fieldname FROM `sys_database_meta` WHERE tablename='" . $this->datasource->db_table . "' ORDER BY `order`", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
				$fields[] = $row["fieldname"];
			
			$data = $this->datasource->get_data();
			foreach($data as $dataitem)
			{
				$this->echo_one_pic($dataitem, $fields);
			}
			
			//ordering functionality
			if(trim($this->order_field) != "")
			{
				echo'<script>
					$(function() {
						$("#piccol_' . $this->id . '" ).sortable({
								update: function(event, ui) {
									var str_ordering="";
									var order_counter = 1;
									$("#piccol_' . $this->id . '" ).children("div").each(function(){
											$(this).find(\'input[name="' . $this->order_field . '"]\').val(order_counter);
											if(str_ordering != "") str_ordering += "$";
											str_ordering += $(this).attr("id") + ";" + order_counter;
											
											order_counter++;
										});
									send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&piccol_id=' . $this->id . '&action=saveorder&orderstr=\' + encodeURI(str_ordering), \'\', null);
								}
							});
					});
					</script>';
			}
			
			if($fromajax == false)
				echo '</div>';
			if(trim($this->order_field) != "")
				echo'<div style="padding-top: 4px;" class="smallgray">Tip: drag ' . ((trim("$this->data_name_plural")=="")?"elements":$this->data_name_plural) . ' to order the data</div>';

		}
		
		function echo_one_pic($dataitem, $fields)
		{
			//we get the table fields
			echo '<div id="' . $dataitem["id"] . '" style="border-bottom: 1px solid #CCCCCC; padding: 4px 0px 4px 0px;">';
			
			//pic field zoeken
			foreach($fields as $one_field)
			{
				if($one_field == $this->picfield)
				{
					//tonen van de thumb
					echo '<div style="float:left; padding-right: 8px;"><img src="' . str_replace('/userfiles', '/picsysthumb', $dataitem[$one_field]) . '"/></div>';
				}
			}
			echo '<div style="float:left>';
			//tonen van pic formats
			$path = substr($dataitem[$this->picfield], 1);
			$path_parts = pathinfo($path);
			echo '<p style="line-height:22px; height: 22px;"><a href="javascript:cms2_open_file(\'' . $path . '\', \'' . $path_parts['extension'] . '\', null);">Original</a></p>';
			$res_formats = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->datasource->db_table . "' AND `datadesc`='22'", __FILE__, __LINE__);
			while($row_formats = mysql_fetch_array($res_formats))
			{
				$options = data_description::options_convert_to_array($row_formats["datadesc"], $row_formats["data_options"]);
				$path_th = substr($dataitem[$row_formats["fieldname"]], 1);
				$path_parts = pathinfo($path_th);
				echo '<p style="line-height:22px; height: 22px;"><a href="javascript:cms2_open_file(\'' . $path_th . '\', \'' . $path_parts['extension'] . '\', null);">' . $row_formats["fieldlabel"] . '</a> <a href="javascript:cms2_open_pic_edit(\'' . $path . '\', \'' . $path_th . '\', \'' . $options["format_x"] . '\', \'' . $options["format_y"] . '\');">edit</a></p>';
			}
			//andere velden
			foreach($fields as $one_field)
			{
				if($one_field != $this->picfield)
				{
					//echoën van het field
					formfield::publish_dbfield($this->datasource->db_table . "." . $one_field, $dataitem[$one_field], NULL, 466);
				}
			}
			echo '</div>';
			echo '<div style="clear:both;"></div></div>';
		}
		
		//returns the id of the component		
		function handle_ajax()
		{
			switch($_GET["action"])
			{
				case "saveorder":
					$parts = explode("$", urldecode($_GET["orderstr"]));
					
					foreach($parts as $part)
					{
						$chomps = explode(";", $part);
						DBConnect::query("UPDATE `" . $this->datasource->db_table . "` SET `" . $this->order_field . "`='" . $chomps[1] . "' WHERE `id`='" . $chomps[0] . "'", __FILE__, __LINE__);
					}
					break;
				case 'fileupload':
					//we zoeken naar de 'direct upload folder'
					$res = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $this->datasource->db_table . "'", __FILE__, __LINE__);
					$row_table = mysql_fetch_array($res);
					//plaatsen van de nieuwe file
					if (is_uploaded_file($_FILES['Filedata']['tmp_name']))	 
					{
						$uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . "userfiles/" . $row_table["gridpiccol_folder"];
						$uploadDirectory = str_replace('//', '/', $uploadDirectory);
						if(!is_dir(str_replace('//', '/', "userfiles/" . $row_table["gridpiccol_folder"])))
						{
							mkdir(str_replace('//', '/', "userfiles/" . $row_table["gridpiccol_folder"]), 0777, true);
						}
						$uploadFile = $uploadDirectory . '/' . basename($_FILES['Filedata']['name']);
						$uploadFile = Files::make_unique($uploadFile);
						move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadFile);
						Pictures::create_system_thumb($uploadFile);
						
						//CREATE PIC FORMATS
						$res_fromats = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->datasource->db_table . "' AND `datadesc`='22'", __FILE__, __LINE__);
						$fieldtoedit = array();
						while($row_formats = mysql_fetch_array($res_fromats))
						{
							//zoeken naar de opties
							$options = data_description::options_convert_to_array($row_formats["datadesc"], $row_formats["data_options"]);
							if($options["master_pic_field"] == $this->picfield)
							{
								//zoeken naar pic format. Als niet bestaat: creëren
								$path = urldecode($uploadFile);
								$path = str_replace("userfiles/", "picformats/", $path);
								$path_parts = pathinfo($path);
								//plaatsen van suffix id_name
								$path_parts['filename'] = $path_parts['filename'] . '-' . $this->datasource->db_table . '_' . $this->picfield . '-' . $row_formats["fieldname"];
								$path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '.' . $path_parts['extension'];
								if(!file_exists($path))
								{
									//creëren van thumb
									Pictures::create_thumb($uploadFile, $path, $options["format_x"],$options["format_y"]);
								}
								$fieldtoedit[$row_formats["fieldname"]] = $path;
							}
						}
						
						//CREATE DATA ROW
						//order ophalen
						$sql = "SELECT `" . $this->order_field . "` FROM `" . $this->datasource->db_table . "`";
						if(trim($this->parent_id_field) != "") 
							$sql .= " WHERE `" . $this->parent_id_field . "`='" . $this->parent_id_value . "'";
						$sql .= " ORDER BY `" . $this->order_field . "` DESC LIMIT 0,1";
						$res = DBConnect::query($sql, __FILE__, __LINE__);
						$row = mysql_fetch_array($res);
						$order = $row[$this->order_field] + 1;
						
						//sql samenstellen
						$sql = "INSERT INTO `" . $this->datasource->db_table . "` (`id`, `" . $this->order_field . "`, `" . $this->picfield . "`";
						if(trim($this->parent_id_field) != "") 
							$sql .= ", `" . $this->parent_id_field . "`";
						foreach($fieldtoedit as $key => $path)
						{
							$sql .= ", `" . $key . "`";
						}
						$sql .= ") VALUES ('', '" . $order . "', '" . str_replace($_SERVER['DOCUMENT_ROOT'], '/', $uploadFile) . "'";
						if(trim($this->parent_id_field) != "") 
							$sql .= ", '" . $this->parent_id_value . "'";
						foreach($fieldtoedit as $key => $path)
						{
							$sql .= ", '" . str_replace($_SERVER['DOCUMENT_ROOT'], '/', $path) . "'";
						}
						$sql .= ")";
						DBConnect::query($sql, __FILE__, __LINE__);
						$new_id =  DBConnect::get_last_inserted($this->datasource->db_table, 'id');
						//nu nog de lang dependent data invullen
						if(DBConnect::check_if_table_exists($this->datasource->db_table . "_lang"))
						{
							foreach(mainconfig::$languages as $abr => $lang)
							{
								DBConnect::query("INSERT INTO `" . $this->datasource->db_table . "_lang` (`lang_id`, `lang_parent_id`, `lang`) VALUES ('', '" . $new_id . "', '" . $abr . "')", __FILE__, __LINE__);
							}
						}
						echo 'piccol_' . $this->id . '_splitter_' . $new_id;
					}
					break;
				case "form_id":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<piccol action="form_id"><piccol_id>' . $this->id . '</piccol_id>';
					echo '<content><![CDATA[';
					$fields = array();
					$res = DBConnect::query("SELECT fieldname FROM `sys_database_meta` WHERE tablename='" . $this->datasource->db_table . "' ORDER BY `order`", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
						$fields[] = $row["fieldname"];
					$data = $this->datasource->get_data();
					foreach($data as $dataitem)
					{
						if($dataitem["id"] == $_POST["picid"])
							$this->echo_one_pic($dataitem, $fields);
					}
					echo ']]></content></piccol>';
					break;
				case "form_path":
					$res = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $this->datasource->db_table . "'", __FILE__, __LINE__);
					$row_table = mysql_fetch_array($res);
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<piccol action="form_path"><piccol_id>' . $this->id . '</piccol_id>';
					echo '<content><![CDATA[';
					//CREATE PIC FORMATS
					$uploadFile = str_replace('//','/',urldecode($_POST["picpath"]));
					$res_fromats = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $this->datasource->db_table . "' AND `datadesc`='22'", __FILE__, __LINE__);
					$fieldtoedit = array();
					while($row_formats = mysql_fetch_array($res_fromats))
					{
						//zoeken naar de opties
						$options = data_description::options_convert_to_array($row_formats["datadesc"], $row_formats["data_options"]);
						if($options["master_pic_field"] == $this->picfield)
						{
							//zoeken naar pic format. Als niet bestaat: creëren
							$path = urldecode($uploadFile);
							$path = str_replace("userfiles/", "picformats/", $path);
							$path_parts = pathinfo($path);
							//plaatsen van suffix id_name
							$path_parts['filename'] = $path_parts['filename'] . '-' . $this->datasource->db_table . '_' . $this->picfield . '-' . $row_formats["fieldname"];
							$path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '.' . $path_parts['extension'];
							if(!file_exists($path))
							{
								//creëren van thumb
								Pictures::create_thumb($uploadFile, $path, $options["format_x"],$options["format_y"]);
							}
							$fieldtoedit[$row_formats["fieldname"]] = $path;
						}
					}
					
					//CREATE DATA ROW
					//order ophalen
					$sql = "SELECT `" . $this->order_field . "` FROM `" . $this->datasource->db_table . "`";
					if(trim($this->parent_id_field) != "") 
						$sql .= " WHERE `" . $this->parent_id_field . "`='" . $this->parent_id_value . "'";
					$sql .= " ORDER BY `" . $this->order_field . "` DESC LIMIT 0,1";
					$res = DBConnect::query($sql, __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$order = $row[$this->order_field] + 1;
					
					//sql samenstellen
					$sql = "INSERT INTO `" . $this->datasource->db_table . "` (`id`, `" . $this->order_field . "`, `" . $this->picfield . "`";
					if(trim($this->parent_id_field) != "") 
						$sql .= ", `" . $this->parent_id_field . "`";
					foreach($fieldtoedit as $key => $path)
					{
						$sql .= ", `" . $key . "`";
					}
					$sql .= ") VALUES ('', '" . $order . "', '" . str_replace($_SERVER['DOCUMENT_ROOT'], '/', $uploadFile) . "'";
					if(trim($this->parent_id_field) != "") 
						$sql .= ", '" . $this->parent_id_value . "'";
					foreach($fieldtoedit as $key => $path)
					{
						$sql .= ", '" . str_replace($_SERVER['DOCUMENT_ROOT'], '/', $path) . "'";
					}
					$sql .= ")";
					DBConnect::query($sql, __FILE__, __LINE__);
					$new_id =  DBConnect::get_last_inserted($this->datasource->db_table, 'id');
					//nu nog de lang dependent data invullen
					if(DBConnect::check_if_table_exists($this->datasource->db_table . "_lang"))
					{
						foreach(mainconfig::$languages as $abr => $lang)
						{
							DBConnect::query("INSERT INTO `" . $this->datasource->db_table . "_lang` (`lang_id`, `lang_parent_id`, `lang`) VALUES ('', '" . $new_id . "', '" . $abr . "')", __FILE__, __LINE__);
						}
					}
					//ECHO FORM
					$fields = array();
					$res = DBConnect::query("SELECT fieldname FROM `sys_database_meta` WHERE tablename='" . $this->datasource->db_table . "' ORDER BY `order`", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
						$fields[] = $row["fieldname"];
					$data = $this->datasource->get_data();
					foreach($data as $dataitem)
					{
						if($dataitem["id"] == $new_id)
							$this->echo_one_pic($dataitem, $fields);
					}
					echo ']]></content></piccol>';
					break;
			}
		}
	}
?>