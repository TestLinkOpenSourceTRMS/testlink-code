// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

function setLMCookie(name, value)
{
	document.cookie = name + '=' + value + ';path=/';
}

function getLMCookie(name)
{
	foobar = document.cookie.split(name + '=');
	if (foobar.length < 2) {
		return null;
	}
	tempString = foobar[1];
	if (tempString.indexOf(';') == -1) {
		return tempString;
	}
	yafoobar = tempString.split(';');
	return yafoobar[0];
}

function parseExpandString()
{
	expandString = getLMCookie('phplm_expand');
	phplm_expand = new Array();
	if (expandString) {
		expanded = expandString.split('|');
		for (i=0; i<expanded.length-1; i++) {
			phplm_expand[expanded[i]] = 1;
		}
	}
}

function parseCollapseString()
{
	collapseString = getLMCookie('phplm_collapse');
	phplm_collapse = new Array();
	if (collapseString) {
		collapsed = collapseString.split('|');
		for (i=0; i<collapsed.length-1; i++) {
			phplm_collapse[collapsed[i]] = 1;
		}
	}
}

parseExpandString();
parseCollapseString();

function saveExpandString()
{
	expandString = '';
	for (i=0; i<phplm_expand.length; i++) {
		if (phplm_expand[i] == 1) {
			expandString += i + '|';
		}
	}
	setLMCookie('phplm_expand', expandString);
}

function saveCollapseString()
{
	collapseString = '';
	for (i=0; i<phplm_collapse.length; i++) {
		if (phplm_collapse[i] == 1) {
			collapseString += i + '|';
		}
	}
	setLMCookie('phplm_collapse', collapseString);
}

