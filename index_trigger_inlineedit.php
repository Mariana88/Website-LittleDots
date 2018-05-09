<?php
	session_start();
	if($_GET["inline_edit"] == "true")
		$_SESSION["inline_edit_on"] = true;
	else
		$_SESSION["inline_edit_on"] = false;
?>