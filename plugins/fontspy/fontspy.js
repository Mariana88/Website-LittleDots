(function($) {

    $.fontSpy = function( element, conf ) {
        var $element = $(element);
        var defaults = {
            font: $element.css("font-family"),
            onLoad: '',
            onFail: '',
            testFont: 'Comic Sans MS',
            testString: 'QW@HhsXJ',
            delay: 50,
            timeOut: 2500
        };
        var config = $.extend( defaults, conf );
        var tester = document.createElement('span');
            tester.style.position = 'absolute';
            tester.style.top = '-9999px';
            tester.style.left = '-9999px';
            tester.style.visibility = 'hidden';
            tester.style.fontFamily = config.testFont;
            tester.style.fontSize = '250px';
            tester.innerHTML = config.testString;
        document.body.appendChild(tester);
        var fallbackFontWidth = tester.offsetWidth;
        tester.style.fontFamily = config.font + ',' + config.testFont;
        function checkFont() {
            var loadedFontWidth = tester.offsetWidth;
            if (fallbackFontWidth === loadedFontWidth){
                if(config.timeOut < 0) {
                    console.log('failure');
                }
                else {
                    setTimeout(checkFont, config.delay);
                    config.timeOut = config.timeOut - config.delay;
                }
            }
            else {
				setTimeout(config.onLoad, 300);
            }
        }
        checkFont();
    };

    $.fn.fontSpy = function(config) {
        return this.each(function() {
            if (undefined == $(this).data('fontSpy')) {
                var plugin = new $.fontSpy(this, config);
                $(this).data('fontSpy', plugin);
            }
        });
    };

})(jQuery);