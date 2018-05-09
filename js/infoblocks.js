function infoblocks_refresh(xmlHttp)
{
	//alert(xmlHttp.responseText);
	infoblocks_html_panel.loadContent('/ajax.php?sessid=' + session_id + '&page_id=' + xmlHttp.responseText + '&action=refresh');
}