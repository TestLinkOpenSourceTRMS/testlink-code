<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/
* $Id: resultsMoreBuildsGUI.php,v 1.7 2010/11/01 17:15:37 franciscom Exp $
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
*
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* rev:
*   20101026 - asimon - BUGID 3930
*   20101022 - asimon - BUGID 3716: replaced old separated inputs for day/month/year by ext js calendar
*	20090912 - franciscom - BUGID 2796 - configuration start_time
*	20090122 - franciscom - BUGID 2012 
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('exec.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args();
$gui = initializeGui($db,$args);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

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
function initializeGui(&$dbHandler,$args)
{
	// BUGID 3930
	global $g_locales_date_format;
	$locale = (isset($_SESSION['locale'])) ? $_SESSION['locale'] : 'en_GB';
	$date_format = $g_locales_date_format[$locale];		
	
    $gui = new stdClass();  
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
    

    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;
    $gui->str_option_none = $gui_open . lang_get('nobody') . $gui_close;
    
    $gui->tplan_id = $args->tplan_id;
    $gui->tproject_id = $args->tproject_id;
    
    
    $tplan_info = $tplan_mgr->get_by_id($gui->tplan_id);
    $gui->tplan_name = $tplan_info['name'];

    $tproject_info = $tproject_mgr->get_by_id($gui->tproject_id);
    $gui->tproject_name = $tproject_info['name'];

    $re = new results($dbHandler, $tplan_mgr,$tproject_info,$tplan_info);

    $gui->assigned_users = new stdClass();
    $gui->keywords = new stdClass();
    $gui->builds = new stdClass();
    $gui->platforms = new stdClass();
    $gui->testsuites = new stdClass();

    // 20090107 - franciscom
    // Show only users that are able to execute test cases ?
    // What happens if a user that has loose right to execute, but
    // before loosing this right has been assigned some tests, or have executed it?
    // 
    // $gui->assigned_users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER, ADD_BLANK_OPTION);
    // $gui->assigned_users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER,
    // 	                                                   array(TL_USER_ANYBODY => $gui->str_option_any,
	  //                                                            TL_USER_NOBODY => $gui->str_option_none) );
    //
    $gui->assigned_users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER,
    	                                                   array(TL_USER_ANYBODY => $gui->str_option_any) );

    $gui->assigned_users->qty = count($gui->assigned_users->items);
    
    // BUGID 2012 - franciscom
    $gui->keywords->items[0]=$gui->str_option_any;
    if(!is_null($tplan_keywords_map=$tplan_mgr->get_keywords_map($gui->tplan_id)) ) 
    {
        $gui->keywords->items += $tplan_keywords_map;
    }
       
    $gui->builds->items = $tplan_mgr->get_builds($gui->tplan_id,testplan::ACTIVE_BUILDS);
    $gui->platforms->items = $tplan_mgr->getPlatforms($gui->tplan_id);
    $gui->testsuites->items = $re->getTopLevelSuites();

    $gui->keywords->qty = count($gui->keywords->items);
    $gui->builds->qty = count($gui->builds->items);
    $gui->platforms->qty = count($gui->platforms->items);
    $gui->testsuites->qty = count($gui->testsuites->items);
    $gui->status_code_label = get_status_for_reports_html_options();
    $gui->report_type = $args->format;

    $reports_cfg = config_get('reportsCfg');
    
    // BUGID 3716
    $startDate = strftime($date_format, time() - ($reports_cfg->start_date_offset));
    $gui->selected_start_date = $startDate;
    $gui->selected_start_time = $reports_cfg->start_time;

    // BUGID 3716
    $gui->selected_end_date = strftime($date_format, time());
    $gui->selected_end_time = null;
    return $gui;
}

function init_args()
{
	$iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
	);

	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);

    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;

    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
