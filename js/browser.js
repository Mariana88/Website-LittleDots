var br_selected_folder = null;
var br_id = 0;
var br_root = "";
var br_clipboard = new Array();
var br_copyorcut = "";

//if the user selects a folder in the treeview
function br_select_folder(folder)
{
	br_selected_folder = folder;
	
	//enable icons
	theimg = document.getElementById('browser_folder_addfolder');
	theimg.onclick=function(e){br_show_newfolder_form(true);}
	theimg.style.cursor='pointer';
	theimg.src = '/css/back/icon/twotone/addfolder.gif';
	
	var tmp = folder.split('/');
	if(tmp[tmp.length-1] != "userfiles")
	{
		theimg = document.getElementById('browser_folder_delete');
		theimg.onclick=function(e)
			{
				br_delete_ask_folder();
				//show_question_message('Are you shure you want to remove the folder "' + br_selected_folder.replace(br_root, "") + '" with all its subfolders and files?', function(){br_delete_folder(); tb_remove();}, function(){tb_remove();});
			}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/trash.gif';
		
		
		
		theimg = document.getElementById('browser_folder_rename');
		theimg.onclick=function(e){br_rename_file(true);}
		theimg.style.cursor='pointer';
		theimg.src = '/css/back/icon/twotone/rename.gif';
		
		theimg = document.getElementById('browser_folder_rights');
		if(theimg != undefined)
		{
			theimg.onclick=function(e){alert("rename box");}
			theimg.style.cursor='pointer';
			theimg.src = '/css/back/icon/twotone/shield.gif';
		}
	}
	else
	{
		theimg = document.getElementById('browser_folder_delete');
		theimg.onclick=null;
		theimg.style.cursor='default';
		theimg.src = '/css/back/icon/twotone/gray/trash.gif';
		
		theimg = document.getElementById('browser_folder_rename');
		theimg.onclick=null;
		theimg.style.cursor='default';
		theimg.src = '/css/back/icon/twotone/gray/rename.gif';
		
		theimg = document.getElementById('browser_folder_rights');
		if(theimg != undefined)
		{
			theimg.onclick=null;
			theimg.style.cursor='default';
			theimg.src = '/css/back/icon/twotone/gray/shield.gif';
		}
	}
}

function br_disablefolder_icons()
{
	theimg = document.getElementById('browser_folder_addfolder');
	theimg.onclick=null;
	theimg.style.cursor='default';
	theimg.src = '/css/back/icon/twotone/gray/addfolder.gif';
	
	theimg = document.getElementById('browser_folder_delete');
	theimg.onclick=null;
	theimg.style.cursor='default';
	theimg.src = '/css/back/icon/twotone/gray/trash.gif';
	
	theimg = document.getElementById('browser_folder_rename');
	theimg.onclick=null;
	theimg.style.cursor='default';
	theimg.src = '/css/back/icon/twotone/gray/rename.gif';
	
	theimg = document.getElementById('browser_folder_rights');
	if(theimg != undefined)
	{
		theimg.onclick=null;
		theimg.style.cursor='default';
		theimg.src = '/css/back/icon/twotone/gray/shield.gif';
	}
}

function br_rename_file(fromfolderview)
{
	if(document.getElementById("br_rename_form") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "br_rename_form");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$(newDiv).html('<label style="width: 50px">Name:</label><input id="br_rename_name" value="" style="width: 200px">');
		$('#br_rename_form').dialog({
					autoOpen: false,
					height: 130,
					width: 280,
					show: 'fade',
					modal: true
					});
	}
	$( "#br_rename_form" ).dialog( "option", "title", "Rename File or Folder");
	$( "#br_rename_form" ).dialog( "option", "buttons", [
		{
			text: "Rename",
			click: function() { 
				var indir = "";
				if(fromfolderview)
				{
					send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=rename&oldname=' + encodeURI(br_selected_folder) + '&newname=' + encodeURI($("#br_rename_name").val()) + '&fromfolderview=true', '', br_handle_ajax_return_new);
				}
				else
				{
					send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=rename&oldname=' + encodeURI(dg_browser.selected_id) + '&newname=' + encodeURI($("#br_rename_name").val()) + '&fromfolderview=false', '', br_handle_ajax_return_new);
				}
			} 
		},
		{
			text: "Cancel",
			click: function() { 
				$(this).dialog("close"); 
			} 
		}
	] );
	if(fromfolderview)
	{
		var chomps = br_selected_folder.split('/'); 
		document.getElementById('br_rename_name').value = chomps[chomps.length - 1]; 
	}
	else
	{
		var chomps = dg_browser.selected_id.split('/'); 
		document.getElementById('br_rename_name').value = chomps[chomps.length - 1]; 
	}
	$( "#br_rename_form" ).dialog('open');
}

