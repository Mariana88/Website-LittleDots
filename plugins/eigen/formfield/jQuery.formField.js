/*------------------------ Formfield!! ---------------------------

een context menu

*/

(function( $ ){
  
  var methods = {
		init : function(settings) { 
			$(this).each(function(){
				if($(this).attr("blicsmFormFieldInitialized") != "true")
				{
					if($(this).attr("blicsmtype") == "PICTURE")
					{
						$(this).blicsmFormField("picture", {"action": "init"});
					}
					if($(this).attr("blicsmtype") == "AUDIO")
					{
						$(this).blicsmFormField("audio", {"action": "init"});
					}
					if($(this).attr("blicsmtype") == "FILE")
					{
						$(this).blicsmFormField("file", {"action": "init"});
					}
					if($(this).attr("blicsmtype") == "VIDEOLINK")
					{
						$(this).blicsmFormField("videolink", {"action": "init"});
					}
					if($(this).attr("blicsmtype") == "LINK")
					{
						$(this).blicsmFormField("link", {"action": "init"});
					}
					
					$(this).attr("blicsmFormFieldInitialized", "true");
				}
			});
		},
		
		getData : function(){
			
		},
		
		setData : function(){
			
		},
		//---------------------------------PICTURE-------------------------------------------
		picture: function(settings)
		{
			if(settings["action"] == "init")
			{
				$(this).wrap('<div id="' + $(this).attr('id') + '_pic" style="float: left; width: 575px; padding: 2px; margin-bottom: 4px;"></div>');
					$(this).parent().append('<div class="field_display"></div>');
					$(this).hide();
					if($(this).val() == "" || $(this).val() == "0")
					{
						$(this).parent().find(".field_display").text("Drag a file from the right");
					}
					else
					{
						//$(this).each(function(){
							$(this).blicsmFormField("picture", {"action":"load"});				  
						//})
					}
					$(this).parent().droppable({
							accept: '[browserfile="true"][browsertype="picture"]',
							activeClass: "field_ontvankelijk",
							hoverClass: "field_ontvankelijk_hover",
							drop: function( event, ui ) {
									$(this).find("input").val(ui.draggable.attr("browserfileId"));
									$(this).find("input").blicsmFormField("picture", {"action":"load"});
								}
							});
			}
			if(settings["action"] == "load")
			{
				$(this).parent().find(".field_display").html('<div class="text-align: center"><img src="/css/back/loader.gif"/></div>');
				var xmlDoc = $.parseXML('<blicsmFormField/>');
				$(xmlDoc).find("blicsmFormField").append('<pic_id></pic_id>');
				$(xmlDoc).find("blicsmFormField").children('pic_id').last().text($(this).val());
				$(xmlDoc).find("blicsmFormField").append('<field_id></field_id>');
				$(xmlDoc).find("blicsmFormField").children('field_id').last().text($(this).attr("name"));
				
				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'blicsmFormField': 1, 'type': 'PICTURE', 'action': 'picInfo', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "html",
					success : $.proxy(function(data, textStatus, jqXHR){
							$(this).parent().find(".field_display").html(data);
							
							$(this).parent().find(".field_display").find(".formField_file_clear").click(function(){
								$(this).parent().parent().parent().find('input').val(0);
								$(this).parent().parent().text("Drag a file from the right");
							});
							
							//zoeken naar de formats
							/*$(this).parent().parent().find('input[masterpic="' + $(this).attr("name") + '"]').each(function(){
								var masterpic = $(this).parent().find('input[name="' + $(this).attr("masterpic") + '"]');
								var button = $('<div class="computerdata formField_file_format">' + $(this).attr("label") +  '</div>');
								masterpic.parent().find(".field_display").append(button);
								
								button.click(function(){
									cms2_open_pic_edit_new($(this).attr("id"), $(this).attr("masterpic"));
								});
							});*/
							
							
						}, $(this)),
					error : function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);	
						}
				});	
			}
		},
		
		//---------------------------------AUDIO-------------------------------------------
		audio: function(settings)
		{
			if(settings["action"] == "init")
			{
				$(this).wrap('<div id="' + $(this).attr('id') + '_pic" style="float: left; width: 575px; padding: 2px; margin-bottom: 4px;"></div>');
					$(this).parent().append('<div class="field_display"></div>');
					$(this).hide();
					if($(this).val() == "" || $(this).val() == "0")
					{
						$(this).parent().find(".field_display").text("Drag a file from the right");
					}
					else
					{
						//$(this).each(function(){
							$(this).blicsmFormField("audio", {"action":"load"});				  
						//})
					}
					$(this).parent().droppable({
							accept: '[browserfile="true"][browsertype="audio"]',
							activeClass: "field_ontvankelijk",
							hoverClass: "field_ontvankelijk_hover",
							drop: function( event, ui ) {
									$(this).find("input").val(ui.draggable.attr("browserfileId"));
									$(this).find("input").blicsmFormField("audio", {"action":"load"});
								}
							});
			}
			if(settings["action"] == "load")
			{
				$(this).parent().find(".field_display").html('<div class="text-align: center"><img src="/css/back/loader.gif"/></div>');
				var xmlDoc = $.parseXML('<blicsmFormField/>');
				$(xmlDoc).find("blicsmFormField").append('<audio_id></audio_id>');
				$(xmlDoc).find("blicsmFormField").children('audio_id').last().text($(this).val());
				
				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'blicsmFormField': 1, 'type': 'AUDIO', 'action': 'audioInfo', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "html",
					success : $.proxy(function(data, textStatus, jqXHR){
							$(this).parent().find(".field_display").html(data);
							
							$(this).parent().find(".field_display").find(".formField_file_clear").click(function(){
								$(this).parent().parent().find('input').val(0);
								$(this).parent().text("Drag a file from the right");
							});
							
						}, $(this)),
					error : function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);	
						}
				});	
			}
		},
		
		//---------------------------------FILE-------------------------------------------
		file: function(settings)
		{
			if(settings["action"] == "init")
			{
				$(this).wrap('<div id="' + $(this).attr('id') + '_pic" style="float: left; width: 575px; padding: 2px; margin-bottom: 4px;"></div>');
					$(this).parent().append('<div class="field_display"></div>');
					$(this).hide();
					if($(this).val() == "" || $(this).val() == "0")
					{
						$(this).parent().find(".field_display").text("Drag a file from the right");
					}
					else
					{
						//$(this).each(function(){
							$(this).blicsmFormField("file", {"action":"load"});				  
						//})
					}
					$(this).parent().droppable({
							accept: '[browserfile="true"][browsertype="file"]',
							activeClass: "field_ontvankelijk",
							hoverClass: "field_ontvankelijk_hover",
							drop: function( event, ui ) {
									$(this).find("input").val(ui.draggable.attr("browserfileId"));
									$(this).find("input").blicsmFormField("file", {"action":"load"});
								}
							});
			}
			if(settings["action"] == "load")
			{
				$(this).parent().find(".field_display").html('<div class="text-align: center"><img src="/css/back/loader.gif"/></div>');
				var xmlDoc = $.parseXML('<blicsmFormField/>');
				$(xmlDoc).find("blicsmFormField").append('<file_id></file_id>');
				$(xmlDoc).find("blicsmFormField").children('file_id').last().text($(this).val());
				
				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'blicsmFormField': 1, 'type': 'FILE', 'action': 'fileInfo', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "html",
					success : $.proxy(function(data, textStatus, jqXHR){
							$(this).parent().find(".field_display").html(data);
							
							$(this).parent().find(".field_display").find(".formField_file_clear").click(function(){
								$(this).parent().parent().find('input').val(0);
								$(this).parent().text("Drag a file from the right");
							});
							
						}, $(this)),
					error : function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);	
						}
				});	
			}
		},
		
		/*--------------------------LINK--------------------------------------------------------------------*/
		link: function(settings)
		{
			if(settings["action"] == "init")
			{
				$(this).wrap('<div id="' + $(this).attr('id') + '_pic" style="float: left; width: 575px; padding: 0px; margin-bottom: 4px;"></div>');
					$(this).parent().append('<div class="field_display computerdata"></div>');
					
					if($(this).val() != "")
					{
						$(this).parent().find(".field_display").html('<a href="' + $(this).val() + '" target="_blank">follow link</a>');			  
					}
					$(this).parent().droppable({
							accept: '[browserfile="true"]',
							activeClass: "field_ontvankelijk",
							hoverClass: "field_ontvankelijk_hover",
							drop: $.proxy(function( event, ui ) {
									$(this).val(ui.draggable.parent().attr("path"));
									$(this).parent().find(".field_display").html('<a href="' + $(this).val() + '" target="_blank">follow link</a>');
								}, $(this))
							});
			}
			if(settings["action"] == "load")
			{
				$(this).parent().find(".field_display").html('<div class="text-align: center"><img src="/css/back/loader.gif"/></div>');
				var xmlDoc = $.parseXML('<newBrowseFileInfo/>');
				$(xmlDoc).find("newBrowseFileInfo").append('<file_id></file_id>');
				$(xmlDoc).find("newBrowseFileInfo").children('file_id').last().text($(this).val());
				
				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'newBrowse': 1, 'action': 'fileInfo', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "html",
					success : $.proxy(function(data, textStatus, jqXHR){
							$(this).parent().find(".field_display").html(data);
							$(this).parent().find(".field_display").append('<div class="computerdata formField_file_clear">clear picture</div>');
							$(this).parent().find(".field_display").find(".formField_file_clear").click(function(){
								$(this).parent().parent().find('input').val(0);
								$(this).parent().text("Drag a file from the right");
							});
						}, $(this)),
					error : function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);	
						}
				});	
			}
		},
		
		//---------------------------------VIDEOLINK-------------------------------------------
		videolink: function(settings)
		{
			if(settings["action"] == "init")
			{
				$(this).wrap('<div id="' + $(this).attr('id') + '_pic" style="float: left; width: 575px; padding: 2px; margin-bottom: 4px;"></div>');
					$(this).parent().append('<div class="field_display"></div>');
					if($(this).val() == "" || $(this).val() == "0")
					{
						$(this).parent().find(".field_display").text("Insert youtube or video link");
					}
					else
					{
						//$(this).each(function(){
							$(this).blicsmFormField("videolink", {"action":"load"});
						//})
					}
					$(this).change(function(){
						$(this).blicsmFormField("videolink", {"action":"load"});
					});
					$(this).keypress(function(e){
						if(e.keyCode == 13)
							$(this).blicsmFormField("videolink", {"action":"load"});
					});
			}
			if(settings["action"] == "load")
			{
				$(this).parent().find(".field_display").html('<div class="text-align: center"><img src="/css/back/loader.gif"/></div>');
				var xmlDoc = $.parseXML('<blicsmFormField/>');
				$(xmlDoc).find("blicsmFormField").append('<videolink></videolink>');
				$(xmlDoc).find("blicsmFormField").children('videolink').last().text($(this).val());
				
				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'blicsmFormField': 1, 'type': 'VIDEOLINK', 'action': 'videoInfo', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "html",
					success : $.proxy(function(data, textStatus, jqXHR){
							$(this).parent().find(".field_display").html(data);
						}, $(this)),
					error : function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);	
						}
				});	
			}
		}
  };

  
  $.fn.blicsmFormField = function(method) {
		
		// Method calling logic
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.blicsmAccordion' );
		}    
		
		return this;
  };
  
})( jQuery );

function initializeBlicsmFormFields()
{
	$('[blicsmfield="true"]').blicsmFormField();
}