// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

useTimeouts = 1;
timeoutLength = 1000;	// time in ms; not significant if useTimeouts = 0;
shutdownOnClick = 0;

loaded = 0;
layersMoved = 0;
layerPoppedUp = '';

timeoutFlag = 0;
if (Opera56 || IE4) {
	useTimeouts = 0;
}
if (NS4 || Opera56 || IE4) {
	shutdownOnClick = 1;
}

currentY = 0;
function grabMouse(e)	// for NS4
{
	currentY = e.pageY;
}
if (NS4) {
	document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE);
	document.onmousemove = grabMouse;
}

function seeThroughElements(show)
{
	if (show) {
		foobar = 'visible';
	} else {
		foobar = 'hidden';
	}
	for (i=0; i<toBeHidden.length; i++) {
		toBeHidden[i].style.visibility = foobar;
	}
}

function shutdown()
{
	for (i=0; i<numl; i++) {
		LMPopUpL(listl[i], false);
	}
	layerPoppedUp = '';
	if (Konqueror || IE5) {
		seeThroughElements(true);
	}
}
if (shutdownOnClick) {
	if (NS4) {
		document.onmousedown = shutdown;
	} else {
		document.onclick = shutdown;
	}
}

function setLMTO()
{
	if (useTimeouts) {
		timeoutFlag = setTimeout('shutdown()', timeoutLength);
	}
}

function clearLMTO()
{
	if (useTimeouts) {
		clearTimeout(timeoutFlag);
	}
}

function moveLayerX(menuName)
{
	if (!loaded || (isVisible(menuName) && menuName != layerPoppedUp)) {
		return;
	}
	if (father[menuName] != '') {
		if (!Opera5 && !IE4) {
			width0 = lwidth[father[menuName]];
			width1 = lwidth[menuName];
		} else if (Opera5) {
			// Opera 5 stupidly and exaggeratedly overestimates layers widths
			// hence we consider a default value equal to $abscissaStep
			width0 = abscissaStep;
			width1 = abscissaStep;
		} else if (IE4) {
			width0 = getOffsetWidth(father[menuName]);
			width1 = getOffsetWidth(menuName);
		}
		onLeft = getOffsetLeft(father[menuName]) - width1 + menuLeftShift;
		onRight = getOffsetLeft(father[menuName]) + width0 - menuRightShift;
		windowWidth = getWindowWidth();
		windowXOffset = getWindowXOffset();
//		if (NS4 && !DOM) {
//			windowXOffset = 0;
//		}
		if (onLeft < windowXOffset && onRight + width1 > windowWidth + windowXOffset) {
			if (onRight + width1 - windowWidth - windowXOffset > windowXOffset - onLeft) {
				onLeft = windowXOffset;
			} else {
				onRight = windowWidth + windowXOffset - width1;
			}
		}
		if (back[father[menuName]]) {
			if (onLeft < windowXOffset) {
				back[menuName] = 0;
			} else {
				back[menuName] = 1;
			}
		} else {
//alert(onRight + ' - ' + width1 + ' - ' +  windowWidth + ' - ' + windowXOffset);
			if (onRight + width1 > windowWidth + windowXOffset) {
				back[menuName] = 1;
			} else {
				back[menuName] = 0;
			}
		}
		if (back[menuName]) {
			setLeft(menuName, onLeft);
		} else {
			setLeft(menuName, onRight);
		}
	}
	moveLayerY(menuName);	// workaround needed for Mozilla < 1.4 for MS Windows
}

function moveLayerY(menuName)
{
	if (!loaded || (isVisible(menuName) && menuName != layerPoppedUp)) {
		return;
	}
	if (!layersMoved) {
		moveLayers();
		layersMoved = 1;
	}
	if (!NS4) {
		newY = getOffsetTop('ref' + menuName);
	} else {
		newY = currentY;
	}
	newY += menuTopShift;
	layerHeight = getOffsetHeight(menuName);
	windowHeight = getWindowHeight();
	windowYOffset = getWindowYOffset();
	if (newY + layerHeight > windowHeight + windowYOffset) {
		if (layerHeight > windowHeight) {
			newY = windowYOffset;
		} else {
			newY = windowHeight + windowYOffset - layerHeight;
		}
	}
	if (Math.abs(getOffsetTop(menuName) - newY) > thresholdY) {
		setTop(menuName, newY);
	}
}

