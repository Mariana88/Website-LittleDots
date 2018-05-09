<?php
	$res_band = DBConnect::query("SELECT page_band.*, page_band_lang.* FROM page_band, page_band_lang WHERE page_band.id=page_band_lang.lang_parent_id AND page_band_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_band = fetch_db($res_band);
	
	//BIO
	echo '<div class="bios"><h2>Bio:</h2>';
	
	echo '<div class="bio"><div class="header">English</div><div class="text">' . $row_band["bioen"] . '</div></div>';
	echo '<div class="bio"><div class="header">Nederlands</div><div class="text">' . $row_band["bionl"] . '</div></div>';
	echo '<div class="bio last"><div class="header">Fran&ccedil;ais</div><div class="text">' . $row_band["biofr"] . '</div></div>';
	
	if(trim($row_band["picture"]) != "")
	{
		echo '<img class="biopic" src="' . $row_band["picture"] . '"/>';	
	}
	
	echo '</div>';
	
	
	
	
	//PERS
	$res_press = DBConnect::query("SELECT * FROM data_press WHERE biopage_id='" . $row_band["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	if(mysql_num_rows($res_press) > 0)
	{
		echo '<div class="pers"><h2>Press:</h2>';
		while($press = fetch_db($res_press))
		{
			
			echo '<div class="press fancybox" data-fancybox-type="iframe" href="http://docs.google.com/viewer?url=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $press["pdf"]) . '&embedded=true">
					<div class="date">' . $press["date"] . '</div>
					<div class="title">' . $press["title"] . '</div>
					<div class="source">' . $press["source"] . '</div>
				</div>';
			
		}
		echo '</div>';
	}
	
	echo '<div style="height: 10px; clear:both;"></div>';
	/*
	
	//BAND
	$res_band = DBConnect::query("SELECT * FROM data_bandmember ORDER BY `order`", __FILE__, __LINE__);
	if(mysql_num_rows($res_band) > 0)
	{
		echo '<div class="medium_box box">';
		while($member = fetch_db($res_band))
		{
			echo '<div class="bandmember">';
			
			if(trim($member["image"]) != "")
			{
				echo '<div class="image">
						<img src="' . $member["format"] . '"/>
					</div>';
			}
			else
			{
				echo '<div class="image"></div>';
			}
			
			echo '<div class="textcontainer">';
			echo '<h3>' . $member["name"] .  ' <br><span style="font-size: 12px;">' . $member["instrument"] .  '</span></h3>';
			echo '<p>' . $member["text"] .  '</p>';
			
			echo '</div>
					<div style="height: 0px; clear:both;"></div>
				</div>';
		}
		echo '</div>';
	}
	*/
?>

<script language="javascript">
	$(".bio .header").click(function(){
		if($(this).parent().hasClass("open"))
		{
			$(".bio.open").find(".text").hide(300);		
			$(".bio.open").removeClass("open");
		}
		else
		{
			$(".bio.open").find(".text").hide(300);	
			$(".bio.open").removeClass("open");
			$(this).parent().find(".text").show(300);
			$(this).parent().addClass("open");
		}
	});
	
	$(".fancybox").fancybox({
		openEffect  : 'none',
		closeEffect : 'none',
		iframe : {
			preload: false
		}
	});
</script>