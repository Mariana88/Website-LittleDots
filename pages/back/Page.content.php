<?php
	if(!login::right("backpage_content_pages", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript" src="/js/page_content.js"></script>
<div id="superdiv">
<div id="content">
	<div style="text-align:center; font-size: 30px;"><br><br><br>Double click on an existing page to edit.<br><br>To create a new page, select the parent page, then use the New Page buttons.</div>
</div>
<div id="sidebar">
	<div class="sidebar_inner">
	<?php
		if(login::right("backpage_content_pages", "new") || true)
		{
			echo '<div id="colpan_front_page_creator">
					<div class="CollapsiblePanelTab"><div class="divleft">New Page</div></div>
			
					<div class="CollapsiblePanelContent">';
	
			$res_templ = DBConnect::query("SELECT * FROM site_pagetemplates");
			while($row_templ = mysql_fetch_array($res_templ))
			{
				if(login::right("template", "create", $row_templ["id"]))
				{
					echo '<div template_id="' . $row_templ["id"] . '" name="newpage_button" class="man_button man_button_disabled" style="float:left; margin:2px;" min_level="' . $row_templ["min_level"] . '" max_level="' . $row_templ["max_level"] . '" parent_templates="' . $row_templ["parent_templates"] . '"  allow_children="' . $row_templ["allow_children"] . '">' . $row_templ["name"] . '</div>';
				}
			}
			
		//	echo '<div class="savebutton" style="float:right; cursor:pointer; margin-top:2px;" onclick="cms2_remove_mce(\'content\'); cms2_show_loader(\'content\'); send_ajax_request(\'GET\', \'/ajax.php?sessid' . session_id() . '&page=content&action=create&template=\' + document.getElementById(\'front_create_page_type\').value, \'\', content_page_on_new);">Create</div>';
			
			echo '<div style="clear:both;"></div>';
			echo	'</div>
				</div>';
		}
		
		echo '<div id="colpan_front_pagetree" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab"><div class="divleft">Site Pages</div></div>
				<div class="CollapsiblePanelContent">
				<div class="toolbar" style="margin-bottom: 4px;">
						<div class="btn disabled" id="pagetree_front_edit"><img src="/css/back/icon/new/edit.png"></div>
						<div class="btn disabled" id="pagetree_front_delete"><img src="/css/back/icon/new/trash.png"></div>
						<div class="btn right" onclick="ddtreemenu.flatten(\'treeview_pages_front\', \'contact\')"><img src="/css/back/icon/new/min.png"></div>
						<div class="btn right" onclick="ddtreemenu.flatten(\'treeview_pages_front\', \'expand\')"><img src="/css/back/icon/new/plus.png"></div>
					</div>
				<input type="hidden" value="" id="content_front_selected_page_container"/>
				<div id="page_front_tree_html_panel">';
		
		//CONTEXT MENU
		/*echo '<ul id="context_pagetree" class="contextMenu">
				<li class="context_pagetree_edit">
					<a href="#edit">Edit</a>
				</li>
				<li class="context_pagetree_delete">
					<a href="#delete">Delete</a>
				</li>
				<li class="context_pagetree_closemenu separator">
					<a href="#close">Close</a>
				</li>
			</ul>';*/
		
		page::publish_tree_front();
		
		echo	'</div>
				</div>';
		
		echo '<br><div id="colpan_front_zoek" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab"><div class="divleft">Search page</div></div>
				<div class="CollapsiblePanelContent">
				<div style="float:left;"><input type="text" name="back_content_zoekterm" id="back_content_zoekterm" style="width: 200px" onKeyUp="if(event.keyCode == 13){ cms2_show_loader(\'zoekresultaten_html_pan\'); $(\'#zoekresultaten_html_pan\').load(\'/ajax.php?sessid=' . session_id() . '&page=content&action=search&searchstring=\' + encodeURI(this.value));}"></div>
				<div style="float:left;"><img src="/css/back/icon/twotone/zoom.gif" class="icon" style="cursor:pointer;" onclick="cms2_show_loader(\'zoekresultaten_html_pan\'); $(\'#zoekresultaten_html_pan\').load(\'/ajax.php?sessid=' . session_id() . '&page=content&action=search&searchstring=\' + encodeURI(document.getElementById(\'back_content_zoekterm\').value));"></div>
				<div style="clear:both;"></div>
				<div id="zoekresultaten_html_pan" name="zoekresultaten_html_pan"></div>
				</div>
				</div>';
	?>
</div>
<div style="clear:both;"></div>
</div>
</div>

<!-- SIDEBAR RIGHT -->
<div id="sidebar_right">
	<div class="accordion">		
		<div class="header open" acc="files">Files</div>
		<div class="content" acc="files" style="">
        <div class="static" style="height: 8px;"></div>
		<?php
            $br = new newBrowse();
			echo '<div class="static" style="height: 32px;">';
			$br->publish_optionbox();
			echo '</div><div class="scroll">';
            $br->publish_tree();
			echo '</div>';
        ?>
        <div class="static" style="height: 8px;"></div>
        </div>
        <!--<div class="header" acc="facebook">Facebook</div>
		<div class="content" acc="facebook">
        	<div class="static" style="height: 30px;">header</div>
            <div class="scroll">een test</div>
        </div>-->
    </div>
</div>
<?php
}
?>
<script language="javascript">
	//CONTEXT MENU
	
	$(document).ready( function() {
 /*
    $("#treeview_pages_front").find("div").contextMenu({
        menu: 'context_pagetree'
    },
        function(action, el, pos) 
		{
 			switch(action)
			{
				case "edit":
					$(el).dblclick();
					break;
				case "delete":
					$("#pagetree_front_delete").click();
					break;
			}
    	}
	);
	*/
	//DRAG AND RROP VAN PAGETREE
	$("#treeview_pages_front").find("li > div").mousemove(function(event) {
			tree_dragmove('treeview_pages_front', this, event);
			//event.pageXevent.pageY;
		});
	$("#treeview_pages_front").find("li > div").mousedown(function(event) {
			if($(this).attr("nodrag") != "1")
				tree_mousedown('treeview_pages_front', this);
			$(this).click();
			//event.pageXevent.pageY;
		});
	$("#treeview_pages_front").find("li > div").mouseup(function(event) {
			tree_mouseup('treeview_pages_front', this, event);
			//event.pageXevent.pageY;
		});
	ddtreemenu.dragcheck = content_tree_dragcheck;
	ddtreemenu.afterdrop = content_tree_afterdrop;
	
	$("#sidebar_right .accordion").blicsmAccordion();
});
</script>