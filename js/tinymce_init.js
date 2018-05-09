	/*
		CONFIG VAN TINYMCE
		0 = Standaard = textpage/about;
		1 = event
		2 = post
		3 = newsletter
	*/
	var mce_config_array = [{
								mode : "none",
								theme : "advanced",
								skin : "o2k7",
								skin_variant : "silver",
								plugins : "safari, paste, inlinepopups, media",
								editor_selector : "tinymce_' . $postname . '",
								content_css : "/css/front/styles.css",
								theme_advanced_toolbar_location : "top",
								theme_advanced_toolbar_align : "left",
								theme_advanced_statusbar : false,
								theme_advanced_buttons1 : "bold,italic,sub,sup,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,link,unlink,code",
								theme_advanced_buttons2 : "styleselect,|,bullist,numlist,|,hr,removeformat,|,charmap,|,media,images,video,audio",
								theme_advanced_buttons3 : "",
								theme_advanced_blockformats : "p,h3,h4",
								width : "362",
								relative_urls : true,
								convert_urls : false,
								paste_text_sticky : true,
								setup : function(ed) {
									ed.onInit.add(function(ed) {
									  ed.pasteAsPlainText = true;
									});
									ed.onClick.add(function(ed, e) {
										//ed.selection.select(ed.dom.select('div.fotogallery'));
										var alldivs = ed.dom.select('div');
										for(var i = 0 ; i < alldivs.length ; i++)
											$(alldivs[i]).removeClass("editorselected");
										if($(e.target).hasClass("fotogallery"))
										{
											ed.selection.select(e.target);
											$(e.target).addClass("editorselected");
										}
									});
									ed.addButton('images', {
											title : 'Insert Images',
											image : 'http://www.deberengieren.be/css/back/img/editor_image.gif',
											onclick : function() {
												// Add you own code to execute something on click
												var format_img_window = window.open("/tinymceaddons.php?type=images&page_id=" + $("#site_page_id").val(), "Insert Image", "location=1,status=1,scrollbars=1, width=1000,height=800");
												format_img_window.tinymce_editor = ed;
											}});
									ed.addButton('video', {
											title : 'Insert video',
											image : 'http://www.deberengieren.be/css/back/img/editor_video.gif',
											onclick : function() {
												// Add you own code to execute something on click
												var format_img_window = window.open("/tinymceaddons.php?type=video&page_id=" + $("#site_page_id").val(), "Insert Image", "location=1,status=1,scrollbars=1, width=1000,height=800");
												format_img_window.tinymce_editor = ed;
											}});
									ed.addButton('audio', {
											title : 'Insert audio',
											image : 'http://www.deberengieren.be/css/back/img/editor_audio.gif',
											onclick : function() {
												// Add you own code to execute something on click
												var format_img_window = window.open("/tinymceaddons.php?type=audio&page_id=" + $("#site_page_id").val(), "Insert Image", "location=1,status=1,scrollbars=1, width=1000,height=800");
												format_img_window.tinymce_editor = ed;
											}});
									}
									
							}];

