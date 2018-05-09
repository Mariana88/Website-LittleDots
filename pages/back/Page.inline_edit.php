<?php
	echo '<iframe style="margin:16px;" id="inline_edit_iframe" src="http://' . $_SERVER['HTTP_HOST'] . '" width="100%" height="600px;"></iframe>';	
?>
<script language="javascript">
	$(document).ready(function() {
		$("#inline_edit_iframe").height($(window).height() - 56);
		$("#inline_edit_iframe").width($(window).width() - 16);
	});
</script>