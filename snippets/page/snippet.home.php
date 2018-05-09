<?php
	$res_home = DBConnect::query("SELECT page_home.*, page_home_lang.* FROM page_home, page_home_lang WHERE page_home.id=page_home_lang.lang_parent_id AND page_home_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_home = fetch_db($res_home);
	
	//NEWS
	//merch
	$res_news = DBConnect::query("SELECT * FROM data_news WHERE home_id='" . $row_home["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	$counter = 0;
	while($news = fetch_db($res_news))
	{
		$counter++;
	
		echo '<div class="merch ' . (($counter == mysql_num_rows($res_news))?"noborder":"") . ' ' . ((trim($news["link"]) != "")?"link":"") . '" href="' . $news["link"] . '" newsid="' . $news["id"] . '">
				<img src="' . $news["format"] . '" class="shadow"/>
				<div class="text">
					<h2 class="title">' . $news["title"] . '</h2>
					<div class="description">' . $news["description"] . '</div>';
		echo '</div>
				<div style="height: 0px; clear:both;"></div>
			</div>
			<a style="display:none" target="_blank" href="' . $news["link"] . '" id="externalnewslink_' . $news["id"] . '"></a>';
	}
	if($_SERVER['QUERY_STRING'] != "page=/home")
	{
?>
<script>
	$("#main_header").hide();
	$("body").css("cursor","pointer");
	$("#logo").attr("ontop", "false");
	$("#main_content").hide();
	$("body").click(function(){
		$("#site_background").animate({opacity: 0.6}, 500);
		$("#logo").animate({'top': "0px"}, 500);
		$("#logo").attr("ontop", "true");
		$("#main_header").fadeIn(500);
		$("#main_content").fadeIn(500);
		$("body").unbind( "click" );
		$("body").css("cursor","default");
	});
	/*background_position();*/
	
	setTimeout(function(){
					$("body").click();
				}, 2000);
	
	
</script>
<?php
	}
?>
<script language="javascript">
	$('.merch.link').click(function(){
		if($(this).attr("href").indexOf("#!") > -1)
		{
			loadpage($(this).attr("href"));	
		}
		else
		{
			$("a#externalnewslink_" + $(this).attr("newsid"))[0].click();
		}
	});
</script>