var dataeditor_current_list_panel = null;
var dataeditor_current_id = null;
var dataeditor_current_dg_id = null;

/*
function dataeditor_ajaxreturn_save(xmlHttp)
{
	//alert(xmlHttp.responseText);
	var save_ok = cms2_interprete_savestat(xmlHttp.responseXML);
	var domdoc = $(xmlHttp.responseXML)
	var dialog_id = domdoc.find('dialog_id').text();
	var dg_id = domdoc.find('dg_id').text();
	if(save_ok)
	{
		if(domdoc.find('reload_form').text() == "yes")
		{
			var button = $("#" + dialog_id).parent().find(".ui-button-text:contains('Saving')");
			button.text("Saved Successfully");
			button = button.get(0);
			button.style.backgroundColor = '#4D6F8C';
			effects_highlight(button, 500, '#66CC66', null);
			setTimeout("dataeditor_reset_savebutton('" + dialog_id + "');", 4000);
			//nu de form herladen
			$("#de_dialog_" + domdoc.find('reload_popup_id').text() + "_form").html('<div style="height:70px;padding-top:30px;text-align:center"><img src="/css/back/loader.gif"></div>'); 
			$("#de_dialog_" + domdoc.find('reload_popup_id').text() + "_form").load(domdoc.find('reload_loadstr').text());
		}
		else
		{
			$("#" + dialog_id).parent().find(".ui-button-text:contains('Saving')").text("Save");
			$("#" + dialog_id).dialog('close');
			//window['dg_' + dg_id + '_html_panel'].loadContent('/ajax.php?sessid=' + session_id + '&dg_id=' + dg_id + '&action=refresh');
		}
		window['dg_' + dg_id + '_html_panel'].loadContent('/ajax.php?sessid=' + session_id + '&dg_id=' + dg_id + '&action=refresh');
	}
	else
	{
		var button = $("#" + dialog_id).parent().find(".ui-button-text:contains('Saving')");
		button.text("NOT Saved");
		button = button.get(0);
		button.style.backgroundColor = '#4D6F8C';
		effects_highlight(button, 500, '#EA4848', null);
		setTimeout("dataeditor_reset_savebutton('" + dialog_id + "');", 4000);
	}
}
*/
/*
function dataeditor_reset_savebutton(dialog_id)
{
	var button = $("#" + dialog_id).parent().find(".ui-button-text:contains('NOT Saved')");
	if(button.length <= 0)
		button = $("#" + dialog_id).parent().find(".ui-button-text:contains('Saved Successfully')");
	button.text("Save");
	button = button.get(0);
	if(button != null)
		effects_highlight(button, 500, '#4D6F8C', null);
}
*/
function dataeditor_delete_accept(the_ids, popup_id, dg_id, list_panel)
{
	dataeditor_current_list_panel = list_panel;
	dataeditor_current_id = popup_id;
	dataeditor_current_dg_id = dg_id;
	
	send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + popup_id + '&delete=1', "delete=" + encodeURI(the_ids), dataeditor_delete_return);
}

function dataeditor_delete_return(xmlHttp)
{
	dataeditor_current_list_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=' + dataeditor_current_dg_id + '&action=refresh');
	$( "#cms2_message_div" ).dialog('close');
}

/*
function dataeditor_fill_form(xmlHttp)
{
	var xmlDoc = xmlHttp.responseXML;

	for(var i = 0 ; i < xmlDoc.documentElement.childNodes.length ; i++)
	{
		var id = xmlDoc.documentElement.childNodes[i].getElementsByTagName("id")[0].childNodes[0].nodeValue.replace(/^\s+|\s+$/g,"");
		var type = xmlDoc.documentElement.childNodes[i].getElementsByTagName("type")[0].childNodes[0].nodeValue.replace(/^\s+|\s+$/g,"");
		var ie = (typeof window.ActiveXObject != 'undefined');
		var value = "";
		if(xmlDoc.documentElement.childNodes[i].getElementsByTagName("value")[0].hasChildNodes)
		{
			if(ie)
				var value = xmlDoc.documentElement.childNodes[i].getElementsByTagName("value")[0].childNodes[0].nodeValue.replace(/^\s+|\s+$/g,"");
			else
			{
				if(xmlDoc.documentElement.childNodes[i].getElementsByTagName("value")[0].childNodes[1] !== undefined)
					var value = xmlDoc.documentElement.childNodes[i].getElementsByTagName("value")[0].childNodes[1].nodeValue.replace(/^\s+|\s+$/g,"");
			}
		}
		
		switch (type)
		{
			case "HTML":
				cms2_add_editor_load(FCKeditorAPI.GetInstance(id), value);
				//FCKeditorAPI.GetInstance(id).SetHTML(value);
				break;
			case "yesno":
				if(value > 0)
					document.getElementsByName(id)[0].checked = true;
				else
					document.getElementsByName(id)[0].checked = false;
				break;
			default:
				document.getElementsByName(id)[0].value = value;
				break;
		}
	}
}
*/

