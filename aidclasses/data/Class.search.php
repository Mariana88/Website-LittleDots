<?php
	class search
	{
		static function search_location_string($page_id, $lang = "")
		{
			if($lang == "")
				$lang = $_SESSION["LANGUAGE"];
			$res_root = DBConnect::query("SELECT * FROM `site_page` WHERE `id`='" . addslashes($page_id) . "'", __FILE__, __LINE__);
			$row_root = mysql_fetch_array($res_root);
			$html1 = "";
			while($row_root)
			{
				$res_page = DBConnect::query("SELECT * FROM `site_page_lang` WHERE `lang_parent_id`='" . addslashes($row_root["id"]) . "' AND `lang`='" . addslashes($lang) . "'", __FILE__, __LINE__);
				$row_page = mysql_fetch_array($res_page);
				if($html1 == "")
					$html1 = $row_page["name"] . $html1;
				else
					$html1 = $row_page["name"] . ' &gt; ' . $html1;
				
				$res_root = DBConnect::query("SELECT * FROM `site_page` WHERE `id`='" . $row_root["parent_id"] . "'", __FILE__, __LINE__);
				$row_root = mysql_fetch_array($res_root);
			}
			return $html1;
		}
	}
?>