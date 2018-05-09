function datagrid(theid) {   
    this.id = theid;  
	this.selected_id = 0;
	this.selected_tr = null;
	this.icon_ids = new Array();
	this.selected_ids = "";
}

// functie voor een rowselect
function datagrid_rowselect(thetd, e) 
{
	//check for ctrl and shift press
	//alert("shift:" + e.shiftKey + " ctrl" + e.ctrlKey);
	
	var thetd_id = null;
	if(thetd.tagName=="DIV")
		thetd_id = thetd.getAttribute("id");
	else
		thetd_id = thetd.parentNode.getAttribute("id");
		
	if(!e.shiftKey && !e.ctrlKey)
	{
		//we deselect all the previous
		
		var allids = this.selected_ids.split('##');
		for(var j = 0 ; j < allids.length ; j++)
		{
			var the_tr = document.getElementById(allids[j]);
			if(the_tr !== null)
			{
				for (var i = 0; i < the_tr.childNodes.length; i++)
				{
					if(thetd.tagName=="DIV")
						$(the_tr).css({"background-color": "#FFFFFF", "color": "#1C2833"});
					else
						$(the_tr).children().css({"background-color": "#FFFFFF", "color": "#1C2833"});
				}
			}
		}
		//reset the selected_ids
		this.selected_id = 0;
		this.selected_tr = null;
		this.selected_ids = "";
		if(thetd.tagName=="DIV")
			this.add_selected_ids(thetd.getAttribute("id"));
		else
			this.add_selected_ids(thetd.parentNode.getAttribute("id"));
		this.select_one_row(thetd);
	}
	
	if(e.shiftKey && !e.ctrlKey)
	{
		
			
		if(this.selected_id != 0)
		{
			//alles tussen selected_id en nieuwe klick selecteren. We overlopen de hele tabel omdat we niet weten welke eerst komt (begin of eind)
			var alltrs = null;
			if(thetd.tagName=="DIV")
				alltrs = thetd.parentNode.childNodes;
			else
				alltrs = thetd.parentNode.parentNode.childNodes;
				
			var start_selecting  = false;
			var stop_selecting = false;
			for(var i = 0 ; i < alltrs.length ; i++)
			{
				if(start_selecting && (alltrs[i].getAttribute("id") == this.selected_id || alltrs[i].getAttribute("id") == thetd_id))
					stop_selecting = true;
				if(start_selecting && !stop_selecting)
				{
					//checken of die al geselecteerd is
					if(!this.in_selected_ids(alltrs[i].getAttribute("id")))
					{
						this.add_selected_ids(alltrs[i].getAttribute("id"));
						if(thetd.tagName=="DIV")
							$(alltrs[i]).css({"background-color": "#DDDDDD", "color": "#1C2833"});
						else
						{
							$(alltrs[i]).children().css({"background-color": "#DDDDDD", "color": "#1C2833"});
						}
					}
				}
				if(!start_selecting && (alltrs[i].getAttribute("id") == this.selected_id || alltrs[i].getAttribute("id") == thetd_id))
					start_selecting = true;
			}
			if(thetd.tagName=="DIV")
				$("#" + this.selected_id).css({"background-color": "#DDDDDD", "color": "#1C2833"});
			else
			{
				$("#" + this.selected_id).children().css({"background-color": "#DDDDDD", "color": "#1C2833"});
			}
		}
		
		this.add_selected_ids(thetd_id);
		this.select_one_row(thetd);
		
	}
	
	if(e.ctrlKey && !e.shiftKey)
	{
		//if the clicked row is aready selected we unselect the row
		if(this.in_selected_ids(thetd_id))
		{
			this.remove_selected_ids(thetd_id);
			if(thetd.tagName=="DIV")
				$(this.selected_tr).css({"background-color": "#FFFFFF", "color": "#1C2833"});
			else
			{
				$(this.selected_tr).children().css({"background-color": "#FFFFFF", "color": "#1C2833"});
			}
			if(this.selected_id == thetd_id)
			{
				//we zorgen ervoor dat een andere zwaar geselecteerd wordt
				if(this.selected_ids.split('##')[0] != "")
				{
					//select the first one
					if(thetd.tagName=="DIV")
						this.select_one_row(document.getElementById(this.selected_ids.split('##')[0]));
					else
						this.select_one_row(document.getElementById(this.selected_ids.split('##')[0]).childNodes[0]);
				}
				else
				{
					//deselect icons
					for (var i = 0; i < this.icon_ids.length; i++)
					{
						var the_icon = document.getElementById(this.icon_ids[i]);
						if(the_icon !== null)
						{
							if(the_icon.getAttribute("state") == "enabled" && the_icon.getAttribute("enabled_by_rowclick") == "true")
							{
								the_icon.src = the_icon.getAttribute("icon_disabled");
								the_icon.style.cursor='normal';
								the_icon.setAttribute("state", "disabled");
							}
						}
					}
				}
			}
		}
		else
		{
			if(this.selected_tr !== null)
			{
				if(this.selected_tr.tagName=="DIV")
					$(this.selected_tr).css({"background-color": "#DDDDDD", "color": "#1C2833"});
				else
				{
					$(this.selected_tr).children().css({"background-color": "#DDDDDD", "color": "#1C2833"});
				}
			}
			this.add_selected_ids(thetd_id);
			this.select_one_row(thetd);
		}
	}
}

