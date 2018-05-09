/*------------------------ Conextmenu ---------------------------

een context menu

*/

(function( $ ){
  
  var methods = {
		init : function(settings) { 
			//var settings = $.extend( {'actions': {}}, settings);
			//CONTRACT
			$("#newBrowse_btn_contract").click(function(){
				if(!$(this).hasClass("disabled"))
				{
					ddtreemenu.flatten('treeview_browser', 'contract');	
				}
			});
			//EXPAND
			$("#newBrowse_btn_expand").click(function(){
				if(!$(this).hasClass("disabled"))
				{
					ddtreemenu.flatten('treeview_browser', 'expand');	
				}
			});
			//FILEINFO
			$("#newBrowse_btn_fileinfo").click(function(){
				if(!$(this).hasClass("disabled"))
				{
					if($("#newBrowse_optionbox .fileinfo").is(":visible"))
					{
						$("#newBrowse_optionbox .fileinfo").hide();
						var newHeight = 32;
						if($('#newBrowse_upload_progress').is(":visible"))
							 newHeight = 50;
						$('#newBrowse_optionbox').parent().css("height", newHeight + "px");
						$("#sidebar_right .accordion").blicsmAccordion("resize");
					}
					else
					{
						$("#newBrowse_optionbox .fileinfo").show();
						var newHeight = 178;
						if($('#newBrowse_upload_progress').is(":visible"))
							 newHeight = 196;
						$('#newBrowse_optionbox').parent().css("height", newHeight + "px");
						$("#sidebar_right .accordion").blicsmAccordion("resize");
						
						//laden van de selectie
						$("#treeview_browser").newBrowse("loadFileInfo");
					}
				}
			});
			
			//DELETE
			$("#newBrowse_btn_delete").click(function(e){
				if(!$(this).hasClass("disabled"))
				{
					$.blicsmPopup({'id': 'ppBrowseDelete', 
								  'buttons': {
									  'Yes': function(){
										  		$("#treeview_browser").newBrowse("deleteSelectedFilesAndFolders"); $.blicsmPopup("closePopup", "ppBrowseDelete");
										},
									  'No': function(){
										  		$.blicsmPopup("closePopup", "ppBrowseDelete")
										}
									},
									'colorClass': 'fel',
									'html': 'Are you sure?',
									'autoDissappear': 5,
									'clickEvent': e,
									'parentSelector': '#sidebar_right'});
				}
			});
			
			//UPLOAD
			$("#newBrowse_btn_upload").click(function(e){
				if(!$(this).hasClass("disabled"))
				{
					return true;
				}
				else
					e.isDefaultPrevented();
			});
			
			//NEW FOLDER
			$("#newBrowse_btn_newfolder").click(function(e){
				if(!$(this).hasClass("disabled"))
				{
					$.blicsmPopup({'id': 'ppBrowseNewFolder', 
								  'buttons': {
									  'Create folder': function(){
										  		//checken of de value goed is
												var value = $("#ppBrowseNewFolder").find('input').val();
										  		$("#treeview_browser").newBrowse("createFolder", value); 
												$.blicsmPopup("closePopup", "ppBrowseNewFolder");
										},
									  'Cancel': function(){
										  		$.blicsmPopup("closePopup", "ppBrowseNewFolder")
										}
									},
									'colorClass': 'fel',
									'html': '<input style="width: 200px;" type="text"/>',
									'clickEvent': e,
									'parentSelector': '#sidebar_right',
									'onOpened': function(){
													$("#ppBrowseNewFolder").find('input').focus();
													//enkel letters en cijfers graag
													$("#ppBrowseNewFolder").find('input').keypress(function (e) {
														//$("#ppBrowseNewFolder .blicsmPopup_content").append(e.keyCode + '<br>');
														if(e.keyCode == 13)
														{
															$("#ppBrowseNewFolder").get(0).settings["buttons"]["Create folder"]();
															return true;	
														}
														if(e.charCode >= 79 && e.charCode <= 122 ||
														   e.charCode >= 65 && e.charCode <= 90 || 
														   e.charCode >= 48 && e.charCode <= 57 ||
														   e.charCode == 32 || e.charCode == 95 || e.charCode == 45 || e.charCode == 0) {
															return true;
														}
							
														e.preventDefault();
														return false;
														
													});
												}
									});
				}
			});
			
			
			//FILE UPLOADER
			$(function () {
				// Change this to the location of your server-side upload handler:
				var url = '/plugins/fileUpload/server/php/';
				$('#fileupload').fileupload({
					url: url,
					dataType: 'json',
					start: function (e, data){
						$('#newBrowse_upload_progress').show(200);
						var newHeight = 50;
						if($('#newBrowse_optionbox .fileinfo').is(":visible"))
							 newHeight = 196;
						$('#newBrowse_optionbox').parent().css("height", newHeight + "px");
						$("#sidebar_right .accordion").blicsmAccordion("resize");
					},
					done: function (e, data) {
						var uploadreturn = jQuery.parseJSON(data.jqXHR.responseText);
						var url_file = uploadreturn.files[0].url.split('userfiles');
						url_file = "/userfiles" + url_file[1];
						var tmp = url_file.split('/');
						var url_folder = '';
						for(var i = 0 ; i < (tmp.length - 1); i++)
						{
							if(tmp[i] != "")
								url_folder += '/' + tmp[i];
						}
						$("#treeview_browser").newBrowse("addFileNode", decodeURI(url_folder), decodeURI(url_file), false);
					},
					fail: function (e, data) {
						alert("something went wrong");
						alert(data.jqXHR.responseText);
					},
					progressall: function (e, data) {
						var progress = parseInt(data.loaded / data.total * 100, 10);
						$('#newBrowse_upload_progress .progress-bar').css(
							'width',
							progress + '%'
						);
					},
					stop: function(e, data){
						$('#newBrowse_upload_progress').hide(200);
						var newHeight = 32;
						if($('#newBrowse_optionbox .fileinfo').is(":visible"))
							 newHeight = 178;
						$('#newBrowse_optionbox').parent().css("height", newHeight + "px");
						$("#sidebar_right .accordion").blicsmAccordion("resize");
					}
				}).prop('disabled', !$.support.fileInput)
					.parent().addClass($.support.fileInput ? undefined : 'disabled');
			});
			
			
			$(this).find("li > div").click(function(evt){
				
				$("#treeview_browser").newBrowse("treeNodeClick", $(this), evt);
				
			});
			
			//DRAGABLE
			$(this).find("li > div").draggable({
						revert: true,
						helper: "clone"
			});
		},
		
		getSelection : function(settings) { 
			//var settings = $.extend( {'actions': {}}, settings);
			var selected = Array();
			var counter = 0;
			$(this).find("li.selected").each(function(){
				selected[counter] = {"path": $(this).attr("path"), "folder": $(this).attr("is_folder"), "is_root": $(this).children("div").first().hasClass("media_root")};
				counter++;
			});
			return selected;
		},
		
		addFileNode : function(folder, file, is_folder) { 
			//we zoeken naar de node
			var theparent = $("#treeview_browser").find('li[path="' + folder + '"]');
			if(theparent.children('ul').length <= 0)
				tree_addsubmenutonode(theparent.get(0));
			
			var chunks = file.split('/');
			var the_li = $('<li id="foldertree_' + file + '" path="' + file + '" is_folder="' + ((is_folder)?'true':'false') + '"><div style="" tree_id="treeview_browser">' + chunks[chunks.length-1] + '</div></li>');
			theparent.children('ul').first().append(the_li);
			
			//laden van icon_path
			$.ajax({
				url: '/ajax.php?sessid=' + session_id + '&newBrowse=1&action=get_icon_path_and_info&path=' + encodeURI(file),
					success: function(data, textStatus, jqXHR){
						var doc = $(jqXHR.responseXML);
						var the_li = $("#treeview_browser").find('li[path="' + doc.find('path').text() + '"]');
						the_li.children('div').first().css('background-image', 'url(' + doc.find('img').text() + ')');
						the_li.children('div').first().attr("browserfileid", doc.find('browserfileid').text());
						the_li.children('div').first().attr("browsertype", doc.find('browsertype').text());
						the_li.children('div').first().attr("browserextension", doc.find('browserextension').text());
						the_li.children('div').first().attr("browserfile", doc.find('browserfile').text());
					}
			});
			
			the_li.children("div").click(function(evt){
				$("#treeview_browser").newBrowse("treeNodeClick", $(this), evt);
			});
			the_li.children("div").first().draggable({
						revert: true,
						helper: "clone"
			});
			ddtreemenu.expandSubTree("treeview_browser", theparent.children('ul').get(0));
			the_li.children("div").click();
		},
		
		orderTree : function(parent_path){
			
		},
		
		deleteSelectedFilesAndFolders: function(){
			//create xml doc with files
			var xmlDoc = $.parseXML('<newBrowseDeleteItems/>');
			var selected = $("#treeview_browser").newBrowse("getSelection");
			for(var i = 0 ; i < selected.length ; i++)
			{
				$(xmlDoc).find("newBrowseDeleteItems").append('<file folder="' + ((selected[i]["folder"] == "true")?'1':'0') +  '"></file>');
				$(xmlDoc).find("newBrowseDeleteItems").children('file').last().text(selected[i]["path"]);
			}
			$.ajax({
				url: '/ajax.php',
				type: 'GET',
    			data: {'sessid': session_id, 'newBrowse': 1, 'action': 'delete', 'xml': escape(xmlToString(xmlDoc))}, 
    			contentType: "text",
    			dataType: "xml",
    			success : function(data, textStatus, jqXHR){
						//alert(jqXHR.responseText);
						$(jqXHR.responseXML).find("file").each(function(){
							$("#treeview_browser").find('li[path="' + $(this).text() + '"]').hide(200, function(){
/*								if($(this).parent().children('li').length == 1)
									$(this).parent().remove();
								else
*/									$(this).remove();
								
							});										
						});
						$("#newBrowse_btn_delete").add("#newBrowse_btn_newfolder").add("#newBrowse_btn_upload").addClass("disabled");
						$('#fileupload').attr("disabled", "disabled");
					},
				error: function(jqXHR, textStatus, errorThrown)
					{
						alert(jqXHR.responseText);
					}
			})
		},
		
		createFolder : function(foldername)
		{
			//checken of er nog een folder is geselecteerd
			var selected = $("#treeview_browser").newBrowse("getSelection");
			if(selected.length == 1 && selected[0]["folder"] == "true")
			{
				var xmlDoc = $.parseXML('<newBrowseCreateFolder/>');
				$(xmlDoc).find("newBrowseCreateFolder").append('<parentPath></parentPath>');
				$(xmlDoc).find("newBrowseCreateFolder").children('parentPath').last().text(selected[0]["path"]);
				$(xmlDoc).find("newBrowseCreateFolder").append('<name></name>');
				$(xmlDoc).find("newBrowseCreateFolder").children('name').last().text(foldername);

				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'newBrowse': 1, 'action': 'newFolder', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "xml",
					success : function(data, textStatus, jqXHR){
							$("#treeview_browser").newBrowse("addFileNode", $(jqXHR.responseXML).find("parentPath").text(), $(jqXHR.responseXML).find("path").text(), true);
						},
					error: function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);
						}
				});
			}
		},
		
		treeNodeClick : function(node, evt) {
			if(!evt.ctrlKey && !evt.shiftKey)
			{
				if(node.parent().hasClass("selected"))
				{
					//start rename
					if(node.find('input[name="newBrowseRename"]').length <= 0)
					{
						
						var the_input = $('<input type="text" style="width: 200px; height: 14px; padding-top: 0px;" name="newBrowseRename"/>');
						the_input.val(node.text().split('.')[0]);
						node.attr("oldname", node.text());
						node.text('');
						node.prepend(the_input);
						the_input.focus();
						the_input.select();
						//enkel letters en cijfers graag
						the_input.keypress(function (e) {
							//$("#ppBrowseNewFolder .blicsmPopup_content").append(e.keyCode + '<br>');
							if(e.keyCode == 13)
							{
								if($(this).val().trim() != "")
								{
									var newName = $(this).val();
									if($(this).parent().attr("oldname").split('.').length > 1)
										newName =  $(this).val() + '.' + $(this).parent().attr("oldname").split('.')[1];
									
									$(this).parent().attr("oldname", newName);
									
									var xmlDoc = $.parseXML('<newBrowseRename/>');
									$(xmlDoc).find("newBrowseRename").append('<path></path>');
									$(xmlDoc).find("newBrowseRename").children('path').last().text($(this).parent().parent().attr("path"));
									$(xmlDoc).find("newBrowseRename").append('<newname></newname>');
									$(xmlDoc).find("newBrowseRename").children('newname').last().text(newName);
					
									$(this).parent().text(newName);
									$(this).remove();
									
									$.ajax({
										url: '/ajax.php',
										type: 'GET',
										data: {'sessid': session_id, 'newBrowse': 1, 'action': 'renameFile', 'xml': escape(xmlToString(xmlDoc))}, 
										contentType: "text",
										dataType: "xml",
										success : function(data, textStatus, jqXHR){
												var the_li = $("#treeview_browser").find('li[path="' + $(jqXHR.responseXML).find("path").text() + '"]');
												the_li.attr('path', $(jqXHR.responseXML).find("newpath").text()).children('div').first().text($(jqXHR.responseXML).find("file").text());
												//als het een folder was -> alle kinderen aanpassen
												var rename_search = $(jqXHR.responseXML).find("path").text();
												var rename_replace = $(jqXHR.responseXML).find("newpath").text();
												if(the_li.find("li").length > 0)
												{
													the_li.find("li").each(function(){
															$(this).attr("path", $(this).attr("path").replace(rename_search, rename_replace));						
													});
												}
												the_li.removeClass("selected");
												the_li.children('div').click();
											},
										error : function(jqXHR, textStatus, errorThrown)
											{
												alert(jqXHR.responseText);	
											}
									});
									
									
									return true;
								}
							}
							if(e.charCode >= 79 && e.charCode <= 122 ||
							   e.charCode >= 65 && e.charCode <= 90 || 
							   e.charCode >= 48 && e.charCode <= 57 ||
							   e.charCode == 32 || e.charCode == 95 || e.charCode == 45 || e.charCode == 0) {
								return true;
							}
							e.preventDefault();
							return false;
						});
						
						the_input.blur(function(e){
							the_input.parent().text(the_input.parent().attr("oldname"));
							$(this).remove();
						});
					}
				}
				$("#treeview_browser").find("li.selected").removeClass("selected");
				node.parent().addClass("selected");
			}
			else
			{
				if(node.parent().hasClass("selected"))
					node.parent().removeClass("selected");
				else
					node.parent().addClass("selected");
			}
			
			var selected = $("#treeview_browser").newBrowse("getSelection");
			
			//nu buttons en of disabelen
			//upload en new folder, kan enkel als slechts één folder is geselecteerd
			if(selected.length == 1 && selected[0]["folder"] == "true")
			{
				$("#newBrowse_btn_upload").removeClass("disabled");
				$('#fileupload').removeAttr("disabled");
				$("#newBrowse_btn_newfolder").removeClass("disabled");
				//opslaan van welke folder
				$.ajax({
					url: '/ajax.php?sessid=' + session_id + '&newBrowse=1&action=last_selected_folder&folder=' + encodeURI(selected[0]["path"]),
					success: function(data, textStatus, jqXHR){
						//alert(jqXHR.responseText);
					}
				});
			}
			else
			{
				$("#newBrowse_btn_upload").addClass("disabled");
				$('#fileupload').attr("disabled", "disabled");
				$("#newBrowse_btn_newfolder").addClass("disabled");
			}
			
			//remove -> vanaf dat er minstens één is geselecteerd
			if(selected.length >= 1)
			{
				//eerst even checken of er geen root node is geselecteerd
				var rootNodeSelected = false;
				for(var i = 0 ; i < selected.length ; i++)
				{
					if(selected[i]["is_root"])
					{
						rootNodeSelected = true;
						break;
					}
				}
				if(!rootNodeSelected)
					$("#newBrowse_btn_delete").removeClass("disabled");
				else
					$("#newBrowse_btn_delete").addClass("disabled");
			}
			else
			{
				$("#newBrowse_btn_delete").addClass("disabled");
			}
			$("#treeview_browser").newBrowse("loadFileInfo");
		},
		
		loadFileInfo : function(){
			if($("#newBrowse_optionbox .fileinfo").is(":visible"))
			{
				var selected = $("#treeview_browser").newBrowse("getSelection");	
				if(selected.length != 1)
				{
					$("#newBrowse_optionbox .fileinfo").html('<div class="text-align: center">Select one file to view the info</div>');
				}
				else
				{
						$("#newBrowse_optionbox .fileinfo").html('<div class="text-align: center"><img src="/css/back/loading.gif"/></div>');
						var xmlDoc = $.parseXML('<newBrowseFileInfo/>');
						$(xmlDoc).find("newBrowseFileInfo").append('<path></path>');
						$(xmlDoc).find("newBrowseFileInfo").children('path').last().text(selected[0]["path"]);
						
						$.ajax({
							url: '/ajax.php',
							type: 'GET',
							data: {'sessid': session_id, 'newBrowse': 1, 'action': 'fileInfo', 'xml': escape(xmlToString(xmlDoc))}, 
							contentType: "text",
							dataType: "html",
							success : function(data, textStatus, jqXHR){
									$("#newBrowse_optionbox .fileinfo").html(data);
								},
							error : function(jqXHR, textStatus, errorThrown)
								{
									alert(jqXHR.responseText);	
								}
						});
				}
			}
		}
  };

  
  $.fn.newBrowse = function(method) {
		
		// Method calling logic
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.newBrowse' );
		}    
		
		return this;
  };
  
})( jQuery );