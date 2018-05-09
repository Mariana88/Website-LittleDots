<?php
	if(!login::right("backpage_security_frontusers", "view"))
	{
		echo "NORIGHTS";
		exit();
	}
	
	switch($_GET["action"])
	{
		case "deleteuser":
			if(login::check_rr("rr_security_front_user_delete"))
				DBConnect::query("DELETE FROM site_user_front WHERE id='" . addslashes($_GET["del_id"]) . "'", __FILE__, __LINE__);
			else
			{
				echo "NORIGHTS:You don't have the permission to delete users.";
				exit();
			}
			echo "OK";
			break;
		case "deletegroup":
			if(login::check_rr("rr_security_front_group_delete"))
				DBConnect::query("DELETE FROM site_usergroup_front WHERE id='" . addslashes($_GET["del_id"]) . "'", __FILE__, __LINE__);
			else
			{
				echo "NORIGHTS:You don't have the permission to delete user groups.";
				exit();
			}
			echo "OK";
			break;
		case "loaduser":
			if(!login::check_rr("rr_security_front_user_edit"))
			{
				echo "NORIGHTS:You don't have the permission to edit users.";
				exit();
			}
			$res = DBConnect::query("SELECT * FROM site_user_front WHERE `id`='" . addslashes($_GET["edit_user_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader"><h1>Edit User: ' . stripslashes($row["name"]) . ' ' . stripslashes($row["surname"]) . '</h1></div><br>';
			echo '<div id="form_securityfront_user">';
			form::show_autoform('site_user_front', $row);
			echo '<div style="text-align:right;"><input type="button" onclick="show_saving_message(); ajax_post_form(\'form_securityfront_user\', \'/ajax.php?sessid=' . session_id() . '&page=securityfront&action=saveuser\', securityfront_page_ajaxreturn_usersave);" value="Save"/></div>';
			echo '</div>';
			break;
		case "saveuser":
			if(!login::check_rr("rr_security_front_user_edit"))
			{
				echo "NORIGHTS:You don't have the permission to edit users.";
				exit();
			}
			$errormsgs = data_description::validate_post_db();
			
			//we checken of het paswoord wel ok is
			if(trim($_POST["site_user_front.lpass"]) != "")
			{
				if($_POST["site_user_front.lpass"] != $_POST["site_user_front.lpass_passretype"])
					$errormsgs["site_user_front.lpass"] = "The password is not the same as the retype";
				if(isset($_POST["site_user_front.lpass_passold"]))
				{
					if(trim($_POST["site_user_front.lpass_passold"]) == "")
						$errormsgs["site_user_front.lpass"] = "If you want to change the password, give the old password as well.";
					else
					{
						//check if the password is right
						$res_check = DBConnect::query("SELECT * FROM `site_user_front` WHERE id='" . $_POST["site_user_front.id"] . "'", __FILE__, __LINE__);
						$row_check = mysql_fetch_array($res_check);
						if(md5(trim($_POST["site_user_front.lpass_passold"])) != $row_check["lpass"])
							$errormsgs["site_user_front.lpass"] = "If you want to change the password, give the right old password as well.";
					}
				}
			}
			if(count($errormsgs) == 0)
			{
				
				//we save the data
				DBConnect::query(data_description::create_sql_from_post("site_user_front", "id"), __FILE__, __LINE__);
				echo 'OK';
			}
			else
			{
				foreach($errormsgs as $name => $error)
					echo $name . ': ' . $error . '<br>';
			}
			break;
		case "loadgroup":
			if(!login::check_rr("rr_security_front_group_edit"))
			{
				echo "NORIGHTS:You don't have the permission to edit groups.";
				exit();
			}
			$res = DBConnect::query("SELECT * FROM site_usergroup_front WHERE `id`='" . addslashes($_GET["edit_group_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader"><h1>Edit Group: ' . stripslashes($row["name"]) . '</h1></div><br>';
			echo '<div id="form_securityfront_group">';
			form::show_autoform('site_usergroup_front', $row);
			echo '<div style="text-align:right;"><input type="button" onclick="show_saving_message(); ajax_post_form(\'form_securityfront_group\', \'/ajax.php?sessid=' . session_id() . '&page=securityfront&action=savegroup\', securityfront_page_ajaxreturn_groupsave);" value="Save"/></div>';
			echo '</div>';
			break;
		case "savegroup":
			if(!login::check_rr("rr_security_front_group_edit"))
			{
				echo "NORIGHTS:You don't have the permission to edit groups.";
				exit();
			}
			$errormsgs = data_description::validate_post_db();
			if(count($errormsgs) == 0)
			{
				//we save the data
				DBConnect::query(data_description::create_sql_from_post("site_usergroup_front", "id"), __FILE__, __LINE__);
				echo 'OK';
			}
			else
			{
				foreach($errormsgs as $name => $error)
					echo $name . ': ' . $error . '<br>';
			}
			break;
		case "newgroup":
			if(!login::check_rr("rr_security_front_group_create"))
			{
				echo "NORIGHTS:You don't have the permission to create groups.";
				exit();
			}
			DBConnect::query("INSERT INTO site_usergroup_front (`name`, `description`) VALUES ('New Group', '')", __FILE__, __LINE__);
			echo mysql_insert_id();
			break;
		case "newuser":
			if(!login::check_rr("rr_security_front_user_create"))
			{
				echo "NORIGHTS:You don't have the permission to create users.";
				exit();
			}
			DBConnect::query("INSERT INTO site_user_front (`user_group`, `name`) VALUES ('" . addslashes($_GET["parent_group"]) . "', 'New user')", __FILE__, __LINE__);
			echo mysql_insert_id();
			break;
		case "refresh_tree":
			echo '<div class="treeview"><ul class="treeview" id="treeview_securityfront_user">';
		
			$res_group = DBConnect::query("SELECT * FROM site_usergroup_front ORDER BY `name`", __FILE__, __LINE__);
			while($row_group = mysql_fetch_array($res_group))
			{
				$res_user = DBConnect::query("SELECT * FROM site_user_front WHERE user_group='" . $row_group["id"] . "' ORDER BY `name`, `surname`", __FILE__, __LINE__);
				echo '<li id="treeview_securityfront_group_' . stripslashes($row_group["id"]) . '" ' . ((mysql_num_rows($res_user)>0)?'class="submenu"':'') . '><a href="javascript:dummy();" ' . ((!login::check_rr("rr_security_front_group_edit"))?'noedit="1"':'') . ' ' . ((!login::check_rr("rr_security_front_group_delete"))?'nodel="1"':'') . '  ' . ((!login::check_rr("rr_security_front_user_create"))?'nouser="1"':'') . ' onclick="select_me_please(\'treeview_securityfront_user\', this); securityfrontusertree_select(this, \'' . stripslashes($row_group["id"]) . '\')" ondblclick="securityfront_content_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=securityfront&action=loadgroup&edit_group_id=' . stripslashes($row_group["id"]) . '\')" nodetype="group">' . stripslashes($row_group["name"]) . '</a>';
				if(mysql_num_rows($res_user)>0)
					echo '<ul>';
				while($row_user = mysql_fetch_array($res_user))
				{
					echo '<li id="treeview_securityfront_user_' . stripslashes($row_user["id"]) . '"><a href="javascript:dummy();" ' . ((!login::check_rr("rr_security_front_user_edit"))?'noedit="1"':'') . ' ' . ((!login::check_rr("rr_security_front_user_delete"))?'nodel="1"':'') . '  onclick="select_me_please(\'treeview_securityfront_user\', this); securityfrontusertree_select(this, \'' . stripslashes($row_user["id"]) . '\')" ondblclick="securityfront_content_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=securityfront&action=loaduser&edit_user_id=' . stripslashes($row_user["id"]) . '\')" nodetype="user">' . stripslashes($row_user["name"]) . ' ' . stripslashes($row_user["surname"]) . '</a></li>';
				}
				if(mysql_num_rows($res_user)>0)
					echo '</ul>';
				echo '</li>';
			}
			echo '</ul></div>
				<script type="text/javascript">ddtreemenu.createTree("treeview_securityfront_user", true);</script>';
			break;
	}
?>