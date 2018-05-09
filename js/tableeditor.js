function tableeditor(theid, postid) {   
    
	this.id = theid;  
	this.div = document.getElementById("table_editor_" + theid);
	this.table = null;
	this.fieldinput = document.getElementById("table_editor_" + theid + "_fieldinput");
	this.fieldheight = document.getElementById("table_editor_" + theid + "_fieldheight");
	this.fieldwidth = document.getElementById("table_editor_" + theid + "_fieldwidth");
	this.fieldpost = document.getElementById(postid);
	this.fieldstyle = document.getElementById("table_editor_" + theid + "_fieldstyle");
	this.img_removerow = document.getElementById("table_editor_" + theid + "_removerow");
	this.img_removecolumn = document.getElementById("table_editor_" + theid + "_removecolumn");
	this.selected_td = null;
	this.last_selected_color = null;
	this.selection_start_td = null;
	this.selection_end_td = null;
	
	if(theid == 0)
		return;
		
	this.table = this.div.childNodes[0];
	this.write_to_post();
	//we get the td's and add some onclick functionality
	var td_elements = this.div.getElementsByTagName("td");
	for (var i=0; i<td_elements.length; i++)
	{
		this.add_cell_functionality(td_elements[i]);
	}
}

function tableeditor_write_to_post()
{
	// get the nodes TABLE and TBODY (you surely need to adjust that)
    var tbodyNode = this.table.firstChild;
    // clone the two nodes TABLE and TBODY into the variables named "cloned*"
    var clonedTableNode = this.table.cloneNode(false);
    var clonedTbodyNode = tbodyNode.cloneNode(false);
    // append the cloned TBODY to the cloned TABLE
    clonedTableNode.appendChild(clonedTbodyNode);
    // copy all the TRs and a cell for each, and finally append them to the TBODY
    var tabel_str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
	for (var i=0;i<this.table.rows.length;i++) 
	{
        tabel_str += "<tr>";
		var tds = this.table.rows[i].getElementsByTagName("td");
		for(var j=0 ; j < tds.length ; j++)
		{
			tabel_str += "<td";
			if(tds[j].className != "")
				tabel_str += ' class="' + tds[j].className + '"';
			if(tds[j].hasAttribute("width"))
				tabel_str += ' width="' + tds[j].getAttribute("width") + '"';
			if(tds[j].hasAttribute("height"))
				tabel_str += ' height="' + tds[j].getAttribute("height") + '"';
			tabel_str += ">";
			tabel_str += htmlspecialchars(htmlspecialchars_decode(tds[j].innerHTML));
			tabel_str += "</td>";
		}
        tabel_str += "</tr>";
    }
	tabel_str += "</table>";
	this.fieldpost.value = tabel_str;
	//alert(tabel_str);
	/*
	for (var i=0;i<this.table.rows.length;i++) 
	{
        var newTr = this.table.rows[i].cloneNode(false);
		var tds = this.table.rows[i].getElementsByTagName("td");
		for(var j=0 ; j < tds.length ; j++)
		{
			var newCell = tds[j].cloneNode(true);
			newCell.removeAttribute("style");
			newTr.appendChild(newCell);
		}
        clonedTbodyNode.appendChild(newTr);
    }
	this.fieldpost.value = '<table width="100%" cellpadding="0" cellspacing="0" border="0">' + clonedTableNode.innerHTML + '</table>';
	*/
}

function tableeditor_add_cell_functionality(thetd)
{
	thetd.onclick = new Function("table_editor_" + this.id + ".select_td(this);");
	/*thetd.onmousedown = new Function("table_editor_" + this.id + ".start_selection(this);");
	thetd.onmousemove = new Function("table_editor_" + this.id + ".move_selection(this);");
	thetd.onmouseup = new Function("table_editor_" + this.id + ".end_selection(this);");*/
	
	thetd.style.borderBottom="1px";
	thetd.style.borderRight="1px";
	thetd.style.borderColor="#CCCCCC";
	thetd.style.borderStyle="solid";
}

function tableeditor_unselect()
{
	this.fieldinput.disabled = true;
	this.fieldwidth.disabled = true;
	this.fieldheight.disabled = true;
	this.fieldstyle.disabled = true;
	this.selected_td = null;
	
	this.img_removerow.src = "/css/back/icon/twotone/gray/trash.gif";
	this.img_removerow.style.cursor = "auto";
	this.img_removerow.onclick = null;
	
	this.img_removecolumn.src = "/css/back/icon/twotone/gray/trash.gif";
	this.img_removecolumn.style.cursor = "auto";
	this.img_removecolumn.onclick = null;
}

