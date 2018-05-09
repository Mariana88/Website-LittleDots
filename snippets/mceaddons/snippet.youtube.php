<?php
	// $row_addon = rij van site_mceaddon
	// $row_type = rij van site_mceaddon_type
	
	$res_yt = DBConnect::query("SELECT * FROM mce_youtube WHERE addon_id='" . $row_addon["id"] . "'", __FILE__, __LINE__);
	$row_yt = mysql_fetch_array($res_yt);
	
	$urlinfo = parse_url($row_yt["link"]);
	$tmp = explode("&", $urlinfo["query"]);
	$vid_id = "";
	foreach($tmp as $queryitem)
	{
		$tmpitem = explode("=", $queryitem);
		if($tmpitem[0] == "v")
			$vid_id = $tmpitem[1];
	}
	$img_url = 'http://img.youtube.com/vi/' . $vid_id . '/0.jpg';
	
	echo '<' . $row_type["html_element"] . ' class="' . $row_type["html_class"] . '" id="' . $row_addon["id"] . '">';
	
	echo '<a href="' . $row_yt["link"] . '" rel="prettyPhoto"><img src="' . $img_url . '">';
	echo '<img src="/css/front/img/button_player_large.png" class="overlay"/></a>';
	
	
	echo '<' . $row_type["html_element"] . '>';
?>