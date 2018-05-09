<?php
	require_once('aidclasses/data/Class.Files.php');
	require_once('aidclasses/Class.Pictures.php');
	require_once('aidclasses/data/Class.datasource.php');
	
	class browser extends popup
	{
		function __construct($id, $width, $height)
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "browser");
			$this->set_config("basepath", "/userfiles/");
			if(!$this->get_config("current_path"))
				$this->set_config("current_path", $_SERVER['DOCUMENT_ROOT'] . "/userfiles");
			
			//we set the default configuration values
			$this->set_config("thumb_width", 100);
			$this->set_config("thumb_height", 100);
			$this->set_config("in_popup", false);
			$this->set_config("addbutton", false);
		}
		
		function set_addbutton() {$this->set_config("addbutton", true);}
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			$this->set_config("in_popup", $in_popup);
			if(isset($_GET["addbutton"]))
				$this->set_config("addbutton", true);
			$panelname = "popup_" . $this->id . "_html_panel";
			$this->publish_start($in_popup, $from_ajax);
			
			//NOW WE GET TO THE REAL PUBLISHING
			echo '<div id="superdiv">';
			//WE FIRST DISPLAY THE DIVS FOR THE THICKBOX POPUPS
			echo '	<script>
						br_id = "' . $this->id . '";
						br_root = "' . $_SERVER['DOCUMENT_ROOT'] . '"
					</script>
					<div id="sidebar" ' . (($this->get_config("in_popup"))?'style="height: 500px; overflow: auto;"':'') . '>
						<div id="colpan_browser_folder" class="CollapsiblePanel">
							<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_browser_folder, document.getElementById(\'colpan_browser_folder_plusminus\'));"><div class="divleft">Folders</div><div class="divright"><img id="colpan_browser_folder_plusminus" src="/css/back/icon/min.gif"/></div></div>
							<div class="CollapsiblePanelContent">
								<div class="iconcontainer">							
									<img id="browser_folder_addfolder" class="icon" title="add folder" src="/css/back/icon/twotone/gray/addfolder.gif">
									<img id="browser_folder_delete" class="icon" title="delete" src="/css/back/icon/twotone/gray/trash.gif">
									<img id="browser_folder_rename" class="icon" title="rename" src="/css/back/icon/twotone/gray/rename.gif">';
			if($_SESSION["login_usergroup_id"] == "1")
				echo '<img id="browser_folder_rights" class="icon" title="rights" src="/css/back/icon/twotone/gray/shield.gif">';
			echo				'</div>
								<div id="browser_folders_html_panel">';
			$this->publish_tree();
			echo '</div>
				</div>
			</div>';
			
?>
<script type="text/javascript">
	var swfu;

	window.onload = function() {
		var settings = {
			flash_url : "/plugins/swfupload/swfupload.swf",
			upload_url: "/ajax.php?sessid=<?php echo session_id();?>&popup_id=<?php echo $this->id;?>&littleupload=1",
			post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
			file_size_limit : "100 MB",
			file_types : "*.*",
			file_types_description : "All Files",
			file_upload_limit : 100,
			file_queue_limit : 0,
			custom_settings : {
				progressTarget : "fsUploadProgress",
				cancelButtonId : "btnCancel"
			},
			debug: false,

			// Button settings
			button_image_url: "/plugins/swfupload/Button 120x20.png",
			button_width: "100",
			button_height: "28",
			button_placeholder_id: "spanButtonPlaceHolder",
			button_text: '<span class="theFont">Upload Files</span>',
			button_text_style: ".theFont { font-size: 11; font-family: verdana; color: #FFFFFF; font-weight: bold;}",
			button_text_left_padding: 10,
			button_text_top_padding: 4,
			button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE, 
			
			// The event handler functions are defined in handlers.js
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete	// Queue plugin event
		};

		swfu = new SWFUpload(settings);
	 };
</script>
<div id="colpan_browser_upload" class="CollapsiblePanel">
	<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_browser_upload, document.getElementById('colpan_browser_upload_plusminus'));"><div class="divleft">Upload in current folder</div><div class="divright"><img id="colpan_browser_upload_plusminus" src="/css/back/icon/min.gif"/></div></div>
	<div class="CollapsiblePanelContent">
		<form id="form1" action="index.php" method="post" enctype="multipart/form-data">
				<span class="contentheader" id="fsUploadProgress"></span>
				<div id="divStatus" style="clear:both; padding-top: 8px; padding-bottom:8px;">0 Files Uploaded</div>
				<div style="clear:both; vertical-align: top;">
					<div style="float:left; z-index:-1;"><span id="spanButtonPlaceHolder" style="padding-top:4px;"></span></div>
					<div style="float:left;"><input id="btnCancel" type="button" value="Cancel All Uploads" onclick="swfu.cancelQueue();" style="width:100px; margin-left:4px; margin-top:0px" /></div>
				<div style="clear:both; "></div>
				<input type="checkbox" <?php echo ((isset($_SESSION["browser_overwrite"]) && $_SESSION["browser_overwrite"] == true)?' checked="checked" ':'');?> id="browser_overwrite_check" onChange="var poststr = 'overwrite=' + this.checked; br_send_ajax('POST', 'overwrite_state=1', poststr);">&nbsp;Overwrite files with the same name
				</div>
		</form>
	</div>