function tableeditor_changestyle()
{
	this.selected_td.className = this.fieldstyle.value;
	this.write_to_post();
}

// functie voor een rowselect
function tableeditor_select_td(thetd) 
{
	if(this.selected_td !== undefined && this.selected_td !== null)
		this.selected_td.style.backgroundColor = this.last_selected_color;
	this.last_selected_color = thetd.style.backgroundColor;
	thetd.style.backgroundColor = "#D1D2AF";
	this.selected_td = thetd;
	if(this.selected_td.innerHTML != "&nbsp;")
		this.fieldinput.value = htmlspecialchars_decode(thetd.innerHTML);
	else
		this.fieldinput.value = "";
	this.fieldinput.disabled = false;
	this.fieldinput.focus();
	
	this.fieldwidth.disabled = false;
	this.fieldwidth.value = this.selected_td.getAttribute("width");
	
	this.fieldheight.disabled = false;
	this.fieldheight.value = this.selected_td.getAttribute("height");
	
	this.fieldstyle.disabled = false;
	this.fieldstyle.value = this.selected_td.getAttribute("class");
	
	this.img_removerow.src = "/css/back/icon/twotone/trash.gif";
	this.img_removerow.style.cursor = "pointer";
	this.img_removerow.onclick = new Function("table_editor_" + this.id + ".deleterow();");
	
	this.img_removecolumn.src = "/css/back/icon/twotone/trash.gif";
	this.img_removecolumn.style.cursor = "pointer";
	this.img_removecolumn.onclick = new Function("table_editor_" + this.id + ".deletecolumn();");
}

function tableeditor_changeinput()
{
	/*if(this.selected_td.firstChild !== null)
		this.selected_td.removeChild(this.selected_td.firstChild);
	this.selected_td.appendChild(document.createTextNode());
	this.selected_td.firstChild.innerContent = htmlspecialchars(this.fieldinput.value);*/
	this.selected_td.innerHTML = htmlspecialchars(this.fieldinput.value);
	this.write_to_post();
}

function tableeditor_addrow()
{
	//if no td is selected we add a row add the end of the table
	if(this.selected_td !== null)
	{
		
		num_cols = this.selected_td.parentNode.getElementsByTagName("td").length;
		var new_tr = document.createElement("tr");
		for(var i = 0; i<num_cols; i++)
		{
			var new_td = document.createElement("td");
			new_td.innerHTML = "&nbsp;";
			new_tr.appendChild(new_td);
			this.add_cell_functionality(new_td);
		}
		this.selected_td.parentNode.parentNode.insertBefore(new_tr, this.selected_td.parentNode);
	}
	else
	{
		var trs = this.table.getElementsByTagName("tr");
		var num_cols = 2;
		if(trs.length > 0)
			num_cols = trs[trs.length - 1].getElementsByTagName("td").length;
		var new_tr = document.createElement("tr");
		for(var i = 0; i<num_cols; i++)
		{
			var new_td = document.createElement("td");
			new_td.innerHTML = "&nbsp;";
			new_tr.appendChild(new_td);
			this.add_cell_functionality(new_td);
		}
		this.table.appendChild(new_tr);
	}
	this.write_to_post();
}

function tableeditor_addcolumn()
{
	//we first check if there are any rows
	if(this.table.getElementsByTagName("tr").length == 0)
	{
		alert("Before adding columns you must add at least one row.");
		return;
	}
	//if no td is selected we add a row add the end of the table
	if(this.selected_td !== null)
	{
		//we bepalen eerst de index van de td die geselecteerd is
		tds = this.selected_td.parentNode.getElementsByTagName("td");
		var index = 0;
		for(var i = 0 ; i<tds.length ; i++)
		{
			if(tds[i] == this.selected_td)
				index = i;
		}
		
		var trs = this.table.getElementsByTagName("tr");
		for(var i = 0; i<trs.length; i++)
		{
			var new_td = document.createElement("td");
			new_td.innerHTML = "&nbsp;";
			trs[i].insertBefore(new_td, trs[i].childNodes[index]);
			this.add_cell_functionality(new_td);
		}
	}
	else
	{
		var trs = this.table.getElementsByTagName("tr");
		for(var i = 0; i<trs.length; i++)
		{
			var new_td = document.createElement("td");
			new_td.innerHTML = "&nbsp;";
			this.add_cell_functionality(new_td);
			trs[i].appendChild(new_td);
		}
	}
	this.write_to_post();
}

