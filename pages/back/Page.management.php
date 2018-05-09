<script language="javascript">
	
	function management_tree_select(ele)
	{
		$("#managementtree_selected_type").val($(ele).attr("type"));
		$("#managementtree_selected_id").val($(ele).attr("the_id"));
		
		switch($(ele).attr("type"))
		{
			case "0":
				$("#managementtree_newgroup").attr("src", "/css/back/icon/management/button_group.gif").css("cursor", "pointer").unbind("click").click(function(){management_newgroup();});
				$("#managementtree_newentity").attr("src", "/css/back/icon/management/button_entity.gif").css("cursor", "pointer").unbind("click").click(function(){management_newentity();});
				$("#managementtree_newcontact").attr("src", "/css/back/icon/management/button_contact.gif").css("cursor", "pointer").unbind("click").click(function(){management_newcontact();});
				$("#managementtree_edit").attr("src", "/css/back/icon/twotone/gray/edit.gif").css("cursor", "default").unbind("click");
				$("#managementtree_delete").attr("src", "/css/back/icon/twotone/gray/trash.gif").css("cursor", "default").unbind("click");
				break;
			case "group":
				$("#managementtree_newgroup").attr("src", "/css/back/icon/management/button_group.gif").css("cursor", "pointer").unbind("click").click(function(){management_newgroup();});
				$("#managementtree_newentity").attr("src", "/css/back/icon/management/button_entity.gif").css("cursor", "pointer").unbind("click").click(function(){management_newentity();});
				$("#managementtree_newcontact").attr("src", "/css/back/icon/management/button_contact.gif").css("cursor", "pointer").unbind("click").click(function(){management_newcontact();});
				$("#managementtree_edit").attr("src", "/css/back/icon/twotone/edit.gif").css("cursor", "pointer").unbind("click").click(function(){management_edit();});
				$("#managementtree_delete").attr("src", "/css/back/icon/twotone/trash.gif").css("cursor", "pointer").unbind("click").click(function(){management_delete();});
				break;
			case "entity":
				$("#managementtree_newgroup").attr("src", "/css/back/icon/management/button_group_gray.gif").css("cursor", "default").unbind("click");
				$("#managementtree_newentity").attr("src", "/css/back/icon/management/button_entity_gray.gif").css("cursor", "default").unbind("click");
				$("#managementtree_newcontact").attr("src", "/css/back/icon/management/button_contact.gif").css("cursor", "pointer").unbind("click").click(function(){management_newcontact();});
				$("#managementtree_edit").attr("src", "/css/back/icon/twotone/edit.gif").css("cursor", "pointer").unbind("click").click(function(){management_edit();});
				$("#managementtree_delete").attr("src", "/css/back/icon/twotone/trash.gif").css("cursor", "pointer").unbind("click").click(function(){management_delete();});
				break;
			case "contact":
				$("#managementtree_newgroup").attr("src", "/css/back/icon/management/button_group_gray.gif").css("cursor", "default").unbind("click");
				$("#managementtree_newentity").attr("src", "/css/back/icon/management/button_entity_gray.gif").css("cursor", "default").unbind("click");
				$("#managementtree_newcontact").attr("src", "/css/back/icon/management/button_contact_gray.gif").css("cursor", "default").unbind("click");
				$("#managementtree_edit").attr("src", "/css/back/icon/twotone/edit.gif").css("cursor", "pointer").unbind("click").click(function(){management_edit();});
				$("#managementtree_delete").attr("src", "/css/back/icon/twotone/trash.gif").css("cursor", "pointer").unbind("click").click(function(){management_delete();});
				break;
		}
	}
	
	function management_newgroup()
	{
		send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id();?>&page=management&action=newgroup&parent_type=" + $("#managementtree_selected_type").val() + "&parent_id=" + $("#managementtree_selected_id").val(), '', management_newnode);
	}
	
	function management_newentity()
	{
		send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id();?>&page=management&action=newentity&parent_type=" + $("#managementtree_selected_type").val() + "&parent_id=" + $("#managementtree_selected_id").val(), '', management_newnode);
	}
	
	function management_newcontact()
	{
		send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id();?>&page=management&action=newcontact&parent_type=" + $("#managementtree_selected_type").val() + "&parent_id=" + $("#managementtree_selected_id").val(), '', management_newnode);
	}
	
	function management_newnode(xmlHttp)
	{
		$("body").append('<div id="management_tmpdiv">' + xmlHttp.responseText + '</div>');
		$("#management_tmpdiv").find('li').attr("id")
		tree_addnode('treeview_contacts', $("#management_tmpdiv").find('li').html(), 'managementtree_' + $("#management_tmpdiv").children("div.parent_type").text() + '_' + $("#management_tmpdiv").children("div.parent_id").text(), $("#management_tmpdiv").find('li').attr("id"));
		
		
		$('#' + $("#management_tmpdiv").find('li').attr("id")).children("div").mousemove(function(event) {tree_dragmove('treeview_contacts', this, event);});
		$('#' + $("#management_tmpdiv").find('li').attr("id")).children("div").mousedown(function(event) {tree_mousedown('treeview_contacts', this);});
		$('#' + $("#management_tmpdiv").find('li').attr("id")).children("div").mouseup(function(event) {tree_mouseup('treeview_contacts', this, event);});
		$('#' + $("#management_tmpdiv").find('li').attr("id")).children("div").click().dblclick();
		
		$("#management_tmpdiv").remove();		
	}
	
	function management_edit()
	{
		var elem = $('div[type="' + $("#managementtree_selected_type").val() + '"][the_id="' + $("#managementtree_selected_id").val() + '"]').get(0);
		management_tree_open(elem);
	}
	
	function management_delete()
	{
		$("#content").load("/ajax.php?sessid=<?php echo session_id();?>&page=management&action=delete&type=" + $("#managementtree_selected_type").val() + "&id=" + $("#managementtree_selected_id").val());
	}
	
	function management_tree_open(ele)
	{
		$("#content").load("/ajax.php?sessid=<?php echo session_id();?>&page=management&action=load&type=" + $(ele).attr("type") + "&id=" + $(ele).attr("the_id"));
	}
	
	function management_tree_dragcheck(drag_el, drop_el, place)
	{
		var parent_type = '';
		if(place == "in")
			parent_type = $(drop_el).attr("type");
		else
			parent_type = $(drop_el).parent().parent().parent().children("div").first().attr("type");
		switch($(drag_el).attr("type"))
		{
			case 'group':
				//mag enkel in andere groep of type 0 staan
				if(parent_type != '0' && parent_type != 'group')
					return false;
				break;
			case 'entity':
				//mag enkel in groep of type 0 staan
				if(parent_type != '0' && parent_type != 'group')
					return false;
				break;
			case 'contact':
				//mag enkel in groep of type 0 staan
				if(parent_type != '0' && parent_type != 'group' && parent_type != 'entity')
					return false;
				break;
		}
		
		return true;
	}
	
	function management_tree_afterdrop(success, drag_el, drop_el, place, copy)
	{
		if(success)
		{
			var parent_type = '';
			var parent_id = '';
			var parent_name = '';
			if(place == "in")
			{
				parent_type = $(drop_el).attr("type");
				parent_id = $(drop_el).attr("the_id");
				parent_name = $(drop_el).find("span").text();
			}
			else
			{
				parent_type = $(drop_el).parent().parent().parent().children("div").first().attr("type");
				parent_id = $(drop_el).parent().parent().parent().children("div").first().attr("the_id");
				parent_name = $(drop_el).parent().parent().parent().children("div").first().find("span").text();
				
			}
			
			//opslaan
			send_ajax_request("GET", "/ajax.php?sessid=<?php echo session_id();?>&page=management&action=drop&parent_type=" + parent_type + "&parent_id=" + parent_id + "&id=" + $(drag_el).attr("the_id") + "&type=" + $(drag_el).attr("type"), '', null);
		}
	}
	function management_test(xmlHttp)
	{
		alert(xmlHttp.responseText);
	}
	function management_savesuccess(data, xmlHttp)
	{
		//checken welke node er open staat in de editor. deze dan ook aanpassen aan de name veld
		if($("#man_entity_id").length > 0)
		{
			$("#managementtree_entity_" + $("#man_entity_id").val()).find('span').first().text($('input[name="man_entity\\.name"]').val());
		}
		else
		{
			if($("#man_entity_group_id").length > 0)
			{
				$("#managementtree_group_" + $("#man_entity_group_id").val()).find('span').first().text($('input[name="man_entity_group\\.name"]').val());
			}
			if($("#man_contact_id").length > 0)
			{
				$("#managementtree_contact_" + $("#man_contact_id").val()).find('span').first().text($('input[name="man_contact\\.name"]').val());
			}
		}
	}