function datagrid_in_selected_ids(sid)
{
	var allids = this.selected_ids.split('##');
	for(var i = 0; i < allids.length ; i++)
	{
		//alert(allids[i]);
		if(allids[i] == sid)
			return true;
	}
	return false;
}

function datagrid_add_selected_ids(sid)
{
	if(this.selected_ids == "")
		this.selected_ids = sid;
	else	
		this.selected_ids += '##' + sid;
}

function datagrid_remove_selected_ids(sid)
{
	this.selected_ids = this.selected_ids.replace(sid + "##", "");
	this.selected_ids = this.selected_ids.replace("##" + sid, ""); 
	this.selected_ids = this.selected_ids.replace(sid, "");
}

function datagrid_get_selected_ids(sid)
{
	return this.selected_ids.split('##');
}

function datagrid_select_one_row(thetd)
{
	//we select the clicked row
	this.selected_id = null;
	this.selected_tr = null;
	if(thetd.tagName=="DIV")
	{
		this.selected_id = thetd.getAttribute("id");
		this.selected_tr = thetd;
	}
	else
	{
		this.selected_id = thetd.parentNode.getAttribute("id");
		this.selected_tr = thetd.parentNode;
	}
	
	if(thetd.tagName=="DIV")
		$(this.selected_tr).css({"background-color": "#4D6F8C", "color": "#FFFFFF"});
	else
	{
		//for (var i = 0; i < this.selected_tr.childNodes.length; i++)
		//{
			$(this.selected_tr).children().css({"background-color": "#4D6F8C", "color": "#FFFFFF"});
		//}
	}
	//we enable the icons
	
	for (var i = 0; i < this.icon_ids.length; i++)
	{
		var the_icon = document.getElementById(this.icon_ids[i]);
		if(the_icon !== null)
		{
			if(the_icon.getAttribute("state") == "disabled" && the_icon.getAttribute("enabled_by_rowclick") == "true")
			{
				the_icon.src = the_icon.getAttribute("icon_enabled");
				the_icon.style.cursor='pointer';
				the_icon.setAttribute("state", "enabled");
			}
		}
	}
}

function datagrid_main_check(selected)
{
	//we deselect or select all checkboxes
	var checkers = document.getElementsByName('dg_' + this.id + '_checkbox');
	for(i = 0 ; i < checkers.length ; i++)
	{
		checkers[i].checked = selected;
	}
}

function datagrid_get_checked_ids(seperator)
{
	//we search for the selected ids
	var checkers = document.getElementsByName('dg_' + this.id + '_checkbox');
	var retstr = "";
	for(i = 0 ; i < checkers.length ; i++)
	{
		if(checkers[i].checked)
		{
			if(retstr == "")
				retstr = checkers[i].value;
			else
				retstr += seperator + checkers[i].value;
		}
	}
	return retstr;
}

function datagrid_test(xmlHttp)
{
	alert(xmlHttp.responseText);
}

function dg_afterorder(xmlHttp)
{
	//voor inline edit refresh
	if($("#main_inline_edit_div").get(0) != undefined)
		inline_edit_after_save();
}

new datagrid(0);
datagrid.prototype.rowselect = datagrid_rowselect;
datagrid.prototype.in_selected_ids = datagrid_in_selected_ids;
datagrid.prototype.add_selected_ids = datagrid_add_selected_ids;
datagrid.prototype.remove_selected_ids = datagrid_remove_selected_ids;
datagrid.prototype.get_selected_ids = datagrid_get_selected_ids;
datagrid.prototype.select_one_row = datagrid_select_one_row;
datagrid.prototype.main_check = datagrid_main_check;
datagrid.prototype.get_checked_ids = datagrid_get_checked_ids;