</div>
<div id="colpan_browser_clipboard" class="CollapsiblePanel">
	<div class="CollapsiblePanelTab" onClick="changeplusminus(colpan_browser_clipboard, document.getElementById('colpan_browser_clipboard_plusminus'));"><div class="divleft">Clipboard</div><div class="divright"><img id="colpan_browser_clipboard_plusminus" src="/css/back/icon/min.gif"/></div></div>
	<div class="CollapsiblePanelContent">
		<div id="browser_clipboard_div">No files cut or copied</div>
	</div>
</div>
<script>
	var colpan_browser_folder = new Spry.Widget.CollapsiblePanel("colpan_browser_folder");
	var colpan_browser_upload = new Spry.Widget.CollapsiblePanel("colpan_browser_upload");
	var colpan_browser_clipboard = new Spry.Widget.CollapsiblePanel("colpan_browser_clipboard");
</script>
<?php
			
			echo '</div>
					<script type="text/javascript">ddtreemenu.createTree("treeview_browser", true)</script>';
			echo '<div id="content">';
			echo '<div ' . (($this->get_config("in_popup"))?'style="height: 500px; overflow: auto;"':'') . '>';
			$this->display_files();
			echo '</div>
			
				</div>
			<div style="clear:both;"></div>
			</div>
			<script language="javascript">
				window.browser_folders_html_panel = new Spry.Widget.HTMLPanel("browser_folders_html_panel", { evalScripts: true });
				br_selected_folder = \'' . $this->get_config("current_path") . '\';
			</script>';
			//END OF THE REAL PUBLISHING
			$this->publish_end($in_popup, $from_ajax);
		}
		
		private function publish_tree()
		{
			$basepath = $this->get_config("basepath");
			if(substr($basepath, strlen($basepath)-1, 1) == "/")
				$basepath = substr($basepath, 0, strlen($basepath)-1);
			echo '<ul id="treeview_browser" class="treeview">
								<li id="foldertree_' . $_SERVER['DOCUMENT_ROOT'] . $basepath . '" folder="' . $_SERVER['DOCUMENT_ROOT'] . $basepath . '" ' . ((Files::check_subdir($_SERVER['DOCUMENT_ROOT'] . $basepath))?'class="submenu"':'') . '><div onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . $_SERVER['DOCUMENT_ROOT'] . $basepath . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&loaddir=' . urlencode($_SERVER['DOCUMENT_ROOT'] . $basepath) . '\'); document.getElementById(\'br_current_location_span\').innerHTML = \'/root\';">root</div>
									<ul>';
			//WE GET the files
			$this->tree_node_publish($_SERVER['DOCUMENT_ROOT'] . $this->get_config("basepath"));
			echo '</ul></li></ul>';
		}
		
		private function tree_node_publish($path)
		{
			/*
			if ($handle = opendir($path)) 
			{
				// This is the correct way to loop over the directory. 
				$firstfound = false;
				while (false !== ($file = readdir($handle))) 
				{
					if(!is_dir($path . $file) || $file=="." || $file=="..")
						continue;
					if(!$firstfound && $path != $_SERVER['DOCUMENT_ROOT'] . $this->get_config("basepath"))
					{
						echo '<ul>';
						$firstfound = true;
					}
					//Files::make_unique($test);
					echo '<li id="foldertree_' . $path . $file . '" folder="' . $path . $file . '" ' . ((Files::check_subdir($path . $file))?'class="submenu"':'') . '><div style="padding-left: 2px;" onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . $path . $file . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&loaddir=' . urlencode($path . $file) . '\'); document.getElementById(\'br_current_location_span\').innerHTML = \'' . str_replace($_SERVER['DOCUMENT_ROOT'] . 'userfiles', '/root', str_replace("//", "/", $path . $file)) . '\';"><img src="/css/back/icon/file/mini/folder.gif"/>' . $file . '</div>';
					//echo '<li id="foldertree_' . $path . $file . '" ' . ((Files::check_subdir($path . $file))?'class="submenu"':'') . '><img src="/css/back/icon/file/mini/folder.gif"/><a href="javascript:dummy();" style="padding-left: 2px;" onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . $path . $file . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/md5.php\'); document.getElementById(\'br_current_location_span\').innerHTML = \'' . str_replace($_SERVER['DOCUMENT_ROOT'] . 'userfiles', 'root', $path . $file) . '\';">' . $file . '</a>';
					$this->tree_node_publish($path . $file . "/");
					echo '</li>';
				}
				if($firstfound && $path != $_SERVER['DOCUMENT_ROOT'] . $this->get_config("basepath"))
					echo '</ul>';
			
				closedir($handle);
			}
			*/
			if ($handle = opendir($path)) 
			{
				$firstfound = false;
				$array = array();
				while (false !== ($file = readdir($handle))) 
				{
					if(!is_dir($path . $file) || $file=="." || $file=="..")
						continue;
					if(!$firstfound && $path != $_SERVER['DOCUMENT_ROOT'] . $this->get_config("basepath"))
					{
						echo '<ul>';
						$firstfound = true;
					}
					$array[] = $path . $file;
					
				}
				sort($array);
				foreach($array as $one)
				{
					$fileinfo = pathinfo($one);
					echo '<li id="foldertree_' . $one . '" folder="' . $one . '" ' . ((Files::check_subdir($one))?'class="submenu"':'') . '><div style="padding-left: 2px;" onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . $one . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&loaddir=' . urlencode($one) . '\'); document.getElementById(\'br_current_location_span\').innerHTML = \'' . str_replace($_SERVER['DOCUMENT_ROOT'] . 'userfiles', '/root', str_replace("//", "/", $one)) . '\';"><img src="/css/back/icon/file/mini/folder.gif"/>' . $fileinfo["filename"] . $fileinfo["extension"] . '</div>';
					$this->tree_node_publish($one . "/");
					echo '</li>';
				}
				if($firstfound && $path != $_SERVER['DOCUMENT_ROOT'] . $this->get_config("basepath"))
					echo '</ul>';
			
				closedir($handle);
			}
		}
		
		public function handle_ajax()
		{
			//for the browser we have to be logged in in the admin section
			if(!login::check_login())
				return "";
			
			switch($_GET["action"])
			{
				case "getfileid":
					echo Files::get_dbfile_id(urldecode($_POST["filepath"]));
					break;
				case "addfolder":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<browser action="addfolder">';
					$indir = urldecode($_GET["indir"]);
					$newname = urldecode($_GET["dirname"]);
					if($indir == "current")
						$indir = $this->get_config("current_path");
					if(!is_dir($indir))
					{
						echo "<status>NOK</status><error>The folder could not be created: the parent folder does not exits.</error></browser>";
						return;
					}
					elseif(trim($newname) == "")
					{
						echo "<status>NOK</status><error>Fill in a correct folder name.</error></browser>";
						return;
					}
					else
					{
						$new_dir_name = Files::make_unique($indir . "/" . $newname);
						mkdir($new_dir_name);
						$nodecontent = '<div href="javascript:dummy();" onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . $new_dir_name . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&loaddir=' . urlencode($new_dir_name) . '\'); document.getElementById(\'br_current_location_span\').innerHTML = \'' . str_replace($_SERVER['DOCUMENT_ROOT'] . 'userfiles', '/root', str_replace("//", "/", $new_dir_name)) . '\';"><img src="/css/back/icon/file/mini/folder.gif"/>' . Files::subtract_filename($new_dir_name) . '</div>';
						echo '<status>OK</status><dirname>' . $new_dir_name . '</dirname><parentdir>' . $indir . '</parentdir><content><![CDATA[' . $nodecontent . ']]></content></browser>';
						return;
					}
					break;
				case "changeview":
					$dg = $_SESSION["datagrids"]["browser"];
					if($_GET["view"] == "picture")
						$dg->picture_view = true;
					else
						$dg->picture_view = false;
					/*$dg->picture_html = '<div style="width: 80px; float:left; height:84px; padding:2px; margin:2px; border: 1px solid #CCCCCC; text-align:center; overflow:hidden;">
											[icon64]<br><span style="width:72px; font-size: 9px; color: #666666; padding-top:2px;">[filename]</span>
										</div>';
					$dg->picture_placeholder_style = "width: 100px; float:left; height:100px; padding:4px; margin:2px; border: 1px solid #CCCCCC; text-align:center";*/
					$_SESSION["datagrids"]["browser"] = $dg;
					break;
				case "rename":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<browser action="rename">';
					$newname = urldecode($_GET["newname"]);
					$oldname = "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", urldecode($_GET["oldname"]));
					$oldname = str_replace("//", "/", $oldname);
					$chomps = explode("/", $oldname);
					if(trim($newname) == "")
					{
						echo "<status>NOK</status><error>Fill in a correct file name.</error></browser>";
						return;
					}
					else
					{
						$newname_real = "";
						for($i = 0 ; $i < (count($chomps)-1) ; $i++)
						{
							if(trim($chomps[$i]) != "")
								$newname_real .= "/" . $chomps[$i];
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
						rename($_SERVER['DOCUMENT_ROOT'] . $oldname, $newname_real);
						//we henoemen ook de thumb
						//if(is_file(str_replace("userfiles", "picsysthumb", $_SERVER['DOCUMENT_ROOT'] . $oldname)) || is_dir(str_replace("userfiles", "picsysthumb", $_SERVER['DOCUMENT_ROOT'] . $oldname)))
						//	rename(str_replace("userfiles", "picsysthumb", $_SERVER['DOCUMENT_ROOT'] . $oldname), str_replace("userfiles", "picsysthumb", $newname_real));
						//aanpassen in`site_files`
						//DBConnect::query("UPDATE `site_files` SET `path`='" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $newname_real)) . "' WHERE `path`='" . addslashes($oldname) . "'");
						DBConnect::query("UPDATE `site_files` SET `path`=REPLACE(`path`,'" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $oldname)) . "','" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $newname_real)) . "') WHERE `path` LIKE '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $oldname)) . "%'", __FILE__, __LINE__);

						//STATUS OUTPUT
						$chomps = explode("/", $newname_real);
						echo "<status>OK</status><oldpath><![CDATA[" . urldecode($_GET["oldname"]) . "]]></oldpath><newpath><![CDATA[" . $newname_real . "]]></newpath><newname><![CDATA[" . $chomps[count($chomps)-1] . "]]></newname><fromfolderview>" . $_GET["fromfolderview"] . "</fromfolderview><currentfolder><![CDATA[" . $this->get_config("current_path") .  "]]></currentfolder></browser>";
						//echo "RENAMEFILE  OK :oldpath;;" . urldecode($_POST["oldname"]) . "#newpath;;" . $newname_real . "#newname;;" . $chomps[count($chomps)-1];	
					}		
					break;
				case "delfolder":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<browser action="delfolder">';
					$dirname = urldecode($_GET["dirname"]);
					if(!is_dir($dirname))
					{
						echo "<status>NOK</status><error>The folder could not be deleted: the folder does not exist.</error></browser>";
						//echo "DELFOLDER   ERR:The folder could not be deleted: the folder does not exist.";
						return;
					}
					else
					{
						Files::delete_directory($dirname);
						echo "<status>OK</status><folder><![CDATA[" . urldecode($_GET["dirname"]) . "]]></folder></browser>";
						return;
					}
					break;
				case "delfiles":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<browser action="delfiles">';
					$delfiles = explode("##", urldecode($_POST["delfile"]));
					$dirs = "";
					foreach($delfiles as $delfile)
					{
						if(is_file($delfile))
						{
							Files::delete_file($delfile);
							/*unlink($delfile);
							if(is_file(str_replace("userfiles", "picsysthumb", $delfile)))
								unlink(str_replace("userfiles", "picsysthumb", $delfile));*/
							echo '<file><![CDATA[' . $delfile . ']]></file>';
						}
						else
						{
							Files::delete_directory($delfile);
							echo '<folder><![CDATA[' . $delfile . ']]></folder>';
						}
					}
					echo '</browser>';
					break;
				case "pastefiles":
					header('Content-Type: text/xml');
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					echo '<browser action="pastefiles"><copyorcut>' . $_POST["copyorcut"] . '</copyorcut>';
					$pastefiles = explode("##", urldecode($_POST["pastefiles"]));
					$dirs = "";
					foreach($pastefiles as $pastefile)
					{
						$temp = explode("/", $pastefile);
						$new_loc = $this->get_config("current_path") . "/" . $temp[count($temp)-1];
						$new_loc = Files::make_unique($new_loc);
						
						if(is_file($pastefile))
						{
							if($_POST["copyorcut"] == "cut")
							{
								rename($pastefile, $new_loc);
								//if(is_file(str_replace("/userfiles/", "/picsysthumb/", $pastefile)))
								//	rename(str_replace("/userfiles/", "/picsysthumb/", $pastefile), str_replace("/userfiles/", "/picsysthumb/", $new_loc));
								//de database aanpassen
								DBconnect::query("UPDATE `site_files` SET `path`='" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_loc)) . "' WHERE `path`='" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $pastefile)) . "'", __FILE__, __LINE__);
								
							}
							if($_POST["copyorcut"] == "copy")
							{
								copy($pastefile, $new_loc);
								//if(is_file(str_replace("/userfiles/", "/picsysthumb/", $pastefile)))
								//	copy(str_replace("/userfiles/", "/picsysthumb/", $pastefile), str_replace("/userfiles/", "/picsysthumb/", $new_loc));
								//we copieren de rij uit de database
								$res_file = DBConnect::query("SELECT * FROM `site_files` WHERE `path`='" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $pastefile)) . "'", __FILE__, __LINE__);
								if($row_file = mysql_fetch_array($res_file))
								{
									DBConnect::query("INSERT INTO `site_files` (`id`, `path`, `copyright`, `description`) VALUES('', '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_loc)) . "', '" . $row_file["copyright"] . "', '" . $row_file["description"] . "')", __FILE__, __LINE__);
									Pictures::system_thumb($new_loc);
								}
							}
						}
						else
						{
							echo '<folder><oldid><![CDATA[' . $pastefile . ']]></oldid><newid><![CDATA[' . $new_loc . ']]></newid>';
							echo '<parentdir><![CDATA[' . $this->get_config("current_path") . ']]></parentdir>';
							$nodecontent = '<div href="javascript:dummy();" onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . $new_loc . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&loaddir=' . urlencode($new_loc) . '\'); document.getElementById(\'br_current_location_span\').innerHTML = \'' . str_replace($_SERVER['DOCUMENT_ROOT'] . 'userfiles', '/root', str_replace("//", "/", $new_loc)) . '\';"><img src="/css/back/icon/file/mini/folder.gif"/>' . Files::subtract_filename($new_loc) . '</div>';
							echo '<content><![CDATA[' . $nodecontent . ']]></content></folder>';
							if($_POST["copyorcut"] == "cut")
							{
								rename($pastefile, $new_loc);
								//if(is_dir(str_replace("/userfiles/", "/picsysthumb/", $pastefile)))
								//	rename(str_replace("/userfiles/", "/picsysthumb/", $pastefile), str_replace("userfiles", "picsysthumb", $new_loc));
								//alle files aanpassen
								DBConnect::query("UPDATE `site_files` SET `path`=REPLACE(`path`,'" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $pastefile)) . "','" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_loc)) . "') WHERE `path` LIKE '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $pastefile)) . "%'", __FILE__, __LINE__);
							}
							if($_POST["copyorcut"] == "copy")
							{
								Files::recurse_copy($pastefile, $new_loc);
								//if(is_dir(str_replace("/userfiles/", "/picsysthumb/", $pastefile)))
								//	Files::recurse_copy(str_replace("/userfiles/", "/picsysthumb/", $pastefile), str_replace("/userfiles/", "/picsysthumb/", $new_loc));
								//alle files copiëren in db
								$res_file = DBConnect::query("SELECT * FROM `site_files` WHERE `path` LIKE '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $pastefile)) . "%'", __FILE__, __LINE__);
								while($row_file = mysql_fetch_array($res_file))
								{
									$filepath = str_replace(addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $pastefile)), addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_loc)), $row_file["path"]);
									DBConnect::query("INSERT INTO `site_files` (`id`, `path`, `copyright`, `description`) VALUES('', '" . $filepath . "', '" . $row_file["copyright"] . "', '" . $row_file["description"] . "')", __FILE__, __LINE__);
									Pictures::system_thumb($filepath);
								}
							}
						}
					}
					echo '</browser>';
					break;
			}
			
			if(isset($_GET["loaddir"]))
			{
				$this->set_config("current_path", urldecode($_GET["loaddir"]));
				//$this->display_files();
				$dg = $_SESSION["datagrids"]["browser"];
				$dg->datasource->fs_folder = $this->get_config("current_path") . "/";
				$dg->datasource->searchstr = "";
				$dg->publish(true);
			}
			/*if(isset($_GET["addfolder"]))
			{
				$indir = urldecode($_POST["indir"]);
				$newname = urldecode($_POST["dirname"]);
				if(!is_dir($indir))
				{
					echo "ADDFOLDER   ERR:The folder could not be created: the parent folder does not exits.";
					return;
				}
				elseif(trim($newname) == "")
				{
					echo "ADDFOLDER   ERR:Fill in a correct folder name.";
					return;
				}
				else
				{
					$new_dir_name = Files::make_unique($indir . "/" . $newname);
					mkdir($new_dir_name);
					$nodecontent = '<img src="/css/back/icon/file/mini/folder.gif"/><span href="javascript:dummy();" onclick="select_me_please(\'treeview_browser\', this); br_select_folder(\'' . str_replace("//", "/", $new_dir_name) . '\')" ondblclick="dg_browser_html_panel.loadContent(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&loaddir=' . urlencode(str_replace("//", "/", $new_dir_name)) . '\'); document.getElementById(\'br_current_location_span\').innerHTML = \'' . str_replace($_SERVER['DOCUMENT_ROOT'] . 'userfiles', 'root', str_replace("//", "/", $new_dir_name)) . '\';">' . Files::subtract_filename(str_replace("//", "/", $new_dir_name)) . '</span>';
					echo "ADDFOLDER   OK :dirname;;" . str_replace("//", "/", $new_dir_name) . '#parentdir;;' . $indir . '#content;;' . $nodecontent;
					return;
				}
			}*/
			/*if(isset($_GET["delfolder"]))
			{
				$dirname = urldecode($_POST["dirname"]);
				if(!is_dir($dirname))
				{
					echo "DELFOLDER   ERR:The folder could not be deleted: the folder does not exist.";
					return;
				}
				else
				{
					Files::delete_directory($dirname);
					echo "DELFOLDER   OK :" . str_replace("//", "/", $dirname);
					return;
				}
			}*/
			/*if(isset($_GET["renamefile"]))
			{
				$newname = urldecode($_POST["newname"]);
				$oldname = "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", urldecode($_POST["oldname"]));
				$oldname = str_replace("//", "/", $oldname);
				$chomps = explode("/", $oldname);
				$newname_real = "";
				for($i = 0 ; $i < (count($chomps)-1) ; $i++)
				{
					if(trim($chomps[$i]) != "")
						$newname_real .= "/" . $chomps[$i];
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
				rename($_SERVER['DOCUMENT_ROOT'] . $oldname, $newname_real);
				//echo "oldname = " . $oldname . " <br>Newname = " . $newname_real;
				$chomps = explode("/", $newname_real);
				echo "RENAMEFILE  OK :oldpath;;" . urldecode($_POST["oldname"]) . "#newpath;;" . $newname_real . "#newname;;" . $chomps[count($chomps)-1];
				return;
			}*/
			/*if(isset($_GET["delfile"]))
			{
				$delfiles = explode("##", urldecode($_POST["delfile"]));
				$dirs = "";
				foreach($delfiles as $delfile)
				{
					if(is_file($delfile))
					{
						unlink($delfile);
						if(is_file(str_replace("userfiles", "picsysthumb", $delfile)))
							unlink(str_replace("userfiles", "picsysthumb", $delfile));
					}
					else
					{
						Files::delete_directory(str_replace($_SERVER['DOCUMENT_ROOT'], "", $delfile));
						if($dirs == "")
							$dirs = str_replace("//", "/", $delfile);
						else
							$dirs .= "##" . str_replace("//", "/", $delfile);
					}
				}
				if($dirs != "")
					echo "DELFILE     DIR:" .$dirs;
				else
					echo "DELFILE     ";
			}*/
			if(isset($_GET["refresh_tree"]))
			{
				$this->publish_tree();
				if(trim($_GET["refresh_tree"]) != "")
				{
					//we select the folder
					echo '<script>
							var the_a = document.getElementById(\'foldertree_' . $_GET["refresh_tree"] . '\');
							if(the_a !== undefined)
								select_me_please(\'treeview_browser\', the_a);
						</script>';
				}
			}
			if(isset($_GET["littleupload"]))
			{
				if (is_uploaded_file($_FILES['Filedata']['tmp_name']))	 
				{
					$uploadDirectory = $this->get_config("current_path") . '/';
					$uploadFile = $uploadDirectory . basename($_FILES['Filedata']['name']);
					if(!isset($_SESSION["browser_overwrite"]) || $_SESSION["browser_overwrite"] == false)
						$uploadFile = Files::make_unique($uploadFile);
					
					move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadFile);
						
					//aanmaken van file row in db
					DBConnect::query("INSERT INTO `site_files` (`id`, `path`) VALUES ('', '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '', $uploadFile)) . "')", __FILE__, __LINE__);
					
					Pictures::system_thumb($uploadFile);
				}
			}
			if(isset($_GET["displaytype"]))
			{
				$this->set_config("displaytype", $_GET["displaytype"]);
				$this->display_files();
			}
			if(isset($_GET["overwrite_state"]))
			{
				if($_POST["overwrite"] == "true")
					$_SESSION["browser_overwrite"] = true;
				else
					$_SESSION["browser_overwrite"] = false;
			}
			/*if(isset($_GET["pastefiles"]))
			{
				echo "PASTEFILES  ";
				$pastefiles = explode("##", urldecode($_POST["pastefiles"]));
				$dirs = "";
				foreach($pastefiles as $pastefile)
				{
					$temp = explode("/", $pastefile);
					$new_loc = $this->get_config("current_path") . "/" . $temp[count($temp)-1];
					//echo $new_loc;
					
					if(is_file($pastefile))
					{
						if($_POST["copyorcut"] == "cut")
						{
							rename($pastefile, $new_loc);
							if(is_file(str_replace("userfiles", "picsysthumb", $pastefile)))
								rename(str_replace("userfiles", "picsysthumb", $pastefile), str_replace("userfiles", "picsysthumb", $new_loc));
						}
						if($_POST["copyorcut"] == "copy")
						{
							copy($pastefile, $new_loc);
							if(is_file(str_replace("userfiles", "picsysthumb", $pastefile)))
								copy(str_replace("userfiles", "picsysthumb", $pastefile), str_replace("userfiles", "picsysthumb", $new_loc));
						}
					}
					else
					{
						if($_POST["copyorcut"] == "cut")
						{
							rename($pastefile, $new_loc);
							if(is_dir(str_replace("userfiles", "picsysthumb", $pastefile)))
								rename(str_replace("userfiles", "picsysthumb", $pastefile), str_replace("userfiles", "picsysthumb", $new_loc));
						}
						if($_POST["copyorcut"] == "copy")
						{
							Files::recurse_copy($pastefile, $new_loc);
							if(is_dir(str_replace("userfiles", "picsysthumb", $pastefile)))
								Files::recurse_copy(str_replace("userfiles", "picsysthumb", $pastefile), str_replace("userfiles", "picsysthumb", $new_loc));
						}
					}
				}
			}*/
		}
		
		public function display_files()
		{
			$displaytype = $this->get_config("displaytype");
			if(!$displaytype)
				$displaytype = "list";
			$path = $this->get_config("current_path");
			//--------------------------------------testphase------------------------------------
			$ds = new datasource();
			$ds->type = "FILESYSTEM";
			$ds->fs_folder = $this->get_config("current_path") . "/";
			$ds->sort_field = "filename";
			$ds->sort_order = "ASC";
			if(isset($_GET["br_extentions"]))
				$ds->fs_extentions = explode("_", $_GET["br_extentions"]);
			//var_dump($ds->get_data_count());
			//var_dump($ds->get_data());
			$dg = new datagridnew();
			$dg->show_title_bar = false;
			$dg->paging = false;
			$dg->id = "browser";
			$dg->datasource = $ds;
			$dg->id_field = "path";
			$dg->picture_view = true;
			$dg->picture_html = '<div style="width: 80px; float:left; height:84px; padding:2px; margin:2px; border: 1px solid #CCCCCC; text-align:center; overflow:hidden;">
									[icon64]<br><span style="width:72px; font-size: 9px; color: #666666; padding-top:2px;">[filename]</span>
								</div>';
			$dg->picture_placeholder_style = "width: 100px; float:left; height:100px; padding:4px; margin:2px; border: 1px solid #CCCCCC; text-align:center";
			
			$dg->rowdblclick = 'br_dblclick(dg_browser.selected_id);';
			//if($this->get_config("in_popup"))
			//	$dg->rowdblclick .= 'var chomps = dg_browser.selected_id.split(\'.\'); if(chomps.length>1 && chomps[chomps.length - 1].length <= 4){window.browserinput.value=encodeURI(dg_browser.selected_id.replace(\'//\', \'/\').replace(\'' . $_SERVER['DOCUMENT_ROOT'] . '\', \'\')); if(window.browserinput.onfilefieldchange != null && window.browserinput.onfilefieldchange != undefined) window.browserinput.onfilefieldchange(); window.close();}';
			
			$dg->addicon("New Folder", "/css/back/icon/twotone/addfolder.gif", "/css/back/icon/twotone/gray/addfolder.gif", "br_show_newfolder_form(false);", false, false, false);
			$dg->addicon("Delete Selected Files", "/css/back/icon/twotone/trash.gif", "/css/back/icon/twotone/gray/trash.gif", "br_delete_file();", true, true, true);
			$dg->addicon("Rename Selected File", "/css/back/icon/twotone/rename.gif", "/css/back/icon/twotone/gray/rename.gif", "br_rename_file(false);", true, true, true);
			$dg->addicon("File options", "/css/back/icon/twotone/edit.gif", "/css/back/icon/twotone/gray/edit.gif", "br_fileoptions(false);", true, true, true);
			$dg->add_icon_splitter();
			$dg->addicon("Cut", "/css/back/icon/twotone/cut.gif", "/css/back/icon/twotone/gray/cut.gif", "br_cut_files();", true, true, true);
			$dg->addicon("Copy", "/css/back/icon/twotone/files.gif", "/css/back/icon/twotone/gray/files.gif", "br_copy_files();", true, true, true);
			$dg->addicon("Paste", "/css/back/icon/twotone/clipboard.gif", "/css/back/icon/twotone/gray/clipboard.gif", "br_paste_files();", false, false, false);
			$dg->add_icon_splitter();
			$dg->addicon("Download Selected File", "/css/back/icon/twotone/download.gif", "/css/back/icon/twotone/gray/download.gif", "br_download();", true, true, true);
			$dg->add_icon_splitter();
			$dg->addicon("listview", "/css/back/icon/twotone/list.gif", "/css/back/icon/twotone/gray/list.gif", "br_change_view('list')", false, false, false);
			$dg->addicon("thumbsview", "/css/back/icon/twotone/thumbs.gif", "/css/back/icon/twotone/gray/thumbs.gif", "br_change_view('picture')", false, false, false);
			
			$dg->addfield("path", "", false, false, false, 0, false);
			$dg->addfield("iconmini", "", false, true, false, 20, true);
			$dg->addfield("filename", "Filename", true, true, true, 450, false);
			$dg->addfield("filesizestr", "Size", false, true, true, 100, false);
			$dg->addfield("icon64", "", false, false, false, 0, false);
			//$dg->addfield("protection", "Protection", true, true, false, 50, false);
			echo '<div class="contentheader">
				<div class="divleft"><h1>Files in <span id="br_current_location_span">' . str_replace("//", "/", str_replace('userfiles', 'root', str_replace($_SERVER['DOCUMENT_ROOT'], "", $path))) . '</span></h1></div>';
			if($this->get_config("addbutton"))
				echo '<div class="divright"><div class="savebutton" onclick="br_addbutton()" id="br_addselection">Add selection</div></div>';
			echo '</div>
				<div class="contentcontent">';
				$dg->publish(false);
			echo '</div>';
			//----------------------------------------end test--------------------------------------
			
			//we echoën de little file upload
			
			/*
			echo '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="720" height="30">
					  <param name="movie" value="/flash/fileUpload.swf?uploadscript=' . urlencode($_SERVER['HTTP_HOST'] . '/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&littleupload=' . $path) . '">
					  <param name="wmode" value="transparent">
					  <param name=quality value=high>
					  <embed wmode="transparent" src="/flash/fileUpload.swf?uploadscript=' . urlencode($_SERVER['HTTP_HOST'] . '/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&littleupload=' . $path) . '" width="720" height="30" quality=high pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" uploadscript="http://localhost/upload.php"></embed>
					</object>';
			*/
		}
	}
?>