</script>
<?php
	if(!login::right("backpage_management_dashboard", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<div id="superdiv" style="width: 1200px;">
<div id="content" style="width:790px;">
<?php
	$_GET["action"] = "render_dashboard";
	include('pages/programmedajax/Ajax.management.php');
?>
</div>
<div id="sidebar" style="width:400px;">
<?php				
	echo '<div id="colpan_other" class="toolbox">
					<div class="toolboxheader">Navigate</div>
			<div class="toolboxcontent">';
	//OTHER STUFF
	echo '<div class="man_button" style="float:left; padding:2px 4px 2px 4px;"onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=render_dashboard\', function(){page_management_resize();});">home</div>
		<div class="man_button" style="float:left; padding:2px 4px 2px 4px;" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=fetch_allemail\', function(){page_management_resize();});">mails</div>
		<div class="man_button" style="float:left; padding:2px 4px 2px 4px;" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=todo\', function(){page_management_resize();});">todo</div>
		<div class="man_button" style="float:left; padding:2px 4px 2px 4px;" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=countrylist\', function(){page_management_resize();});">landen</div>
		<div class="man_button" style="float:left; padding:2px 4px 2px 4px;" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=textlist\', function(){page_management_resize();});">teksten</div>
		<div class="man_button" style="float:left; padding:2px 4px 2px 4px;" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=email_templates\', function(){page_management_resize();});">mail templates</div>';
	if($_SESSION["login_usergroup_id"] == 1)
		echo '<div class="man_button" style="float:left; padding:2px 4px 2px 4px;" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=email_config\', function(){page_management_resize();});">mail cfg</div>';
	echo '<div style="clear:both; height:0px:"></div>
		</div>
	</div>';
?>

	<!--CONTACT TREE-->
    <div id="colpan_entity" class="toolbox">
					<div class="toolboxheader">Contacts</div>
			<div class="toolboxcontent">
<?php				
	//CONTACT TREE
	echo 		'<div class="iconcontainer">
					<div class="divleft">
						<img id="managementtree_newgroup" class="icon" src="/css/back/icon/management/button_group_gray.gif">
						<img id="managementtree_newentity" class="icon" src="/css/back/icon/management/button_entity_gray.gif">
						<img id="managementtree_newcontact" class="icon" src="/css/back/icon/management/button_contact_gray.gif">
						<img id="managementtree_edit" class="icon" src="/css/back/icon/twotone/gray/edit.gif">
						<img id="managementtree_delete" class="icon" src="/css/back/icon/twotone/gray/trash.gif">
					</div>
					<div class="divright">
						<a href="javascript:ddtreemenu.flatten(\'treeview_contacts\', \'expand\')"><img id="pagetree_front_movedown" class="icon" src="/css/back/icon/twotone/zoomin.gif"></a>
						<a href="javascript:ddtreemenu.flatten(\'treeview_contacts\', \'contact\')"><img id="pagetree_front_movedown" class="icon" src="/css/back/icon/twotone/zoomout.gif"></a>
					</div>
				</div>';
	echo '<input type="hidden" value="" id="managementtree_selected_type"/><input type="hidden" value="" id="managementtree_selected_id"/>
		<div id="management_tree_html_panel" style="overflow: scroll; width:380px;">';
	$_GET["action"] = "render_contacttree";
	include('pages/programmedajax/Ajax.management.php');
	echo '</div>';
?>
	<div style="clear:both;">
    </div>
    </div>
	</div>
    
     <!--  FILTER  -->
     <div id="colpan_filter" class="toolbox">
					<div class="toolboxheader"><div class="divleft">Filter Contacts</div></div>
			<div class="toolboxcontent">
<?php				
	//LAND
	echo '<label style="width:100px;">Country:</label><select style="width:280px;" id="contactfilter_land"><option value=""></option>';
	//ophalen alle landen
	$resland = DBConnect::query("SELECT * FROM data_land ORDER BY `naam`", __FILE__, __LINE__);
	while($rowland = mysql_fetch_array($resland))
	{
		echo '<option value="' . $rowland["id"] . '">' . $rowland["naam"] . '</option>';
	}
	echo '</select>';
	//TAG
	echo '<label style="width:100px;">Tag:</label><select style="width:280px;" id="contactfilter_tag"><option value=""></option>';
	//ophalen alle tags
	$resland = DBConnect::query("SELECT * FROM man_tags ORDER BY `rank`, `tag`", __FILE__, __LINE__);
	while($rowland = mysql_fetch_array($resland))
	{
		echo '<option value="' . $rowland["tag"] . '">' . $rowland["tag"] . '</option>';
	}
	echo '</select>';
	//NAME
	echo '<label style="width:100px;">Name:</label><input style="width:274px;" type="text" id="contactfilter_name" value=""/>';
	//Buttons
	echo '<br><input type="button" id="contactfilter_filter" value="Filter"><input type="button" id="contactfilter_clear" value="clear">';
?>
	<div style="clear:both;">
    </div>
    </div>
	</div>
	<script language="javascript">
		$("#contactfilter_filter").click(function(){
			cms2_show_loader('management_tree_html_panel');
			$("#management_tree_html_panel").load('/ajax.php?sessid=<?php echo session_id();?>&page=management&action=render_contacttree&filter_name=' + encodeURI($("#contactfilter_name").val()) + '&filter_land=' + encodeURI($("#contactfilter_land").val()) + '&filter_tag=' + encodeURI($("#contactfilter_tag").val()))								  
		});
		$("#contactfilter_clear").click(function(){
			cms2_show_loader('management_tree_html_panel');
			$("#management_tree_html_panel").load('/ajax.php?sessid=<?php echo session_id();?>&page=management&action=render_contacttree');
			$("#contactfilter_name").val('');
			$("#contactfilter_land").val('');
			$("#contactfilter_tag").val('');
		});
	</script>
	
</div>
</div>
<?php
	}
?>
<script language="javascript">
	$(window).resize(function() {
	 	page_management_resize();
	});
	$(document).ready(function() {
	 	page_management_resize();
	});
	
	function page_management_resize()
	{
		//---------BREEDTE-----------------------------------
		var total_width = $(window).width() - 40;
		if(total_width < 800)
			$("#superdiv").css("width", "800px");
		else
			$("#superdiv").css("width", total_width + "px");

		$("#sidebar").css("width", ($("#superdiv").width() * 0.25) + "px");
		$("#content").css("width", ($("#superdiv").width() - $("#sidebar").width() - 20) + "px");
		$("#management_tree_html_panel").css("width", ($("#sidebar").width() - 16) + "px");
		//filter form
		$("#contactfilter_land").css("width", $("#sidebar").width() - 120 + "px");
		$("#contactfilter_tag").css("width", $("#sidebar").width() - 120 + "px");
		$("#contactfilter_name").css("width", $("#sidebar").width() - 126 + "px");
		
		//content
		$("#man_content_left").css("width", ($("#content").width() - 250) + "px");
		
		//---------HOOGTE------------------------------------
		var total_height = $(window).height() - 160;
		
		if(total_height < 400)
			$("#superdiv").css("height", "200px");
		else
			$("#superdiv").css("height", total_height + "px");
			
		$("#management_tree_html_panel").css("height", ($("#superdiv").height() - $("#colpan_other").height() - $("#colpan_filter").height()) + "px");
		
		//content
		if($(".contentcontent").height() > $("#superdiv").height())
		{
			$(".contentcontent").css({"overflow":"scroll", "height": ($("#superdiv").height() + 28) + "px"});
		}
		else
		{
			$(".contentcontent").css({"overflow":"none", "height": "auto"});
		}
		
		
	}
</script>