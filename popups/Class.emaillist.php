<?php
	class emaillist extends popup
	{
		function __construct($id, $width, $height)
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "emaillist");
			$this->set_config("perpage", 50);
			//$this->set_config("filter_email", array());
		}
		
		//voor een post als er een databasetabel moet geselecteerd worden
		public function add_filter_email($filter_email)
		{
			$emails = $this->get_config("filter_email");
			if(!is_array($emails))
				$emails = array();
			$emails[] = $filter_email;
			$this->set_config("filter_email", $emails);
		}
		
		public function clear_filter_email()
		{
			$this->set_config("filter_email", array());
		}
		
		//de tabel die wordt geladen
		public function set_search($search)
		{
			$this->set_config("search", $search);
		}
		
		public function set_page($page)
		{
			$this->set_config("page", $page);
		}
		
		public function set_entity($entity)
		{
			$this->set_config("entity", $entity);
			$this->set_config("contact", 0);
		}
		
		public function set_contact($contact)
		{
			$this->set_config("contact", $contact);
			$this->set_config("entity", 0);
		}
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			echo '<div id="maillist_' . $this->id . '">';
			$this->render(0);
			echo '</div>';
		}
		
		public function handle_ajax()
		{
			if(!login::check_login())
				return "";
				
			switch($_GET["action"])
			{
				case "getmail":
					$res = DBConnect::query("SELECT * FROM man_email WHERE `id`='" . addslashes($_GET["mailid"]) . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					//opslaan van gelezen
					$redby = unserialize(stripslashes($row["redby"]));	
					$redby[$_SESSION["login_username"]] = 1;
					DBConnect::query("UPDATE man_email SET redby='" . addslashes(serialize($redby)) . "' WHERE `id`='" . addslashes($_GET["mailid"]) . "'", __FILE__, __LINE__);
					$mailcontent = unserialize(base64_decode($row["mailpart"]));
					$attachements = email::man_get_attachments($mailcontent);
					//display controlepaneel
					echo '<div class="mailcontent_panel">
							<div style="float:left;">
								<div><b>From:</b> ' . stripslashes($row["from"]) . '</div>
								<div><b>To:</b> ' . implode(',&nbsp;', unserialize(stripslashes($row["to"]))) . '</div>
								<div><b>Subject:</b> ' . htmlentities(stripslashes($row["subject"])) . '</div>
								<div>
									<span class="man_button" type="reply" mailid="' . $row["id"] . '" onclick="maillist_reply(\'' . $row["id"] . '\')">Reply</span>&nbsp;
									<span class="man_button" type="replyall" mailid="' . $row["id"] . '" onclick="maillist_replyall(\'' . $row["id"] . '\')">Reply all</span>&nbsp;
									<span class="man_button" type="forward" mailid="' . $row["id"] . '" onclick="maillist_forward(\'' . $row["id"] . '\')">Forward</span></div>
							</div>';
					if($attachements)	
					{
						echo '<div style="float:right;">';
						foreach($attachements as $at)
						{
							$filename = explode('/', $at["Filename"]);
							$filename = $filename[count($filename)-1];
							echo '<div style="text-align:right;"><a href="' . $at["Filename"] . '">' . $filename . '</a></div>';	
						}
						echo '</div>';
					}
					echo '<div style="clear:both; height:0px;"></div></div>';
						
					//tonen van de email zelf
					$contentfound = false;
					echo '<div style="overflow:scroll; height:300px;">';
					$html = email::man_get_html($mailcontent);
					if($html)
						echo $html[0]["Text"];
					else
					{
						$plain = email::man_get_plain($mailcontent);
						echo nl2br(htmlentities($plain[0]["Text"]));
					}
					echo '</div>';
					$_REQUEST["output_html"] = true;
					break;
				case "delete":
					//delete mails with ids
					$ids = explode(";", $_GET["ids"]);
					$res = DBConnect::query("SELECT * FROM man_email WHERE `id` IN('" . implode("', '", $ids) . "')", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						//ophalen attachments en deleten
						$mailcontent = unserialize(base64_decode($row["mailpart"]));
						$attachements = email::man_get_attachments($mailcontent);
						if(is_array($attachements))
						{
							foreach($attachements as $attachement)
							{
								unlink($attachement["Filename"]);
							}
						}
					}
					DBConnect::query("DELETE FROM man_email WHERE `id` IN('" . implode("', '", $ids) . "')", __FILE__, __LINE__);
					echo $this->id;
					break;
				case "unread":
					//delete mails with ids
					$ids = explode(";", $_GET["ids"]);
					$res = DBConnect::query("SELECT * FROM man_email WHERE `id` IN('" . implode("', '", $ids) . "')", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						//ophalen attachments en deleten
						$redby = unserialize(stripslashes($row["redby"]));
						$redby[$_SESSION["login_username"]] = 0;
						DBConnect::query("UPDATE man_email SET redby='" . addslashes(serialize($redby)) . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
					}
					echo $this->id;
					break;
				case "page":
					$this->set_page($_GET["pagenum"]);
					$this->render($_GET["pagenum"]);
					break;
				case "refresh":
					email::fetchallemail();
					$this->render($this->get_config("page"));
					break;
				case "call":
					echo 'call';
					break;
				case "callsave":
					
					break;
			}
		}
		
		private function render($page = 0)
		{
			//--------TOOLBOX--------------------------------
			echo '<div class="mail_toolbox">';
			echo '<div class="man_button" onclick="maillist_compose(\'' . $this->id . '\', \'' . (($this->get_config("entity")>0)?'e_' . $this->get_config("entity"):(($this->get_config("contact")>0)?'c_' . $this->get_config("contact"):'')) . '\')">Compose mail</div>
				<div class="man_button" onclick="maillist_addcall(\'' . $this->id . '\', \'' . (($this->get_config("entity")>0)?'e_' . $this->get_config("entity"):(($this->get_config("contact")>0)?'c_' . $this->get_config("contact"):'')) . '\');">Add Call</div>';
			
			echo '<div class="man_button" onclick="maillist_delete(\'' . $this->id . '\')">Delete</div>
				<div class="man_button" onclick="maillist_unread(\'' . $this->id . '\')">Mark as unread</div>
				<div class="man_button" onclick="$(\'#maillist_' . $this->id . '\').load(\'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&action=refresh\');">refresh list</div>';
			$res = DBConnect::query("SELECT * FROM man_email", __FILE__, __LINE__);
			$count = mysql_num_rows($res);
			//$pages = ceil(($count/(int)($this->get_config("perpage"))));
			$pages = 0;
			if($count > 0)
				$pages = ceil(($count/(int)($this->get_config("perpage"))));
			echo '<div style="float:right;">';
			for($i = 0; $i < $pages; $i++)
			{
				echo '<span ' . (($i!=$page)?'style="cursor:pointer;" onclick="$(\'#maillist_' . $this->id . '\').load(\'/ajax.php?sessid' . session_id() . '&popup_id=' . $this->id . '&action=page&pagenum=' . $i . '\');"':'style="font-weight:bold;"') . '>' . ($i+1) . '</span>&nbsp;';
			}
			echo '</div>';
			
			echo '<div style="clear:both; height:0px;"></div></div>';
			
			//---------END TOOLBOX---------------------------
			
			$sql = "SELECT * FROM man_email";
			$where = false;
			$filter_e = $this->get_config("filter_email");
			if(count($filter_e)>0)
			{
				$sql.= " WHERE (";
				$counter = 0;
				foreach($filter_e as $em)
				{
					if($counter > 0)
						$sql .= " OR ";
					$sql .= "`from`='" . addslashes($em) . "' OR `to` LIKE '%\"" . addslashes($em) . "\"%' OR `cc` LIKE '%\"" . addslashes($em) . "\"%' OR `bcc` LIKE '%\"" . addslashes($em) . "\"%'";
					$counter++;
				}
				$sql.= ")";
			}
			$sql .= " ORDER BY date DESC LIMIT " . ($page * $this->get_config("perpage")) . ", " . $this->get_config("perpage");
			
			$res = DBConnect::query($sql, __FILE__, __LINE__);
			while($mail = mysql_fetch_array($res))
			{
				//we checken of we het al gelezen hebben
				$red = false;
				if(trim($mail["redby"]) != "")
				{
					$redby = unserialize(stripslashes($mail["redby"]));	
					if($redby[$_SESSION["login_username"]] == 1)
						$red = true;
				}
				//checken og het een tel is
				if($mail["tel"] == 0)
				{
					echo '<div id="mail_' . $mail["id"] . '" mailid="' . $mail["id"] . '" class="man_emaillist_item">
							<div class="mailheader' . ((!$red)?' unred':'') . '" >
								<div style="float: left; width:50px; vertical-align: middle;" noexpand="true">';
					echo '<input type="checkbox" style="height: 9px; margin:0px; padding: 0px;float: left;" id="maillist_' . $this->id . '_' . $mail["id"] . '" mailid="' . $mail["id"] . '"/>';
					if($mail["inout"] == "out")
						echo '<span style="color:#4D6F8C; font-weight:bold; font-size: 11px; line-height:9px; padding-left:2px;">OUT:</span>';	
					else
						echo '<span style="color:#4D6F8C; font-weight:bold; font-size: 11px; line-height:9px; padding-left:2px;">IN:</span>';	
	
					echo '</div>
							<div style="float: left; cursor:pointer; font-size:11px">' . date('d/m/Y H:i', $mail["date"]) . ' - ' . htmlentities(stripslashes($mail["subject"])) . '</div>
							<div style="clear:both; cursor:pointer; font-size: 9px; margin-left:50px; line-height:16px; font-weight: normal;"><b>From: </b>';
					$from = stripslashes($mail["from"]);
					$res_contact = DBCONNECT::query("SELECT * FROM man_contact WHERE email='" . $from . "' OR email2='" . $from . "'", __FILE__, __LINE__);
					if($row_contact = mysql_fetch_array($res_contact))
					{
						echo '<div style="display:inline; float:none; font-size: 9px;" class="man_button" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=load&type=' . (($row_contact["entity_id"]!=0)?'entity':'contact') . '&id=' . (($row_contact["entity_id"]!=0)?$row_contact["entity_id"]:$row_contact["id"]) . '\');">' . $from . '</div>';
					}
					else
						echo stripslashes($mail["from"]);
					
					echo ' <b>To</b> ';
					
					$arrto = unserialize(stripslashes($mail["to"]));
					foreach($arrto as $oneto)
					{
						$res_contact = DBCONNECT::query("SELECT * FROM man_contact WHERE email='" . $oneto . "' OR email2='" . $oneto . "'", __FILE__, __LINE__);
						if($row_contact = mysql_fetch_array($res_contact))
						{
							echo '<div style="display:inline; float:none; font-size: 9px;" class="man_button" onclick="cms2_show_loader(\'content\'); $(\'#content\').load(\'/ajax.php?sessid=' . session_id() . '&page=management&action=load&type=' . (($row_contact["entity_id"]!=0)?'entity':'contact') . '&id=' . (($row_contact["entity_id"]!=0)?$row_contact["entity_id"]:$row_contact["id"]) . '\');">' . $oneto . '</div> ';
						}
						else
							echo $oneto . ' ';	
					}
					
					echo '</div><div style="clear:both; height:0px"></div></div>
							<div class="mailcontent" closed="true" style="display:none;">';
					echo 	'</div>
						</div>';
				}
				else
				{
					//telefoons tonen
					echo '<div id="mail_' . $mail["id"] . '" mailid="' . $mail["id"] . '" class="man_emaillist_item">
							<div class="mailheader' . ((!$red)?' unred':'') . '" >
								<div style="float: left; width:50px; vertical-align: middle;" noexpand="true">';
					echo '<input type="checkbox" style="height: 9px; margin:0px; padding: 0px;float: left;" id="maillist_' . $this->id . '_' . $mail["id"] . '" mailid="' . $mail["id"] . '"/>';
					echo '<img src="/css/back/img/icon/twotone/phone.gif">';	
	
					echo '</div>
							<div style="float: left; cursor:pointer; font-size:11px">' . date('d/m/Y H:i', $mail["date"]) . ' - ' . htmlentities(stripslashes($mail["tel_description"])) . '</div>';
							
					echo '</div>';	
				}
			}
			?>
            	<script language="javascript">
					$(".mailheader").children("div").click(function(){
						if($(this).attr("noexpand") != "true")
						{
							var contentmail = $(this).parent().parent().find(".mailcontent");								
							if(contentmail.attr("closed") == "true")
							{
								$(this).parent().removeClass("unred");
								contentmail.animate({'height':400}, 500);
								contentmail.css("display", "block");
								contentmail.attr("closed", "false");
								//laden van email
								contentmail.html('<div style="height: 400px; text-align: center; line-height: 400px; font-size:40px; color:#999999;">LOADING EMAIL</div>');
								contentmail.load('/ajax.php?sessid=<?php echo session_id();?>&popup_id=<?php echo $this->id; ?>&action=getmail&mailid=' + contentmail.parent().attr("mailid"), function(){
									$('.mailcontent_panel').each(function(){ 
										$(this).next().height(390 - $(this).height());
									});
									//de reply buttons
									
								});
							}
							else
							{
								contentmail.animate({'height':0}, 500, function(){$(this).css("display", "none");});
								contentmail.attr("closed", "true");	
								contentmail.children().remove();
							}
						}
					});
				</script>
            <?php
			$_REQUEST["output_html"] = true;
		}
	}
?>