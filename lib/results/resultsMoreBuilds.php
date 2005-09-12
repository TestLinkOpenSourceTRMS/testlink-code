<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.11 2005/09/12 16:51:34 franciscom Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
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

$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$projectId = $_SESSION['testPlanId'];
$arrBuilds = getBuilds($projectId); 
$arrOwners = getProjectUsers();
$arrKeywords = selectKeywords($prodID);
$arrComponents = getArrayOfComponentNames($projectId);

$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('projectid', $projectId);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);

$smarty->display('resultsMoreBuilds_query_form.tpl');

?>