var threestatetree=new Object();

threestatetree.img_unchecked = "css/back/img/checkbox/empty.gif";
threestatetree.img_checked = "css/back/img/checkbox/check.gif";
threestatetree.img_gray = "css/back/img/checkbox/half.gif";
threestatetree.change_to_field = new Array(0);
threestatetree.change_to_field_attr = new Array(0);
threestatetree.change_to_field_sep = new Array(0);


threestatetree.create_checktree=function(treeid, defaultchecked)
{
	threestatetree.treeid = treeid;
	var li_elements = document.getElementById(treeid).getElementsByTagName("li");
	for (var i=0; i<li_elements.length; i++)
	{
		var newimg = document.createElement('img');
		
		var the_state = li_elements[i].getAttribute('state');
		//alert(the_state);
		if(the_state == 'checked' || the_state == 'unchecked')
		{
			if(the_state == 'checked')
				newimg.setAttribute('src',threestatetree.img_checked);
			else
				newimg.setAttribute('src',threestatetree.img_unchecked);
		}
		else
		{
			if(defaultchecked == true)
			{
				newimg.setAttribute('src',threestatetree.img_checked);
				the_state = 'checked';
			}
			else
			{
				the_state = 'unchecked';
				newimg.setAttribute('src',threestatetree.img_unchecked);
			}
		}
		newimg.setAttribute('state',the_state);
		newimg.setAttribute('name','threestatecheckbox');
		newimg.onclick = function() 
		{
			//we check or uncheck all the children
			var li_sub = this.parentNode.getElementsByTagName("li");
			for (var j=0; j<li_sub.length; j++)
			{
				var the_img = li_sub[j].childNodes[0];
				if(this.getAttribute('state') == 'unchecked' || this.getAttribute('state') == 'gray')
				{
					the_img.setAttribute('src',threestatetree.img_checked);
					the_img.setAttribute('state','checked');
				}
				else
				{
					the_img.setAttribute('src',threestatetree.img_unchecked);
					the_img.setAttribute('state','unchecked');
				}
			}
			if(this.getAttribute('state') == 'unchecked' || this.getAttribute('state') == 'gray')
			{
				this.setAttribute('src',threestatetree.img_checked);
				this.setAttribute('state','checked');
			}
			else
			{
				this.setAttribute('src',threestatetree.img_unchecked);
				this.setAttribute('state','unchecked');
			}
			//we let the parent nodes check if their childnodes are all checked or not
			threestatetree.check_for_gray();
			for(var k = 0 ; k < threestatetree.change_to_field.length ; k++)
			{
				var the_string = "";
				var li_elements = document.getElementById(threestatetree.treeid).getElementsByTagName("li");
				for (var i=0; i<li_elements.length; i++)
				{
					if((li_elements[i].childNodes[0].getAttribute('state') == 'checked' || li_elements[i].childNodes[0].getAttribute('state') == 'gray') && li_elements[i].getAttribute(threestatetree.change_to_field_attr[k])!=null)
					{
						if(the_string == "")
							the_string += li_elements[i].getAttribute(threestatetree.change_to_field_attr[k]);
						else
							the_string += threestatetree.change_to_field_sep[k] + li_elements[i].getAttribute(threestatetree.change_to_field_attr[k]);
					}
				}
				
				document.getElementsByName(threestatetree.change_to_field[k])[0].value = the_string;
			}
		};
		
		li_elements[i].insertBefore(newimg, li_elements[i].childNodes[0]);
	}
	threestatetree.check_for_gray();
};

threestatetree.check_for_gray=function()
{
	var ul_elements = document.getElementById(threestatetree.treeid).getElementsByTagName("ul");
	for (var i=0; i<ul_elements.length; i++)
	{
		var allchecked = true;
		var allunchecked = true;
		var img_tags = ul_elements[i].getElementsByTagName("img");
		for(var j=0 ; j<img_tags.length ; j++)
		{
			if(img_tags[j].getAttribute('name') == 'threestatecheckbox')
			{
				if(img_tags[j].getAttribute('state') == 'unchecked' || img_tags[j].getAttribute('state') == 'gray')
					allchecked = false;
				if(img_tags[j].getAttribute('state') == 'checked')
					allunchecked = false;
			}
		}
		//search img to change
		var img_change = ul_elements[i].parentNode.childNodes[0];
		if(allchecked && !allunchecked)
		{
			img_change.setAttribute('state', 'checked');
			img_change.setAttribute('src',threestatetree.img_checked);
		}
		if(!allchecked && allunchecked)
		{
			img_change.setAttribute('state', 'unchecked');
			img_change.setAttribute('src',threestatetree.img_unchecked);
		}
		if(!allchecked && !allunchecked)
		{
			img_change.setAttribute('state', 'gray');
			img_change.setAttribute('src',threestatetree.img_gray);
		}
			
	}
}