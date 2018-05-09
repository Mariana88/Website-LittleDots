<?php
	//Filenames is een klasse die helpt bij het bewerken van bestandsnamen. Je weet hoe klote het kan zijn om een unieke naam te vinden maar de extentie te behouden enz.
	class Files
	{
		static public $mime_types_app = array(
	
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',
	
				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
	
				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',
	
				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',
	
				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',
	
				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
	
				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);
		
		static public $file_types_icon = array(
				'aiff' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/aiff.png'),
				'avi' => array('mini' => '/css/back/icon/file/mini/video.gif', '64' => '/css/back/icon/file/64/avi.png'),
				'bmp' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/bmp.png'),
				'css' => array('mini' => '/css/back/icon/file/mini/conf.png', '64' => '/css/back/icon/file/64/css.png'),
				'dat' => array('mini' => '/css/back/icon/file/mini/txt.png', '64' => '/css/back/icon/file/64/dat.png'),
				'divx' => array('mini' => '/css/back/icon/file/mini/video.gif', '64' => '/css/back/icon/file/64/divx.png'),
				'doc' => array('mini' => '/css/back/icon/file/mini/doc.png', '64' => '/css/back/icon/file/64/doc.png'),
				'docx' => array('mini' => '/css/back/icon/file/mini/doc.png', '64' => '/css/back/icon/file/64/doc.png'),
				'dot' => array('mini' => '/css/back/icon/file/mini/doc.png', '64' => '/css/back/icon/file/64/doc.png'),
				'gif' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/gif.png'),
				'html' => array('mini' => '/css/back/icon/file/mini/htm.png', '64' => '/css/back/icon/file/64/html.png'),
				'htm' => array('mini' => '/css/back/icon/file/mini/htm.png', '64' => '/css/back/icon/file/64/html.png'),
				'java' => array('mini' => '/css/back/icon/file/mini/file.png', '64' => '/css/back/icon/file/64/java.png'),
				'js' => array('mini' => '/css/back/icon/file/mini/file.png', '64' => '/css/back/icon/file/64/java.png'),
				'jpg' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/jpg.png'),
				'jpeg' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/jpg.png'),
				'log' => array('mini' => '/css/back/icon/file/mini/file.png', '64' => '/css/back/icon/file/64/log.png'),
				'm4a' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/m4a.png'),
				'mid' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/mid.png'),
				'mov' => array('mini' => '/css/back/icon/file/mini/video.gif', '64' => '/css/back/icon/file/64/mov.png'),
				'mp2' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/mp2.png'),
				'mp3' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/mp3.png'),
				'mp4' => array('mini' => '/css/back/icon/file/mini/video.gif', '64' => '/css/back/icon/file/64/mp4.png'),
				'mpeg' => array('mini' => '/css/back/icon/file/mini/video.gif', '64' => '/css/back/icon/file/64/mpeg.png'),
				'pdf' => array('mini' => '/css/back/icon/file/mini/pdf.png', '64' => '/css/back/icon/file/64/pdf.png'),
				'php' => array('mini' => '/css/back/icon/file/mini/file.png', '64' => '/css/back/icon/file/64/php.png'),
				'ppt' => array('mini' => '/css/back/icon/file/mini/ppt.png', '64' => '/css/back/icon/file/64/ppt.png'),
				'pps' => array('mini' => '/css/back/icon/file/mini/ppt.png', '64' => '/css/back/icon/file/64/ppt.png'),
				'ppsx' => array('mini' => '/css/back/icon/file/mini/ppt.png', '64' => '/css/back/icon/file/64/ppt.png'),
				'psd' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/psd.png'),
				'rar' => array('mini' => '/css/back/icon/file/mini/zip.png', '64' => '/css/back/icon/file/64/rar.png'),
				'tiff' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/tiff.png'),
				'txt' => array('mini' => '/css/back/icon/file/mini/txt.png', '64' => '/css/back/icon/file/64/txt.png'),
				'wav' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/wav.png'),
				'wma' => array('mini' => '/css/back/icon/file/mini/sound.gif', '64' => '/css/back/icon/file/64/wma.png'),
				'wmv' => array('mini' => '/css/back/icon/file/mini/video.gif', '64' => '/css/back/icon/file/64/wmv.png'),
				'xls' => array('mini' => '/css/back/icon/file/mini/xls.gif', '64' => '/css/back/icon/file/64/xls.png'),
				'xlt' => array('mini' => '/css/back/icon/file/mini/xls.gif', '64' => '/css/back/icon/file/64/xls.png'),
				'xml' => array('mini' => '/css/back/icon/file/mini/xml.png', '64' => '/css/back/icon/file/64/xml.png'),
				'zip' => array('mini' => '/css/back/icon/file/mini/zip.png', '64' => '/css/back/icon/file/64/zip.png'),
				'png' => array('mini' => '/css/back/icon/file/mini/image.gif', '64' => '/css/back/icon/file/64/png.png'),
				
				'default' => array('mini' => '/css/back/icon/file/mini/file.png', '64' => '/css/back/icon/file/64/file.png')
			);
		//functie die een gegevens filename uniek maakt in zijn map door er een nummer suffix aan te zetten
		function make_unique($filename)
		{
			$return = $filename;
			if(is_dir($filename))
			{
				$i = 1;
				while(is_dir($return))
				{
					$return = Files::add_suffix($filename, "_" . $i);
					$i++;
				}
			}
			else
			{
				$i = 1;
				while(file_exists($return))
				{
					$return = Files::add_suffix($filename, "_" . $i);
					$i++;
				}
			}
			return $return;
		}
		
		//functie die een suffix voor de extention plaatst
		function add_suffix($filename, $suffix)
		{
			//we halen de extentie eraf
			$tmp_split = explode(".", $filename);
			if(count($tmp_split) > 1)
			{
				$extention = $tmp_split[count($tmp_split) - 1];
				$first_part = substr($filename, 0, strlen($filename) - strlen($extention) - 1);
				return $first_part . $suffix . "." . $extention;
			}
			else
				return $filename . $suffix;
		}
		
		function subtract_filename($filename)
		{
			$filenames = explode("/", $filename);
			return $filenames[count($filenames) - 1];
		}
		
		function subtract_extention($filename)
		{
			$filenames = explode(".", $filename);
			return $filenames[count($filenames) - 1];
		}
		
		//functie die checkt of een dir subdirs heeft
		function check_subdir($path)
		{
			if(!is_dir($path))
				return false;
			if ($handle = opendir($path)) 
			{
				while (false !== ($file = readdir($handle))) 
				{
					if(!is_dir($path . $file) || $file=="." || $file=="..")
						continue;
					closedir($handle);
					return true;
				}
				closedir($handle);
				return false;
			}
		}
		
		
		function file_type_icon($file, $big = false)
		{
			$file = str_replace($_SERVER['DOCUMENT_ROOT'], "", $file);
			
			if(is_dir($file))
			{
				if($big)
					return '/css/back/icon/file/64/folder.png';
				else
					return '/css/back/icon/file/mini/folder.gif';
			}
			else
			{
				$ext = strtolower(Files::subtract_extention($file));
				if(isset(Files::$file_types_icon[$ext]))
				{
					if($big)
					{
						if(strtolower($ext) == "jpg" || strtolower($ext) == "jpeg" || strtolower($ext) == "png" || strtolower($ext) == "gif")
						{
							//return $file;
							/*$returnstr = str_replace("userfiles/", "picsysthumb/", $file);
							if(substr($returnstr, 0, 1) != "/")
								$returnstr = "/" . $returnstr; */
							return Pictures::system_thumb($file);
						}
						else
							return Files::$file_types_icon[$ext]['64'];
					}
					else
						return Files::$file_types_icon[$ext]['mini'];
				}
				else
				{
					if($big)
						return Files::$file_types_icon['default']['64'];
					else
						return Files::$file_types_icon['default']['mini'];
				}
			}
		}
		
		 function delete_directory($dirname)
		 {
    		if (is_dir($dirname))
        		 $dir_handle = opendir($dirname);
			if (!$dir_handle)
         		return false;
     		while($file = readdir($dir_handle)) 
			{
				 if ($file != "." && $file != "..") 
				 {
					 if (!is_dir($dirname."/".$file))
					 {
						 Files::delete_file($dirname.'/'.$file);
					 }
					 else
						 Files::delete_directory($dirname.'/'.$file); 
				 }
			 }
			 closedir($dir_handle);
			 rmdir($dirname);
			 if(is_dir(str_replace("userfiles", "picsysthumb", $dirname)))
				rmdir(str_replace("userfiles", "picsysthumb", $dirname));
			 return true;
		}
		
		function delete_file($path)
		{
			unlink($path);
			//uit db verwijderen
			$res = DBConnect::query("SELECT * FROM `site_files` WHERE `path`='" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $path)) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				//checken of de file een thumb had
				if(is_file($_SERVER['DOCUMENT_ROOT'] . stripslashes($row["pic_thumb"])))
					unlink($_SERVER['DOCUMENT_ROOT'] . stripslashes($row["pic_thumb"]));
				//zoeken naar afgeleiden
				$res_der = DBConnect::query("SELECT * FROM `site_files_derived` WHERE `file_id`='" . $row["id"] . "'", __FILE__, __LINE__);
				while($row_der = mysql_fetch_array($res_der))
				{
					//file deleten
					unlink($_SERVER['DOCUMENT_ROOT'] . stripslashes($row_der["path"]));
				}
				DBConnect::query("DELETE FROM `site_files_derived` WHERE `file_id`='" . $row["id"] . "'", __FILE__, __LINE__);
				DBConnect::query("DELETE FROM `site_files` WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
			}
		}
		
		function filesize_str($file)
		{
			$file = str_replace($_SERVER['DOCUMENT_ROOT'], "", $file);
			if(substr($file, 0, 1) == "/")
				$file = substr($file, 1);
			if(!is_dir($file))
			{
				if($fsize = filesize($file))
				{
					if($fsize < 1024)
						return $fsize . " byte";
					elseif($fsize < 1048576)
						return round($fsize/1024, 2) . " Kb";
					elseif($fsize < 1073741824)
						return round($fsize/1048576, 2) . " Mb";
					else
						return round($fsize/1073741824, 2) . " Gb";
				}
				else
					return "";
			}
			else
				return "";
		}
		
		function recurse_copy($src,$dst) {
			$dir = opendir($src);
			@mkdir($dst);
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($src . '/' . $file) ) {
						Files::recurse_copy($src . '/' . $file,$dst . '/' . $file);
					}
					else {
						copy($src . '/' . $file,$dst . '/' . $file);
					}
				}
			}
			closedir($dir);
		} 
		
		function get_dbfile_path($id)
		{
			$res = DBConnect::query("SELECT `path` FROM `site_files` WHERE `id`='" . addslashes($id) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
				return stripslashes($row["path"]);
			else
				return "";
		}
		
		function get_dbfile_id($path)
		{
			$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
			if(substr($path, 0, 1) != "/")
				$path = "/" . $path;
			$res = DBConnect::query("SELECT `id` FROM `site_files` WHERE `path`='" . addslashes($path) . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
				return stripslashes($row["id"]);
			else
			{
				//rij invoegen
				DBConnect::query("INSERT INTO `site_files` (`id`, `path`) VALUES ('', '" . addslashes($path) . "')", __FILE__, __LINE__);
				return DBConnect::get_last_inserted('site_files', 'id');
			}
		}
		
		function rename_file($oldpath, $newname)
		{
			$oldpath = "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", $oldpath);
			$oldpath = str_replace("//", "/", $oldpath);
			$chomps = explode("/", $oldpath);
			if(trim($newname) == "")
			{
				return false;
			}
			else
			{
				$newname_real = "";
				for($i = 0 ; $i < (count($chomps)-1) ; $i++)
				{
					if(trim($chomps[$i]) != "")
					{
						if($newname_real != "")
							$newname_real .= "/";
						$newname_real .= $chomps[$i];
					}
				}
				$newname_real .= "/" . $newname;
				//check if the user didn't add a suffix
				if (count(explode(".", $newname)) <= 1)
				{
					//als de oude filename een suffic heeft dan nemen we die over
					$chomps = explode(".", $chomps[count($chomps)-1]);
					if(count($chomps) >= 2)
						$newname_real .= "." . $chomps[count($chomps)-1];
				}
				//we do a make unique
				$newname_real = Files::make_unique($_SERVER['DOCUMENT_ROOT'] . $newname_real);
				rename($_SERVER['DOCUMENT_ROOT'] . $oldpath, $newname_real);
				//DBConnect::query("UPDATE `site_files` SET `path`=REPLACE(`path`,'" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $oldpath)) . "','" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $newname_real)) . "') WHERE `path` LIKE '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $oldpath)) . "%'", __FILE__, __LINE__);
				DBConnect::query("UPDATE `site_files` SET `path`='/" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $newname_real)) . "' WHERE `path`='" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $oldpath)) . "'", __FILE__, __LINE__);
				
				return str_replace($_SERVER['DOCUMENT_ROOT'], "", $newname_real);
			}
		}
		
		function create_folder($parent, $name)
		{
			$parent = $_SERVER['DOCUMENT_ROOT'] . $parent;
			$newpath = $parent . '/' . $name;
			$newpath = str_replace('//', '/', $newpath);
			$new_path_root = $newpath;
			$counter = 1;
			while(is_dir($newpath))
			{
				$newpath = $new_path_root . '_' . $counter;
				$counter++;
			}
			mkdir($newpath, 0777);
			return $newpath;
		}
		
		function formatBytes($bytes, $precision = 2) { 
			$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		
			$bytes = max($bytes, 0); 
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
			$pow = min($pow, count($units) - 1); 
		
			// Uncomment one of the following alternatives
			 $bytes /= pow(1024, $pow);
			// $bytes /= (1 << (10 * $pow)); 
		
			return round($bytes, $precision) . ' ' . $units[$pow]; 
		} 
	}
?>