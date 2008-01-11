<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: staticPage.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/01/11 00:54:33 $  $Author: havlat $
 * @author 	Martin Havlat
 *
 * manage launch of static pages (help, instructions).
 *
**/

require('../../config.inc.php');

if (isset($_REQUEST['key'])) {
	$pageKey = $_REQUEST['key'];
} else {
	exit ("Error: Invalid page parameter.");
}

// need session to get active locale
session_start();
// link appropriate definition file
$locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : $g_default_language;
require('../../locale/'.$locale.'/texts.php');

if (isset($TLS_htmltext[$pageKey])) {
	$pageTitle = $TLS_htmltext_title[$pageKey];
	$pageContent =  $TLS_htmltext[$pageKey];
}
else
{
	$pageTitle = "";
	$pageContent = "Please, ask administrator to update localization file (&lt;testlink_root&gt;/locale/$locale/texts.php)"
		." - missing key: ".$pageKey;
}

$smarty = new TLSmarty();
$smarty->assign('title', $pageTitle);
$smarty->assign('pageContent', $pageContent); 
$smarty->display('staticPage.tpl');

?>
