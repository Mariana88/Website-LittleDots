//variabelen voor als we op een link klikken
var inline_edit_link_clicked = "";
var inline_edit_link_clicked_target = "";
var inline_edit_link_clicked_parent = null;

function inline_edit()
{
	//alle toolboxen voorzien van hun functionaliteiten
	$('div[inlinetoolbox="true"]').each(function(){
		//$(this).parent().append('<div type="blicsm_page_edit" class="inline_edit_edit_btn">Edit Page</div>');
		$(this).hover(
			  function () {
				if(document.getElementById("inline_edit_marker") == undefined)
				{
					var newDiv = document.createElement("div");
					newDiv.setAttribute("id", "inline_edit_marker");
					document.body.appendChild(newDiv);
					$("#inline_edit_marker").css("display", "none");
					$("#inline_edit_marker").css("background-color", "green");
					$("#inline_edit_marker").css("z-index", "50");
					$("#inline_edit_marker").css("position", "absolute");
					$("#inline_edit_marker").css("filter", "alpha(opacity=50)");
					$("#inline_edit_marker").css("opacity", "0.5");
				}
				$("#inline_edit_marker").width($(this).parent().width());
				$("#inline_edit_marker").height($(this).parent().height());
				$("#inline_edit_marker").css("padding", $(this).parent().css("padding"));
				$("#inline_edit_marker").css("padding-top", $(this).parent().css("padding-top"));
				$("#inline_edit_marker").css("padding-right", $(this).parent().css("padding-right"));
				$("#inline_edit_marker").css("padding-bottom", $(this).parent().css("padding-bottom"));
				$("#inline_edit_marker").css("padding-left", $(this).parent().css("padding-left"));
				$("#inline_edit_marker").css("margin", $(this).parent().css("margin"));
				$("#inline_edit_marker").css("margin-top", $(this).parent().css("margin-top"));
				$("#inline_edit_marker").css("margin-right", $(this).parent().css("margin-right"));
				$("#inline_edit_marker").css("margin-bottom", $(this).parent().css("margin-bottom"));
				$("#inline_edit_marker").css("margin-left", $(this).parent().css("margin-left"));
				$("#inline_edit_marker").css("display", "block");
				$("#inline_edit_marker").offset($(this).parent().offset());
				
				//$(this).parent().css("filter", "alpha(opacity=50)");
				//$(this).parent().css("opacity", "0.5");
				$(this).css("z-index", "100");
			  },
			  function () {
				//$(this).parent().css("filter", "alpha(opacity=100)");
				//$(this).parent().css("opacity", "1");
				$(this).css("z-index", "99");
				$("#inline_edit_marker").css("display", "none");
			  }
			);
		//positioneren
		var tmp = $(this).attr("pos").split(" ");
		for(var i = 0 ; i < tmp.length ; i++)
		{
			var offset = $(this).offset();
			switch(tmp[i])
			{
				case "top": offset.top = $(this).parent().offset().top; break;
				case "bottom": offset.top = $(this).parent().offset().top + $(this).parent().height() - $(this).height(); break;
				case "left": offset.left = $(this).parent().offset().left; break;
				case "right": offset.left = $(this).parent().offset().left + $(this).parent().width() - $(this).width(); break;
			}
			//if($(this).attr("corr_left") != undefined)
				offset.left += parseInt($(this).attr("corr_left"));
			$(this).offset(offset);
		}
		//de javascript aan de knopkes hangen
		$(this).children("img").each(function(){
			$(this).css("cursor", "pointer");
			$(this).hover(
				  function () {
					$(this).parent().children("span").text(" " + $(this).attr("help"));
					$(this).parent().css("z-index", "1000");
				  },
				  function () {
					$(this).parent().children("span").text("");
				  }
			);
			$(this).click(function(){
				inline_edit_load($(this).attr("type"), $(this).attr("button"), $(this).attr("data1"), $(this).attr("data2"), $(this).attr("data3"), $(this).attr("help"));
			});
		});
	});
	
	//nu alle ile nodes vinden voor echte inline edit
	$('ile').each(function(){
		//eerst kijken of het volgende element in het domdoc een img is, dan slaat het daarop
		var parentel = $(this).parent();
		
		if($(this).get(0).nextSibling != null)
		{
			if($(this).get(0).nextSibling.tagName)
			{
				if($(this).get(0).nextSibling.tagName.toLowerCase() == "img")
				{
					parentel = $($(this).get(0).nextSibling);
				}
			}
		}
		parentel.hover(
			  function () {
				$(this).css("filter", "alpha(opacity=50)");
				$(this).css("opacity", "0.5");
			  },
			  function () {
				$(this).css("filter", "alpha(opacity=100)");
				$(this).css("opacity", "1");
			  }
			);
		parentel.click(function(){
			if($(this).get(0).tagName.toLowerCase() == "img")
			{
				if(inline_edit_link_clicked_parent != null && inline_edit_link_clicked_parent.get(0) == $(this).get(0))
					inline_edit_fieldedit($($(this).get(0).previousSibling), inline_edit_link_clicked);
				else
					inline_edit_fieldedit($($(this).get(0).previousSibling), null);
			}
			else
			{
				if(inline_edit_link_clicked_parent != null && inline_edit_link_clicked_parent.get(0) == $(this).get(0))
					inline_edit_fieldedit($(this).children('ile'), inline_edit_link_clicked);
				else
					inline_edit_fieldedit($(this).children('ile'), null);
			}
		});
		//alle links zoeken in parentel en disabelen
		parentel.find("a").each(function(){
			$(this).click(function(e) {
				inline_edit_link_clicked = $(this).attr("href");
				inline_edit_link_clicked_target = $(this).attr("target");
				inline_edit_link_clicked_parent = parentel;
				e.preventDefault();
				//do other stuff when a click happens
			});
		})
	});
}

