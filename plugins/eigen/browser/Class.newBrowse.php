<?php
	class newBrowse
	{
		public $protectedPath;
		public $publicPath;
		
		function __construct()
		{
			$this->basePath = 'userfiles';	
			$this->protectedPath = 'userfiles/protected';
			$this->publicPath = 'userfiles/public';
		}
		
		public function publish_optionbox()
		{
			echo '<div id="newBrowse_optionbox">
					<div class="toolbar">
						<div class="btn disabled" id="newBrowse_btn_delete"><img src="/css/back/icon/new/trash.png"></div>
						<div class="btn disabled" id="newBrowse_btn_newfolder"><img src="/css/back/icon/new/addFolder.png"></div>
						<div class="btn disabled fileinput-button" id="newBrowse_btn_upload"><img src="/css/back/icon/new/upload.png"><input id="fileupload" type="file" name="files[]" multiple></div>
						<div class="btn right" id="newBrowse_btn_fileinfo"><img src="/css/back/icon/new/eye.png"></div>
						<div class="btn right" id="newBrowse_btn_contract"><img src="/css/back/icon/new/min.png"></div>
						<div class="btn right" id="newBrowse_btn_expand"><img src="/css/back/icon/new/plus.png"></div>
					</div>
					<div id="newBrowse_upload_progress" class="newBrowse_progress" style="display: none;">
						<div class="progress-bar progress-bar-success"></div>
					</div>
					<div class="fileinfo" style="display: none; border: 1px solid #DDDDDD; border-radius: 2px; padding: 8px; margin-top: 8px; clear: both; height: 120px;">fileinfo: select a file</div>
				</div>';	
		}
		
		public function publish_tree()
		{
			echo '<ul id="treeview_browser" class="treeview">';
			//public
			echo '<li id="foldertree_' . $_SERVER['DOCUMENT_ROOT'] . $this->publicPath . '" path="/' . $this->publicPath . '"  is_folder="true" class="' . ((Files::check_subdir($_SERVER['DOCUMENT_ROOT'] . $this->publicPath))?'submenu':'') . '"><div class="media_root newBrowse_public">Public Files</div>';
			$this->tree_node_publish($_SERVER['DOCUMENT_ROOT'] . $this->publicPath);
			echo '</li>';
			
			//private
			echo '<li id="foldertree_' . $_SERVER['DOCUMENT_ROOT'] . $this->protectedPath . '" path="/' . $this->protectedPath . '"  is_folder="true" class="' . ((Files::check_subdir($_SERVER['DOCUMENT_ROOT'] . $this->protectedPath))?'submenu':'') . '"><div class="media_root newBrowse_private">Private Files</div>';
			$this->tree_node_publish($_SERVER['DOCUMENT_ROOT'] . $this->protectedPath);
			echo '</li>';
			
			echo '</ul>';
			?>
				<script type="text/javascript">ddtreemenu.createTree("treeview_browser", true);
                	$("#treeview_browser").newBrowse();
                </script>
            <?php
		}

		public function tree_node_publish($path)
		{
			//echo $path;
			if ($handle = opendir($path . '/')) 
			{
				$firstfound = false;
				$array_dir = array();
				$array_file = array();
				while (false !== ($file = readdir($handle))) 
				{
					//echo $path . '/' . $file . '<br>';
					
					if((!is_file($path . '/' . $file) && !is_dir($path . '/' . $file)) || $file=="." || $file=="..")
						continue;
					
					if(!$firstfound)
					{
						echo '<ul>';
						$firstfound = true;
					}
					if(is_dir($path . '/' . $file))
						$array_dir[] = $path . '/' . $file;
					elseif(is_file($path . '/' . $file))
						$array_file[] = $path . '/' . $file;
				}
				//var_dump($array_dir);
				sort($array_dir);
				sort($array_file);
				foreach($array_dir as $one)
				{
					$fileinfo = pathinfo($one);
					$relpath = '/' . str_replace($_SERVER['DOCUMENT_ROOT'], "", $one);
					echo '<li id="foldertree_' . $relpath . '" path="' . $relpath . '" ' . ((Files::check_subdir($one))?'class="submenu"':'') . ' is_folder="true">
								<div style="background-image: url(' . ((is_dir($one))?'/css/back/icon/file/mini/folder.gif':Files::$file_types_icon[strtolower($fileinfo['extension'])]['mini']) . ');" browserfile="false" browsertype="folder" tree_id="treeview_browser">' . htmlspecialchars($fileinfo["filename"]) . '</div>';
					if(is_dir($one))
						$this->tree_node_publish($one);
					echo '</li>';
				}
				foreach($array_file as $one)
				{
					$fileinfo = pathinfo($one);
					$relpath = '/' . str_replace($_SERVER['DOCUMENT_ROOT'], "", $one);
					$browsertype = "file";
					if(strtolower($fileinfo["extension"]) == "jpg" || strtolower($fileinfo["extension"]) == "png" || strtolower($fileinfo["extension"]) == "jpeg" || strtolower($fileinfo["extension"]) == "gif")
						$browsertype = "picture"; 
					if(strtolower($fileinfo["extension"]) == "mp3")
						$browsertype = "audio"; 
					echo '<li id="foldertree_' . $relpath . '" path="' . $relpath . '" ' . ((Files::check_subdir($one))?'class="submenu"':'') . ' is_folder="false">
								<div style="background-image: url(' . ((is_dir($one))?'/css/back/icon/file/mini/folder.gif':Files::$file_types_icon[strtolower($fileinfo['extension'])]['mini']) . ');" browserfile="true" browserextension="' . $fileinfo["extension"] . '" browsertype="' . $browsertype . '" browserfileId="' . Files::get_dbfile_id($relpath) . '" tree_id="treeview_browser">' . htmlspecialchars($fileinfo["filename"]) . '.' . $fileinfo["extension"] . '</div>';
					if(is_dir($one))
						$this->tree_node_publish($one);
					echo '</li>';
				}
				if($firstfound)
					echo '</ul>';
			
				closedir($handle);
			}
		}
		
		public function ajax()
		{
			//hanling the ajax calls
			switch($_GET["action"])
			{
				case "last_selected_folder":
					$_SESSION["newBrowse"]["last_selected_folder"] = urldecode($_GET["folder"]);
					break;
				case "get_icon_path":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<newBrowseReturn><path>' . urldecode($_GET["path"]) . '</path><img>';
					if(is_dir(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . urldecode($_GET["path"]))))
					{
						echo '/css/back/icon/file/mini/folder.gif';
					}
					else
					{
						$fileinfo = pathinfo(urldecode($_GET["path"]));
						echo Files::$file_types_icon[strtolower($fileinfo['extension'])]['mini'];
					}
					echo '</img></newBrowseReturn>';
					break;
				case "get_icon_path_and_info":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<newBrowseReturn><path>' . urldecode($_GET["path"]) . '</path>';
					if(is_dir(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . urldecode($_GET["path"]))))
					{
						echo '<img>/css/back/icon/file/mini/folder.gif</img><browsertype>folder</browsertype>';
					}
					else
					{
						$fileinfo = pathinfo(urldecode($_GET["path"]));
						echo  '<img>' . Files::$file_types_icon[strtolower($fileinfo['extension'])]['mini'] . '</img><browserfile>true</browserfile><browserfileid>' . Files::get_dbfile_id($_GET["path"]) . '</browserfileid>';
						$browsertype = "file";
						if(strtolower($fileinfo["extension"]) == "jpg" || strtolower($fileinfo["extension"]) == "png" || strtolower($fileinfo["extension"]) == "jpeg" || strtolower($fileinfo["extension"]) == "gif")
							$browsertype = "picture"; 
						if(strtolower($fileinfo["extension"]) == "mp3")
							$browsertype = "audio"; 
						echo '<browsertype>' . $browsertype . '</browsertype>';
						echo '<browserextension>' . $fileinfo["extension"] . '</browserextension>';
						//opzoeken van de file_id
					}
					
					//en nu de andere info
					echo '</newBrowseReturn>';
					break;
				case 'delete':
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					
					$doc = new SimpleXMLElement(urldecode(stripslashes($_GET["xml"])));
					
					foreach($doc->file as $file)
					{
						$path = $_SERVER['DOCUMENT_ROOT'] . substr($file[0], 1);
						if(is_dir($path))
						{
							Files::delete_directory($path);
						}
						else
						{
							Files::delete_file($path);
						}
					}
					echo urldecode(stripslashes($_GET["xml"]));
					break;
				case 'newFolder':
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					
					$doc = new SimpleXMLElement(urldecode(stripslashes($_GET["xml"])));
					if($newPath = Files::create_folder(((string)$doc->parentPath[0][0]), ((string)$doc->name[0][0])))
					{
						if(substr($newPath, strlen($newPath)-2, 1) == '/')
							$newPath = substr($newPath, 0, strlen($newPath)-1);
						$tmp = explode("/", $newPath);
						echo '<newBrowseNewFolder><parentPath><![CDATA[' . ((string)$doc->parentPath[0][0]) . ']]></parentPath><path>/' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $newPath) . '</path><name><![CDATA[' . $tmp[count($tmp)-1] . ']]></name></newBrowseNewFolder>';
					}
					else
						echo '<newBrowseNewFolder><error>Folder Could Not Be Created</error></newBrowseNewFolder>';
					
					break;
				case 'renameFile':
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					
					$doc = new SimpleXMLElement(urldecode(stripslashes($_GET["xml"])));
					if($newfilename = Files::rename_file(((string)$doc->path[0][0]), ((string)$doc->newname[0][0])))
					{
						$tmp = explode('/', $doc->path[0][0]);
						$newPath = "";
						for($i = 0 ; $i < (count($tmp)-1) ; $i++)
						{
							if($newPath != "")
								$newPath .= '/';
							$newPath .= $tmp[i];
						}
						$newPath .= '/' . $newfilename;
						
						$fileinfo = pathinfo($newfilename);
						echo '<newBrowseRenameFile><path><![CDATA[' . ((string)$doc->path[0][0]) . ']]></path><newpath>' . $newPath . '</newpath><file><![CDATA[' . $fileinfo["filename"] . ((trim($fileinfo["extension"]) != '')?'.':'') . $fileinfo["extension"] . ']]></file></newBrowseRenameFile>';
					}
					else
						echo '<newBrowseRenameFile><error>File Could Not Be Renamed</error></newBrowseRenameFile>';
					
					break;
				case 'fileInfo': 
					$doc = new SimpleXMLElement(urldecode(stripslashes($_GET["xml"])));
					$relPath = ((string)$doc->path[0][0]);
					if(trim($relPath) == "")
					{
						$relPath = Files::get_dbfile_path(((string)$doc->file_id[0][0]));
					}
					$absPath = $_SERVER['DOCUMENT_ROOT'] . substr($relPath, 1);
					$link = "http://" . $_SERVER['HTTP_HOST'] . $relPath;
					$pathinfo = pathinfo($relPath);
					if(strtolower($pathinfo["extension"]) == "jpg" || strtolower($pathinfo["extension"]) == "jpeg" || strtolower($pathinfo["extension"]) == "png" || strtolower($pathinfo["extension"]) == "gif")
					{
						echo '<img class="systemthumb" src="' . Pictures::system_thumb($relPath) . '" style="float: left; width: 120px; height: 120px; margin-right: 8px;"/>';	
						echo '<p class="computerdata"><b>Image size:</b> ' . Pictures::get_pic_width($absPath) . ' x ' . Pictures::get_pic_height($absPath) . '</p>';
					}
					else
					{
						echo '<img class="systemthumb" src="' . Files::$file_types_icon[strtolower($pathinfo["extension"])]['64'] . '" style="float: left; width: 64px; height: 64px; margin-right: 8px; margin-bottom: 8px; border: 1px solid #DDDDDD;"/>';	
					}
					echo '<p class="computerdata"><b>File size:</b> ' . Files::formatBytes(filesize($absPath)) . '</p>';
					echo '<p class="computerdata"><b>link: </b><a style="darklink" href="' . $link . '" target="blank">' . $link . '</a></p>';
					break;
			}
		}
	}