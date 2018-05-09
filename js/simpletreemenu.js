window.persisteduls=new Object()
var ddtreemenu=new Object()
var litags=null;

ddtreemenu.closefolder="/css/back/icon/plus.gif" //set image path to "closed" folder image
ddtreemenu.openfolder="/css/back/icon/min.gif" //set image path to "open" folder image
ddtreemenu.dragginglink = null //als we drag and drop doen dan slaan we hier de node in op
ddtreemenu.dragginglinkover = null
ddtreemenu.dragcheck = null
ddtreemenu.afterdrop = null


function tree_addnode(thetreeid, scontent, parent_id, nodeid)
{
	thepnode = document.getElementById(parent_id);
	thepnode = $(thepnode);
	if(thepnode.length > 0)
	{
		//checken of er al een ul is
		the_ul = thepnode.children("ul");
		if(the_ul.length <= 0)
		{
			 the_ul = tree_addsubmenutonode(thepnode.get(0))
		}
		else
			the_ul = the_ul.get(0);
		the_li = document.createElement('li');
		the_li.setAttribute('id', nodeid);
		the_ul.appendChild(the_li);
		$(the_li).html(scontent);
		ddtreemenu.expandSubTree(thetreeid, the_ul);
	}
}


function tree_addsubmenutonode(node)
{
	the_ul = document.createElement('ul');
	the_ul.setAttribute("rel", "open");
	the_ul.style.display = "block";
	node.onclick = function(e)
		{
			
			if (window.event) e = window.event;
			coor = getRelativeMouseCoordinates(e, this);
			
			var x = coor.x;
			var y = coor.y; 
			
			//alert(e.clientX + " x " + e.clientY + "    " + x + " x " + y);
			if(x > 16 || y > 20)
			{
				return 0;
			}
			var submenu = $(this).children("ul").get(0)
			if (submenu.getAttribute("rel")=="closed")
			{
				submenu.style.display="block"
				submenu.setAttribute("rel", "open")
				submenu.parentNode.style.backgroundImage="url("+ddtreemenu.openfolder+")"
			}
			else if (submenu.getAttribute("rel")=="open")
			{
				submenu.style.display="none"
				submenu.setAttribute("rel", "closed")
				submenu.parentNode.style.backgroundImage="url("+ddtreemenu.closefolder+")"
			}
			ddtreemenu.preventpropagate(e)
		}
	node.style.backgroundImage = "url("+ddtreemenu.openfolder+")"
	node.style.backgroundPosition = "left 2px"
	node.style.backgroundRepeat="no-repeat"
	node.appendChild(the_ul);
	return the_ul;
}

function tree_removesubmenu(node)
{
	$(node).children("ul").remove();
	node.onclick = null;
	node.style.backgroundImage = "url(/css/back/icon/list.gif)"
	node.style.backgroundPosition = "left center"
}

function tree_removenode(node_id)
{
	var thenode = document.getElementById(node_id);
	if(thenode !== null)
	{
		var thepnode = thenode.parentNode;
		thepnode.removeChild(thenode);
		
		if(thepnode.childNodes.length == 0)
		{
			thepnode.parentNode.style.backgroundImage = 'url(icon/list.gif)';
			thepnode.parentNode.onclick = dummy();
			thepnode.parentNode.removeChild(thepnode);
		}
	}
}

function tree_select_node(thetreeid, nodeid)
{
	//alert(nodeid);
	var the_li = document.getElementById(nodeid);
	if(the_li != undefined)
		select_me_please(thetreeid, the_li.getElementsByTagName("div")[0]);
}

function tree_editnode(node_id)
{
		var thenode = document.getElementById(node_id);
		if(thenode !== null)
		{
			var thepnode = thenode.parentNode;
			thepnode.removeChild(thenode);
			
			if(thepnode.childNodes.length == 0)
			{
				thepnode.parentNode.style.backgroundImage = 'url(icon/list.gif)';
				thepnode.parentNode.onclick = dummy();
				thepnode.parentNode.removeChild(thepnode);
			}
		}
}

