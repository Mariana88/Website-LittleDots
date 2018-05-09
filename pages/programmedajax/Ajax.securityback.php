<?php
	if(!login::right("backpage_security_adminusers", "view"))
	{
		echo "NORIGHTS";
		exit();
	}

	
	switch($_GET["action"])
	{
		case "deluser":
			$res = DBConnect::query("SELECT * FROM site_user WHERE `id`='" . addslashes($_GET["del_user_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Delete user: ' . $row["name"] . ' ' . $row["surname"] . '</div>
					</div>
					<div class="contentcontent">
						<div style="margin-top: 20px; text-align:center;">';
					
			if(isset($_GET["delbev"]))
			{
				if($_GET["delbev"] == "yes")
				{
					DBConnect::query("DELETE FROM site_user WHERE `id`='" . addslashes($_GET["del_user_id"]) . "'", __FILE__, __LINE__);
					echo 'The user is deleted<script language="javascript">securityback_refresh_tree();</script>';
				}
				else
				{
					echo 'The user is NOT deleted';
				}
			}
			else
			{
				echo 'Are you sure you want to delete this user?<br><br>
					<input type="button" value="yes" onclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=deluser&del_user_id=' . $_GET["del_user_id"] . '&delbev=yes\');">&nbsp;
					<input type="button" value="no" onclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=deluser&del_user_id=' . $_GET["del_user_id"] . '&delbev=no\');">';
			}
			echo '</div></div></div>';
			break;
		case "loaduser":
			$res = DBConnect::query("SELECT * FROM site_user WHERE `id`='" . addslashes($_GET["edit_user_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Edit user: ' . $row["name"] . ' ' . $row["surname"] . '</div>
						<div class="divright">
							<div class="savebutton" onclick="window[\'site_user_form\'].savebutton = $(this); window[\'site_user_form\'].post();">Save</div>
						</div>
					</div>
					<div class="contentcontent">';
			//var_dump($row);
			form::show_autoform_new("site_user", $row);
			echo '<script>window["site_user_form"].aftersave_success = "securityback_user_on_save";</script>';
			echo '</div></div>';
			
			break;
		case "newuser":
			echo '<div class="contentheader">
						<div class="divleft">Create new user</div>
						<div class="divright">
							<div class="savebutton" onclick="window[\'site_user_form\'].savebutton = $(this); window[\'site_user_form\'].post();">Save</div>
						</div>
					</div>
					<div class="contentcontent">';
			$data["user_group"] = "2";
			form::show_autoform_new("site_user", $data);
			echo '<script>window["site_user_form"].aftersave_success = "securityback_user_on_save";</script>';
			echo '</div></div>';
			/*if(!login::check_rr("rr_security_back_user_create"))
			{
				echo "NORIGHTS:You don't have the permission to create users.";
				exit();
			}
			DBConnect::query("INSERT INTO site_user (`user_group`, `name`) VALUES ('" . addslashes($_GET["parent_group"]) . "', 'New user')", __FILE__, __LINE__);
			echo mysql_insert_id();*/
			break;
		case "refresh_tree":
			echo '<div id="securityback_user_list">';
		
			$res_user = DBConnect::query("SELECT * FROM site_user ORDER BY `name`, `surname`", __FILE__, __LINE__);				
			while($row_user = mysql_fetch_array($res_user))
				echo '<div style="padding: 3px; cursor: pointer;" id="securityback_user_' . stripslashes($row_user["id"]) . '" onclick="securitybackusertree_select(this, \'' . stripslashes($row_user["id"]) . '\')" ondblclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=loaduser&edit_user_id=' . stripslashes($row_user["id"]) . '\');">' . stripslashes($row_user["name"]) . ' ' . stripslashes($row_user["surname"]) . '</div>';
			
			echo '</div>';
			break;
		case "delgroup":
			$res = DBConnect::query("SELECT * FROM site_usergroup WHERE `id`='" . addslashes($_GET["del_group_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Delete group: ' . $row["name"] . '</div>
					</div>
					<div class="contentcontent">
						<div style="margin-top: 20px; text-align:center;">';
					
			if(isset($_GET["delbev"]))
			{
				if($_GET["delbev"] == "yes")
				{
					DBConnect::query("DELETE FROM site_usergroup WHERE `id`='" . addslashes($_GET["del_group_id"]) . "'", __FILE__, __LINE__);
					echo 'The group is deleted<script language="javascript">securityback_refresh_grouptree();</script>';
				}
				else
				{
					echo 'The group is NOT deleted';
				}
			}
			else
			{
				echo 'Are you sure you want to delete this user?<br><br>
					<input type="button" value="yes" onclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=delgroup&del_group_id=' . $_GET["del_group_id"] . '&delbev=yes\');">&nbsp;
					<input type="button" value="no" onclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=delgroup&del_group_id=' . $_GET["del_group_id"] . '&delbev=no\');">';
			}
			echo '</div></div></div>';
			break;
		case "loadgroup":
			$res = DBConnect::query("SELECT * FROM site_usergroup WHERE `id`='" . addslashes($_GET["edit_group_id"]) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			echo '<div class="contentheader">
						<div class="divleft">Edit group: ' . $row["name"] . '</div>
						<div class="divright">
							<div class="savebutton" onclick="window[\'site_usergroup_form\'].savebutton = $(this); window[\'site_usergroup_form\'].post();">Save</div>
						</div>
					</div>
					<div class="contentcontent">';
			//var_dump($row);
			form::show_autoform_new("site_usergroup", $row);
			
			//RIGHT RULES
			//template rules
			echo '<div class="splitter"><span>Template rights</span></div>';
			$res = DBConnect::query("SELECT * FROM site_pagetemplates", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
			{
				echo '<div class="splitter_light"><span>' . $row["name"] . '</span></div>';
				rightform::publish("template", $row["id"], $_GET["edit_group_id"]);
			}
			
			//main rules
			echo '<div class="splitter"><span>Backpage rules</span></div>';
			$res = DBConnect::query("SELECT * FROM sys_rightrules", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res))
			{
				if(strlen($row["name"]) > 9 && substr($row["name"], 0, 9) == "backpage_")
				{
					echo '<div class="splitter_light"><span>' . str_replace('_', ' -&gt; ', substr($row["name"], 9)) . '</span></div>';
					rightform::publish($row["name"], NULL, $_GET["edit_group_id"]);
				}
			}
			
			echo '<script>window["site_usergroup_form"].aftersave_success = "securityback_group_on_save";</script>';
			echo '</div></div>';
			break;
		case "newgroup":
			echo '<div class="contentheader">
						<div class="divleft">Create new group</div>
						<div class="divright">
							<div class="savebutton" onclick="window[\'site_usergroup_form\'].savebutton = $(this); window[\'site_usergroup_form\'].post();">Save</div>
						</div>
					</div>
					<div class="contentcontent">';
			$data["name"] = "New Group";
			form::show_autoform_new("site_usergroup", $data);
			echo '<script>window["site_usergroup_form"].aftersave_success = "securityback_group_on_save";</script>';
			echo '</div></div>';
			/*if(!login::check_rr("rr_security_back_user_create"))
			{
				echo "NORIGHTS:You don't have the permission to create users.";
				exit();
			}
			DBConnect::query("INSERT INTO site_user (`user_group`, `name`) VALUES ('" . addslashes($_GET["parent_group"]) . "', 'New user')", __FILE__, __LINE__);
			echo mysql_insert_id();*/
			break;
		case "refresh_tree_group":
			echo '<div id="securityback_group_list">';
		
			$res_group = DBConnect::query("SELECT * FROM site_usergroup ORDER BY `name`", __FILE__, __LINE__);				
			while($row_group = mysql_fetch_array($res_group))
				echo '<div style="padding: 3px; cursor: pointer;" id="securityback_group_' . stripslashes($row_group["id"]) . '" onclick="securitybackgrouptree_select(this, \'' . stripslashes($row_group["id"]) . '\')" ondblclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=loadgroup&edit_group_id=' . stripslashes($row_group["id"]) . '\');">' . stripslashes($row_group["name"]) . '</div>';
			
			echo '</div>';
			break;
	}
?>