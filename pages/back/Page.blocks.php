<?php
	if(!login::right("backpage_content_blocks", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function blocks_selectme(the_id)
	{
		document.getElementById("blocks_selected_table_container").value = the_id;
		// we unselecteren alle andere
		var all_divs = document.getElementById("blocks_list").getElementsByTagName("div");
		for(var i = 0 ; i < all_divs.length ; i++)
		{
			all_divs[i].style.backgroundColor = "#FFFFFF";
		}
		document.getElementById("blocks_list_" + the_id).style.backgroundColor = "#88A688";
		
		//enable the icons
		theimg = document.getElementById('blocks_list_edit');
		theimg.onclick=function(e){blocks_content.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=blocks&action=load&block=' + the_id);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/edit.gif';
	}	
</script>
<div id="superdiv">
<div id="content">
	<div style="text-align:center; color:#CCCCCC" id="blocks_content"><br><br><br>Double click on one of the blocks to edit it's data.</div>
</div>
<div id="sidebar">
	<?php
		echo '<div id="colpan_blocks" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_blocks, document.getElementById(\'colpan_blocks_plusminus\'));"><div class="divleft">Site Blocks</div><div class="divright"><img id="colpan_blocks_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">
				<div class="iconcontainer">
					<img id="blocks_list_edit" alt="edit" name="edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
				</div>';
		//we get all the existing db tables and show them in a list
		echo '<input type="hidden" id="blocks_selected_table_container"/>
			<div id="blocks_list">';
		$result = DBConnect::query("SELECT * FROM `site_blocks` ORDER BY `order`", __FILE__, __LINE__);
		while($row = mysql_fetch_array($result))
		{
			if(login::right("block", "edit", $row["id"]))
				echo '<div onselectstart="return false;" style="padding: 3px; cursor: pointer;" id="blocks_list_' . $row["id"] . '" onclick="blocks_selectme(\'' . $row["id"] . '\');" ondblclick="blocks_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=blocks&action=load&block=' . $row["id"] . '\');">' . $row["name"] . '</div>';
		}
		echo	'</div>
			</div>
		</div>';
		
		echo '<script language="javascript">
				var colpan_blocks = new Spry.Widget.CollapsiblePanel("colpan_blocks");
				var blocks_content = new Spry.Widget.HTMLPanel("content",{evalScripts:true}); 
			</script>';
	?>
</div>
<div style="clear:both;"></div>
</div>
<?php
}
?>