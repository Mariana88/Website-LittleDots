
//----------------------------------------------------------------------------
// FCK
//----------------------------------------------------------------------------

var sa_the_fck_id;
function sa_get_data_field_in_fck(type, id, fck_id)
{
  sa_the_fck_id = fck_id;
  var xmlHttp;
  try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
  catch (e)
  {
  // Internet Explorer
    try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      try
      {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e)
      {
      alert("Your browser does not support AJAX!");
      return false;
      }
    }
  }
  
  
  xmlHttp.onreadystatechange=function()
  {
    if(xmlHttp.readyState==4)
    {
    	//alert(xmlHttp.responseText);
		var FCK = FCKeditorAPI.GetInstance(sa_the_fck_id); 
		FCK.SetHTML(xmlHttp.responseText);
    }
  }
  //alert("ajax.php?getdata=" + type + "&id=" + id);
  xmlHttp.open("GET","/ajax.php?getdata=" + type + "&id=" + id,true);
  xmlHttp.send(null);
}


//this function can be used to send an ajax request!!!
//with GET OR POST
function send_ajax_request(method, urlstr, postvars, handlefuntion)
{
  var xmlHttp;
  try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
  catch (e)
  {
  // Internet Explorer
    try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      try
      {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e)
      {
      alert("Your browser does not support AJAX!");
      return false;
      }
    }
  }
  
  
  xmlHttp.onreadystatechange=function()
  {
    if(xmlHttp.readyState==4)
    {
    	//alert(xmlHttp.responseText);
		if(handlefuntion !== null)
			handlefuntion(xmlHttp);
    }
	else
	{
		
	}
  }
  //alert("ajax.php?getdata=" + type + "&id=" + id);
  if(method == "POST")
  {
	  xmlHttp.open('POST', urlstr, true);
      xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xmlHttp.setRequestHeader("Content-length", postvars.length);
      xmlHttp.setRequestHeader("Connection", "close");
	 // xmlHttp.overrideMimeType('text/xml');
	  xmlHttp.send(postvars);
  }
  else
  {
  	xmlHttp.open('GET', urlstr,true);
	//xmlHttp.overrideMimeType('text/xml');
    xmlHttp.send(null);
  }
}

//THIS FUNCTION POSTS A FORM
function ajax_post_form(form_id, urlstr, handlefunction, returnstring)
{
	if (returnstring == null)
   		returnstring = false;
 	
	var theform = document.getElementById(form_id);
	if(theform !== null)
	{
		var thepostvars = new Array();
		var index = 0;
		var input_elements = theform.getElementsByTagName("input");
		for (var i=0; i<input_elements.length; i++)
		{
			//als het input element in een element zit met autopost="no" als attribuut dan posten we het niet!!
			try
  			{
				var nopost = false;
				var parentnode = input_elements[i].parentNode;
				while(parentnode != null && parentnode != undefined && parentnode.id != form_id)
				{
					if(parentnode.getAttribute("autopost") == "no" && parentnode.id != form_id)
					{
						nopost = true;
						break;
					}
					parentnode =parentnode.parentNode;
				}
				if(nopost)
					continue;
			}
			catch(err)
			{
				
			}
			switch(input_elements[i].getAttribute("type"))
			{
				case "checkbox":
					if(input_elements[i].checked)
						thepostvars[index] = new Array(input_elements[i].getAttribute("name"), 1);
					else
						thepostvars[index] = new Array(input_elements[i].getAttribute("name"), 0);
					index++;
					break;
				case "radio":
					if(input_elements[i].checked)
					{
						thepostvars[index] = new Array(input_elements[i].getAttribute("name"), input_elements[i].value);
						index++;
					}
					break;
				case "hidden": // we have to check if its a hidden box from a fckeditor!!! else we handle it like text
					var inst = null;
					try {
						var inst = FCKeditorAPI.GetInstance(input_elements[i].getAttribute("name"));
					} catch (error) {
						
					}
					
					if(inst !== null && inst !== undefined)
					{
						thepostvars[index] = new Array(input_elements[i].getAttribute("name"), inst.GetHTML());
					}
					else
					{
						thepostvars[index] = new Array(input_elements[i].getAttribute("name"), input_elements[i].value);
					}
					index++;
					break;
				case "button":
					break;
				case "submit":
					break;
				default:
					thepostvars[index] = new Array(input_elements[i].getAttribute("name"), input_elements[i].value);
					index++;
					break;
			}
		}
		var select_elements = theform.getElementsByTagName("select");
		for (var i=0; i<select_elements.length; i++)
		{
			try
  			{
				var nopost = false;
				var parentnode = select_elements[i].parentNode;
				while(parentnode != null && parentnode != undefined && parentnode.id != form_id)
				{
					if(parentnode.getAttribute("autopost") == "no" && parentnode.id != form_id)
					{
						nopost = true;
						break;
					}
					parentnode =parentnode.parentNode;
				}
				if(nopost)
					continue;
			}
			catch(err)
			{
				
			}
			thepostvars[index] = new Array(select_elements[i].getAttribute("name"), select_elements[i].value);
			index++;
		}
		var textarea_elements = theform.getElementsByTagName("textarea");
		for (var i=0; i<textarea_elements.length; i++)
		{
			try
  			{
				var nopost = false;
				var parentnode = textarea_elements[i].parentNode;
				while(parentnode != null && parentnode != undefined && parentnode.id != form_id)
				{
					if(parentnode.getAttribute("autopost") == "no" && parentnode.id != form_id)
					{
						nopost = true;
						break;
					}
					parentnode =parentnode.parentNode;
				}
				if(nopost)
					continue;
			}
			catch(err)
			{
				
			}
			thepostvars[index] = new Array(textarea_elements[i].getAttribute("name"), textarea_elements[i].value);
			index++;
		}
		
		//now we convert to post string
		var poststr = "";
		for (var i=0; i<thepostvars.length; i++)
		{
			if(poststr != "")
				poststr += "&";
			poststr += encodeURI(thepostvars[i][0]).replace(".", "##") + "=" + encodeURI(thepostvars[i][1]).replace(/&/g, "_ampersant_").replace(/\+/g, "_plus_");
		}
		//alert(poststr);
		if(returnstring)
			return poststr;
		else
			send_ajax_request("POST", urlstr, poststr, handlefunction);
	}
}

function check_error_return(xmlHttp)
{
	if(xmlHttp.responseText.substring(0, 8) == "NORIGHTS")
	{
		show_error_message(xmlHttp.responseText.substring(9));
		return false;
	}
	else
	{
		return true;
	}
}

function alert_ajax_return(xmlHttp)
{
		alert(xmlHttp.responseText);
}

function include(script_filename) {
    document.write('<' + 'script');
    document.write(' language="javascript"');
    document.write(' type="text/javascript"');
    document.write(' src="' + script_filename + '">');
    document.write('</' + 'script' + '>');
}
