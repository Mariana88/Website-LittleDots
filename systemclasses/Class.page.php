<?php
//page know's everything about the existing pages in the project.
//it can scan all the template files and save the metadata in the database

	class page
	{
		public $page_id;
		public $published = 1;
		private $defaulttab = 1;
		//Displays the backside of the component
		public function __construct($page_id)
		{
			$this->page_id = $page_id;
		}
						
		public function creator()
		{
			//we get the page data
			$result = DBConnect::query("SELECT * FROM `site_page` WHERE `id`='" . addslashes($this->page_id) . "'", __FILE__, __LINE__);
			$row_page = mysql_fetch_array($result);
			
			if(!isset($_SESSION["CMS_EDIT_LANG"]))
				$_SESSION["CMS_EDIT_LANG"] = mainconfig::$standardlanguage;
				
			$result = DBConnect::query("SELECT * FROM `site_page_lang` WHERE lang_parent_id='" . addslashes($this->page_id) . "' AND lang='" . $_SESSION["CMS_EDIT_LANG"] . "'", __FILE__, __LINE__);
			$row_page_lang = mysql_fetch_array($result);
			
			//als het niet bestaat voor de taal dan aanmaken
			if(!$row_page_lang)
			{
				DBConnect::query("INSERT INTO `site_page_lang` (`lang_parent_id`, `lang`) VALUES('" . addslashes($this->page_id) . "', '" . $_SESSION["CMS_EDIT_LANG"] . "')", __FILE__, __LINE__);
				$row_page_lang["lang_id"] = DBConnect::get_last_inserted('site_page_lang');
				$row_page_lang["lang"] = $_SESSION["CMS_EDIT_LANG"];
				$row_page_lang["lang_parent_id"] = $this->page_id;
			}
			
			//get the template data
			$result = DBConnect::query("SELECT * FROM `site_pagetemplates` WHERE `id`='" . addslashes($row_page["template_id"]) . "'", __FILE__, __LINE__);
			$row_template = mysql_fetch_array($result);
			
			echo '<div id="site_page_form">
					<div class="contentheader">
						<div class="divleft">Edit Page: ' . htmlentities(stripslashes($row_page_lang["name"])) . '</div>
						<div class="divright">
							<div class="savebutton" onclick="content_save(window.' . $row_template["table"] . '_form);" id="content_page_savebutton">Save Page</div>
						</div>
						<div class="divright">';
			
			echo '<div style="text-align: right; float:right; padding-right: 20px; margin-top: 3px;" id="sitepage_langlinks">';
			foreach(mainconfig::$languages as $abr => $lang)
			{
				echo '<span lang="' . $abr . '" class="' . (($abr == $_SESSION["CMS_EDIT_LANG"])? 'form_lang_selector_selected':'form_lang_selector') . '" current="' . (($abr == $_SESSION["CMS_EDIT_LANG"])? 'true':'false') . '" onclick="content_changelang(window.' . $row_template["table"] . '_form, this);">' . $abr . '</span>';
			}
			echo '</div>';
			
			//HIDDEN FIELDS
			formfield::publish_dbfield("site_page.id", $row_page["id"]);
			formfield::publish_dbfield("site_page.parent_id", $row_page["parent_id"]);
			formfield::publish_dbfield("site_page.template_id", $row_page["template_id"]);
			formfield::publish_dbfield("site_page.lang_id", $row_page_lang["lang_id"]);
			formfield::publish_dbfield("site_page.lang_parent_id", $row_page_lang["lang_parent_id"]);
			formfield::publish_dbfield("site_page.lang", $row_page_lang["lang"]);
						
			echo '</div>
					</div>';
			echo '<div class="pageedit-section-head open">Main info</div><div class="pageedit-section-content open">';
			formfield::publish_dbfield("site_page.name", $row_page_lang["name"]);
			formfield::publish_dbfield("site_page.menu_name", $row_page_lang["menu_name"]);
			formfield::publish_dbfield("site_page.published", $row_page_lang["published"]);
			echo '<div style="clear:both; height: 0px;"></div></div>';
						
			echo '<div class="pageedit-section-head open">Page info</div><div class="pageedit-section-content open">';
			//data ophalen
			$res_content = DBConnect::query("SELECT * FROM `" . $row_template["table"] . "` WHERE `page_id`='" . $this->page_id . "'", __FILE__, __LINE__);
			$row_content = mysql_fetch_array($res_content);
			if($row_content)
			{
				//checken of lang dependent zoja, dan de data ophalen en mergen
				//echo "SELECT * FROM `sys_database_table` WHERE `table`='" . $row_template["table"] . "'";
				$res_content = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $row_template["table"] . "'", __FILE__, __LINE__);
				if($row_table = mysql_fetch_array($res_content))
				{
					if($row_table["lang_dep"] > 0)
					{
						//var_dump($row_content);
						$res_content = DBConnect::query("SELECT * FROM `" . $row_template["table"] . "_lang` WHERE `lang_parent_id`='" . $row_content["id"] . "' AND `lang`='" . $_SESSION["CMS_EDIT_LANG"] . "'", __FILE__, __LINE__);
						if($row_content_lang = mysql_fetch_array($res_content))
						{
							foreach($row_content_lang as $k => $v)
								$row_content[$k] = $v;
						}
					}
				}
			}
			else
			{
				//we zetten de page_id, voor de rest leeg
				$row_content = array();
				$row_content["page_id"] = $this->page_id;
			}
			form::show_autoform_new($row_template["table"], $row_content, $_SESSION["CMS_EDIT_LANG"], false, false);
			echo '<div style="clear:both; height: 0px;"></div></div>';
			
			echo '<div class="pageedit-section-head closed">Search engine optimalistation &amp; sharing</div><div class="pageedit-section-content closed">';
			$seo_auto = NULL;
			if($row_page_lang["seo_auto"] > 0)
			{
				$seo_auto = page_front::seo($row_page["id"], $_SESSION["CMS_EDIT_LANG"]);
			}
			$seo = NULL;
			if($row_page_lang["seo_add_parent_description"] > 0 || $row_page_lang["seo_add_parent_keywords"] > 0)
			{
				$seo = page_front::seo($row_page["parent_id"], $_SESSION["CMS_EDIT_LANG"]);
			}
			formfield::publish_dbfield("site_page.seo_auto", $row_page_lang["seo_auto"]);
			echo '<div id="content_seo_auto" style="clear: both; color:#888888; padding: 4px 0px 8px 150px; display: none; line-height: 15px;">';
			if($row_page_lang["seo_auto"] > 0 && $seo_auto)
				echo '<b>Title:</b> ' . htmlentities($seo_auto["title"]) . '<br>
						<b>Description:</b> ' . htmlentities($seo_auto["description"]) . '<br>
						<b>Keywords:</b> ' . htmlentities($seo_auto["keywords"]);
			echo '</div>';
			formfield::publish_dbfield("site_page.seo_use_parent", $row_page_lang["seo_use_parent"]);
			formfield::publish_dbfield("site_page.seo_title", $row_page_lang["seo_title"]);
			formfield::publish_dbfield("site_page.seo_add_parent_description", $row_page_lang["seo_add_parent_description"]);
			echo '<div id="content_seo_parent_description" style="clear: both; color:#888888; padding: 4px 0px 8px 150px; display: none; line-height: 15px;">';
			if($row_page_lang["seo_add_parent_description"] > 0 && $seo)
				echo '<b>Parent description:</b> ' . htmlentities($seo["description"]);
			echo '</div>';
			formfield::publish_dbfield("site_page.seo_description", $row_page_lang["seo_description"]);
			formfield::publish_dbfield("site_page.seo_add_parent_keywords", $row_page_lang["seo_add_parent_keywords"]);
			echo '<div id="content_seo_parent_keywords" style="clear: both; color:#888888; padding: 4px 0px 8px 150px; display: none; line-height: 15px;">';
			if($row_page_lang["seo_add_parent_keywords"] > 0 && $seo)
				echo '<b>Parent keywords:</b> ' . htmlentities(str_replace(',', ', ', $seo["keywords"]));
			echo '</div>';
			formfield::publish_dbfield("site_page.seo_keywords", $row_page_lang["seo_keywords"]);
			echo '<script>
					$("#site_page_seo_add_parent_description").click(function(){
						if(this.checked)
						{
							$("#content_seo_parent_description").load("/ajax.php?sessid=' . session_id() . '&page=content&action=getparentseo_desc&page_id=' . $row_page["id"] . '&lang=" + window.site_page_form.currentlang);
							$("#content_seo_parent_description").css("display", "block");
						}
						else
						{
							$("#content_seo_parent_description").empty();
							$("#content_seo_parent_description").css("display", "none");
						}
					});
					$("#site_page_seo_add_parent_keywords").click(function(){
						if(this.checked)
						{
							$("#content_seo_parent_keywords").load("/ajax.php?sessid=' . session_id() . '&page=content&action=getparentseo_key&page_id=' . $row_page["id"] . '&lang=" + window.site_page_form.currentlang);
							$("#content_seo_parent_keywords").css("display", "block");
						}
						else
						{
							$("#content_seo_parent_keywords").empty();
							$("#content_seo_parent_keywords").css("display", "none");
						}
					});
					$("#site_page_seo_auto").click(function(){
						if(this.checked)
						{
							$("#content_seo_auto").load("/ajax.php?sessid=' . session_id() . '&page=content&action=getautoseo&page_id=' . $row_page["id"] . '&lang=" + window.site_page_form.currentlang);
							$("#content_seo_auto").css("display", "block");
						}
						else
						{
							$("#content_seo_auto").empty();
							$("#content_seo_auto").css("display", "none");
						}
					});';
			if($row_page_lang["seo_add_parent_description"] > 0)
				echo '$("#content_seo_parent_description").css("display", "block");';
			if($row_page_lang["seo_add_parent_keywords"] > 0)
				echo '$("#content_seo_parent_keywords").css("display", "block");';
			if($row_page_lang["seo_auto"] > 0)
				echo '$("#content_seo_auto").css("display", "block");';
			
			echo '</script>';
			echo '<div style="clear:both; height: 0px;"></div></div>';
			
			echo '<div class="pageedit-section-head closed">Menu options</div><div class="pageedit-section-content closed">';
			formfield::publish_dbfield("site_page.menu_alt", $row_page_lang["menu_alt"]);
			formfield::publish_dbfield("site_page.hide_in_menu", $row_page["hide_in_menu"]);
			echo '<div style="clear:both; height: 0px;"></div></div>';
			
			
			if(login::right("backpage_content_pages", "tab_security"))
			{
				echo '<div class="pageedit-section-head closed">Securiy options</div><div class="pageedit-section-content closed">';
				formfield::publish_dbfield("site_page.front_protected", $row_page["front_protected"]);
				echo '<div style="clear:both; height: 0px;"></div></div>';
			}

			
			
			$datastr = form::load_data('site_page', $this->page_id);
			echo '</div>
					</div>
					</form>
					<script language="JavaScript" type="text/javascript">
						datastr = \'' . $datastr . '\';
						//alert(datastr);
						window.site_page_form = new form(\'site_page_form\', \'site_page\', \'' . $this->page_id . '\', "") ;
						window.site_page_form.currentlang = \'' . $_SESSION["CMS_EDIT_LANG"] . '\';
						window.site_page_form.dataDoc = $(cms2_string_to_xml(datastr));
						window.site_page_form.clear_subdata();';
			$res_sub = DBConnect::query("SELECT * FROM sys_database_subtable WHERE `table_parent`='site_page' ORDER BY `order`", __FILE__, __LINE__);
			while($row_sub = mysql_fetch_array($res_sub))
			{
				//we get the table meta
				$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $row_sub["table_sub"] . "'", __FILE__, __LINE__);
				$row_meta = mysql_fetch_array($res_meta);
				if($row_meta["lang_dep"] > 0 || $row_sub["new_list_per_lang"] > 0)
					echo 'window.site_page_form.add_subdata(\'form_site_page_' . $row_sub["table_sub"] . '\');';
			}
			//SCRIPT VOOR HET OPEN EN DICHT KLAPPEN VAN DE PANELS
			echo '$(".pageedit-section-head").blicsmColpanel();';
			//script voor de formfields
			echo 'initializeBlicsmFormFields();';
			echo '</script>
					<div style="clear:both; padding-top: 10px; font-size:9px; color:#4D6F8C;">** = These fields are language independent, if you change such a field, it will be changed for all the languages.</div>';
		}
		
		/*static function create_page($id)
		{
			$result = DBConnect::query("SELECT * FROM site_page_root WHERE id='" . addslashes($id) . "'", __FILE__, __LINE__);
			if($row_page = mysql_fetch_array($result))
			{
				
				$result_template = DBConnect::query("SELECT * FROM sys_pagetemplates WHERE id='" . $row_page["template_id"] . "'", __FILE__, __LINE__);
				if($row_templ = mysql_fetch_array($result_template))
				{
					require_once(stripslashes($row_templ["url"]));
					$return = new $row_templ["classname"];
					$return->page_id = $id;
					$return->published = stripslashes($row_page["published"]);
					return $return;
				}
				else
					return NULL;
			}
			else
				return NULL;
		}*/
		
		static function publish_tree_front()
		{
			echo '<div class="treeview"><ul class="treeview" id="treeview_pages_front">';
			//SITE
			if(login::rootpage() <= 0)
			{
				echo '<li id="pagetree_front_site"><div pageid="0" canhavechild="1" parenttemplates="" template="" nodel="1" nodrag="1" noedit="' . ((login::right("backpage_content_pages", "homeconfig"))?'0':'1') . '" noclone="1" level="-1" href="javascript:dummy();" onclick="select_me_please(\'treeview_pages_front\', this); frontpagetree_select(this, \'site\')" ondblclick="if(this.getAttribute(\'noedit\') != \'1\'){cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=content&action=homeconfig\');}">Website</div><ul>';
				page::tree_node_publish_front(0, 0, true);
				echo '</ul></li>';
			}
			else
				page::tree_node_publish_front(0, 0, false);
			
			echo '</ul></div>
					<script type="text/javascript">
						ddtreemenu.createTree("treeview_pages_front", true);
					</script>';
		}
		
		//pagefound = als in rechten een rootpage gedefinieerd is dan gebruiken we dit attribuut
		static function tree_node_publish_front($parent_id, $level, $pagefound)
		{
			//checken of de parent children autosort aan hebben staan
			$res_templ = DBConnect::query("SELECT site_pagetemplates.* FROM site_pagetemplates, site_page WHERE site_pagetemplates.id=site_page.template_id AND site_page.id='" . $parent_id . "'",__FILE__, __LINE__);
			$row_templ = mysql_fetch_array($res_templ);
			$result = NULL;
			if($row_templ["childrenautosort"])
			{
				
				$result = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.copyof<='0' AND site_page.id=site_page_lang.lang_parent_id AND site_page_lang.lang='" . mainconfig::$standardlanguage . "' AND site_page.parent_id='" . addslashes($parent_id) . "' UNION SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.copyof>'0' AND site_page.copyof=site_page_lang.lang_parent_id AND site_page_lang.lang='" . mainconfig::$standardlanguage . "' AND site_page.parent_id='" . addslashes($parent_id) . "' ORDER BY `name`",__FILE__, __LINE__);
			}
			else
				$result = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.copyof<='0' AND site_page.id=site_page_lang.lang_parent_id AND site_page_lang.lang='" . mainconfig::$standardlanguage . "' AND site_page.parent_id='" . addslashes($parent_id) . "' UNION SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.copyof>'0' AND site_page.copyof=site_page_lang.lang_parent_id AND site_page_lang.lang='" . mainconfig::$standardlanguage . "' AND site_page.parent_id='" . addslashes($parent_id) . "' ORDER BY `menu_order`",__FILE__, __LINE__);
			$firstfound = false;
			while ($row = mysql_fetch_array($result)) 
			{
				if(!$firstfound && $parent_id != 0  && $parent_id != "-1")
				{
					echo '<ul>';
					$firstfound = true;
				}
				$subpages = false;
				$restest = DBConnect::query("SELECT * FROM site_page WHERE parent_id='" . $row["id"] . "'", __FILE__, __LINE__);
				if(mysql_num_rows($restest) > 0)
					$subpages = true;
				
				//we gaan de template data ophalen. 
				$restemplate = DBConnect::query("SELECT * FROM site_pagetemplates WHERE `id`='" . $row["template_id"] . "'", __FILE__, __LINE__);
				$rowtemplate = mysql_fetch_array($restemplate);
				$nodel = 1;
				$noedit = 1;
				$nodrag = 1;
				$noclone = 1;
				if(login::right("template", "delete", $rowtemplate["id"])) $nodel = 0;
				if(login::right("template", "create", $rowtemplate["id"])) $noclone = 0;
				if(login::right("template", "edit", $rowtemplate["id"])) $noedit = 0;
				if(login::right("template", "move", $rowtemplate["id"]) && $row_templ["childrenautosort"] <= 0) $nodrag = 0;
				$suffix = "";
				
				//PUT SITE SPECIFIC SHIT HERE
				if($row["copyof"]>0)
					$suffix = ' <span style="font-size:10px; color:#666666;">copy</span>';
				if($pagefound || login::rootpage() == $row["id"])
				{
					echo '<li id="pagetree_front_' . stripslashes($row["id"]) . '" ' . (($subpages)?'class="submenu"':'') . '><div pageid="' . stripslashes($row["id"]) . '" canhavechild="' . $rowtemplate["allow_children"] . '" level="' . $level . '" parenttemplates="' . $rowtemplate["parent_templates"] . '" template="' . $rowtemplate["id"] . '" max_level="' . $rowtemplate["max_level"] . '" min_level="' . $rowtemplate["min_level"] . '" nodel="' . $nodel . '" noedit="' . $noedit . '" nodrag="' . $nodrag . '" noclone="' . $noclone . '" copyof="' . $row["copyof"] . '" href="javascript:dummy();" onclick="select_me_please(\'treeview_pages_front\', this); frontpagetree_select(this, \'' . stripslashes($row["id"]) . '\')" ondblclick="if(this.getAttribute(\'noedit\') != \'1\'){cms2_remove_mce(\'content\'); cms2_show_loader(\'content\');  $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=content&action=loadpage&edit_page_id=\' + (($(this).attr(\'copyof\')>0)?$(this).attr(\'copyof\'):$(this).attr(\'pageid\')));}">' . (($row["published"] <= 0)?'<span class="unpublished">':'') . str_replace('&lt;br&gt;', ' ', htmlentities(stripslashes($row["name"]))) . $suffix . (($row["published"] <= 0)?'</span>':'') . '</div>';
				}
					page::tree_node_publish_front(stripslashes($row["id"]), ($level+1), ($pagefound || login::rootpage() == $row["id"]));
				if($pagefound || login::rootpage() == $row["id"])
					echo '</li>';
			}
			if($firstfound)
				echo '</ul>';
		}
		
		
		/*
		static function write_page_selectbox($veldid, $value, $parent_systemplate = 0, $nullname = "NONE", $asparent = 0)
		{
			echo '<input type="hidden" value="' . $value . '" id="' . $veldid . '" name="' . $veldid . '" ></input>';
			//we checken of de value bestaat in db
			$res = DBConnect::query("SELECT * FROM site_page_root WHERE id='" . $value . "'", __FILE__, __LINE__);
			$row_selected = mysql_fetch_array($res);
			echo '<div id="pageselect_tekst_' . $veldid . '" class="pageselect_veld" onclick="
									var the_div = document.getElementById(\'pageselect_div_' . $veldid . '\');
									if(the_div.style.display == \'none\')
									{
										the_div.style.display = \'block\';
										the_div.style.position = \'absolute\'; 
										the_div.style.top = (parseInt(this.style.offsetTop) + parseInt(this.style.height)).toString() + \'px\'; 
										the_div.style.left = this.style.offsetLeft; 
									}
									else
										the_div.style.display = \'none\';
									">&nbsp;' . (($row_selected)?page::page_selectbox_text($row_selected["id"]):$nullname) . '</div><br>';
			//dopdowndiv
			echo '<div id="pageselect_div_' . $veldid . '" class="pageselect_div" style="display:none">';
			//page::page_selectbox_item(0, $parent_systemplate, $veldid);
			echo '</div>';
			echo '<script>
					var pageselect_div_' . str_replace(".", "_", $veldid) . ' = new Spry.Widget.HTMLPanel("pageselect_div_' . $veldid . '",{evalScripts:true});
					pageselect_div_' . str_replace(".", "_", $veldid) . '.loadContent(\'/ajax.php?sessid=' . session_id() . '&pageselectbox=1&systemtemplate=' . $parent_systemplate . '&veldid=' . $veldid .'&asparent=' . $asparent . '\');
				</script>';
		}
		
		static function page_selectbox_text($page_id)
		{
			$res_root = DBConnect::query("SELECT * FROM `site_page_root` WHERE `id`='" . addslashes($page_id) . "'", __FILE__, __LINE__);
			$row_root = mysql_fetch_array($res_root);
			$html1 = "";
			while($row_root)
			{
				$res_page = DBConnect::query("SELECT * FROM `site_page` WHERE `root_id`='" . addslashes($row_root["id"]) . "' AND `lang`='" . addslashes($_SESSION["CMS_EDIT_LANG"]) . "'", __FILE__, __LINE__);
				$row_page = mysql_fetch_array($res_page);
				if($html1 == "")
					$html1 = $row_page["menu_name"] . $html1;
				else
					$html1 = $row_page["menu_name"] . '&nbsp;&gt;&nbsp;' . $html1;
				
				$res_root = DBConnect::query("SELECT * FROM `site_page_root` WHERE `id`='" . $row_root["parent_id"] . "'", __FILE__, __LINE__);
				$row_root = mysql_fetch_array($res_root);
			}
			return $html1;
		}
		
		static function page_selectbox_item($parent_id, $parent_systemplate, $veldid, $text = "", $level = 1, $template_row = NULL, $asparent)
		{
			if($template_row == NULL)
			{
				$res_templ = DBConnect::query("SELECT * FROM `sys_pagetemplates` WHERE `id`='" . $parent_systemplate . "'", __FILE__, __LINE__);
				$template_row = mysql_fetch_array($res_templ);
			}
			if($parent_id == 0)
			{
				if(trim($template_row["parent_page_restr"]) == "" || trim($template_row["parent_page_restr"]) != "0")
					echo '<div class="pageselect_itemdiv" onclick="document.getElementById(\'pageselect_tekst_' . $veldid . '\').innerHTML=\'NONE\'; document.getElementById(\'' . $veldid . '\').value=\'0\'; var the_div = document.getElementById(\'pageselect_div_' . $veldid . '\'); the_div.style.display=\'none\'" >NONE</div>';
				if(trim($template_row["parent_page_restr"]) == "0")
					return;
				if(trim($template_row["max_level_restr"]) == "0")
					return;
			}
			$res = DBConnect::query("SELECT * FROM `site_page_root` WHERE `parent_id`='" . $parent_id . "' AND `back`<='0' ORDER BY menu_order", __FILE__, __LINE__);
			
			while($row = mysql_fetch_array($res))
			{
				$text = page::page_selectbox_text($row["id"]);
				
				$echo = true;
				if(trim($template_row["parent_templates_restr"]) != "" && $template_row["parent_templates_restr"] != $row["template_id"])
					$echo = false;
				elseif(trim($template_row["parent_page_restr"]) != "" && $template_row["parent_page_restr"] != $row["id"])
					$echo = false;
				if($asparent != 0 && $row["id"] == $asparent)
					$echo = false;
				if($echo)
					echo '<div class="pageselect_itemdiv" onclick="document.getElementById(\'pageselect_tekst_' . $veldid . '\').innerHTML=\'' . $text . '\'; document.getElementById(\'' . $veldid . '\').value=\'' . $row["id"] . '\'; var the_div = document.getElementById(\'pageselect_div_' . $veldid . '\'); the_div.style.display=\'none\'" >' . $text . '</div>';
				
				if(trim($template_row["max_level_restr"]) != "" && $template_row["max_level_restr"] <= $level)
				{
					//we dont display any further
				}
				else
				{
					if($asparent == 0 || $row["id"] != $asparent)
						page::page_selectbox_item($row["id"], $parent_systemplate, $veldid, $text, ($level+1), $template_row, $asparent);
				}
				
			}
		}
		*/
		
		static function last_update($page_id)
		{
			DBConnect::query("UPDATE `site_page` SET `last_update`='" . time() . "' WHERE id='" . $page_id . "'",__FILE__, __LINE__);
		}
		
		static function get_last_update($page_id)
		{
			$res = DBConnect::query("SELECT * FROM `site_page` WHERE `id`='" . $page_id . "'",__FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			return $row["last_update"];
		}
		static function create_page($template_id, $parent_page = NULL)
		{
			$restempl = DBConnect::query("SELECT * FROM site_pagetemplates WHERE `id`='" . $template_id . "'", __FILE__, __LINE__);
			$rowtempl = mysql_fetch_array($restempl);

			//we zoeken eerst naar de menu order
			$menuorder = 1;
			if(!isset($parent_page))
				$parent_page = 0;
			//get template info
			
			//als er slechts één parent megelijkheid is dan gaan we daarvoor
			if(trim($rowtempl["parent_templates"]) != "")
			{
				$sql = "SELECT `id` FROM site_page WHERE `template_id` IN ('" . implode("','", explode(";", $rowtempl["parent_templates"])) . "') LIMIT 0, 2";
				//echo '<alert><![CDATA[' . $sql . ']]></alert>';
				$restest = DBConnect::query($sql, __FILE__, __LINE__);
				if(mysql_num_rows($restest) == 1)
				{
					$rowtest = mysql_fetch_array($restest);
					$parent_page = $rowtest["id"];
				}
			}
			//als de pagina enkel in de root kan staan dan ook gewoon direct onder de root plaatsten
			if(trim($rowtempl["parent_templates"]) == "" && $rowtempl["min_level"] == 0 && $rowtempl["max_level"] == 0)
				$parent_page = 0;
				
			$res = DBConnect::query("SELECT menu_order FROM site_page WHERE parent_id='" . $parent_page . "' ORDER BY menu_order DESC LIMIT 0,1", __FILE__, __LINE__);
			
			if($row = mysql_fetch_array($res))
				$menuorder = stripslashes($row["menu_order"]) + 1;
			DBConnect::query("INSERT INTO `site_page`(`parent_id`, `template_id`, `menu_order`) VALUES('" . $parent_page . "', '" . $rowtempl["id"] . "', '" . $menuorder . "')", __FILE__, __LINE__);
			$new_id = mysql_insert_id();
			foreach(mainconfig::$languages as $abr => $lang)
				DBConnect::query("INSERT INTO `site_page_lang`(`lang_parent_id`, `lang`, `name`, `seo_auto`) VALUES('" . $new_id . "', '" . $abr . "', 'New Page', '1')", __FILE__, __LINE__);
			
			//nu nog de standaard waardes invullen in de page table
			$res_fields = DBConnect::query("SELECT * FROM sys_database_meta WHERE `tablename`='" . $rowtempl["table"] . "'", __FILE__, __LINE__);
			$fields = array();
			$fieldslang = array();
			while($row_field = mysql_fetch_array($res_fields))
			{
				if($row_field["lang_dep"] > 0)
					$fieldslang[$row_field["fieldname"]] = $row_field["data_standaardwaarde"];
				else
					$fields[$row_field["fieldname"]] = $row_field["data_standaardwaarde"];
			}
			$into = '';
			$values = '';
			foreach($fields as $k => $v)
			{
				if($k == 'id') $v = '';
				if($k == 'page_id') $v = $new_id;
				$into .= (($into != '')?',':'') . '`' . $k . '`';
				$values .= (($values != '')?',':'') . "'" . $v . "'";
			}
			//debug::message()
			DBConnect::query("INSERT INTO `" . $rowtempl["table"] . "` (" . $into . ") VALUES (" . $values . ")", __FILE__, __LINE__);
			$new_data_id = mysql_insert_id();
			if(count($fieldslang) > 0)
			{
				foreach(mainconfig::$languages as $abr => $lang)
				{
					$into = '';
					$values = '';
					foreach($fieldslang as $k => $v)
					{
						if($k == 'lang_id') $v = '';
						if($k == 'lang_parent_id') $v = $new_data_id;
						if($k == 'lang') $v = $abr;
						$into .= (($into != '')?',':'') . '`' . $k . '`';
						$values .= (($values != '')?',':'') . "'" . $v . "'";
					}
					DBConnect::query("INSERT INTO `" . $rowtempl["table"] . "_lang` (" . $into . ") VALUES (" . $values . ")", __FILE__, __LINE__);
				}
			}
			
			return array("id" => $new_id, "parent" => $parent_page);
		}
	}
?>