<?php
	if(!login::right("backpage_security_frontusers", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function securityfront_page_ajaxreturn_groupsave(xmlHttp)
	{
		if(!check_error_return(xmlHttp))
			return;
		if(xmlHttp.responseText == "OK")
		{
			//we passen de tree aan
			tb_remove();
			securityfront_tree_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&page=securityfront&action=refresh_tree');
		}
		else
		{	
			$("#TB_window").remove();
			$("body").append("<div id=\'TB_window\'></div>");
			$("body").append("<div id=\"message_saving_content\" style=\"display:none; text-align:center\"><div style=\"color: #4A6867; font-weight: bold; font-size:18px; line-height:70px; text-align:center\">... SAVING ...</div></div>");
			show_error_message(xmlHttp.responseText);
		}
	}
	
	function securityfront_page_ajaxreturn_usersave(xmlHttp)
	{
		if(!check_error_return(xmlHttp))
			return;
		if(xmlHttp.responseText == "OK")
		{
			//we passen de tree aan
			tb_remove();
			securityfront_tree_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&page=securityfront&action=refresh_tree');
		}
		else
		{	
			$("#TB_window").remove();
			$("body").append("<div id=\'TB_window\'></div>");
			$("body").append("<div id=\"message_saving_content\" style=\"display:none; text-align:center\"><div style=\"color: #4A6867; font-weight: bold; font-size:18px; line-height:70px; text-align:center\">... SAVING ...</div></div>");
			show_error_message(xmlHttp.responseText);
		}
	}
	
	function securityfront_page_ajaxreturn_userdelete(xmlHttp)
	{
		if(!check_error_return(xmlHttp))
			return;
	}
	
	function securityfront_delete_accept()
	{
		if(document.getElementById("securityfront_selected_node_container").value != "")
		{
			if(document.getElementById("securityfront_selected_type_container").value == "user")
				send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=deleteuser&del_id=" + document.getElementById("securityfront_selected_node_container").value, "", securityfront_ajaxreturn_delete);
			if(document.getElementById("securityfront_selected_type_container").value == "group")
				send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=deletegroup&del_id=" + document.getElementById("securityfront_selected_node_container").value, "", securityfront_ajaxreturn_delete);
			tb_remove();
		}
	}
	
	function securityfront_ajaxreturn_delete(xmlHttp)
	{
		if(!check_error_return(xmlHttp))
			return;
		if(xmlHttp.responseText == "OK")
		{
			securityfront_refresh_tree();
		}
		else
		{
			alert(xmlHttp.responseText);
		}
	}
	
	function securityfront_refresh_tree()
	{
		securityfront_tree_html_panel.loadContent("/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=refresh_tree");
	}
	
	function securityfrontusertree_select(the_link, the_id)
	{
		document.getElementById("securityfront_selected_node_container").value = the_id;
		document.getElementById("securityfront_selected_type_container").value = the_link.getAttribute("nodetype");
		
		theimg = document.getElementById('usertree_securityfront_new_user');
		if(the_link.getAttribute("nodetype") == "group")
		{
			if(the_link.getAttribute("nouser") != "1")
			{
				theimg.onclick=function(e){send_ajax_request('GET', '/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=newuser&paren_group=' + document.getElementById("securityfront_selected_node_container").value, '', securityfront_user_on_new);}
				theimg.style.cursor='pointer';
				theimg.src = '/css/back/icon/twotone/user.gif';
			}
			else
			{
				theimg.onclick=null;
				theimg.style.cursor='default';
				theimg.src = '/css/back/icon/twotone/gray/user.gif';
			}
		}
		else
		{
			theimg.onclick=null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/user.gif';
		}
		
		theimg = document.getElementById('usertree_securityfront_edit');
		if(the_link.getAttribute("noedit") != "1")
		{
			if(the_link.getAttribute("nodetype") == "user")
				theimg.onclick=function(e){securityfront_content_panel.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=loaduser&edit_user_id=' + the_id);}
			if(the_link.getAttribute("nodetype") == "group")
				theimg.onclick=function(e){securityfront_content_panel.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=loadgroup&edit_group_id=' + the_id);}
			theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/edit.gif';
		}
		else
		{
			theimg.onclick=null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/edit.gif';
		}
		
		theimg = document.getElementById('usertree_securityfront_delete');
		if(the_link.getAttribute("nodel") != "1")
		{
			if(the_link.getAttribute("nodetype") == "user")
				theimg.onclick=function(e){show_question_message('Are you sure you want to delete this user?', securityfront_delete_accept, tb_remove);}
			if(the_link.getAttribute("nodetype") == "group")
				theimg.onclick=function(e){show_question_message('Are you sure you want to delete this group?', securityfront_delete_accept, tb_remove);}
			theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/trash.gif';
		}
		else
		{
			theimg.onclick=null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/trash.gif';
		}
	}
	
	function securityfront_user_on_new(xmlHttp)
	{
		if(!check_error_return(xmlHttp))
			return;
		securityfront_content_panel.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=loaduser&edit_user_id=' + xmlHttp.responseText);
		document.getElementById("securityfront_selected_node_container").value = xmlHttp.responseText;
		document.getElementById("securityfront_selected_type_container").value = "user";
		securityfront_refresh_tree();
	}
	
	function securityfront_group_on_new(xmlHttp)
	{
		if(!check_error_return(xmlHttp))
			return;
		securityfront_content_panel.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=loadgroup&edit_group_id=' + xmlHttp.responseText);
		document.getElementById("securityfront_selected_node_container").value = xmlHttp.responseText;
		document.getElementById("securityfront_selected_type_container").value = "group";
		securityfront_refresh_tree();
	}
	
</script>
<div id="superdiv">
<div id="content">
<div id="securityfront_content_panel">
	<div style="text-align:center">
		<br><br>
		Double click on a user or a usergroup to edit.<br><br>
		To create a new user or a usergroup, use the icons above the user tree.
	</div>	
</div>	
</div>
<div id="sidebar">
	<?php
		echo '<div id="colpan_securityfront_usertree" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_securityfront_usertree, document.getElementById(\'colpan_securityfront_usertree_plusminus\'));"><div class="divleft">Users and groups</div><div class="divright"><img id="colpan_securityfront_usertree_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">
				<div class="iconcontainer">';
		if(login::check_rr("rr_security_front_group_create"))
				echo '<img id="usertree_securityfront_new_folder" class="icon" src="/css/back/icon/twotone/addfolder.gif" style="cursor: pointer;" onclick="send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=securityfront&action=newgroup\', \'\', securityfront_group_on_new);">';
		else
				echo '<img id="usertree_securityfront_new_folder" class="icon" src="/css/back/icon/twotone/gray/addfolder.gif"> '; 
		echo 			'<img id="usertree_securityfront_new_user" class="icon" src="/css/back/icon/twotone/gray/user.gif">
						<img id="usertree_securityfront_edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
						<img id="usertree_securityfront_delete" class="icon" src="/css/back/icon/twotone/gray/trash.gif">
				</div>
				<input type="hidden" value="" id="securityfront_selected_node_container"/>
				<input type="hidden" value="" id="securityfront_selected_type_container"/>
				<div id="securityfront_tree_html_panel">
			</div>
		</div>';
		
		echo 	'<script language="javascript">
						var colpan_securityfront_usertree = new Spry.Widget.CollapsiblePanel("colpan_securityfront_usertree");
						var securityfront_tree_html_panel = new Spry.Widget.HTMLPanel("securityfront_tree_html_panel",{evalScripts:true});
						var securityfront_content_panel = new Spry.Widget.HTMLPanel("securityfront_content_panel",{evalScripts:true}); 
						//securityfront_tree_html_panel.addObserver(content_front_tree_observer); 
				</script>';
	?>
</div>
<div style="clear:both;"></div>
</div>
<script>
	securityfront_tree_html_panel.loadContent("/ajax.php?sessid=<?php echo session_id(); ?>&page=securityfront&action=refresh_tree");
</script>
<?php
}
?>