<?php
	class page_front
	{
		//functie die de pagina seo ophaalt
		static function seo($page_id, $lang = NULL)
		{
			if(!$lang) $lang = $_SESSION["LANGUAGE"];
			$return = array("title" => "", "keywords" => "", "description" => "");
			if($page_id <= 0)
			{
				//ophalen site config
				$res = DBConnect::query("SELECT `site_homecfg`.*, `site_homecfg_lang`.* FROM `site_homecfg`, `site_homecfg_lang` WHERE site_homecfg.id=site_homecfg_lang.lang_parent_id AND site_homecfg_lang.lang='" . $lang . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				$return["title"] = $row["seo_title"];
				$return["keywords"] = $row["seo_keywords"];
				$return["description"] = $row["seo_description"];
			}
			else
			{
				$res = DBConnect::query("SELECT * FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page_lang.lang = '" . $lang . "' AND site_page.id='" . $page_id . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				if($row["seo_auto"] > 0)
				{
					//template info ophalen
					$res_templ = DBConnect::query("SELECT * FROM site_pagetemplates WHERE `id`='" . $row["template_id"] . "'", __FILE__, __LINE__);
					$row_templ = mysql_fetch_array($res_templ);
					//Title = home title + page title
					$res_home = DBConnect::query("SELECT `site_homecfg`.*, `site_homecfg_lang`.* FROM `site_homecfg`, `site_homecfg_lang` WHERE site_homecfg.id=site_homecfg_lang.lang_parent_id AND site_homecfg_lang.lang='" . $lang . "'", __FILE__, __LINE__);
					$row_home = mysql_fetch_array($res_home);
					$return["title"] = stripslashes($row_home["seo_title"]);
					if(trim($row["menu_name"] != ""))
						$return["title"] .= ' - ' . stripslashes($row["menu_name"]);
					else
						$return["title"] .= ' - ' . stripslashes($row["name"]);
					//Description = home description + specified field(s)
					$return["description"] = stripslashes($row_home["seo_description"]);
					$fieldstrs = explode(":", $row_templ["seo_description"]);
					foreach($fieldstrs as $fieldstr)
					{
						if(trim($fieldstr != ""))
						{
							$str = page_front::get_seo_field($page_id, $lang, $fieldstr);
							if(strlen($str) > 150)
								$str = substr($str, 0, 150) . "... ";
							$return["description"] = " " . $str;
						}
					}
					//keywords = home keywords + page title + specified field(s)
					$return["keywords"] = stripslashes($row_home["seo_keywords"]);
					if(trim($row["menu_name"] != ""))
						$return["keywords"] .= ((trim($return["keywords"]) != '')?',':'') . stripslashes($row["menu_name"]);
					$return["keywords"] .= ((trim($return["keywords"]) != '')?',':'') . stripslashes($row["name"]);
					$fieldstrs = explode(":", $row_templ["seo_keywords"]);
					foreach($fieldstrs as $fieldstr)
					{
						if(trim($fieldstr != ""))
						{
							$str = page_front::get_seo_field($page_id, $lang, $fieldstr);
							$return["keywords"] =  ((trim($return["keywords"]) != '')?',':'') . $str;
						}
					}
				}
				else
				{
					if($row["seo_use_parent"] > 0)
						$return = page_front::seo($row["parent_id"], $lang);
					else
					{
						$return["title"] = $row["seo_title"];
						$tmp = NULL;
						if($row["seo_add_parent_description"] || $row["seo_add_parent_keywords"])
							$tmp = page_front::seo($row["parent_id"], $lang);
						if($row["seo_add_parent_description"])
							$return["description"] = $tmp["description"] . ((trim($row["seo_description"]) != "" && trim($tmp["description"]) != "")? ' ':'') . stripslashes($row["seo_description"]);
						else
							$return["description"] = stripslashes($row["seo_description"]);
						if($row["seo_add_parent_keywords"])
							$return["keywords"] = $tmp["keywords"] . ((trim($row["seo_keywords"]) != "" && trim($tmp["keywords"]) != "")? ',':'') . stripslashes($row["seo_keywords"]);
						else
							$return["keywords"] = stripslashes($row["seo_keywords"]);
					}
				}
			}
			return $return;
		}
		
		static function get_seo_field($page_id, $lang, $fieldstr)
		{
			//fieldstr = table.field/id_field (id_field bevat dan de page_id)
			$tmp = explode("/", $fieldstr);
			$tmp2 = explode(".", $tmp[0]);
			$table = $tmp2[0];
			$field = $tmp2[1];
			$id_field = $tmp[1];
			
			//ophalen van field meta
			$res = DBConnect::query("SELECT * FROM sys_database_meta WHERE `tablename`='" . addslashes($table) . "' AND `fieldname`='" . addslashes($field) . "'", __FILE__, __LINE__);
			$row_meta = mysql_fetch_array($res);
			//zoeken of de tabel een lang heeft
			$restable = DBConnect::query("SELECT * FROM sys_database_table WHERE `table`='" . addslashes($table) . "'", __FILE__, __LINE__);
			$row_table = mysql_fetch_array($restable);
			$sql = "";
			if($row_table["lang_dep"] > 0 && $row_meta["lang_dep"] > 0)
			{
				$sql = "SELECT " . $table . "_lang." . $field . " FROM `" . $table . "`, `" . $table . "_lang` WHERE " . $table . ".id = " . $table . "_lang.lang_parent_id AND " . $table . "_lang.lang='" . $lang . "' AND " . $table . "." . $id_field . "='" . $page_id . "' LIMIT 0, 1";
			}
			else
				$sql = "SELECT `" . $field . "` FROM `" . $table . "` WHERE `" . $id_field . "`='" . $page_id . "' LIMIT 0, 1";
			$res = DBConnect::query($sql, __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				//nog even checken of het een html field is
				if($row_meta["datadesc"] == 3 || $row_meta["datadesc"] == 4)
					return strip_tags(stripslashes($row[$field]));
				else
					return stripslashes($row[$field]);
			}
			else
				return "";
		}
		
		static function publish($page_id, $lang)
		{
			//get the page info
			//check for copy
			$res = DBConnect::query("SELECT site_page.* FROM site_page WHERE site_page.id='" . $page_id . "'", __FILE__, __LINE__);
			$row_page = mysql_fetch_array($res);
			if($row_page["copyof"]<=0)
			{
				$res = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.id='" . $page_id . "' AND site_page_lang.lang='" . $lang . "'", __FILE__, __LINE__);
				$row_page = mysql_fetch_array($res);
			}
			$res = DBConnect::query("SELECT * FROM site_pagetemplates WHERE id='" . $row_page["template_id"] . "'", __FILE__, __LINE__);
			$row_templ = mysql_fetch_array($res);
			$res = DBConnect::query("SELECT * FROM site_superhtml WHERE id='" . $row_templ["superhtml"] . "'", __FILE__, __LINE__);
			$row_html = mysql_fetch_array($res);
			
			//create the html
			$html = stripslashes($row_html["html"]);
			//we checken of we de standaard blocks moeten ophalen of niet
			$sql_blocks = "";
			if($row_templ["use_superhtml_blocks"] > 0)
				$sql_blocks = "SELECT * FROM site_superhtml_standardblocks WHERE superhtml_id='" . $row_html["id"] . "' ORDER BY `area`, `order`";
			else
				$sql_blocks = "SELECT * FROM site_pagetemplates_blocks WHERE pagetemplate='" . $row_templ["id"] . "' ORDER BY `area`, `order`";
			$res_blocks = DBConnect::query($sql_blocks, __FILE__, __LINE__);
			$areahtml = "";
			$last_area = "";
			while($row_bl = mysql_fetch_array($res_blocks))
			{
				if($row_bl["area"] != $last_area)
				{
					if($last_area != "")
					{
						//we updaten de html
						$html = str_replace('[' . $last_area . ']', $areahtml, $html);
						$areahtml = "";
					}
				}	
				$last_area = $row_bl["area"];
				
				$res = DBConnect::query("SELECT * FROM `site_blocks` WHERE id = '" . $row_bl["block"] . "'", __FILE__, __LINE__);
				if($row_block = mysql_fetch_array($res))
				{
					ob_start();
					include 'snippets/blocks/snippet.' . $row_block["snippet"] . '.php';
					$tmp = ob_get_contents();
					ob_end_clean();
					$areahtml .= $tmp;
				}
			}
			if($last_area != "")
			{
				//we updaten de html
				$html = str_replace('[' . $last_area . ']', $areahtml, $html);
				$areahtml = "";
			}
			
			//nu nog de content
			ob_start();
			if(check_inline())
			{
				$buttons = array(array("button" => "edit", "type" => "page", "data1" => $page_id, "data2" => "", "data3" => "", "help" => "Edit page"));
				inline_edit::display_toolbox($buttons, "top right", 50);
			}
			
			include 'snippets/page/snippet.' . $row_templ["script"] . '.php';
			$tmp = ob_get_contents();
			ob_end_clean();
			$html = str_replace('[content]', $tmp, $html);
			
			//LANG LINKS
			if(mainconfig::$multilanguage)
			{
				ob_start();
				page_front::write_lang_links($page_id, "");
				$tmp = ob_get_contents();
				ob_end_clean();
				$html = str_replace('[langlinks]', $tmp, $html);
			}
			
			echo $html;
		}
		
		//functie die de language links schrijft
		static function write_lang_links($page_id, $splitter)
		{
			$counter = 0;
			$toecho = "";
			foreach(mainconfig::$languages as $abr => $language)
			{
				$res = DBConnect::query("SELECT * FROM site_page_lang WHERE lang_parent_id='" . addslashes($page_id) . "' AND lang='" . $abr . "' AND published > '0'", __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					if($counter > 0)
						$toecho .= $splitter;
					if($_SESSION["LANGUAGE"] == $abr)
						$toecho .= '<div class="current"><span>' . strtoupper($abr) . '</span></div>';
					else
						$toecho .= '<div><a href="' . url_front::create_url($page_id, strtoupper($abr)) . '">' . strtoupper($abr) . '</a></div>';
					$counter++;
				}
			}
			if($counter > 1)
				echo $toecho;
		}
		
		//functie die de menu items opphaalt van een bepaalde root
		static function get_menu($parent_id, $depth = 1)
		{
			$return = array();
			$res_root = DBConnect::query("SELECT site_page.* FROM site_page WHERE site_page.parent_id='" . $parent_id . "' AND site_page.hide_in_menu <= '0' AND front_protected <= '0' ORDER BY `menu_order`", __FILE__, __LINE__);
			//
			while($row_root = mysql_fetch_array($res_root))
			{
				//check for copy
				$res = NULL;
				if($row_root["copyof"]>0)
					$res = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.parent_id='" . $parent_id . "' AND site_page_lang.published > '0' AND site_page.hide_in_menu <= '0' AND front_protected <= '0' AND site_page_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND site_page.id='" . $row_root["copyof"] . "' ORDER BY `menu_order`", __FILE__, __LINE__);
				else
					$res = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.parent_id='" . $parent_id . "' AND site_page_lang.published > '0' AND site_page.hide_in_menu <= '0' AND front_protected <= '0' AND site_page_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND site_page.id='" . $row_root["id"] . "' ORDER BY `menu_order`", __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					$link = url_front::create_url($row["id"]);
					$caption = htmlentities(stripslashes($row["menu_name"]));
					if(trim($row["menu_name"]) == "")
						$caption = htmlentities(stripslashes($row["name"]));
					$inurl = (url_front::in_url($row["id"]));
					$return[] = array("id" => $row["id"], "link" => $link, "caption" => $caption, "alt" => stripslashes($row["menu_alt"]), "inurl" => $inurl, "template_id" => $row["template_id"]);
				}
			}
			return $return;
		}
		
		//functie die de data pagineert
		static function paged_list($sql, $html_item, $html_between, $page_url, $perpage, $pagenums)
		{
			$return = array("paging_html" => "", "html" => "");
			$paging_html = "";
			$html = "";
			$tmp = explode("/", url_front::$url_extra);
			$currentpage = ((isset($tmp[1]) && is_numeric($tmp[1]))?$tmp[1]:1);
			$res_all = DBConnect::query($sql, __FILE__, __LINE__);
			$total_data = mysql_num_rows($res_all);
			$total_pages = 1;
			if($total_data > 0)
				$total_pages = ceil($total_data/$perpage);
			
			
			//PAGING
			$paging_html .= '<div class="pagingnums">';
			if($total_pages > 1)
			{
				$startnum = 1;
				$stopnum = $pagenums;
				if($currentpage > ($pagenums/2))
				{
					$startnum = (int)($currentpage-($pagenums/2));
					$stopnum = (int)($currentpage+($pagenums/2));
				}
				if($currentpage > ($total_pages - ($pagenums/2)))
				{
					$startnum = $total_pages-$pagenums+1;
					
					$stopnum = $total_pages;
				}
				if($startnum < 1)
						$startnum = 1;
						
				if($startnum != 1)
					$paging_html .= '<span class="pagingnums_dots_before">...</span>';
				for($i = $startnum; $i <= $stopnum ; $i++)
				{
					if($i == $currentpage)
						$paging_html .= '<span>' . $i . '</span>';
					else
						$paging_html .= '<a href="' . $page_url . '/' . $i . '">' . $i . '</a>';
				}
				if($stopnum != $total_pages)
					$paging_html .= '<span class="pagingnums_dots_after">...</span>';
			}
			$paging_html .= '</div>';
			
			//CONTENT HTML
			$sql .= " LIMIT " . (($currentpage-1)*$perpage) . ", " . $perpage;
			$res_data = DBConnect::query($sql, __FILE__, __LINE__);
			$counter = 0;
			while($row_data = fetch_db($res_data))
			{
				if($counter > 0) $html .= $html_between;
				$counter++;
				
				$tmp_html = $html_item;
				foreach($row_data as $key => $value)
					$tmp_html = str_replace("[" . $key . "]", $value, $tmp_html);
				//zoeken naar condities
				//[*veld][\] = moet weg als het veld leeg is
				//[!veld][\] = moet er staan als het veld niet leeg is
				$matches = array();
				preg_match_all("/\[\*[^\]]*\][^\[]*\[\\\]/", $tmp_html, $matches);
				foreach($matches[0] as $match)
				{
					//veld opzoeken
					$veldnaam = substr($match, 2, (strpos($match,"]")-2));
					if(trim($row_data[$veldnaam]) == "")
						$tmp_html = preg_replace("/\[\*" . str_replace(".", "\.", $veldnaam) . "\][^\[]*\[\\\]/", "", $tmp_html);
				}
				preg_match_all("/\[![^\]]*\][^\[]*\[\\\]/", $tmp_html, $matches);
				foreach($matches[0] as $match)
				{
					//veld opzoeken
					$veldnaam = substr($match, 2, (strpos($match,"]")-2));
					if(trim($row_data[$veldnaam]) != "")
						$tmp_html = preg_replace("/\[!" . str_replace(".", "\.", $veldnaam) . "\][^\[]*\[\\\]/", "", $tmp_html);
				}
				$tmp_html = preg_replace("/\[[\*|!][^\]]*\]/", "", $tmp_html);
				$tmp_html = preg_replace("/\[\\\]/", "", $tmp_html);
				$html .= $tmp_html;
			}
			
			return array("paging_html" => $paging_html, "html" => $html);
		}
		
		//functie die door de pagina's zoekt
		static function search($string)
		{
		
		}
		
		//functie die opzoekt of een pagina een copie is of niet zoja geeft het de echte id terug
		static function get_real_page_id($id)
		{
			$res = DBConnect::query("SELECT site_page.copyof FROM site_page WHERE `id`='" . addslashes($id) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			if($row["copyof"]>0)
			{
				return $row["copyof"];
			}
			else
				return $id;
		}
	}
?>