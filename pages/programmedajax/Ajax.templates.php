<?php
	if(!login::right("backpage_config_templates", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	switch($_GET["action"])
	{
		case "sp_new":
			$_REQUEST["output_html"] = true;
			echo '<div class="contentheader">
						<div class="divleft">Create a new template</div>
						<div class="divright">
							<div class="savebutton" onclick="window[\'site_pagetemplates_form\'].aftersave_success = \'sp_page_refresh_list\'; window[\'site_pagetemplates_form\'].savebutton = $(this); window[\'site_pagetemplates_form\'].post();">Save</div>
						</div>
					</div>
					<div class="contentcontent">';
			form::show_autoform_new('site_pagetemplates', array(), mainconfig::$standardlanguage);
			echo '</div>';
			break;
		case "refresh_list":
			$_REQUEST["output_html"] = true;
			$res = DBConnect::query("SELECT * FROM `site_pagetemplates`", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
			{
				echo '<div onselectstart="return false;" style="padding: 3px; cursor: pointer;" id="sp_list_' . stripslashes($row["id"]) . '" onclick="sp_selectme(\'' . stripslashes($row["id"]) . '\');" ondblclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=templates&action=sp_load&sp_id=' . stripslashes($row["id"]) . '\');">' . stripslashes($row["id"]) . '&nbsp;' . stripslashes($row["name"]) . '</div>';
			}
			break;
		/*case "sp_create":
			DBConnect::query("INSERT INTO `sys_pagetemplates`(`name`, `caption`, `description`, `classname`, `url`) VALUES('new template', 'new template', 'put your description here', 'specialpage', 'pages/templates/Page.specialpage.php')", __FILE__, __LINE__);
			$sys_page_id = mysql_insert_id();
			DBConnect::query("INSERT INTO `special_page_cfg`(`sys_page_id`, `name`, `description`) VALUES('" . $sys_page_id . "', 'new template', 'put your description here')", __FILE__, __LINE__);
			echo mysql_insert_id();
			break;*/
		case "sp_load":
			$_REQUEST["output_html"] = true;
			$res = DBConnect::query("SELECT * FROM `site_pagetemplates` WHERE `id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Edit template: ' . stripslashes($row["name"]) . '</div>
						<div class="divright">
							<div class="savebutton" onclick="window[\'site_pagetemplates_form\'].aftersave_success = \'sp_page_refresh_list\'; window[\'site_pagetemplates_form\'].savebutton = $(this); window[\'site_pagetemplates_form\'].post();">Save</div>
						</div>
					</div>
					<div class="contentcontent">';
			form::show_autoform_new('site_pagetemplates', $row, mainconfig::$standardlanguage);
			echo '</div>';
			break;
		case "sp_delete":
			$_REQUEST["output_html"] = true;
			$res = DBConnect::query("SELECT * FROM `site_pagetemplates` WHERE `id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Delete: ' . stripslashes($row["name"]) . '</div>
					</div>
					<div class="contentcontent" style="padding-top: 30px;">';
			if(isset($_GET["delbev"]))
			{
				if($_GET["delbev"] == "yes")
				{
					DBConnect::query("DELETE FROM site_pagetemplates WHERE `id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
					DBConnect::query("DELETE FROM site_pagetemplates_blocks WHERE `pagetemplate`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
					echo 'The template is removed successfully
					<script>
						sp_page_refresh_list(null, null);
					</script>';
				}
				else
					echo 'The template is not removed';
			}
			else
			{
				echo 'Are you sure you want to delete this template?<br><br>
					<div class="savebutton" style="float:left; margin-right:8px;" onclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=templates&action=sp_delete&sp_id=' . $_GET["sp_id"] . '&delbev=yes\');">Yes</div>
					<div class="savebutton" style="float:left;" onclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=templates&action=sp_delete&sp_id=' . $_GET["sp_id"] . '&delbev=no\');">No</div>
					<div style="clear:both;"></div>';
			}
			echo '</div>';
			break;
			/*
			$res = DBConnect::query("SELECT * FROM `special_page_cfg` WHERE `id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
			$row_sp = mysql_fetch_array($res);
			echo '<div class="contentheader"><h1>Edit Special Page: ' . stripslashes($row_sp['name']) . '</h1></div>';
			echo '<div class="TabbedPanels" id="tap_special_page">
					<ul class="TabbedPanelsTabGroup">
						<li class="TabbedPanelsTab" tabindex="0">DBtest</li>
						<li class="TabbedPanelsTab" tabindex="0">Main Cfg</li>
						<li class="TabbedPanelsTab" tabindex="0">Content Template</li>
						<li class="TabbedPanelsTab" tabindex="0">Fields</li>
					</ul>
					<div class="TabbedPanelsContentGroup">
						<div class="TabbedPanelsContent">';
			$dbedit = new dbeditor("dbedit", 500, 500);
			$dbedit->publish(false);
			echo		'</div>
						<div class="TabbedPanelsContent">
							<div id="sp_main_form">';
			$ff = new formfieldnew(NULL, "DATABASE", "special_page_cfg.id", "special_page_cfg.id");
			$ff->publish(stripslashes($row_sp["id"]));
			
			echo '<label>Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "special_page_cfg.name", "special_page_cfg.name");
			$ff->publish(stripslashes($row_sp["name"]));
			
			echo '<br><label>Description</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "special_page_cfg.description", "special_page_cfg.description");
			$ff->publish(stripslashes($row_sp["description"]));
			
			echo '<br><label>Parent sp</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "special_page_cfg.musthaveparent", "special_page_cfg.musthaveparent");
			$ff->publish(stripslashes($row_sp["musthaveparent"]));
			
			echo '<br><label>Data Table</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "special_page_cfg.table", "special_page_cfg.table");
			$ff->publish(stripslashes($row_sp["table"]));
			
			echo '<br><label>Data Table (Language dependent)</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "special_page_cfg.table_lang", "special_page_cfg.table_lang");
			$ff->publish(stripslashes($row_sp["table_lang"]));
			
			echo			'<br><input type="button" value="Save" onclick="show_saving_message(); ajax_post_form(\'sp_main_form\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=save_main\', sp_page_ajaxreturn_save); " />
							</div>
						</div>
						<div class="TabbedPanelsContent">';
			//HTML templates
			echo '<div class="TabbedPanels" id="tap_special_page_content">
					<ul class="TabbedPanelsTabGroup">';
			$counter = 0;
			foreach(mainconfig::$languages as $code => $name)
			{
				echo '<li class="TabbedPanelsTab" tabindex="0">' . $name . '</li>';
				$counter++;
			}
			echo '</ul>
					<div class="TabbedPanelsContentGroup">';
			foreach(mainconfig::$languages as $langcode => $langname)
			{
				echo '<div class="TabbedPanelsContent" id="sp_content_' . $langcode . '">';
				
				$res = DBConnect::query("SELECT * FROM `sp_cfg_content` WHERE `sp_id`='" . $row_sp["id"] . "' AND `lang`='" . $langcode . "'", __FILE__, __LINE__);
				$row_content = mysql_fetch_array($res);
				$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_content.sp_id", "sp_cfg_content.sp_id_" . $langcode);
				$ff->publish(stripslashes($row_sp["id"]));
				
				$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_content.lang", "sp_cfg_content.lang_" . $langcode);
				$ff->publish($langcode);
				
				$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_content.html", "sp_cfg_content.html_" . $langcode, 600, 724);
				$ff->publish(stripslashes($row_content["html"]));
				echo '<br><input type="button" value="Save" onclick="show_saving_message(); ajax_post_form(\'sp_content_' . $langcode . '\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=save_content&lang=' . $langcode . '\', tb_remove); " />';
				echo '</div>';
			}
			echo '</div></div>';
			
			echo 		'</div>
						<div class="TabbedPanelsContent">';
			//Show The Hidden Forms
			//DB FIELD FORM
			echo '<div id="form_sp_input_field" name="form_sp_input_field" style="display:none">
					<div id="form_sp_input_field_form" name="form_sp_input_field_form">';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.id", "sp_cfg_input.id_field");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.sp_id", "sp_cfg_input.sp_id_field");
			$ff->publish(stripslashes($row_sp["id"]));
			
			echo '<label>Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.name", "sp_cfg_input.name_field");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.type", "sp_cfg_input.type_field");
			$ff->publish("");
			
			echo '<hr>';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_field.sp_input_id", "sp_cfg_input_field.sp_input_id");
			$ff->publish("");
			echo '<label>Data Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_field.dataname", "sp_cfg_input_field.dataname");
			$ff->publish("");
			echo '<br><label>Label Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_field.labelname", "sp_cfg_input_field.labelname");
			$ff->publish("");
			echo '<br><label>Lang Dependent</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_field.lang_dependent", "sp_cfg_input_field.lang_dependent");
			$ff->publish("");
			
			echo '<br><input type="button" onclick="ajax_post_form(\'form_sp_input_field_form\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=save_input_field\', sp_page_ajaxreturn_save);" value="Save"/>&nbsp;
				<input style="margin-left: 4px;" type="button" onclick="tb_remove();" value="Cancel"/>';
			echo '</div></div>';
			
			//REPEATER form
			echo '<div id="form_sp_input_repeater" style="display:none">
					<div id="form_sp_input_repeater_form" name="form_sp_input_repeater_form">';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.id", "sp_cfg_input.id_repeater");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.sp_id", "sp_cfg_input.sp_id_repeater");
			$ff->publish(stripslashes($row_sp["id"]));
			
			echo '<label>Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.name", "sp_cfg_input.name_repeater");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.type", "sp_cfg_input.type_repeater");
			$ff->publish("");
			
			echo '<hr>';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.sp_input_id", "sp_cfg_input_repeater.sp_input_id");
			$ff->publish("");
			echo '<label>Table</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.table", "sp_cfg_input_repeater.table");
			$ff->publish("");
			echo '<br><label>Input at top</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.inputtop", "sp_cfg_input_repeater.inputtop");
			$ff->publish(0);
			echo '<br><label>Field lang suffix</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.field_lang_suffix", "sp_cfg_input_repeater.field_lang_suffix");
			$ff->publish("");
			echo '<br><label>Table lang suffix</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.table_lang_suffix", "sp_cfg_input_repeater.table_lang_suffix");
			$ff->publish("");
			echo '<br><label>Pre HTML</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.pre_html", "sp_cfg_input_repeater.pre_html", 10);
			$ff->publish("");
			echo '<br><label>repeat HTML</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.repeat_html", "sp_cfg_input_repeater.repeat_html", 10);
			$ff->publish("");
			echo '<br><label>Post HTML</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.post_html", "sp_cfg_input_repeater.post_html", 10);
			$ff->publish("");
			echo '<br><label>DG hiddenfields</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_repeater.hiddenfields_dg", "sp_cfg_input_repeater.hiddenfields_dg", 10);
			$ff->publish("");
			
			echo '<br><input type="button" onclick="ajax_post_form(\'form_sp_input_repeater_form\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=save_input_repeater\', sp_page_ajaxreturn_save);" value="Save"/>&nbsp;
				<input style="margin-left: 4px;" type="button" onclick="tb_remove();" value="Cancel"/>';
			echo '</div></div>';
			
			//CODE form
			echo '<div id="form_sp_input_code" style="display:none">
					<div id="form_sp_input_code_form" name="form_sp_input_code_form">';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.id", "sp_cfg_input.id_code");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.sp_id", "sp_cfg_input.sp_id_code");
			$ff->publish(stripslashes($row_sp["id"]));
			
			echo '<label>Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.name", "sp_cfg_input.name_code");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.type", "sp_cfg_input.type_code");
			$ff->publish("");
			
			echo '<hr>';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_code.sp_input_id", "sp_cfg_input_code.sp_input_id");
			$ff->publish("");
			echo '<label>Snippet</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_code.snippet", "sp_cfg_input_code.snippet");
			$ff->publish("");
			
			echo '<br><label>Backcode</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_code.back", "sp_cfg_input_code.back");
			$ff->publish("");
			
			echo '<br><label>Is block</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input_code.is_block", "sp_cfg_input_code.is_block");
			$ff->publish("");
			
			echo '<br><input type="button" onclick="ajax_post_form(\'form_sp_input_code_form\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=save_input_code\', sp_page_ajaxreturn_save);" value="Save"/>&nbsp;
				<input style="margin-left: 4px;" type="button" onclick="tb_remove();" value="Cancel"/>';
			echo '</div></div>';
			
			//SPLITTER form
			echo '<div id="form_sp_input_splitter" style="display:none">
					<div id="form_sp_input_splitter_form" name="form_sp_input_splitter_form">';
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.id", "sp_cfg_input.id_splitter");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.sp_id", "sp_cfg_input.sp_id_splitter");
			$ff->publish(stripslashes($row_sp["id"]));
			
			echo '<label>Name</label>';
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.name", "sp_cfg_input.name_splitter");
			$ff->publish("");
			
			$ff = new formfieldnew(NULL, "DATABASE", "sp_cfg_input.type", "sp_cfg_input.type_splitter");
			$ff->publish("");
			
			echo '<br><input type="button" onclick="ajax_post_form(\'form_sp_input_splitter_form\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=save_input_splitter\', sp_page_ajaxreturn_save);" value="Save"/>&nbsp;
				<input style="margin-left: 4px;" type="button" onclick="tb_remove();" value="Cancel"/>';
			echo '</div></div>';
			
			//FORMKE MET DE TYPE KEUZE KNOPPEN VOOR EEN NIEUWE INPUT
			echo '<div id="form_sp_input_choose_type" style="display:none">';
			echo '<div style="text-align: center">';
			echo '<input style="margin-top: 8px;" type="button" onclick="document.getElementById(\'form_sp_input_choose_type\').style.display=\'none\'; send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=loadinput&input_id=new_field&sp_id=' . $row_sp["id"] . '\', \'\', sp_fill_input_form);" value="Database Field"/><br>
					<input style="margin-top: 8px;" type="button" onclick="document.getElementById(\'form_sp_input_choose_type\').style.display=\'none\'; send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=loadinput&input_id=new_repeater&sp_id=' . $row_sp["id"] . '\', \'\', sp_fill_input_form);" value="Repeater"/><br>
					<input style="margin-top: 8px;" type="button" onclick="document.getElementById(\'form_sp_input_choose_type\').style.display=\'none\'; send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=loadinput&input_id=new_code&sp_id=' . $row_sp["id"] . '\', \'\', sp_fill_input_form);" value="Code snippet"/><br>
					<input style="margin-top: 8px;" type="button" onclick="document.getElementById(\'form_sp_input_choose_type\').style.display=\'none\'; send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=loadinput&input_id=new_splitter&sp_id=' . $row_sp["id"] . '\', \'\', sp_fill_input_form);" value="Splitter"/><br><br>
				<input type="button" onclick="tb_remove();" value="Cancel"/>';
			echo '</div></div>';
			
			//WE SHOW THE FIELDS DATAGRID
			$ds = new datasource();
			$ds->type = "DATABASE";
			$ds->db_table = "sp_cfg_input";
			$ds->sort_field = "order";
			$ds->sort_order = "ASC";
			$ds->db_extra_where = "`sp_id`='" . $row_sp["id"] . "'";
				
			$dg = new datagridnew();
			$dg->show_title_bar = false;
			$dg->checkbox = false;
			$dg->id = "dg_sp_inputs";
			$dg->datasource = $ds;
			$dg->id_field = "id";
			$dg->sort_field = "order";
			$dg->rowdblclick = 'send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=loadinput&input_id=\' + dg_dg_sp_inputs.selected_id, \'\', sp_fill_input_form);';
			$dg->paging = false;
			$dg->perpage = 30;
			$dg->addicon("new", "/css/back/icon/twotone/plus.gif", "/css/back/icon/twotone/gray/plus.gif", 'tb_show(\'Choose your type\', \'#TB_inline?height=150&width=200&inlineId=form_sp_input_choose_type&modal=false\', false);', false, false, false);
			$dg->addicon("edit", "/css/back/icon/twotone/edit.gif", "/css/back/icon/twotone/gray/edit.gif", 'send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=loadinput&input_id=\' + dg_dg_sp_inputs.selected_id, \'\', sp_fill_input_form);', true, true, true);
			$dg->addicon("delete", "/css/back/icon/twotone/trash.gif", "/css/back/icon/twotone/gray/trash.gif", 'show_question_message(\'Are you sure you want to delete the selected inputs?\', function(){sp_input_delete_accept();}, tb_remove);', true, true, true);
			$dg->add_icon_splitter();
			$dg->addicon("move up", "/css/back/icon/twotone/arrow-up.gif", "/css/back/icon/twotone/gray/arrow-up.gif", 'send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=moveup&input_id=\' + dg_dg_sp_inputs.selected_id, \'\', sp_movesort_return);', true, true, true);
			$dg->addicon("move down", "/css/back/icon/twotone/arrow-down.gif", "/css/back/icon/twotone/gray/arrow-down.gif", 'send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=specialpages&action=movedown&input_id=\' + dg_dg_sp_inputs.selected_id, \'\', sp_movesort_return);', true, true, true);
			
			$dg->addfield("order", "order", true, true, true, 0, false);
			$dg->addfield("name", "name", true, true, true, 0, false);
			$dg->addfield("type", "type", true, true, true, 0, false);
				
			$dg->publish(false);
			
			echo		'</div>
					</div>
				</div>
				<script language="JavaScript" type="text/javascript">
					var tap_special_page_content = new Spry.Widget.TabbedPanels("tap_special_page_content", { defaultTab: 0 });
					var tap_special_page = new Spry.Widget.TabbedPanels("tap_special_page", { defaultTab: 0 });
				</script>';
						
			break;
		case "save_main":
			$errormsgs = data_description::validate_post_db();
			if(count($errormsgs) == 0)
			{
				$sql = data_description::create_sql_from_post("special_page_cfg", "id");
				if($sql != "")
					DBConnect::query($sql, __FILE__, __LINE__);
				$res = DBConnect::query("SELECT * FROM `special_page_cfg` WHERE `id`='" . addslashes($_POST["special_page_cfg.id"]) . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				DBConnect::query("UPDATE `sys_pagetemplates` SET `name`='" . addslashes($_POST["special_page_cfg.name"]) . "', `caption`='" . addslashes($_POST["special_page_cfg.name"]) . "', `description`='" . addslashes($_POST["special_page_cfg.description"]) . "' WHERE `id`='" . $row["sys_page_id"] . "'", __FILE__, __LINE__);
				echo 'OK';
			}
			else
			{
				header('Content-Type: text/xml');
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				echo '<?xml version="1.0" encoding="ISO-8859-1"?>
					<errors>';
				foreach($errormsgs as $name => $error)
					echo '<error><fieldid>' . $name . '</fieldid><desc>' . $error . '</desc></error>';
				echo '</errors>';
			}
			break;
		case "save_content":
			$res = DBConnect::query("SELECT * FROM `sp_cfg_content` WHERE `sp_id`='" . addslashes($_POST["sp_cfg_content.sp_id_" . $_GET['lang']]) . "' AND `lang`='" . addslashes($_POST["sp_cfg_content.lang_" . $_GET['lang']]) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				//we doen een UPDATE
				DBConnect::query("UPDATE `sp_cfg_content` SET `html`='" . addslashes($_POST["sp_cfg_content.html_" . $_GET['lang']]) . "' WHERE `sp_id`='" . addslashes($_POST["sp_cfg_content.sp_id_" . $_GET['lang']]) . "' AND `lang`='" . addslashes($_POST["sp_cfg_content.lang_" . $_GET['lang']]) . "'", __FILE__, __LINE__);
			}
			else
			{
				//we doen een insert
				DBConnect::query("INSERT INTO `sp_cfg_content` VALUES ('" . addslashes($_POST["sp_cfg_content.sp_id_" . $_GET['lang']]) . "', '" . addslashes($_POST["sp_cfg_content.lang_" . $_GET['lang']]) . "', '" . addslashes($_POST["sp_cfg_content.html_" . $_GET['lang']]) . "')", __FILE__, __LINE__);
			}
			break;
		case "save_input_field":
			if(trim($_POST["sp_cfg_input.id_field"]) == "")
			{
				//we do an insert
				//search for order+1
				$order = DataOrder::get_order_last('sp_cfg_input', 'order', 'sp_id', addslashes($_POST["sp_cfg_input.sp_id_field"]));
				DBConnect::query("INSERT INTO `sp_cfg_input`(`sp_id`, `order`, `name`, `type`) VALUES('" . addslashes($_POST["sp_cfg_input.sp_id_field"]) . "', '" . $order . "', '" . addslashes($_POST["sp_cfg_input.name_field"]) . "', '" . addslashes($_POST["sp_cfg_input.type_field"]) . "')", __FILE__, __LINE__);
				$newid = mysql_insert_id();
				DBConnect::query("INSERT INTO `sp_cfg_input_field`(`sp_input_id`, `dataname`, `labelname`, `lang_dependent`) VALUES('" . $newid . "', '" . addslashes($_POST["sp_cfg_input_field.dataname"]) . "', '" . addslashes($_POST["sp_cfg_input_field.labelname"]) . "', '" . addslashes($_POST["sp_cfg_input_field.lang_dependent"]) . "')", __FILE__, __LINE__);
			}
			else
			{
				//we do an edit
				DBConnect::query("UPDATE `sp_cfg_input` SET `name`='" . addslashes($_POST["sp_cfg_input.name_field"]) . "' WHERE id='" . addslashes($_POST["sp_cfg_input.id_field"]) . "'", __FILE__, __LINE__);
				DBConnect::query("UPDATE `sp_cfg_input_field` SET `dataname`='" . addslashes($_POST["sp_cfg_input_field.dataname"]) . "', `labelname`='" . addslashes($_POST["sp_cfg_input_field.labelname"]) . "', `lang_dependent`='" . addslashes($_POST["sp_cfg_input_field.lang_dependent"]) . "' WHERE sp_input_id='" . addslashes($_POST["sp_cfg_input.id_field"]) . "'", __FILE__, __LINE__);
			}
			echo 'INPUTOK';
			break;
		case "save_input_repeater":
			if(trim($_POST["sp_cfg_input.id_repeater"]) == "")
			{
				//we do an insert
				//search for order+1
				$order = DataOrder::get_order_last('sp_cfg_input', 'order');
				DBConnect::query("INSERT INTO `sp_cfg_input`(`sp_id`, `order`, `name`, `type`) VALUES('" . addslashes($_POST["sp_cfg_input.sp_id_repeater"]) . "', '" . $order . "', '" . addslashes($_POST["sp_cfg_input.name_repeater"]) . "', '" . addslashes($_POST["sp_cfg_input.type_repeater"]) . "')", __FILE__, __LINE__);
				$newid = mysql_insert_id();
				DBConnect::query("INSERT INTO `sp_cfg_input_repeater`(`sp_input_id`, `table`, `inputtop`, `field_lang_suffix`, `table_lang_suffix`, `pre_html`, `repeat_html`, `post_html`, `hiddenfields_dg`) VALUES('" . $newid . "', '" . addslashes($_POST["sp_cfg_input_repeater.table"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.inputtop"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.field_lang_suffix"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.table_lang_suffix"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.pre_html"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.repeat_html"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.post_html"]) . "', '" . addslashes($_POST["sp_cfg_input_repeater.hiddenfields_dg"]) . "')", __FILE__, __LINE__);
			}
			else
			{
				//we do an edit
				DBConnect::query("UPDATE `sp_cfg_input` SET `name`='" . addslashes($_POST["sp_cfg_input.name_repeater"]) . "' WHERE id='" . addslashes($_POST["sp_cfg_input.id_repeater"]) . "'", __FILE__, __LINE__);
				DBConnect::query("UPDATE `sp_cfg_input_repeater` SET `table`='" . addslashes($_POST["sp_cfg_input_repeater.table"]) . "', `inputtop`='" . addslashes($_POST["sp_cfg_input_repeater.inputtop"]) . "', `field_lang_suffix`='" . addslashes($_POST["sp_cfg_input_repeater.field_lang_suffix"]) . "', `table_lang_suffix`='" . addslashes($_POST["sp_cfg_input_repeater.table_lang_suffix"]) . "', `pre_html`='" . addslashes($_POST["sp_cfg_input_repeater.pre_html"]) . "', `repeat_html`='" . addslashes($_POST["sp_cfg_input_repeater.repeat_html"]) . "', `post_html`='" . addslashes($_POST["sp_cfg_input_repeater.post_html"]) . "', `hiddenfields_dg`='" . addslashes($_POST["sp_cfg_input_repeater.hiddenfields_dg"]) . "' WHERE sp_input_id='" . addslashes($_POST["sp_cfg_input.id_repeater"]) . "'", __FILE__, __LINE__);
			}
			echo 'INPUTOK';
			break;
		case "save_input_code":
			if(trim($_POST["sp_cfg_input.id_code"]) == "")
			{
				//we do an insert
				//search for order+1
				$order = DataOrder::get_order_last('sp_cfg_input', 'order');
				DBConnect::query("INSERT INTO `sp_cfg_input`(`sp_id`, `order`, `name`, `type`) VALUES('" . addslashes($_POST["sp_cfg_input.sp_id_code"]) . "', '" . $order . "', '" . addslashes($_POST["sp_cfg_input.name_code"]) . "', '" . addslashes($_POST["sp_cfg_input.type_code"]) . "')", __FILE__, __LINE__);
				$newid = mysql_insert_id();
				DBConnect::query("INSERT INTO `sp_cfg_input_code`(`sp_input_id`, `snippet`, `back`, `is_block`) VALUES('" . $newid . "', '" . addslashes($_POST["sp_cfg_input_code.snippet"]) . "', '" . (($_POST["sp_cfg_input_code.back"] > 0)?"1":"0") . "', '" . (($_POST["sp_cfg_input_code.is_block"] > 0)?"1":"0") . "')", __FILE__, __LINE__);
			}
			else
			{
				//we do an edit
				DBConnect::query("UPDATE `sp_cfg_input` SET `name`='" . addslashes($_POST["sp_cfg_input.name_code"]) . "' WHERE id='" . addslashes($_POST["sp_cfg_input.id_code"]) . "'", __FILE__, __LINE__);
				DBConnect::query("UPDATE `sp_cfg_input_code` SET `snippet`='" . addslashes($_POST["sp_cfg_input_code.snippet"]) . "', `back`='" . (($_POST["sp_cfg_input_code.back"] > 0)?"1":"0") . "', `is_block`='" . (($_POST["sp_cfg_input_code.is_block"] > 0)?"1":"0") . "' WHERE sp_input_id='" . addslashes($_POST["sp_cfg_input.id_code"]) . "'", __FILE__, __LINE__);
			}
			echo 'INPUTOK';
			break;
		case "save_input_splitter":
			if(trim($_POST["sp_cfg_input.id_splitter"]) == "")
			{
				//we do an insert
				//search for order+1
				$order = DataOrder::get_order_last('sp_cfg_input', 'order');
				DBConnect::query("INSERT INTO `sp_cfg_input`(`sp_id`, `order`, `name`, `type`) VALUES('" . addslashes($_POST["sp_cfg_input.sp_id_splitter"]) . "', '" . $order . "', '" . addslashes($_POST["sp_cfg_input.name_splitter"]) . "', '" . addslashes($_POST["sp_cfg_input.type_splitter"]) . "')", __FILE__, __LINE__);
			}
			else
			{
				//we do an edit
				DBConnect::query("UPDATE `sp_cfg_input` SET `name`='" . addslashes($_POST["sp_cfg_input.name_splitter"]) . "' WHERE id='" . addslashes($_POST["sp_cfg_input.id_splitter"]) . "'", __FILE__, __LINE__);
			}
			echo 'INPUTOK';
			break;
		case "loadinput":
			header('Content-Type: text/xml');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			echo '<?xml version="1.0" encoding="ISO-8859-1"?>
				<fields>';
			switch($_GET["input_id"])
			{
				case "new_field":
					echo '<field><id>sp_cfg_input.id_field</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.sp_id_field</id><type>HIDDEN</type><value><![CDATA[' . $_GET["sp_id"] . ']]></value></field>
						<field><id>sp_cfg_input.name_field</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.type_field</id><type>HIDDEN</type><value><![CDATA[dbfield]]></value></field>
						<field><id>sp_cfg_input_field.sp_input_id</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_field.dataname</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_field.labelname</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_field.lang_dependent</id><type>YESNO</type><value><![CDATA[]]></value></field>
						<inputtype>dbfield</inputtype>';
					break;
				case "new_repeater":
					echo '<field><id>sp_cfg_input.id_repeater</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.sp_id_repeater</id><type>HIDDEN</type><value><![CDATA[' . $_GET["sp_id"] . ']]></value></field>
						<field><id>sp_cfg_input.name_repeater</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.type_repeater</id><type>HIDDEN</type><value><![CDATA[repeater]]></value></field>
						<field><id>sp_cfg_input_repeater.sp_input_id</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.table</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.inputtop</id><type>YESNO</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.field_lang_suffix</id><type>YESNO</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.table_lang_suffix</id><type>YESNO</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.pre_html</id><type>TEXT</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.repeat_html</id><type>TEXT</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.post_html</id><type>TEXT</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_repeater.hiddenfields_dg</id><type>TEXT</type><value><![CDATA[]]></value></field>
						<inputtype>repeater</inputtype>';
					break;
				case "new_code":
					echo '<field><id>sp_cfg_input.id_code</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.sp_id_code</id><type>HIDDEN</type><value><![CDATA[' . $_GET["sp_id"] . ']]></value></field>
						<field><id>sp_cfg_input.name_code</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.type_code</id><type>HIDDEN</type><value><![CDATA[code]]></value></field>
						<field><id>sp_cfg_input_code.sp_input_id</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_code.snippet</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input_code.back</id><type>YESNO</type><value><![CDATA[0]]></value></field>
						<field><id>sp_cfg_input_code.is_block</id><type>YESNO</type><value><![CDATA[0]]></value></field>
						<inputtype>code</inputtype>';
					break;
				case "new_splitter":
					echo '<field><id>sp_cfg_input.id_splitter</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.sp_id_splitter</id><type>HIDDEN</type><value><![CDATA[' . $_GET["sp_id"] . ']]></value></field>
						<field><id>sp_cfg_input.name_splitter</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
						<field><id>sp_cfg_input.type_splitter</id><type>HIDDEN</type><value><![CDATA[splitter]]></value></field>
						<inputtype>splitter</inputtype>';
					break;
				default:
					$res = DBConnect::query("SELECT * FROM `sp_cfg_input` WHERE id='" . addslashes($_GET["input_id"]) . "'", __FILE__, __LINE__);
					$row_inp = mysql_fetch_array($res);
					if($row_inp)
					{
						$suffix = "";
						if($row_inp["type"] == "dbfield") $suffix = "field";
						if($row_inp["type"] == "repeater") $suffix = "repeater";
						if($row_inp["type"] == "code") $suffix = "code";
						if($row_inp["type"] == "splitter") $suffix = "splitter";
						
						echo '<field><id>sp_cfg_input.id_' . $suffix . '</id><type>HIDDEN</type><value><![CDATA[' . stripslashes($row_inp["id"]) . ']]></value></field>
							<field><id>sp_cfg_input.sp_id_' . $suffix . '</id><type>HIDDEN</type><value><![CDATA[' . stripslashes($row_inp["sp_id"]) . ']]></value></field>
							<field><id>sp_cfg_input.name_' . $suffix . '</id><type>VARCHAR</type><value><![CDATA[' . stripslashes($row_inp["name"]) . ']]></value></field>
							<field><id>sp_cfg_input.type_' . $suffix . '</id><type>HIDDEN</type><value><![CDATA[' . stripslashes($row_inp["type"]) . ']]></value></field>';
						//type specific
						switch($row_inp["type"])
						{
							case "dbfield":
								$res_spec = DBConnect::query("SELECT * FROM `sp_cfg_input_field` WHERE `sp_input_id`='" . $row_inp["id"] . "'", __FILE__, __LINE__);
								$row_spec = mysql_fetch_array($res_spec);
								echo '<field><id>sp_cfg_input_field.sp_input_id</id><type>HIDDEN</type><value><![CDATA[' . stripslashes($row_spec["sp_input_id"]) . ']]></value></field>
									<field><id>sp_cfg_input_field.dataname</id><type>VARCHAR</type><value><![CDATA[' . stripslashes($row_spec["dataname"]) . ']]></value></field>
									<field><id>sp_cfg_input_field.labelname</id><type>VARCHAR</type><value><![CDATA[' . stripslashes($row_spec["labelname"]) . ']]></value></field>
									<field><id>sp_cfg_input_field.lang_dependent</id><type>YESNO</type><value><![CDATA[' . stripslashes($row_spec["lang_dependent"]) . ']]></value></field>';
								break;
							case "repeater":
								$res_spec = DBConnect::query("SELECT * FROM `sp_cfg_input_repeater` WHERE `sp_input_id`='" . $row_inp["id"] . "'", __FILE__, __LINE__);
								$row_spec = mysql_fetch_array($res_spec);
								echo '<field><id>sp_cfg_input_field.sp_input_id</id><type>HIDDEN</type><value><![CDATA[' . stripslashes($row_spec["sp_input_id"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.table</id><type>VARCHAR</type><value><![CDATA[' . stripslashes($row_spec["table"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.inputtop</id><type>YESNO</type><value><![CDATA[' . stripslashes($row_spec["inputtop"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.field_lang_suffix</id><type>YESNO</type><value><![CDATA[' . stripslashes($row_spec["field_lang_suffix"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.table_lang_suffix</id><type>YESNO</type><value><![CDATA[' . stripslashes($row_spec["table_lang_suffix"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.pre_html</id><type>TEXT</type><value><![CDATA[' . stripslashes($row_spec["pre_html"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.repeat_html</id><type>TEXT</type><value><![CDATA[' . stripslashes($row_spec["repeat_html"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.post_html</id><type>TEXT</type><value><![CDATA[' . stripslashes($row_spec["post_html"]) . ']]></value></field>
									<field><id>sp_cfg_input_repeater.hiddenfields_dg</id><type>TEXT</type><value><![CDATA[' . stripslashes($row_spec["hiddenfields_dg"]) . ']]></value></field>';
								break;
							case "code":
								$res_spec = DBConnect::query("SELECT * FROM `sp_cfg_input_code` WHERE `sp_input_id`='" . $row_inp["id"] . "'", __FILE__, __LINE__);
								$row_spec = mysql_fetch_array($res_spec);
								echo '<field><id>sp_cfg_input_field.sp_input_id</id><type>HIDDEN</type><value><![CDATA[' . stripslashes($row_spec["sp_input_id"]) . ']]></value></field>
									<field><id>sp_cfg_input_code.snippet</id><type>VARCHAR</type><value><![CDATA[' . stripslashes($row_spec["snippet"]) . ']]></value></field>
									<field><id>sp_cfg_input_code.back</id><type>YESNO</type><value><![CDATA[' . stripslashes($row_spec["back"]) . ']]></value></field>
									<field><id>sp_cfg_input_code.is_block</id><type>YESNO</type><value><![CDATA[' . stripslashes($row_spec["is_block"]) . ']]></value></field>';
								break;
						}
						echo '<inputtype>' . stripslashes($row_inp["type"]) . '</inputtype>';
					}
					else
					{
						echo '<field><id>sp_cfg_input.id_field</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
							<field><id>sp_cfg_input.sp_id_field</id><type>HIDDEN</type><value><![CDATA[' . $_GET["sp_id"] . ']]></value></field>
							<field><id>sp_cfg_input.name_field</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
							<field><id>sp_cfg_input.type_field</id><type>HIDDEN</type><value><![CDATA[dbfield]]></value></field>
							<field><id>sp_cfg_input_field.sp_input_id</id><type>HIDDEN</type><value><![CDATA[]]></value></field>
							<field><id>sp_cfg_input_field.dataname</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
							<field><id>sp_cfg_input_field.labelname</id><type>VARCHAR</type><value><![CDATA[]]></value></field>
							<field><id>sp_cfg_input_field.lang_dependent</id><type>YESNO</type><value><![CDATA[]]></value></field>
							<inputtype>dbfield</inputtype>';
					}
					//load an existing
					break;
			}
			echo '</fields>';
			break;
		case "moveup":
			//we zoeken nog even de sp_id op
			$res = DBConnect::query("SELECT * FROM `sp_cfg_input` WHERE id='" . addslashes($_GET["input_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			DataOrder::move_one_up("sp_cfg_input", "order", $_GET["input_id"], "sp_id", $row["sp_id"]);
			break;
		case "movedown":
			$res = DBConnect::query("SELECT * FROM `sp_cfg_input` WHERE id='" . addslashes($_GET["input_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			DataOrder::move_one_down("sp_cfg_input", "order", $_GET["input_id"], "sp_id", $row["sp_id"]);
			break;
		case "delete_input":
			$ids = explode('##', urldecode($_POST["delete"]));
			var_dump($ids);
			foreach($ids as $one_id)
			{
				$res = DBConnect::query("SELECT * FROM `sp_cfg_input` WHERE id='" . $one_id . "'", __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					switch($row["type"])
					{
						case "dbfield":
							DBConnect::query("DELETE FROM `sp_cfg_input_field` WHERE `sp_input_id`='" . addslashes($one_id) . "'", __FILE__, __LINE__);
							break;
						case "repeater":
							DBConnect::query("DELETE FROM `sp_cfg_input_repeater` WHERE `sp_input_id`='" . addslashes($one_id) . "'", __FILE__, __LINE__);
							break;
						case "code":
							DBConnect::query("DELETE FROM `sp_cfg_input_code` WHERE `sp_input_id`='" . addslashes($one_id) . "'", __FILE__, __LINE__);
							break;
					}
					DBConnect::query("DELETE FROM `sp_cfg_input` WHERE `id`='" . $one_id . "'", __FILE__, __LINE__);
				}
			}
			DataOrder::reorder_table("sp_cfg_input", "order", "name", "sp_id");
			break;
		case "sp_delete":
			//ALLE VELDEN ENZO OOK DELETEN!!!!
			$res = DBConnect::query("SELECT * FROM `sp_cfg_input` WHERE `sp_id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
			{
				switch($row["type"])
				{
					case "dbfield":
						DBConnect::query("DELETE FROM `sp_cfg_input_field` WHERE `sp_input_id`='" . $row["id"] . "'", __FILE__, __LINE__);
						break;
					case "repeater":
						DBConnect::query("DELETE FROM `sp_cfg_input_repeater` WHERE `sp_input_id`='" . $row["id"] . "'", __FILE__, __LINE__);
						break;
					case "code":
						DBConnect::query("DELETE FROM `sp_cfg_input_code` WHERE `sp_input_id`='" . $row["id"] . "'", __FILE__, __LINE__);
						break;
				}
				DBConnect::query("DELETE FROM `sp_cfg_input` WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
			}
			$res = DBConnect::query("SELECT * FROM `special_page_cfg` WHERE `id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			DBConnect::query("DELETE FROM `sys_pagetemplates` WHERE `id`='" . $row["sys_page_id"] . "'", __FILE__, __LINE__);
			DBConnect::query("DELETE FROM `special_page_cfg` WHERE `id`='" . addslashes($_GET["sp_id"]) . "'", __FILE__, __LINE__);
			DataOrder::reorder_table("sp_cfg_input", "order", "name", "sp_id");
			echo "OK";
			break;*/
	}
?>