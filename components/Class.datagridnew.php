<?php
	class datagridnew
	{
		public $id;					//the datagrid id
		public $title;				//datagrid title
		public $show_title_bar;		//show the title or not
		public $show_icon_bar;		//show the iconbar or not
		public $icons;				//the icons
		public $rowclick;			//java action when row click
		public $rowdblclick;		//java action when row dbl click
		public $search;				//we have a search box
		public $datasource;			//the datasource to get the data
		public $paging;				//use paging or not
		public $perpage;			//how many items per page
		public $current_page;		//which page are we currently showing
		public $fields;				//the fields of the datagrid
		public $checkbox;			//show checkboxes or not
		public $id_field;			//the dataname of the id field
		public $sort_field;			//the current sorting field
		public $sort_order;			//ASC or DESC
		public $format_width;		//the width of the datagrid
		public $format_fixed_height;//if isset we use a scrollbar
		public $order_field;
		public $picture_view;
		public $picture_html;
		public $picture_placeholder_style;
		public $data_name;
		public $data_name_plural;
		
		function __construct() 
		{
       		//we set the default vars
			$this->id = "temp";
			$this->title = ""; 			
			
			$this->show_title_bar = true;	
			$this->show_icon_bar = true;	
			$this->icons = array();		
			$this->rowclick = "";
			$this->rowdblclick = "";
			$this->search = true;		
			$this->datasource = NULL;
			$this->paging = true;
			$this->perpage = 20;
			$this->fields = array();
			$this->checkbox = false;
			$this->id_field = "";
			$this->sort_field = "";
			$this->sort_order = "ASC";
			$this->format_width = "100%";
			$this->format_fixed_height = 0;
			$this->current_page = 1;
			$this->order_field = "";
			$this->picture_view = false;
			$this->picture_html = "";
			$this->picture_placeholder_style = "";
			$this->data_name = "";
			$this->data_name_plural = "";
		}
		
		public function addicon($name, $img, $img_gray, $action, $enabled_by_rowclick, $enabled_by_rowcheck, $initial_disabled)
		{
			$this->icons[] = array("name" => $name,
								   "img" => $img,
								   "img_gray" => $img_gray,
								   "action" => $action,
								   "enabled_by_rowclick" => $enabled_by_rowclick,
								   "enabled_by_rowcheck" => $enabled_by_rowcheck,
								   "initial_disabled" => $initial_disabled,
								   "splitter" => false);
		}
		
		public function addfield($dataname, $displayname, $editable, $visible, $sortable, $width, $is_icon, $dbname = "")
		{
			$this->fields[] = array("dataname" => $dataname,
								   "displayname" => $displayname,
								   "editable" => $editable,
								   "visible" => $visible,
								   "sortable" => $sortable,
								   "width" => $width,
								   "is_icon" => $is_icon,
								   "dbname" => $dbname);
		}
		
		public function add_icon_splitter()
		{
			$this->icons[] = array("splitter" => true);
		}
		
		//Displays the backside of the component
		function creator()
		{
			
		}
		
		//Displays the component
		function publish($fromajax)
		{
			$html_pan = 'dg_' . $this->id . '_html_panel';
			$_SESSION["datagrids"][$this->id] = $this;
			$totalrows = 0;
			//we maken eerst en vooral dat het javascript datagrid object bestaat
			if($fromajax == false)
			{
				
				echo '<script>
					window.dg_' . $this->id . ' = new datagrid("' . $this->id . '");';
				$iconcount = 0;
				foreach($this->icons as $one_icon)
				{
					echo 'dg_' . $this->id . '.icon_ids[' . $iconcount . '] = "' . $this->id . '_' . $one_icon["name"] . '";';
					$iconcount++;
				}
				echo '</script>';
				
				echo '<div id="' . $html_pan . '" name="' . $html_pan . '" onselectstart="return false;" class="datagrid">';
			}
			//we tellen eerst het aantal velden die getoont worden
			$fieldcount=0;
			foreach($this->fields as $one_field)
			{
				if($one_field["visible"])
					$fieldcount++;
			}
			if($this->checkbox)
				$fieldcount++;
			//start showing the grid
			if($this->show_title_bar && !$this->show_icon_bar)
				echo '<div class="datagrid_title">' . $this->title . '</div>';
			if($this->show_icon_bar)
			{
				echo '<div class="iconcontainer" style="height:18px;">';
				
				if($this->show_title_bar)
					echo '<div class="divleft"><span class="datagrid_title">' . $this->title . '</span></div>';
				echo '<div class="divleft">';
				foreach($this->icons as $one_icon)
				{
					if($one_icon["splitter"])
						echo '<img src="/css/back/icon/twotone/splitter.gif" class="icon"/>';
					else
						echo '<img alt="' . $one_icon["name"] . '" title="' . $one_icon["name"] . '" class="icon" id="' . $this->id . '_' . $one_icon["name"] . '" state="' . (($one_icon["initial_disabled"])?"disabled":"enabled") . '" enabled_by_rowclick="' . (($one_icon["enabled_by_rowclick"])?"true":"false") . '" enabled_by_rowcheck="' . (($one_icon["enabled_by_rowcheck"])?"true":"false") . '" icon_enabled="' . $one_icon["img"] . '" icon_disabled="' . $one_icon["img_gray"] . '" src="' . (($one_icon["initial_disabled"])?$one_icon["img_gray"]:$one_icon["img"]) . '" onclick="' . $one_icon["action"] . '" style="' . (($one_icon["initial_disabled"])?"":"cursor:pointer;") . '">';
				}
				
				echo '</div>';
				
				if($this->paging)
				{
					echo '<div class="divleft" style="padding-left: 30px;"><div class="dg_pagenum_current" style="color:#666666;background-color: #FFFFFF;">Pages:&nbsp;&nbsp;</div>';
					$totalrows = $this->datasource->get_data_count();
					$count_pages = $totalrows/$this->perpage; 
					if($totalrows % $this->perpage != 0)
						$count_pages++;
					for($i = 1 ; $i <= $count_pages ; $i++)
					{
						if($i == $this->current_page)
							echo '<div class="dg_pagenum_current">' . $i . '</div>';
						else
							echo '<div onclick="dg_' . $this->id . '_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=page&pagenum=' . $i . '\');" class="dg_pagenum">' . $i . '</div>';
					}
					echo '<div onclick="dg_' . $this->id . '_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=page&pagenum=all\');" class="' . (($this->current_page == "all")?'dg_pagenum_current':'dg_pagenum') . '">all</div>';
					echo '</div>';
				}
				
				if($this->search)
					echo '<div class="divright"><img alt="search" title="search" id="dg_' . $this->id . '_search" class="icon" src="/css/back/icon/twotone/zoom.gif" style="cursor: pointer;" onClick="dg_' . $this->id . '_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=search&searchstr=\' + document.getElementById(\'dg_' . $this->id . '_searchfield\').value);"></div>
						<div class="divright" style="margin-right: 4px; margin-top: 2px;"><input class="smallinput" id="dg_' . $this->id . '_searchfield" type="text" size="40" value="' . $this->datasource->searchstr . '" onKeyUp="if(event.keyCode == 13)dg_' . $this->id . '_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=search&searchstr=\' + this.value);"></div>';
				echo '</div>';
			}
			if($this->picture_view)
			{
				echo '<div id="dg_table_' . $this->id . '">';
				if($this->paging && $this->current_page != 'all')
				{
					$this->datasource->limit_top = ($this->current_page - 1) * $this->perpage;
					$this->datasource->limit_count = $this->perpage;
				}
				else
				{
					$this->datasource->limit_top = "";
					$this->datasource->limit_count = $totalrows;
				}
				$data = $this->datasource->get_data();
				$data_not_grid = $data;
				if($this->datasource->type == "DATABASE")
					$data = data_description::convert_for_grid($this->datasource->db_table, $data);
				foreach($data as $key => $dataitem)
				{
					$html = $this->picture_html;
					foreach($this->fields as $one_field)
					{
						if(strstr($html, "[" . $one_field["dataname"] . ":systemthumb]"))
						{
							$html = str_replace("[" . $one_field["dataname"] . ":systemthumb]", Pictures::get_systemthumb_path($data_not_grid[$key][$one_field["dataname"]]), $html);
						}
						elseif($testpart = strstr($html, '[' . $one_field["dataname"] . '?'))
						{
							$testpart = substr($testpart, 0, strpos($testpart, ']'));
							$replace = $testpart . ']';
							$split = explode('?', $testpart);
							$testpart = $split[1];
							$split = explode(';', $testpart);
							foreach($split as $testpart)
							{
								$split2 = explode(':', $testpart);
								if($split2[0] == $dataitem[$one_field["dataname"]])
								{
									$html = str_replace($replace, $split2[1], $html);
									break;
								}
							}
						}
						else
							$html = str_replace("[" . $one_field["dataname"] . "]", $dataitem[$one_field["dataname"]], $html);
					}
					if(substr($html, 0, 4) == "<div")
					{
						$html = substr($html, 4);
						$html =  '<div id="' . $dataitem[$this->id_field] . '" ' . ((trim($this->order_field) != "")?'order="' . $dataitem[$this->order_field] . '"':'') . ' onselectstart="return false;" onclick="dg_' . $this->id . '.rowselect(this, event); ' . $this->rowclick . '" ondblclick="' . $this->rowdblclick . '"' . $html;
					}
					echo $html;
				}
				echo '<div id="dg_table_' . $this->id . '_cleardiv" style="clear:both;"></div></div>';
			}
			else
			{
				//show the headers
				echo '<table id="dg_table_' . $this->id . '" width="100%" cellpadding="0" cellspacing="0">
						<thead><tr>';
				$tmpcount = 0;
				if($this->checkbox)
				{
					echo '<td class="datagrid_field_header_left" width="20px"><input type="checkbox" onChange="dg_' . $this->id . '.main_check(this.checked)"></td>';
					$tmpcount++;
				}
				foreach($this->fields as $one_field)
				{
					if($one_field["visible"])
					{
						$tmpcount++;
						$class="datagrid_field_header";
						if($tmpcount == 1)
							$class="datagrid_field_header_left";
						if($tmpcount == $fieldcount)
							$class="datagrid_field_header_right";
						
						if($one_field["sortable"])
						{
							if($this->datasource->sort_field == $one_field["dataname"])
								echo '<td class="' . $class . '" width="' . $one_field["width"] . 'px"><a href="javascript: ' . $html_pan . '.loadContent(\'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=sort&sortfield=' . $one_field["dataname"] . '\');">' . $one_field["displayname"] . '</a><img src="' . (($this->datasource->sort_order == "ASC")?"/css/back/icon/sort_asc.gif":"/css/back/icon/sort_desc.gif") . '"/>&nbsp;</td>';
							else
								echo '<td class="' . $class . '" width="' . $one_field["width"] . 'px"><a href="javascript: ' . $html_pan . '.loadContent(\'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=sort&sortfield=' . $one_field["dataname"] . '\');">' . $one_field["displayname"] . '</a>&nbsp;</td>';
						}
						else
							echo '<td class="' . $class . '" width="' . $one_field["width"] . 'px">' . $one_field["displayname"] . '&nbsp;</td>';
					}
				}
				echo '</tr></thead><tbody>';
				
				//we start to show the real data
				if($this->datasource)
				{
					//if we have paging we limit the data
					if($this->paging && $this->current_page != 'all')
					{
						$this->datasource->limit_top = ($this->current_page - 1) * $this->perpage;
						$this->datasource->limit_count = $this->perpage;
					}
					else
					{
						$this->datasource->limit_top = "";
						$this->datasource->limit_count = $totalrows;
					}
					$data = $this->datasource->get_data();
					if($this->datasource->type == "DATABASE")
						$data = data_description::convert_for_grid($this->datasource->db_table, $data);
					
					$rowcount = 1;
					foreach($data as $dataitem)
					{
						$class = "datagrid_field_data_2";
						if($rowcount%2 == 1)
							$class = "datagrid_field_data_1";
						
						echo '<tr id="' . $dataitem[$this->id_field] . '" class="' . $class . '">';
						/*if($this->checkbox)
						{
							echo '<td width="20px" class="' . $class . '">&nbsp;<input type="checkbox" style="" name="dg_' . $this->id . '_checkbox" value="' . $dataitem[$this->id_field] . '"></td>';
						}*/
						foreach($this->fields as $one_field)
						{
							if($one_field["visible"])
								echo '<td fieldname="' . $one_field["dataname"] . '" onclick="dg_' . $this->id . '.rowselect(this, event); ' . $this->rowclick . '" ondblclick="' . $this->rowdblclick . '">' . $dataitem[$one_field["dataname"]] . '</td>';
						}
						$rowcount++;
						echo '</tr>';
					}
				}
				else
				{
					echo '<tr><td colspan="' . $fieldcount . '">There is no data to display</td></tr>';
				}
				echo '</tbody></table>';
			}
			
			//ordering functionality
			if(trim($this->order_field) != "" && $this->order_field == $this->datasource->sort_field && $this->datasource->sort_order == "ASC" && trim($this->datasource->searchstr) == "")
			{
				echo'<div style="padding-top: 4px;" class="smallgray">Tip: drag ' . ((trim("$this->data_name_plural")=="")?"elements":$this->data_name_plural) . ' to order the data</div>';
				if($this->picture_view)
					echo '<style>
						.grid_placeholder_' . $this->id . ' {' . $this->picture_placeholder_style . '}
							</style>';
				echo'<script>
					$(function() {
						var fixHelper = function(e, ui) {
							ui.children().each(function() {
								$(this).width($(this).width());
							});
							return ui;
						};
						$( "#dg_table_' . $this->id . (($this->picture_view)?'':' > tbody') . '" ).sortable({
								update: function(event, ui) {
									var order_field=\'' . $this->order_field . '\';';
									if($this->paging)
										echo 'var order_counter = ' . ((($this->current_page-1) * $this->perpage) + 1) . ';
										';
									else
										echo 'var order_counter = 1;
										';
				echo				'if(order_counter < 1)
										order_counter = 1;
									var str_ordering="";';
				if($this->picture_view)
					echo			'ui.item.parent().children().each(function(){
											if($(this).attr("id") != "dg_table_' . $this->id . '_cleardiv")
											{
												$(this).attr(\'order\', order_counter);
												if(str_ordering != "") str_ordering += "$";
												str_ordering += $(this).attr("id") + ";" + order_counter;
												order_counter++;
											}
										});';
				else
					echo			'ui.item.parent().children().each(function(){
											$(this).find(\'td[fieldname="\' + order_field + \'"]\').text(order_counter);
											
											if(str_ordering != "") str_ordering += "$";
											str_ordering += $(this).attr("id") + ";" + order_counter;
											
											order_counter++;
										});';
									//posten van de order string
				echo				'send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&dg_id=' . $this->id . '&action=saveorder&orderstr=\' + encodeURI(str_ordering), \'\', dg_afterorder);
								}' . (($this->picture_view)?', placeholder: "grid_placeholder_' . $this->id . '"':', helper: fixHelper') . '
							});
						$( "#dg_table_' . $this->id . ' > tbody" ).disableSelection();
					});
					</script>';
			}
			else
			{
				if(trim($this->order_field) != "" && !$this->picture_view)
					echo '<div style="color:#666666; padding-top: 4px; font-size: 9px;">Tip: to order the ' . ((trim("$this->data_name_plural")=="")?"data":$this->data_name_plural) . ', sort the table on field \'' . $this->order_field . '\', and remove the search term.</div>';
				elseif($this->picture_view && trim($this->order_field) != "")
					echo '<div style="color:#666666; padding-top: 4px; font-size: 9px;">Tip: to order the ' . ((trim("$this->data_name_plural")=="")?"data":$this->data_name_plural) . ', remove the search term.</div>';
			}
			//script met het html panel
			
			if($fromajax == false)
				echo '</div>
					<script language="javascript">
						window.' . $html_pan . ' = new Spry.Widget.HTMLPanel("' . $html_pan . '", { evalScripts: true });
					</script>';
		}
		
		//returns the id of the component
		function get_id()
		{
			return $this->id;
		}
		
		function handle_ajax()
		{
			switch($_GET["action"])
			{
				case "sort":
					if($this->datasource->sort_field == $_GET["sortfield"])
					{
						if($this->datasource->sort_order == "ASC")
							$this->datasource->sort_order = "DESC";
						else
							$this->datasource->sort_order = "ASC";
					}
					else
					{
						$this->datasource->sort_field = $_GET["sortfield"];
						$this->datasource->sort_order = "ASC";
					}
					$this->publish(true);
					break;
				case "page":
					$this->current_page = $_GET["pagenum"];
					$this->publish(true);
					break;
				case "refresh":
					$this->publish(true);
					break;
				case "search":
					$this->datasource->searchstr = trim($_GET["searchstr"]);
					$this->publish(true);
					break;
				case "saveorder":
					$parts = explode("$", urldecode($_GET["orderstr"]));
					
					foreach($parts as $part)
					{
						$chomps = explode(";", $part);
						DBConnect::query("UPDATE `" . $this->datasource->db_table . "` SET `" . $this->order_field . "`='" . $chomps[1] . "' WHERE `id`='" . $chomps[0] . "'", __FILE__, __LINE__);
					}
					break;
			}
		}
		
		//this function can be called from code to create a new dg
		function create_new($comp_back, $comp_name, $title, $sql, $sort_field, $sort_order, $paging, $per_page)
		{
			DBConnect::query("INSERT INTO site_component (`back`, `name`, `type_id`) VALUES('" . addslashes($comp_back) . "', '" . addslashes($comp_name) . "', '3')", __FILE__, __LINE__);
			$this->comp_id = DBConnect::get_last_inserted('site_component', 'id');
			DBConnect::query("INSERT INTO comp_datagrid(`comp_id`, `title`, `sql`, `sort_field`, `sort_order`, `paging`, `per_page`) VALUES('" . addslashes($this->comp_id) . "', '" . addslashes($title) . "', '" . addslashes($sql) . "', '" . addslashes($sort_field) . "', '" . addslashes($sort_order) . "', '" . addslashes($paging) . "', '" . addslashes($per_page) . "')", __FILE__, __LINE__);
			return $this->comp_id;
		}
		
		function update_dg($fields)
		{
			if(is_array($fields) && count($fields) > 0)
			{
				$sql = "UPDATE comp_datagrid SET ";
				$index = 0;
				foreach($fields as $dataname => $value)
				{
					if($index == 0)
						$sql .= "`" . $dataname . "`='" . addslashes($value) . "'";
					else
						 $sql .= ", `" . $dataname . "`='" . addslashes($value) . "'";
					$index++;
				}
				$sql .= " WHERE comp_id='" . $this->comp_id . "'";
				DBConnect::query($sql, __FILE__, __LINE__);
			}
		}
		
		function create_new_field($dataname, $displayname, $type, $max_size, $format, $fixed_html, $field_order)
		{
			DBConnect::query("INSERT INTO comp_datagrid_fields(`dg_id`, `dataname`, `displayname`, `type`, `max_size`, `format`, `fixed_html`, `field_order`) VALUES('" . $this->comp_id . "', '" . addslashes($dataname) . "', '" . addslashes($displayname) . "', '" . addslashes($type) . "', '" . addslashes($max_size) . "', '" . addslashes($format) . "', '" . addslashes($fixed_html) . "', '" . addslashes($field_order) . "')", __FILE__, __LINE__);
			return DBConnect::get_last_inserted('comp_datagrid_fields', 'id');
		}
		
		function update_field($dataname, $fields)
		{
			if(is_array($fields) && count($fields) > 0)
			{
				$sql = "UPDATE comp_datagrid_fields SET ";
				$index = 0;
				foreach($fields as $fieldname => $value)
				{
					if($index == 0)
						$sql .= "`" . $fieldname . "`='" . addslashes($value) . "'";
					else
						 $sql .= ", `" . $fieldname . "`='" . addslashes($value) . "'";
					$index++;
				}
				$sql .= " WHERE `dataname`='" . $dataname . "'";
				DBConnect::query($sql, __FILE__, __LINE__);
			}
		}
		
		function remove_field($dataname)
		{ 
			DBConnect::query("DELETE FROM comp_datagrid_fields WHERE `dataname`='" . $dataname . "' AND dg_id='" . $this->comp_id . "'", __FILE__, __LINE__);
		}
	}
?>