function moveLayerX1(menuName, father)
{
	if (!lwidthDetected) {
		return;
	}
	if (!Opera5 && !IE4) {
		width1 = lwidth[menuName];
	} else if (Opera5) {
		// Opera 5 stupidly and exaggeratedly overestimates layers widths
		// hence we consider a default value equal to $abscissaStep
		width1 = abscissaStep;
	}
	foobar = getOffsetLeft(father + menuName);
if (!IE4) {
	windowWidth = getWindowWidth();
	windowXOffset = getWindowXOffset();
	if (foobar + width1 > windowWidth + windowXOffset) {
		foobar = windowWidth + windowXOffset - width1;
	}
	if (foobar < windowXOffset) {
		foobar = windowXOffset;
	}
}
	setLeft(menuName, foobar);
}

function layersOverlap(layer, i)
{
	if (Konqueror22) {
		return true;
	}

//	xa1 = getOffsetLeft(layer);
//setLeft(layer, xa1);
	xa1 = layerLeft[layer];
	xa2 = xa1 + getOffsetWidth(layer);
//setWidth(layer, xa2-xa1);
//	ya1 = getOffsetTop(layer);
//setTop(layer, ya1);
	ya1 = layerTop[layer];
	ya2 = ya1 + getOffsetHeight(layer);
//setHeight(layer, ya2-ya1);
//alert(':' + xa1 + ':' + xa2 + ':' + ya1 + ':' + ya2 + ':');

	xb1 = toBeHiddenLeft[i];
	xb2 = xb1 + toBeHidden[i].offsetWidth;
	yb1 = toBeHiddenTop[i];
	yb2 = yb1 + toBeHidden[i].offsetHeight;
//alert(':' + xb1 + ':' + xb2 + ':' + yb1 + ':' + yb2 + ':');

	if(xb1>xa1) xa1=xb1; if(xb2<xa2) xa2=xb2;
	if(yb1>ya1) ya1=yb1; if(yb2<ya2) ya2=yb2;

	return (xa2>xa1 && ya2>ya1);
}

function seeThroughWorkaround(menuName, on)
{
	for (i=0; i<toBeHidden.length; i++) {
		if (layersOverlap(menuName, i)) {
			if (on) {
				toBeHidden[i].style.visibility = 'hidden';
			} else {
				toBeHidden[i].style.visibility = 'visible';
			}
		}
	}
}

function LMPopUpL(menuName, on)
{
	if (!loaded) {
		return;
	}
	if (!layersMoved) {
		moveLayers();
		layersMoved = 1;
	}
	setVisibility(menuName, on);
}

function LMPopUp(menuName, isCurrent)
{
	if (!loaded || menuName == layerPoppedUp || (isVisible(menuName) && !isCurrent)) {
		return;
	}
	if (menuName == father[layerPoppedUp]) {
		LMPopUpL(layerPoppedUp, false);
//		seeThroughWorkaround(menuName, false);
	} else if (father[menuName] == layerPoppedUp) {
		LMPopUpL(menuName, true);
		seeThroughWorkaround(menuName, true);
	} else {
		shutdown();
		foobar = menuName;
		do {
			LMPopUpL(foobar, true);
			seeThroughWorkaround(foobar, true);
			foobar = father[foobar];
		} while (foobar != '')
	}
/*
	if (layerPoppedUp == '') {
		seeThroughElements(false);
	}
*/
	layerPoppedUp = menuName;
}

function resizeHandler()
{
	if (NS4) {
		window.location.reload();
	}
	shutdown();
	for (i=0; i<numl; i++) {
		setLeft(listl[i], 0);
		setTop(listl[i], 0);
	}
	if (toBeHidden != null && toBeHidden.length > 0) {
		seeThroughCoordinatesDetection();
	}
//	moveLayers();
	layersMoved = 0;
}
window.onresize = resizeHandler;

function yaresizeHandler()
{
	if (window.innerWidth != origWidth || window.innerHeight != origHeight) {
		if (Konqueror22 || Opera5) {
			window.location.reload();	// Opera 5 often fails this
		}
		origWidth  = window.innerWidth;
		origHeight = window.innerHeight;
		resizeHandler();
	}
	setTimeout('yaresizeHandler()', 500);
}
function loadHandler()
{
	if (Konqueror22 || Opera56) {
		origWidth  = window.innerWidth;
		origHeight = window.innerHeight;
		yaresizeHandler();
	}
}
window.onload = loadHandler;

function fixieflm(menuName)
{
	if (DOM) {
		setWidth(menuName, '100%');
	} else {	// IE4 IS SIMPLY A BASTARD !!!
		document.write('</div>');
		document.write('<div id="IE4' + menuName + '" style="position: relative; width: 100%; visibility: visible;">');
	}
}