function inline_edit_reposition()
{
	$('div[inlinetoolbox="true"]').each(function(){
		//positioneren
		var tmp = $(this).attr("pos").split(" ");
		for(var i = 0 ; i < tmp.length ; i++)
		{
			var offset = $(this).offset();
			switch(tmp[i])
			{
				case "top": offset.top = $(this).parent().offset().top; break;
				case "bottom": offset.top = $(this).parent().offset().top + $(this).parent().height() - $(this).height(); break;
				case "left": offset.left = $(this).parent().offset().left; break;
				case "right": offset.left = $(this).parent().offset().left + $(this).parent().width() - $(this).width(); break;
			}
			//if($(this).attr("corr_left") != undefined)
				offset.left += parseInt($(this).attr("corr_left"));
			$(this).offset(offset);
		}
	});
}

//type = page, field, block
function inline_edit_load(type, action, data1, data2, data3, help)
{
	data1 = data1.replace(/_dblquot_/g, '"');
	data2 = data2.replace(/_dblquot_/g, '"');
	data3 = data3.replace(/_dblquot_/g, '"');
	help = help.replace(/_dblquot_/g, '"');
	if(document.getElementById("inline_edit_popup") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "inline_edit_popup");
		document.body.appendChild(newDiv);
		$('#inline_edit_popup').dialog({
					autoOpen: false,
					height: 500,
					width: 800,
					show: 'fade',
					modal: true
					});
	}
	$( "#inline_edit_popup").dialog( "option", "title", help );
	/*$( "#inline_edit_popup").dialog( "option", "buttons", [
		{
			text: "Close",
			click: function() { $(this).dialog("close"); }
		}
	] );*/
	//hiden van inline edits
	$("#inline_edit_editinline").css("display", "none");
	$( "#inline_edit_popup").dialog('open');
	cms2_show_loader("inline_edit_popup");
	
	$( "#inline_edit_popup").load('/ajax.php?sessid=' + encodeURI(session_id) + '&inline_edit=true&type=' + encodeURI(type) + '&action=' + encodeURI(action) + '&data1=' + encodeURI(data1) + '&data2=' + encodeURI(data2) + '&data3=' + encodeURI(data3));
}

