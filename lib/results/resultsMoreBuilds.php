<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * This page will forward the user to a form where they can select
 * the builds they would like to query results against.
 * 
 * @package 	TestLink
 * @author		Kevin Levy <kevinlevy@users.sourceforge.net>
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: resultsMoreBuilds.php,v 1.76.2.2 2010/12/21 12:00:22 amkhullar Exp $
 *
 * @internal Revisions:
 *  20101202 - asimon - BUGID 4027: Query metrics start date and end date are not working - getting all results
 *  20101026 - Julian - BUGID 3930 - Localized dateformat for datepicker
 *  20101022 - asimon - BUGID 3716 - replaced old separated inputs for day/month/year by ext js calendar
 *	20101019 - eloff - BUGID 3794 - added contribution by rtessier
 *	20091027 - franciscom - BUGID 2500
 *	20090409 - amitkhullar- code refactor for results object
 *	20090327 - amitkhullar- BUGID 2156 - added option to get latest/all results in Query metrics report.
 *	20090122 - franciscom - BUGID 2012 
 *	20080524 - franciscom - BUGID 1430
 *	20070901 - franciscom - refactoring
 * 
 **/
require_once('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('users.inc.php');
require_once('displayMgr.php');
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$date_format_cfg = config_get('date_format');

$args = init_args();
$gui = initializeGui($db,$args,$date_format_cfg);
$mailCfg = buildMailCfg($gui);

$smarty = new TLSmarty();

$smarty->assign('gui', $gui);
$smarty->assign('report_type', $args->report_type);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->report_type,$mailCfg);


/**
 * initialize Gui
 */
