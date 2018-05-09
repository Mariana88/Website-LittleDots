var cms2_last_error_eles = new Array();
var cms2_fck_editors_load = new Array();
var cms2_fck_editors_load_values = new Array();

//functie om elke dubbele waarde uit een array te halen
Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j, 1);
        }
    }

    return a;
};

//muis coordinaten functies------------------------------------------------------------------------

function getAbsoluteMousePosition(element) {
    var r = { x: element.offsetLeft, y: element.offsetTop };
    if (element.offsetParent) {
      var tmp = getAbsoluteMousePosition(element.offsetParent);
      r.x += tmp.x;
      r.y += tmp.y;
    }
    return r;
  };
  
function getRelativeMouseCoordinates(event, reference) {
	event = event || window.event;
	var offset = $(reference).offset();
	clickX = event.pageX - offset.left;
	clickY = event.pageY - offset.top;
	//alert(offset.top);
	return { 'x': clickX, 'y': clickY };
  }

function cms2_xpath(domDoc, xpath, multiple)
{
	var chomps = xpath.split("/");
	var the_return = null;
	var level = 0;
	var parentnode = domDoc.documentElement;
	var arrcount = 0;
	var levelfound = false;
	
	while(the_return == null)
	{
		levelfound = false;
		for(var i = 0 ; i<parentnode.childNodes.length ; i++)
		{
			if(chomps[level+1] == parentnode.childNodes[i].tagName)
			{
				if(chomps.length == level+2)
				{
					//we found the node
					if(multiple)
					{
						if(the_return == null) the_return = new Array();
						the_return[arrcount] = parentnode.childNodes[i];
						arrcount++;
					}
					else
					{
						the_return = parentnode.childNodes[i];
						break;
					}
				}
				else
				{
					parentnode = parentnode.childNodes[i];
					level++;
					levelfound = true;
					break;
				}
			}
		}
		if(!levelfound)
			break;
	}
	return the_return;
}

function cms2_show_loader(divid)
{
	$('#' + divid).html('<div style="height:70px;padding-top:30px;text-align:center"><img src="/css/back/loader.gif"></div>'); 
}

function cms2_open_file(path, extention, popupdiv)
{
	if(popupdiv == null)
	{
		//we creëren zelf een divke (checken of het al bestaat!)
		if(document.getElementById("cms2_open_file_picdialog") == undefined)
		{
			var newDiv = document.createElement("div");
			newDiv.setAttribute("id", "cms2_open_file_picdialog");
			newDiv.style.display = 'none';
			document.body.appendChild(newDiv);
			$('#cms2_open_file_picdialog').dialog({
						autoOpen: false,
						height: 700,
						width: 700,
						show: 'fade',
						title: 'Picture',
						modal: true,
						maxHeight: 800,
						buttons: {
							"Ok": function() { 
									$(this).dialog("close"); 
								}
							}
						});
		}
		
		popupdiv = "cms2_open_file_picdialog";
	}
	switch (extention)
	{
		case "gif":
		case "jpeg":
		case "png":
		case "jpg":
			$('#' + popupdiv).dialog( "option", "title", "Picture: " + path);
			$('#' + popupdiv).html('<img src="/' + path + '?x=' + Math.round(new Date().getTime() / 1000) + '"/>');
			$('#' + popupdiv).dialog('open');
			break;
		default: 
			window.open('/bestand.php?path=' + encodeURI(path),'','scrollbars=no,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no');
			break;
	}
}

