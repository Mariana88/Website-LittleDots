<?php
	if(!login::right("backpage_security_adminusers", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function securityback_ajaxreturn_delete(xmlHttp)
	{
		securityback_refresh_tree();
	}
	
	function securityback_refresh_tree()
	{
		$("#securityback_tree_html_panel").load("/ajax.php?sessid=" + session_id + "&page=securityback&action=refresh_tree");
	}
	
	function securityback_refresh_grouptree()
	{
		$("#securityback_grouptree_html_panel").load("/ajax.php?sessid=" + session_id + "&page=securityback&action=refresh_tree_group");
	}
	
	function securitybackusertree_select(the_div, the_id)
	{
		document.getElementById("securityback_selected_node_container").value = the_id;
		
		//selecting
		$("#securityback_user_list").children("div").css("backgroundColor", "#FFFFFF")
		$("#securityback_user_" + the_id).css("backgroundColor", "#88A688");
		
		theimg = document.getElementById('usertree_securityback_edit');
		if(the_div.getAttribute("noedit") != "1")
		{
			theimg.onclick=function(e){$("#securityback_content_panel").load('/ajax.php?sessid=' + session_id + '&page=securityback&action=loaduser&edit_user_id=' + the_id);}
			theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/edit.gif';
		}
		else
		{
			theimg.onclick=null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/edit.gif';
		}
		
		theimg = document.getElementById('usertree_securityback_delete');
		if(the_div.getAttribute("nodel") != "1")
		{
			theimg.onclick=function(e){$("#securityback_content_panel").load('/ajax.php?sessid=' + session_id + '&page=securityback&action=deluser&del_user_id=' + the_id);}
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
	
	function securitybackgrouptree_select(the_div, the_id)
	{
		document.getElementById("securityback_selected_group_container").value = the_id;
		
		//selecting
		$("#securityback_group_list").children("div").css("backgroundColor", "#FFFFFF")
		$("#securityback_group_" + the_id).css("backgroundColor", "#88A688");
		
		theimg = document.getElementById('grouptree_securityback_edit');
		if(the_div.getAttribute("noedit") != "1")
		{
			theimg.onclick=function(e){$("#securityback_content_panel").load('/ajax.php?sessid=' + session_id + '&page=securityback&action=loadgroup&edit_group_id=' + the_id);}
			theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/edit.gif';
		}
		else
		{
			theimg.onclick=null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/edit.gif';
		}
		
		theimg = document.getElementById('grouptree_securityback_delete');
		if(the_div.getAttribute("nodel") != "1")
		{
			theimg.onclick=function(e){$("#securityback_content_panel").load('/ajax.php?sessid=' + session_id + '&page=securityback&action=delgroup&del_group_id=' + the_id);}
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
	
	function securityback_user_on_save(aftersave_data, xmlHttp)
	{
		securityback_refresh_tree();
	}	
	
	function securityback_group_on_save(aftersave_data, xmlHttp)
	{
		securityback_refresh_grouptree();
	}	
</script>
<div id="superdiv">
<div id="content">
<div id="securityback_content_panel">
	<div style="text-align:center; color:#CCCCCC;">
		<br><br>
		Double click on a user or group to edit.<br><br>
	</div>	
</div>	
</div>
<div id="sidebar">
	<?php
		echo '<div id="colpan_securityback_usertree" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_securityback_usertree, document.getElementById(\'colpan_securityback_usertree_plusminus\'));"><div class="divleft">Admin Users</div><div class="divright"><img id="colpan_securityback_usertree_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">
				<div class="iconcontainer">';
		echo 			'<img id="usertree_securityback_new_user" class="icon" style="cursor:pointer;" src="/css/back/icon/twotone/plus.gif" onclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=newuser\');">
						<img id="usertree_securityback_edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
						<img id="usertree_securityback_delete" class="icon" src="/css/back/icon/twotone/gray/trash.gif">
				</div>
				<input type="hidden" value="" id="securityback_selected_node_container"/>
				<div id="securityback_tree_html_panel">
			</div>
		</div>';
		
		echo '<br><br><div id="colpan_securityback_grouptree" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_securityback_grouptree, document.getElementById(\'colpan_securityback_grouptree_plusminus\'));"><div class="divleft">User Groups</div><div class="divright"><img id="colpan_securityback_grouptree_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">
				<div class="iconcontainer">';
		echo 			'<img id="grouptree_securityback_new_user" class="icon" style="cursor:pointer;" src="/css/back/icon/twotone/plus.gif" onclick="$(\'#securityback_content_panel\').load(\'/ajax.php?sessid=' . session_id() . '&page=securityback&action=newgroup\');">
						<img id="grouptree_securityback_edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
						<img id="grouptree_securityback_delete" class="icon" src="/css/back/icon/twotone/gray/trash.gif">
				</div>
				<input type="hidden" value="" id="securityback_selected_group_container"/>
				<div id="securityback_grouptree_html_panel">
			</div>
		</div>';
		
		echo 	'<script language="javascript">
						var colpan_securityback_usertree = new Spry.Widget.CollapsiblePanel("colpan_securityback_usertree");
						var colpan_securityback_grouptree = new Spry.Widget.CollapsiblePanel("colpan_securityback_grouptree");
				</script>';
	?>
</div>
<div style="clear:both;"></div>
</div>
<script>
	$("#securityback_tree_html_panel").load("/ajax.php?sessid=<?php echo session_id(); ?>&page=securityback&action=refresh_tree");
	$("#securityback_grouptree_html_panel").load("/ajax.php?sessid=<?php echo session_id(); ?>&page=securityback&action=refresh_tree_group");
</script>
<?php
}
?>