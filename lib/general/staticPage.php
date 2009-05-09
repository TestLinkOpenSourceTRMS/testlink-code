<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: staticPage.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/05/09 17:59:19 $  $Author: schlundus $
 * @author 	Martin Havlat
 *
 * manage launch of static pages (help, instructions).
 *
**/
require('../../config.inc.php');
require('../functions/common.php');
testlinkInitPage($db);

$args = init_args();

$gui = new stdClass();
$gui->pageTitle = '';
$gui->pageContent = '';
$gui->refreshTree = $args->refeshTree;

$pageKey = $args->key;
if ($pageKey == "") 
	exit ("Error: Invalid page parameter.");
	
// link appropriate definition file and default to en_GB if not present in the current language
$locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : $tlCfg->default_language;
if (file_exists('../../locale/'.$locale.'/texts.php'))
	include('../../locale/'.$locale.'/texts.php');
else
	include('../../locale/en_GB/texts.php');
	
if (isset($TLS_htmltext[$pageKey]))
{
	$gui->pageTitle = $TLS_htmltext_title[$pageKey];
	$gui->pageContent =  $TLS_htmltext[$pageKey];
}
else
{
	$gui->pageTitle = "";
	$gui->pageContent = "Please, ask administrator to update localization file (&lt;testlink_root&gt;/locale/$locale/texts.php)"
		." - missing key: ".$pageKey;
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display('staticPage.tpl');

function init_args()
{
	$iParams = array(
		"key" => array(tlInputParameter::STRING_N),
		"refreshTree" => array(tlInputParameter::INT_N),
	);
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
	return $args;
}
?>
