<?php
	//heeft de variabele $include_tracks nodig
	// array => array("title", "path")
	if(isset($include_tracks) && count($include_tracks) > 0)
	{
		//We tonen de tracklist
		echo '<div class="content_tracklist">';
		foreach($include_tracks as $track)
		{
			echo '<div path="' . $track["path"] . '" title="' . $track["title"] . '" class="content_track"><img src="/css/front/img/audioplaybluesmall.gif"/>' . $track["title"] . '</div>';	
		}
		echo '<div class="content_track_add_all">Listen All</div>';
		echo '</div>';
	}
?>
<script language="javascript">
	$(window).ready(function(){
		$(".content_track_add_all").click(function(){
			var addtrackcounter = 0;
			$(".content_track").each(function(){
				if(addtrackcounter == 0)
					addtrack($(this).attr("title"), $(this).attr("path"), true);
				else
					addtrack($(this).attr("title"), $(this).attr("path"), false);
				addtrackcounter++;
			});
		});
		
		$(".content_track").click(function(){
			addtrack($(this).attr("title"), $(this).attr("path"), true);
		});
		
		//ROLLOVER VAN TRACKLISTS
		$(".content_track").bind("mouseenter", function(){
			$(this).css("background-color", "#EEEEEE");
		});
		$(".content_track").bind("mouseleave", function(){
			$(this).css("background-color", "#FFFFFF");
		});
	});
	
	
</script>