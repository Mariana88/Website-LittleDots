<?php
	
	class tableeditor extends popup
	{
		function __construct($id, $width, $height)
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "tableeditor");
		}
		
		public function set_table_html($html)
		{
			$this->set_config("html", $html);
		}
		
		public function set_styles($styles)
		{
			$this->set_config("styles", $styles);
		}
		
		public function set_postname($postname)
		{
			$this->set_config("postname", $postname);
		}
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			$this->publish_start($in_popup, $from_ajax);
			
			//we show the table
			echo '<div style="border: 1px solid #4A6867; padding:4px;">
				<div class="iconcontainer">
					<div class="divleft">
						<input style="width:140px;" class="smallinput" type="text" id="table_editor_' . $this->id . '_fieldinput" value="" onkeyup="if(event.keyCode == 13) table_editor_' . $this->id . '.changeinput();" disabled/>
						<span>Width: </span><input style="width:25px;" class="smallinput" type="text" id="table_editor_' . $this->id . '_fieldwidth" value="" onkeyup="if(event.keyCode == 13) table_editor_' . $this->id . '.changewidth();" disabled/>
						<span>Height: </span><input style="width:25px;" class="smallinput" type="text" id="table_editor_' . $this->id . '_fieldheight" value="" onkeyup="if(event.keyCode == 13) table_editor_' . $this->id . '.changeheight();" disabled/>
						<span>Style: </span><select style="width:80px;" class="smallinput" id="table_editor_' . $this->id . '_fieldstyle" value="" onchange="table_editor_' . $this->id . '.changestyle();" disabled><option value=""></option>';
			if(is_array($this->get_config("styles")))
			{
				foreach($this->get_config("styles") as $key => $one_style)
				{
					echo '<option value="' . $key . '">' . $one_style . '</option>';
				}
			}
			echo		'</select><input type="hidden" name="' . $this->get_config("postname") . '" id="' . $this->get_config("postname") . '" value=""/>
					</div>
					<div class="divright">
						<span>Row: </span><img src="/css/back/icon/twotone/plus.gif" class="icon" style="cursor:pointer;" onclick="table_editor_' . $this->id . '.addrow();"/>
						<img id="table_editor_' . $this->id . '_removerow" src="/css/back/icon/twotone/gray/trash.gif" class="icon"/>&nbsp;
						<span>Column: </span><img src="/css/back/icon/twotone/plus.gif" class="icon" style="cursor:pointer;" onclick="table_editor_' . $this->id . '.addcolumn();"/>
						<img id="table_editor_' . $this->id . '_removecolumn" src="/css/back/icon/twotone/gray/trash.gif" class="icon"/>
					</div>
				</div>
				<div id="table_editor_' . $this->id . '" style="border-left: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC;">' . $this->get_config("html") . '
				</div>
				</div>';
			
			echo '<script>
					window.table_editor_' . $this->id . ' = new tableeditor(\'' . $this->id . '\', \'' . $this->get_config("postname") . '\');
				</script>';
			$this->publish_end($in_popup, $from_ajax);
		}
		
		public function handle_ajax()
		{
			//for the browser we have to be logged in in the admin section
			if(!login::check_login())
				return "";
		}
	}
?>