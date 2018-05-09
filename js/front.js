function initpage()
{	
	$(".content_track").click(function(){
		addtrack($(this).attr("title"), $(this).attr("path"), true);
	});

	
	//video starts Monitoring
	$(".youtubecontainer").each(function(){
		player = new YT.Player('yt' + $(this).attr("vidid"), {
		  height: $(this).attr("vidheight"),
		  width: $(this).attr("vidwidth"),
		  videoId: $(this).attr("vidid"),
		  events: {
			'onStateChange': function(event){
					if(event.data == 1)
					{
						stop_audio();
					}
				}
		  }
		});
	});
	
	if($(".vimeocontainer").length > 0)
	{
		if (window.addEventListener){
			window.addEventListener('message', onVimeoMessageReceived, false);
		}
		else {
			window.attachEvent('onmessage', onVimeoMessageReceived, false);
		}	
	}
}

function vimeopost(action, value) {
    
}

function onVimeoMessageReceived(e)
{
	var data = JSON.parse(e.data);
    switch (data.event) {
       	case 'ready':
			$(".vimeocontainer").each(function()
			{
				var f = $(this);
    			var url = f.attr('src').split('?')[0];
				var postdata = { method: 'addEventListener', value: 'play' };
				f[0].contentWindow.postMessage(JSON.stringify(postdata), url);
			});
			break;
	   	case 'play':
            stop_audio();
            break;
    }
}

function stop_audio()
{
	if($f("audio").isPlaying() && audio_started_first)
	{
		$f("audio").pause();
	}		
}

function background_position()
{
	if((($(window).width()/parseInt($("#site_background").attr("original_width")))*parseInt($("#site_background").attr("original_height"))) < ($(window).height()))
	{
		var newwidth = (($(window).height())/parseInt($("#site_background").attr("original_height")))*parseInt($("#site_background").attr("original_width"));
		$("#site_background").css({"height": ($(window).height()) + "px", "width": newwidth + "px", "left": "0px"});
		$("#site_background").css({"z-index": '-100', 'top': '0px'});
	}
	else
	{
		$("#site_background").css({"height": (($(window).width()/parseInt($("#site_background").attr("original_width")))*parseInt($("#site_background").attr("original_height"))) + "px", "width": $(window).width() + "px", "left": "0px"});
		
		//top instellen
		$("#site_background").css({"z-index": '-100'});
	}	
	
	//de menu pics
	$(".menu_red_hover").css({"height": $("#site_background").css("height"),
								  "width": $("#site_background").css("width"),
								  "top": $("#site_background").css("top"),
								  "left": $("#site_background").css("left"),
								  "z-index": "-50"});
	
	if(!$("#site_background").is(":visible"))
	{
		//$("#site_background").css({'opacity':'0', 'filter':'alpha(opacity=0)'});
		$("#site_background").show();
		//$("#site_background").animate({'opacity':'100'}, 1000);	
	}
	
	//if logo is visable
	//if($("#logo").is(":visible"))
	//{
		var sizeratio = $("#site_background").height()/$("#site_background").attr("original_height_backup");
		var sizeratio = sizeratio * 0.8;
		
		//var width = ($("#logo").attr("original_width") * sizeratio);
		//var height = ($("#logo").attr("original_height") * sizeratio);
		
		var width = 200;
		var height = 75;
		
		var left = ($(window).width()-width)/2;
		if(left < $("#site_background").width()/2.8 && $("#logo").attr("ontop") != "true")
		{
			left = $("#site_background").width()/2.8;
		}
		
		$("#logo").css({'width': width + "px",
						'height': height + "px",
						'left': left + "px"});
		if($("#logo").attr("ontop") == "true")
		{
			$("#logo").css({'top': "0px"});	
		}
		else
		{
			$("#logo").css({'top': (($(window).height()-($("#logo").attr("original_height") * sizeratio))/2) + "px"});	
		}
		$("#logo").show();
	//}
	
	//content
	/*var left = $("#site_background").width()/2.8;*/
	if($(".content_middle").text() == "ON")
	{
		$('#main_content').css({"text-align": "center", "margin-left": "20px", "width": "-moz-calc(100% - 40px)", "width": "-webkit-calc(100% - 40px)", "width": "-o-calc(100% - 40px)", "width": "calc(100% - 40px)"});
	}
	else
	{
		var left = parseInt($("#logo").css("left")) + 8;
		$('#main_content').css({"text-align": "left", "width": "-moz-calc(100% - " + (left+20) + "px)", "width": "-webkit-calc(100% - " + (left+20) + "px)", "width": "-o-calc(100% - " + (left+20) + "px)", "width": "calc(100% - " + (left+20) + "px)"});
		$('#main_content').css("margin-left", left + "px");
		$("#main_content").css("z-index", "100");
	}
}

