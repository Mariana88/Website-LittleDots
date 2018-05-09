<?php
	//---------AUDIO PLAYER-------------------------------
	$res_home = DBConnect::query("SELECT * FROM page_home", __FILE__, __LINE__);
	$row_home = fetch_db($res_home);
	//ophalen alle hometracks
	$res = DBConnect::query("SELECT * FROM data_audiotrack WHERE home_id='" . $row_home["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	
	if($row = fetch_db($res))
	{
	?>
	<div id="audio" style="display:block;width:400px;height:20px; float:left" href="http://www.littledots.info<?php echo $row["track"]; ?>" title="<?php echo $row["titleplayer"];?>"></div>
    <div id="audio_play_button"><img src="/css/front/img/audio_play.png" /></div>
    <div id="playlist">
    	<div class="currentinfo"><span class="realtrackinfo"></span>
        	<div class="audioplaylist">
<?php
			mysql_data_seek($res, 0);
			while($row = fetch_db($res))
			{
				echo '<div href="http://www.littledots.info' . $row["track"] . '" title="' . $row["titleplayer"] . '">
						<div>' . $row["title"] . '</div>
					</div>';
			}
?>
        </div>
      </div>
    </div>
	<?php
	}
	
	$res_menu = DBConnect::query("SELECT site_page.*, site_page_lang.* FROM site_page, site_page_lang WHERE site_page.id=site_page_lang.lang_parent_id AND site_page_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND site_page.parent_id='0' AND published='1' AND hide_in_menu='0' ORDER BY `menu_order`", __FILE__, __LINE__);
	
	echo '<div class="mainmenu">';
	
	//---------MENU---------------------------------------
	$counter = 0;
	while($row_menu = fetch_db($res_menu))
	{		
		if($counter == 0)
			echo '<div class="left"><table><tr>';
		if($counter == 3)
			echo '</tr></table></div>
				<div class="right"><table><tr>';
		
		echo '<td class="' . (($counter%3 == 0)?"first":"") . (($counter%3 == 2)?"last":"") . '"><a href="#!' . url_front::create_url($row_menu["id"]) . '" ' . ((url_front::in_url($row_menu["id"]))?'class="current"':'') . ' name="' . $row_menu["menu_name"] . '" menuplace="' . ($counter + 1) . '">' . $row_menu["menu_name"] . '
				</a></td>';
		
		$counter++;
	}
	
	if($counter > 0)
		echo '</tr></table></div>';
		
	//homelink
	echo '<a href="#!/home" id="homelink"></a>';
	
	echo '<div style="width: 100%; clear:both;"></div>';
	echo '</div>';
	echo '<div style="width: 100%; clear:both; height: 10px;"></div>';
	
	//SOCiAL
	echo '<div id="socialicons">
			<a href="https://www.facebook.com/littledotsmusic" target="_blank"><img src="css/front/img/social/facebook.png"></a>
			<a href="https://twitter.com/littledotsmusic" target="_blank"><img src="css/front/img/social/twitter.png"></a>
			<a href="http://instagram.com/littledotsmusic" target="_blank"><img src="css/front/img/social/instagram.png"></a>
			<a href="https://www.youtube.com/user/LittleDotsMusic" target="_blank"><img src="css/front/img/social/youtube.png"></a>
			<a href="http://littledotsmusic.tumblr.com/" target="_blank"><img src="css/front/img/social/tumblr.png"></a>
			<a href="https://soundcloud.com/littledots" target="_blank"><img src="css/front/img/social/soundcloud.png"></a>
		</div>';
?>
<script language="javascript">
	$(".mainmenu").find("a").click(function(){
		loadpage($(this).attr("href"));	
		$(".mainmenu").find("td.selected").removeClass("selected");
		$(this).parent().addClass("selected");
		$(".mainmenu").find("td.realselected > a").each(function(){
			$('.menu_red_hover[menuplace="' + $(this).attr("menuplace") + '"]').hide();														
		});
		$(".mainmenu").find("td.realselected").removeClass("realselected");
		$(this).parent().addClass("realselected");
	});
	
	$("#logo").click(function(){
		$("#homelink").click();
		//loadpage("#!/home");						  
	});
	
	$(".mainmenu").find("a").hover(function() {
												/*$(this).parent().find(".menu_red_hover").fadeIn(1);*/
												$(this).parent().addClass("selected");
												$('.menu_red_hover[menuplace="' + $(this).attr("menuplace") + '"]').show();
											}, function() {
												if(!$(this).parent().hasClass("realselected"))
												{
													$(this).parent().removeClass("selected");
													$('.menu_red_hover[menuplace="' + $(this).attr("menuplace") + '"]').hide();			
												}
											});
	
	
	var audio_started_first = false;
	var timer_playlist = null;
	
	

	$(document).ready(function(){
		//PLAY BUTTON
		$("#audio_play_button").click(function(){
			if($f("audio").isPlaying() && audio_started_first)
			{
				$f("audio").pause();
				//$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_play.png");
			}
			else
			{
				if(!audio_started_first)
				{
					$(".realtrackinfo").text($(".audioplaylist > div:first").attr("title"))
					$(".audioplaylist > div:first").addClass("trackplay");
				}
				$f("audio").play();
				//$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_pauze.png");
				audio_started_first = true;
			}
		});
		//ROLLOVER FOOTER VOOR TONEN VAN PLAYLIST
		/*
		$("#audio_play_button").add(".audioplaylist").bind("mouseenter", function(){
			if(!$(".audioplaylist").is(":visible"))
			{
				$(".audioplaylist").css("width", "200px");
				$(".audioplaylist").show(200);
			}
				
			clearTimeout(timer_playlist);
		});
		$("#audio_play_button").add(".audioplaylist").bind("mouseleave", function(){
			if($(".audioplaylist").is(":visible"))
			{
				timer_playlist = setTimeout(function(){$(".audioplaylist").hide(200);}, 2000);
			}
		});	*/	
		
	});
	
	//INITIALIZING FLOWPLAYER
	if(window.macdevice == true)
	{
		$f("audio", "/plugins/flowplayer/flowplayer-3.2.7.swf", {
			clip: { 
				autoPlay: false,
				onStart: function(clip) {
					this.setVolume(100);
					//$(".realtrackinfo").text($("div.trackplay").text());
					$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_pauze.png");
					//blink_audio_button();
				},
				onResume: function(clip) {
					//$(".currentinfo").children().first().text($("div.trackplay").text());
					$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_pauze.png");
					//blink_audio_button();
				},
				onPause: function(clip) {
					//$(".currentinfo").children().first().text($("div.trackplay").text());
					$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_play.png");
					//blink_audio_button();
				}
			},
			plugins: {
				audio: {
					url: '/plugins/flowplayer/flowplayer.audio-3.2.2.swf'
				},
				controls: {
					fullscreen: false,
					height: 20,
					autoHide: false,
					background: '#FFFFFF',
					backgroundGradient: 'none',
					timeColor: '#000000',
					progressColor: '#000000',
					sliderColor: '#AE0A0A',
					buttonColor: '#666666',
					buttonOverColor: '#000000',
					bufferColor: '#FFFFFF'
				}
			}
		
		}).ipad();
	}
	else
	{
		$f("audio", "/plugins/flowplayer/flowplayer-3.2.7.swf", {
			clip: { 
				autoPlay: false,
				onStart: function(clip) {
					this.setVolume(100);
					//$(".realtrackinfo").text($(".trackplay").text());
					$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_pauze.png");
					//blink_audio_button();
				},
				onResume: function(clip) {
					//$(".currentinfo").children().first().text($("div.trackplay").text());
					$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_pauze.png");
					//blink_audio_button();
				},
				onPause: function(clip) {
					//$(".currentinfo").children().first().text($("div.trackplay").text());
					$("#audio_play_button").find('img').attr("src", "/css/front/img/audio_play.png");
					//blink_audio_button();
				}
			},
			plugins: {
				audio: {
					url: '/plugins/flowplayer/flowplayer.audio-3.2.2.swf'
				},
				controls: {
					fullscreen: false,
					height: 20,
					autoHide: false,
					background: '#FFFFFF',
					backgroundGradient: 'none',
					timeColor: '#000000',
					progressColor: '#000000',
					sliderColor: '#AE0A0A',
					buttonColor: '#666666',
					buttonOverColor: '#000000',
					bufferColor: '#FFFFFF'
				}
			}
		
		});
	}
	
	//$f("audio").playlist("div.audioplaylist", {loop:true, playingClass: 'trackplay', pausedClass: 'trackpause', progressClass:'trackprogress'});
	
	//$("#audio").attr("playafterclose", "false");
</script>