function cms2_open_pic_edit(path_original, path_dest, dest_x, dest_y)
{
	if(document.getElementById("cms2_open_picedit_dialog") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_open_picedit_dialog");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$('#cms2_open_picedit_dialog').dialog({
					autoOpen: false,
					height: 700,
					width: 700,
					show: 'fade',
					title: 'Picture',
					modal: true,
					maxHeight: 800,
					buttons: {
						"Save": function() { 
								//alert ('x:' + $('#cms2_open_picedit_src').attr("crop_x") + 'y:' + $('#cms2_open_picedit_src').attr("crop_y") + 'w:' + $('#cms2_open_picedit_src').attr("crop_w") + 'h:' + $('#cms2_open_picedit_src').attr("crop_h"));
								//alert('/ajax.php?sessid=' + session_id + '&piccropper=1&action=save&path_original=' + encodeURI(path_original) + '&path_dest=' + encodeURI(path_dest) + '&dest_x=' + dest_x + '&dest_y=' + dest_y + '&crop_x=' + $('#cms2_open_picedit_src').attr("crop_x") + '&crop_y=' + $('#cms2_open_picedit_src').attr("crop_y") + '&crop_w=' + $('#cms2_open_picedit_src').attr("crop_w") + '&crop_h=' + $('#cms2_open_picedit_src').attr("crop_h"));
								send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&piccropper=1&action=save&path_original=' + encodeURI(path_original) + '&path_dest=' + encodeURI(path_dest) + '&dest_x=' + dest_x + '&dest_y=' + dest_y + '&crop_x=' + $('#cms2_open_picedit_src').attr("crop_x") + '&crop_y=' + $('#cms2_open_picedit_src').attr("crop_y") + '&crop_w=' + $('#cms2_open_picedit_src').attr("crop_w") + '&crop_h=' + $('#cms2_open_picedit_src').attr("crop_h"), '', null);
								$(this).dialog("close"); 
							},
						"Cancel": function() { 
								$(this).dialog("close"); 
							}
						}
					});
	}
	cms2_show_loader("cms2_open_picedit_dialog");
	popupdiv = $("#cms2_open_picedit_dialog");
	popupdiv.dialog( "option", "title", "Picture crop: " + path_dest);
	popupdiv.dialog('open');
	popupdiv.load('/ajax.php?sessid=' + session_id + '&piccropper=1&action=load&path_original=' + encodeURI(path_original) + '&path_dest=' + encodeURI(path_dest) + '&dest_x=' + dest_x + '&dest_y=' + dest_y)
}

function cms2_open_pic_edit_new(format_id, img_id_after_update)
{
	if(document.getElementById("cms2_open_picedit_dialog") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_open_picedit_dialog");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$('#cms2_open_picedit_dialog').dialog({
					autoOpen: false,
					width:'auto',
					resizable: false,
					show: 'fade',
					title: 'Picture crop',
					modal: true,
					maxHeight: 800
					});
	}
	cms2_show_loader("cms2_open_picedit_dialog");
	popupdiv = $("#cms2_open_picedit_dialog");
	popupdiv.dialog( "option", "title", "Picture crop");
	$( "#cms2_open_picedit_dialog" ).dialog( "option", "buttons", [
		{
			text: "Save",
			click: function() { 
								
								if(img_id_after_update)
								{
									send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&piccropper=1&action=save&format_id=' + format_id + '&crop_x=' + $('#cms2_open_picedit_src').attr("crop_x") + '&crop_y=' + $('#cms2_open_picedit_src').attr("crop_y") + '&crop_w=' + $('#cms2_open_picedit_src').attr("crop_w") + '&crop_h=' + $('#cms2_open_picedit_src').attr("crop_h") + '&img_id_after_update=' + img_id_after_update, '', cms2_open_pic_edit_new_aftersave);
								}
								else
									$.ajax({
										method: 'GET',
										url: '/ajax.php?sessid=' + session_id + '&piccropper=1&action=save&format_id=' + format_id + '&crop_x=' + $('#cms2_open_picedit_src').attr("crop_x") + '&crop_y=' + $('#cms2_open_picedit_src').attr("crop_y") + '&crop_w=' + $('#cms2_open_picedit_src').attr("crop_w") + '&crop_h=' + $('#cms2_open_picedit_src').attr("crop_h"),
										complete: function(jqXHR, textStatus )
											{
												//alert(jqXHR.responseText);	
											}
									})
									/*send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&piccropper=1&action=save&format_id=' + format_id + '&crop_x=' + $('#cms2_open_picedit_src').attr("crop_x") + '&crop_y=' + $('#cms2_open_picedit_src').attr("crop_y") + '&crop_w=' + $('#cms2_open_picedit_src').attr("crop_w") + '&crop_h=' + $('#cms2_open_picedit_src').attr("crop_h"), '', null);*/
								$(this).dialog("close"); 
									
							}
		},
		{
			text: "Cancel",
			click: function() { $(this).dialog("close"); }
		}
	] );
	
	popupdiv.load('/ajax.php?sessid=' + session_id + '&piccropper=1&action=load&format_id=' + format_id, function(){$(this).dialog('open');})
}