function loadpage(href)
{
	//checken of het logo nog in't midden staat
	if($("#logo").attr("ontop") != "true")
	{
		$("#site_background").animate({opacity: 0.6}, 500);
		$("#logo").animate({'top': "0px"}, 500);
		$("#logo").attr("ontop", "true");
		$("#main_header").fadeIn(500);
		$("body").unbind( "click" );
		$("body").css("cursor","default");
	}
	
	
	var hrefsplit = href.split('#!');
	$("#main_content").html("");
	show_loader();
	/*if($("#site_background").attr("original_image") != $("#site_background").attr("src"))
	{
		$("#site_background").attr("original_height", $("#site_background").attr("original_height_backup"));
		$("#site_background").attr("original_width", $("#site_background").attr("original_width_backup"));
		$("#site_background").attr("donthideloader", "true");
		$("#site_background").attr("src", $("#site_background").attr("original_image"));
	}*/
	$("#main_content").load('/ajaxfront.php?page=' + encodeURI(hrefsplit[1]), function(response, status, xhr) {
			hide_loader();
			window.scrollTo(window.pageXOffset || document.documentElement.scrollLeft, 0);
			initpage();
			$(".menu_red_hover").css({"z-index": "-50"});
			$("#main_content").css({"z-index": "100","position": "absolute", "display": "block"});
			
			if($(".content_middle").text() == "ON")
			{
				$('#main_content').css({"text-align": "center", "margin-left": "20px", "width": "-moz-calc(100% - 40px)", "width": "-webkit-calc(100% - 40px)", "width": "-o-calc(100% - 40px)", "width": "calc(100% - 40px)"});
				$('#main_content').css("margin-left", "20px");
			}
			else
			{
				var left = parseInt($("#logo").css("left")) + 8;
				$('#main_content').css({"text-align": "left", "width": "-moz-calc(100% - " + (left+20) + "px)", "width": "-webkit-calc(100% - " + (left+20) + "px)", "width": "-o-calc(100% - " + (left+20) + "px)", "width": "calc(100% - " + (left+20) + "px)"});
				$('#main_content').css("margin-left", left + "px");
				$("#main_content").css("z-index", "100");
			}
		}
	);
}

function show_loader()
{
	$("body").append('<div class="loader_container"><img src="/css/front/img/ajax-loader.gif"/></div>');
	var toppx = ((parseInt($(window).height()) - parseInt($(".loader_container").height())) / 2) - 21;
	var leftpx = (($(window).width()) - ($(".loader_container").width())) / 2;
	$(".loader_container").css({"top": toppx + "px", "right": leftpx + "px"});
}

function hide_loader()
{
	$("body").find(".loader_container").remove();
}

function addtrack(title, path, play)
{
	//checken of de track er al in staat (aan de hand van pad)
	if($(".audioplaylist").children('div[href="' + path + '"]').length == 0)
	{
		var htmltoadd = '<div title="' + title + '" href="' + path + '"><div>' + title + '</div></div>';
		if($(".trackplay").length > 0)
			$(".trackplay").after(htmltoadd);
		else
			$(".audioplaylist").prepend(htmltoadd);
	}
	//$f("audio").playlist("div.audioplaylist", {loop:true, playingClass: 'trackplay', pausedClass: 'trackpause', progressClass:'trackprogress'});
	if(play)
	{
		var listitem = $(".audioplaylist").children('div[href="' + path + '"]');
		if(!listitem.hasClass("trackplay"))
		{
			$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_pauze.png");
			//$(".audioplaylist").children('div[href="' + path + '"]').click();
			$(".realtrackinfo").text(listitem.text());
			$(".trackplay").removeClass("trackplay");
			listitem.addClass("trackplay");
			//load the track
			$f().play([{url: path, title: title}]);			
		}
	}
	
	/*
	
	//ROLLOVER VAN TRACKLISTS
	$(".audioplaylist").children("div").bind("mouseenter", function(){
		$(this).css("background-color", "#EEEEEE");
	});
	$(".audioplaylist").children("div").bind("mouseleave", function(){
		$(this).css("background-color", "#FFFFFF");
	});*/
	
	/*
	// VERWIJDER KNOPJES ROLLOVER
	$(".audioplaylist").children("div").children("img").bind("mouseenter", function(){
		$(this).attr("src", "/css/front/img/audioremovefromlistred.gif")
	});
	$(".audioplaylist").children("div").children("img").bind("mouseleave", function(){
		$(this).attr("src", "/css/front/img/audioremovefromlist.gif")
	});
	
	//VERWIJDER KNOP
	$(".audioplaylist").children("div").children("img").click(function(){
		$(this).parent().remove();
	});	*/
}

function blink_audio_button()
{
	$("#audio_play_button").css("background-color", "#FFFFFF");
	setTimeout(function(){$("#audio_play_button").css("background-color", "#AA0000");}, 250);
	
}