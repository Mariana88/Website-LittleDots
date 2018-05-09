/*------------------------ Conextmenu ---------------------------

een context menu

*/

(function( $ ){
  
  var methods = {
		init : function(settings) { 
			/*SETTINGS
				- id = de window id, moet uniek zijn
				- buttons = {name: function()}
				- html = de inhoud
				- url = de inhoud laden via een url
				- modal
				- position = mouse or center
				- blanksite  = of de site gehide wordt
				- width
				- height
				- colorClass
				- autodissappear -> in seconds
				- clickEvent -> the event object which started the popup
				- parentSelector -> the div that hosts the popup
				- onOpened -> functie die na het openen wordt uitgevoerd
			*/
			
			var settings = $.extend({'id': 'popup1', 'html': '', 'buttons': {}, 'url': '', 'modal': false, 'position': 'mouse', 'blanksite': false, 'width': 'auto', 'height': 'auto', 'colorClass': 'standard', 'autoDissappear': 0, 'clickEvent': null, 'parentSelector': 'body', 'onOpened' : null}, settings);
			
			//----------------CREATE-------------------
			if($(settings["parentSelector"]).find("#" + settings["id"]).length > 0)
				return;
			
			var maindiv = $('<div class="blicsmPopup ' + settings["colorClass"] + '" id="' + settings["id"] + '"></div>');
			//maindiv.css({'opacity':'0'});
			if(settings["position"] == 'mouse')
				maindiv.append('<div class="blicsmPopup_arrow"></div>');
			var contentdiv = $('<div class="blicsmPopup_content"><div class="blicsmPopup_content_inner"></div></div>');
			maindiv.append(contentdiv);
			//html
			if(settings["url"] != '')
			{
				contentdiv.children(".blicsmPopup_content_inner").html('load the url, todo: program this');
			}
			else
				contentdiv.children(".blicsmPopup_content_inner").html(settings["html"]);
			
			//buttons
			if(Object.keys(settings["buttons"]).length > 0)
			{
				
				var buttondiv = $('<div class="blicsmPopup_buttons"></div>');
				for (var name in settings["buttons"]){
					
					var the_button = $('<div class="man_button">' + name + '</div>');
					buttondiv.append(the_button);
					the_button.click(settings["buttons"][name]);
				}
				contentdiv.append(buttondiv);
			}
			//positioning
			//voorlopig gewoon top
			$(settings["parentSelector"]).append(maindiv);
			if(settings["position"] == "mouse" && settings["clickEvent"])
			{
				maindiv.css({'position': 'fixed', 'top': (settings["clickEvent"].pageY - $(document).scrollTop()) + 'px', 'left': (settings["clickEvent"].pageX - 17) + 'px'});	
			}
			else
			{
				
			}
			
			maindiv.fadeIn(300);
			maindiv.get(0).settings = settings;
			
			if(settings["autoDissappear"] > 0)
			{
				setTimeout(function(){$.blicsmPopup("closePopup", settings["id"])}, settings["autoDissappear"] * 1000);	
			}
			
			if(settings["onOpened"])
			{
				settings["onOpened"]();
			}
		},
		
		closePopup: function(popupId)
		{
			$('body').find("#" + popupId).fadeOut(300, function(){$(this).remove(); });
		}
  };

  
  $.blicsmPopup = function(method) {
		
		// Method calling logic
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.blicsmPopup' );
		}    
		
		return this;
  };
  
})( jQuery );