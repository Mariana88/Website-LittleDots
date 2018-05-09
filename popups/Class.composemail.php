<?php
	class composemail extends popup
	{
		function __construct($id, $width, $height)
		{
			parent::__construct($id, $width, $height);
			$this->set_config("classname", "composemail");
			$this->set_config("content", "");
		}
		
		//voor een post als er een databasetabel moet geselecteerd worden
		public function set_to($to)
		{
			$this->set_config("mailto", $to);
		}
		
		public function set_from($from)
		{
			$this->set_config("from", $from);
		}
		
		public function set_cc($cc)
		{
			$this->set_config("cc", $cc);
		}
		
		public function set_bcc($bcc)
		{
			$this->set_config("bcc", $bcc);
		}
		
		//de tabel die wordt geladen
		public function set_subject($subject)
		{
			$this->set_config("subject", $subject);
		}
		
		public function set_content($content)
		{
			$this->set_config("content", $content);
		}
		
		public function set_signature($signature)
		{
			$this->set_config("signature", $signature);
		}
		
		public function set_errors($errors)
		{
			$this->set_config("errors", $errors);
		}
		
		public function set_attachments($attachments)
		{
			$this->set_config("attachments", $attachments);
		}
		
		public function set_optional_attachments($optional_attachments)
		{
			$this->set_config("optional_attachments", $optional_attachments);
		}
		
		public function add_optional_attachments($optional_attachments)
		{
			$opt = $this->get_config("optional_attachments");
			if(!is_array($opt))
				$opt = array();
			foreach($optional_attachments as $optional_attachment)
			{
				if(!in_array($optional_attachment, $opt))
					$opt[] = $optional_attachment;
			}
			$this->set_config("optional_attachments", $opt);
		}
		
		//publish the component
		public function publish($in_popup, $from_ajax = false)
		{
			$errors = $this->get_config('errors');
			echo '<form id="manmail" method="POST">
					<label' . ((isset($errors['from']))?'style="color: red;"':'') . '>From:</label>
					<select id="from" name="from">';
			//ophalen alle users
			$res = DBConnect::query("SELECT man_emailconfig.*, site_user.id as 'userid', site_user.lname as 'username' FROM man_emailconfig, site_user WHERE man_emailconfig.user=site_user.id", __FILE__, __LINE__);
			$tmp_selectfound = false;
			while($row = mysql_fetch_array($res))
			{
				if(!$tmp_selectfound)
				{
					echo '<option value="' . $row["email"] . '" ' . ((trim($this->get_config("from")) != "")?(($row["email"] == $this->get_config("from"))?' selected="selected"':''):(($_SESSION["login_username"]==$row["username"])?' selected="selected"':'')) . '>' . $row["username"] . '</option>';
					if($_SESSION["login_username"]==$row["username"])
					$tmp_selectfound = true;
				}
				else
					echo '<option value="' . $row["email"] . '">' . $row["username"] . '</option>';
			}
			echo '</select>';
			//onChange="composemail_fill_replycheck()"
			echo '<label ' . ((isset($errors['to']))?'style="color: red;"':'') . '>To:</label>
				<textarea id="to" name="to">' . ((is_array($this->get_config("mailto")))?implode(', ', $this->get_config("mailto")):$this->get_config("mailto")) . '</textarea>';
			echo '<label ' . ((isset($errors['cc']))?'style="color: red;"':'') . '>CC:</label>
				<input type="text" id="cc" name="cc" value="' . ((is_array($this->get_config("cc")))?implode(', ', $this->get_config("cc")):$this->get_config("cc")) . '"/>';
			echo '<label ' . ((isset($errors['bcc']))?'style="color: red;"':'') . '>BCC:</label>
				<input type="text" id="bcc" name="bcc" value="' . ((is_array($this->get_config("bcc")))?implode(', ', $this->get_config("bcc")):$this->get_config("bcc")) . '"/>';
			echo '<label ' . ((isset($errors['subject']))?'style="color: red;"':'') . '>Subject:</label>
				<input type="text" id="subject" name="subject" value="' . $this->get_config("subject") . '"/>';
			echo '<div style="clear:both; height: 0px;"></div>
					<label>Signature:</label>
				<input type="checkbox" id="signature" name="signature" value="1" ' . (($this->get_config("signature") == true)?'checked="checked"':"") . '/>';
			//attachments
			echo '<label>Attachments:</label>';
			echo '<div style="float:left; width: 450px;">
					<input type="hidden" id="attachments" name="attachments" value="' . ((is_array($this->get_config("attachments")))?implode('|', $this->get_config("attachments")):'') . '">
					<div id="attachments_select">';
			//tonen van de optional attachments
			$oas = $this->get_config("optional_attachments");
			$att = $this->get_config("attachments");
			if(is_array($oas))
			{
				$counter = 0;
				foreach($oas as $oa)
				{
					$counter++;
					echo '<div><input id="att_' . $counter . '" name="att_' . $counter . '" type="checkbox" value="' . $oa . '" ' . ((is_array($att) && in_array($oa, $att))?'checked="checked"':'') . '>&nbsp;' . $oa . '</div>';	
				}
			}
			echo '</div>';
			echo '<input type="hidden" onchange="composemail_addattachment()" name="inputaddattachment" id="inputaddattachment" value=""/>';
			echo '<div class="man_button" onclick="somewindow = window.open(\'/browser.php?addbutton=yes\',\'\',\'width=1024,height=516,scrollbars=no,toolbar=no,location=no,resizable=no,status=no\'); somewindow.browserinput=document.getElementById(\'inputaddattachment\'); document.getElementById(\'inputaddattachment\').onfilefieldchange = composemail_addattachment;">add attachment</div>';
			
			echo '</div>';
			echo '<div style="clear:both; height:0px;"></div>';
			//reply check
			echo '<label>Reply check</label>
				<select name="replycheck_days" id="replycheck_days" style="width: 100px;">
					<option value="0">No Check</option>
					<option value="1">1 Day</option>
					<option value="2">2 Days</option>
					<option value="3">3 Days</option>
					<option value="4">4 Days</option>
					<option value="5">5 Days</option>
					<option value="6">6 Days</option>
					<option value="7">1 week</option>
					<option value="14">2 weeks</option>
					<option value="21">3 weeks</option>
					<option value="30">1 month</option>
					<option value="45">1,5 month</option>
					<option value="60">2 months</option>
					<option value="90">3 months</option>
					<option value="120">4 months</option>
				</select>';
			/*
			echo '<select name="replycheck_email" id="replycheck_email" style="width: 300px;">';
			if(is_array($this->get_config("mailto")))
			{
				foreach($this->get_config("mailto") as $onemailto)
				{
					echo '<option vlaue="' . $onemailto . '">' . $onemailto . '</option>';
				}
			}
			echo '</select>';
			*/
			echo '<textarea id="mailhtml" name="mailhtml" style="witdh:370px; height:370px;">' . $this->get_config("content") . '</textarea>';
					echo '<script type="text/javascript">
							$(document).ready(function(){
								tinyMCE.settings = mce_config_array[2];
								tinyMCE.execCommand(\'mceAddControl\', false, \'mailhtml\');
							});
						</script>';
			echo '<input type="submit" value="Send Mail" name="sendmail"/>';
			
			echo '</form>';
			?>
            <script language="javascript">
            	$('#manmail').submit(function(){ //listen for submit event
                	tinyMCE.triggerSave();
              		return true;
				});
			</script>
            
<?php
			//Templates
			echo '<div class="splitter"><span>Email templates</span></div>';
			//load
			echo '<label>Load template:</label>';
			$res_templ = DBConnect::query("SELECT * FROM man_email_template", __FILE__, __LINE__);
			echo '<select id="loadtemplateselect" style="width:300px; float:left;">';
			while($row_templ = fetch_db($res_templ))
			{
				echo '<option value="' . $row_templ["id"] . '">' . $row_templ["name"] . '</option>';	
			}
			echo '</select><div class="man_button" id="loadtemplatebutton" onclick="send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&page=management&action=get_template&templid=\' + $(\'#loadtemplateselect\').val(), \'\', composemail_loadtemplate);">Load template</div>';
			echo '<div style="clear:both; height:0px;"></div>';
			//save
			echo '<label>Save current as template:</label>';
			echo '<input type="text" id="savetemplatename" style="width:296px;  float:left;" value=""/>';
			echo '<div class="man_button" id="loadtemplatebutton" onclick="composemail_savetemplate();">Save template</div>';
			echo '<div style="clear:both; height:0px;"></div>';
		}
		
		public function handle_ajax()
		{
			if(!login::check_login())
				return "";
				
			switch($_GET["action"])
			{
				
			}
		}
	}
?>