
//if we press the save button we try to save the row
function dbeditor_sendfield(editor_id, savebutton)
{
	//we get the tr which contains the savebutton
	var rowdiv = savebutton.parentNode;
	while(rowdiv.getAttribute("rowdiv") != "true")
		rowdiv = rowdiv.parentNode;
		
	var strpost = '';
	//oldname
	var inputs = rowdiv.getElementsByTagName('input');
	for ( var i in inputs )
	{
		if(typeof inputs[i] != 'object' ) continue;
		if(strpost != '') strpost += '&';
		if(inputs[i].getAttribute("type") == "checkbox")
			strpost += inputs[i].getAttribute("name") + '=' + (inputs[i].checked?'1':'0');
		else
			strpost += inputs[i].getAttribute("name") + '=' + inputs[i].value;
	}
	var selects = rowdiv.getElementsByTagName('select');
	for ( var i in selects )
	{
		if(typeof selects[i] != 'object' ) continue;
		if(strpost != '') strpost += '&';
		strpost += selects[i].getAttribute("name") + '=' + selects[i].value;
	}
	
	//alert(strpost);
	send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=savefield', encodeURI(strpost), dbeditor_sendfield_return);
}

//handles the return
function dbeditor_sendfield_return(xmlHttp)
{
	//alert(xmlHttp.responseText);
	var domDoc = xmlHttp.responseXML;
	
	var grididNode = cms2_xpath(domDoc, "/gridid", false);
	var oldnameNode = cms2_xpath(domDoc, "/oldname", false);
	var statusNode = cms2_xpath(domDoc, "/status", false);
	var errorNodes = cms2_xpath(domDoc, "/errors/error", true);
	
	//we search first for the containing rowdiv
	var the_table = document.getElementById(grididNode.childNodes[0].nodeValue);
	var all_rowdivs = the_table.childNodes;
	//alert(all_rowdivs[0])
	var the_rowdiv = null;
	for(var i = 0 ; i < all_rowdivs.length ; i++)
	{
		//first td first input
		if(all_rowdivs[i].nodeName != "DIV")
			continue;
		if(all_rowdivs[i].getAttribute("rowdiv") == "true")
		{
			var tmp_input = all_rowdivs[i].getElementsByTagName("div")[0].getElementsByTagName("input")[0];
			//alert(tmp_input);
			if(tmp_input !== null && tmp_input != undefined)
			{
				if(tmp_input.getAttribute("name") == "oldname" 
					&& tmp_input.value == ((oldnameNode.childNodes[0] != undefined)?oldnameNode.childNodes[0].nodeValue : ''))
				{
					the_rowdiv = all_rowdivs[i];
				}
			}
		}
	}
	
	if(statusNode.childNodes[0].nodeValue=="OK")
	{
		//we set the oldname to the new one and blank all fields
		var input_oldname = null;
		var input_fieldname = null;
		var inputs = the_rowdiv.getElementsByTagName('input');
		for ( var i in inputs )
		{
			if(typeof inputs[i] != 'object' ) continue;
			if(inputs[i].getAttribute("name") == "oldname") input_oldname = inputs[i];
			if(inputs[i].getAttribute("name") == "fieldname") input_fieldname = inputs[i];
			effects_highlight(inputs[i], 1000, '#66CC66', '#FFFFFF');
		}
		var selects = the_rowdiv.getElementsByTagName('select');
		for ( var i in selects )
		{
			if(typeof selects[i] != 'object' ) continue;
			effects_highlight(selects[i], 1000, '#66CC66', '#FFFFFF');
		}
		if(input_oldname !== null && input_fieldname !== null)
			input_oldname.value = input_fieldname.value;
	}
	if(statusNode.childNodes[0].nodeValue=="NOK")
	{
		//we redlight all the inputs on whitch there is an error
		var inputs = the_rowdiv.getElementsByTagName('input');
		for ( var i in inputs )
		{
			if(typeof inputs[i] != 'object' ) continue;
			for(var j = 0 ; j < errorNodes.length ; j++)
			{
				if(errorNodes[j].childNodes[0].nodeValue == inputs[i].getAttribute("name"))
				{
					//inputs[i].style.backgroundColor = "red";
					effects_highlight(inputs[i], 1000, '#EA4848', false);
					break;
				}
			}
		}
		var selects = the_rowdiv.getElementsByTagName('select');
		for ( var i in selects )
		{
			if(typeof selects[i] != 'object' ) continue;
			for(var j = 0 ; j < errorNodes.length ; j++)
			{
				if(errorNodes[j].childNodes[0].nodeValue == selects[i].getAttribute("name"))
				{
					//selects[i].style.backgroundColor = "red";
					effects_highlight(selects[i], 1000, '#EA4848', false);
					break;
				}
			}
		}
	}
}

