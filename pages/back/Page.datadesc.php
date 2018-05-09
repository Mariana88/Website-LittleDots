<?php
	if(!login::right("backpage_config_datadesc", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function dbtables_on_save(xmlHttp)
	{
		if(xmlHttp.responseText == "NOK")
		{
			//VELD ROODMAKEN
			effects_highlight(document.getElementById("newtable.name"), 1000, "#FF0000", false);
		}
		else
		{
			var table = xmlHttp.responseText;
			//creëren van een nieuw list item en selecteren
			the_div = document.createElement("div");
			the_div.setAttribute("id", 'dbtables_list_' + table);
			the_div.onselectstart = function(e){return false;};
			the_div.style.padding = "3px";
			the_div.style.cursor = "pointer";
			the_div.onclick = function(e){dbtables_selectme(table);};
			the_div.ondblclick = function(e){datadesc_content.loadContent('/ajax.php?sessid=' + session_id + '&page=datadesc&action=loadtable&table=' + table);};
			document.getElementById("dbtables_list").appendChild(the_div);
			the_div.innerHTML = table;
			the_div.style.display = "block";
			dbtables_selectme(table);

			//dan laden van de tabel
			datadesc_content.loadContent('/ajax.php?sessid=' + session_id + '&page=datadesc&action=loadtable&table=' + table);
		}
	}
	
	function dbtables_on_tablemeta(xmlHttp)
	{
		//alert(xmlHttp.responseText);
		var save_ok = cms2_interprete_savestat(xmlHttp.responseXML);
		if(save_ok)
		{
			document.getElementById("button_dbtablemeta").value = 'Saved successfully';
			document.getElementById("button_dbtablemeta").style.backgroundColor = '#88A688';
			effects_highlight(document.getElementById("button_dbtablemeta"), 500, '#66CC66', false);
			setTimeout('document.getElementById("button_dbtablemeta").value = "Save"; effects_highlight(document.getElementById("button_dbtablemeta"), 500, "#88A688", false);', 4000);
		}
		else
		{
			document.getElementById("button_dbtablemeta").value = 'NOT Saved';
			document.getElementById("button_dbtablemeta").style.backgroundColor = '#88A688';
			effects_highlight(document.getElementById("button_dbtablemeta"), 500, '#EA4848', false);
			setTimeout('document.getElementById("button_dbtablemeta").value = "Save"; effects_highlight(document.getElementById("button_dbtablemeta"), 500, "#88A688", false);', 4000);
		}
	}
	
	function dbtables_on_rename(xmlHttp)
	{
		if(xmlHttp.responseText == "NOK")
		{
			//VELD ROODMAKEN
			effects_highlight(document.getElementById("datadesc_rename_table"), 1000, "#FF0000", false);
		}
		else
		{
			//niets gebeurd
			if(xmlHttp.responseText == "NO")
				return;
			
			var chomps = xmlHttp.responseText.split('##splitter##');
			effects_highlight(document.getElementById("datadesc_rename_table"), 2000, "#00FF00", "#FFFFFF");
			//alert(chomps[0]);
			//de div in list aanpassen
			var the_div = document.getElementById('dbtables_list_' + chomps[0])
			the_div.innerHTML = chomps[1];
			the_div.setAttribute("id", 'dbtables_list_' + chomps[1]);
			the_div.onclick = function(e){dbtables_selectme(chomps[1]);};
			the_div.ondblclick = function(e){datadesc_content.loadContent('/ajax.php?sessid=' + session_id + '&page=datadesc&action=loadtable&table=' + chomps[1]);};
			
			//de hidden field met oldname aanpassen
			document.getElementById('datadesc_rename_table_old').value = chomps[1];
			
			//het veld in tablemeta aanpassen
			document.getElementsByName('sys_database_table.table')[0].value = chomps[1];
		}
	}
	
	function dbtables_selectme(the_id)
	{
		document.getElementById("dbtables_selected_table_container").value = the_id;
		// we unselecteren alle andere
		var all_divs = document.getElementById("dbtables_list").getElementsByTagName("div");
		for(var i = 0 ; i < all_divs.length ; i++)
		{
			all_divs[i].style.backgroundColor = "#FFFFFF";
		}
		document.getElementById("dbtables_list_" + the_id).style.backgroundColor = "#88A688";
		
		//enable the icons
		theimg = document.getElementById('dbtables_list_edit');
		theimg.onclick=function(e){datadesc_content.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=datadesc&action=loadtable&table=' + the_id);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/edit.gif';
		
		theimg = document.getElementById('dbtables_list_delete');
		theimg.onclick=function(e){datadesc_content.loadContent('/ajax.php?sessid=<?php echo session_id(); ?>&page=datadesc&action=table_del&table=' + the_id);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/trash.gif';
	}
	
	function datadesc_removefromlist_table(table)
	{
		document.getElementById("dbtables_list_" + table).parentNode.removeChild(document.getElementById("dbtables_list_" + table));
		if(document.getElementById("dbtables_selected_table_container").value == table)
		{
			document.getElementById("dbtables_selected_table_container").value = "";
			//enable the icons
			theimg = document.getElementById('dbtables_list_edit');
			theimg.onclick = null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/edit.gif';
			
			theimg = document.getElementById('dbtables_list_delete');
			theimg.onclick = null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/trash.gif';
		}
	}
	
</script>
<div id="superdiv">
<div id="content">
	<div style="text-align:center; color:#CCCCCC"><br><br><br>Double click on an existing table or datadescription</div>
</div>
<div id="sidebar"><div class="sidebar_inner">
	<?php
		echo '<div id="colpan_dbtables" class="CollapsiblePanel">
				<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_dbtables, document.getElementById(\'colpan_dbtables_plusminus\'));"><div class="divleft">Existing DB Tables</div><div class="divright"><img id="colpan_dbtables_plusminus" src="/css/back/icon/min.gif"/></div></div>
				<div class="CollapsiblePanelContent">
				<div class="iconcontainer">
					<img id="dbtables_list_edit" alt="edit" name="edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
					<img id="dbtables_list_delete" alt="delete" name="delete" class="icon" src="/css/back/icon/twotone/gray/trash.gif">
					<img id="dbtables_list_addnew" alt="add new" name="add new" class="icon" src="/css/back/icon/twotone/plus.gif" onclick="datadesc_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=datadesc&action=newtable\');" style="cursor: pointer;">
				</div>';
		//we get all the existing db tables and show them in a list
		echo '<input type="hidden" id="dbtables_selected_table_container"/>
			<div id="dbtables_list">';
		$result = DBConnect::query("show tables", __FILE__, __LINE__);
		while($row = mysql_fetch_array($result))
		{
			//checken of het een lang suffix heeft en dat de tabel ook bestaat
			$show = true;
			$tmp = explode("_", $row[0]);
			if($tmp[count($tmp)-1] == "lang")
			{
				unset($tmp[count($tmp)-1]);
				$tmp = implode("_", $tmp);
				if(DBConnect::check_if_table_exists($tmp))
					$show = false;
			}
			if($show)
				echo '<div onselectstart="return false;" style="padding: 3px; cursor: pointer;" id="dbtables_list_' . $row[0] . '" onclick="dbtables_selectme(\'' . $row[0] . '\');" ondblclick="datadesc_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=datadesc&action=loadtable&table=' . $row[0] . '\');">' . $row[0] . '</div>';
		}
		echo	'</div>
			</div>
		</div>';
		
		echo '<script language="javascript">
				var colpan_dbtables = new Spry.Widget.CollapsiblePanel("colpan_dbtables");
				var datadesc_content = new Spry.Widget.HTMLPanel("content",{evalScripts:true}); 
				var dbtables_list = new Spry.Widget.HTMLPanel("dbtables_list",{evalScripts:true}); 
			</script>';
	?>
</div></div>div>
<div style="clear:both;"></div>
</div>
<?php
}
?>