<div>
<?php
	$res = DBConnect::query("SELECT site_homecfg.*, site_homecfg_lang.* FROM site_homecfg, site_homecfg_lang WHERE site_homecfg.id=site_homecfg_lang.lang_parent_id AND site_homecfg_lang.lang='" . $_SESSION["LANGUAGE"] . "'", __FILE__, __LINE__);
	$row = mysql_fetch_array($res);
	
	echo '<div class="under_construction">
			<img src="css/front/img/logo.png"/>
			<div class="small">coming soon</div>
			<div class="small"><br>in the mean time go to our <a href="https://www.facebook.com/littledotsmusic?ref=ts&fref=ts">facebook</a></div>
		</div>';
?>
</div>