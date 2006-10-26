<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: resultsGeneral.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2006/10/26 06:37:37 $ by $Author: kevinlevy $
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

print "KL - 20061025 - work in progress <BR>";
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('../functions/results.class.php');
testlinkInitPage($db);


$tpID = $_SESSION['testPlanId']; 


//$arrDataPriority = getPriorityReport($db,$tpID);
$arrDataPriority = null;

//$arrDataSuite = getTestSuiteReport($db,$tpID);
$arrDataSuite = null;

//$arrDataKeys = getKeywordsReport($db,$tpID);
$arrDataKeys = null;

//$arrDataOwner = getOwnerReport($db,$tpID);
$arrDataOwner = null;

$smarty = new TLSmarty;
$smarty->assign('tpName', $_SESSION['testPlanName']);
$smarty->assign('arrDataPriority', $arrDataPriority);
$smarty->assign('arrDataSuite', $arrDataSuite);
$smarty->assign('arrDataOwner', $arrDataOwner);
$smarty->assign('arrDataKeys', $arrDataKeys);
$smarty->display('resultsGeneral.tpl');

?>