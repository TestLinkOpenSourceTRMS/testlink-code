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
		elem.src = "http://qa/testlink/icons/minus.gif";
		elem.className = "minus";
	}
	else
	{
		elem.src = "http://qa/testlink/icons/plus.gif";
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
	for(i = 0; i < imgs.length;i++)
	{
		if (imgs[i].className == 'plus' || imgs[i].className == 'minus')
			toggleSection(imgs[i]);
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
