<?php
	//URL class makes it easy to create url's in the site. It knows how the url query system works
	class url_front
	{
		static $pages;
		static $url_analysed;
		static $lang;
		static $url_extra;
		static $page_action;
		static $ajax_refresh;
		
		static function create_url($page_id, $lang = NULL)
		{
			if(!$lang)
				$lang = $_SESSION["LANGUAGE"];
			if(!isset(url_front::$url_analysed))
				url_front::analyse_url();
			
			//echo "SELECT site_page.menu_name, site_page_root.parent_id FROM `site_page_root`, `site_page` WHERE site_page_root.id = site_page.root_id AND site_page_root.id='" . $page_id . "' AND site_page.lang='" . $_SESSION["LANGUAGE"] . "'";
			$return="";
			//check for copy
			$result = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . $page_id . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($result);
			if($row["copyof"]>0)
				$result = DBConnect::query("SELECT site_page_lang.name FROM `site_page_lang` WHERE site_page_lang.lang_parent_id='" . $row["copyof"] . "' AND site_page_lang.lang='" . $lang . "'", __FILE__, __LINE__);
			else
				$result = DBConnect::query("SELECT site_page_lang.name FROM `site_page_lang` WHERE site_page_lang.lang_parent_id='" . $page_id . "' AND site_page_lang.lang='" . $lang . "'", __FILE__, __LINE__);
			$row_lang = mysql_fetch_array($result);
			while($row)
			{
				$return = "/" . urlencode(str_replace(" ", "_", $row_lang["name"])) . $return;

				$result = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . $row["parent_id"] . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($result);
				if($row["copyof"]>0)
					$result = DBConnect::query("SELECT site_page_lang.name FROM `site_page_lang` WHERE site_page_lang.lang_parent_id='" . $row["copyof"] . "' AND site_page_lang.lang='" . $lang . "'", __FILE__, __LINE__);
				else
					$result = DBConnect::query("SELECT site_page_lang.name FROM `site_page_lang` WHERE site_page_lang.lang_parent_id='" . $row["id"] . "' AND site_page_lang.lang='" . $lang . "'", __FILE__, __LINE__);
				$row_lang = mysql_fetch_array($result);
				//$result = DBConnect::query("SELECT site_page_lang.name, site_page.parent_id FROM `site_page`, `site_page_lang` WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.id='" . $row["parent_id"] . "' AND site_page_lang.lang='" . $_SESSION["LANGUAGE"] . "'", __FILE__, __LINE__);
			}
			if(mainconfig::$multilanguage)
			{
				$return = "/" . $lang . $return;
			}
			return $return;
		}
		
		static public function get_last_page_id()
		{
			if(!isset(url_front::$url_analysed))
				url_front::analyse_url();
			if(is_array(url_front::$pages))
			{
				return url_front::$pages[count(url_front::$pages) - 1]["id"];
			}
			else
				return false;
		}
		
		static public function in_url($page_id)
		{
			if(!isset(url_front::$url_analysed))
				url_front::analyse_url();
			if(is_array(url_front::$pages))
			{
				foreach(url_front::$pages as $test)
				{
					if($test["id"] == $page_id)
						return true;
				}
				return false;
			}
			else
				return false;
		}
		
		static public function analyse_url()
		{
			if(!mainconfig::$multilanguage)
				url_front::$lang = mainconfig::$standardlanguage;
			$url_parsed = parse_url($_SERVER["REQUEST_URI"]);
			$parts = explode("/", $url_parsed["path"]);
			if($parts[(count($parts)) - 1] == "ajax_refresh")
			{
				url_front::$ajax_refresh=true;
				unset($parts[(count($parts)) - 1]);
			}
			url_front::$url_extra = "";
			$counter = -1;
			$parent_id = 0;
			url_front::$pages = array();
			foreach($parts as $part)
			{
				$part = urldecode($part);
				if(trim($part) != "")
				{
					if($counter == -1 && mainconfig::$multilanguage)
					{
						url_front::$lang = strtoupper(trim($part));
						foreach(mainconfig::$languages as $abr => $lang)
						{
							if($abr == strtoupper(trim($part)))
							{
								$_SESSION["LANGUAGE"] = strtoupper(trim($part));
								break;
							}
						}
						if(!isset($_SESSION["LANGUAGE"]))
							$_SESSION["LANGUAGE"] = mainconfig::$standardlanguage;
						$counter++;
					}
					else
					{
						//we zoeken via de root
						//echo "SELECT site_page_root.id, site_page.id as 'page_id', site_page.menu_name FROM site_page_root, site_page WHERE site_page_root.id=site_page.root_id AND site_page_root.back='0' AND site_page_root.parent_id='" . $parent_id . "' AND site_page.lang='" . url_front::$lang . "' AND site_page.menu_name='" . str_replace("_", " ", $part) . "'";
						$res = DBConnect::query("SELECT site_page.id, site_page.copyof, site_page_lang.lang_id, site_page_lang.name FROM site_page, site_page_lang WHERE site_page.id=site_page_lang.lang_parent_id AND site_page.parent_id='" . $parent_id . "' AND site_page_lang.lang='" . url_front::$lang . "' AND site_page_lang.name='" . addslashes(urldecode(str_replace("_", " ", $part))) . "'", __FILE__, __LINE__);
						if($row = mysql_fetch_array($res))
						{
							if($counter < 0) $counter++;
							url_front::$pages[$counter] = array("id" => $row["id"], "lang_id" => $row["lang_id"], "name" => $row["name"]);
							$parent_id = $row["id"];
							$counter++;
						}
						else
						{
							//juist checken of het geen copy is.
							$res = DBConnect::query("SELECT * FROM site_page WHERE parent_id='" . $parent_id . "' AND copyof>'0'",__FILE__, __LINE__);
							$copyfound = false;
							while($row = mysql_fetch_array($res))
							{
								//we zoeken naar de copyof (=master) en checken of zijn name in de url staat
								$res_master = DBConnect::query("SELECT site_page.id, site_page.copyof, site_page_lang.lang_id, site_page_lang.name FROM site_page, site_page_lang WHERE site_page.id=site_page_lang.lang_parent_id AND site_page.id='" . $row["copyof"] . "' AND site_page_lang.lang='" . url_front::$lang . "' AND site_page_lang.name='" . addslashes(urldecode(str_replace("_", " ", $part))) . "'",__FILE__, __LINE__);
								if($row_master = mysql_fetch_array($res_master))
								{
									if($counter < 0) $counter++;
									url_front::$pages[$counter] = array("id" => $row["id"], "lang_id" => $row_master["lang_id"], "name" => $row_master["name"]);
									$parent_id = $row["id"];
									$counter++;
								}
							}
							if(!$copyfound)
								url_front::$url_extra .= "/" . $part;
						}
					}
				}
			}
			
			if(count(url_front::$pages) == 0)
			{
				//we get the homepage
				url_front::$lang = mainconfig::$standardlanguage;
				$res = DBConnect::query("SELECT * FROM site_homecfg", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				$res = DBConnect::query("SELECT site_page.id, site_page_lang.lang_id, site_page_lang.name FROM site_page, site_page_lang WHERE site_page.id=site_page_lang.lang_parent_id AND site_page_lang.lang='" . url_front::$lang . "' AND site_page.id='" . $row["homepage"] . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);

				url_front::$pages[0] = array("id" => $row["id"], "lang_id" => $row["lang_id"], "name" => $row["name"]);
			}
			if(!isset($_SESSION["LANGUAGE"]))
				$_SESSION["LANGUAGE"] = mainconfig::$standardlanguage;
			url_front::$url_analysed = true;
			
		}
	}
?>