function tableeditor_changewidth()
{
	this.selected_td.setAttribute("width", this.fieldwidth.value);
	this.write_to_post();
}

function tableeditor_changeheight()
{
	this.selected_td.setAttribute("height", this.fieldheight.value);
	this.write_to_post();
}

function tableeditor_deleterow()
{
	if(this.selected_td == null)
		return;
	
	this.selected_td.parentNode.parentNode.removeChild(this.selected_td.parentNode);
	
	this.unselect();
	this.write_to_post();
}

function tableeditor_deletecolumn()
{
	if(this.selected_td == null)
		return;
		
	tds = this.selected_td.parentNode.getElementsByTagName("td");
	var index = 0;
	for(var i = 0 ; i<tds.length ; i++)
	{
		if(tds[i] == this.selected_td)
			index = i;
	}
	
	var trs = this.table.getElementsByTagName("tr");
	for(var i = 0; i<trs.length; i++)
	{
		trs[i].removeChild(trs[i].childNodes[index]);
	}
	
	this.unselect();
	this.write_to_post();
}

function tableeditor_start_selection(the_td)
{
	this.selection_start_td = the_td;
}

function tableeditor_move_selection(the_td)
{
	
}

function tableeditor_end_selection(the_td)
{
	this.selection_end_td = the_td;
}

new tableeditor(0);
tableeditor.prototype.select_td = tableeditor_select_td;
tableeditor.prototype.changeinput = tableeditor_changeinput;
tableeditor.prototype.addrow = tableeditor_addrow;
tableeditor.prototype.addcolumn = tableeditor_addcolumn;
tableeditor.prototype.changewidth = tableeditor_changewidth;
tableeditor.prototype.changeheight = tableeditor_changeheight;
tableeditor.prototype.deleterow = tableeditor_deleterow;
tableeditor.prototype.deletecolumn = tableeditor_deletecolumn;
tableeditor.prototype.start_selection = tableeditor_start_selection;
tableeditor.prototype.move_selection = tableeditor_move_selection;
tableeditor.prototype.end_selection = tableeditor_end_selection;
tableeditor.prototype.add_cell_functionality = tableeditor_add_cell_functionality;
tableeditor.prototype.unselect = tableeditor_unselect;
tableeditor.prototype.changestyle = tableeditor_changestyle;
tableeditor.prototype.write_to_post = tableeditor_write_to_post;

