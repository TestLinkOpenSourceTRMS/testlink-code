<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/
* $Id: resultsMoreBuilds.php,v 1.62 2008/05/17 17:41:09 franciscom Exp $
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
*
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* rev:
*      20080517 - franciscom - refactoring
*      20070901 - franciscom - refactoring
*                              using reports_cfg
**/
require('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('users.inc.php');
testlinkInitPage($db);


$template_dir='results/';
$gui=initializeGui($db);
$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($template_dir .'resultsMoreBuilds_query_form.tpl');


/*
  function: get_status_for_reports_html_options
            generates map useful for smarty html_options

  args :

  returns: map key -> status code, value -> localized status description

*/
function get_status_for_reports_html_options()
{
	$reports_cfg = config_get('reportsCfg');
	$results = config_get('results');
	
	foreach($reports_cfg->exec_status as $verbose_status => $status_label)
	{
		$code = $results['status_code'][$verbose_status];
		$html_options[$code] = lang_get($status_label);
	}
	
	return $html_options;
}


/*
  function: initializeGui

  args :

  returns: 

*/
function initializeGui(&$dbHandler)
{
    $gui=new stdClass();  
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    

    $gui->tplan_id=$_REQUEST['tplan_id'];
    $gui->tproject_id=$_SESSION['testprojectID'];
    
    
    $tplan_info = $tplan_mgr->get_by_id($gui->tplan_id);
    $gui->tplan_name = $tplan_info['name'];

    $tproject_info = $tproject_mgr->get_by_id($gui->tproject_id);
    $gui->tproject_name = $tproject_info['name'];

    $re = new results($dbHandler, $tplan_mgr,$tproject_info,$tplan_info);

    $gui->assigned_users = new stdClass();
    $gui->keywords = new stdClass();
    $gui->builds = new stdClass();
    $gui->testsuites = new stdClass();

    $gui->assigned_users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER, ADD_BLANK_OPTION);
    $gui->assigned_users->qty = count($gui->assigned_users->items);
    
    $gui->keywords->items = $tplan_mgr->get_keywords_map($gui->tplan_id);
    $gui->builds->items = $tplan_mgr->get_builds($gui->tplan_id,testplan::ACTIVE_BUILDS);
    $gui->testsuites->items = $re->getTopLevelSuites();

    $gui->keywords->qty = count($gui->keywords->items);
    $gui->builds->qty = count($gui->builds->items);
    $gui->testsuites->qty = count($gui->testsuites->items);

    $gui->status_code_label = get_status_for_reports_html_options();

    $gui->report_type = isset($_REQUEST['format']) ? intval($_REQUEST['format']) : null;
    $gui->build = isset($_REQUEST['build']) ? intval($_REQUEST['build']) : null;



    $reports_cfg = config_get('reportsCfg');
    $startDate = time() - ($reports_cfg->start_date_offset);
    $gui->selected_start_date=$startDate;
    $gui->selected_start_time='00:00';

    $gui->selected_end_date=null;
    $gui->selected_end_time=null;

    return $gui;
}
?>