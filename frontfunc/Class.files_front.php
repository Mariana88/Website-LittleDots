<?php
	//Filenames is een klasse die helpt bij het bewerken van bestandsnamen. Je weet hoe klote het kan zijn om een unieke naam te vinden maar de extentie te behouden enz.
	class files_front
	{
		function get_dbfile_path($id)
		{
			$res = DBConnect::query("SELECT `path` FROM `site_files` WHERE `id`='" . addslashes($id) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
				return stripslashes($row["path"]);
			else
				return NULL;
		}
		
		function get_dbfileformat_path($id)
		{
			$res = DBConnect::query("SELECT `path` FROM `site_files_derived` WHERE `id`='" . addslashes($id) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
				return stripslashes($row["path"]);
			else
				return NULL;
		}
		
		function get_video_id($url)
		{
			$img_url = "";
			if(strstr($url, "youtube.com"))
			{
				$urlinfo = parse_url($url);
				$tmp = explode("&", $urlinfo["query"]);
				$vid_id = "";
				foreach($tmp as $queryitem)
				{
					$tmpitem = explode("=", $queryitem);
					if($tmpitem[0] == "v")
						$vid_id = $tmpitem[1];
				}
				return $vid_id;
			}
			elseif(strstr($url, "vimeo.com"))
			{
				$tmp = explode("vimeo.com/", $url);
				$vid_id = "";
				if(is_numeric($tmp[1]))
					$vid_id = $tmp[1];
				else
				{
					$tmp = explode("/", $tmp[1]);
					$vid_id = $tmp[0];
				}
				return $vid_id;
			}
		}
		
		function videolink($url, $intern)
		{
			$img_url = "";
			if(strstr($url, "youtube.com"))
			{
				if($intern)
					echo '<div>[youtube=' . $url . ']</div>';
				else
				{
					$urlinfo = parse_url($url);
					$tmp = explode("&", $urlinfo["query"]);
					$vid_id = "";
					foreach($tmp as $queryitem)
					{
						$tmpitem = explode("=", $queryitem);
						if($tmpitem[0] == "v")
							$vid_id = $tmpitem[1];
					}
					$img_url = 'http://img.youtube.com/vi/' . $vid_id . '/1.jpg';
				}
			}
			elseif(strstr($url, "vimeo.com"))
			{
				if($intern)
					echo '<div>[vimeo=' . $url . ']</div>';
				else
				{
					$tmp = explode("vimeo.com/", $url);
					$vid_id = "";
					if(is_numeric($tmp[1]))
						$vid_id = $tmp[1];
					else
					{
						$tmp = explode("/", $tmp[1]);
						$vid_id = $tmp[0];
					}
					//ophalen van info php
					$file = fopen("http://vimeo.com/api/v2/video/" . $vid_id . ".php", "r");
    				$videoinfo = unserialize(stream_get_contents($file));
					fclose($file);
					$img_url = $videoinfo[0]["thumbnail_medium"];
					//echo '<div style="float:left; padding:10px;"><a href="' . $url . '" target="_blank"><img src="' . $videoinfo[0]["thumbnail_large"] . '"/></a></div>';
				}
			}
			elseif(strstr($url, "flickr.com"))
				echo '<div style="float:left; padding:10px;">[flickr=' . $url . ']</div>';
			elseif(strstr($url, "livestream.com"))
				echo '<div style="float:left; padding:10px;">[livestream=' . $url . ']</div>';
			elseif(strstr($url, "ustream.tv"))
				echo '<div style="float:left; padding:10px;">[ustream=' . $url . ']</div>';
			if(!$intern)
			{
				echo '<div style="height: 87px; width: 155px; overflow: hidden;"><img width="155px" height="87px" src="' . $img_url . '"/></div>';
			}
		}
	}
?>