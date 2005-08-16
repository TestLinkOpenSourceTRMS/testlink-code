<?php

function inAgent($string)
{
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
	} else {
		$http_user_agent = '';
	}
	return !(strpos($http_user_agent, $string) === false);
}

if (inAgent('Gecko')) {
	$browser = 'Mozilla';
} elseif (
	inAgent('Lynx') || inAgent('lynx')
	|| inAgent('Links') || inAgent('links')
	|| inAgent('w3m')
	) {
	$browser = 'TEXT';
// I detect Konqueror and Opera before than the others,
// as they often qualify themselves as Mozilla/Netscape/MSIE
} elseif (inAgent('Konqueror')) {
	if (inAgent('Konqueror 1') || inAgent('Konqueror/1')) {
		$browser = 'Konqueror1';
		// On KDE 1.1.2, kfm qualifies itself as "Konqueror/1.1.2"
		// (tested on Red Hat Linux 6.2)
	} elseif (inAgent('Konqueror 2.0') || inAgent('Konqueror/2.0')
		  || inAgent('Konqueror 2;') || inAgent('Konqueror/2;')
		  || inAgent('Konqueror 2)') || inAgent('Konqueror/2)')
	) {
		$browser = 'Konqueror20';
	} elseif (inAgent('Konqueror 2.1') || inAgent('Konqueror/2.1')) {
		$browser = 'Konqueror21';
	} else {
		$browser = 'Konqueror';
	}
} elseif (inAgent('Opera')) {
	if (inAgent('Opera 4') || inAgent('Opera/4')) {
		$browser = 'Opera4';
		// I hope that Opera 4 users can be satisfied by the Plain menu version;
		// sorry, but I have never used Opera 4 and I do not have a copy of it
		// to perform tests.  If you are using Opera < 4, it's your problem :-P
	} elseif ((inAgent('Opera 6') || inAgent('Opera/6')) && inAgent('Linux')) {
		$browser = 'Opera6forLinux';
	} elseif (
		inAgent('Opera 5') || inAgent('Opera/5')
		|| inAgent('Opera 6') || inAgent('Opera/6')
	) {
		$browser = 'Opera56';
	} else {
		$browser = 'Opera';
	}
} elseif (inAgent('Safari')) {
	$browser = 'Safari';
} elseif (inAgent('MSIE 4') || inAgent('MSIE/4')) {
	$browser = 'IE4';
} elseif (inAgent('MSIE')) {
	$browser = 'IE5';
	// msie != 4 is handled like msie 5+; if you are using msie 3-, it's your problem :-P
} elseif (inAgent('Mozilla 4') || inAgent('Mozilla/4')) {
	$browser = 'NS4';
} else {
	$browser = 'Unknown';
}

if (
	$browser == 'TEXT'
	|| $browser == 'Konqueror1' || $browser == 'Konqueror20' || $browser == 'Konqueror21'
	// IMO, on Konqueror 2.1, the Plain version is more usable than the "OLD" one
	|| $browser == 'Opera4'
) {
	$menuType = 'PLAIN';
} elseif ($browser == 'NS4' || $browser == 'Opera56' || $browser == 'IE4') {
	$menuType = 'OLD';
} else {
	$menuType = 'DOM';
}

?>
