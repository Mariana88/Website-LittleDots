<?php
	//this class helps with email functions
	class email
	{
		static function checkemail($email) 
		{
 			if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
    			/*list($username,$domain)=split('@',$email);
    			if(!checkdnsrr($domain,'MX')) 
				{
      				return false;
    			}*/
   				return true;
  			}
  			return false;
		}
		
		static function send_one_mail($to, $from, $replyto, $subject, $content, $unsubscribefooter, $tracing_pic, $newsletter_id)
		{
			$result = DBConnect::query("SELECT * FROM site_user_front WHERE email='" . addslashes($to) . "'", __FILE__, __LINE__);
			$row_user = mysql_fetch_array($result);
			
			//we check if we have to add the unsubscribe footer
			if($unsubscribefooter)
			{
				//ob_start();
				//include ('aidclasses/email_files/unsubscribe_footer.php');
				//$content .= ob_get_contents();
				$content = str_replace("[uid]", $row_user["id"], $content);
				//ob_end_clean();
			}
			
			//we check if we have to add the unsubscribe footer
			if($tracing_pic)
			{
				//we get the user id
				$content .= '<img src="http://' . $_SERVER['HTTP_HOST'] . '/new/aidclasses/email_files/tracing_pic.php?uid=' . $row_user["id"] . '&mailid=' . $newsletter_id . '"/>';
			}
			
			//maken van de header
			$headers = "";
			$headers .= "From: " . $from . "\n";
			$headers .= "Reply-To: " . $replyto . "\n";
			$headers .= "Errors-To: " . "ezineerror@metra.be" . "\n";
			$headers .= "Return-Path: " . $replyto . "\n";
			$headers .= "MIME-Version: 1.0\n"; 

			//unique boundary 
			$boundary = uniqid(time()); 

			//tell e-mail client this e-mail contains//alternate versions 
			$headers .= "Content-Type: multipart/alternative" . 
			   "; boundary = $boundary\n\n";

			//message to people with clients who don't 
			//understand MIME 
			$headers .= "This is a MIME encoded message.\n\n"; 

			//plain text version of message 
			$headers .= "--$boundary\n" . 
			   "Content-Type: text/plain; charset=ISO-8859-1\n" . 
			   "Content-Transfer-Encoding: base64\n\n"; 
			$headers .= chunk_split(base64_encode($content)); 

			//HTML version of message 
			$headers .= "--$boundary\n" . 
			   "Content-Type: text/html; charset=ISO-8859-1\n" . 
			   "Content-Transfer-Encoding: base64\n\n"; 
			$headers .= chunk_split(base64_encode($content));
			
			ini_set("SMTP","io.pong.be");
			ini_set("sendmail_from","info@deberengieren.be");
			//now we send the mail
			mail($to, $subject, "", $headers);
			//we save the email for tracing
			if($tracing_pic)
			{
				DBConnect::query("INSERT INTO site_newsletter_tracking(`uid`, `newsletter_id`) VALUES ('" . $row_user["id"] . "', '" . $newsletter_id . "')", __FILE__, __LINE__);
			}
		}
		
		static function send_many_mails($to, $from, $replyto, $subject, $content, $unsubscribefooter, $tracing_pic, $newsletter_id)
		{
			$adresses = explode(",", $to);
			$content = email::html_to_absolute_paths($content);
			foreach($adresses as $adress)
			{
				$adress = trim($adress);
				if($adress != "")
					email::send_one_mail($adress, $from, $replyto, $subject, $content, $unsubscribefooter, $tracing_pic, $newsletter_id);
			}
		}
		
		static function send_waiting_mails()
		{
			$res = DBConnect::query("SELECT * FROM sys_sending_mails LIMIT 0, 10", __FILE__, __LINE__);
			$ids_to_del = array();
			while($row = mysql_fetch_array($res))
			{
				$ids_to_del[] = $row["id"];
				email::send_one_mail(stripslashes($row["to"]), stripslashes($row["from"]), stripslashes($row["replyto"]), stripslashes($row["subject"]), stripslashes($row["content"]), ($row["ezine"] == 1), false, NULL);
			}
			
			foreach($ids_to_del as $one_id)
			{
				DBConnect::query("DELETE FROM sys_sending_mails WHERE `id`='" . $one_id . "'", __FILE__, __LINE__);
			}
			return count($ids_to_del);
		}
		
		static function html_to_absolute_paths($content)
		{
			$return= str_replace('href="?', 'href="http://' . $_SERVER['HTTP_HOST'] . '?', $content);  
			$return= str_replace('href="/', 'href="http://' . $_SERVER['HTTP_HOST'] . '/', $content);  
			$return= str_replace('src="/', 'src="http://' . $_SERVER['HTTP_HOST'] . '/', $return);
			return $return;
		}
		
		static function fetchallemail()
		{
			$res_cfg = DBConnect::query("SELECT * FROM man_emailconfig", __FILE__, __LINE__);
			while($row_cfg = mysql_fetch_array($res_cfg))
			{
				$pop3 = new ezcMailPop3Transport($row_cfg["popserver"]);
				$pop3->authenticate( $row_cfg["email"], $row_cfg["passw"] );
				// Get the number of messages on the server and their combined size
				// in the variables $num and $size
				$pop3->status( $num, $size );
				// Get the list of message numbers on the server and their sizes
				// the returned array is something like: array( 1 => 1500, 2 => 45200 )
				// where the key is a message number and the value is the message size
				$messages = $pop3->listMessages();
				// Get the list of message unique ids on the server and their sizes
				// the returned array is something like: array( 1 => '00000f543d324', 2 => '000010543d324' )
				// where the key is an message number and the value is the message unique id
				$messages = $pop3->listUniqueIdentifiers();
				// Usually you will call one of these 3 fetch functions:
				// Fetch all messages on the server
				$set = $pop3->fetchAll();
				// Create a new mail parser object
				$parser = new ezcMailParser();
				// Parse the set of messages retrieved from the server earlier
				$mails = $parser->parseMail( $set ); 
				
				foreach($mails as $mail)
				{
					$mail = formatMail($mail);
					//replace attachments
					$attachements = email::man_get_attachments($mail["mailpart"]);
					if($attachements)	
					{
						foreach($attachements as $at)
						{
							$filename = explode('/', $at["Filename"]);
							$filename = $filename[count($filename)-1];
							$filename = $_SERVER['DOCUMENT_ROOT'] . 'userfiles/protected/mail/attachment/in/' . $filename;
							$filename = Files::make_unique($filename);
							copy($at["Filename"], $filename);
							
							//vervangen in mailpart
							$mail["mailpart"] = email::recursive_array_replace($at["Filename"] ,str_replace($_SERVER['DOCUMENT_ROOT'], '/', $filename), $mail["mailpart"]);	
						}
					}
					//checken of de mail al is opgehaald om te voorkomen dat het 2 maal wordt opgeslaan
					$res_msgid = DBConnect::query("SELECT * FROM man_email WHERE messageid='" . addslashes($mail["messageid"]) . "'",__FILE__, __LINE__);
					if(mysql_num_rows($res_msgid) == 0)
					{
						//opslaan van de mail
						$plaintext = email::man_get_plain($mail["mailpart"]);
						
						$sql = "INSERT INTO man_email (`id`, `from`, `to`, `cc`, `bcc`, `date`, `messageid`, `subject`, `mailpart`, `inout`, `plaintext`) VALUES('', '" . addslashes($mail["from"]) . "', '" . addslashes(serialize($mail["to"])) . "', '" . addslashes(serialize($mail["cc"])) . "', '" . addslashes(serialize($mail["bcc"])) . "', '" . addslashes($mail["date"]) . "', '" . addslashes($mail["messageid"]) . "', '" . addslashes(utf8_decode($mail["subject"])) . "', '" . base64_encode(serialize($mail["mailpart"])) . "', 'in', '" . addslashes(utf8_decode($plaintext[0]["Text"])) . "')";
						DBConnect::query($sql, __FILE__, __LINE__);
					}
					
				}
				for($i = count($mails) ; $i>0 ; $i--)
					$pop3->delete($i);
			}	
		}
		
		static function recursive_array_replace($find, $replace, $array){
			if (!is_array($array)) {
				return str_replace($find, $replace, $array);
			}
			$newArray = array();
			foreach ($array as $key => $value) {
				$newArray[$key] = email::recursive_array_replace($find, $replace, $value);
			}
			return $newArray;
		}
		//-------------------------------GET INFO FROM SAVED EMAILs-----------------------------------*/
		
		//get the html text from a saved email
		function man_get_html($arr)
		{
			return email::man_get_type($arr, "html");
		}
		
		function man_get_plain($arr)
		{
			return email::man_get_type($arr, "plain");
		}
		
		function man_get_attachments($arr)
		{
			return email::man_get_type($arr, "attachment");
		}
		
		
		function man_get_type($arr, $type)
		{
			if(isset($arr["Type"]) || isset($arr["Disposition Type"]))
			{
				if($arr["Type"] == $type || $arr["Disposition Type"] == $type)
					return array($arr);
				else 
					return false;
			}
			else
			{
				$return = array();
				if(is_array($arr))
				{
					foreach($arr as $subarr)
					{
						if(!is_array($subarr))
							continue;
						if($ret = email::man_get_type($subarr, $type))
						{
							foreach($ret as $tosave)
							{
								$return[] = $tosave;
							}
						}
					}
				}
				if(count($return)>0)
					return $return;
				else
					return false;
			}
		}
	}
	//------------------------------------POP3--------------------------------------------
	function formatMail( $mail )
  {
      $t = array();
	  $t["from"] = formatAddress( $mail->from );
      $t["to"] = formatAddresses( $mail->to );
      $t["cc"] = formatAddresses( $mail->cc );
      $t["bcc"] = formatAddresses( $mail->bcc );
      $t["date"] = $mail->timestamp;
      $t["subject"] = $mail->subject;
      $t["messageid"] = $mail->messageId;
      $t["mailpart"] = formatMailPart( $mail->body );
      return $t;
  }
  
  function formatMailPart( $part )
  {
      if ( $part instanceof ezcMail )
          return formatMail( $part );
  
      if ( $part instanceof ezcMailText )
          return formatMailText( $part );
  
      if ( $part instanceof ezcMailFile )
          return formatMailFile( $part );
  
      if ( $part instanceof ezcMailRfc822Digest )
          return formatMailRfc822Digest( $part );
  
      if ( $part instanceof ezcMailMultiPart )
          return formatMailMultipart( $part );
  
      //die( "No clue about the ". get_class( $part ) . "\n" );
  }
  
  function formatMailMultipart( $part )
  {
      if ( $part instanceof ezcMailMultiPartAlternative )
          return formatMailMultipartAlternative( $part );
  
      if ( $part instanceof ezcMailMultiPartDigest )
          return formatMailMultipartDigest( $part );
  
      if ( $part instanceof ezcMailMultiPartRelated )
          return formatMailMultipartRelated( $part );
  
      if ( $part instanceof ezcMailMultiPartMixed )
          return formatMailMultipartMixed( $part );
  
      //die( "No clue about the ". get_class( $part ) . "\n" );
 }
  
  function formatMailMultipartMixed( $part )
  {
      $t = array();
      foreach ( $part->getParts() as $key => $alternativePart )
      {
          $t["MIXED"][$key] = formatMailPart( $alternativePart );
      }
      return $t;
  }
  
  function formatMailMultipartRelated( $part )
  {
      $t = array();
      $t["RELATED"]["MAIN"] = formatMailPart( $part->getMainPart() );
      foreach ( $part->getRelatedParts() as $key => $alternativePart )
      {
          $t["RELATED"][$key] = formatMailPart( $alternativePart );
      }
      return $t;
  }
  
  function formatMailMultipartDigest( $part )
  {
      $t = array();
      foreach ( $part->getParts() as $key => $alternativePart )
      {
          $t["DIGEST"][$key] = formatMailPart( $alternativePart );
      }
     return $t;
 }
 
 function formatMailRfc822Digest( $part )
 {
     $t = array();
     $t["DIGEST_ITEM"][$key] = formatMailpart( $part->mail );
     return $t;
 }
 
 function formatMailMultipartAlternative( $part )
 {
     $t = array();
     foreach ( $part->getParts() as $key => $alternativePart )
     {
         $t ["ALTERNATIVE ITEM"][$key] = formatMailPart( $alternativePart );
     }
     return $t;
 }
 
function formatMailText( $part )
 {
     $t = array();
     $t["Original Charset"] = $part->originalCharset;
     $t["Charset"] = $part->charset;
     $t["Encoding"] = $part->encoding;
     $t["Type"] = $part->subType;
     $t["Text"] = $part->text;
     return $t;
 }
 
 function formatMailFile( $part )
 {
     $t = array();
     $t["Disposition Type"] = $part->dispositionType;
     $t["Content Type"] = $part->contentType;
     $t["Mime Type"] = $part->mimeType;
     $t["Content ID"] = $part->contentId;
     $t["Filename"] = $part->fileName;
     return $t;
 }

function formatAddresses( $addresses )
 {
     $fa = array();
     foreach ( $addresses as $address )
     {
         $fa[] = formatAddress( $address );
     }
     return $fa;
 }
 
 function formatAddress( $address )
 {
     return $address->email;
 }
?>