function dataeditor_aftersavesuccess(popup_id, xmlHttp)
{
	//alert(popup_id);
	window['dg_dg_de_' + popup_id + '_html_panel'].loadContent('/ajax.php?sessid=' + session_id + '&dg_id=dg_de_' + popup_id + '&action=refresh');
	//voor inline edit refresh
	if($("#main_inline_edit_div").get(0) != undefined)
		inline_edit_after_save();
}

function dataeditor_show_input_dialog(popup_id, titlestr, loadstr, formobj_id, formheight)
{
	//we checken of de form div al bestaat
	if(document.getElementById("de_dialog_" + popup_id) == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "de_dialog_" + popup_id);
		newDiv.style.display = 'none';
		var newDivForm = document.createElement("div");
		newDivForm.setAttribute("id", "de_dialog_" + popup_id + "_form");
		newDivForm.setAttribute("autopost", "no");
		newDiv.appendChild(newDivForm);
		document.body.appendChild(newDiv);
		$('#de_dialog_' + popup_id).dialog({
			autoOpen: false,
			height: formheight,
			width: 800,
			resizable: true,
			show: 'fade',
			title: 'Add or edit a new data entry',
			modal: true,
			maxHeight: 600,
			buttons: {
				"Save": function() { 
					//$(this).parent().find(".ui-button-text:contains('Save')").text("Saving");
					if($(this).parent().find(".ui-dialog-buttonset > button").length > 0)
						window[formobj_id].savebutton = $(this).parent().find(".ui-dialog-buttonset > button").first();
					window[formobj_id].indialog = $('#de_dialog_' + popup_id);
					window[formobj_id].aftersave_success = "dataeditor_aftersavesuccess";
					window[formobj_id].aftersave_data = popup_id;
					window[formobj_id].post();
					//ajax_post_form('de_dialog_' + popup_id + '_form', '/ajax.php?sessid=' + session_id + '&popup_id=' + popup_id + '&showform=save', dataeditor_ajaxreturn_save);
				}, 
				"Cancel": function() { 
					$(this).parent().find(".ui-button-text:contains('NOT Saved')").text("Save");
					$(this).dialog("close"); 
				} 
			},
			close: function(event, ui) {
				cms2_remove_mce($(this).attr("id"));
				$(this).parent().find(".ui-button-text:contains('NOT Saved')").text("Save");
			}
		});
	}
	//Nu laden van de data en tonen van de dialog
	if(titlestr != "")
		$('#de_dialog_' + popup_id).dialog( "option", "title", titlestr );
	$("#de_dialog_" + popup_id + "_form").html('<div style="height:70px;padding-top:30px;text-align:center"><img src="/css/back/loader.gif"></div>'); 
	$('#de_dialog_' + popup_id).dialog('open');
	$("#de_dialog_" + popup_id + "_form").load(loadstr);
}

function dataeditor_addbypic(editor_id, picpaths)
{
	send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&addbypic=1', "picpaths=" + encodeURI(picpaths), dataeditor_addbypic_return);
	$("#br_addselection").text("Adding selection");
	$("#br_addselection").css("background-color", "#5F7A72");
}

function dataeditor_addbypic_return(xmlHttp)
{
	//alert(xmlHttp.responseText);
	var doc = $(xmlHttp.responseXML);
	window.opener['dg_' + doc.find("dg_id").text() + '_html_panel'].loadContent('/ajax.php?sessid=' + session_id + '&dg_id=' + doc.find("dg_id").text() + '&action=refresh');
	$("#br_addselection").text("Selection Added");
	$("#br_addselection").css("background-color", "#66CC66");
	setTimeout("$('#br_addselection').text('Add Selection'); $('#br_addselection').css('background-color', '#4D6F8C');", 3000);
}