function cms2_open_pic_edit_new_aftersave(xmlHttp)
{
	cms2_reload_image(xmlHttp.responseText, $("#" + xmlHttp.responseText).attr('src'));
}

function cms2_open_file_options(path)
{
	if(document.getElementById("cms2_open_file_options_dialog") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_open_file_options_dialog");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$('#cms2_open_file_options_dialog').dialog({
					autoOpen: false,
					height: 700,
					width: 760,
					show: 'fade',
					title: 'File Info',
					modal: true,
					maxHeight: 800,
					buttons: {
						"Save": function() { 
								ajax_post_form('file_info_form', '/ajax.php?sessid=' + session_id + '&fileoptions=1&action=save', null, false);
								$(this).dialog("close"); 
							},
						"Cancel": function() { 
								$(this).dialog("close"); 
							}
						}
					});
	}
	cms2_show_loader("cms2_open_file_options_dialog");
	popupdiv = $("#cms2_open_file_options_dialog");
	popupdiv.dialog( "option", "title", "File Options");
	popupdiv.dialog('open');
	popupdiv.load('/ajax.php?sessid=' + session_id + '&fileoptions=1&action=load&file_path=' + encodeURI(path));
}

function cms2_test(xmlHttp)
{
	alert(xmlHttp.responseText);
}

function cms2_formfield_timechange(postname)
{
	var hours = document.getElementById(postname + '_time_hours');
	var minutes = document.getElementById(postname + '_time_minutes');
	var seconds = document.getElementById(postname + '_time_seconds');
	var timestr = "";
	if(hours != undefined && hours !== null) timestr += hours.value;
	if(minutes != undefined && minutes !== null) timestr += (timestr.length>0?':':'') + minutes.value;
	if(seconds != undefined && seconds !== null) timestr += (timestr.length>0?':':'') + seconds.value;
	document.getElementById(postname).value = timestr;
}

function cms2_ajax_error(xmlHttp)
{
	var tmp = xmlHttp.responseText.split(":");
	if(tmp[0] == "NORIGHTS")
	{
		cms2_show_error_message(tmp[1], "Permission denied");
		return true;
	}
	else
		return false
}

function cms2_show_error_message(stext, stitle)
{
	if(document.getElementById("cms2_message_div") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_message_div");
		newDiv.style.display = 'none';
		newDiv.style.fontSize = '13px';
		newDiv.style.textAlign = 'center';
		newDiv.style.lineHeight = '20px';
		document.body.appendChild(newDiv);
		$('#cms2_message_div').dialog({
					autoOpen: false,
					height: 170,
					width: 300,
					show: 'fade',
					modal: true
					});
	}
	$( "#cms2_message_div" ).text(stext);
	$( "#cms2_message_div" ).dialog( "option", "title", stitle );
	$( "#cms2_message_div" ).dialog( "option", "buttons", [
		{
			text: "Close",
			click: function() { $(this).dialog("close"); }
		}
	] );
	$( "#cms2_message_div" ).dialog('open');
	/*document.getElementById("message_error_content").innerHTML = stext;
	tb_show(null, "#TB_inline?height=70&width=400&inlineId=message_error&modal=true", false)*/
}

function cms2_show_question_message(stext, stitle, fyes, fno)
{
	if(document.getElementById("cms2_message_div") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_message_div");
		newDiv.style.display = 'none';
		newDiv.style.fontSize = '13px';
		newDiv.style.textAlign = 'center';
		newDiv.style.lineHeight = '20px';
		document.body.appendChild(newDiv);
		$('#cms2_message_div').dialog({
					autoOpen: false,
					height: 170,
					width: 300,
					show: 'fade',
					modal: true
					});
	}
	$( "#cms2_message_div" ).text(stext);
	$( "#cms2_message_div" ).dialog( "option", "title", stitle );
	$( "#cms2_message_div" ).dialog( "option", "buttons", [
		{
			text: "Yes",
			click: fyes 
		},
		{
			text: "No",
			click: fno 
		}
	] );
	$( "#cms2_message_div" ).dialog('open');
}

