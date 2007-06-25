<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.50 2007/06/25 06:23:45 franciscom Exp $ 
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
require_once('results.class.php');
// used to retrieve users 
require_once('users.inc.php');
testlinkInitPage($db);

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;


$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, !ADD_BLANK_OPTION);
$tp = new testplan($db);
//$tree = new tree($db);
$builds_to_query = -1;
$suitesSelected = 'all';
//$re = new results($db, $tp, $suitesSelected, $builds_to_query);
$re = new results($db, $tp);

$arrKeywords = $tp->get_keywords_map($tplan_id); 
$arrBuilds = $tp->get_builds($tplan_id); 
$arrComponents = $re->getTopLevelSuites();

$smarty = new TLSmarty();
$smarty->assign('tproject_name', $_SESSION['testprojectName'] );
$smarty->assign('tplan_name', $tplan_name );

$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('show_untested_code', $g_untested_reports);

$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}
$smarty->assign('report_type', $report_type);
$smarty->display('resultsMoreBuilds_query_form.tpl');
?>