function dbeditor_sendplaceholder(editor_id, savebutton)
{
	var rowdiv = savebutton.parentNode;
	while(rowdiv.getAttribute("rowdiv") != "true")
		rowdiv = rowdiv.parentNode;
	var strpost = '';

	var inputs = rowdiv.getElementsByTagName('input');
	for ( var i in inputs )
	{
		if(typeof inputs[i] != 'object' ) continue;
		if(strpost != '') strpost += '&';
		if(inputs[i].getAttribute("type") == "checkbox")
			strpost += inputs[i].getAttribute("name") + '=' + (inputs[i].checked?'1':'0');
		else
			strpost += inputs[i].getAttribute("name") + '=' + inputs[i].value;
	}
	var selects = rowdiv.getElementsByTagName('select');
	for ( var i in selects )
	{
		if(typeof selects[i] != 'object' ) continue;
		if(strpost != '') strpost += '&';
		strpost += selects[i].getAttribute("name") + '=' + selects[i].value;
	}
	
	//alert(strpost);
	send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=saveplaceholder', encodeURI(strpost), dbeditor_sendplaceholder_return);
}

function dbeditor_sendplaceholder_return(xmlHttp)
{
	//alert(xmlHttp.responseText);
	var domDoc = $(xmlHttp.responseXML);
	//we search first for the containing rowdiv
	$('#' + domDoc.find('gridid').text()).find('input[name="id"][value="' + domDoc.find('id').text() + '"]').parent().parent().find("input").each(function() {
		effects_highlight(this, 1000, '#66CC66', '#FFFFFF');																										   
	});
	$('#' + domDoc.find('gridid').text()).find('input[name="id"][value="' + domDoc.find('id').text() + '"]').parent().parent().find("select").each(function() {
		effects_highlight(this, 1000, '#66CC66', '#FFFFFF');																										   
	});
}

//if we press the delete button on a row
function dbeditor_deletefield(editor_id, delbutton)
{
	if(confirm("Do you want to delete this field?"))
	{
		var rowdiv = delbutton.parentNode;
		while(rowdiv.getAttribute("rowdiv") != "true")
			rowdiv = rowdiv.parentNode;
			
		var strpost = '';
		//oldname
		var inputs = rowdiv.getElementsByTagName('input');
		for ( var i in inputs )
		{
			if(typeof inputs[i] != 'object' ) continue;
			if(inputs[i].getAttribute("name") == "oldname")
			{
				if(strpost != '') strpost += '&';
				strpost += 'oldname=' + inputs[i].value;
				break;
			}
		}
		
		send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=deletefield', encodeURI(strpost), dbeditor_deletefield_return);
	}
}

//if we press the delete button on a row
function dbeditor_deleteplaceholder(editor_id, delbutton)
{
	if(confirm("Do you want to delete this placeholder?"))
	{
		var rowdiv = delbutton.parentNode;
		while(rowdiv.getAttribute("rowdiv") != "true")
			rowdiv = rowdiv.parentNode;
			
		strpost = 'placeholder_id=' + $(rowdiv).find('input[name="id"]').val();
		
		send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=deleteplaceholder', encodeURI(strpost), dbeditor_deleteplaceholder_return);
	}
}

