<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.54 2007/09/03 17:09:16 franciscom Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* rev:
*      20070901 - franciscom - refactoring
*                              using reports_cfg       
**/
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('users.inc.php');
testlinkInitPage($db);

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;


// $assigned_users = get_users_for_html_options($db, ALL_USERS_FILTER, !ADD_BLANK_OPTION);
$assigned_users = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);
$tc_status_code_label = get_status_for_reports_html_options();

$tp = new testplan($db);
$builds_to_query = -1;
$suitesSelected = 'all';
$re = new results($db, $tp);

$arrKeywords = $tp->get_keywords_map($tplan_id); 
$arrBuilds = $tp->get_builds($tplan_id); 
$arrTestsuites = $re->getTopLevelSuites();



$smarty = new TLSmarty();
$smarty->assign('tproject_name', $_SESSION['testprojectName'] );
$smarty->assign('tplan_name', $tplan_name );

$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrTestsuites', $arrTestsuites);
$smarty->assign('arrOwners', $assigned_users);

$smarty->assign('build_qty', count($arrBuilds)); 
$smarty->assign('keyword_qty', count($arrKeywords));
$smarty->assign('testsuite_qty', count($arrTestsuites));
$smarty->assign('user_qty', count($assigned_users));


$report_type = isset($_GET['report_type']) ? intval($_GET['report_type']) : null;
if (!isset($_GET['report_type']))
{
	tlog('$_GET["report_type"] is not defined');
	exit();
}
$build = isset($_GET['build']) ? intval($_GET['build']) : null;
$smarty->assign('build', $build);
$smarty->assign('report_type', $report_type);

// 20070902 - franciscom
$reports_cfg=config_get('reports_cfg');
$startDate=time()-($reports_cfg->start_date_offset);
$smarty->assign('selected_start_date', $startDate);
$smarty->assign('selected_start_time', '00:00');

$smarty->assign('status_code_label', $tc_status_code_label);

$smarty->display('resultsMoreBuilds_query_form.tpl');
?>

<?php
/*
  function: get_status_for_reports_html_options
            generates map useful for smarty html_options

  args :
  
  returns: map key -> status code, value -> localized status description

*/
function get_status_for_reports_html_options()
{
  $reports_cfg=config_get('reports_cfg');
  $map_verbose_status_label=$reports_cfg->tc_status;
  $map_verbose_status_code=config_get('tc_status');
  
	foreach($map_verbose_status_label as $verbose_status => $status_label)
	{
	   $code=$map_verbose_status_code[$verbose_status];
	   $html_options[$code]=lang_get($status_label); 
  }

	return $html_options;
}
?>