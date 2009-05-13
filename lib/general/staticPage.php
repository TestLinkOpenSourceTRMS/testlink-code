<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: staticPage.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2009/05/13 16:31:39 $  $Author: schlundus $
 * @author 	Martin Havlat
 *
 * manage launch of static pages (help, instructions).
 *
 * 20090512 - franciscom - fixed typo errors and refactoring
 *
**/
require('../../config.inc.php');
require('../functions/common.php');
testlinkInitPage($db);

$args = init_args();

$gui = new stdClass();
$gui->pageTitle = '';
$gui->pageContent = '';
$gui->refreshTree = $args->refreshTree;

$pageKey = $args->key;
if ($pageKey == "") 
{
	exit ("Error: Invalid page parameter.");
}
	
// link appropriate definition file and default to en_GB if not present in the current language
$locale = isset($_SESSION['locale']) ? $_SESSION['locale'] : $tlCfg->default_language;
$language = (file_exists('../../locale/' . $locale . '/texts.php')) ? $locale : 'en_GB';
include('../../locale/'. $language .'/texts.php');

if (isset($TLS_htmltext[$pageKey]))
{
	$gui->pageTitle = $TLS_htmltext_title[$pageKey];
	$gui->pageContent =  $TLS_htmltext[$pageKey];
}
else
{
	$gui->pageContent = "Please, ask administrator to update localization file" .
	                    "(&lt;testlink_root&gt;/locale/$locale/texts.php)" . 
	                    " - missing key: " . $pageKey;
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display('staticPage.tpl');


/**
 * init_args()
 *
 */
function init_args()
{
	$iParams = array("key" => array(tlInputParameter::STRING_N),
		             "refreshTree" => array(tlInputParameter::INT_N));
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	return $args;
}
?>
