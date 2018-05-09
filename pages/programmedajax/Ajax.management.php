<?php
	if(!login::right("backpage_management_dashboard", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	switch($_GET["action"])
	{
		case 'render_dashboard':
			echo '<div class="contentheader">
						<div class="divleft">Management Start</div>
					</div>
					<div class="contentcontent">';
			//EMAIL
			//Tonen van emails
			echo '<div class="" style="width: 536px; float:left;" id="man_content_left">';
			email::fetchallemail();
			$el = new emaillist("dashboardemail", 368, 800);
			$el->publish(false);
			echo '</div>';
			
			//TODO
			echo '<div class="toolbox" style="width: 200px; float:right; margin-right: 10px;">
					<div class="toolboxheader"><div class="divleft">todo v.d. week</div><div style="float:right;" class="man_button" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=todo\');">all todo</div><div style="clear:both; height:0px;"></div></div>
					<div class="toolboxcontent">';
			$res_todo = DBConnect::query("SELECT * FROM data_todo where `datum`<'" . (time()+604800) . "' ORDER BY `datum` ASC", __FILE__, __LINE__);
			while($row_todo = fetch_db($res_todo))
			{
				echo '<div class="toolbox_listdiv"><div style="font-weight:bold; margin-bottom: 8px;">' . $row_todo["datum"] . '&nbsp;<span style="font-size: 9px; font-weight: normal;">prior:</span> ' . $row_todo["prioriteit"] . '</div>' . $row_todo["wat"] . '</div>';	
			}
			echo '</div></div>';
			
			//Reply checks
			echo '<div class="toolbox" style="width: 200px; float:right; margin-right: 10px;">';
			echo '<div class="toolboxheader">Mail replies this week</div>
					<div class="toolboxcontent">';
			
			$res_reply = DBConnect::query("SELECT man_email.*, man_email_replycheck.id as `reply_id`, man_email_replycheck.date as `reply_date` FROM man_email, man_email_replycheck WHERE man_email_replycheck.mailid=man_email.id AND man_email_replycheck.date<'" . (time()+604800) . "' ORDER BY `date` ASC", __FILE__, __LINE__);
			while($row_reply = mysql_fetch_array($res_reply))
			{
				echo '<div class="toolbox_listdiv"><div style="margin-bottom: 6px; padding-bottom: 2px; border-bottom: 1px solid #999999;"><span style="font-weight:bold;">' . date('d.m.Y', $row_reply["date"]) . '</span> ' . stripslashes($row_reply["subject"]) . '</div>';
				$arrto = unserialize(stripslashes($row_reply["to"]));
				foreach($arrto as $oneto)
				{
					$res_contact = DBCONNECT::query("SELECT * FROM man_contact WHERE email='" . $oneto . "' OR email2='" . $oneto . "'", __FILE__, __LINE__);
					if($row_contact = mysql_fetch_array($res_contact))
					{
						echo '<div style="display:inline; float:none; font-size: 9px;" class="man_button" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=load&type=' . (($row_contact["entity_id"]!=0)?'entity':'contact') . '&id=' . (($row_contact["entity_id"]!=0)?$row_contact["entity_id"]:$row_contact["id"]) . '\');">' . $oneto . '</div> ';
					}
					else
						echo $oneto . ' ';	
				}
				echo '<div style="clear:both; height: 0px;"></div>
					<div style="font-size: 8px; background-color:#AAAAAA; margin-top: 7px; font-weight:bold;" class="man_button" onclick="if(confirm (\'delete this reply check?\')) {$(this).parent().remove(); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=management&action=delreplycheck&replycheck_id=' . $row_reply["reply_id"] . '\', \'\', null);}">ANTWOORD GEKREGEN</div>
					<div style="clear:both; height: 0px;"></div>';
				echo '</div>';	
			}
			
			echo '</div></div>';
			
			
			echo '<div style="clear:both; height:0px;"></div></div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'dashboard_todo';
			break;
		case 'render_contacttree':
			//The get vaiabelen in $filter steken
			$filter = array();
			if(trim($_GET["filter_name"]) != "") $filter["name"] = urldecode($_GET["filter_name"]);
			if(trim($_GET["filter_land"]) != "") $filter["land"] = urldecode($_GET["filter_land"]);
			if(trim($_GET["filter_tag"]) != "") $filter["tag"] = urldecode($_GET["filter_tag"]);
			management::publish_contacttree($filter);
			echo '<script>
				$("#treeview_contacts").find("div").mousemove(function(event) {
						tree_dragmove(\'treeview_pages_front\', this, event);
						//event.pageXevent.pageY;
					});
				$("#treeview_contacts").find("div").mousedown(function(event) {
						if($(this).attr("nodrag") != "1")
							tree_mousedown(\'treeview_pages_front\', this);
						//event.pageXevent.pageY;
					});
				$("#treeview_contacts").find("div").mouseup(function(event) {
						tree_mouseup(\'treeview_pages_front\', this, event);
						//event.pageXevent.pageY;
					});
				ddtreemenu.dragcheck = management_tree_dragcheck;
				ddtreemenu.afterdrop = management_tree_afterdrop;
			</script>';
			break;
		case 'render_contacttree_mail':
			//The get vaiabelen in $filter steken
			$filter = array();
			if(trim($_GET["filter_name"]) != "") $filter["name"] = urldecode($_GET["filter_name"]);
			if(trim($_GET["filter_land"]) != "") $filter["land"] = urldecode($_GET["filter_land"]);
			if(trim($_GET["filter_tag"]) != "") $filter["tag"] = urldecode($_GET["filter_tag"]);
			management::publish_contacttree($filter, true);
			break;
		case 'newgroup':
			DBConnect::query("INSERT INTO `man_entity_group` (`id`, `parent_id`, `name`) VALUES ('', '" . addslashes($_GET["parent_id"]) . "', 'New Group')", __FILE__, __LINE__);
			//echo node
			$new_id = mysql_insert_id();
			echo '<div class="parent_type">' . addslashes($_GET["parent_type"]) . '</div><div class="parent_id">' . addslashes($_GET["parent_id"]) . '</div><li id="managementtree_group_' . $new_id . '"><div the_id="' . $new_id . '" type="group" onclick="select_me_please(\'treeview_contacts\', this); management_tree_select(this);" ondblclick="management_tree_open(this);" style="padding: 2px 4px 2px 4px; backgound-color:#456123; min-height:18px; "><img src="/css/back/icon/management/tree_group.gif">&nbsp;<span>New Group</span></div></li>';
			break;
		case 'newentity':
			DBConnect::query("INSERT INTO `man_entity` (`id`, `group_id`, `name`) VALUES ('', '" . addslashes($_GET["parent_id"]) . "', 'New Entity')", __FILE__, __LINE__);
			//echo node
			$new_id = mysql_insert_id();
			echo '<div class="parent_type">' . addslashes($_GET["parent_type"]) . '</div><div class="parent_id">' . addslashes($_GET["parent_id"]) . '</div><li id="managementtree_entity_' . $new_id . '"><div the_id="' . $new_id . '" type="entity" onclick="select_me_please(\'treeview_contacts\', this); management_tree_select(this);" ondblclick="management_tree_open(this);" style="padding: 2px 4px 2px 4px; backgound-color:#456123; min-height:18px; "><img src="/css/back/icon/management/tree_entity.gif">&nbsp;<span>New Entity</span></div></li>';
			break;
		case 'newcontact':
			DBConnect::query("INSERT INTO `man_contact` (`id`, `entity_group_id`, `entity_id`, `name`) VALUES ('', '" . (($_GET["parent_type"]=="group")?addslashes($_GET["parent_id"]):'0') . "', '" . (($_GET["parent_type"]=="entity")?addslashes($_GET["parent_id"]):'0') . "', 'New Contact')", __FILE__, __LINE__);
			//echo node
			$new_id = mysql_insert_id();
			echo '<div class="parent_type">' . addslashes($_GET["parent_type"]) . '</div><div class="parent_id">' . addslashes($_GET["parent_id"]) . '</div><li id="managementtree_contact_' . $new_id . '"><div the_id="' . $new_id . '" type="contact" onclick="select_me_please(\'treeview_contacts\', this); management_tree_select(this);" ondblclick="management_tree_open(this);" style="padding: 2px 4px 2px 4px; backgound-color:#456123; min-height:18px; "><img src="/css/back/icon/management/tree_contact.gif">&nbsp;<span>New Contact</span></div></li>';
			$_REQUEST["output_html"] = true;
			break;
		case 'load':
			$row = NULL;
			$table = "";
			switch($_GET["type"])
			{
				case "group":
					$res = DBConnect::query("SELECT * FROM `man_entity_group` WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$table = "man_entity_group";
					break;
				case "entity":
					$res = DBConnect::query("SELECT * FROM `man_entity` WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$table = "man_entity";
					break;
				case "contact":
					$res = DBConnect::query("SELECT * FROM `man_contact` WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$table = "man_contact";
					break;
			}
			echo '<div class="contentheader">
						<div class="divleft">Edit ' . $_GET["type"] . ': ' . htmlentities(stripslashes($row["name"])) . '</div>';
			if($_GET["type"] == "group")
				echo '<div class="divright">
								<div class="savebutton" onclick="window[\'' . $table . '_form\'].aftersave_success = \'management_savesuccess\'; window[\'' . $table . '_form\'].savebutton = $(this); window[\'' . $table . '_form\'].post();">Save</div>
							</div>';
			echo '</div>';
			if($_GET["type"] == "entity")
			{
				echo '<div class="TabbedPanels" id="management_form_entity">
						<ul class="TabbedPanelsTabGroup">
							<li class="TabbedPanelsTab" tabindex="0">Contact</li>
							<li class="TabbedPanelsTab" tabindex="1">Edit Info</li>
							<li class="TabbedPanelsTab" tabindex="2">Old Communications</li>
						</ul>
						<div class="TabbedPanelsContentGroup">
							<div class="TabbedPanelsContent">';
				//CONTACT----------------------------------------------------------------------
				//eerst een samenvatting van de data + de contact personen
				echo '<div class="man_rounddiv" style="">
					<div class="man_h1" style="float:left; margin-bottom:7px;">Contacts</div>
					<div class="man_button" style="float:right; margin-bottom:7px;" onClick="dataeditor_show_input_dialog(\'form_man_entity_man_contact\', \'Add a new Contact\', \'/ajax.php?sessid=' . session_id() . '&popup_id=form_man_entity_man_contact&showform=new\', \'man_contact_form\', 500);">add</div>
					<div class="man_button" style="float:right; margin-bottom:7px;" onClick="cms2_show_question_message(\'Are you sure you want to delete the selected entries?\', \'Delete?\', function(){dataeditor_delete_accept(dg_dg_de_form_man_entity_man_contact.selected_ids, \'form_man_entity_man_contact\', \'dg_de_form_man_entity_man_contact\', dg_dg_de_form_man_entity_man_contact_html_panel);}, function(){$(this).dialog(\'close\');});">delete</div>';
				$de = new dataeditor("form_man_entity_man_contact", 500, 500, "man_contact");
				$de->set_parent("man_entity", "entity_id", $row["id"]);
				$de->set_current_lang("NL");
				$de->set_show_icon_bar(false);
				$de->publish(false);
				echo '</div>';
				//Emails
				echo '<div class="man_rounddiv" style="">
						<div class="man_h1" style="float:left; margin-bottom:7px;">Emails</div><div style="clear:both; height:0px"></div>';
				$el = new emaillist("entityemail", 368, 800);
				//opzoeken van alle contacten bij entity
				$el->set_entity($row["id"]);
				$el->clear_filter_email();
				$res_contacts = DBConnect::query("SELECT * FROM man_contact WHERE `entity_id`='" . $row["id"] . "'", __FILE__, __LINE__);
				$emailfound = false;
				while($row_contact = mysql_fetch_array($res_contacts))
				{
					if(trim($row_contact["email"]) != "")
					{
						$el->add_filter_email($row_contact["email"]);
						$emailfound = true;
					}
					if(trim($row_contact["email2"]) != "")
					{
						$el->add_filter_email($row_contact["email2"]);
						$emailfound = true;
					}
				}
				$el->set_page(0);
				if($emailfound)
					$el->publish(false);
				echo '</div>';
				
				
				//END Contact------------------------------------------------------------------
				echo '		</div>
							<div class="TabbedPanelsContent">';
				//INFO
				echo '<div class="savebutton" style="float:right;" onclick="window[\'' . $table . '_form\'].aftersave_success = \'management_savesuccess\'; window[\'' . $table . '_form\'].savebutton = $(this); window[\'' . $table . '_form\'].post();">Save</div>';
				form::show_autoform_new($table, $row, mainconfig::$standardlanguage);
				echo '		</div>
							<div class="TabbedPanelsContent">';
				//OLD Communications
				$de = new dataeditor("form_man_entity_man_communicatie", 500, 500, "man_communicatie");
				$de->set_parent("man_entity", "entity_id", $row["id"]);
				$de->set_title("Old communications");
				$de->publish(false);
				echo '		</div>
						</div>
					</div>';
				//script
				echo '<script language="Javascript">
						var tp_management_form_entity = new Spry.Widget.TabbedPanels("management_form_entity");
					</script>';
			}
			elseif($_GET["type"] == "contact")
			{
				echo '<div class="TabbedPanels" id="management_form_contact">
						<ul class="TabbedPanelsTabGroup">
							<li class="TabbedPanelsTab" tabindex="0">Contact</li>
							<li class="TabbedPanelsTab" tabindex="1">Edit Info</li>
						</ul>
						<div class="TabbedPanelsContentGroup">
							<div class="TabbedPanelsContent">';
				echo '<div class="man_rounddiv" style="">
						<div class="man_h1" style="float:left; margin-bottom:7px;">Emails</div><div style="clear:both; height:0px"></div>';
				$el = new emaillist("entityemail", 368, 800);
				//opzoeken van alle contacten bij entity
				$el->set_contact($row["id"]);
				$el->clear_filter_email();
				$emailfound = false;
				if(trim($row["email"]) != "")
				{
					$el->add_filter_email($row["email"]);
					$emailfound = true;
				}
				if(trim($row["email2"]) != "")
				{
					$el->add_filter_email($row["email2"]);
					$emailfound = true;	
				}
				$el->set_page(0);
				if($emailfound)
					$el->publish(false);
				echo '</div>
					</div>
					<div class="TabbedPanelsContent">';
				echo '<div class="savebutton" style="float:right;" onclick="window[\'' . $table . '_form\'].aftersave_success = \'management_savesuccess\'; window[\'' . $table . '_form\'].savebutton = $(this); window[\'' . $table . '_form\'].post();">Save</div>';
				form::show_autoform_new($table, $row, mainconfig::$standardlanguage);
				echo '		</div>
						</div>
					</div>';
				echo '<script language="Javascript">
						var tp_management_form_contact = new Spry.Widget.TabbedPanels("management_form_contact");
					</script>';
			}
			else
			{
				echo '<div class="contentcontent">';
				form::show_autoform_new($table, $row, mainconfig::$standardlanguage);
				echo '</div>';
			}
			$_REQUEST["output_html"] = true;
			break;
		case 'delete':
			echo '<div class="contentheader">
						<div class="divleft">Delete ' . $_GET["type"] . '</div>
					</div>
					<div class="contentcontent">';
			if($_GET["delbev"] == '1')
			{
				management::delete_treenode($_GET['type'], $_GET["id"]);
				echo '<br><br><br>The ' . $_GET["type"] . ' was deleted';
				echo '<script language="javascript">
						tree_removenode("managementtree_' . $_GET["type"] . '_' . $_GET["id"] . '");
					</script>';
			}
			elseif($_GET["delbev"] == '0')
			{
				echo '<br><br><br>The ' . $_GET["type"] . ' was NOT deleted';
			}
			else
			{
				echo '<br><br><br>Are you sure you want to delete this ' . $_GET["type"] . ' (subitems will be deleted too)<br><br>
						<input type="button" value="Yes" onclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=delete&type=' . $_GET["type"] . '&id=' . $_GET["id"] . '&delbev=1\');"/>&nbsp;
						<input type="button" value="No" onclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=delete&type=' . $_GET["type"] . '&id=' . $_GET["id"] . '&delbev=0\');"/>';
			}
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'drop':
			switch($_GET["type"])
			{
				case 'group':
					//we updaten
					DBConnect::query("UPDATE man_entity_group SET `parent_id`='" . addslashes($_GET["parent_id"]) . "' WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					break;
				case 'entity':
					//we updaten
					DBConnect::query("UPDATE man_entity SET `group_id`='" . addslashes($_GET["parent_id"]) . "' WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					break;
				case 'contact':
					//we updaten
					if($_GET["parent_type"] == 'group')
					{
						DBConnect::query("UPDATE man_contact SET `entity_group_id`='" . addslashes($_GET["parent_id"]) . "', `entity_id`='0' WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					}
					elseif($_GET["parent_type"] == 'entity')
					{
						DBConnect::query("UPDATE man_contact SET `entity_group_id`='0', `entity_id`='" . addslashes($_GET["parent_id"]) . "' WHERE `id`='" . addslashes($_GET["id"]) . "'", __FILE__, __LINE__);
					}
					break;
			}
			break;
		case 'todo':
			echo '<div class="contentheader">
						<div class="divleft">TODO List</div>
					</div>
					<div class="contentcontent" id="testpost">';
			$de = new dataeditor("test_de", 500, 500, "data_todo");
			$de->set_current_lang("NL");
			$de->publish(false);
		
			echo '<div style="clear:both;"></div>';
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'countrylist':
			echo '<div class="contentheader">
						<div class="divleft">Country List</div>
					</div>
					<div class="contentcontent" id="testpost">';
			$de = new dataeditor("test_de", 500, 500, "data_land");
			$de->set_current_lang("NL");
			$de->publish(false);
		
			echo '<div style="clear:both;"></div>';
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'textlist':
			echo '<div class="contentheader">
						<div class="divleft">Text List</div>
					</div>
					<div class="contentcontent" id="testpost">';
			$de = new dataeditor("test_de", 500, 500, "man_teksten");
			$de->set_current_lang("NL");
			$de->publish(false);
		
			echo '<div style="clear:both;"></div>';
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'fetch_allemail':
				echo '<div class="contentheader">
						<div class="divleft">Fetch all email</div>
					</div>
					<div class="contentcontent" id="testpost">';
			//ophalen van alle emails
			email::fetchallemail();
			
			$el = new emaillist("allemail", 368, 800);
			$el->publish(false);
			
			echo '<div style="clear:both;"></div>';
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'email_config':
				echo '<div class="contentheader">
						<div class="divleft">Email Config</div>
					</div>
					<div class="contentcontent" id="man_emailconfigpost">';
			$de = new dataeditor("man_emailconfig_de", 500, 500, "man_emailconfig");
			$de->set_current_lang("NL");
			$de->publish(false);
			echo '<div style="clear:both;"></div>';
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'email_templates':
				echo '<div class="contentheader">
						<div class="divleft">Email Templates</div>
					</div>
					<div class="contentcontent" id="man_emailtemplatepost">';
			$de = new dataeditor("man_email_template_de", 500, 500, "man_email_template");
			$de->set_current_lang("NL");
			$de->publish(false);
			echo '<div style="clear:both;"></div>';
			echo '</div>';
			$_REQUEST["output_html"] = true;
			break;
		case 'savetemplate':
			$name = urldecode($_POST["name"]);
			$subject = urldecode($_POST["subject"]);
			$text = urldecode($_POST["text"]);
			$text = str_replace("___AMP___", "&", $text);
			$text = str_replace("___QUEST___", "?", $text);
			$text = str_replace("___HEK___", "#", $text);
			$text = str_replace("___PLUS___", "+", $text);
			$text = str_replace("___EUR___", "€", $text);
			DBConnect::query("INSERT INTO man_email_template (`id`, `name`, `subject`, `text`) VALUES('', '" . addslashes($name) . "', '" . addslashes($subject) . "', '" . addslashes($text) . "')", __FILE__, __LINE__);
			break;
		case 'get_template':
			$restempl = DBConnect::query("SELECT * FROM man_email_template WHERE `id`='" . $_GET["templid"] . "'", __FILE__, __LINE__);
			$rowtempl = mysql_fetch_array($restempl);
			header('Content-Type: text/xml');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			echo '<?xml version="1.0" encoding="utf-8"?>
					<template>';
			echo '<name><![CDATA[' . stripslashes($rowtempl["name"]) . ']]></name>';
			echo '<subject><![CDATA[' . stripslashes($rowtempl["subject"]) . ']]></subject>';
			echo '<text><![CDATA[' . stripslashes($rowtempl["text"]) . ']]></text>';
			echo '</template>';
			break;
		case 'delreplycheck':
			DBConnect::query("DELETE FROM man_email_replycheck WHERE `id`='" . addslashes($_GET["replycheck_id"]) . "'", __FILE__, __LINE__);
			break;
	}
?>