//functie die wordt aangeroepen door een link in de tree. die wil geselectreed worden
function select_me_please(thetreeid, thelink)
{
	/*
	//alles deselecteren
	litags = document.getElementById(thetreeid).getElementsByTagName("li");
	for (var i=0; i<litags.length; i++)
	{
		litags[i].getElementsByTagName("div")[0].style.backgroundColor="#EEEEEE";
		litags[i].getElementsByTagName("div")[0].style.color="#1C2833";
	}
	
	thelink.style.backgroundColor="#4D6F8C";
	thelink.style.color="#FFFFFF";
	//thelink.style.border="solid";
	//thelink.style.borderColor="#C9D5D6";
	//thelink.style.borderWidth="1px";
	thelink.style.paddingLeft="2px";
	thelink.style.paddingRight="2px";*/
	
	$("#" + thetreeid).find("li.selected").removeClass("selected");
	$(thelink).parent().addClass("selected");
}


//DRAG AND DROP-------------------------------------------------------------------------------

function tree_mousedown(thetreeid, thelink)
{
	draganddrop.startdrag(thelink);
	//draganddrop.functionalfterstop = new function(){tree_mouseup(null, null);}
	draganddrop.source = "tree";
	ddtreemenu.dragginglink = thelink;
}

function tree_mouseup(thetreeid, thelink, e)
{
	//hier doen we de acties
	if(ddtreemenu.dragginglinkover !== null)
	{
		ddtreemenu.dragginglinkover.style.backgroundImage = "none"
	}
	
	if(ddtreemenu.dragginglink != ddtreemenu.dragginglinkover && ddtreemenu.dragginglinkover !== null)
	{
		if(thelink !== null)
		{
			//we doen de drop
			var coor = getRelativeMouseCoordinates(e, thelink);
			//alert(coor.y)
			var dropabove = false
			var dropunder = false
			var dropin = false
			if(coor.y < 6)
				dropabove = true
			if(coor.y > 12)
				dropunder = true
			if(coor.y >= 6 && coor.y <= 12)
				dropin = true;
			var place = "in";
			if(coor.y > 12) place = "under";
			if(coor.y < 6) place = "above";
			
			draglink_li = ddtreemenu.dragginglink.parentNode
			dragover_li = ddtreemenu.dragginglinkover.parentNode
			//CHECKEN OF ER IN ZICHZELF WORDT GEDROPT
			draginitself = tree_check_initself(coor.y)
			var error = false;
			if(ddtreemenu.dragcheck != null && !draginitself)
			{
				if(!ddtreemenu.dragcheck(ddtreemenu.dragginglink, ddtreemenu.dragginglinkover, place))
					error = true;
			}
			if(!draginitself && !error)
			{
				//EERST AANVRAGEN AAN AJAX OF HET OK IS
				
				//Node verplaatsen
				//checken of de parent nog li's heeft
				remove_sub = false;
				parent_li = $(draglink_li).parent().parent().get(0);
				if($(draglink_li).parent().children("li").length <= 1)
					remove_sub = true;
				if(!e.ctrlKey)
				{
					draglink_li.parentNode.removeChild(draglink_li);
					if(remove_sub)
						tree_removesubmenu(parent_li)
				}
				else
					draglink_li = $(draglink_li).clone().get(0);
				
				if(dropabove)
				{
					dragover_li.parentNode.insertBefore(draglink_li, dragover_li)
				}
				if(dropunder)
				{
					dragover_li.parentNode.insertBefore(draglink_li, dragover_li.nextSibling)
				}
				if(dropin)
				{
					if($(dragover_li).children("ul").length > 0)
					{
						$(dragover_li).children("ul").get(0).appendChild(draglink_li);
					}
					else
					{
						the_ul = tree_addsubmenutonode(dragover_li);
						the_ul.appendChild(draglink_li);
					}
				}
				//AANPASSEN ALS HET EEN COPY BETREFD
				if(e.ctrlKey)
				{
					$(draglink_li).children("ul").remove();
					$(draglink_li).children("div").attr("copyof", $(draglink_li).children("div").attr("pageid"));
					if($(draglink_li).children("div").children("span").text() != "copy")
						$(draglink_li).children("div").append(' <span style="font-size:10px; color:#666666;">copy</span>');
					//pageid kan pas worden aangepast nadat de server de nieuwe pagina heeft aangemaakt
					
				}
			}
			if(ddtreemenu.afterdrop != null)
			   ddtreemenu.afterdrop((!draginitself && !error), ddtreemenu.dragginglink, ddtreemenu.dragginglinkover, place, e.ctrlKey.toString());
		}
	}
	
	ddtreemenu.dragginglink = null;
	ddtreemenu.dragginglinkover = null;
	if(draganddrop.dragging)
	{
		draganddrop.stopdrag();
	}
}