function br_show_newfolder_form(fromfolderview)
{
	if(document.getElementById("br_newfolder_form") == undefined)
	{
		var newDiv = document.createElement("div");
		newDiv.setAttribute("id", "br_newfolder_form");
		newDiv.style.display = 'none';
		document.body.appendChild(newDiv);
		$(newDiv).html('<label style="width: 50px">Name:</label><input id="br_newfolder_name" value="" style="width: 200px">');
		$('#br_newfolder_form').dialog({
					autoOpen: false,
					height: 130,
					width: 280,
					show: 'fade',
					modal: true
					});
	}
	$( "#br_newfolder_form" ).dialog( "option", "title", "Create New Folder");
	$( "#br_newfolder_form" ).dialog( "option", "buttons", [
		{
			text: "Create",
			click: function() { 
				var indir = "";
				if(fromfolderview)
					indir = br_selected_folder;
				else
					indir = "current";
				
				send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=addfolder&indir=' + encodeURI(indir) + '&dirname=' + encodeURI($("#br_newfolder_name").val()), '', br_handle_ajax_return_new);
			} 
		},
		{
			text: "Cancel",
			click: function() { 
				$(this).dialog("close"); 
			} 
		}
	] );
	$( "#br_newfolder_form" ).dialog('open');
}

function br_change_view(view)
{
	send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=changeview&view=' + view, '', br_change_view_return);
	//dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=browser&action=refresh');
}
function br_change_view_return(xmlHttp)
{
	dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=browser&action=refresh');
}

function br_littleUploadComplete(fname, fsize) 
{
	dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=browser&action=refresh');
}

//if a user selects a file in the file view
/*function br_select_file()
{
	
}*/

//if a user clicks on the trash button in the folder view
function br_delete_folder()
{
	//var poststr = "dirname=" + encodeURI(br_selected_folder);
	//br_send_ajax("POST", "delfolder=1", poststr);
	send_ajax_request('GET', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=delfolder&dirname=' + encodeURI(br_selected_folder), '', br_handle_ajax_return_new);
}

//if a user clicks on the shield button in the folder view
function br_shield_folder()
{
	alert('shield ' + br_selected_folder);
}

//if a user clicks on the addfolder button in the folder view
/*function br_addfolder_folder(sname)
{
	var poststr = "indir=" + encodeURI(br_selected_folder) +
			"&dirname=" + encodeURI(sname);

	br_send_ajax("POST", "addfolder=1", poststr);
}*/

/*function br_renamefile(sname)
{
	var poststr = "newname=" + encodeURI(sname) + "&oldname=" + encodeURI(dg_browser.selected_id);
	br_send_ajax("POST", "renamefile=1", poststr);
}*/

//function that is used to request things to the server
function br_send_ajax(method, urlstr, postvars)
{
  var xmlHttp;
  try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
  catch (e)
  {
  // Internet Explorer
    try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      try
      {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e)
      {
      alert("Your browser does not support AJAX!");
      return false;
      }
    }
  }
  
  
  xmlHttp.onreadystatechange=function()
  {
    if(xmlHttp.readyState==4)
    {
    	//alert(xmlHttp.responseText);
		br_handle_ajax_return(xmlHttp);
    }
	else
	{
		
	}
  }
  //alert("ajax.php?getdata=" + type + "&id=" + id);
  if(method == "POST")
  {
  	  xmlHttp.open('POST', "/ajax.php?popup_id=" + br_id + "&" + urlstr, true);
      xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xmlHttp.setRequestHeader("Content-length", postvars.length);
      xmlHttp.setRequestHeader("Connection", "close");
      xmlHttp.send(postvars);
  }
  else
  {
  	xmlHttp.open('GET',"/ajax.php?popup_id=" + br_id + "&" + urlstr,true);
    xmlHttp.send(null);
  }
}