function dbeditor_deletefield_return(xmlHttp)
{
	if(xmlHttp.responseText == 'NOK')
	{
		alert('The field could not be found and is not deleted');
	}
	else
	{
		//we zoeken de rowdiv
		var chomps = xmlHttp.responseText.split("##splitter##");
		var the_table = document.getElementById(chomps[0]);
		var all_rowdivs = the_table.childNodes;
		//alert(all_rowdivs[0])
		var the_rowdiv = null;
		for(var i = 0 ; i < all_rowdivs.length ; i++)
		{
			//first td first input
			if(all_rowdivs[i].nodeName != "DIV")
				continue;
			if(all_rowdivs[i].getAttribute("rowdiv") == "true")
			{
				var tmp_input = all_rowdivs[i].getElementsByTagName("div")[0].getElementsByTagName("input")[0];
				//alert(tmp_input);
				if(tmp_input !== null && tmp_input != undefined)
				{
					if(tmp_input.getAttribute("name") == "oldname" && tmp_input.value == chomps[1])
					{
						the_rowdiv = all_rowdivs[i];
					}
				}
			}
		}
		
		effects_fade(the_rowdiv, 1000);
		setTimeout("dbeditor_deletefield_rowdeleteafterhiding('" + chomps[0] + "', '" + chomps[1] + "')", 1050);
	}
}

function dbeditor_deleteplaceholder_return(xmlHttp)
{
		//we zoeken de rowdiv
		var chomps = xmlHttp.responseText.split("##splitter##");
		$('#' + chomps[0]).find('input[name="id"][value="' + chomps[1] + '"]').parent().parent().remove();
}


function dbeditor_deletefield_rowdeleteafterhiding(tableid, oldname)
{
	var the_table = document.getElementById(tableid);
	var all_rowdivs = the_table.childNodes;
	//alert(all_rowdivs[0])
	var the_rowdiv = null;
	for(var i = 0 ; i < all_rowdivs.length ; i++)
	{
		//first td first input
		if(all_rowdivs[i].nodeName != "DIV")
			continue;
		if(all_rowdivs[i].getAttribute("rowdiv") == "true")
		{
			var tmp_input = all_rowdivs[i].getElementsByTagName("div")[0].getElementsByTagName("input")[0];
			//alert(tmp_input);
			if(tmp_input !== null && tmp_input != undefined)
			{
				if(tmp_input.getAttribute("name") == "oldname" && tmp_input.value == oldname)
				{
					the_rowdiv = all_rowdivs[i];
				}
			}
		}
	}
	
	the_table.removeChild(the_rowdiv);
}

//if we press on options textbox
function dbeditor_open_options(editor_id, field, htmlpan, dialogdiv_id)
{
	//we zoeken naar de rowdiv
	var rowdiv = field.parentNode;
	while(rowdiv.getAttribute("rowdiv") != "true")
		rowdiv = rowdiv.parentNode;
		
	var desc_id = '';
	//oldname
	var selects = rowdiv.getElementsByTagName('select');
	for ( var i in selects )
	{
		if(typeof selects[i] != 'object' ) continue;
		if(selects[i].getAttribute("name") == "datadesc")
		{
			desc_id = selects[i].value;
			break;
		}
	}
	
	document.getElementById(dialogdiv_id).innerHTML = '<div style="height:70px; padding-top:30px; text-align:center"><img src="/css/back/loader3.gif"></div>';
	$('#' + dialogdiv_id).dialog('open');
	htmlpan.loadContent('/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=optionform&desc_id=' + desc_id + '&options_str=' + field.value);
	document.getElementById(dialogdiv_id).dbeditor_current_optionfield = field;
}

