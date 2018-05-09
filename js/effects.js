var effects = new Array();
var effects_running = false;
var TimeToFade = 1000.0;

function effects_fade(element, effect_time)
{
	var effects_i = effects.length;
	effects[effects_i] = element;
	element.effect = "fade";
	element.effect_time = effect_time;
 	
 	if(element.FadeState == null)
  	{
    	if(element.style.opacity == null
        	|| element.style.opacity == ''
        	|| element.style.opacity == '1')
    	{
      		element.FadeState = 2;
    	}
    	else
   		{
      		element.FadeState = -2;
    	}
  	}
  	if(element.FadeState == 1 || element.FadeState == -1)
  	{
    	element.FadeState = element.FadeState == 1 ? -1 : 1;
    	element.FadeTimeLeft = element.effect_time - element.FadeTimeLeft;
  	}
  	else
  	{
   	 	element.FadeState = element.FadeState == 2 ? -1 : 1;
    	element.FadeTimeLeft = element.effect_time;
		if(!effects_running)
		{
    		effects_running = true;
			setTimeout("animateEffects(" + new Date().getTime() + ")", 33);
		}
  	}  
}

function effects_changeheight(element, effect_time, fromheight, toheight)
{
	//make sure the element has a background
	if(element.style.height == undefined)
		element.style.height = fromheight + 'px';
	element.style.overflow = 'hidden';
	var effects_i = effects.length;
	effects[effects_i] = element;
	element.effect = "changeheight";
	element.effect_time = effect_time;
	element.effect_ch_fromheight = fromheight;
	element.effect_ch_toheight = toheight;
 	
    element.FadeTimeLeft = element.effect_time;
    if(!effects_running)
	{
		effects_running = true;
		setTimeout("animateEffects(" + new Date().getTime() + ")", 33);
	}
}

function effects_highlight(element, effect_time, tocolor, andreturn)
{
	try
	{
		//make sure the element has a background
		if(element.style.backgroundColor == undefined || element.style.backgroundColor == '')
			element.style.backgroundColor = "#FFFFFF";
		var effects_i = effects.length;
		effects[effects_i] = element;
		element.effect = "highlight";
		element.effect_time = effect_time;
		element.effect_hl_tocolor = tocolor;
		element.effect_hl_fromcolor = effects_rgbConvert(element.style.backgroundColor);
		element.effect_hl_return = andreturn;
		if(andreturn)
			element.effect_time = effect_time/2;
		
		element.FadeState = -1;
		element.FadeTimeLeft = element.effect_time;
		if(!effects_running)
		{
			effects_running = true;
			setTimeout("animateEffects(" + new Date().getTime() + ")", 33);
		}
	}
	catch(err){}
}

function animateEffects(lastTick)
{  
	var curTick = new Date().getTime();
	var elapsedTicks = curTick - lastTick;
	var tmp_effects_running = false;
	for(i = 0 ; i < effects.length ; i++)
	{
		switch(effects[i].effect)
		{
			case "fade":
				if (animateFade(lastTick, curTick, elapsedTicks, effects[i])) tmp_effects_running = true;
				else
				{
					effects.splice(i, 1);
					i--;
				}
				break;
			case "highlight":
				if (animateHighlight(lastTick, curTick, elapsedTicks, effects[i])) tmp_effects_running = true;
				else
				{
					if(effects[i].effect_hl_return)
					{
						effects_highlight(effects[i], effects[i].effect_time, (typeof effects[i].effect_hl_return == "string"? effects[i].effect_hl_return : effects[i].effect_hl_fromcolor), false);
					}
					effects.splice(i, 1);
					i--;
				}
				break;
			case "changeheight":
				if (animateChangeheight(lastTick, curTick, elapsedTicks, effects[i])) tmp_effects_running = true;
				else
				{
					effects.splice(i, 1);
					i--;
				}
				break;
		}
	}
	
	if(tmp_effects_running)
		setTimeout("animateEffects(" + curTick + ")", 33);
	else
		effects_running = false;
}

function animateFade(lastTick, curTick, elapsedTicks, element)
{
	if(element.FadeTimeLeft <= elapsedTicks)
  	{
		element.style.opacity = element.FadeState >= 1 ? '1' : '0';
    	element.style.filter = 'alpha(opacity = ' + (element.FadeState >= 1 ? '100' : '0') + ')';
    	element.FadeState = element.FadeState >= 1 ? 2 : -2;
    	return false;
  	}
 
  	element.FadeTimeLeft -= elapsedTicks;
  	var newOpVal = element.FadeTimeLeft/element.effect_time;
  	if(element.FadeState == 1)
    	newOpVal = 1 - newOpVal;

  	element.style.opacity = newOpVal;
  	element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
 	
	return true;
}