function cms2_show_right_form(stitle, rightrule, data_id)
{
	if(document.getElementById("cms2_right_form_div") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "cms2_right_form_div");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$('#cms2_right_form_div').dialog({
					autoOpen: false,
					height: 130,
					width: 200,
					show: 'fade',
					modal: true
					});
	}
	$( "#cms2_right_form_div" ).load('/ajax.php?sessid' + session_id + '&rightform=1&action=publish&r_name=' + rightrule  + '&r_data_id=' + data_id);
	$( "#cms2_right_form_div" ).dialog( "option", "title", stitle );
	$( "#cms2_right_form_div" ).dialog('open');
}

function cms2_string_to_xml(the_string)
{
	var browserName = navigator.appName;
	var doc;
	if (browserName == 'Microsoft Internet Explorer')
	{
		doc = new ActiveXObject('Microsoft.XMLDOM');
		doc.async = 'false'
		doc.loadXML(the_string);
	} else {
		doc = (new DOMParser()).parseFromString(the_string, 'text/xml');
	}
	return doc;
}

function cms2_xml_to_string(xmlNode)
{
  try {
    // Gecko-based browsers, Safari, Opera.
    return (new XMLSerializer()).serializeToString(xmlNode);
  }
  catch (e) {
    try {
      // Internet Explorer.
      return xmlNode.xml;
    }
    catch (e)
    {//Strange Browser ??
     alert('Xmlserializer not supported');
    }
  }
  return false;
}

function cms_wordlist_add(fieldid, sep)
{
	var value = $("#" + fieldid + "_insert").val();
	var valuein = false;
	$("#" + fieldid + "_wordlist").children("div").children("div").each(function(){
		if(value.toLowerCase() == $(this).text().toLowerCase())
		{
			valuein = true;
			effects_highlight($(this).parent().get(0), 1500, "#66CC66", true);
			effects_highlight($(this).get(0), 1500, "#66CC66", true);
		}
	});
	if(value != "" && !valuein)
	{
		$("#" + fieldid + "_wordlist").append('<div><div>' + value + '</div><img src="/css/back/label-cross.gif" onclick="cms2_wordlist_remove($(this).parent().get(0), \'' + fieldid + '\', \'' + sep + '\');"/></div>');
	}
	var the_str = "";
	$("#" + fieldid + "_wordlist").children("div").each(function(){
		if(the_str != "") the_str += sep;
		the_str += $(this).text()
	});
	$("#" + fieldid).val(the_str);
	//$(".wordlist").children("div").corner("7px");
}

function cms_wordlist_fill(fieldid, values)
{
	sep = $("#" + fieldid + "_wordlist").attr("seperation");
	$("#" + fieldid + "_wordlist").children().remove();
	items = values.split(sep);
	for(var i = 0 ; i < items.length ; i++)
	{
		if(items[i] != "")
			$("#" + fieldid + "_wordlist").append('<div><div>' + items[i] + '</div><img src="/css/back/label-cross.gif" onclick="cms2_wordlist_remove($(this).parent().get(0), \'' + fieldid + '\', \'' + sep + '\');"/></div>');
	}
	//$(".wordlist").children("div").corner("7px");
}

function cms2_wordlist_remove(the_div, fieldid, sep)
{
	$(the_div).remove();
	
	var the_str = "";
	$("#" + fieldid + "_wordlist").children("div").children("div").each(function(){
		if(the_str != "") the_str += sep;
		the_str += $(this).text()
	});
	$("#" + fieldid).val(the_str);
}

