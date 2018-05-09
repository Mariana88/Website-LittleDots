<?php
	/*
		CONFIG VAN TINYMCE
		0 = Standaard = textpage/about;
		1 = event
		2 = post
		3 = newsletter
	*/
	require_once("../systemclasses/Class.dbconnect.php");
?>
	var mce_config_array = [{
								menubar : false,
                                theme : "modern",
                                plugins: "link",
								editor_selector : "tinymce_' . $postname . '",
								content_css : "/css/front/styles.css",
								theme_modern_toolbar_location : "top",
								theme_modern_toolbar_align : "left",
								theme_modern_statusbar : false,
								theme_modern_buttons1 : "bold,italic,sub,sup,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,align,justifyleft,justifycenter,justifyright,justifyfull|,link,unlink,image,code",
								theme_modern_buttons2 : "formatselect,|,bullist,numlist,|,hr,removeformat,|,charmap",
								theme_modern_buttons3 : "",
								theme_modern_blockformats : "p,h1,h2,h3,h4",
								width : "378",
								relative_urls : true,
								convert_urls : false
                            },
                            {
								theme : "modern",
								editor_selector : "tinymce_' . $postname . '",
								content_css : "/css/front/styles.css",
								theme_modern_toolbar_location : "top",
								theme_modern_toolbar_align : "left",
								theme_modern_statusbar : false,
								theme_modern_buttons1 : "bold,italic,sub,sup,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,align,justifyleft,justifycenter,justifyright,justifyfull|,link,unlink,image,code",
								theme_modern_buttons2 : "formatselect,|,bullist,numlist,|,hr,removeformat,|,charmap",
								theme_modern_buttons3 : "",
								theme_modern_blockformats : "p,h1,h2,h3,h4",
								width : "378",
								relative_urls : true,
								convert_urls : false
							},
                            {
                               theme : "modern",
								editor_selector : "tinymce_' . $postname . '",
								content_css : "/css/front/styles.css",
								theme_modern_toolbar_location : "top",
								theme_modern_toolbar_align : "left",
								theme_modern_statusbar : false,
								theme_modern_buttons1 : "bold,italic,sub,sup,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,align,justifyleft,justifycenter,justifyright,justifyfull|,link,unlink,image,code",
								theme_modern_buttons2 : "formatselect,|,bullist,numlist,|,hr,removeformat,|,charmap",
								theme_modern_buttons3 : "",
								theme_modern_blockformats : "p,h1,h2,h3,h4",
								width : "378",
								relative_urls : true,
								convert_urls : false
							}];

