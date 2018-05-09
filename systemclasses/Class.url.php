<?php
	//URL class makes it easy to create url's in the site. It knows how the url query system works
	class url
	{
		static $page;
		static $page_actions;
		
		static public function analyse_url()
		{
			if(trim($_GET["q"]) != "")
			{
				if(substr($_GET["q"], 0, 1) == "/")
					$_GET["q"] = substr($_GET["q"], 1);
				$parts = explode("/", $_GET["q"]);
				url::$page = $parts[0];
				url::$page_actions = array();
				for($i = 1 ; $i < count($parts) ; $i++)
				{
					url::$page_actions[] = $parts[$i];
				}
				if (url::$page=="")
					url::$page = "content";
			}
			else
			{
				url::$page = "content";
				url::$page_actions = array();
			}
		}
	}
?>
