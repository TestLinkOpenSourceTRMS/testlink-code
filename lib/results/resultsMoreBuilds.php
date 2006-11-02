<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.45 2006/11/02 21:47:12 schlundus Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
**/
require('../../config.inc.php');
require_once('common.php');
require_once('../functions/results.class.php');
// used to retrieve users 
require_once('../functions/users.inc.php');
testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$tplanName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;

define('ALL_USERS_FILTER', null);
define('ADD_BLANK_OPTION', false);
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);

$tp = new testplan($db);
//$tree = new tree($db);
$builds_to_query = -1;
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);

$arrKeywords = $tp->get_keywords_map($tpID); 
$arrBuilds = $tp->get_builds($tpID); 
$arrComponents = $re->getTopLevelSuites();
$mapOfSuiteSummary = $re->getAggregateMap();

while ($mapOfSuiteSummary && ($key2 = key($mapOfSuiteSummary)))
{
	// print "key2 = $key2 <BR>";
	next($mapOfSuiteSummary);
}

$smarty = new TLSmarty();
$smarty->assign('testPlanName',$tplanName);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);
$smarty->assign('arrOwners', $arrOwners);
$smarty->display('resultsMoreBuilds_query_form.tpl');
?>