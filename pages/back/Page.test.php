<?php
	if(!login::right("backpage_config_test", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>

<script>
	function test_postback(xmlHttp)
	{
		var save_ok = cms2_interprete_savestat(xmlHttp.responseXML);
		if(save_ok)
		{
			document.getElementById("savebutton").value = 'Saved successfully';
			document.getElementById("savebutton").style.backgroundColor = '#88A688';
			effects_highlight(document.getElementById("savebutton"), 500, '#66CC66', false);
			setTimeout('document.getElementById("savebutton").value = "Save"; effects_highlight(document.getElementById("savebutton"), 500, "#88A688", false);', 4000);
		}
		else
		{
			document.getElementById("savebutton").value = 'NOT Saved';
			document.getElementById("savebutton").style.backgroundColor = '#88A688';
			effects_highlight(document.getElementById("savebutton"), 500, '#EA4848', false);
			setTimeout('document.getElementById("savebutton").value = "Save"; effects_highlight(document.getElementById("savebutton"), 500, "#88A688", false);', 4000);
		}
	}
</script>

<div id="superdiv">
<div id="content">


<?php
	echo '<div class="contentheader">
				<div class="divleft">Hier doen we tests</div>
			</div>
			<div class="contentcontent" id="testpost">';
	//form::show_autoform_new("abcd_test", NULL);
	//echo '<input type="button" value="Save" id="savebutton" onclick="this.value=\'...saving...\'; ajax_post_form(\'testpost\', \'/ajax.php?sessid=' . session_id() . '&page=test&action=post\', test_postback, false)">';
	
	$de = new dataeditor("test_de", 500, 500, "data_test");
	//$de->set_current_lang("FR");
	$de->publish(false);

	echo '<div style="clear:both;"></div>';
	echo '</div>';
	
	//inputten van data
	/*for($i = 1 ; $i < 100 ; $i++)
	{
		DBConnect::query("INSERT INTO `a_grid_test` (`order`, `beetje`, `veel`) VALUES ('" . $i . "', 'beetje tekst', 'veeeeeeeeel tekst, jaja met veel bedoel ik veel')", __FILE__, __LINE__);
	}*/
?>
</div>
</div>
<?php
	}
?>
