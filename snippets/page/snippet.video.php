<?php
	echo '<div class="content_middle">ON</div>';
	$res_pagevid = DBConnect::query("SELECT page_video.*, page_video_lang.* FROM page_video, page_video_lang WHERE page_video.id=page_video_lang.lang_parent_id AND page_video_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_pagevid = fetch_db($res_pagevid);
	
	echo '<div class="medium_box box">';
	$res_vid = DBConnect::query("SELECT * FROM `data_video` WHERE pagevideo_id='" . $row_pagevid["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	while($vid = fetch_db($res_vid))
	{
		
		if(trim($vid["video"]) != "")
		{
			$data = Video::analyseUrl($vid["video"]);
			if($vid["auto_description"])
				echo '<h2 class="video_title">' . $data["title"] . '</h2>';
			else
				echo '<h2 class="video_title">' . $vid["title"] . '</h2>';
			
			Video::echoVideoFront($vid["video"], 630, false);
			
			if($vid["auto_description"])
				echo '<div class="video_desc">' . $data["description"] . '</div>';
			else
				echo '<div class="video_desc">' . $vid["description"] . '</div>';
		}
		else
		{
			echo '<h2 class="video_title">' . $vid["title"] . '</h2>';
			echo '<div class="inline_video" style="width: 630px;">' . $vid['embed_raw'] . '</div>';
			echo '<div class="video_desc">' . $vid["description"] . '</div>';
		}
		echo '<div style="height: 20px;"></div>';
	}
	echo '</div>';
?>