function dbeditor_options_ok(divid)
{
	var the_div = document.getElementById(divid);
	var inputs = the_div.getElementsByTagName('input');
	
	var the_str = "";
	for(var i in inputs)
	{
		if(typeof inputs[i] != 'object' ) continue;
		var value = "";
		if(inputs[i].getAttribute("type") == "checkbox")
			value = (inputs[i].checked)? '1':'0';
		else
			value = inputs[i].value;
		
		if(i != 0)
			the_str += "|";
		the_str += value;
	}
	
	the_div.dbeditor_current_optionfield.value = the_str;
}

//als het datatype veranderd dan moet het optieveld geleegd worden
function dbeditor_typechange(the_select)
{
	//we zoeken naar de rowdiv
	var rowdiv = the_select.parentNode;
	while(rowdiv.getAttribute("rowdiv") != "true")
		rowdiv = rowdiv.parentNode;
		
	//oldname
	var inputs = rowdiv.getElementsByTagName('input');
	for ( var i in inputs )
	{
		if(typeof inputs[i] != 'object' ) continue;
		if(inputs[i].getAttribute("name") == "data_options")
		{
			inputs[i].value = '';
			break;
		}
	}
}

//if we press the add row button
function dbeditor_addemptyrow(editor_id)
{
	//getting the html
	send_ajax_request("GET", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=emptyrowcode', "", dbeditor_addemptyrow_return);
}

function dbeditor_addemptyrow_return(xmlHttp)
{
	var chomps = xmlHttp.responseText.split("##splitter##");
	var the_table = document.getElementById(chomps[0]);
	//we add a rowdiv
	var rowdiv = document.createElement('div');
	rowdiv.setAttribute("rowdiv", "true");
	rowdiv.setAttribute("id", chomps[1]);
	rowdiv.innerHTML = chomps[2];
	rowdiv.style.opacity = '0';
    rowdiv.style.filter = 'alpha(opacity = 0)';
	the_table.appendChild(rowdiv);
	rowdiv.style.display = "block";
	effects_fade(rowdiv, 1000);
}

//if we press the add row button
function dbeditor_addplaceholder(editor_id)
{
	//getting the html
	send_ajax_request("GET", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=newplaceholder', "", dbeditor_addplaceholder_return);
}

function dbeditor_addplaceholder_return(xmlHttp)
{
	var doc = $(xmlHttp.responseXML);
	var the_table = document.getElementById(doc.find("table").text());
	$(the_table).append(doc.find("html").text());
}

function dbeditor_sortfields(editor_id)
{
	var ids = "";
	$('#dbedit_grid_' + editor_id).children('div[rowdiv="true"]').each(function (){
		if($(this).attr("type") == "placeholder")
		{
			if(ids != "") ids += ";";
				ids += 'p:' + $(this).find('input[name="id"]').val();
		}
		else
		{
			if(ids != "") ids += ";";
				ids += 'f:' + $(this).find('input[name="oldname"]').val();
		}
	});
	var counter = 1;
	$('#dbedit_grid_' + editor_id).children('div[rowdiv="true"]').each(function (){
		$(this).find('input[name="order"]').val(counter);
		counter++;
	});
	/*
	$('#dbedit_grid_' + editor_id + ' > div > div > input[name="oldname"]').each(function(){
		if(ids != "") ids += ";";
		ids += $(this).val();
	});
	var counter = 1;
	$('#dbedit_grid_' + editor_id + ' > div > div > input[name="order"]').each(function(){
		$(this).val(counter);
		counter++;
	});
	*/
	send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&popup_id=' + editor_id + '&action=order', "fieldnames=" + encodeURI(ids), null);
}

function dbeditor_aftertablesave(table_name, xmlHttp)
{
	send_ajax_request("GET", '/ajax.php?sessid=' + session_id + '&page=datadesc&action=tablemeta&table=' + table_name, "", null);
}