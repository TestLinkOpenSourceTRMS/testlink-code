<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/09/06 06:42:04 $ by $Author: franciscom $
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * This page show Test Results over all Builds.
 *
 * @author 20050905 - fm - reduce global coupling
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

$tpID = $_SESSION['testPlanId']; 
$arrDataSuite = getTestSuiteReport($tpID);
$arrDataKeys = getKeywordsReport($tpID);
$arrDataOwner = getOwnerReport($tpID);

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsGeneral.tpl');
?>