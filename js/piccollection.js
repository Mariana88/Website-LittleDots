function piccollection(theid) {   
    this.id = theid; 
}

function piccollection_deletepic(thetd) 
{

}

function piccollection_addpic_id(theid)
{
	send_ajax_request('POST', '/ajax.php?sessid=' + session_id + '&piccol_id=' + this.id + '&action=form_id', 'picid=' + theid, this.returnform);
	//alert('add id: ' + theid);
}

function piccollection_addpic_path(thepath)
{
	send_ajax_request('POST', '/ajax.php?sessid=' + session_id + '&piccol_id=' + this.id + '&action=form_path', 'picpath=' + thepath, this.returnform);
}

function piccollection_returnform(xmlHttp)
{
	doc = $(xmlHttp.responseXML);
	$("#piccol_" + doc.find("piccol_id").text()).append(doc.find("content").text());
}

new piccollection(0);
piccollection.prototype.deletepic = piccollection_deletepic;
piccollection.prototype.addpic_id = piccollection_addpic_id;
piccollection.prototype.addpic_path = piccollection_addpic_path;
piccollection.prototype.returnform = piccollection_returnform;