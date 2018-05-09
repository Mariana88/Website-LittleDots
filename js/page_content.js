function content_changelang(templateform, langlink)
	{
		if($(langlink).parent().children('span[current="true"]').attr("lang") != $(langlink).attr("lang"))
		{
			window.content_page_templateform = templateform;
			window.content_page_templateform.changelang($(langlink).attr("lang"), langlink);
			window.site_page_form.changelang($(langlink).attr("lang"), langlink);
			
			//veranderen van langlinks
			$(langlink).parent().children("span").css({'background-color':'#CCCCCC', 'color':'#666666', 'cursor':'pointer'});
			$(langlink).parent().children('span[error="true"]').css({'background-color':'#EA4848', 'color':'#666666'});
			$(langlink).parent().children('span').attr("current", "false");
			if($(langlink).attr("error") != "true")
				$(langlink).css({'background-color':'#666666', 'color':'#CCCCCC', 'cursor':'auto'});
			else
				$(langlink).css({'background-color':'#EA4848', 'color':'#CCCCCC', 'cursor':'auto'});
			$(langlink).attr("current", "true");
			//naar server sturen welke lang we selecteren
			send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&page=content&action=changelang&lang=' + $(langlink).attr("lang"), '', null);
			//de seo info aanpassen
			if($("#site_page_seo_add_parent_keywords").get(0).checked)
			{
				$("#content_seo_parent_keywords").load("/ajax.php?sessid=" + session_id + "&page=content&action=getparentseo_key&page_id=" + $("#site_page_id").val() + "&lang=" + window.site_page_form.currentlang);
				$("#content_seo_parent_keywords").css("display", "block");
			}
			else
			{
				$("#content_seo_parent_keywords").empty();
				$("#content_seo_parent_keywords").css("display", "none");
			}
			if($("#site_page_seo_add_parent_description").get(0).checked)
			{
				$("#content_seo_parent_description").load("/ajax.php?sessid=" + session_id + "&page=content&action=getparentseo_desc&page_id=" + $("#site_page_id").val() + "&lang=" + window.site_page_form.currentlang);
				$("#content_seo_parent_description").css("display", "block");
			}
			else
			{
				$("#content_seo_parent_description").empty();
				$("#content_seo_parent_description").css("display", "none");
			}
		}
	}
	
	function content_save(templateform)
	{
		window.content_page_templateform = templateform;
		$("#content_page_savebutton").text("Saving");
		//$("#content_page_savebutton").corner("tl bevel 10px");
		//get the formdata toghether and post it
		page_xml = window.site_page_form.create_post_xml(false);
		content_xml = templateform.create_post_xml(false);
		//copy_page_xml = $(cms2_string_to_xml((new XMLSerializer()).serializeToString(page_xml.get(0))));
		//copy_content_xml = $(cms2_string_to_xml((new XMLSerializer()).serializeToString(content_xml.get(0))));
		//copy_page_xml = $(cms2_cloneObj(page_xml.get(0)));
		//copy_content_xml = $(cms2_cloneObj(content_xml.get(0)));
		page_node = page_xml.find("table").get(0).cloneNode(true);
		content_node = content_xml.find("table").get(0).cloneNode(true);
		new_doc = cms2_string_to_xml('<?xml version="1.0" encoding="utf-8"?><formdata></formdata>');
		new_doc.documentElement.appendChild(page_node);
		new_doc.documentElement.appendChild(content_node);
		//new_doc = $(new_doc);
				
		xmlstr = cms2_xml_to_string(new_doc);
		//alert(xmlstr);
		xmlstr = xmlstr.replace(/&/g, "___AMP___");
		xmlstr = xmlstr.replace(/\?/g, "___QUEST___");
		xmlstr = xmlstr.replace(/#/g, "___HEK___");
		xmlstr = xmlstr.replace(/\+/g, "___PLUS___");
		xmlstr = xmlstr.replace(/€/g, "___EUR___");
		//alert(xmlstr);
		
		send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&page=content&action=save', 'data=' + encodeURI(xmlstr), content_page_ajaxreturn_save);
	}
	
	function content_page_ajaxreturn_save(xmlHttp)
	{
		if(cms2_ajax_error(xmlHttp)) return;
		//alert(xmlHttp.responseText);
		window.content_page_save_ok_page = null;
		window.content_page_save_ok_template = null;
		
		window.site_page_form.aftersave_success = "content_page_aftersave_success_page";
		window.site_page_form.aftersave_error = "content_page_aftersave_error_page";
		window.content_page_templateform.aftersave_success = "content_page_aftersave_success_template";
		window.content_page_templateform.aftersave_error = "content_page_aftersave_error_template";
		window.site_page_form.interprete_save(xmlHttp);
		window.content_page_templateform.interprete_save(xmlHttp);
		
		//hiden van langlinks errors
		$("#sitepage_langlinks").children('span').attr('error', 'false');
		$("#sitepage_langlinks").children("span").css({'background-color':'#CCCCCC', 'color':'#666666'});
		$("#sitepage_langlinks").children('span[current="true"]').css({'background-color':'#666666', 'color':'#CCCCCC'});
		$("#sitepage_langlinks").children('span[error="true"]').css({'background-color':'#EA4848', 'color':'#666666'});
		$(xmlHttp.responseXML).find("lang").each(function(){
			if ($(this).find("error").length > 0)
			{
				$("#sitepage_langlinks").children('span[lang="' + $(this).attr("id") + '"]').css('background-color','#EA4848');
				$("#sitepage_langlinks").children('span[lang="' + $(this).attr("id") + '"]').attr('error', 'true');
			}
		});
	}
	//FUNCTIES DIE AANGEROEPEN WORDEN DOOR DE FORM OM TE CHECKEN OF DE 2 FORMS SUCCESSVOL WAREN
	//---------------------------------------------------------------------------------------
	function content_page_aftersave_success_page(aftersavedata, xmlHttp)
	{
		if(cms2_ajax_error(xmlHttp)) return;
		window.content_page_save_ok_page = true;
		if(window.content_page_save_ok_template != null)
		{
			if(window.content_page_save_ok_template && window.content_page_save_ok_page)
				content_page_aftersave_success();
			else
				content_page_aftersave_error();
		}
	}
	
	function content_page_aftersave_success_template(aftersavedata, xmlHttp)
	{
		if(cms2_ajax_error(xmlHttp)) return;
		window.content_page_save_ok_template = true;
		if(window.content_page_save_ok_page != null)
		{
			if(window.content_page_save_ok_template && window.content_page_save_ok_page)
				content_page_aftersave_success();
			else
				content_page_aftersave_error();
		}
	}
	
	function content_page_aftersave_error_page(aftersavedata, xmlHttp)
	{
		if(cms2_ajax_error(xmlHttp)) return;
		window.content_page_save_ok_page = false;
		if(window.content_page_save_ok_template != null)
		{
			if(window.content_page_save_ok_template && window.content_page_save_ok_page)
				content_page_aftersave_success();
			else
				content_page_aftersave_error();
		}
	}
	
	function content_page_aftersave_error_template(aftersavedata, xmlHttp)
	{
		if(cms2_ajax_error(xmlHttp)) return;
		window.content_page_save_ok_template = false;
		if(window.content_page_save_ok_page != null)
		{
			if(window.content_page_save_ok_template && window.content_page_save_ok_page)
				content_page_aftersave_success();
			else
				content_page_aftersave_error();
		}
	}
	
	//----------------------------------------------------------------------------------------
	
	function content_page_aftersave_success()
	{
		//content_front_refresh_tree();
		$("#pagetree_front_" + $("#content_front_selected_page_container").val()).children("div").load('/ajax.php?sessid=' + session_id + '&page=content&action=getpagecaption&page_id=' + $("#content_front_selected_page_container").val());
		$("#content_page_savebutton").text("SAVED");
		//$("#content_page_savebutton").corner("tl bevel 10px");
		
		effects_highlight($("#content_page_savebutton").get(0), 1000, "#66CC66", false);
		setTimeout('$("#content_page_savebutton").text("Save Page");  effects_highlight($("#content_page_savebutton").get(0), 1000, "#4D6F8C", false);', 4000);
		
		//check for inline edit
		if($("#main_inline_edit_div").get(0) != undefined)
			inline_edit_after_save();
	}
	
	function content_page_aftersave_error()
	{
		$("#content_page_savebutton").text("ERRORS");
		//$("#content_page_savebutton").corner("tl bevel 10px");
		
		effects_highlight($("#content_page_savebutton").get(0), 1000, "#EA4848", false);
		setTimeout('$("#content_page_savebutton").text("Save Page"); effects_highlight($("#content_page_savebutton").get(0), 1000, "#4D6F8C", false);', 4000)
	}
	
	function content_page_ajaxreturn_delete(xmlHttp)
	{
		//alert(xmlHttp.responseText);
		if(cms2_ajax_error(xmlHttp)) return;
		
		if(xmlHttp.responseText == "OK")
		{
			//de geselecteerde node verwijderen uit de boom
			$("#pagetree_front_" + document.getElementById("content_front_selected_page_container").value).remove();
		}
		else
		{
			alert(xmlHttp.responseText);
		}
	}
	
	function content_page_on_new(xmlHttp)
	{
		//alert(xmlHttp.responseText);
		if(cms2_ajax_error(xmlHttp)) return;
		
		doc = $(xmlHttp.responseXML);
		if(doc.find("alert").length > 0)
			alert(doc.find("alert").text());
		$('#content').load('/ajax.php?sessid=' + session_id + '&page=content&action=loadpage&edit_page_id=' + doc.find("newid").text());
		document.getElementById("content_front_selected_page_container").value = doc.find("newid").text();
		
		//toevoegen node aan tree
		tree_addnode("treeview_pages_front", doc.find("divhtml").text(), doc.find("parent_li_id").text(), doc.find("li_id").text());
		
		the_div = $("#" + doc.find("li_id").text()).children("div");
		the_div.mousemove(function(event) {tree_dragmove('treeview_pages_front', this, event);});
		the_div.mousedown(function(event) {	if($(this).attr("nodrag") != "1") tree_mousedown('treeview_pages_front', this);});
		the_div.mouseup(function(event) {tree_mouseup('treeview_pages_front', this, event);});
		select_me_please('treeview_pages_front', the_div.get(0)); 
		frontpagetree_select(the_div.get(0), doc.find("newid").text());
	}
	
	//TOCHECK
	function frontpagetree_select(the_link, page_id)
	{
		//alert(the_link);
		document.getElementById("content_front_selected_page_container").value = page_id;
		if(the_link.getAttribute("noedit") != "1")
		{
			theimg = document.getElementById('pagetree_front_edit');
			//theimg.onclick = the_link.ondblclick;
			theimg.onclick=function(e){cms2_remove_mce('content'); cms2_show_loader('content'); $('#content').load('/ajax.php?sessid=' + session_id + '&page=content&action=loadpage&edit_page_id=' + page_id);}
			/*theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/edit.gif';
			$("#context_pagetree").enableContextMenuItems("#edit");*/
			$(theimg).removeClass("disabled");
		}
		else
		{
			theimg = document.getElementById('pagetree_front_edit');
			theimg.onclick=function(e){}
			/*theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/edit.gif';
			$("#context_pagetree").disableContextMenuItems("#edit");*/
			$(theimg).addClass("disabled");
		}
		
		if(the_link.getAttribute("nodel") != "1")
		{
			theimg = document.getElementById('pagetree_front_delete');
			theimg.onclick=function(e)
			{
				cms2_show_question_message('Are you sure you want to delete this page?', 'delete?', content_delete_accept, function(){$( "#cms2_message_div" ).dialog('close')})
			}
			/*theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/trash.gif';
			$("#context_pagetree").enableContextMenuItems("#delete");*/
			$(theimg).removeClass("disabled");
		}
		else
		{
			theimg = document.getElementById('pagetree_front_delete');
			theimg.onclick=function(e){}
			/*theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/trash.gif';
			$("#context_pagetree").disableContextMenuItems("#delete");*/
			$(theimg).addClass("disabled");
		}
		
		//EDIT NEW PAGE BUTTONS
	/*	var level = -1;
		var levelch = $(the_link).parent("li");
		while(levelch.attr("id") != "pagetree_front_site" && levelch.attr("id") != "pagetree_front_new")
		{
			level++;
			levelch = levelch.parent("ul").parent("li");
		}
		level++;
		*/
		$('div[name="newpage_button"]').each(function(){
			//canhavechildren
			var enabled = true;
			if($(the_link).attr("canhavechild")!='1')
				enabled = false;
			//parent_templates
			if($(this).attr("parent_templates") != "")
			{
				
				var possible_templates = $(this).attr("parent_templates").split(';');
				var foundtemplate = false;
				for(var i = 0 ; i < possible_templates.length ; i++)
				{
					if(possible_templates[i] == $(the_link).attr("template"))	
					{
						foundtemplate = true;
						break;
					}
				}
				if(!foundtemplate)
					enabled = false;
			}
			//max and min level
			
			if(parseInt($(this).attr("min_level")) > (parseInt($(the_link).attr('level'))+1) || parseInt($(this).attr("max_level")) < (parseInt($(the_link).attr('level'))+1))
				enabled = false;
			
			//add functions
			if(enabled)
			{
				$(this).removeClass("man_button_disabled");
				$(this).unbind('click');
				$(this).click(function(){
					cms2_remove_mce('content');
					cms2_show_loader('content');
					send_ajax_request('GET', '/ajax.php?sessid' + session_id + '&page=content&action=create&template=' + $(this).attr("template_id") + '&parent_id=' + $(the_link).attr("pageid"), '', content_page_on_new);				   
				});
			}
			else
			{
				$(this).unbind('click');
				$(this).addClass('man_button_disabled');	
			}
		});
	}
	
	//TOCHECK
	function content_delete_accept()
	{
		if(document.getElementById("content_front_selected_page_container").value != "")
		{
			send_ajax_request("GET", "/ajax.php?sessid=" + session_id + "&page=content&action=delete&delpage_id=" + document.getElementById("content_front_selected_page_container").value, "", content_page_ajaxreturn_delete);
			$( "#cms2_message_div" ).dialog('close');
			$('div[name="newpage_button"]').unbind('click');
			$('div[name="newpage_button"]').addClass('man_button_disabled');
		}
	}
	
	function content_front_refresh_tree()
	{
		ddtreemenu.rememberstate('treeview_pages_front', 1);
		$('#page_front_tree_html_panel').load("/ajax.php?sessid=" + session_id + "&page=content&action=refresh_tree");
	}
	
	/*//TOCHECK
	function content_front_tree_observer(notificationType, notifier, data)
   	{
      	if (notificationType == "onPostUpdate")
        {
			if(document.getElementById("content_front_selected_page_container").value == "")
			{
				//disable icons
				theimg = document.getElementById('pagetree_front_edit');
				theimg.onclick=function(e){}
				theimg.style.cursor='default';
				theimg.src = '/css/back/icon/twotone/gray/edit.gif';
				
				theimg = document.getElementById('pagetree_front_delete');
				theimg.onclick=function(e){}
				theimg.style.cursor='default';
				theimg.src = '/css/back/icon/twotone/gray/trash.gif';
			}
			else
			{
				var the_li = document.getElementById("pagetree_front_" + document.getElementById("content_front_selected_page_container").value);
				var the_a = the_li.getElementsByTagName("a")[0];
				if(the_a !== null)
				{
					select_me_please('treeview_pages_front', the_a);
					frontpagetree_select(the_a, document.getElementById("content_front_selected_page_container").value);
				}
			}
		}
   	}*/
	
	//place can be 'in', 'under', 'above'
	function content_tree_dragcheck(drag_el, drop_el, place)
	{
		//CHECK IF YOU WANT TO DROP OUTSIDE HOME OR NEWPAGES
		if(place != "in")
		{
			if($(drop_el).parent().attr("id")=="pagetree_front_site" || $(drop_el).parent().attr("id")=="pagetree_front_new")
				return false;
		}
		//check if the templates are good! "canhavechild", "parenttemplates"
		if(place == "in")
		{
			if($(drop_el).attr("canhavechild") == "0")
				return false;
		}
		else
		{
			if($(drop_el).parent().parent().parent().children("div").attr("canhavechild") == "0")
				return false;
		}
		
		//if($("#testdiv").get(0) == undefined)
		//	$("#superdiv").append('<div id="testdiv" style="font-size:16px; color:white; "></div>');
		if(place == "in")
		{
			if($(drag_el).attr("parenttemplates") != "")
			{
				
				var tmp = $(drag_el).attr("parenttemplates").split(";");
				//$("#testdiv").text((tmp[1]==$(drop_el).attr("template")).toString());
				var found = false;
				for(var i = 0 ; i < tmp.length ; i++)
				{
					if(tmp[i] == $(drop_el).attr("template")){
						found = true;
						break;
					}
				}
				if(!found)
					return false;
			}
		}
		else
		{
			if($(drag_el).attr("parenttemplates") != "")
			{
				tmp = $(drag_el).attr("parenttemplates").split(';');
				found = false;
				for(var i = 0 ; i < tmp.length ; i++)
				{
					if(tmp[i] == $(drop_el).parent("li").parent("ul").parent("li").children("div").attr("template")){
						found = true;
						break;
					}
				}
				if(!found)
						return false;
			}
		}
		
		//nu nog de levels checken
		var level = parseInt($(drop_el).attr("level"));
		/*var levelch = $(drop_el).parent("li");
		while(levelch.attr("id") != "pagetree_front_site" && levelch.attr("id") != "pagetree_front_new")
		{
			level++;
			levelch = levelch.parent("ul").parent("li");
		}*/
		if(place == "in")
			level++;
		
		if($(drag_el).attr("max_level") < level || $(drag_el).attr("min_level") > level)
			return false;
		
		return true;
	}

	function content_tree_afterdrop(success, drag_el, drop_el, place, copy)
	{
		if(success)
		{
			send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&page=content&action=drop&drag_id=' + $(drag_el).attr("pageid") + '&drop_id=' + $(drop_el).attr("pageid") + '&place=' + place + '&copy=' + copy, '', content_tree_afterdrop_return);
		}
		//else
			//alert("no drop");
	}
	
	function content_tree_afterdrop_return(xmlHttp)
	{
		if(cms2_ajax_error(xmlHttp)) return;
		//alert(xmlHttp.responseText);
		doc = $(xmlHttp.responseXML)
		if(doc.find("error").length > 0)
			cms2_show_error_message(doc.find("error").text(), 'Page drop');
		if(doc.find("warning").length > 0)
			cms2_show_error_message(doc.find("warning").text(), 'Page drop');
		if(!doc.find("error").length > 0)
		{
			if(doc.find("copyof_root").get(0) != undefined && doc.find("copyof_newid").get(0))
			{
				//alert('div[pageid="' + doc.find("copyof_root").text() + '"][copyof="' + doc.find("copyof_root").text() + '"]');
				$('div[pageid="' + doc.find("copyof_root").text() + '"][copyof="' + doc.find("copyof_root").text() + '"]').attr("pageid", doc.find("copyof_newid").text());
				//alert($('div[pageid="' + doc.find("copyof_newid").text() + '"][copyof="' + doc.find("copyof_root").text() + '"]').attr("pageid"));
				//toevoegen van de dragfuncties
				$("#treeview_pages_front").find('div[pageid="' + doc.find("copyof_newid").text() + '"][copyof="' + doc.find("copyof_root").text() + '"]').mousemove(function(event) {
						tree_dragmove('treeview_pages_front', this, event);
					});
				$("#treeview_pages_front").find('div[pageid="' + doc.find("copyof_newid").text() + '"][copyof="' + doc.find("copyof_root").text() + '"]').mousedown(function(event) {
						if($(this).attr("nodrag") != "1")
							tree_mousedown('treeview_pages_front', this);
					});
				$("#treeview_pages_front").find('div[pageid="' + doc.find("copyof_newid").text() + '"][copyof="' + doc.find("copyof_root").text() + '"]').mouseup(function(event) {
						tree_mouseup('treeview_pages_front', this, event);
					});
			}
		}
			
	}