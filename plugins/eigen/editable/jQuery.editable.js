/*------------------------ Conextmenu ---------------------------

een context menu

*/

(function( $ ){
  
  var methods = {
		init : function(settings) { 
			//opslaan van de settings
			$(this).get(0).newRow = settings["newRow"];
			$(this).get(0).newButton = settings["newButton"];
			$(this).get(0).gridId = settings["id"];
			$(this).get(0).tableName = settings["tableName"];
			
			//NEW Button
			$(this).get(0).newButton.click(function(){
				$(this).prev().editable("addRow");
			});
			
			//SAVE BUTTON
			$(this).find(".editable_save").each(function(){
				$(this).click(function(){$(this).editable("saveButtonClick");});
			});
			
			//REMOVE BUTTON
			$(this).find(".editable_remove").each(function(){
				$(this).click(function(e){$(this).editable("removeButtonClick", e);});
			});
			
			
			if(settings["ordering"])
			{
				 $(this).sortable({
					items: "tr.datarow",
					handle: ".editable_ordering",
					axis: 'y',
					update: function( event, ui ){
						ui.item.find(".editable_save").click();
					}
				 });
			}
		},
		addRow: function(){
			//checken voor newId
			var tmp_ID = 0;
			$(this).find("tr").last().find('input[name="' + $(this).get(0).tableName + '.id"]').each(function(){
				if(parseInt($(this).attr("tmpid")) > tmp_ID)
					tmp_ID = parseInt($(this).attr("tmpid"));
			});
			tmp_ID++;
			
			$(this).append($(this).get(0).newRow);
			$(this).find("tr").last().find('input[name="' + $(this).get(0).tableName + '.id"]').attr("tmpid", tmp_ID);
			
			//buttons
			$(this).find("tr").last().find(".editable_save").click(function(){$(this).editable("saveButtonClick");});
			$(this).find("tr").last().find(".editable_remove").click(function(e){$(this).editable("removeButtonClick", e);});
		},
		saveButtonClick: function(){
			//opzoeken onder welke we staan
				var afterRow = 0;
				if($(this).parent().parent().prev().hasClass("datarow"))
				{
					afterRow = $(this).parent().parent().prev().find('input[name="' + $(this).attr("tableName") + '.id"]').val();
				}
				//ophalen van alle velden en in een xml doc steken
				var xmlDoc = $.parseXML('<editable_save/>');
				$(xmlDoc).find("editable_save").append('<afterRow>' + afterRow + '</afterRow>')
				$(this).parent().parent().find("input, select, textarea").each(function(){
					$(xmlDoc).find("editable_save").append('<field></field>');
					$(xmlDoc).find("editable_save").children('field').last().text($(this).val());
					$(xmlDoc).find("editable_save").children('field').last().attr("name", $(this).attr("name"));
					
					if(parseInt($(this).attr("tmpid")) > 0)
						$(xmlDoc).find("editable_save").append('<tmpid>' + parseInt($(this).attr("tmpid")) + '</tmpid>');
				});
				
				//alert(xmlToString(xmlDoc));
				
				$.ajax({
					url: '/ajax.php',
					type: 'GET',
					data: {'sessid': session_id, 'dataeditor_editable': $(this).attr("gridId"), 'action': 'save', 'xml': escape(xmlToString(xmlDoc))}, 
					contentType: "text",
					dataType: "html",
					success : $.proxy(function(data, textStatus, jqXHR){
							
							var doc = $($.parseXML(jqXHR.responseText));
							//eerst checken of er errors zijn
							if(doc.find("error").length > 0)
							{
								
							}
							else
							{
								//we zoeken eerst of er velden zijn die moeten aangepast worden
								var row = $(this).parent().parent();
								
								doc.find("field").each(function(){
									var attr = $(this).attr("tmpid");
									if (typeof attr !== 'undefined' && attr !== false) {
										$(this).removeAttr("tmpid");
									}
									row.find('[name="' + $(this).attr("name") + '"]').val($(this).text());
								});
								
								//saveknop oplichten
								$(this).parent().parent().effect("highlight", {color: '#22AA22'}, 1000);
							}
						}, $(this)),
					error : function(jqXHR, textStatus, errorThrown)
						{
							alert(jqXHR.responseText);	
						}
				});		
		},
		removeButtonClick: function(e){
			
			$.blicsmPopup({'id': 'editableRemove' + $(this).attr("gridId"), 
								  'buttons': {
									  'Yes': $.proxy(function(){
										  		 var xmlDoc = $.parseXML('<editable_remove/>');
												
													$(xmlDoc).find("editable_remove").append('<id></id>');
													$(xmlDoc).find("editable_remove").children('id').last().text($(this).parent().parent().find('input[name="' + $(this).attr("tableName") + '.id"]').val());
													$(xmlDoc).find("editable_remove").children('id').last().attr("table",  $(this).attr("tableName"));
												
												//alert(xmlToString(xmlDoc));
												
												$.ajax({
													url: '/ajax.php',
													type: 'GET',
													data: {'sessid': session_id, 'dataeditor_editable': $(this).attr("gridId"), 'action': 'remove', 'xml': escape(xmlToString(xmlDoc))}, 
													contentType: "text",
													dataType: "html",
													success : $.proxy(function(data, textStatus, jqXHR){
															
														}, $(this)),
													error : function(jqXHR, textStatus, errorThrown)
														{
															alert(jqXHR.responseText);	
														}
												});		
											$(this).parent().parent().hide(300, function(){$(this).remove()});	
												
											$.blicsmPopup("closePopup", 'editableRemove' + $(this).attr("gridId"));
										}, $(this)),
									  'No': $.proxy(function(){
										  		$.blicsmPopup("closePopup", 'editableRemove' + $(this).attr("gridId"))
										}, $(this))
									},
									'colorClass': 'fel',
									'html': 'Are you sure?',
									'autoDissappear': 5,
									'clickEvent': e,
									'parentSelector': 'body'});
		}
  };

  
  $.fn.editable = function(method) {
		
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