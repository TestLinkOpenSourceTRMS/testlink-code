<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBugs.php,v 1.3 2005/09/06 20:19:40 schlundus Exp $ 
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* 
* This page show Bug Report.
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('results.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// get Priority data
$arrData = getBugsReport($_SESSION['testPlanId']);

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrData', $arrData);
$smarty->display('resultsBugs.tpl');
?>