function cms_worddatalist_add(fieldid)
{
	var value = $("#" + fieldid + "_insert").val();
	var text = $("#" + fieldid + "_insert").find('option[value="' + value + '"]').text();
	var valuein = false;
	var chomps = $("#" + fieldid).val().split(";");
	for(var i = 0 ; i < chomps.length ; i++)
	{
		if(value == chomps[i])
		{
			valuein = true;
			tmp_div = $("#" + fieldid + "_worddatalist").children('div[dataid="' + value + '"]');
			//alert(tmp_div.length);
			effects_highlight(tmp_div.children("div").get(0), 1500, "#66CC66", true);
			effects_highlight(tmp_div.get(0), 1500, "#66CC66", true);
		}
	}
	if(value != "" && !valuein)
	{
		$("#" + fieldid + "_worddatalist").append('<div dataid="' + value + '"><div>' + text + '</div><img src="/css/back/label-cross.gif" onclick="cms2_worddatalist_remove($(this).parent().get(0), \'' + fieldid + '\');"/></div>');
	}
	var the_str = "";
	$("#" + fieldid + "_worddatalist").children("div").each(function(){
		if(the_str != "") the_str += ";";
		the_str += $(this).attr("dataid");
	});
	$("#" + fieldid).val(the_str);
	//$(".wordlist").children("div").corner("7px");
}

function cms_worddatalist_fill(fieldid, values, lang)
{
	//alert(lang);
	sep = ";";
	//alert($("#" + fieldid + "_worddatalist_lang_" + lang).get(0).innerHTML);
	$("#" + fieldid + "_insert").children("option").remove()
	$("#" + fieldid + "_worddatalist_lang_" + lang).children("div").each(function(){
		//var newoption = document.createElement("option");
		//newoption.setAttribute("value", $(this).attr("value"));
		//$("#" + fieldid + "_insert").get(0).appendChild(newoption);
		//$(newoption).text($(this).text());
		//alert($(this).attr("value"));
		$("#" + fieldid + "_insert").append('<option value="' + $(this).attr("value") + '">' + $(this).attr("caption") + '</option>');
	});
	$("#" + fieldid + "_worddatalist").children().remove();
	items = values.split(sep);
	for(var i = 0 ; i < items.length ; i++)
	{
		var text = $("#" + fieldid + "_insert").find('option[value="' + items[i] + '"]').text();
		if(items[i] != "")
			$("#" + fieldid + "_worddatalist").append('<div dataid="' + items[i] + '"><div>' + text + '</div><img src="/css/back/label-cross.gif" onclick="cms2_worddatalist_remove($(this).parent().get(0), \'' + fieldid + '\');"/></div>');
	}
	//$(".wordlist").children("div").corner("7px");
}

function cms2_worddatalist_remove(the_div, fieldid)
{
	$(the_div).remove();
	
	var the_str = "";
	var the_str = "";
	$("#" + fieldid + "_worddatalist").children("div").each(function(){
		if(the_str != "") the_str += ";";
		the_str += $(this).attr("dataid");
	});
	$("#" + fieldid).val(the_str);
}

function cms2_reload_image(img_id, new_src) 
{
	img_id = $("#" + img_id);
	if (img_id) {
		old_src = img_id.attr('src');
		// No change in source we'll have to add random data to the url to refresh the image
		if (new_src == '' || old_src == new_src) {
			if (old_src.indexOf('?') == -1) { 
				old_src += '?';
			} else {
				old_src += '&';
			}
			old_src += '__rnd=' + Math.random();
			img_id.attr('src', old_src);
		} else {
			img_id.attr('src', new_src);
		}
	}
}

function cms2_remove_mce(element)
{
	$("#" + element).find("textarea").each(function(){
		if($(this).attr("htmleditor") == 1)
		{
			//tinyMCE.execCommand('mceFocus', false, $(this).attr("id"));                    
			tinyMCE.execCommand('mceRemoveEditor', false, $(this).attr("id"));
		}   
	});
}

function xmlToString(xmlData) { 

    var xmlString;
    //IE
    if (window.ActiveXObject){
        xmlString = xmlData.xml;
    }
    // code for Mozilla, Firefox, Opera, etc.
    else{
        xmlString = (new XMLSerializer()).serializeToString(xmlData);
    }
    return xmlString;
} 