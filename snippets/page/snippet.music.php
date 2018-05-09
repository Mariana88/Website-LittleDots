<?php
	$res_music = DBConnect::query("SELECT page_music.*, page_music_lang.* FROM page_music, page_music_lang WHERE page_music.id=page_music_lang.lang_parent_id AND page_music_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_music = fetch_db($res_music);
	
	echo '<div class="medium_box box">';
	$res_disc = DBConnect::query("SELECT * FROM `data_disc` WHERE pagemusic_id='" . $row_music["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	while($disc = fetch_db($res_disc))
	{
		echo '<div class="music_disc">';
		echo '<h3>' . $disc["title"] . '</h3>';
		echo '<div class="label">' . ((trim($disc["label_link"]))?'<a href="' . $disc["label_link"] . '" target="_blank">':'') . $disc["label"] . ((trim($disc["label_link"]))?'</a>':'');
		if(trim($disc["label2"]) != "")
			echo ' / ' . ((trim($disc["label2_link"]))?'<a href="' . $disc["label2_link"] . '" target="_blank">':'') . $disc["label2"] . ((trim($disc["label2_link"]))?'</a>':'');
		echo ' - ' . $disc["release_date"];
		echo '</div>';
		echo '<div class="info">';
		echo '<br><img src="' . $disc["image_format"] . '" class="shadow"/>';
		
		
		//BUY Cd
		if(trim($disc["buylink"]) != "")
		{
			echo '<div style="height: 3px; clear: both;"></div>';
			echo '<a class="button" href="' . $disc["buylink"] . '" target="_blank">' . $disc["buylinklabel"] . '</a>';
		}
		//BUY Vinyl
		if(trim($disc["buylinkvinyl"]) != "")
		{
			if(trim($disc["buylink"]) == "")
				echo '<div style="height: 3px; clear: both;"></div>';
			echo '<a class="button" ' . ((trim($disc["buylink"]) != "")?' style="margin-left: 8px;"':'') . ' href="' . $disc["buylinkvinyl"] . '" target="_blank">' . $disc["buylinkvinyllabel"] . '</a>';
		}
		
		//BUY Digi
		if(trim($disc["buylinkdigi"]) != "")
		{
			if(trim($disc["buylink"]) == "" && trim($disc["buylinkvinyl"]) == "")
				echo '<div style="height: 3px; clear: both;"></div>';
			echo '<a class="button" ' . ((trim($disc["buylink"]) != "" || trim($disc["buylinkvinyl"]) != "")?' style="margin-left: 8px;"':'') . ' href="' . $disc["buylinkdigi"] . '" target="_blank">' . $disc["buylinkdigilabel"] . '</a>';
		}
		
		if(trim($disc["codelink"]) != "")
		{
			echo '<div style="height: 3px; clear: both;"></div>';
			echo '<a class="button" style="margin-top: 4px;" href="' . $disc["codelink"] . '" target="_blank">Download with code</a>';
			echo '<div style="height: 10px; clear: both;"></div>';
		}
		
		echo '<p class="credits">' . $disc["credits"] . '</p>';
		
		echo '</div>'; //info
		//tacks
		$res_track = DBConnect::query("SELECT * FROM data_audiotrack_disc WHERE disc_id='" . $disc["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
		if(mysql_num_rows($res_track))
		{
			echo '<div class="songlist">';
			
			while($track = fetch_db($res_track))
			{
				echo '<div class="song">
						<div class="header">
							<div class="text">
								<div class="title">' . $track["title"] . '</div>
								<div class="credits">' . $track["credits"] . '</div>
							</div>';
				if(trim($track["track"]) != "")
					echo '<div class="content_track" path="http://www.littledots.info' . $track["track"] . '" title="' . $track["titleplayer"] . '"><img src="/css/front/img/audioplaysmall.png"></div>';
				echo '<div style="clear:both; height: 0px;"></div>
					</div>';
				
				echo '<div class="lyrics">' . $track["lyrics"] . '</div>';
				
				echo '</div>';
			}
			echo '</div>';
		}
		
		
		echo '</div><div style="clear: both; height: 0px;"></div></div>';
	}
	echo '</div>';
?>
<script language="javascript">
	$(".song .header").click(function(){
		if($(this).parent().hasClass("open"))
		{
			$(".song.open").find(".lyrics").hide(300);			
			$(".song.open").removeClass("open");
		}
		else
		{
			$(".song.open").find(".lyrics").hide(300);			
			$(".song.open").removeClass("open");
			$(this).parent().find(".lyrics").show(300);
			$(this).parent().addClass("open");
		}
	});
</script>