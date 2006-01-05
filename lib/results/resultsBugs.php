<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsBugs.php,v 1.5 2006/01/05 07:30:34 franciscom Exp $ 
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
testlinkInitPage($db);


$arrData = getBugsReport($db,$_SESSION['testPlanId']);

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrData', $arrData);
$smarty->display('resultsBugs.tpl');
?>