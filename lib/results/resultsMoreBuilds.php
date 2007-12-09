<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.58 2007/12/09 02:15:19 havlat Exp $ 
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

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);

$template_dir='results/';

$tplan_id=$_REQUEST['tplan_id'];
$tproject_id=$_SESSION['testprojectID'];

$tplan_info = $tplan_mgr->get_by_id($tplan_id);
$tproject_info = $tproject_mgr->get_by_id($tproject_id);

$tplan_name = $tplan_info['name'];
$tproject_name = $tproject_info['name'];


$assigned_users = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);
$tc_status_code_label = get_status_for_reports_html_options();

$re = new results($db, $tplan_mgr,$tproject_info,$tplan_info);

$arrKeywords = $tplan_mgr->get_keywords_map($tplan_id); 
$arrBuilds = $tplan_mgr->get_builds($tplan_id, 1); //MHT: active builds only
$arrTestsuites = $re->getTopLevelSuites();



$smarty = new TLSmarty();

$smarty->assign('tproject_name', $tproject_name);
$smarty->assign('tplan_name', $tplan_name);
$smarty->assign('tplan_id', $tplan_id );

$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrTestsuites', $arrTestsuites);
$smarty->assign('arrOwners', $assigned_users);

$smarty->assign('build_qty', count($arrBuilds)); 
$smarty->assign('keyword_qty', count($arrKeywords));
$smarty->assign('testsuite_qty', count($arrTestsuites));
$smarty->assign('user_qty', count($assigned_users));


$format = isset($_GET['format']) ? intval($_GET['format']) : null;
if (!isset($_GET['format']))
{
	tlog('$_GET["format"] is not defined', 'ERROR');
	exit();
}

$build = isset($_GET['build']) ? intval($_GET['build']) : null;
$smarty->assign('build', $build);
$smarty->assign('report_type', $format);

// 20070902 - franciscom
$reports_cfg=config_get('reports_cfg');
$startDate=time()-($reports_cfg->start_date_offset);
$smarty->assign('selected_start_date', $startDate);
$smarty->assign('selected_start_time', '00:00');

$smarty->assign('status_code_label', $tc_status_code_label);

$smarty->display($template_dir .'resultsMoreBuilds_query_form.tpl');
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