function tree_check_initself(ycoord)
{
	draglink_li = ddtreemenu.dragginglink.parentNode
	dragover_li = ddtreemenu.dragginglinkover.parentNode
	if($(draglink_li).attr("id") == $(dragover_li).attr("id") && (ycoord < 6 || ycoord > 12))
		return false;
	//CHECKEN OF ER IN ZICHZELF WORDT GEDROPT
	draginitself = false
	tmp = dragover_li
	while(tmp.tagName.toLowerCase() != "div")
	{
		if(tmp == draglink_li)
		{
			//alert("You cannot drag an element in itself.")
			draginitself = true
			break;
		}
		tmp = tmp.parentNode
	}
	return draginitself;
}

function tree_dragmove(thetreeid, thelink, e)
{
	if(ddtreemenu.dragginglink !== null)
	{
		if(e.ctrlKey)
		{
			if($(draganddrop.movediv).find("#dragcopyimg").get(0) == undefined)
			$(draganddrop.movediv).prepend('<img id="dragcopyimg" src="/css/back/icon/plus.gif"/>');
		}
		else
		{
			$(draganddrop.movediv).find("#dragcopyimg").remove();
		}
		if(ddtreemenu.dragginglinkover != thelink)
		{
			if(ddtreemenu.dragginglinkover != null)
			{
				ddtreemenu.dragginglinkover.style.backgroundImage = "none"
			}
			ddtreemenu.dragginglinkover = thelink
			var tmp = ddtreemenu.dragginglinkover.parentNode.id
			setTimeout("tree_dragexpand('" + tmp + "')",1200);
		}
		
		var coor = getRelativeMouseCoordinates(e, thelink);
		var good = true;
		
		
		if(tree_check_initself(coor.y))
			good = false;
		if(ddtreemenu.dragcheck != null && good)
		{
			var place = "in";
			if(coor.y > 12) place = "under";
			if(coor.y < 6) place = "above";
			if(!ddtreemenu.dragcheck(ddtreemenu.dragginglink, thelink, place))
				good = false;
		}
		
		if(coor.y > 12)
		{
			thelink.style.backgroundImage = "url(" + ((good)?"/css/back/treedrag/good-under.gif":"/css/back/treedrag/error-under.gif") + ")"
		}
		if(coor.y < 6)
		{
			thelink.style.backgroundImage = "url(" + ((good)?"/css/back/treedrag/good-above.gif":"/css/back/treedrag/error-above.gif") + ")"
		}
		if(coor.y <= 12 && coor.y >= 6)
		{
			thelink.style.backgroundImage = "url(" + ((good)?"/css/back/treedrag/good-in.gif":"/css/back/treedrag/error-in.gif") + ")"
		}
	}
}