function br_handle_ajax_return_new(xmlHttp)
{
	//alert(xmlHttp.responseText);
	doc = $(xmlHttp.responseXML);
	switch(doc.find("browser").attr("action"))
	{
		case "addfolder":
			if(doc.find("status").text() == "OK")
			{
				tree_addnode('treeview_browser', doc.find("content").text(), "foldertree_" + doc.find("parentdir").text(), "foldertree_" + doc.find("dirname").text(), true);
				//br_select_folder(doc.find("dirname").text());
				$(document.getElementById("foldertree_" + doc.find("dirname").text())).children("div").click();
				dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&loaddir=' + encodeURI(doc.find("dirname").text()));
				var tmparr = doc.find("dirname").text().split("userfiles");
				document.getElementById("br_current_location_span").innerHTML = '/root' + tmparr[1];
				$('#br_newfolder_form').dialog("close");
			}
			else if(doc.find("status").text() == "NOK")
			{
				alert(doc.find("error").text());
			}
			break;
		case "rename":
			if(doc.find("status").text() == "OK")
			{
				var oldpath = doc.find("oldpath").text();
				var newpath = doc.find("newpath").text();
				var newname = doc.find("newname").text();
				var currentfolder = doc.find("currentfolder").text();
				var fromfolderview = ((doc.find("fromfolderview").text() == "true")?true:false);
				//datagrid refreshen
				var path_from_root = newpath.replace("//", "/");
				var tmp = path_from_root.split("userfiles");
				path_from_root = "/root" + tmp[1];
				if(currentfolder == oldpath)
				{
					dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&loaddir=' + encodeURI(newpath));
					document.getElementById('br_current_location_span').innerHTML = path_from_root;
				}
				else
					dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=browser&action=refresh');
				
				//treeview aanpassen
				var the_li = document.getElementById("foldertree_" + oldpath);
				if(the_li !== null)
				{
					$(the_li).children("div").html('<img src="/css/back/icon/file/mini/folder.gif"/>' + newname);
					$(the_li).children("div").get(0).onclick = function(e){
							select_me_please('treeview_browser', this); 
							br_select_folder(newpath);
						}
					
					$(the_li).children("div").get(0).ondblclick = function(e){
						dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&loaddir=' + encodeURI(newpath));
						//dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&popup_id=browser&loaddir=' + encodeURI(newpath.replace("//", "/")));
						document.getElementById('br_current_location_span').innerHTML = path_from_root;
					}
					$(the_li).attr("id", "foldertree_" + newpath);
					$(the_li).attr("folder", newpath);
				}
				//als de folder geselecteerd was dan even aanpassen
				if(br_selected_folder == oldpath)
					br_select_folder(newpath);
				$('#br_rename_form').dialog("close");
			}
			else if(doc.find("status").text() == "NOK")
			{
				alert(doc.find("error").text());
			}
			break;
		case "delfolder":
			if(doc.find("status").text() == "OK")
			{
				the_li = document.getElementById("foldertree_" + doc.find("folder").text());
				$(the_li).remove();
				br_disablefolder_icons();
				//checken of de huidige folder is geladen
				var path_from_root = doc.find("folder").text().replace("//", "/");
				var tmp = path_from_root.split("userfiles");
				path_from_root = "/root" + tmp[1];
				if(document.getElementById('br_current_location_span').innerHTML == path_from_root)
				{
					$("#treeview_browser").children("li").first().children("div").get(0).onclick();
					$("#treeview_browser").children("li").first().children("div").get(0).ondblclick();
				}
			}
			else if(doc.find("status").text() == "NOK")
			{
				alert(doc.find("error").text());
			}
			break;
		case "delfiles":
			doc.find("folder").each(function (){
					the_li = document.getElementById("foldertree_" + $(this).text());
					if(the_li != undefined)
						$(the_li).remove();
				});
			dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=browser&action=refresh');
			$( "#cms2_message_div" ).dialog('close');
			break;
		case "pastefiles":
			var copyorcut = doc.find("copyorcut").text();
			document.getElementById("browser_clipboard_div").innerHTML = '<div style="text-align:center">Ready</div>';
			dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&dg_id=browser&action=refresh');
			//de tree aanpassen
			doc.find("folder").each(function (){
					tree_addnode('treeview_browser', $(this).find("content").text(), "foldertree_" + $(this).find("parentdir").text(), "foldertree_" + $(this).find("newid").text(), true);
					if(copyorcut == "cut")
					{
						the_li = document.getElementById("foldertree_" + $(this).find("oldid").text());
						if(the_li != undefined)
							$(the_li).remove();
					}
				});
			break;
	}
}

