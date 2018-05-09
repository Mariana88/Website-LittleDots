<?php
	// $row_addon = rij van site_mceaddon
	// $row_type = rij van site_mceaddon_type
	
	$res_gal = DBConnect::query("SELECT * FROM mce_gallery WHERE addon_id='" . $row_addon["id"] . "'", __FILE__, __LINE__);
	$row_gal = mysql_fetch_array($res_gal);
	$res_pics = DBConnect::query("SELECT * FROM mce_gallery_pics WHERE gallery_id='" . $row_gal["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	
	$height = 20;
	if(mysql_num_rows($res_pics) > 0)
		$height = (ceil(mysql_num_rows($res_pics)/3) * 61) -6;
	
	echo '<' . $row_type["html_element"] . ' class="' . $row_type["html_class"] . '" style="height: ' . $height . 'px;" id="' . $row_addon["id"] . '">';
	$counter = 1;
	while($row_pic = fetch_db($res_pics))
	{
		if($counter>1 && $counter%3 == 1)
			echo '<div class="space_6">&nbsp;</div>';
		echo '<div class="fotogallery_div_' . ($counter%3) . '"><a href="' . $row_pic["picture"] . '" rel="prettyPhoto[pp_gal' . $row_addon["id"] . ']" title="' . $row_pic["title"] . ((trim($row_pic["copyright"]) != "")?'&nbsp;&nbsp;&nbsp;&nbsp;&copy;' . $row_pic["copyright"]:'') .'"><img src="' . $row_pic["thumb"] . '" style="height: 55px; width:88px;"/></a></div>';
		$counter++;
		
	}
	
	echo '<' . $row_type["html_element"] . '>';
?>