function inline_edit_fieldedit(ile, link_clicked)
{
	if(document.getElementById("inline_edit_editinline") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "inline_edit_editinline");
		document.body.appendChild(newDiv);
		$("#inline_edit_editinline").css("display", "none");
		$("#inline_edit_editinline").css("border", "1px solid green");
		$("#inline_edit_editinline").css("background-color", "#FFFFFF");
		$("#inline_edit_editinline").css("z-index", "102");
		$("#inline_edit_editinline").css("position", "absolute");
		$("#inline_edit_editinline").attr("class", "ui-dialog");
		$("#inline_edit_editinline").css("width", "auto");
	}
	
	//offset.top += parseInt($(document).scrollTop());
	$("#inline_edit_editinline").css("display", "block");
	$("#inline_edit_editinline").text("Loading");
	$("#inline_edit_editinline").load('/ajax.php?sessid=' + encodeURI(session_id) + '&inline_edit=true&type=field&table=' + encodeURI(ile.attr("table")) + '&field=' + encodeURI(ile.attr("field")) + '&id_field=' + encodeURI(ile.attr("id_field")) + '&id_value=' + encodeURI(ile.attr("id_value")) + '&link_clicked=' + encodeURI(link_clicked) + '&link_clicked_target=' + encodeURI(inline_edit_link_clicked_target));
	
	//positioneren
	var parentel = ile.parent();
	if(ile.get(0).nextSibling != null)
	{
		if(ile.get(0).nextSibling.tagName)
		{
			if(ile.get(0).nextSibling.tagName.toLowerCase() == "img")
			{
				parentel = $(ile.get(0).nextSibling);
			}
		}
	}
	var offset = parentel.offset();
	$("#inline_edit_editinline").offset(offset);
}

function inline_edit_after_save()
{
	$("#main_inline_edit_div").append('<div inlinetoolbox="true" class="inline_edit" style="left:0px; top:0px; font-size: 16px;">Refreshing page ...</div>');
	$("#main_inline_edit_div").load(document.location.href + "/ajax_refresh");
}

function inline_edit_after_fieldsave(xmlHttp)
{
	if(xmlHttp.responseText == "OK")
	{
		$("#inline_edit_editinline").css("display", "none");
		inline_edit_after_save();
	}
	else
	{
		alert("The data could not be saved: " + xmlHttp.responseText);
	}
}

function inline_edit_save_data_item(form_id)
{
	window[form_id].savebutton = $("#inline_edit_data_savebutton");
	window[form_id].indialog = null;
	window[form_id].aftersave_success = "inline_edit_after_save";
	window[form_id].aftersave_data = null;
	window[form_id].post();
}

function inline_edit_aanuit(aanuit)
{
	if(document.getElementById("inline_edit_aanuitdiv") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "inline_edit_aanuitdiv");
		document.body.appendChild(newDiv);
		$("#inline_edit_aanuitdiv").css({'cursor': 'pointer', 'position':'absolute', 'z-index':'100', 'background-color':'#FFFFFF', 'font-family':'Arial, Helvetica, sans-serif', 'padding':'5px 5px 3px 5px', 'border':'2px solid green',	'-moz-border-radius':'12px', 'border-radius':'12px', 'top':'0px', 'right':'0px', 'font-weight':'bold'});
	}
	if(aanuit == "aan")
	{
		$("#inline_edit_aanuitdiv").text("Edit ON");
		$("#inline_edit_aanuitdiv").click(function(){
			send_ajax_request('GET', '/index_trigger_inlineedit.php?inline_edit=true', '', inline_edit_aanuit_return);
		});
	}
	else
	{
		$("#inline_edit_aanuitdiv").text("Edit OFF");
		$("#inline_edit_aanuitdiv").click(function(){
			send_ajax_request('GET', '/index_trigger_inlineedit.php?inline_edit=false', '', inline_edit_aanuit_return);
		});
	}
}

function inline_edit_aanuit_return()
{
	document.location.reload(true);
}