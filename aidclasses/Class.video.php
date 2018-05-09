<?php
	//pictures is een hulpklasse bij het processen van pictures
	class Video
	{
		//functie die een thumbnail maakt van een pic
		static function analyseUrl($url)
		{
			if(strstr($url, 'youtube.com'))
			{
				$return = array();
				$return["platform"] = "youtube";
				$tmp = parse_url($url);
				parse_str($tmp["query"], $query);
				$return["id"] = $query["v"];
				$return["thumb"] = 'http://img.youtube.com/vi/' . $return["id"] . '/3.jpg';
				$return["image"] = 'http://img.youtube.com/vi/' . $return["id"] . '/0.jpg';
				
				//checken of het bestaat
				/*$headers = get_headers('http://gdata.youtube.com/feeds/api/videos/' . $return["id"] . '?v=2&alt=json');
				if (!strpos($headers[0], '200')) {
					return false;
				}*/
				//$json_output = file_get_contents('http://gdata.youtube.com/feeds/api/videos/' . $return["id"] . '?v=2&alt=json');
				//$json = json_decode($json_output, true);
				//$return["description"] = $json['entry']['media$group']['media$description']['$t'];
				//$return["title"] = $json['entry']['title']['$t'];
				$return["link"] = $url;
				return $return;
				
			}
			elseif(strstr($url, 'vimeo.com'))
			{
				$return = array();
				$return["platform"] = "vimeo";
				
				$tmp = parse_url($url);
				$return["id"] = substr($tmp["path"], 1);
				$php_output = file_get_contents('http://vimeo.com/api/v2/video/' . $return["id"] . '.php');
				$data = unserialize($php_output);
				
				$return["thumb"] = $data[0]["thumbnail_small"];
				$return["image"] = $data[0]["thumbnail_large"];
				
				$return["description"] = $data[0]["description"];
				$return["title"] = $data[0]["title"];
				$return["link"] = $url;
				return $return;
			}
			else
			{
				return false;
			}
		}
		
		static function echoVideoInfoBack($url)
		{
			if($data = Video::analyseUrl($url))
			{
				$data["description"] = str_replace(array('<br>','<BR>', '<br/>', '<BR/>', '<br />', '<BR />', '</br>', '</BR>'), ' ', $data["description"]);
				if(strlen($data["description"]) > 200)
					$data["description"] = substr($data["description"], 0, 170) . "...";
				echo '<div class="computerdata">
							<a href="' . $data["link"] . '" target="_blank"><img class="systemthumb" style="float:left; margin-right: 8px; width:120px;" src="' . $data["thumb"] . '"/></a>title: ' . $data["title"] . '<br>
							description: ' . $data["description"] . '<br>
							<a href="' . $data["link"] . '" target="_blank">watch it</a>
						<div style="height: 0px,"></div>
						</div>';
			}
			else
			{
				echo '<div class="computerdata error">This is not a valid Youtube or vimeo link</div>';	
			}
		}
		
		static function echoVideoFront($url, $width = 400, $showmeta = false)
		{
			$height = (int)(($width * 9)/16);
			if($data = Video::analyseUrl($url))
			{
				if($showmeta)
				{
					echo '<h2>' . $data["title"] . '</h2>';	
				}
				echo '<div class="inline_video" style="width: ' . $width . 'px;">';
				
				if($data["platform"] == "youtube")
				{
					echo '<div class="youtubecontainer" vidwidth="' . $width . '" vidheight="' . $height . '" vidid="' . $data["id"] . '" id="yt' . $data["id"] . '"></div>';
					//echo '<iframe width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $data["id"] . '" frameborder="0" allowfullscreen></iframe>';
				}
				elseif($data["platform"] == "vimeo")
				{
					echo '<iframe class="vimeocontainer" id="vimeo' . $data["id"] . '" src="http://player.vimeo.com/video/' . $data["id"] .  '?byline=0&portrait=0&api=1&player_id=vimeo' . $data["id"] . '" width="' . $width . '" height="' .  $height. '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				}
				
				echo '</div>';
				if($showmeta)
				{
					echo '<p>' . $data["description"] . '</p>';	
				}
			}
		}
	}
?>