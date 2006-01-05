<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2006/01/05 07:30:34 $ by $Author: franciscom $
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
testlinkInitPage($db);


$tpID = $_SESSION['testPlanId']; 
$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataSuite = getTestSuiteReport($db,$tpID);
$arrDataKeys = getKeywordsReport($db,$tpID);
$arrDataOwner = getOwnerReport($db,$tpID);

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsGeneral.tpl');
?>