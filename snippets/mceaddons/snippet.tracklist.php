<?php
	// $row_addon = rij van site_mceaddon
	// $row_type = rij van site_mceaddon_type
	
	$res_tr = DBConnect::query("SELECT * FROM mce_tracklist WHERE addon_id='" . $row_addon["id"] . "'", __FILE__, __LINE__);
	$row_tr = mysql_fetch_array($res_tr);
	$res_tracks = DBConnect::query("SELECT * FROM mce_tracklist_track WHERE tracklist_id='" . $row_tr["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	
	
	echo '<' . $row_type["html_element"] . ' class="' . $row_type["html_class"] . '" id="' . $row_addon["id"] . '"><div>';
	
	while($row_track = fetch_db($res_tracks))
	{
		echo '<div path="http://www.deberengieren.be' . $row_track["file"] . '" title="' . $row_track["title_player"] . '" class="content_track"><img src="/css/front/img/button_play_small.gif"/>' . str_replace(' ', '&nbsp;', $row_track["title"]) . '</div>';	
	}
	if(mysql_num_rows($res_tracks) > 1)
		echo '<div class="redbg content_track_add_all" style="float:right; clear:both; cursor: pointer; margin-top:3px; padding-left: 4px;">Listen&nbsp;All</div>';
	
	echo '<div style="clear:both; height:3px;">&nbsp;</div></div><div class="hr_3">&nbsp;</div><' . $row_type["html_element"] . '>';
?>