function tree_dragexpand(thestartelementid)
{
	if(ddtreemenu.dragginglinkover!==null)
	{
		if(thestartelementid == ddtreemenu.dragginglinkover.parentNode.id)
		{
			var theexpandelement = ddtreemenu.dragginglinkover;
			while(theexpandelement.tagName.toLowerCase() != "li")
				var theexpandelement = theexpandelement.parentNode;
			//we zoeken naar de ul
			var theexpandelement = theexpandelement.getElementsByTagName("ul")[0];
			if(theexpandelement !== null && theexpandelement != undefined)
			{
				
				theexpandelement.style.display="block"
				theexpandelement.setAttribute("rel", "open") //indicate it's open
				theexpandelement.parentNode.style.backgroundImage="url("+ddtreemenu.openfolder+")"
			}
		}
	}
}

//////////No need to edit beyond here///////////////////////////

ddtreemenu.createTree = function(treeid, enablepersist, persistdays)
{
	var ultags=document.getElementById(treeid).getElementsByTagName("ul")

	if (typeof persisteduls[treeid]=="undefined")
 		persisteduls[treeid]=(enablepersist==true && ddtreemenu.getCookie(treeid)!="")? ddtreemenu.getCookie(treeid).split(",") : ""

	var test_uls = ddtreemenu.getCookie(treeid).split(",");
	for (var i=0; i<ultags.length; i++)
		ddtreemenu.buildSubTree(treeid, ultags[i], i, test_uls)

	if (enablepersist==true)
	{ //if enable persist feature
		var durationdays=(typeof persistdays=="undefined")? 1 : parseInt(persistdays)
		ddtreemenu.dotask(window, function(){ddtreemenu.rememberstate(treeid, durationdays)}, "unload") //save opened UL indexes on body unload
	}
}

ddtreemenu.buildSubTree = function(treeid, ulelement, index, persisted)
{
	ulelement.parentNode.className="submenu"
	if (typeof persisted=="object")
	{ //if cookie exists (persisteduls[treeid] is an array versus "" string)
		//if (ddtreemenu.searcharray(persisteduls[treeid], index))
		if (ddtreemenu.searcharray(persisted, ulelement.parentNode.getAttribute("id")))
		{
			ulelement.setAttribute("rel", "open")
			ulelement.style.display="block"
			ulelement.parentNode.style.backgroundImage="url("+ddtreemenu.openfolder+")"
		}
		else
			ulelement.setAttribute("rel", "closed")
	} //end cookie persist code
	else if (ulelement.getAttribute("rel")==null || ulelement.getAttribute("rel")==false) //if no cookie and UL has NO rel attribute explicted added by user
		ulelement.setAttribute("rel", "closed")
	else if (ulelement.getAttribute("rel")=="open") //else if no cookie and this UL has an explicit rel value of "open"
		ddtreemenu.expandSubTree(treeid, ulelement) //expand this UL plus all parent ULs (so the most inner UL is revealed!)
	
	ulelement.parentNode.onclick=function(e)
	{
		//alert("test");
		if (window.event) e = window.event;
		//var parent = this;
		//var offset = $(parent).offset();
		if ( e.pageX == null && e.clientX != null ) {
		  var b = document.body;
		  e.pageX = e.clientX + (e && e.scrollLeft || b.scrollLeft || 0);
		  e.pageY = e.clientY + (e && e.scrollTop || b.scrollTop || 0);
	   }
		coor = getRelativeMouseCoordinates(e, this);
		//alert(coor.x + " " + coor.y);
		var x = coor.x;
		var y = coor.y; 
		
		//alert(e.clientX + " x " + e.clientY + "    " + x + " x " + y);
		if(x > 16 || y > 20)
		{
			return 0;
		}
		var submenu=this.getElementsByTagName("ul")[0]
		if (submenu.getAttribute("rel")=="closed")
		{
			submenu.style.display="block"
			submenu.setAttribute("rel", "open")
			ulelement.parentNode.style.backgroundImage="url("+ddtreemenu.openfolder+")"
		}
		else if (submenu.getAttribute("rel")=="open")
		{
			submenu.style.display="none"
			submenu.setAttribute("rel", "closed")
			ulelement.parentNode.style.backgroundImage="url("+ddtreemenu.closefolder+")"
		}
		ddtreemenu.preventpropagate(e)
	}
	ulelement.onclick=function(e)
	{
		ddtreemenu.preventpropagate(e)
	}
}

