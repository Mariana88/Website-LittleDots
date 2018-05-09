<?php
	if(!login::right("backpage_content_extra", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function extra_selectme(the_id)
	{
		// we unselecteren alle andere
		var all_divs = document.getElementById("extra_list").getElementsByTagName("div");
		for(var i = 0 ; i < all_divs.length ; i++)
		{
			all_divs[i].style.backgroundColor = "#FFFFFF";
		}
		document.getElementById(the_id).style.backgroundColor = "#88A688";		
	}	
</script>
<div id="superdiv">
<div id="content">
	<div style="text-align:center; color:#CCCCCC" id="blocks_content"><br><br><br>Double click on one of the items to edit.</div>
</div>
<div id="sidebar">
	<?php
		echo '<div id="colpan_extra" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_extra, document.getElementById(\'colpan_extra_plusminus\'));"><div class="divleft">Site Blocks</div><div class="divright"><img id="colpan_extra_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">';
		echo '<div id="extra_list">';
		echo '<div style="padding: 3px; cursor: pointer;" id="extra_locaties" onclick="extra_selectme(\'extra_locaties\');" ondblclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=content_extra&action=locaties\');">Locaties</div>';
		echo '<div style="padding: 3px; cursor: pointer;" id="extra_instrumenten" onclick="extra_selectme(\'extra_instrumenten\');" ondblclick="$(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=content_extra&action=instrumenten\');">Instrumenten</div>';
		echo	'</div>
			</div>
		</div>';
		
		echo '<script language="javascript">
				var colpan_extra = new Spry.Widget.CollapsiblePanel("colpan_extra");
			</script>';
	?>
</div>
<div style="clear:both;"></div>
</div>
<?php
}
?>