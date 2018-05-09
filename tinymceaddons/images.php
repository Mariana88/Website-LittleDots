<div id="selection" style="display:none;">
</div>

<div class="form" id="formdiv" style="display:none;">
<div class="splitter"><span>edit or add image</span></div>
<?php
	formfield::publish('image', '', false, 14, '', true, "Image", NULL, NULL, 0, 0, "", $_SESSION["CMS_EDIT_LANG"]);
	formfield::publish('thumb', '', false, 22, '', true, "thumbnail", NULL, NULL, 0, 0, "", $_SESSION["CMS_EDIT_LANG"]);
	formfield::publish('title', '', false, 1, '', true, "Info", NULL, NULL, 0, 0, "", $_SESSION["CMS_EDIT_LANG"]);
	formfield::publish('copyright', '', false, 1, '', true, "Copyright", NULL, NULL, 0, 0, "", $_SESSION["CMS_EDIT_LANG"]);
?>
<div style="clear:both;"></div>
<div class="savebutton" id="savebutton" style="float: left;">OK</div><div class="savebutton" id="cancelbutton" style="float: left;">Cancel</div>
</div>

<div class="savebutton" id="newbutton" style="float: right;">Add image</div>
<div class="splitter" style="margin-top:10px; clear:both;"><span>List</span></div>
<div id="list">
</div>

<div class="savebutton" id="insertbutton" style="float: right;">Insert Code</div>

<div style="clear:both;"></div>
<script language="javascript">
	$(document).ready(function(){
		$("#selection").html(window.tinymce_editor.selection.getContent());
		if($("#selection").find("div.fotogallery").length > 0)
		{
			//ophalen van alle a's, overlopen en interpreteren
			$("#selection").find("div.fotogallery").find("a").each(function(){
				$("#list").append('<div style="height:90px;"><img style="float: left; width:80px; height:80px; margin-right:10px;" src="' + $(this).find("img").attr("src") + '"><span style="font-weight:bold">Title:</span><span>' + $(this).attr("title") + '</span><br><span style="font-weight:bold">Copyright:</span><span>' + $(this).attr("copyright") + '</span><br><br><a class="listedit">edit</a> <a class="listdelete">delete</a><div class="data" style="display:none;"><div data="image">' + $(this).attr("href") + '</div><div data="thumb">' + $(this).find("img").attr("src") + '</div><div data="title">' + $(this).attr("title") + '</div><div data="copyright">' + $(this).attr("copyright") + '</div></div></div>');	
			});
			//de sorteerbaarheid instellen
			$("#list").sortable();
			
			//edits & deletes
			edits_deletes()
		}	
		
		//NEW BUTTON
		$("#newbutton").click(function(){
			$("#formdiv").show();
		});
		
		//SAVE Button
		$("#savebutton").click(function(){
			alert("save");	
			$("#formdiv").hide();
		});
		
		//Cancel Button 
		$("#cancelbutton").click(function(){
			$("#formdiv").hide();
		});
		
		//Insert Button 
		$("#insertbutton").click(function(){
			alert("insert");	
		});
	});
	
	function edits_deletes()
	{
		//edits
		$("#list").find("a.listedit").click(function(){
			$('[name="image"]').val($(this).parent().find('div[data="image"]').text());
			$('[name="title"]').val($(this).parent().find('div[data="title"]').text());
			$('[name="copyright"]').val($(this).parent().find('div[data="copyright"]').text());
			$("#formdiv").show();							
		});
		//deletes
		$("#list").find("a.listdelete").click(function(){
			var answer = confirm ("Are you sure")
			if (answer)
				$(this).parent().remove();		
		});
	}
</script>