function initializeGui(&$dbHandler,&$argsObj,$dateFormat)
{
    $reports_cfg = config_get('reportsCfg');
    
    $gui = new stdClass();
    $tplan_mgr = new testplan($dbHandler);
    $tproject_mgr = new testproject($dbHandler);
 
    $getOpt = array('outputFormat' => 'map');
    $gui->platformSet = $tplan_mgr->getPlatforms($argsObj->tplan_id,$getOpt);


    $gui->title = lang_get('query_metrics_report');

    $gui->showPlatforms=true;
	if( is_null($gui->platformSet) )
	{
		$gui->platformSet = array('');
		$gui->showPlatforms=false;
	}
   
    $gui->resultsCfg = config_get('results');

    // BUGID 3716, BUGID 3930
	// convert starttime to iso format for database usage
    if (isset($_REQUEST['selected_start_date']) && $_REQUEST['selected_start_date'] != '') {
		$date_array = split_localized_date($_REQUEST['selected_start_date'], $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$gui->startTime = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	// convert starttime to iso format for database usage
    if (isset($_REQUEST['selected_end_date']) && $_REQUEST['selected_end_date'] != '') {
		$date_array = split_localized_date($_REQUEST['selected_end_date'], $dateFormat);
		if ($date_array != null) {
			// set date in iso format
			$gui->endTime = $date_array['year'] . "-" . $date_array['month'] . "-" . $date_array['day'];
		}
	}
	
	$start_hour = isset($_REQUEST['start_Hour']) ? $_REQUEST['start_Hour'] : "00";
	$gui->startTime = $gui->startTime . " " . $start_hour . ":00:00";
	$end_hour = isset($_REQUEST['end_Hour']) ? $_REQUEST['end_Hour'] : "00";
	$gui->endTime = $gui->endTime . " " . $end_hour . ":59:59";
	   
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');
    $gui->str_option_any = $gui_open . lang_get('any') . $gui_close;
    $gui->str_option_none = $gui_open . lang_get('nobody') . $gui_close;

    $gui->search_notes_string = $argsObj->search_notes_string;

    $gui->tplan_id = $argsObj->tplan_id;
    $gui->tproject_id = $argsObj->tproject_id;
   
    $tplan_info = $tplan_mgr->get_by_id($gui->tplan_id);
    $tproject_info = $tproject_mgr->get_by_id($gui->tproject_id);
    $gui->tplan_name = $tplan_info['name'];
    $gui->tproject_name = $tproject_info['name'];

    $testsuiteIds = null;
    $testsuiteNames = null;
            
    $tsuites_qty = sizeOf($argsObj->testsuitesSelected);
    for ($id = 0; $id < $tsuites_qty ; $id++)
    {
    	list($suiteId, $suiteName) = preg_split("/\,/", $argsObj->testsuitesSelected[$id], 2);
    	$testsuiteIds[$id] = $suiteId;
    	$testsuiteNames[$id] = $suiteName;	
    }

    $buildsToQuery = -1;
    if (sizeof($argsObj->buildsSelected)) {
    	$buildsToQuery = implode(",", $argsObj->buildsSelected);
    }


    // statusForClass is used for results.class.php
    // lastStatus is used to be displayed 
    $statusForClass = 'a';
    
    // amitkhullar - added this parameter to get the latest results. 
	$latest_resultset = $argsObj->display->latest_results;
	
	// BUGID 2500
    // $assignee = $argsObj->ownerSelected ? TL_USER_ANYBODY : null;
    // $tester = $argsObj->executorSelected ? TL_USER_ANYBODY : null;
    $assignee = $argsObj->ownerSelected > 0 ? $argsObj->ownerSelected : TL_USER_ANYBODY;
    $tester = $argsObj->executorSelected > 0 ? $argsObj->executorSelected : TL_USER_ANYBODY  ;
    
    // BUGID 4027

    
    $re = new newResults($dbHandler, $tplan_mgr,$tproject_info,$tplan_info, 
                      	 $testsuiteIds, $buildsToQuery,
                         $argsObj->platformsSelected, $statusForClass,
                         $latest_resultset, $argsObj->keywordSelected,
                         $assignee, $gui->startTime,
                         $gui->endTime, $tester,
                         $argsObj->search_notes_string, null);
                      
    $gui->suiteList = $re->getSuiteList();  // test executions results
    // Filter test cases on selected platforms
    foreach ($gui->suiteList as $suiteid => $tcases) {
        $filtered = array();
        foreach ($tcases as $index => $tcase) {
            if ($tcase['platform_id'] == 0 ||
                $argsObj->platformsSelected[0] == ALL_PLATFORMS ||
                array_search($tcase['platform_id'], $argsObj->platformsSelected) !== false) {
                array_push($filtered, $tcase);
            }
        }
        unset($gui->suiteList[$suiteid]);
        $gui->suiteList[$suiteid] = $filtered;
    }
    $gui->flatArray = $re->getFlatArray();
    $gui->mapOfSuiteSummary =  $re->getAggregateMap();
    
    $gui->totals = new stdClass();
    $gui->totals->items = $re->getTotalsForPlan();
    $gui->totals->labels = array();
    
    foreach($gui->totals->items as $key => $value)
    {
        $l18n = $key == 'total' ? 'th_total_cases' : $gui->resultsCfg['status_label'][$key];
        $gui->totals->labels[$key] = lang_get($l18n);  
    }

    // BUGID 2012 - franciscom
    $gui->keywords = new stdClass();             
    $gui->keywords->items[0] = $gui->str_option_any;
    if(!is_null($tplan_keywords_map = $tplan_mgr->get_keywords_map($gui->tplan_id)))
    {
        $gui->keywords->items += $tplan_keywords_map; 
    }    
    $gui->keywords->qty = count($gui->keywords->items);
    $gui->keywordSelected = $gui->keywords->items[$argsObj->keywordSelected];
    
    $gui->builds_html = $tplan_mgr->get_builds_for_html_options($gui->tplan_id);
    $gui->users = getUsersForHtmlOptions($dbHandler,ALL_USERS_FILTER,
                                         array(TL_USER_ANYBODY => $gui->str_option_any));

    $gui->ownerSelected = $gui->users[$argsObj->ownerSelected];      
    $gui->executorSelected = $gui->users[$argsObj->executorSelected];
    $gui->testsuitesSelected = $testsuiteNames;
    $gui->buildsSelected = $argsObj->buildsSelected;
    $gui->platformsSelected = $argsObj->platformsSelected;
    $gui->display = $argsObj->display;

    // init display rows attribute and some status localized labels
    $gui->displayResults = array();
    $gui->lastStatus = array();
    foreach($reports_cfg->exec_status as $verbose => $label)
    {
		$gui->displayResults[$gui->resultsCfg['status_code'][$verbose]]=false;
    }

	foreach($gui->resultsCfg['status_label'] as $status_verbose => $label_key)
	{
		$gui->statusLabels[$gui->resultsCfg['status_code'][$status_verbose]] = lang_get($label_key);
	}
    
	$lastStatus_localized = null;
    foreach($argsObj->lastStatus as $key => $status_code)
    {
    	$verbose = $gui->resultsCfg['code_status'][$status_code];
		$gui->displayResults[$status_code] = true;
		$lastStatus_localized[] = lang_get($gui->resultsCfg['status_label'][$verbose]);
    }	
    $gui->lastStatus = $lastStatus_localized;
    
    return $gui;
}

/**
 * Initialize input data
 */
function init_args()
{
	$iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"report_type" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
		"build" => array(tlInputParameter::ARRAY_INT),
		"platform" => array(tlInputParameter::ARRAY_INT),
		"keyword" => array(tlInputParameter::INT_N),
		"owner" => array(tlInputParameter::INT_N),
		"executor" => array(tlInputParameter::INT_N),
		"display_totals" => array(tlInputParameter::INT_N,1),
		"display_query_params" => array(tlInputParameter::INT_N,1),
		"display_test_cases" => array(tlInputParameter::INT_N,1),
		"display_latest_results" => array(tlInputParameter::INT_N,1),
		"display_suite_summaries" => array(tlInputParameter::INT_N,1),
		"lastStatus" => array(tlInputParameter::ARRAY_STRING_N),
		"testsuite" => array(tlInputParameter::ARRAY_STRING_N),
		"search_notes_string" => array(tlInputParameter::STRING_N),
	);
	$args = new stdClass();

	$_REQUEST=strings_stripSlashes($_REQUEST);
	$pParams = R_PARAMS($iParams);
	
	$args->format = $pParams["format"];
	$args->report_type = $pParams["report_type"];
	$args->tplan_id = $pParams["tplan_id"];
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    
    $args->display = new stdClass();
    $args->display->suite_summaries = $pParams["display_suite_summaries"];
    $args->display->totals = $pParams["display_totals"];    
    $args->display->query_params = $pParams["display_query_params"];
    $args->display->test_cases = $pParams["display_test_cases"];
    $args->display->latest_results = $pParams["display_latest_results"];
    
    $args->lastStatus = $pParams["lastStatus"] ? $pParams["lastStatus"] : array();
    $args->keywordSelected = $pParams["keyword"];
    $args->ownerSelected = $pParams["owner"];
    $args->executorSelected = $pParams["executor"];
    $args->buildsSelected = $pParams["build"] ? $pParams["build"] : array();
    $args->platformsSelected = $pParams["platform"] ? $pParams["platform"] : array();
    $args->testsuitesSelected = $pParams["testsuite"] ? $pParams["testsuite"] : array();
    $args->search_notes_string = $pParams['search_notes_string'];

    return $args;  
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}

/**
 * 
 *
 */
function buildMailCfg(&$guiObj)
{
	$labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
	$cfg = new stdClass();
	$cfg->cc = ''; 
	$cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name . 
	                ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;
	                 
	return $cfg;
}
?>
