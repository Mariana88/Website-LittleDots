<?php
	$res_page = DBConnect::query("SELECT page_pics.*, page_pics_lang.* FROM page_pics, page_pics_lang WHERE page_pics.id=page_pics_lang.lang_parent_id AND page_pics_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_page = fetch_db($res_page);
	
	$res_pic = DBConnect::query("SELECT * FROM data_pics WHERE page_pics_id='" . $row_page["id"] . "' ORDER BY `order`", __FILE__, __LINE__);
	
	if(mysql_num_rows($res_pic) > 0)
	{
		echo '<div class="pics_title box"><div class="title"></div><div class="copyright"></div></div>';
		$width = ceil(sqrt(mysql_num_rows($res_pic))) * 108;
		echo '<div class="pics_thumbnail_box box" style="width: ' . $width . 'px;">
				<div class="scroll_top"><img src="/css/front/img/scroll_top.png"/></div>
				<div class="scroll_outer"><div class="scroll_inner">';
		$first = NULL;
		while($pic = fetch_db($res_pic))
		{
			if(!$first) $first = $pic;
			
			$absPath = $_SERVER['DOCUMENT_ROOT'] . $pic["picture"];
			echo '<img src="' . $pic["picture_thumbnail"] . '" class="pic_thumbnail box" picId="' . $pic["id"] . '" title="' . $pic["title"] . '" copyright="' . $pic["copyright"] . '" picture="' . $pic["picture"] . '" picHeight="' . Pictures::get_pic_height($absPath) . '"  picWidth="' . Pictures::get_pic_width($absPath) . '"/>';
		}
		echo '</div></div>
			<div class="scroll_bottom"><img src="/css/front/img/scroll_bottom.png"/></div>
		</div>';
		
		//echo '<div class="box pic_container"><h2>' . $first["title"] . '</h2><img src="' . $first["picture"] . '"/></div>';
	}
?>
<script language="javascript">
	//centreren van de pics
	var toppx = ((parseInt($(window).height()) - parseInt($(".pics_thumbnail_box").height())) / 2) - 21;
	var leftpx = (($(window).width()) - ($(".pics_thumbnail_box").width())) / 2;
	$(".pics_thumbnail_box").css({"top": toppx + "px", "right": leftpx + "px", "opacity": 1});
	
	
	$(".pic_thumbnail").click(function(){
		show_loader();
		//$(".pic_container").find("img").attr("src", $(this).attr("picture"));
		$("#site_background").attr("original_height", $(this).attr("picHeight"));
		$("#site_background").attr("original_width", $(this).attr("picWidth"));
		$("#site_background").attr("src", $(this).attr("picture"));
		if($(this).attr("title") != "" || $(this).attr("copyright") != "")
		{
			if($(this).attr("title") != "")
				$(".pics_title").find(".title").text($(this).attr("title")).css("display", "block");
			else
				$(".pics_title").find(".title").css("display", "none");
			if($(this).attr("copyright") != "")
				$(".pics_title").find(".copyright").html('&copy;&nbsp;' + $(this).attr("copyright")).css("display", "block");
			else
				$(".pics_title").find(".copyright").css("display", "none");
			$(".pics_title").css("display", "block");
		}
		else
		{
			$(".pics_title").css("display", "none");	
		}
		
		if($(".pics_thumbnail_box").attr("moved") != "true")
		{
			$(".pics_thumbnail_box").stop().animate({"top": "20px", "right": "20px", "opacity": "0.6", "width": "100px", "bottom": "20px"}, 500, function(){
				$(".scroll_outer").css("height", ($(window).height() - 120) + "px");
				$(".pics_thumbnail_box").hover(
				  function () {
					$(this).stop().animate({"opacity": "1"})
				  }, 
				  function () {
					$(this).stop().animate({"opacity": "0.6"})
				  }
				);																										  
			});
			
			$(".scroll_top, .scroll_bottom").stop().animate({"height": "16px", "margin-top": "4px", "margin-bottom": "4px"}, 500, function(){
						$(this).removeAttr('style');
						$(this).addClass("open");
			});			
			$(".pics_thumbnail_box").attr("moved", "true");
		}
	});
	
	$(".scroll_top").click(function(){
		if(parseInt($(".scroll_inner").css("margin-top")) < 0)
		{
			var top = parseInt($(".scroll_inner").css("margin-top")) + 200;
			if(top > 0) top = 0;
			
			$(".scroll_inner").stop().animate({"margin-top": top + "px"}, 300);
		}
	});
	
	$(".scroll_bottom").click(function(){
		if((-parseInt($(".scroll_inner").css("margin-top")) + $(".scroll_outer").height()) < $(".scroll_inner").height())
		{
			var top = parseInt($(".scroll_inner").css("margin-top")) - 200;
			if((-top + $(".scroll_outer").height()) > $(".scroll_inner").height())
				top = $(".scroll_outer").height() - $(".scroll_inner").height();
			
			$(".scroll_inner").stop().animate({"margin-top": top + "px"}, 300);
		}
	});
	
	
	$(window).resize(function(){
		$(".scroll_outer").css("height", ($(window).height() - 120) + "px");									 
	});
</script>