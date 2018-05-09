/*------------------------ Conextmenu ---------------------------

een context menu

*/

(function( $ ){
  
  var methods = {
		init : function(settings) { 
			$(this).click(function(){
				if($(this).hasClass("open"))
				{
					$(this).removeClass("open");
					$(this).addClass("closed");
					$(this).next().hide(300);
				}
				else
				{
					$(this).removeClass("closed");
					$(this).addClass("open");
					$(this).next().show(300);
				}
			});
		}
  };

  
  $.fn.blicsmColpanel = function(method) {
		
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