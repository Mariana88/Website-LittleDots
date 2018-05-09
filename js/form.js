function form(formid, table, dataid, savebuttonselector) 
{   
    this.id = formid;  
	this.table = table;
	this.dataid = dataid;
	this.savebutton = null;
	this.indialog = null;
	this.currentlang = "";
	this.dataDoc = null;
	this.subdata_editor_id = Array();
	this.aftersave_error = null;
	this.aftersave_success = null;
	this.aftersave_data = null;
}

function form_add_subdata(popup_id)
{
	this.subdata_editor_id[this.subdata_editor_id.length] = popup_id;
}

function form_clear_subdata()
{
	this.subdata_editor_id = Array();
}

//functie die de data laad voor zijn dataid
function form_load_data()
{
	/*
	$.ajax({
        type: "GET",
		url: "/ajax.php?sessid=" + session_id + "&form=1&action=load_data&table=" + this.table + "&dataid=" + this.dataid,
		dataType: "xml",
		success: function(xml) {
	 		//alert($(xml).find("table").attr("id"));
			thethis = window[$(xml).find("table").attr("id") + "_form"];
			thethis.dataDoc = $(xml);
			//enable the lang links
			thethis.enable_lang_links();
		}
	});
	*/
}

function form_enable_lang_links()
{
	/*
	//we zoeken naar alle lang nodes in de xml
	$langnodes = this.dataDoc.find("lang");
	
	if($langnodes.length > 0)
	{
		var htmlstr = '<div style="text-align: right; padding-right: 4px; padding-bottom:4px;" id="' + this.id + '_langlinks">';
		var thecurlang = this.currentlang;
		var theformid = this.id;
		$langnodes.each(function(index, Element) {
				if (thecurlang == $(Element).attr("id"))
					htmlstr += '<span lang="' + $(Element).attr("id") + '" class="form_lang_selector_selected" current="yes" onclick="window[\'' + theformid + '\'].changelang(\'' + $(Element).attr("id") + '\', this);">' + $(Element).attr("id") + '</span>';
				else
					htmlstr += '<span lang="' + $(Element).attr("id") + '" class="form_lang_selector" current="no" onclick="window[\'' + theformid + '\'].changelang(\'' + $(Element).attr("id") + '\', this);">' + $(Element).attr("id") + '</span>';
		  	});

		htmlstr += '</div>';
		//alert(htmlstr);
		$('#' + this.id).prepend(htmlstr);
	}
	*/
}