function htmlspecialchars (string, quote_style) {
    var histogram = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();
    
    if (false === (histogram = get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    
    for (symbol in histogram) {
        entity = histogram[symbol];
        tmp_str = tmp_str.split(symbol).join(entity);
    }
    
    return tmp_str;
}

function htmlspecialchars_decode(string, quote_style) {
    var histogram = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();
    
    if (false === (histogram = get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
 
    // &amp; must be the last character when decoding!
    delete(histogram['&']);
    histogram['&'] = '&amp;';
 
    for (symbol in histogram) {
        entity = histogram[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    
    return tmp_str;
}

function get_html_translation_table(table, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco
    // +   bugfixed by: madipta
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js. Meaning the constants are not
    // %          note: real constants, but strings instead. integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // %          note: Table from http://www.the-art-of-web.com/html/character-codes/
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    
    var entities = {}, histogram = {}, decimal = 0, symbol = '';
    var constMappingTable = {}, constMappingQuoteStyle = {};
    var useTable = {}, useQuoteStyle = {};
    
    useTable      = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
    useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');
    
    // Translate arguments
    constMappingTable[0]      = 'HTML_SPECIALCHARS';
    constMappingTable[1]      = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';
    
    // Map numbers to strings for compatibilty with PHP constants
    if (!isNaN(useTable)) {
        useTable = constMappingTable[useTable];
    }
    if (!isNaN(useQuoteStyle)) {
        useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
    }
 
    if (useTable == 'HTML_SPECIALCHARS') {
        // ascii decimals for better compatibility
        entities['38'] = '&amp;';
        if (useQuoteStyle != 'ENT_NOQUOTES') {
            entities['34'] = '&quot;';
        }
        if (useQuoteStyle == 'ENT_QUOTES') {
            entities['39'] = '&#039;';
        }
        entities['60'] = '&lt;';
        entities['62'] = '&gt;';
    } else if (useTable == 'HTML_ENTITIES') {
        // ascii decimals for better compatibility
      entities['38']  = '&amp;';
        if (useQuoteStyle != 'ENT_NOQUOTES') {
            entities['34'] = '&quot;';
        }
        if (useQuoteStyle == 'ENT_QUOTES') {
            entities['39'] = '&#039;';
        }
      entities['60']  = '&lt;';
      entities['62']  = '&gt;';
      entities['160'] = '&nbsp;';
      entities['161'] = '&iexcl;';
      entities['162'] = '&cent;';
      entities['163'] = '&pound;';
      entities['164'] = '&curren;';
      entities['165'] = '&yen;';
      entities['166'] = '&brvbar;';
      entities['167'] = '&sect;';
      entities['168'] = '&uml;';
      entities['169'] = '&copy;';
      entities['170'] = '&ordf;';
      entities['171'] = '&laquo;';
      entities['172'] = '&not;';
      entities['173'] = '&shy;';
      entities['174'] = '&reg;';
      entities['175'] = '&macr;';
      entities['176'] = '&deg;';
      entities['177'] = '&plusmn;';
      entities['178'] = '&sup2;';
      entities['179'] = '&sup3;';
      entities['180'] = '&acute;';
      entities['181'] = '&micro;';
      entities['182'] = '&para;';
      entities['183'] = '&middot;';
      entities['184'] = '&cedil;';
      entities['185'] = '&sup1;';
      entities['186'] = '&ordm;';
      entities['187'] = '&raquo;';
      entities['188'] = '&frac14;';
      entities['189'] = '&frac12;';
      entities['190'] = '&frac34;';
      entities['191'] = '&iquest;';
      entities['192'] = '&Agrave;';
      entities['193'] = '&Aacute;';
      entities['194'] = '&Acirc;';
      entities['195'] = '&Atilde;';
      entities['196'] = '&Auml;';
      entities['197'] = '&Aring;';
      entities['198'] = '&AElig;';
      entities['199'] = '&Ccedil;';
      entities['200'] = '&Egrave;';
      entities['201'] = '&Eacute;';
      entities['202'] = '&Ecirc;';
      entities['203'] = '&Euml;';
      entities['204'] = '&Igrave;';
      entities['205'] = '&Iacute;';
      entities['206'] = '&Icirc;';
      entities['207'] = '&Iuml;';
      entities['208'] = '&ETH;';
      entities['209'] = '&Ntilde;';
      entities['210'] = '&Ograve;';
      entities['211'] = '&Oacute;';
      entities['212'] = '&Ocirc;';
      entities['213'] = '&Otilde;';
      entities['214'] = '&Ouml;';
      entities['215'] = '&times;';
      entities['216'] = '&Oslash;';
      entities['217'] = '&Ugrave;';
      entities['218'] = '&Uacute;';
      entities['219'] = '&Ucirc;';
      entities['220'] = '&Uuml;';
      entities['221'] = '&Yacute;';
      entities['222'] = '&THORN;';
      entities['223'] = '&szlig;';
      entities['224'] = '&agrave;';
      entities['225'] = '&aacute;';
      entities['226'] = '&acirc;';
      entities['227'] = '&atilde;';
      entities['228'] = '&auml;';
      entities['229'] = '&aring;';
      entities['230'] = '&aelig;';
      entities['231'] = '&ccedil;';
      entities['232'] = '&egrave;';
      entities['233'] = '&eacute;';
      entities['234'] = '&ecirc;';
      entities['235'] = '&euml;';
      entities['236'] = '&igrave;';
      entities['237'] = '&iacute;';
      entities['238'] = '&icirc;';
      entities['239'] = '&iuml;';
      entities['240'] = '&eth;';
      entities['241'] = '&ntilde;';
      entities['242'] = '&ograve;';
      entities['243'] = '&oacute;';
      entities['244'] = '&ocirc;';
      entities['245'] = '&otilde;';
      entities['246'] = '&ouml;';
      entities['247'] = '&divide;';
      entities['248'] = '&oslash;';
      entities['249'] = '&ugrave;';
      entities['250'] = '&uacute;';
      entities['251'] = '&ucirc;';
      entities['252'] = '&uuml;';
      entities['253'] = '&yacute;';
      entities['254'] = '&thorn;';
      entities['255'] = '&yuml;';
    } else {
        throw Error("Table: "+useTable+' not supported');
        return false;
    }
    
    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal);
        histogram[symbol] = entities[decimal];
    }
    
    return histogram;
}
