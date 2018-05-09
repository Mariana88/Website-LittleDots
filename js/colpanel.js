function  changeplusminus(thepanel, theimage)
{
	//we have to think in reverse: close means that ist gonna open
	var xx = thepanel.isOpen();
	if(xx)
		theimage.src='/css/back/icon/plus.gif';
	else
		theimage.src='/css/back/icon/min.gif';
}