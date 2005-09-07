<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.8 2005/09/07 20:19:25 schlundus Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* 20050903 - fm - refactoring 
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('plan.core.inc.php');
require_once('../keywords/keywords.inc.php');
testlinkInitPage();

// 20050905 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$arrBuilds = getBuilds($_SESSION['testPlanId']); // get Builds
$arrOwners = getProjectUsers();
$arrKeywords = selectKeywords($prodID);


$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('projectid', $_SESSION['testPlanId']);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);

// for excel send header
if ($xls)
{
	sendXlsHeader();
	$smarty->assign('printDate', strftime($g_date_format, time()) );
	$smarty->assign('user', $_SESSION['user']);
}

$smarty->display('resultsMoreBuilds_query_form.tpl');
?>