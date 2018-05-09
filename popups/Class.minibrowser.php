<?php
	class minibrowser extends popup
	{
		function __construct($id, $width, $height)
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "minibrowser");
		}
		
		//de start folder en current folders
		public function set_folder($folder)
		{
			//zorgen dat current path toch onthouden word
			if(trim($this->get_config("folder")) == "")
				$this->set_config("folder", $folder);
		}
		
		public function set_current_path($current_path)
		{
			$this->set_config("current_path", $current_path);
		}
		
		//view: als picture dan enkel thumbs opties: "picture", "list"
		public function set_view($view)
		{
			$this->set_config("view", $view);
		}
		
		public function set_extentions($extentions)
		{
			$this->set_config("extentions", $extentions);
		}
		
		public function set_input_field_id($input_field_id)
		{
			$this->set_config("input_field_id", $input_field_id);
		}
		
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			echo $this->get_config("folder");
			//<div style="height:70px; padding-top:30px; text-align:center"><img src="/css/back/loader3.gif"></div>
			$this->publish_start($in_popup, $from_ajax);
			echo '<div id="minibrowser_' . $this->id . '" style="height:300px; overflow-y:scroll;">';
			//if there is a value then we browse direct to that folder and select the file
			if(trim($this->get_config("current_path")) != "")
			{
				$tmp = pathinfo($this->get_config("current_path"));
				$this->set_config("folder", $tmp["dirname"]);
			}
			//$this->publish_grid();
			echo '<div style="padding-top:20px;text-align: center;"><img src="/css/back/loader.gif"></div>
				</div>';
			echo '<style>
					#minibrowser_' . $this->id . ' .ui-selecting { background: #CCCCCC; }
					#minibrowser_' . $this->id . ' .ui-selected { background: #88A688; }
				</style>';
			echo '<script> 
					//window.minibrowser_' . $this->id . '_panel = new Spry.Widget.HTMLPanel("minibrowser_' . $this->id . '",{evalScripts:true});
					$("#minibrowser_' . $this->id . '").load(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&action=loaddir&thedir=' . urlencode($this->get_config("folder")) . '\');
					//$("#minibrowser_' . $this->id . '").selectable();
					$("#minibrowser_' . $this->id . '" ).selectable({
						stop: function() {
							var ids = "";
							var counter = 0;
							$( ".ui-selected", this ).each(function() {
								if(counter == 0)
								{
									if(this.getAttribute("is_dir") != "true")
									{
										field = document.getElementById("' . $this->get_config("input_field_id") . '");
										field.value = this.getAttribute("file_id");
										if(field.onfilefieldchange != null && field.onfilefieldchange != undefined)
											field.onfilefieldchange();
										send_ajax_request("GET", "/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&action=set_current&path=" + encodeURI(this.getAttribute("path")), "", null);
									}
								}
								if(counter > 0)
									$(this).removeClass("ui-selected");
							
								counter++;
							});
						}
					});
				</script>';
			
			$this->publish_end($in_popup, $from_ajax);
		}
		
		private function publish_grid()
		{
			//we zoeken alle files fab de folder
			$ds = new datasource();
			$ds->type = "FILESYSTEM";
			$ds->fs_folder = $this->get_config("folder") . "/";
			$ds->sort_field = "filename";
			$ds->sort_order = "ASC";
			if(trim($this->get_config("extentions")) != "")
				$ds->fs_extentions = explode("_", $this->get_config("extentions"));
			$data = $ds->get_data();
			//var_dump($data);
			
			//eerst checken of we .. map moeten toevoegen
			if($ds->fs_folder != "userfiles/" && $ds->fs_folder != "userfiles")
			{
				//maken van pad - laatste folder
				$newfolder = substr($ds->fs_folder, 0, strlen($ds->fs_folder)-2);
				$tmp = explode('/', $newfolder);
				unset($tmp[count($tmp)-1]);
				$newfolder = implode('/', $tmp) . '/';
				$ondblclick = "$('#minibrowser_" . $this->id . "').load('/ajax.php?sessid=" . session_id() . "&popup_id=" . $this->id . "&action=loaddir&thedir=" . urlencode($newfolder) . "');";
				if($this->get_config("view") == "picture")
					echo '<div is_dir="true" path="' . $newfolder . '"  ondblclick="' . $ondblclick . '" style="width: 80px; float:left; height:84px; padding:2px; margin:2px; border: 1px solid #CCCCCC; text-align:center; overflow:hidden;">
							<img src="/css/back/icon/file/64/folder.png"><br><span style="width:72px; font-size: 9px; color: #666666; padding-top:2px;">..</span>
						</div>';
				else
					echo '<div is_dir="true" path="' . $newfolder . '"  ondblclick="' . $ondblclick . '" style="padding: 2px; border-bottom: 1px solid #CCCCCC">
							<img src="/css/back/icon/file/mini/folder.gif"><span style="line-height:16px; vertical-align:top; padding-left:8px;">..</span>
						</div>';
			}
			foreach($data as $row)
			{
				$ondblclick = "";
				$tmp = pathinfo($row["path"]);
				$file_id = "";
				if($row["is_dir"] == "true")
					$ondblclick =  "$('#minibrowser_" . $this->id . "').load('/ajax.php?sessid=" . session_id() . "&popup_id=" . $this->id . "&action=loaddir&thedir=" . urlencode($row["path"]) . "');";
				else
				{
					$ondblclick = "cms2_open_file('" . $row["path"] . "', '" . strtolower($tmp["extension"]) . "', null);";
					$file_id = Files::get_dbfile_id($row["path"]);
				}
				if($this->get_config("view") == "picture")
				{
					echo '<div ' . (($this->get_config("current_path")==$row["path"])?'class="ui-selected"':'') . ' is_dir="' . $row["is_dir"] . '" path="' . $row["path"] . '" file_id="' . $file_id . '" ondblclick="' . $ondblclick . '" style="width: 80px; float:left; height:84px; padding:2px; margin:2px; border: 1px solid #CCCCCC; text-align:center; overflow:hidden;">
							' . $row["icon64"] . '
							<br><span style="width:72px; font-size: 9px; color: #666666; padding-top:2px;">' . $row["filename"] . '</span>
						</div>';
				}
				else
					echo '<div ' . (($this->get_config("current_path")==$row["path"])?'class="ui-selected"':'') . ' is_dir="' . $row["is_dir"] . '" path="' . $row["path"] . '" file_id="' . $file_id . '" ondblclick="' . $ondblclick . '" style="padding: 2px; border-bottom: 1px solid #CCCCCC">
							' . $row["iconmini"] . '<span style="line-height:16px; vertical-align:top; padding-left:8px;">' . $row["filename"] . '</span>
						</div>';
			}
		}
		
		public function handle_ajax()
		{
			if(!login::check_login())
				return "";
			
			//var_dump($_GET);
			switch($_GET["action"])
			{
				case "loaddir":
					$tmp = urldecode($_GET["thedir"]);
					if(substr($tmp, strlen($tmp)-1) == '/')
						$tmp = substr($tmp, 0, strlen($tmp)-1);
					$this->set_config("folder", $tmp);
					$this->publish_grid();
					break;
				case "set_current":
					$this->set_config("current_path", urldecode($_GET["path"]));
					break;
				case "select_file":
					$this->set_config("current_path", urldecode($_GET["path"]));
					$tmp = pathinfo(urldecode($_GET["path"]));
					$this->set_config("folder", $tmp["dirname"]);
					$this->publish_grid();
					break;
			}
		}
	}
?>