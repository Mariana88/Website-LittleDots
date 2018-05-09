<?php
	//ophalen van de store
	$res_store = DBConnect::query("SELECT page_store.*, page_store_lang.* FROM page_store, page_store_lang WHERE page_store.id=page_store_lang.lang_parent_id AND page_store_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_store = fetch_db($res_store);
	
	//merch
	$res_merch = DBConnect::query("SELECT * FROM data_merch WHERE shop_id='" . $row_store["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	$counter = 0;
	while($merch = fetch_db($res_merch))
	{
		$counter++;
	
		echo '<div class="merch ' . (($counter == mysql_num_rows($res_merch))?"noborder":"") . '">
				<img src="' . $merch["format"] . '" class="shadow"/>
				<div class="text">
					<h2 class="title">' . $merch["title"] . '</h2>
					<div class="description">' . $merch["description"] . '</div>';
		//ophalen links
		$res_link = DBConnect::query("SELECT * FROM data_merch_link WHERE merch_id='" . $merch["id"] . "'", __FILE__, __LINE__);
		if(mysql_num_rows($res_link) > 0)
		{
			echo '<div class="buttons">';
			while($link = fetch_db($res_link))
			{
				echo '<a href="' . $link["link"] . '" target="_blank"><div class="button">' . $link['label'] . '</div></a>';
			}
			echo '</div>';
		}
		echo '</div>
				<div style="height: 0px; clear:both;"></div>
			</div>';
	}
?>
<!--<script async src="//www.bandpage.com/extensionsdk"></script><div class="bp-extension" data-bandpage-bid="376768559499526144"></div>-->