function maillist_delete(popup_id)
{
	//zoeken naar alle checkboxes
	if($("#maillist_" + popup_id).find('input[type="checkbox"]:checked').length > 0)
	{
		var ids = "";
		$("#maillist_" + popup_id).find('input[type="checkbox"]:checked').each(function(){
			if(ids != "")
				ids += ";";
			ids += $(this).attr("mailid");
		});
		if(confirm('Do you really want to delete thes emails?'))
		{
			send_ajax_request("GET", "/ajax.php?sessid=" + session_id + "&popup_id=" + popup_id + "&action=delete&ids=" + ids, '', maillist_refresh);
		}
	}
}

function maillist_refresh(xmlHttp)
{
	$("#maillist_" + xmlHttp.responseText).load("/ajax.php?sessid=" + session_id + "&popup_id=" + xmlHttp.responseText + "&action=refresh");
}

function maillist_unread(popup_id)
{
	//zoeken naar alle checkboxes
	if($("#maillist_" + popup_id).find('input[type="checkbox"]:checked').length > 0)
	{
		var ids = "";
		$("#maillist_" + popup_id).find('input[type="checkbox"]:checked').each(function(){
			if(ids != "")
				ids += ";";
			ids += $(this).attr("mailid");
			$(this).parent().parent().addClass("unred");
		});
		
		send_ajax_request("GET", "/ajax.php?sessid=" + session_id + "&popup_id=" + popup_id + "&action=unread&ids=" + ids, '', null);
	}
}

function maillist_compose(popup_id, url_extra)
{
	window.open('/manmail.php?to=' + url_extra,'','width=1220,height=800,scrollbars=yes,toolbar=no,location=no,resizable=yes,status=no');
}

function maillist_reply(mail_id)
{
	window.open('/manmail.php?reply=' + mail_id,'','width=1220,height=800,scrollbars=yes,toolbar=no,location=no,resizable=yes,status=no');
}

function maillist_replyall(mail_id)
{
	window.open('/manmail.php?replyall=' + mail_id,'','width=1220,height=800,scrollbars=yes,toolbar=no,location=no,resizable=yes,status=no');
}

function maillist_forward(mail_id)
{
	window.open('/manmail.php?forward=' + mail_id,'','width=1220,height=800,scrollbars=yes,toolbar=no,location=no,resizable=yes,status=no');
}

function maillist_addcall(popup_id, parent_id)
{
	if(document.getElementById("cms2_maillist_call") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_maillist_call");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$('#cms2_maillist_call').dialog({
					autoOpen: false,
					height: 700,
					width: 700,
					show: 'fade',
					title: 'Picture',
					modal: true,
					maxHeight: 800
					});
	}
	cms2_show_loader("cms2_maillist_call");
	popupdiv = $("#cms2_maillist_call");
	popupdiv.dialog( "option", "title", "Add or Edit Call");
	$( "#cms2_maillist_call" ).dialog( "option", "buttons", [
		{
			text: "Save",
			click: function() { 
								send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&popup_id=' + popup_id + '&action=callsave', '', null);
								$(this).dialog("close"); 
									
							}
		},
		{
			text: "Cancel",
			click: function() { $(this).dialog("close"); }
		}
	] );
	popupdiv.dialog('open');
	popupdiv.load('/ajax.php?sessid=' + session_id + '&popup_id=' + popup_id + '&action=call');
}

function composemail_addattachment()
{	
	$("#attachments_select").append('<div><input id="att_' + ($("#attachments_select").find("input").length + 1) + '" name="att_' + ($("#attachments_select").find("input").length + 1) + '" type="checkbox" value="' + $("#inputaddattachment").val() + '" checked="checked">&nbsp;' + $("#inputaddattachment").val() + '</div>');
}

function composemail_fill_replycheck()
{	
	$("#replycheck_email").children().remove();
	var emails = $("#to").val().split(",");
	for(var i = 0 ; i < emails.length ; i++)
	{
		email = emails[i].replace(/^\s+/,'');
  		email = email.replace(/\s+$/,'');
		if(email != "")
		{
			$("#replycheck_email").append('<option value="' + email + '">' + email + '</option>');
		}
	}
}

function composemail_loadtemplate(xmlHttp)
{
	var doc = $(xmlHttp.responseXML);
	
	tinyMCE.get("mailhtml").setContent(doc.find("text").text());
	$("#subject").val(doc.find("subject").text());
}

function composemail_savetemplate()
{
	if($("#savetemplatename").val() == "")
	{
		alert("Fill in a name to save");
	}
	else
	{
		var postvars = "name=" + encodeURI($("#savetemplatename").val()) + "&subject=" + encodeURI($("#subject").val()) + "&text=";
		tinyMCE.triggerSave();
		var thehtml = $("#mailhtml").val();
		thehtml = thehtml.replace(/&/g, "___AMP___");
		thehtml = thehtml.replace(/\?/g, "___QUEST___");
		thehtml = thehtml.replace(/#/g, "___HEK___");
		thehtml = thehtml.replace(/\+/g, "___PLUS___");
		thehtml = thehtml.replace(/€/g, "___EUR___");
		
		postvars += encodeURI(thehtml);
		
		send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&page=management&action=savetemplate', postvars, composemail_aftersavetemplate);
	}
}

function composemail_aftersavetemplate(xmlHttp)
{
	//alert(xmlHttp.responseText);
}