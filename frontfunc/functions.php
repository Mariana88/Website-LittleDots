<?php
	//fuctie die checked of we inline edit aan staat en of het mag
	function check_inline()
	{
		return (login::check_login() && $_SESSION["inline_edit_on"] == true);
	}
	
	//deze functie gaat een array ophalen uit een sql result en die converteren naar direct outputbare data
	function fetch_db($res)
	{
		if($raw_row = mysql_fetch_array($res, MYSQL_ASSOC))
		{
			if(!is_array($_REQUEST["output_desc"])) $_REQUEST["output_desc"] = array();
			$return = array();
			for($i = 0 ; $i < mysql_num_fields($res) ; $i++)
			{
				$field_table = mysql_field_table($res, $i);
				$field_name_result = mysql_field_name($res, $i);
				$field_name = $field_name_result;
				if(strstr($field_name, "."))
				{
					$tmp = explode(".", $field_name);
					$field_table = $tmp[0];
					$field_name = $tmp[1];
				}
				if(strstr($field_table, "_lang"))
					$field_table = str_replace("_lang", "", $field_table);
				if(!isset($_REQUEST["output_desc"][$field_table . "." . $field_name]))
				{
					//ophalen van description
					$res_desc = DBConnect::query("SELECT `sys_database_meta`.*, sys_datadescriptions.name as 'descname' FROM sys_database_meta, sys_datadescriptions WHERE sys_database_meta.datadesc=sys_datadescriptions.id AND sys_database_meta.tablename='" . $field_table . "' AND sys_database_meta.fieldname='" . $field_name . "'", __FILE__, __LINE__);
					$_REQUEST["output_desc"][$field_table . "." . $field_name] = mysql_fetch_array($res_desc, MYSQL_ASSOC);
				}
				switch($_REQUEST["output_desc"][$field_table . "." . $field_name]["descname"])
				{
					case "VARCHAR":
					case "AUTOCOMPLETE":
						$return[$field_name_result] = htmlentities(stripslashes($raw_row[$field_name_result]));
						break;
					case "TEXT":
						$return[$field_name_result] = nl2br(htmlentities(stripslashes($raw_row[$field_name_result])));
						$return[$field_name_result . '_raw'] = $raw_row[$field_name_result];
						break;
					case "HTML BASIC":
					case "HTML FULL":
					case "LINK":
					case "EMAIL":
					case "PASSWORD":
						$return[$field_name_result] = stripslashes($raw_row[$field_name_result]);
						break;
					case "HIDDEN ID":
					case "HIDDEN VARCHAR":
					case "HIDDEN NUMERIC":
					case "NUMERIC":
						$return[$field_name_result] = $raw_row[$field_name_result];
						break;
					case "DATE":
						$return[$field_name_result] = date("d.m.Y", $raw_row[$field_name_result]);
						break;
					case "TIME":
						$options = data_description::options_convert_to_array($_REQUEST["output_desc"][$field_table . "." . $field_name]["datadesc"], $_REQUEST["output_desc"][$field_table . "." . $field_name]["data_options"]);
						$format = "";
						$onlytime = $raw_row[$field_name_result] % 86400;
						if($options["hours"])
							$format .= ((floor($onlytime/3600)<10)?'0':'') . (string)(floor($onlytime / 3600));
						if($options["minutes"])
							$format .= (($format != "")?':':'') . ((floor(($onlytime % 3600)/60)<10)?'0':'') . (string)(floor(($onlytime % 3600)/60));
						if($options["seconds"])
							$format .= (($format != "")?':':'') . ((($onlytime % 60)<10)?'0':'') . (string)($onlytime % 60);
							
						$return[$field_name_result] = $format;
						/*if($options["hours"])
							$format = "H";
						if($options["minutes"])
							$format .= (($format != "")?':':'') . "i";
						if($options["seconds"])
							$format .= (($format != "")?':':'') . "s";
						$return[$field_name_result] = date($format, $raw_row[$field_name_result]);*/
						break;
					case "YESNO":
						$return[$field_name_result] = ($raw_row[$field_name_result] > 0);
						break;
					case "FILE":
					case "PICTURE":
					case "VIDEO":
					case "AUDIO":
						$return[$field_name_result] = files_front::get_dbfile_path($raw_row[$field_name_result]);
						break;
					case "ENUM LANGUAGE":
					case "ENUM FROM TABLE":
					case "ENUM PAGES FROM TEMPLATE":
					case "ENUM PAGES FROM PARENT":
						$return[$field_name_result] = $raw_row[$field_name_result];
						break;
					case "ENUM STATIC":
						$return[$field_name_result] = stripslashes($raw_row[$field_name_result]);
						break;
					case "PICTURE FORMAT":
						$return[$field_name_result] = files_front::get_dbfileformat_path($raw_row[$field_name_result]);
						break;
					case "WORDLIST":
						$options = data_description::options_convert_to_array($_REQUEST["output_desc"][$field_table . "." . $field_name]["datadesc"], $_REQUEST["output_desc"][$field_table . "." . $field_name]["data_options"]);
						$tmp = explode($options["seperation"], stripslashes(($raw_row[$field_name_result])));
						$return[$field_name_result] = array();
						foreach($tmp as $t)
							$return[$field_name_result][] = htmlentities($t);
						break;
					case "WORDDATALIST":
						$return[$field_name_result] = explode(";", $raw_row[$field_name_result]);
						break;
					default:
						$return[$field_name_result] = stripslashes($raw_row[$field_name_result]);
				}
			}
			return $return;
		}
		else
			return false;
	}
	
	function staticword($group, $word)
	{
		//echo "SELECT site_static_words_lang.vertaling FROM site_static_words, site_static_words_lang, site_static_words_group WHERE site_static_words.id=site_static_words_lang.lang_parent_id AND site_static_words.group=site_static_words_group.id AND site_static_words_lang.lang='" . $_SESSION["language"] . "' AND site_static_words.identifier='" . addslashes($word) . "' AND site_static_words_group.group='" . addslashes($group) . "'";
		$res = DBConnect::query("SELECT site_static_words_lang.vertaling FROM site_static_words, site_static_words_lang, site_static_words_group WHERE site_static_words.id=site_static_words_lang.lang_parent_id AND site_static_words.group=site_static_words_group.id AND site_static_words_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND site_static_words.identifier='" . addslashes($word) . "' AND site_static_words_group.group='" . addslashes($group) . "'", __FILE__, __LINE__);
		$row = mysql_fetch_array($res);
		return htmlentities(stripslashes($row["vertaling"]));
	}
	
	function format_date_ma($timestamp)
	{
		$names = array();
		switch($_SESSION["LANGUAGE"])
		{
			case 'NL':
				$names = array("ZO", "MA", "DI", "WO", "DO", "VR", "ZA");
				break;
			case 'EN':
				$names = array("SUN", "MnO", "TUES", "WED", "THURS", "FRI", "SAT");
				break;
			case 'FR':
				$names = array("DIM", "LUN", "MAR", "MER", "JEU", "VEN", "SAM");
				break;
		}
		
		$return = $names[date('w', $timestamp)] . '&nbsp;';
		$return .= date('j.n', $timestamp);
		return $return;
	}
	
	function echo_video($link, $big = true)
	{
		$vidid = files_front::get_video_id($link);
		if(strstr($link, "youtube.com"))
		{
			echo '<iframe width="616" height="346" src="http://www.youtube.com/embed/' . $vidid . '?autoplay=' . (($autoplay==true)?"1":"0") . '" frameborder="0" allowfullscreen wmode="Transparent"></iframe>';
		}
		elseif(strstr($link, "vimeo.com"))
		{
			echo '<iframe src="http://player.vimeo.com/video/' . $vidid . '?autoplay=false" width="616" height="346" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		}	
	}
	
	function autodetect_language()
	{
		$langs = array();

		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			// break up string into pieces (languages and q factors)
			preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
		
			if (count($lang_parse[1])) {
				// create a list like "en" => 0.8
				$langs = array_combine($lang_parse[1], $lang_parse[4]);
				
				// set default to 1 for any without q factor
				foreach ($langs as $lang => $val) {
					if ($val === '') $langs[$lang] = 1;
				}
		
				// sort list based on value	
				arsort($langs, SORT_NUMERIC);
			}
			
			foreach ($langs as $lang => $val) {
				if (strpos($lang, 'nl') === 0)
				{
					return 'NL';
				} 
				else if (strpos($lang, 'en') === 0) {
					return 'EN';
				} 
				else if (strpos($lang, 'fr') === 0) {
					return 'FR';
				} 
			}
		}
		else
		{
			return 'EN';	
		}
	}
?>