function animateChangeheight(lastTick, curTick, elapsedTicks, element)
{
	if(element.FadeTimeLeft <= elapsedTicks)
  	{
		element.style.height = element.effect_ch_toheight + 'px';
    	return false;
  	}
 
  	element.FadeTimeLeft -= elapsedTicks;
	
  	var percent = (element.effect_time - element.FadeTimeLeft)/element.effect_time;
	var newheight = 0;
	if(element.effect_ch_fromheight < element.effect_ch_toheight)
		newheight = element.effect_ch_fromheight + parseInt(((element.effect_ch_toheight - element.effect_ch_fromheight)*percent));
	else
		newheight = element.effect_ch_fromheight - parseInt(((element.effect_ch_fromheight - element.effect_ch_toheight)*percent));
	
  	element.style.height = newheight + "px";
 	
	return true;
}

function animateHighlight(lastTick, curTick, elapsedTicks, element)
{
	if(element.FadeTimeLeft <= elapsedTicks)
  	{
		element.style.backgroundColor = element.effect_hl_tocolor;
    	return false;
  	}
 
  	element.FadeTimeLeft -= elapsedTicks;
	var fromcolor = element.effect_hl_fromcolor.substring(1);
	var tocolor = element.effect_hl_tocolor.substring(1);
	var from_r = parseInt(fromcolor.substring(0,2), 16);
	var from_g = parseInt(fromcolor.substring(2,4), 16);
	var from_b = parseInt(fromcolor.substring(4,6), 16);
	var to_r = parseInt(tocolor.substring(0,2), 16);
	var to_g = parseInt(tocolor.substring(2,4), 16);
	var to_b = parseInt(tocolor.substring(4,6), 16);
	
  	var percent = (element.effect_time - element.FadeTimeLeft)/element.effect_time;
	var new_r = 0;
	var new_g = 0;
	var new_b = 0;
	if(from_r < to_r) new_r = from_r + parseInt(((to_r - from_r)*percent));
	else new_r = from_r - parseInt(((from_r - to_r)*percent));
	if(from_g < to_g) new_g = from_g + parseInt(((to_g - from_g)*percent));
	else new_g = from_g - parseInt(((from_g - to_g)*percent));
	if(from_b < to_b) new_b = from_b + parseInt(((to_b - from_b)*percent));
	else new_b = from_b - parseInt(((from_b - to_b)*percent));
	
	var new_r_str = new_r.toString(16);
	if(new_r_str.length == 1) new_r_str = "0" + new_r_str;
	var new_g_str = new_g.toString(16);
	if(new_g_str.length == 1) new_g_str = "0" + new_g_str;
	var new_b_str = new_b.toString(16);
	if(new_b_str.length == 1) new_b_str = "0" + new_b_str;
	
	var newcolor = "#" + new_r_str + new_g_str + new_b_str;
	//alert(newcolor);
  	element.style.backgroundColor = newcolor;
 	
	return true;
}

function effects_rgbConvert(str) {
	if(str.substring(0,3) == "rgb")
	{
	   str = str.replace(/rgb\(|\)/g, "").split(",");
	   str[0] = parseInt(str[0], 10).toString(16).toLowerCase();
	   str[1] = parseInt(str[1], 10).toString(16).toLowerCase();
	   str[2] = parseInt(str[2], 10).toString(16).toLowerCase();
	   str[0] = (str[0].length == 1) ? '0' + str[0] : str[0];
	   str[1] = (str[1].length == 1) ? '0' + str[1] : str[1];
	   str[2] = (str[2].length == 1) ? '0' + str[2] : str[2];
	   return ('#' + str.join(""));
	}
	else
		return str;
}

/*
function fade(eid)
{
  var element = document.getElementById(eid);
  if(element == null)
    return;
   
  if(element.FadeState == null)
  {
    if(element.style.opacity == null
        || element.style.opacity == ''
        || element.style.opacity == '1')
    {
      element.FadeState = 2;
    }
    else
    {
      element.FadeState = -2;
    }
  }
   
  if(element.FadeState == 1 || element.FadeState == -1)
  {
    element.FadeState = element.FadeState == 1 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade - element.FadeTimeLeft;
  }
  else
  {
    element.FadeState = element.FadeState == 2 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade;
    setTimeout("animateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
  }  
}

function animateFade(lastTick, eid)
{  
  var curTick = new Date().getTime();
  var elapsedTicks = curTick - lastTick;
 
  var element = document.getElementById(eid);
 
  if(element.FadeTimeLeft <= elapsedTicks)
  {
    element.style.opacity = element.FadeState == 1 ? '1' : '0';
    element.style.filter = 'alpha(opacity = '
        + (element.FadeState == 1 ? '100' : '0') + ')';
    element.FadeState = element.FadeState == 1 ? 2 : -2;
    return;
  }
 
  element.FadeTimeLeft -= elapsedTicks;
  var newOpVal = element.FadeTimeLeft/TimeToFade;
  if(element.FadeState == 1)
    newOpVal = 1 - newOpVal;

  element.style.opacity = newOpVal;
  element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
 
  setTimeout("animateFade(" + curTick + ",'" + eid + "')", 33);
}*/