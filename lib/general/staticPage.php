<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: staticPage.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/03/24 19:33:28 $  $Author: havlat $
 * @author 	Martin Havlat
 *
 * manage launch of static pages (help, instructions).
 *
**/
require('../../config.inc.php');
require('../functions/common.php');
testlinkInitPage($db);

if (isset($_REQUEST['key'])) {
	$pageKey = $_REQUEST['key'];
} else {
	exit ("Error: Invalid page parameter.");
}
// link appropriate definition file and default to en_GB if not present in the current language
$locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : $tlCfg->default_language;
if (file_exists('../../locale/'.$locale.'/texts.php'))
	include('../../locale/'.$locale.'/texts.php');
else
	include('../../locale/en_GB/texts.php');
	
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