ddtreemenu.expandSubTree=function(treeid, ulelement)
{ //expand a UL element and any of its parent ULs
	var rootnode=document.getElementById(treeid)
	var currentnode=ulelement
	currentnode.style.display="block"
	currentnode.parentNode.style.backgroundImage="url("+ddtreemenu.openfolder+")"
	while (currentnode!=rootnode)
	{
		if (currentnode.tagName=="UL"){ //if parent node is a UL, expand it too
			currentnode.style.display="block"
			currentnode.setAttribute("rel", "open") //indicate it's open
			currentnode.parentNode.style.backgroundImage="url("+ddtreemenu.openfolder+")"
		}
		currentnode=currentnode.parentNode
	}
}

ddtreemenu.flatten=function(treeid, action){ //expand or contract all UL elements
var ultags=document.getElementById(treeid).getElementsByTagName("ul")
for (var i=0; i<ultags.length; i++){
ultags[i].style.display=(action=="expand")? "block" : "none"
var relvalue=(action=="expand")? "open" : "closed"
ultags[i].setAttribute("rel", relvalue)
ultags[i].parentNode.style.backgroundImage=(action=="expand")? "url("+ddtreemenu.openfolder+")" : "url("+ddtreemenu.closefolder+")"
}
}

ddtreemenu.rememberstate=function(treeid, durationdays)
{ 
//store index of opened ULs relative to other ULs in Tree into cookie
	var ultags=document.getElementById(treeid).getElementsByTagName("ul")
	var openuls=new Array()
	for (var i=0; i<ultags.length; i++)
	{
		if (ultags[i].getAttribute("rel")=="open")
		{
			//openuls[openuls.length]=i //save the index of the opened UL (relative to the entire list of ULs) as an array element
			openuls[openuls.length]=ultags[i].parentNode.getAttribute("id")
		}
	}
	if (openuls.length==0) //if there are no opened ULs to save/persist
		openuls[0]="none open" //set array value to string to simply indicate all ULs should persist with state being closed
	ddtreemenu.setCookie(treeid, openuls.join(","), durationdays) //populate cookie with value treeid=1,2,3 etc (where 1,2... are the indexes of the opened ULs)
}

////A few utility functions below//////////////////////

ddtreemenu.getCookie=function(Name){ //get cookie value
var re=new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
if (document.cookie.match(re)) //if cookie found
return document.cookie.match(re)[0].split("=")[1] //return its value
return ""
}

ddtreemenu.setCookie=function(name, value, days){ //set cookei value
var expireDate = new Date()
//set "expstring" to either future or past date, to set or delete cookie, respectively
var expstring=expireDate.setDate(expireDate.getDate()+parseInt(days))
document.cookie = name+"="+value+"; expires="+expireDate.toGMTString()+"; path=/";
}

ddtreemenu.searcharray=function(thearray, value){ //searches an array for the entered value. If found, delete value from array
var isfound=false
for (var i=0; i<thearray.length; i++){
if (thearray[i]==value){
isfound=true
thearray.shift() //delete this element from array for efficiency sake
break
}
}
return isfound
}

ddtreemenu.preventpropagate=function(e){ //prevent action from bubbling upwards
if (typeof e!="undefined")
{
	if(e.stopPropagation)
		e.stopPropagation()
	else
		e.cancelBubble = true;
}
else
event.cancelBubble=true
}

ddtreemenu.dotask=function(target, functionref, tasktype){ //assign a function to execute to an event handler (ie: onunload)
var tasktype=(window.addEventListener)? tasktype : "on"+tasktype
if (target.addEventListener)
target.addEventListener(tasktype, functionref, false)
else if (target.attachEvent)
target.attachEvent(tasktype, functionref)
}