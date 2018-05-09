<?php
	if(!login::right("backpage_newsletter_new", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<script language="javascript">
	function newsletter_send_getallcontacts()
	{
		send_ajax_request('GET', '/ajax.php?sessid=<?php echo session_id(); ?>&page=newsletter_new&action=getallcontacts', '', newsletter_send_addcontacts);
	}
	
	function newsletter_send_getezinecontacts()
	{
		send_ajax_request('GET', '/ajax.php?sessid=<?php echo session_id(); ?>&page=newsletter_new&action=getezinecontacts', '', newsletter_send_addcontacts);
	}
	
	function newsletter_send_getsectorcontacts()
	{
		send_ajax_request('GET', '/ajax.php?sessid=<?php echo session_id(); ?>&page=newsletter_new&action=getsectorcontacts&sector=' + document.getElementById('newsletter_send_contacts_sectors').value, '', newsletter_send_addcontacts);
	}
	
	function newsletter_send_gettaalcontacts()
	{
		send_ajax_request('GET', '/ajax.php?sessid=<?php echo session_id(); ?>&page=newsletter_new&action=gettaalcontacts&taal=' + document.getElementById('newsletter_send_contacts_taal').value, '', newsletter_send_addcontacts);
	}
	
	//adds comma seperated emails to recipient box
	function newsletter_send_addcontacts(xmlHttp)
	{
		//alert(xmlHttp.responseText);
		newsletter_send_addcontacts_str(xmlHttp.responseText);
	}
	
	function newsletter_send_addcontacts_str(thestr)
	{
		var new_emails = thestr.split(",");
		var old_emails = document.getElementById('newssend_to').value.split(',');
		var new_list = new_emails.concat(old_emails).unique();
		var new_str = new_list.join(",").replace(/^\s+/,'').replace(/\s+$/,'');
		
		if(new_str.substr(0, 1) == ",")
			new_str = new_str.substr(1);
		if(new_str.substr((new_str.length - 1), 1) == ",")
			new_str = new_str.substr(0, (new_str.length - 1));
		document.getElementById('newssend_to').value = new_str;
	}
	
	function newsletter_send_send_return(xmlHttp)
	{
		//alert(xmlHttp.responseText);
		//we kleuren elk veld terug wit
		//document.getElementById("newssend_from").style.backgroundColor = "#FFFFFF";
		//document.getElementById("newssend_replyto").style.backgroundColor = "#FFFFFF";
		document.getElementById("newssend_subject").style.backgroundColor = "#FFFFFF";
		document.getElementById("newssend_to").style.backgroundColor = "#FFFFFF";
		
		if(xmlHttp.responseText != "")
		{
			var ids = xmlHttp.responseText.split(";");
			for(var i = 0 ; i < ids.length ; i++)
			{
				if(ids[i] != "" && ids[i] != "newsletter_content_text")
				{
					var ele = document.getElementsByName(ids[i])[0];
					if(ele !== null && ele !== undefined)
						ele.style.backgroundColor = "#E77C7C";
				}
				if(ids[i] == "newsletter_content_text")
				{
					alert("You cannot send an email without any content!");
				}
			}
		}
		else
		{
			somewindow = window.open('http://<?php echo $_SERVER['HTTP_HOST']; ?>/mailssend.php','','width=700,height=250,scrollbars=no,toolbar=no,location=no,resizable=no,status=no');
			//alert("uw bericht is verzonden");
			newsletternew_content.loadContent('/ajax.php?sessid=' + session_id + '&page=newsletter_new&action=content_choice');
		}
	}
</script>
		<?php
		echo '<div id="superdiv"><div style="padding-left:8px; padding-right:8px;" name="form_siteconfig" id="form_siteconfig">';
		
		echo '<div class="contentheader">
					<h1>Newsletter: Send a new email</h1>
				</div>';
		
		//we tonen het formulier
		echo '<div id="newsletter_send" style="padding-left:8px; padding-right:8px;">';
		echo '<div class="splitter"><span>Your info</span></div>';
		/*echo '<label>From</label>
				<input name="newssend_from" id="newssend_from" type="text" value="info@metra.be" style="width: 824px;"/>';
		echo '<label>Reply To</label>
				<input name="newssend_replyto" id="newssend_replyto" type="text" value="info@metra.be" style="width: 824px;"/>';*/
		echo '<label>Email subject</label>
				<input name="newssend_subject" id="newssend_subject" type="text" value="" style="width: 824px;"/>';
		
		echo '<div class="splitter"><span>Recipients</span></div>';
		//we zorgen dat de gebruiker gemakkelijk kan selecteren
		echo '<input type="button" value="Add ALL contacts" onClick="newsletter_send_getallcontacts();" class="nowidth"/> || ';
		echo '<input type="button" value="Add EZINE contacts" onClick="newsletter_send_getezinecontacts();" class="nowidth"/> || ';
		$res = DBConnect::query("SELECT * FROM `data_sectoren`", __FILE__, __LINE__);
		echo '<select name="newsletter_send_contacts_sectors" id="newsletter_send_contacts_sectors" class="smallinput">';
		while($row = mysql_fetch_array($res))
		{
			echo '<option value="' . $row["tekst_nl"] . '">' . $row["tekst_nl"] . '</option>';
		}
		echo '</select>';
		echo '<input type="button" value="Add SECTOR contacts" onClick="newsletter_send_getsectorcontacts();" class="nowidth"/> || ';
		echo '<input type="button" value="Select contacts" onClick="tb_show(\'Select contacts\', \'#TB_inline?height=700&width=1000&inlineId=tb_contactselect&modal=false\', false); form_contactselect.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=selmech\');" class="nowidth"/> || ';
		echo '<select name="newsletter_send_contacts_taal" id="newsletter_send_contacts_taal" class="smallinput">';
		foreach(mainconfig::$languages as $code => $langname)
		{
			echo '<option value="' . $code . '">' . $langname . '</option>';
		}
		echo '</select>';
		echo '<input type="button" value="Add language contacts" onClick="newsletter_send_gettaalcontacts();" class="nowidth"/> || ';
		echo '<input type="button" value="Clear" onClick="document.getElementById(\'newssend_to\').value=\'\';" class="nowidth"/>';
		echo '<textarea name="newssend_to" id="newssend_to" id="newssend_to" type="text" style="width: 974px; height: 100px"/></textarea>';
		
		//selectie mechanisme formulier
		echo '<div id="tb_contactselect" style="display:none">
				<div id="form_contactselect" autopost="no">Loading...</div>
			</div>
			<script>
				var form_contactselect = new Spry.Widget.HTMLPanel("form_contactselect",{evalScripts:true});
			</script>';
		
		
		//EMAIL CONTENT
		echo '<div class="splitter"><span>Email content</span></div>';
		echo '<div id="newsletternew_content"></div>
			<script>
				var newsletternew_content = new Spry.Widget.HTMLPanel("newsletternew_content",{evalScripts:true});
				newsletternew_content.loadContent(\'/ajax.php?sessid=' . session_id() . '&page=newsletter_new&action=content_choice\');
			</script>';
		echo '</div></div></div>';
	}
?>