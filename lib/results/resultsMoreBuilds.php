<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.4 2005/09/03 00:58:02 kevinlevy Exp $ 
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
require_once('../keywords/keywords.inc.php');
testlinkInitPage();

$arrBuilds = getBuilds($_SESSION['testPlanId']); // get Builds
$arrOwners = getProjectUsers();
$arrKeywords = selectKeywords();
$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('projectid', $_SESSION['project']);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);


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