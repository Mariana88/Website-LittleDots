<?php
	//Menu Component is a vertical or horizontal menu.
	class piccropper
	{
		static function ajax()
		{
			$path_original = urldecode($_GET["path_original"]);
			$path_dest = urldecode($_GET["path_dest"]);
			if(isset($_GET["format_id"]))
			{
				//we halen de paths uit de database
				$res = DBConnect::query("SELECT * FROM `site_files_derived` WHERE `id`='" . addslashes($_GET["format_id"]) . "'", __FILE__, __LINE__);
				$row_derived = mysql_fetch_array($res);
				$path_dest = str_replace("/picformats/", "picformats/", stripslashes($row_derived["path"]));
				$res = DBConnect::query("SELECT * FROM `site_files` WHERE `id`='" . $row_derived["file_id"] . "'", __FILE__, __LINE__);
				$row = mysql_fetch_array($res);
				$path_original = str_replace("/userfiles/", "userfiles/", stripslashes($row["path"]));
				//opzoeken van de breedte en hoogte
				$res_meta = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `id`='" . $row_derived["thumb_meta"] . "'", __FILE__, __LINE__);
				if($row_meta = mysql_fetch_array($res_meta))
				{
					$options = data_description::options_convert_to_array($row_meta["datadesc"], $row_meta["data_options"]);
					$_GET["dest_x"] = $options["format_x"];
					$_GET["dest_y"] = $options["format_y"];
					$_GET["watermark"] = $options["watermark"];
				}
				else
				{
					$_GET["dest_x"] = 100;
					$_GET["dest_y"] = 100;
				}
			}
			
			switch($_GET["action"])
			{
				case "load":
					
					//we bepalen hoe groot we het origineel tonen
					$max_w = 400;
					$max_h = 400;
					//max size displayed = 600 x 500
					$original_w = Pictures::get_pic_width($path_original);
					$original_h = Pictures::get_pic_height($path_original);
					$display_w = 0;
					$display_h = 0;
					if($max_h/$original_h < $max_w/$original_w) //resizen naar hoogte
					{
						$display_h = $max_h;
						$display_w = $original_w * ($max_h/$original_h);
					}
					else
					{
						$display_w = $max_w;
						$display_h = $original_h * ($max_w/$original_w);
					}
					echo '<div style="float:left">
							<div class="splitter_light"><span>Original Pic: Select area</span></div>
							<img id="cms2_open_picedit_src" style="width:' . $display_w . 'px; height:' . $display_h . 'px; border:1px solid #CCCCCC;" width="' . $display_w . 'px" height="' . $display_h . 'px" src="/' . $path_original . '">
						</div>';
					echo '<div style="float:left; padding-left: 8px;">
							<div class="splitter_light"><span>New format</span></div>
							<div style="overflow: hidden; width: ' . $_GET["dest_x"] .  'px; height: ' . $_GET["dest_y"] .  'px; border:1px solid #CCCCCC;">
								<img style="width:' . $display_w . 'px; height:' . $display_h . 'px; margin-left: -26px; margin-top: -11px;" src="/' . $path_original . '" id="cms2_open_picedit_dst">
							</div>
						</div>';
					echo '<script> 
						$(function() {
								window.cms_crop_preview = function(coords){
									
									if(coords.w == 0) coords.w = 1;
									if(coords.h == 0) coords.h = 1;
									var rx = ' . $_GET["dest_x"] . ' / (coords.w * (' . $original_w/$display_w . '));
									var ry = ' . $_GET["dest_y"] .  ' / (coords.h * (' . $original_h/$display_h . '));
									//alert(rx);
									$(\'#cms2_open_picedit_dst\').css({
										"width": Math.round(rx * ' . $original_w . ').toString() + "px",
										"height": Math.round(ry * ' . $original_h . ').toString() + "px",
										"margin-left": "-" + Math.round(rx * coords.x * ' . $original_w/$display_w . ').toString() + "px",
										"margin-top": "-" + Math.round(ry * coords.y * ' . $original_h/$display_h . ').toString() + "px"
									});
									
									$(\'#cms2_open_picedit_src\').attr("crop_x", Math.round(coords.x * ' . $original_w/$display_w . ')); 
									$(\'#cms2_open_picedit_src\').attr("crop_y", Math.round(coords.y * ' . $original_h/$display_h . ')); 
									$(\'#cms2_open_picedit_src\').attr("crop_w", Math.round(coords.w * ' . $original_w/$display_w . ')); 
									$(\'#cms2_open_picedit_src\').attr("crop_h", Math.round(coords.h * ' . $original_h/$display_h . ')); 
									
								};
								
								$(\'#cms2_open_picedit_src\').Jcrop({
									onChange: cms_crop_preview,
									onSelect: cms_crop_preview,
									setSelect:   [ 100, 100, 50, 50 ],
									aspectRatio: ' . $_GET["dest_x"] . ' / ' . $_GET["dest_y"] . '
								});
							});
						</script>';
					break;
				case "save":
					$targ_w = $_GET["dest_x"];
					$targ_h = $_GET["dest_y"];
					$jpeg_quality = 90;
					
					$type = exif_imagetype($path_original);
					$img_r=NULL;
					if ($type==1)
						$img_r=imagecreatefromgif($path_original);
					elseif ($type==2)
						$img_r=imagecreatefromjpeg($path_original);
					elseif($type == 3)
						$img_r=imagecreatefrompng($path_original);
					else
						break;
					
					$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
					
					imagecopyresampled($dst_r,$img_r,0,0,$_GET['crop_x'],$_GET['crop_y'],
						$targ_w,$targ_h,$_GET['crop_w'],$_GET['crop_h']);
						
					if($_GET["watermark"] && trim($_GET["watermark"]) != "")
					{
						$watermark = imagecreatefrompng($_GET["watermark"]);  
						$watermark_width = imagesx($watermark);  
						$watermark_height = imagesy($watermark);  
						imagecopy($dst_r, $watermark, 0, 0, 0, 0, $watermark_width, $watermark_height);      
						imagedestroy($watermark);
					}
				
					if ($type == 3)
						imagepng($dst_r,$path_dest); 
					elseif($type == 2) 
						imagejpeg($dst_r,$path_dest); 
					elseif($type == 1) 
						imagegif($dst_r,$path_dest);
						 
					imagedestroy($dst_r); 
					imagedestroy($img_r); 
					
					if(isset($_GET["img_id_after_update"]))
						echo $_GET["img_id_after_update"];
					break;
			}
		}
	}
?>