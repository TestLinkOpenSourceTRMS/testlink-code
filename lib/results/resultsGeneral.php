<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:58 $ by $Author: franciscom $
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * This page show Test Results over all Builds.
 *
 * @author 20050807 - fm
 * refactoring:  changes in getTestSuiteReport() call
 *
 * 
 */
require('../../config.inc.php');
require_once('common.php');
require_once('results.inc.php');

testlinkInitPage();

$arrDataPriority = getPriorityReport();

// 20050807 - fm 
$arrDataSuite = getTestSuiteReport($_SESSION['testPlanId']);

$arrDataOwner = getOwnerReport();
$arrDataKeys = getKeywordsReport();

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsGeneral.tpl');
?>