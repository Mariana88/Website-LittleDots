<?php
	$res_contact = DBConnect::query("SELECT page_contact.*, page_contact_lang.* FROM page_contact, page_contact_lang WHERE page_contact.id=page_contact_lang.lang_parent_id AND page_contact_lang.lang='" . $_SESSION["LANGUAGE"] . "' AND `page_id`='" . $row_page["id"] . "'", __FILE__, __LINE__);
	$row_contact = fetch_db($res_contact);

	echo $row_contact["text"];
	
?>
<!-- Begin MailChimp Signup Form -->
<style type="text/css">
#mc_embed_signup{width:400px;}
</style>
<div id="mc_embed_signup">
<form action="//littledots.us7.list-manage.com/subscribe/post?u=9a71b4047d3412ed36dda473a&amp;id=472ba2f5a5" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
<br /><br /><h2>Subscribe to our mailing list</h2>
<div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
<div class="mc-field-group">
<label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
</label>
<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
</div>
<div class="mc-field-group">
<label for="mce-FNAME">First Name </label>
<input type="text" value="" name="FNAME" class="" id="mce-FNAME">
</div>
<div class="mc-field-group">
<label for="mce-LNAME">Last Name </label>
<input type="text" value="" name="LNAME" class="" id="mce-LNAME">
</div>
<div id="mce-responses" class="clear">
<div class="response" id="mce-error-response" style="display:none"></div>
<div class="response" id="mce-success-response" style="display:none"></div>
</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    <div style="position: absolute; left: -5000px;"><input type="text" name="b_9a71b4047d3412ed36dda473a_472ba2f5a5" tabindex="-1" value=""></div>
    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
</form>
</div>
<!--<script type='text/javascript' src='/plugins/mailchimp/mailchimp.js'></script>
<script type='text/javascript'>
	(function($) {
		window.fnames = new Array(); 
		window.ftypes = new Array();
		fnames[0]='EMAIL';
		ftypes[0]='email';
		fnames[1]='FNAME';
		ftypes[1]='text';
		fnames[2]='LNAME';
		ftypes[2]='text';
	}(jQuery));
	//var $mcj = jQuery.noConflict(true);
	//var $mcj = jQuery;
</script>-->