//functie die de form post
function form_post()
{
	this.savebutton.text("Saving");
	var xmlstr = this.create_post_xml(true);
	xmlstr = xmlstr.replace(/&/g, "___AMP___");
	xmlstr = xmlstr.replace(/\?/g, "___QUEST___");
	xmlstr = xmlstr.replace(/#/g, "___HEK___");
	xmlstr = xmlstr.replace(/\+/g, "___PLUS___");
	xmlstr = xmlstr.replace(/€/g, "___EUR___");
	
	send_ajax_request("POST", '/ajax.php?sessid=' + session_id + '&form=1&action=save_data', 'data=' + encodeURI(xmlstr), form_save_return);
	//alert(xmlstr);
}

//functie die enkel voor het huidige form een xml doc maakt
function form_create_post_xml(in_str)
{
	tinyMCE.triggerSave();
	//first we save the data in the doc
	//LANG DEP
	var thethis = this;
	this.dataDoc.find('lang[id="' + thethis.currentlang + '"]').find("field").each(function(){
			//we zoeken naar het veld
			the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
			the_value = "";
			switch(the_input.attr("type"))
			{
				case "checkbox":
						if(the_input.get(0).checked)
							the_value = 1;
						else
							the_value = 0;
					break;
				case "hidden":
						var inst = null;
						try {
							var inst = FCKeditorAPI.GetInstance(the_input.attr("name"));
						} catch (error) {}
						
						if(inst !== null && inst !== undefined)
							the_value = inst.GetHTML();
						else
							the_value = the_input.val();
					break;
				default:
						the_value = the_input.val();
					break;
			}
			$(this).text(the_value);
		});
	//NOT LANG DEP
	this.dataDoc.find('table[id="' + thethis.table + '"]').children("field").each(function(){
			//we zoeken naar het veld
			the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
			the_value = "";
			switch(the_input.attr("type"))
			{
				case "checkbox":
						if(the_input.get(0).checked)
							the_value = 1;
						else
							the_value = 0;
					break;
				case "hidden":
						var inst = null;
						try {
							var inst = FCKeditorAPI.GetInstance(the_input.attr("name"));
						} catch (error) {}
						
						if(inst !== null && inst !== undefined)
							the_value = inst.GetHTML();
						else
							the_value = the_input.val();
					break;
				default:
						the_value = the_input.val();
					break;
			}
			$(this).text(the_value);
		});
	if(in_str)
		return xmlstr = cms2_xml_to_string(this.dataDoc.get(0));
  	else
		return this.dataDoc;
}

//laad de data voor de nieuwe lang (ook de grids veranderen)
function form_changelang(newlang, element)
{
	//alert(cms2_xml_to_string(this.dataDoc.get(0)));
	tinyMCE.triggerSave();
	if($(element).attr("current") == "yes")
		return;
	
	//VALUES VAN CURRENT LANG IN DE XML LADEN
	var thethis = this;
	this.dataDoc.find('lang[id="' + thethis.currentlang + '"]').find("field").each(function(){
			//we zoeken naar het veld
			the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
			the_label = $('#' + thethis.id).find('label[name="' + $(this).attr("id") + '_label"]');
			the_value = "";
			the_to_value = thethis.dataDoc.find('lang[id="' + newlang + '"]').find('field[id="' + $(this).attr("id") + '"]').text();
			if(thethis.dataDoc.find('lang[id="' + newlang + '"]').find('field[id="' + $(this).attr("id") + '"]').attr("error") == "true")
			{
				effects_highlight(the_input.get(0), 400, "#EA4848", false);
				the_label.children('img[icontype="error"]').remove();
				form_add_error_tolabel(the_label, thethis.dataDoc.find('lang[id="' + newlang + '"]').find('field[id="' + $(this).attr("id") + '"]').attr("errormsg"));
			}
			else
			{
				effects_highlight(the_input.get(0), 400, "#FFFFFF", false);
				the_label.children('img[icontype="error"]').remove();
			}
			switch(the_input.attr("type"))
			{
				case "checkbox":
						//GET
						if(the_input.get(0).checked)
							the_value = 1;
						else
							the_value = 0;
						//SET
						if(the_to_value >= 1)
							the_input.get(0).checked = true;
						else
							the_input.get(0).checked = false;
					break;
				case "hidden":
						var inst = null;
						/*try {
							var inst = FCKeditorAPI.GetInstance(the_input.attr("name"));
						} catch (error) {}
						
						if(inst !== null && inst !== undefined)
						{
							//GET
							the_value = inst.GetHTML();
							//SET
							inst.SetHTML(the_to_value);
						}
						else
						{*/
							//checken voor wordlist
							if(the_input.attr("wordlist") == "true")
							{
								cms_wordlist_fill($(this).attr("id").replace("\.", "_"), the_to_value);
							}
							
							//checken voor wordlist
							if(the_input.attr("worddatalist") == "true")
							{
								cms_worddatalist_fill($(this).attr("id").replace("\.", "_"), the_to_value, newlang);
							}
							//GET
							the_value = the_input.val();
							//SET
							the_input.val(the_to_value);
						//}
					break;
				default:
					if(the_input.get(0).nodeName == "TEXTAREA" && tinyMCE.get(the_input.attr("id")) !== undefined && the_input.attr("id") !== undefined)
					{
						//GET
						the_value = the_input.val();
						//SET
						tinyMCE.get(the_input.attr("id")).setContent(the_to_value);
					}
					else
					{
						//GET
						the_value = the_input.val();
						//SET
						the_input.val(the_to_value);
					}
					break;
			}
			$(this).text(the_value);
		});
	//alert(this.dataDoc.html());
	
	
	//LANGLINKS BEWERKEN
	$('#' + this.id + '_langlinks').find('span').each(function(){
			if($(this).attr("lang") == newlang)
			{
				$(this).attr("current", "yes");
				$(this).removeClass('form_lang_selector').addClass('form_lang_selector_selected');
				if($(this).attr("error") != "true")
					$(this).get(0).style.backgroundColor = "#666666";
			}
			else
			{
				$(this).attr("current", "no");
				$(this).removeClass('form_lang_selector_selected').addClass('form_lang_selector');
				if($(this).attr("error") != "true")
					$(this).get(0).style.backgroundColor = "#CCCCCC";
			}
		});
	//SUBDATA BIJWERKEN
	for(var i = 0 ; i < this.subdata_editor_id.length ; i++)
	{
		$("#dg_dg_de_" + this.subdata_editor_id[i] + "_html_panel").load("/ajax.php?sessid=" + session_id + "&popup_id=" + this.subdata_editor_id[i] + "&changelang=" + newlang);
	}
	this.currentlang = newlang;
}

//gaat checken of de save gelukt is zoniet, errors tonen
function form_interprete_save(xmlHttp)
{
	//alert(xmlHttp.responseText);
	doc = $(xmlHttp.responseXML);
	tablenode = doc.find('table[id="' + this.table + '"]');
	thethis = this;
	//CHECKEN VOOR ERRORS
	this.hide_errors();
	if(tablenode.find("savestatus").text() == "NOK")
	{
		//the button
		if($(this.savebutton).length > 0)
		{
			this.savebutton.text("NOT Saved");
			effects_highlight(this.savebutton.get(0), 1000, "#EA4848", false);
			setTimeout('window["' + this.id + '"].savebutton.text("Save"); effects_highlight(window["' + this.id + '"].savebutton.get(0), 1000, "#4D6F8C", false);', 4000)
		}
		//we gaan de errors ophalen voor de niet lang velden
		tablenode.find('errors').children('error').each(function(){
			the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
			the_label = $('#' + thethis.id).find('label[name="' + $(this).attr("id") + '_label"]');
			effects_highlight(the_input.get(0), 1000, "#EA4848", false);
			the_input.attr("error", "true");
			form_add_error_tolabel(the_label, $(this).text())
			thethis.dataDoc.find('table[id="' + thethis.table + '"]').children('field[id="' + $(this).attr("id") + '"]').attr("error", "true");
		});
		tablenode.find('errors').children('lang').each(function(){
			thelang = $(this);
			if($(this).find("error").length > 0)
			{
				//langlink roodmaken
				thelanglink = $('#' + thethis.id + '_langlinks').find('span[lang="' + $(this).attr("id") + '"]');
				if(thelanglink.length > 0)
				{
					if(thelanglink.attr("current") == "yes")
						thelanglink.get(0).style.backgroundColor = "#666666";
					if(thelanglink.attr("current") == "no")
						thelanglink.get(0).style.backgroundColor = "#CCCCCC";
					effects_highlight(thelanglink.get(0), 1000, "#EA4848", false);
					thelanglink.attr("error", "true");
				}
			}
			$(this).find("error").each(function(){
					the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
					the_label = $('#' + thethis.id).find('label[name="' + $(this).attr("id") + '_label"]');
					if(thelang.attr("id") == thethis.currentlang)
					{
						effects_highlight(the_input.get(0), 1000, "#EA4848", false);
						the_input.attr("error", "true");
						form_add_error_tolabel(the_label, $(this).text());
					}
					thethis.dataDoc.find('table[id="' + thethis.table + '"]').children('lang[id="' + thelang.attr("id") + '"]').children('field[id="' + $(this).attr("id") + '"]').attr("error", "true");
					thethis.dataDoc.find('table[id="' + thethis.table + '"]').children('lang[id="' + thelang.attr("id") + '"]').children('field[id="' + $(this).attr("id") + '"]').attr("errormsg", $(this).text());
				});
		});
		//de after functie oproepen
		if(this.aftersave_error != null)
		{
			window[this.aftersave_error](this.aftersave_data, xmlHttp);
		}
	}
	else
	{
		//DATADOC EN FIELDS UPDATEN
		if(tablenode.find("savetype").text() == "insert")
		{
			//elk id veld updaten
			$("#" + this.table + "_id").val(tablenode.find('newid[id="nolang"]').text());
			this.dataDoc.find('table[id="' + this.table + '"]').children('field[id="' + this.table + '.id"]').text(tablenode.find('newid[id="nolang"]').text());
			//voor elke taal
			this.dataDoc.find('table[id="' + this.table + '"]').children('lang').each(function(){
					$(this).find('field[id="' + thethis.table + '_lang_id"]').text(tablenode.find('newid[id="' + $(this).attr("id") + '"]').text());
					$(this).find('field[id="' + thethis.table + '_lang_parent_id"]').text(tablenode.find('newid[id="nolang"]').text());
				});
			//huidig open taal velden aanpassen
			$("#" + this.table + ".lang_id").val(tablenode.find('newid[id="' + this.currentlang + '"]').text());
			$("#" + this.table + ".lang_parent_id").val(tablenode.find('newid[id="nolang"]').text());
		}
		//WARNING
		var warning = doc.find('table[id="' + this.table + '"]').children('warning');
		if(warning.get(0) != null)
		{
			cms2_show_error_message(warning.text() + " Click cancel to close the window.", "Warning");
		}
		
		//CHECKEN OF DE FROM IN EEN DIALOG ZIT, ZOJA EVENTUEEL SLUITEN
		if(this.indialog != null && warning.get(0) == null)
		{
			//alert(tablenode.find("hassubdata").text());
			if((tablenode.find("savetype").text() == "update") || (tablenode.find("savetype").text() == "insert" && tablenode.find("hassubdata").text() == "no"))
			{
				//reset savebutton
				if(this.savebutton != null)
				{
					this.savebutton.get(0).style.backgroundColor = "#4D6F8C";
					this.savebutton.text('Save');
				}
				this.indialog.dialog('close');
			}
			if(tablenode.find("savetype").text() == "insert" && tablenode.find("hassubdata").text() == "yes")
			{
				//Button updaten
				if($(this.savebutton).length > 0)
				{
					this.savebutton.text("Saved Successfully");
					effects_highlight(this.savebutton.get(0), 1000, "#66CC66", false);
					setTimeout('window["' + this.id + '"].savebutton.text("Save"); effects_highlight(window["' + this.id + '"].savebutton.get(0), 1000, "#4D6F8C", false);', 4000)
				}
				//form reload
				loaddiv = $('#' + thethis.id).parent();
				cms2_show_loader(loaddiv.attr("id"));
				loaddiv.load('/ajax.php?sessid=' + session_id + '&form=1&action=reload_form&rf_table=' + this.table + '&rf_id=' + tablenode.find('newid[id="nolang"]').text() + '&rf_lang=' + this.currentlang);
				//alert(loaddiv);
			}
		}
		else
		{
			//Button updaten
			if($(this.savebutton).length > 0)
			{
				this.savebutton.text("Saved Successfully");
				effects_highlight(this.savebutton.get(0), 1000, "#66CC66", false);
				setTimeout('window["' + this.id + '"].savebutton.text("Save"); effects_highlight(window["' + this.id + '"].savebutton.get(0), 1000, "#4D6F8C", false);', 4000)
			}
		}
		
		if(this.aftersave_success != null)
		{
			window[this.aftersave_success](this.aftersave_data, xmlHttp);
		}
	}
}

function form_interprete_save_doc(doc)
{
	
}

function form_add_error_tolabel(label, msg)
{
	label.attr("error", "true");
	label.prepend('<img icontype="error" src="/css/back/label-exclamation.gif" title="' + msg + '" style="margin-right:4px;"/>');
	label.children('img[icontype="error"]').tooltip({showURL: false});
}

function form_hide_errors()
{
	thethis = this;
	this.dataDoc.find('[error="true"]').attr("error", "false");
	//labelerrorspics weg doen
	//LANGLINKS terugzetten
	$('#' + this.id + '_langlinks').find('span').each(function(){
			if($(this).attr("current") == "yes")
				effects_highlight($(this).get(0), 1000, "#666666", false);
			else
				effects_highlight($(this).get(0), 1000, "#CCCCCC", false);
			$(this).attr("error", "false");
		});
	this.dataDoc.find('table[id="' + thethis.table + '"]').children("field").each(function(){
			the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
			the_label = $('#' + thethis.id).find('label[name="' + $(this).attr("id") + '_label"]');
			if(the_input.attr("error") == "true")
			{
				effects_highlight(the_input.get(0), 1000, "#FFFFFF", false);
				the_input.attr("error", "false");
				the_label.attr("error", "false");
				the_label.children('img[icontype="error"]').remove();
			}
		});
	this.dataDoc.find('table[id="' + thethis.table + '"]').find("lang").first().find("field").each(function(){
			the_input = $('#' + thethis.id).find('[name="' + $(this).attr("id") + '"]');
			the_label = $('#' + thethis.id).find('label[name="' + $(this).attr("id") + '_label"]');
			if(the_input.attr("error") == "true")
			{
				effects_highlight(the_input.get(0), 1000, "#FFFFFF", false);
				the_input.attr("error", "false");
				the_label.attr("error", "false");
				the_label.children('img[icontype="error"]').remove();
			}
		});
}

function form_save_return(xmlHttp)
{
	//alert(xmlHttp.responseText);
	doc = $(xmlHttp.responseXML);
	doc.find("table").each(function(){
			window[$(this).attr("id") + '_form'].interprete_save(xmlHttp);
		});
}

new form(0);
form.prototype.load_data = form_load_data;
form.prototype.post = form_post;
form.prototype.create_post_xml = form_create_post_xml;
form.prototype.changelang = form_changelang;
form.prototype.interprete_save = form_interprete_save;
form.prototype.interprete_save_doc = form_interprete_save_doc;
form.prototype.enable_lang_links = form_enable_lang_links;
form.prototype.hide_errors = form_hide_errors;
form.prototype.add_subdata = form_add_subdata;
form.prototype.clear_subdata = form_clear_subdata;