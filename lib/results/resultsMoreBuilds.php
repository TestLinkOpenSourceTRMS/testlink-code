<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.14 2005/10/19 05:47:45 kevinlevy Exp $ 
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
$arrBuilds = getBuilds($projectId, " ORDER BY build.name "); 
$arrOwners = getTestPlanUsers();
$arrKeywords = selectKeywords($prodID);
$arrComponents = getArrayOfComponentNames($projectId);

$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('projectid', $projectId);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);

// kl - 10182005 debug
//print "resultMoreBuilds.php array of components = ";
//print_r($arrComponents);

$smarty->display('resultsMoreBuilds_query_form.tpl');

?>