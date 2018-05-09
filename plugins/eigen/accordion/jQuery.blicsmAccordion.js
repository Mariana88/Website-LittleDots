/*------------------------ Conextmenu ---------------------------

een context menu

*/

(function( $ ){
  
  var methods = {
		init : function(settings) { 
			$(this).children('.header').each(function(){
				var content = $(this).parent().children('.content[acc="' + $(this).attr("acc") + '"]');
				if(!$(this).hasClass("open"))
					content.hide();
				//zowat css in orde brengen
				content.children(".scroll").css({"overflow-y": "scroll"});
				content.children(".static").css({"overflow-y": "hidden"})
				
				$(this).click(function(e){
					if($(this).hasClass("open"))
						return;
					
					//zoeken naar de andere die open is
					$(this).parent().children(".header.open").each(function(){
						$(this).parent().children('.content[acc="' + $(this).attr("acc") + '"]').hide(300);
						$(this).removeClass("open");
					});
					
					$(this).addClass("open");
					
					$(this).parent().blicsmAccordion("resize");
				});
			});
			$(this).blicsmAccordion("resize");
			$(window).resize($.proxy(function(){$(this).blicsmAccordion("resize");}, $(this)));
		},
		
		resize : function(){
			//zoeken naar de open header
			var header = $(this).children(".header.open");
			var content = $(this).children('.content[acc="' + header.attr("acc") + '"]');
			
			//setten van css
			var accHeight = $(window).height() - 60;
			var contentHeight = accHeight;
			$(this).children('.header').each(function(){
				contentHeight -= parseInt($(this).height()) + parseInt($(this).css("border-top-width")) + parseInt($(this).css("border-bottom-width")) + parseInt($(this).css("padding-top")) + parseInt($(this).css("padding-bottom")) + parseInt($(this).css("margin-top")) + parseInt($(this).css("margin-bottom"));									  
			});
			var scrollHeight = contentHeight;
			content.children(".static").each(function(){
				scrollHeight -= parseInt($(this).height()) + parseInt($(this).css("border-top-width")) + parseInt($(this).css("border-bottom-width")) + parseInt($(this).css("padding-top")) + parseInt($(this).css("padding-bottom")) + parseInt($(this).css("margin-top")) + parseInt($(this).css("margin-bottom"));											  
			});
			
			$(this).css("height", accHeight + "px");
			content.css("height", contentHeight + "px");
			var the_scroll = content.children(".scroll").first();
			scrollHeight -= parseInt(the_scroll.css("border-top-width")) + parseInt(the_scroll.css("border-bottom-width")) + parseInt(the_scroll.css("padding-top")) + parseInt(the_scroll.css("padding-bottom")) + parseInt(the_scroll.css("margin-top")) + parseInt(the_scroll.css("margin-bottom"));	
			the_scroll.css("height", scrollHeight + "px");
			
			if(!content.is(":visible"))
				content.show(300);
		}
  };

  
  $.fn.blicsmAccordion = function(method) {
		
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