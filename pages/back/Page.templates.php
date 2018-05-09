<?php
	if(!login::right("backpage_config_templates", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function sp_selectme(the_id)
	{
		document.getElementById("special_page_selected_page_container").value = the_id;
		// we unselecteren alle andere
		var all_divs = document.getElementById("specialpage_list").getElementsByTagName("div");
		for(var i = 0 ; i < all_divs.length ; i++)
		{
			all_divs[i].style.backgroundColor = "#FFFFFF";
		}
		document.getElementById("sp_list_" + the_id).style.backgroundColor = "#88A688";
		
		//enable the icons
		theimg = document.getElementById('sp_list_edit');
		theimg.onclick=function(e){$("#content").load('/ajax.php?sessid=' + session_id + '&page=templates&action=sp_load&sp_id=' + the_id);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/edit.gif';
		
		theimg = document.getElementById('sp_list_delete');
		theimg.onclick=function(e){$("#content").load('/ajax.php?sessid=' + session_id + '&page=templates&action=sp_delete&sp_id=' + the_id);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/trash.gif';
		
		theimg = document.getElementById('sp_list_rights');
		theimg.onclick=function(e){cms2_show_right_form('Template Rights', 'template', the_id);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/shield.gif';
		
		
	}
			
	function sp_page_refresh_list(aftersave_data, xmlHttp)
	{
		$("#specialpage_list").load('/ajax.php?sessid=' + session_id + '&page=templates&action=refresh_list');
		
		//disable the icons
		theimg = document.getElementById('sp_list_edit');
		theimg.onclick=function(e){}
		theimg.style.cursor='normal';
		theimg.src = '/css/back/icon/twotone/gray/edit.gif';
		
		theimg = document.getElementById('sp_list_delete');
		theimg.onclick=function(e){}
		theimg.style.cursor='normal';
		theimg.src = '/css/back/icon/twotone/gray/trash.gif';
	
		theimg = document.getElementById('sp_list_rights');
		theimg.onclick=function(e){}
		theimg.style.cursor='normal';
		theimg.src = '/css/back/icon/twotone/gray/shield.gif';
	}
	
</script>
<div id="superdiv">
<div id="content">
	<div style="text-align:center; color:#CCCCCC"><br><br><br>Double click on an existing special page to edit.</div>
</div>
<div id="sidebar">
	<?php
		echo '<div id="colpan_specialpages" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_specialpages, document.getElementById(\'colpan_specialpages_plusminus\'));"><div class="divleft">Existing templates</div><div class="divright"><img id="colpan_specialpages_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">
				<div class="iconcontainer">
					<img id="sp_list_edit" alt="edit" name="edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
					<img id="sp_list_delete" alt="delete" name="delete" class="icon" src="/css/back/icon/twotone/gray/trash.gif">
					<img id="sp_list_addnew" alt="add new" name="add new" class="icon" src="/css/back/icon/twotone/plus.gif" onclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=templates&action=sp_new\');" style="cursor: pointer;">
					<img id="sp_list_rights" alt="rights" name="rights" class="icon" src="/css/back/icon/twotone/gray/shield.gif">
					
				</div>';
		//we get all the existing special pages and show them in a list
		echo '<input type="hidden" id="special_page_selected_page_container"/>
			<div id="specialpage_list" style="margin-top: 6px;">';
		$res = DBConnect::query("SELECT * FROM `site_pagetemplates`", __FILE__, __LINE__);
		while($row = mysql_fetch_array($res))
		{
			echo '<div onselectstart="return false;" style="padding: 3px; cursor: pointer;" id="sp_list_' . stripslashes($row["id"]) . '" onclick="sp_selectme(\'' . stripslashes($row["id"]) . '\');" ondblclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=templates&action=sp_load&sp_id=' . stripslashes($row["id"]) . '\');">' . stripslashes($row["id"]) . '&nbsp;' . stripslashes($row["name"]) . '</div>';
		}
		echo	'</div>
			</div>
		</div>';
		
		echo '<script language="javascript">
				var colpan_specialpages = new Spry.Widget.CollapsiblePanel("colpan_specialpages");
			</script>';
	?>
</div>
<div style="clear:both;"></div>
</div>
<?php
}
?>