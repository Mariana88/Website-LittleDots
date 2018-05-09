<?php
	// $row_addon = rij van site_mceaddon
	// $row_type = rij van site_mceaddon_type
	
	$res_rm = DBConnect::query("SELECT * FROM mce_readmore WHERE addon_id='" . $row_addon["id"] . "'", __FILE__, __LINE__);
	$row_rm = fetch_db($res_rm);
	
	echo '<' . $row_type["html_element"] . ' class="' . $row_type["html_class"] . '" id="' . $row_addon["id"] . '" ' . (($row_rm["inline"])?'style="display: inline"':'') . '>';
	
	echo '<div class="readmore_header" ' . (($row_rm["inline"])?'style="display: inline"':'') . ' text1="' . $row_rm["text1"] . '" text2="' . $row_rm["text2"] . '">' .  $row_rm["text1"] . '</div><div class="readmore_content"><p>sfsdf </p></div>';
	
	echo '<' . $row_type["html_element"] . '>';
?>