// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

layerLeft = new Array();
layerTop = new Array();

function setVisibility(layer, on)
{
	if (on) {
		if (DOM) {
			document.getElementById(layer).style.visibility = 'visible';
		} else if (NS4) {
			document.layers[layer].visibility = 'show';
		} else {
			document.all[layer].style.visibility = 'visible';
		}
	} else {
		if (DOM) {
			document.getElementById(layer).style.visibility = 'hidden';
		} else if (NS4) {
			document.layers[layer].visibility = 'hide';
		} else {
			document.all[layer].style.visibility = 'hidden';
		}
	}
}

function isVisible(layer)
{
	if (DOM) {
		return (document.getElementById(layer).style.visibility == 'visible');
	} else if (NS4) {
		return (document.layers[layer].visibility == 'show');
	} else {
		return (document.all[layer].style.visibility == 'visible');
	}
}

function setLeft(layer, x)
{
layerLeft[layer] = x;
	if (DOM && !Opera5) {
		document.getElementById(layer).style.left = x + 'px';
	} else if (Opera5) {
		document.getElementById(layer).style.left = x;
	} else if (NS4) {
		document.layers[layer].left = x;
	} else {
		document.all[layer].style.pixelLeft = x;
	}
}

function getOffsetLeft(layer)
{
	var value = 0;
	if (DOM) {	// Mozilla, Konqueror >= 2.2, Opera >= 5, IE
		object = document.getElementById(layer);
		value = object.offsetLeft;
//alert (object.tagName + ' --- ' + object.offsetLeft);
		while (object.tagName != 'BODY' && object.offsetParent) {
			object = object.offsetParent;
//alert (object.tagName + ' --- ' + object.offsetLeft);
			value += object.offsetLeft;
		}
	} else if (NS4) {
		value = document.layers[layer].pageX;
	} else {	// IE4 IS SIMPLY A BASTARD !!!
		if (document.all['IE4' + layer]) {
			layer = 'IE4' + layer;
		}
		object = document.all[layer];
		value = object.offsetLeft;
		while (object.tagName != 'BODY') {
			object = object.offsetParent;
			value += object.offsetLeft;
		}
	}
	return (value);
}

function setTop(layer, y)
{
layerTop[layer] = y;
	if (DOM && !Opera5) {
		document.getElementById(layer).style.top = y + 'px';
	} else if (Opera5) {
		document.getElementById(layer).style.top = y;
	} else if (NS4) {
		document.layers[layer].top = y;
	} else {
		document.all[layer].style.pixelTop = y;
	}
}

function getOffsetTop(layer)
{
// IE 5.5 and 6.0 behaviour with this function is really strange:
// in some cases, they return a really too large value...
// ... after all, IE is buggy, nothing new
	var value = 0;
	if (DOM) {
		object = document.getElementById(layer);
		value = object.offsetTop;
		while (object.tagName != 'BODY' && object.offsetParent) {
			object = object.offsetParent;
			value += object.offsetTop;
		}
	} else if (NS4) {
		value = document.layers[layer].pageY;
	} else {	// IE4 IS SIMPLY A BASTARD !!!
		if (document.all['IE4' + layer]) {
			layer = 'IE4' + layer;
		}
		object = document.all[layer];
		value = object.offsetTop;
		while (object.tagName != 'BODY') {
			object = object.offsetParent;
			value += object.offsetTop;
		}
	}
	return (value);
}

function setWidth(layer, w)
{
	if (DOM) {
		document.getElementById(layer).style.width = w;
	} else if (NS4) {
//		document.layers[layer].width = w;
	} else {
		document.all[layer].style.pixelWidth = w;
	}
}

function getOffsetWidth(layer)
{
	var value = 0;
	if (DOM && !Opera56) {
		value = document.getElementById(layer).offsetWidth;
	} else if (NS4) {
		value = document.layers[layer].document.width;
	} else if (Opera56) {
		value = document.getElementById(layer).style.pixelWidth;
	} else {	// IE4 IS SIMPLY A BASTARD !!!
		if (document.all['IE4' + layer]) {
			layer = 'IE4' + layer;
		}
		value = document.all[layer].offsetWidth;
	}
	return (value);
}

function setHeight(layer, h)	// unused, not tested
{
	if (DOM) {
		document.getElementById(layer).style.height = h;
	} else if (NS4) {
//		document.layers[layer].height = h;
	} else {
		document.all[layer].style.pixelHeight = h;
	}
}

function getOffsetHeight(layer)
{
	var value = 0;
	if (DOM && !Opera56) {
		value = document.getElementById(layer).offsetHeight;
	} else if (NS4) {
		value = document.layers[layer].document.height;
	} else if (Opera56) {
		value = document.getElementById(layer).style.pixelHeight;
	} else {	// IE4 IS SIMPLY A BASTARD !!!
		if (document.all['IE4' + layer]) {
			layer = 'IE4' + layer;
		}
		value = document.all[layer].offsetHeight;
	}
	return (value);
}

function getWindowWidth()
{
	var value = 0;
	if ((DOM && !IE) || NS4 || Konqueror || Opera) {
		value = window.innerWidth;
//	} else if (NS4) {
//		value = document.width;
	} else {	// IE
		if (document.documentElement && document.documentElement.clientWidth) {
			value = document.documentElement.clientWidth;
		} else if (document.body) {
			value = document.body.clientWidth;
		}
	}
	if (isNaN(value)) {
		value = window.innerWidth;
	}
	return (value);
}

function getWindowXOffset()
{
	var value = 0;
	if ((DOM && !IE) || NS4 || Konqueror || Opera) {
		value = window.pageXOffset;
	} else {	// IE
		if (document.documentElement && document.documentElement.scrollLeft) {
			value = document.documentElement.scrollLeft;
		} else if (document.body) {
			value = document.body.scrollLeft;
		}
	}
	return (value);
}

function getWindowHeight()
{
	var value = 0;
	if ((DOM && !IE) || NS4 || Konqueror || Opera) {
		value = window.innerHeight;
	} else {	// IE
		if (document.documentElement && document.documentElement.clientHeight) {
			value = document.documentElement.clientHeight;
		} else if (document.body) {
			value = document.body.clientHeight;
		}
	}
	if (isNaN(value)) {
		value = window.innerHeight;
	}
	return (value);
}

function getWindowYOffset()
{
	var value = 0;
	if ((DOM && !IE) || NS4 || Konqueror || Opera) {
		value = window.pageYOffset;
	} else {	// IE
		if (document.documentElement && document.documentElement.scrollTop) {
			value = document.documentElement.scrollTop;
		} else if (document.body) {
			value = document.body.scrollTop;
		}
	}
	return (value);
}

