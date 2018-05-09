<?php
	if(!login::right("backpage_security_rightrules", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<div id="superdiv">
<div class="contentheader">
	<div class="divleft">Right rules: specify whitch rules can be set for certain domains</div>
</div>
<div class="contentcontent" style="padding-left:20px; padding-right:20px;" name="form_siteconfig" id="form_siteconfig">
<?php
	$de = new dataeditor("rightrule_de", 500, 500, "sys_rightrules");
	$de->publish(false);
	echo '<div style="clear:both;"></div>';
	
	/*echo '<div class="splitter"><span>Backpage rules</span></div>';
	$res = DBConnect::query("SELECT * FROM sys_rightrules", __FILE__, __LINE__);
	while($row = mysql_fetch_array($res))
	{
		if(strlen($row["name"]) > 9 && substr($row["name"], 0, 9) == "backpage_")
		{
			echo '<div class="splitter_light"><span>' . str_replace('_', ' -&gt; ', substr($row["name"], 9)) . '</span></div>';
			rightform::publish($row["name"]);
		}
	}*/
?>
</div>
</div>
<?php
	}
?>