function br_handle_ajax_return(xmlHttp)
{
	//alert(xmlHttp.responseText);
	
	var ret_req = xmlHttp.responseText.substring(0,12);
	var ret_code = xmlHttp.responseText.substring(12,16);
	var ret_text = xmlHttp.responseText.substring(16);
	
	
}

function get_parameters(sparam, stext)
{
	var splitted = stext.split("#");
	var i = 0;
	for(i = 0 ; i < splitted.length ; i++)
	{
		var split2 = splitted[i].split(";;");
		if(sparam == split2[0])
			return split2[1];
	}
	return "";
}

function br_delete_file()
{
	cms2_show_question_message('Are you sure you want to delete all selected files and folders?', 'Delete files', br_delete_file_accept, function(){$( "#cms2_message_div" ).dialog('close');})
	//show_question_message('Are you sure you want to delete all selected files and folders?', br_delete_file_accept, tb_remove);
}

function br_delete_file_accept()
{
	//var poststr = "delfile=" + encodeURI(dg_browser.selected_ids);
	//br_send_ajax("POST", "delfile=1", poststr);
	//alert(dg_browser.selected_ids);
	send_ajax_request('POST', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=delfiles', 'delfile=' + encodeURI(dg_browser.selected_ids), br_handle_ajax_return_new);
}

function br_delete_ask_folder()
{
	cms2_show_question_message('Are you sure you want to delete the selected folder?', 'Delete folder', function(){br_delete_folder(); $( "#cms2_message_div" ).dialog('close');}, function(){$( "#cms2_message_div" ).dialog('close');})
	//show_question_message('Are you sure you want to delete all selected files and folders?', br_delete_file_accept, tb_remove);
}

function br_dblclick(path)
{
	//als het een folder is, dan gaan we dieper
	var chomps = path.split(".");
	if(chomps.length<=1 || chomps[chomps.length - 1].length > 4)
	{
		nodeid = "foldertree_" + path;
		/*if(document.getElementById(nodeid) == undefined)
			nodeid = "foldertree_" + path.replace("//", "/");*/
		//alert(nodeid);
		
		tree_select_node("treeview_browser", nodeid);
		
		br_select_folder(path);
		dg_browser_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&loaddir=' + encodeURI(path));
		var tmparr = path.split("userfiles");
		document.getElementById("br_current_location_span").innerHTML = '/root' + tmparr[1];
	}
	else
	{
		if(window.browserinput != undefined)
		{
			if(window.browserinput.getAttribute("pathinput") == "true")
			{
				tmppath = path.split('userfiles/');
				window.browserinput.value = '/userfiles/' + tmppath[1];
				window.close();
			}
			else
				send_ajax_request('POST', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=getfileid', 'filepath=' + encodeURI(path), br_dblclick_browserinput);
		}
	}
}

function br_dblclick_browserinput(xmlHttp)
{
	window.browserinput.value = "/bestand_front.php?file_id=" + xmlHttp.responseText;
	if(window.browserinput.onfilefieldchange != null && window.browserinput.onfilefieldchange != undefined) 
		window.browserinput.onfilefieldchange(); 
	window.close();
}


function br_download()
{
	//als het een folder is, dan gaan we dieper

	var chomps = dg_browser.selected_id.split(".");
	
	if(chomps.length>1 || chomps[chomps.length - 1].length <= 4)
	{
		window.open('/bestand.php?path=' + encodeURI(dg_browser.selected_id), '');
	}
}

function br_fileoptions()
{
	var chomps = dg_browser.selected_id.split(".");
	
	if(chomps.length>1 || chomps[chomps.length - 1].length <= 4)
	{
		cms2_open_file_options(dg_browser.selected_id);
	}
}

function br_cut_files()
{
	br_copyorcut = "cut";
	br_clipboard = new Array();
	var the_filenames = dg_browser.selected_ids.split('##');
	var html = "";
	for(var i = 0 ; i < the_filenames.length ; i++)
	{
		var tmparr = the_filenames[i].split("userfiles");
		br_clipboard[i] = the_filenames[i];
		//we get the image url
		var imgurl = document.getElementById(the_filenames[i]).getElementsByTagName("img")[0].getAttribute("src");
		html += '<img src="' + imgurl + '" style="float: left; margin-right: 4px;"><div style="overflow:hidden;">/root' + tmparr[1].replace('//', '/') + '</div><hr style="clear:both;">';
	}
	
	document.getElementById("browser_clipboard_div").innerHTML = '<div style="margin-bottom: 8px; text-align:center;">Cutted files</div>' + html;
}

function br_copy_files()
{
	br_copyorcut = "copy";
	br_clipboard = new Array();
	var the_filenames = dg_browser.selected_ids.split('##');
	var html = "<ul>";
	for(var i = 0 ; i < the_filenames.length ; i++)
	{
		var tmparr = the_filenames[i].split("userfiles");
		br_clipboard[i] = the_filenames[i];
		//we get the image url
		var imgurl = document.getElementById(the_filenames[i]).getElementsByTagName("img")[0].getAttribute("src");
		html += '<img src="' + imgurl + '" style="float: left; margin-right: 4px;">/root' + tmparr[1].replace('//', '/') + '<hr style="clear:both;">';
	}
	html += "</ul>";
	
	document.getElementById("browser_clipboard_div").innerHTML = '<div style="margin-bottom: 8px; text-align:center;">Copied files</div>' + html;
}

function br_paste_files()
{
	if(br_clipboard.length > 0)
	{
		document.getElementById("browser_clipboard_div").innerHTML = '<div style="text-align:center">Pasting files</div>';
		var poststr = "";
		for(var i = 0 ; i < br_clipboard.length ; i++)
		{
			if(poststr != "")
				poststr += "##" + br_clipboard[i];
			else
				poststr += br_clipboard[i];
		}
		poststr = "pastefiles=" + poststr + "&copyorcut=" + br_copyorcut;
		send_ajax_request('POST', '/ajax.php?sessid=' + session_id + '&popup_id=' + br_id + '&action=pastefiles', poststr, br_handle_ajax_return_new);

		//br_send_ajax("POST", "pastefiles=1", poststr);
	}
	else
	{
		document.getElementById("browser_clipboard_div").innerHTML = '<div style="text-align:center">No files to paste</div>';
	}
}

function br_addbutton()
{
	if(window.browserinput != null && window.browserinput != undefined)
	{
		var chomps = dg_browser.selected_id.split('.');
		if(chomps.length>1 && chomps[chomps.length - 1].length <= 4)
		{
			var chomps = dg_browser.selected_id.split('/userfiles/');
			var value = encodeURI('/userfiles/' + chomps[chomps.length - 1]);
			window.browserinput.value = value; 
			if(window.browserinput.onfilefieldchange != null && window.browserinput.onfilefieldchange != undefined)
				window.browserinput.onfilefieldchange(); 
			window.close();
		}
	}
	if(window.piccol != null && window.piccol != undefined)
	{
		var ids = dg_browser.selected_ids.split('##');
		for(var i = 0 ; i < ids.length ; i++)
		{
			var chomps = ids[i].split('.');
			if(chomps.length>1 && chomps[chomps.length - 1].length <= 4)
				window.piccol.addpic_path(ids[i])
		}
		window.close();
	}
	if(window.browser_piccol != null && window.browser_piccol != undefined)
	{
		var ids = dg_browser.selected_ids.split('##');
		var thestring = "";
		for(var i = 0 ; i < ids.length ; i++)
		{
			var chomps = ids[i].split('.');
			if(chomps.length>1 && chomps[chomps.length - 1].length <= 4)
			{
				if(thestring == "")
					thestring = ids[i];
				else
					thestring += "__splitter__" + ids[i];
			}
		}
		if(thestring != "")
			dataeditor_addbypic(window.browser_piccol, thestring);
		else
			alert("Select at least one file to add");
	}
}