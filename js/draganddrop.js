//hierin staat de code die drag and dropping mogelijk maakt.
//er wordt aangegeven wel element er wordt gedragd
var draganddrop=new Object();

draganddrop.element = null;		//element dat gecopiëerd moet worden
draganddrop.movediv = null;		//de namaak div
draganddrop.dragging = false;	//zijn we aan het draggen?
draganddrop.source = null;		//hierin komt welk object de source is, voor de actie na de drop


document.onmousemove=function(e)
{
	e = e || window.event; 
	if(draganddrop.dragging)
	{
		if(draganddrop.movediv == null)
		{
			//creëren van de copydiv
			draganddrop.movediv = document.createElement('div');
		   	draganddrop.movediv.setAttribute('id', 'draganddropcopydiv');
		   	draganddrop.movediv.innerHTML = draganddrop.element.innerHTML;
			draganddrop.movediv.style.position = 'absolute';
			draganddrop.movediv.style.top = (e.clientY + document.body.scrollTop) + 10;
			draganddrop.movediv.style.left = e.clientX + 10;
			draganddrop.movediv.onmouseup = function(e){draganddrop.stopdrag();}
			
		   	document.body.appendChild(draganddrop.movediv);
			draganddrop.movediv.style.display = 'block';
			
			//selectie tegengaan
			document.onselectstart = function() {return false;}
			//voor mozilla
			document.body.style.MozUserSelect = "none";
			if (window.getSelection) {
       			 window.getSelection().removeAllRanges();
    		} else if (document.selection) {
        		document.selection.empty();
    		}
		}
		draganddrop.movediv.style.top = (e.clientY + document.body.scrollTop) + "px";
		draganddrop.movediv.style.left = (e.clientX+10) + "px";
	}
}

draganddrop.startdrag = function(copyelement)
{
	draganddrop.element = copyelement;
	document.onmouseup = function(e){draganddrop.stopdrag();}
	draganddrop.dragging = true;
}

draganddrop.stopdrag = function()
{
	//als de movediv bestaat dan verwijderen
	if (draganddrop.movediv != null)
	{
			//alert("stopdrag");
			document.body.removeChild(draganddrop.movediv);
			draganddrop.movediv = null;
	}
	draganddrop.dragging = false;
	
	document.onmouseup = null;
	document.onselectstart = null;
	document.body.style.MozUserSelect = "inherit";
	
	switch(draganddrop.source)
	{
		case "tree": tree_mouseup(null, null);
			break;
	}
}
