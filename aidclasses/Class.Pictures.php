<?php
	//pictures is een hulpklasse bij het processen van pictures
	class Pictures
	{
		//functie die een thumbnail maakt van een pic
		function create_thumb($name,$filename,$new_w,$new_h, $watermark = NULL)
		{
			//eerst checken of de dir bestaat
			$path_parts = pathinfo($filename);
			$type = exif_imagetype($name);
			
			if(!file_exists($path_parts["dirname"]))
			{
				mkdir($path_parts["dirname"], 0777, true);
			}
			
			//we zetten eerst en vooral de imgTooBig op de plaats, voor alloc memory probleem
			//----------------------------------------------------------------------------------
			$src_img=imagecreatefromgif($_SERVER['DOCUMENT_ROOT'] . "/watermarks/imgTooBig.gif");
			$dst_img=ImageCreateTrueColor($new_w,$new_h);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, 120, 120, 120, 120); 
			if ($type == 3)
				imagepng($dst_img,$filename); 
			elseif($type == 2) 
				imagejpeg($dst_img,$filename, 95); 
			elseif($type == 1) 
				imagegif($dst_img,$filename);
				 
			imagedestroy($dst_img); 
			imagedestroy($src_img); 
			//---------------------------------------------------------------------------------
			
			$src_img=NULL;
			if ($type==1)
				$src_img=imagecreatefromgif($name);
			elseif ($type==2)
				$src_img=imagecreatefromjpeg($name);
			elseif($type == 3)
				$src_img=imagecreatefrompng($name);
			else
				return 0;
			$old_w=imageSX($src_img);
			$old_h=imageSY($src_img);
			
			$src_w;
			$src_h;
			$src_x;
			$src_y;
			if($old_w/$new_w > $old_h/$new_h) //we moeten strippen in de breedte
			{
				//eerst origineel vergroten/verkleinen tot hoogte correct is
				$src_h = $old_h;
				$src_w = (int)($new_w * ($old_h/$new_h));
				//
				$src_x = (int)(($old_w - $src_w) / 2);
				$src_y = 0;
			}
			else
			{
				$src_w = $old_w;
				$src_h = (int)($new_h * ($old_w/$new_w));
				//
				$src_x = 0;
				$src_y = (int)(($old_h - $src_h) / 2);
			}
			
			$dst_img=ImageCreateTrueColor($new_w,$new_h);
			imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $new_w, $new_h, $src_w, $src_h); 
			if($watermark && trim($watermark) != "")
			{
				$watermark = imagecreatefrompng($watermark);  
				$watermark_width = imagesx($watermark);  
				$watermark_height = imagesy($watermark);  
				imagecopy($dst_img, $watermark, 0, 0, 0, 0, $watermark_width, $watermark_height);      
				imagedestroy($watermark);
			}
			
			if ($type == 3)
				imagepng($dst_img,$filename); 
			elseif($type == 2) 
				imagejpeg($dst_img,$filename, 95); 
			elseif($type == 1) 
				imagegif($dst_img,$filename);
				 
			imagedestroy($dst_img); 
			imagedestroy($src_img); 
		}
		
		function get_systemthumb_path($id)
		{
			//return ("kakaka_" . $id);
			$res = DBConnect::query("SELECT * FROM site_files WHERE `id`='" . addslashes($id) . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($res);
			return stripslashes($row["pic_thumb"]);
		}
		
		function system_thumb($path)
		{
			$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
			$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . $path);
			//is het pad wel een pic??
			$file_info = getimagesize($path);
			if(empty($file_info))
				return NULL;
			
			//maken van de thumb filename
			$chomps = explode("/", $path);
			$newpath = $_SERVER['DOCUMENT_ROOT'] . "picsysthumb/" . $chomps[count($chomps)-1];
			$newpath = Files::make_unique($newpath);
			//we zoeken of de file al een thumb heeft
			$res = DBConnect::query("SELECT * FROM `site_files` WHERE `path`='/" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $path)) . "'");
			$row = mysql_fetch_array($res);
			if($row)
			{
				//checken of de thumb bestaat. zonee aanmaken
				if(is_file($_SERVER['DOCUMENT_ROOT'] . stripslashes($row["pic_thumb"])))
					return stripslashes($row["pic_thumb"]);
				else
				{
					//db updaten
					DBConnect::query("UPDATE `site_files` SET `pic_thumb`='/" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $newpath)) . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
					//aanmaken van thumb
					Pictures::create_thumb($path,$newpath, 120, 120);
					return '/' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $newpath);
				}
			}
			else
			{
				//aanmaken thumb en db rij
				DBConnect::query("INSERT INTO `site_files` (`id`, `path`, `pic_thumb`) VALUES('', '/" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $path)) . "', '/" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $newpath)) . "')", __FILE__, __LINE__);
				Pictures::create_thumb($path,$newpath, 120, 120);
				return str_replace($_SERVER['DOCUMENT_ROOT'], '', $newpath);
			}
		}
		
		function resize_pic($source, $new_w, $new_h, $deform)
		{
			$type = exif_imagetype($source);
			$src_img=NULL;
			if ($type==1)
				$src_img=imagecreatefromgif($source);
			elseif ($type==2)
				$src_img=imagecreatefromjpeg($source);
			elseif($type == 3)
				$src_img=imagecreatefrompng($source);
			else
				return 0;
			$old_x=imageSX($src_img);
			$old_y=imageSY($src_img);
			$thumb_w=1;
			$thumb_h=1;
			if($deform)
			{
				$thumb_w=$new_w;
				$thumb_h=$new_h;
			}
			else
			{
				if ($old_x > $old_y) {
					$thumb_w=$new_w;
					$thumb_h=$old_y*($new_h/$old_x);
				}
				if ($old_x < $old_y) {
					$thumb_w=$old_x*($new_w/$old_y);
					$thumb_h=$new_h;
				}
				if ($old_x == $old_y) {
					$thumb_w=$new_w;
					$thumb_h=$new_h;
				}
			}
			$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
			imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
			if ($type == 3)
				imagepng($dst_img); 
			elseif($type == 2) 
				imagejpeg($dst_img); 
			elseif($type == 1) 
				imagegif($dst_img);
			imagedestroy($dst_img); 
			imagedestroy($src_img); 
		}
		
		function output_resized_pic($source, $new_w, $new_h, $deform)
		{
			$type = exif_imagetype($source);
			$src_img=NULL;
			if ($type==1)
				$src_img=imagecreatefromgif($source);
			elseif ($type==2)
				$src_img=imagecreatefromjpeg($source);
			elseif($type == 3)
				$src_img=imagecreatefrompng($source);
			else
				return 0;
			$old_x=imageSX($src_img);
			$old_y=imageSY($src_img);
			
			$thumb_w=1;
			$thumb_h=1;
			if($deform)
			{
				$thumb_w=$new_w;
				$thumb_h=$new_h;
			}
			else
			{
				if($old_x/$new_w > $old_y/$new_h)
				{
					//naar de breedte aanpassen
					$thumb_w = $new_w;
					$thumb_h = (int)(($new_w/$old_x)*$old_y);
				}
				else
				{
					//aan de hoogte aanpassen
					$thumb_h = $new_h;
					$thumb_w = (int)(($new_h/$old_y)*$old_x);
				}
			}
			/*var_dump($size);
			echo "siz: " . $size["x"] . "x" . $$size["y"] . '<br>';
			echo "old: " . $old_x . "x" . $old_y . '<br>';
			echo "max: " . $new_w . "x" . $new_h . '<br>';
			echo "new: " . $thumb_w . "x" . $thumb_h . '<br>';*/
			$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
			imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
			if ($type == 3)
			{
				header('Content-type: image/png');
				imagepng($dst_img); 
			}
			elseif($type == 2) 
			{
				header('Content-type: image/jpeg');
				imagejpeg($dst_img); 
			}
			elseif($type == 1) 
			{
				header('Content-type: image/gif');
				imagegif($dst_img);
			}
			imagedestroy($dst_img); 
			imagedestroy($src_img); 
		}
		
		function validate_size($source, $check_width, $check_height)
		{
			$type = exif_imagetype($source);
			$src_img=NULL;
			if ($type==1)
				$src_img=imagecreatefromgif($source);
			elseif ($type==2)
				$src_img=imagecreatefromjpeg($source);
			elseif($type == 3)
				$src_img=imagecreatefrompng($source);
			else
				return 0;
			$old_x=imageSX($src_img);
			$old_y=imageSY($src_img);
			imagedestroy($src_img); 
			if($old_x == $check_width && $old_y == $check_height)
				return true;
			else
				return false;
		}
		
		function get_pic_height($source)
		{
			$type = exif_imagetype($source);
			$src_img=NULL;
			if ($type==1)
				$src_img=imagecreatefromgif($source);
			elseif ($type==2)
				$src_img=imagecreatefromjpeg($source);
			elseif($type == 3)
				$src_img=imagecreatefrompng($source);
			else
				return 0;
			
			return imageSY($src_img);
		}
		
		function get_pic_width($source)
		{
			$type = exif_imagetype($source);
			$src_img=NULL;
			if ($type == 1)
				$src_img=imagecreatefromgif($source);
			elseif ($type == 2)
				$src_img=imagecreatefromjpeg($source);
			elseif($type == 3)
				$src_img=imagecreatefrompng($source);
			else
				return 0;
			
			return imageSX($src_img);
		}
		
		function resize_picobject($src_img, $new_w, $new_h, $deform)
		{
			$old_x=imageSX($src_img);
			$old_y=imageSY($src_img);
			$thumb_w=1;
			$thumb_h=1;
			if($deform)
			{
				$thumb_w=$new_w;
				$thumb_h=$new_h;
				
				$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
				imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
				return $dst_img;
			}
			else
			{
				$part_w=$new_w;
				$part_h=$new_h;
				$start_x = 0;
				$start_y = 0;
				if($old_x/$old_y < $new_w/$new_h)
				{
					//smaller dan de nieuwe!! dus start_y moet ingesteld worden
					$part_x = $old_x;
					$part_y = ($new_w/$new_h) * $old_x;
					$start_y = ($old_y - $part_y)/2;
				}
				elseif($old_x/$old_y > $new_w/$new_h)
				{
					//breder dan de nieuwe!! dus start_y moet ingesteld worden
					$part_y = $old_y;
					$part_x = ($new_w/$new_h) * $old_y;
					$start_x = ($old_x - $part_x)/2;
				}
				else
				{
					$part_x = $old_x;
					$part_y = $old_y;
				}
				
				/*if ($old_y != $new_h) {
					$thumb_h=$new_h;
					$thumb_w=$old_x*($new_h/$old_y);
					$start_x = ($thumb_w-$new_w)/2;
					$start_y = 0;
				}
				if ($thumb_w < $new_h) {
					$thumb_w=$new_w;
					$thumb_h=$old_y*($new_w/$old_x);
					$start_x = 0;
					$start_y = ($thumb_h-$new_h)/2;
				}*/
				$dst_img=ImageCreateTrueColor($new_w,$new_h);
				imagecopyresampled($dst_img,$src_img,0,0,$start_x,$start_y,$new_w,$new_h,$part_x,$part_y); 
				//imagecopyresampled($dst_img,$src_img,0,0,$start_x,$start_y,$thumb_w,$thumb_h,$old_x,$old_x); 
				return $dst_img;
			}
			
		}
	}
?>