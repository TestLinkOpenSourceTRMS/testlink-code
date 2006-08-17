// TestLink Open Source Project - http://testlink.sourceforge.net/ 
// $Id: expandAndCollapseFunctions.js,v 1.8 2006/08/17 19:29:59 schlundus Exp $ 
//
//
function plusMinus_onClick(elem)
{
var elem = elem.firstChild;
	toggleSection(elem);
}

function toggleSection(elem)
{
	var d = "";
	if (!elem)
		return;
	if (elem.className == '' || elem.className == 'plus')
	{
		elem.src = "icons/minus.gif";
		elem.className = "minus";
	}
	else
	{
		elem.src = "icons/plus.gif";
		elem.className = "plus";
		d = "none";
	}
	var iter = elem.parentNode;
	while (iter && iter.tagName != 'DIV')
		iter = iter.nextSibling;	
	if (!iter)
		return;
	iter.style.display = d;
}
function showOrCollapseAll()
{
	var imgs = document.getElementsByTagName('img');
	if (!imgs || !imgs.length)
		return;
	var i = 0;
	var len = imgs.length;
	for(i = 0; i < len;i++)
	{
		var img = imgs[i];
		var imgClassName = img.className;
		if (imgClassName == 'plus' || imgClassName == 'minus')
			toggleSection(img);
	}
}

function progress()
{
	var o  = document.getElementById('progress');
	if (o)
		o.innerHTML += ".";
	g_pCount++;
	if (g_pCount % 80 == 0)
		o.innerHTML += "<br>";
	clearTimeout(g_progress);
	g_progress = setTimeout("progress()",50);
	
}

function onLoad()
{
	clearTimeout(g_progress);
	var o = document.getElementById('teaser');
	if (o)
		o.style.display = "none";
	o = document.getElementById('content');
	if (o)
		o.style.display = "block";
}

function viewElement(obj,show_me)
{
	if (obj)
	{
		obj.style.display = (show_me ? '' : 'none');
	}	
}


// 20060808 - franciscom - a variation of viewElement
function show_hide(elem_oid,hidden_oid,show)
{
	var obj = document.getElementById(elem_oid);
	var hidden_in  = document.getElementById(hidden_oid);

	if (obj)
	{
		obj.style.display=(show ? '' : 'none');
		hidden_in.value=(show ? 1 : 0);
	}	
}
