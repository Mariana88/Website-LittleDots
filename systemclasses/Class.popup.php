<?php
//a popup is a kind of a component. But the big difference is that it does not have a db configuration. 
//the configuration happens in the code itself. --> so it does not have a creator functionality
//another big difference is that once it is displays it handles every interaction with ajax so the configuration is saved in session variables.
//although it does not have a database configuration, it can handle database stuff.
	abstract class popup
	{
		protected $id;
		
		function __construct($id, $width, $height)
		{
			$this->id = $id;
			if(!is_array($_SESSION["popup_vars"]))
			{
				$_SESSION["popup_vars"] = array();
				$_SESSION["popup_vars"][$id] = array();
			}
			if(isset($width))
				$_SESSION["popup_vars"][$id]["width"] = $width;
			if(isset($height))
				$_SESSION["popup_vars"][$id]["height"] = $height;
		}
		
		//publish the component
		abstract public function publish($in_popup, $from_ajax = false);
		
		abstract public function handle_ajax();
		
		public function set_config($name, $value)
		{
			$_SESSION["popup_vars"][$this->id][$name] = $value;
		}
		
		public function get_config($name)
		{
			return $_SESSION["popup_vars"][$this->id][$name];
		}
		
		protected function publish_start($in_popup, $from_ajax = false)
		{
			$panelname = "popup_" . $this->id . "_html_panel";
			/*if(!$from_ajax)
				echo '<div width="' . $this->get_config("width") . '" height="' . $this->get_config("height") . '" id="' . $panelname . '">';*/
			return $panelname;
		}
		
		protected function publish_end($in_popup, $from_ajax = false)
		{
			$panelname = "popup_" . $this->id . "_html_panel";
			/*if(!$from_ajax)
				echo '</div>
						<script language="javascript">
							var ' . $panelname . ' = new Spry.Widget.HTMLPanel("' . $panelname . '", { evalScripts: true });
						</script>';*/
		}
		
		//function that creates the right popup from an id and returns it
		static function create_popup_from_id($the_id)
		{
			if(isset($_SESSION["popup_vars"][$the_id]["classname"]))
			{
				$return = new $_SESSION["popup_vars"][$the_id]["classname"]($the_id, $_SESSION["popup_vars"][$the_id]["width"], $_SESSION["popup_vars"][$the_id]["height"]);
				
				return $return;
			}
			else
				return NULL;
		}
	}
?>