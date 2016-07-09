<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource	tcCreatedPerUser.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @author 		Bruno P. Kinoshita - brunodepaulak@yahoo.com.br
 * @link 		http://www.teamst.org/index.php
 * @since 		1.9.4
 * 
 * Generates report of test cases created per user. It produces a report with 
 * all test cases created within a project. 
 * 
 * @internal revisions:
 * 20111120 - kinow - BUGID 1761
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('results.class.php');
require_once('exec.inc.php');
require_once('users.inc.php');

testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();
$results_config = config_get('results');

// init arguments
$args = init_args($db);
$gui = initializeGui($db,$args);

$l18n = init_labels(array('tcversion_indicator' => null,'goto_testspec' => null, 'version' => null, 
						  'testplan' => null, 'assigned_tc_overview' => null,'testcases_created_per_user' => null,
                           'design' => null, 'execution' => null, 'execution_history' => null, 
						   'low_priority' => null, 'medium_priority' => null, 'high_priority' => null));

$gui->pageTitle = sprintf($l18n['testcases_created_per_user'],$gui->tproject_name);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 */
function initializeGui(&$dbHandler,$args)
{
	$locale = (isset($_SESSION['locale'])) ? $_SESSION['locale'] : 'en_GB';
	$localesDateFormat = config_get('locales_date_format');
	$date_format = $localesDateFormat[$locale];

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
	unset($tplan_mgr);
	$gui->tplan_name = $tplan_info['name'];

	$tproject_info = $tproject_mgr->get_by_id($gui->tproject_id);
	unset($tproject_mgr);
	$gui->tproject_name = $tproject_info['name'];

	$gui->users = new stdClass();

	$gui->users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER,
									array(TL_USER_ANYBODY => $gui->str_option_any) );

	$gui->users->qty = count($gui->users->items);

	$reports_cfg = config_get('reportsCfg');

	$startDate = strftime($date_format, time() - ($reports_cfg->start_date_offset));
    $gui->selected_start_date = $startDate;
	$gui->selected_start_time = $reports_cfg->start_time;

	$gui->selected_end_date = strftime($date_format, time());
	$gui->selected_end_time = null;
	return $gui;
}

/**
 * Gets the arguments used to create the report. 
 * 
 * Some of these arguments are set in the $_REQUEST, and some in $_SESSION. Having 
 * these arguments in hand, the init_args method will use TestLink objects, such 
 * as a Test Project Manager (testproject class) to retrieve other information 
 * that is displayed on the screen (e.g.: project name).
 * 
 * @param $dbHandler handler to TestLink database
 * @return object of stdClass
 */
function init_args(&$dbHandler)
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	if($args->tproject_id >0)
	{ 
		$tproject_mgr = new testproject($dbHandler);
		$dummy = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name = $dummy['name'];
		$args->priority_enabled = $dummy['opt']->testPriorityEnabled ? true : false;
		unset($tproject_mgr);
	}
	
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;

    $args->user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if( $args->user_id == 0)
    {
        $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
        $args->user_name = $_SESSION['currentUser']->login;
    }	

	return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>