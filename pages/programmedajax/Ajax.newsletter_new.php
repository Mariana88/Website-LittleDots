<?php
	if(!login::right("backpage_newsletter_new", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	switch($_GET["action"])
	{
		case "getallcontacts":
			$res = DBConnect::query("SELECT * FROM `site_user_front`", __FILE__, __LINE__);
			$first = true;
			while($row = mysql_fetch_array($res))
			{
				if($first)
				{
					echo $row["email"];
					$first = false;
				}
				else
					echo ", " . $row["email"];
			}
			break;
		case "getezinecontacts":
			$res = DBConnect::query("SELECT * FROM `site_user_front` WHERE `ezine`='1'", __FILE__, __LINE__);
			$first = true;
			while($row = mysql_fetch_array($res))
			{
				if($first)
				{
					echo $row["email"];
					$first = false;
				}
				else
					echo ", " . $row["email"];
			}
			break;
		case "getsectorcontacts":
			$res = DBConnect::query("SELECT * FROM `site_user_front` WHERE `sectoren` LIKE '%" . $_GET["sector"] . "%'", __FILE__, __LINE__);
			$first = true;
			while($row = mysql_fetch_array($res))
			{
				if($first)
				{
					echo $row["email"];
					$first = false;
				}
				else
					echo ", " . $row["email"];
			}
			break;
		case "gettaalcontacts":
			$res = DBConnect::query("SELECT * FROM `site_user_front` WHERE `taal` LIKE '" . $_GET["taal"] . "'", __FILE__, __LINE__);
			$first = true;
			while($row = mysql_fetch_array($res))
			{
				if($first)
				{
					echo $row["email"];
					$first = false;
				}
				else
					echo ", " . $row["email"];
			}
			break;
		case "selmech":
			$extra_where = "";
			if(isset($_POST["site_user_front.id"]))
			{
				//we maken de extra_where voor de datagrid
				foreach($_POST as $key => $value)
				{
					if(trim($value) == "" || trim($value) == "0")
						continue;
					$test = explode(".", $key);
					$table = $test[0];
					$field = $test[1];
					//een nofield negeren we
					$test = explode("_", $key);
					if($test[count($test) - 1] == "nofield")
						continue;
					//checken of het een veld is met checkbox opties
					if(isset($_POST[$key . "_chk_nofield"]))
					{
						//we moeten alle opties checken
						$opties = explode(";", $value);
						$counter = 0;
						if($extra_where == "")
							$extra_where .= " (";
						else
							$extra_where .= " AND (";
						foreach($opties as $optie)
						{
							if ($counter == 0)
								$extra_where .= " `" . $field . "` LIKE '%" . $optie . "%' ";
							else
								$extra_where .= " OR `" . $field . "` LIKE '%" . $optie . "%' ";
							$counter++;
						}
						$extra_where .= ") ";
					}
					else
					{
						if($key == "site_user_front.creatie")
						{
							if ($extra_where == 0)
								$extra_where .= " (`" . $field . "`>='" . DateAndTime::check_date("d/m/Y", $value) . "' AND `" . $field . "`<='" . (DateAndTime::check_date("d/m/Y", $value) + 86400) . "') ";
							else
								$extra_where .= " AND (`" . $field . "`>='" . DateAndTime::check_date("d/m/Y", $value) . "' AND `" . $field . "`<='" . (DateAndTime::check_date("d/m/Y", $value) + 86400) . "') ";
						}
						elseif($key == "site_user_front.ezine")
						{
							if($extra_where == "")
								$extra_where .= " `" . $field . "`>'0' ";
							else
								$extra_where .= " AND `" . $field . "`>'0' ";
						}
						else
						{
							if($extra_where == "")
								$extra_where .= " `" . $field . "` LIKE '%" . $value . "%' ";
							else
								$extra_where .= " AND `" . $field . "` LIKE '%" . $value . "%' ";
						}
					}
				}
			}
			echo '<div class="splitter"><span>Filter</span></div>';
			echo '<span style="cursor: pointer" onClick="document.getElementById(\'newsletter_contact_select_filter\').style.display = \'Block\'; this.style.display = \'none\';">Show filter</span>';
			echo '<div id="newsletter_contact_select_filter" style="display:none;">';
			form::show_autoform("site_user_front", $_POST, array("id" => "id", "user_group" => "user_group", "lname" => "lname", "lpass" => "lpass", "can_login" => "can_login"));
			echo '<input type="button" value="Filter" onClick="var thepoststr = ajax_post_form(\'newsletter_contact_select_filter\', \'\', null, true); 
							var thehead = new Object();  
							thehead[\'Content-Type\'] = \'application/x-www-form-urlencoded; charset=UTF-8\';
							form_contactselect.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=selmech\', {method:\'POST\', headers: thehead, postData:thepoststr});"/>';
			
			echo '</div>';
			
			echo '<div class="splitter"><span>Results</span></div>';
			//tonen van selectie mechanisme
			$ds = new datasource();
			$ds->type = "DATABASE";
			$ds->db_table = "site_user_front";
			
			if($extra_where != "")
				$ds->db_extra_where = $extra_where;
			
			$dg = new datagridnew();
			$dg->title = "Select Contacts";
			$dg->show_title_bar = false;
			$dg->checkbox = true;
			$dg->id = "dg_newsletter_selectcontacts";
			$dg->datasource = $ds;
			$dg->id_field = "email";
			//if(trim($autosortfield) != "")
			//	$dg->sort_field = $autosortfield;
			//$dg->rowdblclick = 'tb_show(\'Edit or add a data entry\', \'#TB_inline?height=500&width=650&inlineId=tb_de_' . $this->id . '&modal=false\', false); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=\' + dg_dg_de_' . $this->id . '.selected_id, \'\', cms2_fill_form);';
			$dg->paging = false;
			$dg->perpage = 50;
			//$dg->addicon("new", "/css/back/icon/twotone/plus.gif", "/css/back/icon/twotone/gray/plus.gif", 'tb_show(\'Edit or add a data entry\', \'#TB_inline?height=500&width=650&inlineId=tb_de_' . $this->id . '&modal=false\', false); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=new\', \'\', cms2_fill_form);', false, false, false);
			//$dg->addicon("edit", "/css/back/icon/twotone/edit.gif", "/css/back/icon/twotone/gray/edit.gif", 'tb_show(\'Edit or add a data entry\', \'#TB_inline?height=500&width=650&inlineId=tb_de_' . $this->id . '&modal=false\', false); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=\' + dg_dg_de_' . $this->id . '.selected_id, \'\', cms2_fill_form);', true, true, true);
			//$dg->addicon("delete", "/css/back/icon/twotone/trash.gif", "/css/back/icon/twotone/gray/trash.gif", 'show_question_message(\'Are you sure you want to delete the selected entries?\', function(){dataeditor_delete_accept(dg_dg_de_' . $this->id . '.selected_ids, \'' . $this->id . '\', \'dg_de_' . $this->id . '\', dg_dg_de_' . $this->id . '_html_panel);}, tb_remove);', true, true, true);
			
			$result_fields = DBConnect::query("SHOW COLUMNS FROM `site_user_front`", __FILE__, __LINE__);
			
			while($row_field = mysql_fetch_array($result_fields))
			{
				if($row_field["Field"] != "id" && $row_field["Field"] != "user_group" && $row_field["Field"] != "lname" && $row_field["Field"] != "lpass" && $row_field["Field"] != "can_login")
				{
					$ddesc = new data_description("","","DATABASE", "site_user_front." . $row_field["Field"]);
					
					$dg->addfield($row_field["Field"], $row_field["Field"], true, true, true, 0, false, $ddesc->type);
				}
			}
				
			$dg->publish(false);
			
			echo '<div style="text-align:right;">
					<input type="button" value="Add selection" onClick="newsletter_send_addcontacts_str(dg_dg_newsletter_selectcontacts.get_checked_ids(\', \')); tb_remove()"/>&nbsp;
					<input type="button" value="Cancel" onClick="tb_remove()"/>
				</div>';
			break;
		case "content_choice":
			//keuze voor platte text
			echo '<div style="width: 486px; float: left; text-align: center;">Create a flat text mail with little html opportunities.<br><br><input class="nowidth" type="button" Value="Text mail" onClick="newsletternew_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=content_text\');"></div>';
			
			//keuze voor EZINE
			echo '<div style="width: 486px; float: left; text-align: center;">
					Select witch e-zine you want to send.<br><br>
					<select id="newsletter_contentchoice_ezine" class="nowidth">';
			$res = DBConnect::query("SELECT site_page.menu_name, site_page.root_id, site_page_root.parent_id FROM site_page_root, site_page WHERE site_page_root.id = site_page.root_id AND site_page.lang='NL' AND site_page_root.template_id='21'", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
			{
				$res_parent = DBConnect::query("SELECT site_page.menu_name, site_page.root_id FROM site_page_root, site_page WHERE site_page_root.id = site_page.root_id AND site_page.lang='NL' AND site_page_root.id='" . $row["parent_id"] . "'", __FILE__, __LINE__);
				$row_parent = mysql_fetch_array($res_parent);
				echo '<option value="' . $row["root_id"] . '">' . $row_parent["menu_name"] . ' > ' . $row["menu_name"] . '</option>';
			}
			echo '</select><br>
					<select id="newsletter_contentchoice_ezine_lang" class="nowidth">';
			foreach(mainconfig::$languages as $lcode => $lname)
			{
				echo '<option value="' . $lcode . '">' . $lname . '</option>';
			}
			echo '</select><br><br><input class="nowidth" type="button" Value="Load Ezine Page" onClick="newsletternew_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=content_ezine&ezine_id=\' + document.getElementById(\'newsletter_contentchoice_ezine\').value + \'&ezine_lang=\' + document.getElementById(\'newsletter_contentchoice_ezine_lang\').value);">';
			echo '</div>';
			
			echo '<div style="clear:both;"></div>';
			break;
		case "content_text":
			echo '<input class="nowidth" type="button" Value="Choose other type" onClick="newsletternew_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=content_choice\');"><br>';
			$oFCKeditor = new FCKeditor("newsletter_content_text") ;
			$oFCKeditor->Width = 974;
			$oFCKeditor->Height = 500; 
			$oFCKeditor->Config['UserFilesPath'] = '/userfiles/';
			$oFCKeditor->BasePath	= '/plugins/fckeditor/' ;
			$oFCKeditor->Value		= "";
			$oFCKeditor->ImageBrowser = true;
			$oFCKeditor->ImageUpload = true;
			$oFCKeditor->ToolbarSet = "EMAIL";
			$oFCKeditor->Create();
			echo '<input class="nowidth" type="button" Value="SEND" onClick="ajax_post_form(\'newsletter_send\', \'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=post\', newsletter_send_send_return, \'\')">';
			break;
		case "content_ezine":
			echo '<input class="nowidth" type="button" Value="Choose other type" onClick="newsletternew_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=content_choice\');"><br>';
			//echo "ezine:" . $_GET["ezine_id"] . " lang:" . $_GET["ezine_lang"];
			$oldlang = $_SESSION["LANGUAGE"];
			$_SESSION["LANGUAGE"] = $_GET["ezine_lang"];
			ob_start();
			$ezine_id = $_GET["ezine_id"];
			include ("snippets/snippet.ezinemail.php");
			$repl = ob_get_contents();
			ob_end_clean();
			$repl_enc = str_replace("&", "__amper__", $repl);
			$repl_enc = htmlentities($repl_enc);
			echo '<input type="hidden" name="newsletter_content_text" value="' . $repl_enc . '" >
					<input type="hidden" name="newsletter_content_isezine" value="true" >
					<div style="border: 2px solid #000000; padding: 16px;">' . $repl . '</div>';
			$_SESSION["LANGUAGE"] = $oldlang;
			echo '<input class="nowidth" type="button" Value="SEND" onClick="ajax_post_form(\'newsletter_send\', \'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=post\', newsletter_send_send_return, \'\')">';
			break;
		case "post":
			$fout = false;
			if(trim($_POST["newssend_subject"]) == "")
			{
				echo "newssend_subject;";
				$fout = true;
			}
			if(trim($_POST["newssend_to"]) == "")
			{
				echo "newssend_to;";
				$fout = true;
			}
			if(trim(strip_tags($_POST["newsletter_content_text"])) == "")
			{
				echo "newsletter_content_text;";
				$fout = true;
			}
			if(!$fout)
			{
				$emails = explode(",", trim($_POST["newssend_to"]));
				$html = $_POST["newsletter_content_text"];
				if(isset($_POST["newsletter_content_isezine"]))
				{
					$html = html_entity_decode($html);
					$html = str_replace("__amper__", "&", $html);
				}
				$html = email::html_to_absolute_paths($html);
				//echo $html;
				foreach($emails as $one_email)
				{
					$one_email = trim($one_email);
					if(email::checkemail($one_email))
					{
						//email::send_one_mail($one_email, "info.metra.be", "info.metra.be", $_POST["newssend_subject"], $html, ((isset($_POST["newsletter_content_isezine"]))?true: false), false, NULL);
						DBConnect::query("INSERT INTO `sys_sending_mails` (`to`, `from`, `replyto`, `subject`, `content`, `ezine`) VALUES('" . addslashes($one_email) . "', 'info@metra.be', 'info@metra.be', '" . addslashes($_POST["newssend_subject"]) . "', '" . addslashes($html) . "', '" . ((isset($_POST["newsletter_content_isezine"]))?"1": "0") . "')", __FILE__, __LINE__);
					}
				}
			}
		default:
			break;
	}
?>