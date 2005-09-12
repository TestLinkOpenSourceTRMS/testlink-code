<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.9 2005/09/12 00:56:26 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
// allow us to retreive array of users 
require_once('plan.core.inc.php');
require_once('resultsMoreBuilds.inc.php');
require_once('../keywords/keywords.inc.php');
testlinkInitPage();

$projectId = $_SESSION['testPlanId'];
$arrBuilds = getBuilds($projectId); 
$arrOwners = getProjectUsers();
$arrKeywords = selectKeywords();
$arrComponents = getArrayOfComponentNames($projectId);

$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('projectid', $projectId);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);

// for excel send header
if ($xls) {
  sendXlsHeader();
  $smarty->assign('printDate', date('"F j, Y, H:m"'));
  $smarty->assign('user', $_SESSION['user']);
 }

// this contains example of how this excel data gets used
// $smarty->display('resultsTC.tpl');

$smarty->display('resultsMoreBuilds_query_form.tpl');

?>