<?php
	//check for rights!!!
	require_once("aidclasses/data/Class.search.php");
	if(!login::right("backpage_content_pages", "view"))
	{
		echo "NORIGHTS:You don't have the permission to view or edit pages";
		exit();
	}

	
	switch($_GET["action"])
	{
		case "loadpage":
			//zoeken of we dit wel mogen
			$res_page = DBConnect::query("SELECT * FROM site_page WHERE id='" . $_GET["edit_page_id"] . "'", __FILE__, __LINE__);
			$row_page = mysql_fetch_array($res_page);
			if(!login::right("template", "edit", $row_page["template_id"]))
			{
				//ophalen van de template title
				$res = DBConnect::query("SELECT `name` FROM `site_pagetemplates` WHERE `id`='" . $row_page["template_id"] . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				echo "<div style=\"text-align:center; color: #FFFFFF;\">NORIGHTS: You don't have the permission to edit a '" . $row["name"] . "' page</div>";
				break;
			}
			$_REQUEST["output_html"] = true;
			
			if(isset($_GET["edit_page_lang"]))
				$_SESSION["CMS_EDIT_LANG"] = $_GET["edit_page_lang"];
			
			$page = new page($_GET["edit_page_id"]);
			$_SESSION["CURRENT_PAGE_EDIT"] = $_GET["edit_page_id"];
			if(isset($page))
			{
				$page->creator();
			}
			break;
		case "refresh_tree":
			page::publish_tree_front();
			break;
		case "save":
			if(!login::right("backpage_content_pages", "edit"))
			{
				echo "NORIGHTS:You don't have the permission to edit pages";
				break;
			}
			//checken of de name uniek is in elke taal
			$data = $_POST["data"];
			$data = urldecode($data);
			$data = str_replace("___EUR___", "€", $data);
			$data = utf8_encode($data);
			$data = stripslashes($data);
			$data = str_replace("___AMP___", "&", $data);
			$data = str_replace("___QUEST___", "?", $data);
			$data = str_replace("___HEK___", "#", $data);
			$data = str_replace("___PLUS___", "+", $data);
			
			
			$extra_errors = array();
			$doc = new SimpleXMLElement($data);
			$result = $doc->xpath("//table[@id='site_page']/field[@id='site_page.parent_id']");
			$parent_id = $result[0];
			$result = $doc->xpath("//table[@id='site_page']/field[@id='site_page.id']");
			$page_id = $result[0];
			$result = $doc->xpath("//table[@id='site_page']/field[@id='site_page.template_id']");
			$template_id = $result[0];
			
			if(!login::right("template", "edit", $template_id))
			{
				//ophalen van de template title
				$res = DBConnect::query("SELECT `name` FROM `site_pagetemplates` WHERE `id`='" . addslashes($template_id) . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				echo "NORIGHTS:You don't have the permission to edit a '" . $row["name"] . "' page";
				break;
			}
			foreach(mainconfig::$languages as $abr => $lang)
			{
				//zoeken naar value
				$result = $doc->xpath("//table[@id='site_page']/lang[@id='" . $abr . "']/field[@id='site_page.name']");
				$value = $result[0];
				$result = $doc->xpath("//table[@id='site_page']/lang[@id='" . $abr . "']/field[@id='site_page.published']");
				if($result[0] > 0)
				{
					$rescheck = DBConnect::query("SELECT `id` FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page_lang.published='1' AND site_page.parent_id='" . addslashes($parent_id) . "' AND site_page_lang.name='" . addslashes($value) . "' AND site_page.id <> '" . $page_id . "' AND site_page_lang.lang='" . $abr . "'", __FILE__, __LINE__);
					if(mysql_num_rows($rescheck) > 0)
						$extra_errors[$abr]["site_page.name"] = "This field must be unique within it's parent page";
				}
			}
			$_GET["action"] = "save_data";
			form::ajax($extra_errors, array("site_page.menu_order" => "site_page.menu_order", "site_page.parent_id" => "site_page.parent_id"));
			//------------------------LABO21------------------------
			//checken of het een nieuwsbrief is, zoja creëren van de files
			if($template_id == 47)
			{
				
				//opkalen van de link text
				$res_linktext = DBConnect::query("SELECT * FROM page_newsletters", __FILE__, __LINE__);
				$row_linktext = mysql_fetch_array($res_linktext);
				$res_newsl = DBConnect::query("SELECT page_newsletter.*, site_page_lang.name as 'pagename' FROM page_newsletter, site_page_lang WHERE  page_newsletter.page_id=site_page_lang.lang_parent_id AND page_newsletter.page_id='" . $page_id . "'", __FILE__, __LINE__);
				while($row_newsl = mysql_fetch_array($res_newsl))
				{
					$fp = fopen('newsletters/EN_' . $row_newsl["id"] . '.html', 'w');
					
					//de styles includen
					fwrite($fp, '<style>');
					//fwrite($fp, file_get_contents('css/front/newsletter.css'));
					fwrite($fp, '</style>');
					
					//Link naar site
					$link = $_SERVER['HTTP_HOST'] . url_front::create_url( $row_newsl["page_id"]);
					
					fwrite($fp, '<center><div class="nwsl-toplink"><a href="http://' . $link . '" style="font-size:12px; color:#888; border:none; border-bottom: 1px dotted #888">' . $row_linktext["link_tekst"] . '</a></div></center>');
					
					//prehtml
					fwrite($fp, file_get_contents('newsletters/_fixed/pre_EN.html'));
					
					//alle relatieve paden opzetten
					$html = stripslashes($row_newsl["text"]);
					$html = str_replace('src="/', 'src="http://' . $_SERVER['HTTP_HOST'] . '/', $html);
					$html = str_replace('href="/', 'href="http://' . $_SERVER['HTTP_HOST'] . '/', $html);
					
					//de picture paths omzetten naar url_encoded
					$big_parts = explode('src="' . $tag, $html);
					$first = true;
					$new_html = "";
					foreach($big_parts as $big_part)
					{
						if($first)
						{
							$new_html .= $big_part;
							$first = false;
							continue;
						}
						$small_parts = explode('"', $big_part);
						$path_info = pathinfo($small_parts[0]);
						$path_info["filename"] = urlencode($path_info["filename"]);
						$path_info["filename"] = str_replace('+', '%20', $path_info["filename"]);
						unset($small_parts[0]);
						$new_html .= 'src="' . $path_info["dirname"] . '/' . $path_info["filename"] . '.' . $path_info["extension"] . '"' . implode('"', $small_parts);
					}
					
					$html = $new_html;
					
					//CSS Toepassen------------------------------------
					$tags = array("body" => "font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #000;",
								  "div" => "font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #000;",
								  "p" => "font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #000;",
								  "ul" => "font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #000;margin: 0 0 0 10px; padding: 0;",
								  "li" => "font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #000;margin: 0 0 0 10px; padding: 0; list-style:disc; list-style:outside;",
								  "a" => "font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #000;",
								  "b" => "font-weight:bold;",
								  "strong" => "font-weight:bold;",
								  "u" => "text-decoration:underline;",
								  "i" => "font-style:italic;",
								  "em" => "font-style:italic;",
								  "hr" => "padding:0px 0px 0px 0px; margin: 0px 0px 0px 0px; background-color:#000; border:0px; height:1px; clear:both;",
								  "h1" => "font-family: Arial, Helvetica, sans-serif; color:#000; font-size: 20px; font-weight: bold; margin-bottom: 3px; ine-height: 110%;",
								  "h2" => "font-family: Arial, Helvetica, sans-serif; color:#000; font-size: 17px; font-weight: bold; margin-bottom: 3px; ine-height: 110%;",
								  "h3" => "font-family: Arial, Helvetica, sans-serif; color:#000; font-size: 14px; font-weight: bold; margin-bottom: 3px; ine-height: 110%;",
								  "object" => "margin:10px 0; text-align:center;",
								  "img" => "margin:10px 0; text-align:center;");
					
					$classes = array("title_xl" => 'font-family: Arial, Helvetica, sans-serif; color:#000; font-size: 20px; font-weight: bold; margin-bottom: 3px; ine-height: 110%;',
									 "title_l" => 'font-family: Arial, Helvetica, sans-serif; color:#000; font-size: 17px; font-weight: bold; margin-bottom: 3px; ine-height: 110%;',
									 "title_m" => 'font-family: Arial, Helvetica, sans-serif; color:#000; font-size: 14px; font-weight: bold; margin-bottom: 3px; ine-height: 110%;',
									 "text_s" => 'font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 10px; color: #000;',
									 "text_gray" => 'font-family: Arial, Helvetica, sans-serif; line-height:122%; font-size: 13px; color: #707070;');
					
					//toepassen tags --> zoeken naar alle tags die geen klasse hebben
					foreach($tags as $tag => $css)
					{
						$new_html = "";
						$big_parts = explode('<' . $tag, $html);
						$first = true;
						foreach($big_parts as $big_part)
						{
							if($first)
							{
								$new_html .= $big_part;
								$first = false;
								continue;
							}
							//checken of er een klasse in zit
							$small_parts = explode('>', $big_part);
							if(!stristr($small_parts[0], 'class="'))
							{
								//toevoegen style
								$new_html .= '<' . $tag . ' style="' . $css . '" ' .  $big_part;
							}
							else
								$new_html .= '<' . $tag . $big_part;
						}
						
						$html = $new_html;
					}
					
					//toepassen classes
					foreach($classes as $class => $css)
					{
						$html = str_replace('class="' . $class . '"', 'style="' . $css . '"', $html);
					}
					
					fwrite($fp, $html);
					
					//posthtml
					fwrite($fp, file_get_contents('newsletters/_fixed/post_EN.html'));
					
					fclose($fp);
				}
			}
			break;
		case "changelang":
			$_SESSION["CMS_EDIT_LANG"] = $_GET["lang"];
			break;
		case "getpagecaption":
			$res = DBConnect::query("SELECT `id`, `name`, `template_id`, `published` FROM `site_page`, `site_page_lang` WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.id='" . addslashes($_GET["page_id"]) . "' AND site_page_lang.lang='" . mainconfig::$standardlanguage . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			
			$suffix = "";
			//PUT SITE SPECIFIC SHIT HERE
			echo (($row["published"] <= 0)?'<span style="font-style:italic; color:#666666;">':'') . htmlentities(stripslashes($row["name"])) . $suffix . (($row["published"] <= 0)?'</span>':'');
			break;
		case "homeconfig":
			if(!login::right("backpage_content_pages", "homeconfig"))
			{
				echo '<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to edit the home configuration<br><br><br><br></div>';
				break;
			}
			$_REQUEST["output_html"] = true;
			
			echo '<div class="contentheader">
						<div class="divleft">Home configuration</div>
						<div class="divright">
							<div class="savebutton" onclick="window.site_homecfg_form.savebutton=$(\'#site_homecfg_form_savebutton\'); window.site_homecfg_form.post();" id="site_homecfg_form_savebutton">Save</div>
						</div>
						<div class="divright">';
			echo '<div style="text-align: right; float:right; padding-right: 20px; margin-top: 3px;" id="site_homecfg_form_langlinks">';
			foreach(mainconfig::$languages as $abr => $lang)
			{
				echo '<span lang="' . $abr . '" class="' . (($abr == $_SESSION["CMS_EDIT_LANG"])? 'form_lang_selector_selected':'form_lang_selector') . '" current="' . (($abr == $_SESSION["CMS_EDIT_LANG"])? 'yes':'no') . '" onclick="window.site_homecfg_form.changelang(\'' . $abr . '\', this);">' . $abr . '</span>';
			}
			echo '</div></div></div>';
			echo '<div class="contentcontent">';
			$res = DBConnect::query("SELECT `site_homecfg`.*, `site_homecfg_lang`.* FROM `site_homecfg`, `site_homecfg_lang` WHERE site_homecfg.id=site_homecfg_lang.lang_parent_id AND site_homecfg_lang.lang='" . $_SESSION["CMS_EDIT_LANG"] . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			if(!$row) $row = array();
			form::show_autoform_new('site_homecfg', $row, $_SESSION["CMS_EDIT_LANG"], false, false);
			echo '</div>';
			
			break;
		case "newpagesexplanation":
			$_REQUEST["output_html"] = true;
			echo '<div id="site_page_form">
					<div class="contentheader">
						<div class="divleft">New pages</div>
						<div style="clear:both;"></div></div>';
			echo '<div class="contentcontent" style="text-align:center;">
					<br>The "new pages" node in the page tree contains the newly created pages. 
					<br><br>You can drag these pages to the place where they belong.<br><br>
				</div>';
			
			echo '</div>';
			break;
		case "getparentseo_desc":
			//GET [page_id] [lang]
			$res = DBConnect::query("SELECT `id`, `parent_id` FROM site_page WHERE `id`='" . addslashes($_GET["page_id"]) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				$seo = page_front::seo($row["parent_id"], addslashes($_GET["lang"]));
				echo '<b>Parent description:</b> ' . htmlentities($seo["description"]);
			}
			break;
		case "getparentseo_key":
			//GET [page_id] [lang]
			$res = DBConnect::query("SELECT `id`, `parent_id` FROM site_page WHERE `id`='" . addslashes($_GET["page_id"]) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				$seo = page_front::seo($row["parent_id"], addslashes($_GET["lang"]));
				echo '<b>Parent keywords:</b> ' . htmlentities(str_replace(',', ', ', $seo["keywords"]));
			}
			break;
		case "getautoseo":
			//GET [page_id] [lang]
				$seo = page_front::seo(addslashes($_GET["page_id"]), addslashes($_GET["lang"]));
				echo '<b>Title:</b> ' . htmlentities($seo["title"]) . '<br>
					<b>Description:</b> ' . htmlentities($seo["description"]) . '<br>
					<b>Keywords:</b> ' . htmlentities($seo["keywords"]);
			break;
		case "drop":
			//$_GET["place"] ["drag_id"] ["drop_id"] ["copy"]
			if(!login::right("backpage_content_pages", "move"))
			{
				echo "NORIGHTS:You don't have the permission to move pages";
				break;
			}
			
			$res_page = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
			$row_page = mysql_fetch_array($res_page);
			//CHECKEN of we een copy versleuren en willen kopiëren, dan ["drag_id"] aanpassen
			if($_GET["copy"] == "true");
			{
				if($row_page["copyof"] > 0)
				{
					$_GET["drag_id"] = $row_page["copyof"];
					$res_page = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
					$row_page = mysql_fetch_array($res_page);
				}
			}
			
			$res_templ = DBConnect::query("SELECT * FROM site_pagetemplates WHERE `id`='" . $row_page["template_id"] . "'", __FILE__, __LINE__);
			$row_templ = mysql_fetch_array($res_templ);
			
			if(!login::right("template", "move", $row_templ["id"]))
			{
				echo "NORIGHTS:You don't have the permission to move a '" . $row_templ["name"] . "' page";
				break;
			}
			header('Content-Type: text/xml');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			echo '<?xml version="1.0" encoding="utf-8"?>
					<drop>'; 
			
			//we zoeken naar de parent id
			$parent_id = $_GET["drop_id"];
			if($_GET["place"] != "in")
			{
				$res_check = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . addslashes($_GET["drop_id"]) . "'", __FILE__, __LINE__);
				$row_check = mysql_fetch_array($res_check);
				$parent_id = $row_check["parent_id"];
			}
			$res_check = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . $parent_id . "'", __FILE__, __LINE__);
			$row_parent = mysql_fetch_array($res_check);
			$parent_template = $row_parent["template_id"];
			$res_check = DBConnect::query("SELECT * FROM site_pagetemplates WHERE `id`='" . $parent_template . "'", __FILE__, __LINE__);
			$parent_template_row = mysql_fetch_array($res_check);
			//checken of er mag gedropt worden.
			$error = false;
			//mag parent children hebben
			if($parent_template_row["allow_children"] <= 0 && $parent_id > 0)
				$error = "The parent page cannot contain children ";
			//kloppen de templates
			if(trim($row_templ["parent_templates"]) != "")
			{
				if(!in_array($row_parent["template_id"], explode(';', $row_templ["parent_templates"])))
					$error = "The page cannot be a child of the page you dropped it in";
			}
			//get the level
			$level = 0;
			$row_check = $row_parent;
			if($row_check) $level++;
			while($row_check && $row_check["parent_id"] > 0)
			{
				$level++;
				$res_check = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . $row_check["parent_id"] . "'", __FILE__, __LINE__);
				$row_check = mysql_fetch_array($res_check);
			}
			if($level > $row_templ["max_level"] || $level < $row_templ["min_level"])
				$error = "Page cannot be dropped at this level " .$level;
			
			if($error)
				echo '<error>' . $error . '</error>';
			else
			{
				//we checken voor elke taal of de name uniek is
				$warning = false;
				foreach(mainconfig::$languages as $abr => $lang)
				{
					//get the name value
					//debug::message("SELECT `id`, `name` FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.id='" . addslashes($_GET["drag_id"]) . "' AND site_page_lang.lang='" . $abr . "'");
					$res_check = DBConnect::query("SELECT `id`, `name` FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.id='" . addslashes($_GET["drag_id"]) . "' AND site_page_lang.lang='" . $abr . "'", __FILE__, __LINE__);
					$row_check = mysql_fetch_array($res_check);
					
					$value = $row_check["name"];
					//debug::message("SELECT `id` FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.parent_id='" . addslashes($parent_id) . "' AND site_page_lang.name='" . addslashes($value) . "' AND site_page.id <> '" . addslashes($_GET["drag_id"]) . "' AND site_page_lang.lang='" . $abr . "'");
					//debug::message("SELECT `id` FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.parent_id='" . addslashes($parent_id) . "' AND site_page_lang.name='" . addslashes($value) . "' AND site_page.id <> '" . addslashes($_GET["drag_id"]) . "' AND site_page_lang.lang='" . $abr . "'");
					$res_check = DBConnect::query("SELECT `id` FROM site_page, site_page_lang WHERE site_page.id = site_page_lang.lang_parent_id AND site_page.parent_id='" . addslashes($parent_id) . "' AND site_page_lang.name='" . addslashes($value) . "' AND site_page.id <> '" . addslashes($_GET["drag_id"]) . "' AND site_page_lang.lang='" . $abr . "' AND site_page_lang.published='1'", __FILE__, __LINE__);
					if(mysql_num_rows($res_check) > 0)
						$warning = true;
				}
				if($warning)
					echo '<warning><![CDATA[There is a page with the same name. Please change one of these pages. The page name is used in the url and may cause problems if there are more than one with the same name.]]></warning>';
			
				//we update the data
				switch($_GET["place"])
				{
					case "in":
						$res_order = DBConnect::query("SELECT * FROM site_page WHERE parent_id='" . $parent_id . "' ORDER BY menu_order", __FILE__, __LINE__);
						$counter = 1;
						while($row_order = mysql_fetch_array ($res_order))
						{
							if($row_order["id"] != $_GET["drag_id"] || $_GET["copy"] == "true")
							{
								DBConnect::query("UPDATE site_page SET `menu_order`='" . $counter . "' WHERE `id`='" . $row_order["id"] . "'", __FILE__, __LINE__);
								$counter++;
							}
						}
						/*$res = DBConnect::query("SELECT `menu_order` FROM site_page WHERE parent_id='" . $parent_id . "' ORDER BY menu_order DESC LIMIT 0,1", __FILE__, __LINE__);
						$menu_order = $row["menu_order"] + 1;*/
						if($_GET["copy"] == "true")
						{
							$res_copy = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
							$row_copy = mysql_fetch_array($res_copy);
							DBConnect::query("INSERT INTO site_page (`id`, `parent_id`, `copyof`, `template_id`, `menu_order`) VALUES('', '" . $parent_id . "', '" . $row_copy["id"] . "', '" . $row_copy["template_id"] . "', '" . $counter . "')", __FILE__, __LINE__);
							echo '<copyof_root>' . $_GET["drag_id"] . '</copyof_root><copyof_newid>' . DBConnect::get_last_inserted("site_page", "id") . '</copyof_newid>';
						}
						else
							DBConnect::query("UPDATE site_page SET `parent_id`='" . $parent_id . "', `menu_order`='" . $counter . "' WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
						break;
					case "under":
						$res = DBConnect::query("SELECT `id` FROM site_page WHERE  parent_id='" . $parent_id . "' ORDER BY `menu_order`", __FILE__, __LINE__);
						$order = 1;
						while($row = mysql_fetch_array($res))
						{
							if($row["id"] != $_GET["drag_id"] || $_GET["copy"] == "true")
							{
								DBConnect::query("UPDATE site_page SET `menu_order`='" . $order . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
								$order++;
							}
							if($row["id"] == $_GET["drop_id"])
							{
								if($_GET["copy"] == "true")
								{
									$res_copy = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
									$row_copy = mysql_fetch_array($res_copy);
									DBConnect::query("INSERT INTO site_page (`id`, `parent_id`, `copyof`, `template_id`, `menu_order`) VALUES('', '" . $parent_id . "', '" . $row_copy["id"] . "', '" . $row_copy["template_id"] . "', '" . $order . "')", __FILE__, __LINE__);
									echo '<copyof_root>' . $_GET["drag_id"] . '</copyof_root><copyof_newid>' . DBConnect::get_last_inserted("site_page", "id") . '</copyof_newid>';
								}
								else
									DBConnect::query("UPDATE site_page SET `parent_id`='" . $parent_id . "', `menu_order`='" . $order . "' WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
								$order++;
							}
						}
						//$menu_order = $row["menu_order"] + 1;
						//DBConnect::query("UPDATE site_page SET `menu_order`=`menu_order`+1 WHERE `menu_order`>='" . $menu_order . "'", __FILE__, __LINE__);
						//DBConnect::query("UPDATE site_page SET `parent_id`='" . $parent_id . "', `menu_order`='" . $menu_order . "' WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
						break;
					case "above":
						$res = DBConnect::query("SELECT `id` FROM site_page WHERE  parent_id='" . $parent_id . "' ORDER BY `menu_order`", __FILE__, __LINE__);
						$order = 1;
						while($row = mysql_fetch_array($res))
						{
							if($row["id"] == $_GET["drop_id"])
							{
								if($_GET["copy"] == "true")
								{
									$res_copy = DBConnect::query("SELECT * FROM site_page WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
									$row_copy = mysql_fetch_array($res_copy);
									DBConnect::query("INSERT INTO site_page (`id`, `parent_id`, `copyof`, `template_id`, `menu_order`) VALUES('', '" . $parent_id . "', '" . $row_copy["id"] . "', '" . $row_copy["template_id"] . "', '" . $order . "')", __FILE__, __LINE__);
									echo '<copyof_root>' . $_GET["drag_id"] . '</copyof_root><copyof_newid>' . DBConnect::get_last_inserted("site_page", "id") . '</copyof_newid>';
								}
								else
									DBConnect::query("UPDATE site_page SET `parent_id`='" . $parent_id . "', `menu_order`='" . $order . "' WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);
								$order++;
							}
							if($row["id"] != $_GET["drag_id"] || $_GET["copy"] == "true")
							{
								DBConnect::query("UPDATE site_page SET `menu_order`='" . $order . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
								$order++;
							}
						}
						/*$res = DBConnect::query("SELECT `menu_order` FROM site_page WHERE `id`='" . addslashes($_GET["drop_id"]) . "'", __FILE__, __LINE__);
						$menu_order = $row["menu_order"];
						DBConnect::query("UPDATE site_page SET `menu_order`=`menu_order`+1 WHERE `menu_order`>='" . $menu_order . "'", __FILE__, __LINE__);
						DBConnect::query("UPDATE site_page SET `parent_id`='" . $parent_id . "', `menu_order`='" . $menu_order . "' WHERE `id`='" . addslashes($_GET["drag_id"]) . "'", __FILE__, __LINE__);*/
						
						break;
				}
			}
			echo '</drop>';
			break;
		case "delete":
			if(!login::right("backpage_content_pages", "delete"))
			{
				echo "NORIGHTS:You don't have the permission to delete pages";
				break;
			}
			$res_page = DBConnect::query("SELECT * FROM `site_page` WHERE `id`='" . addslashes($_GET["delpage_id"]) . "'", __FILE__, __LINE__);
			$row_page = mysql_fetch_array($res_page);
			//we get the page template table
			$res_templ = DBConnect::query("SELECT * FROM `site_pagetemplates` WHERE `id`='" . $row_page["template_id"] . "'", __FILE__, __LINE__);
			$row_template = mysql_fetch_array($res_templ);
			if(!login::right("template", "delete", $row_template["id"]))
			{
				echo "NORIGHTS:You don't have the permission to delete a '" . $row_template["name"] . "' page";
				break;
			}
			if($row_page["copyof"] > 0)
			{
				DBConnect::query("DELETE FROM `site_page` WHERE `id`='" . addslashes($_GET["delpage_id"]) . "'", __FILE__, __LINE__);
			}
			else
			{
				//we get the data of the template table
				$res_data = DBConnect::query("SELECT * FROM `" . $row_template["table"] . "` WHERE `page_id`='" . addslashes($_GET["delpage_id"]) . "'", __FILE__, __LINE__);
				$row_data = mysql_fetch_array($res_data);
				//delete page data
				data::delete("site_page", $_GET["delpage_id"], "menu_order", "parent_id", $row_page["parent_id"]);
				//opzoeken copies
				$res_copy = DBConnect::query("SELECT * FROM `site_page` WHERE `copyof`='" . addslashes($_GET["delpage_id"]) . "'", __FILE__, __LINE__);
				while($row_copy = mysql_fetch_array($res_copy))
				{
					DBConnect::query("DELETE FROM `site_page` WHERE `id`='" . $row_copy["id"] . "'", __FILE__, __LINE__);
				}
				//delete template data
				data::delete($row_template["table"], $row_data["id"]);
			}
			echo "OK";
			break;
		case "clonepage":
			if(!login::right("backpage_content_pages", "new"))
			{
				echo "NORIGHTS:You don't have the permission to create new pages";
				break;
			}
			echo "Clone page " . $_GET["page_id"];
			break;
		case "create":
			if(!login::right("backpage_content_pages", "new"))
			{
				echo "NORIGHTS:You don't have the permission to create new pages";
				break;
			}
			$restempl = DBConnect::query("SELECT * FROM site_pagetemplates WHERE `id`='" . addslashes($_GET["template"]) . "'", __FILE__, __LINE__);
			$rowtempl = mysql_fetch_array($restempl);
			if(!login::right("template", "create", $rowtempl["id"]))
			{
				echo "NORIGHTS:You don't have the permission to delete a '" . $rowtempl["name"] . "' page";
				break;
			}
			header('Content-Type: text/xml');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			echo '<?xml version="1.0" encoding="utf-8"?>
					<pagecreate>';
			$new_info = page::create_page($rowtempl["id"], addslashes($_GET["parent_id"]));
			if($new_info["parent"] == 0)
				echo '<parent_li_id>pagetree_front_site</parent_li_id>';
			else
				echo '<parent_li_id>pagetree_front_' . $new_info["parent"] . '</parent_li_id>';
			echo '<li_id>pagetree_front_' . $new_info["id"] . '</li_id>';
			echo '<divhtml><![CDATA[<div pageid="' . $new_info["id"] . '" canhavechild="' . $rowtempl["allow_children"] . '" parenttemplates="' . $rowtempl["parent_templates"] . '" template="' . $rowtempl["id"] . '" max_level="' . $rowtempl["max_level"] . '" min_level="' . $rowtempl["min_level"] . '" href="javascript:dummy();" onclick="select_me_please(\'treeview_pages_front\', this); frontpagetree_select(this, \'' . $new_info["id"] . '\')" ondblclick="if(this.getAttribute(\'noedit\') != \'1\'){cms2_remove_mce(\'content\'); cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=content&action=loadpage&edit_page_id=' . $new_info["id"] . '\');}" style="padding: 2px 4px 2px 4px; backgound-color:#456123; height:18px;"><span style="font-style:italic; color:#666666;">New Page</span></div>]]></divhtml>';
			echo '<newid>' . $new_info["id"] . '</newid></pagecreate>';
			break;
		case "dead_links":
			echo '<div class="contentheader">
						<h1>Dead links</h1>
					</div>';
			break;
		case "search":
			if(trim(urldecode($_GET["searchstring"])) !="")
			{
				$res = DBConnect::query("SELECT site_page.* FROM site_page, site_page_lang WHERE site_page.id=site_page_lang.lang_parent_id AND site_page_lang.lang='" . mainconfig::$standardlanguage . "' AND `name` LIKE '%" . addslashes(urldecode($_GET["searchstring"])) . "%'", __FILE__, __LINE__);
				if(mysql_num_rows($res) <= 0)
					echo '<br>no search results for "' . urldecode($_GET["searchstring"]) . '"';
				while($row = mysql_fetch_array($res))
				{
					if (login::right("template", "edit", $row["template_id"]))
						echo '<div class="search_result" onclick="cms2_remove_mce(\'content\'); cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=content&action=loadpage&edit_page_id=' . stripslashes($row["id"]) . '\')">' . str_ireplace(urldecode($_GET["searchstring"]), '<b>' . urldecode($_GET["searchstring"]) . '</b>', search::search_location_string($row["id"], mainconfig::$standardlanguage)) . '</div>';
					else
						echo '<div class="search_result_noedit">' . str_ireplace(urldecode($_GET["searchstring"]), '<b>' . urldecode($_GET["searchstring"]) . '</b>', search::search_location_string($row["id"], mainconfig::$standardlanguage)) . '</div>';
				}
				echo '<script language="javascript">
						$("#zoekresultaten_html_pan").children(\'div[class="search_result"]\').hover(
						  function () {
							$(this).css("background-color", "#CCCCCC");
						  },
						  function () {
							$(this).css("background-color", "#FFFFFF");
						  }
						);
					</script>';
			}
			break;
		case "saveworktab":
			$_SESSION["back_working_tab"] = $_GET["tab"];
			break;
	}
?>