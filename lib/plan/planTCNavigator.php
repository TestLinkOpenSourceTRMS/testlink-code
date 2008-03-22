<?php
/**
*	TestLink Open Source Project - http://testlink.sourceforge.net/
* @version $Id: planTCNavigator.php,v 1.8 2008/03/22 23:47:05 schlundus Exp $
*	@author Martin Havlat
*
* Used in the remove test case feature
*
* rev :
*      20080311 - franciscom - BUGID 1427 - first developments
*      20070925 - franciscom - added management of workframe
*/
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$filters = new stdClass();
$additionalInfo = new stdClass();

$template_dir='plan/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$tplan_mgr = new testplan($db);
$args = init_args($tplan_mgr);

$testers = null;

$filters->keyword_id = $args->keyword_id;
$filters->tc_id = FILTER_BY_TC_OFF;
$filters->build_id = FILTER_BY_BUILD_OFF;
$filters->assignedTo = FILTER_BY_ASSIGNED_TO_OFF;
$filters->status = FILTER_BY_TC_STATUS_OFF;
$filters->cf_hash = SEARCH_BY_CUSTOM_FIELDS_OFF;
$filters->include_unassigned=1;
$filters->show_testsuite_contents=1;

$additionalInfo->useCounters=CREATE_TC_STATUS_COUNTERS_OFF;
$additionalInfo->useColours=COLOR_BY_TC_STATUS_OFF;


// filter using user roles
$tplans = getAccessibleTestPlans($db,$args->tproject_id,$args->user_id,1);
$map_tplans = array();
foreach($tplans as $key => $value)
{
	$map_tplans[$value['id']] = $value['name'];
}

// We only want to use in the filter, keywords present in the test cases that are
// linked to test plan, and NOT all keywords defined for test project
$keywords_map = $tplan_mgr->get_keywords_map($args->tplan_id, " order by keyword ");
if(!is_null($keywords_map))
{
	$keywords_map = array( 0 => '') + $keywords_map;
}


// set feature data
switch($args->feature)
{
  case 'updateTC':
	$menuUrl = "lib/plan/planUpdateTC.php";
	$title = lang_get('title_test_plan_navigator');
	$filters->hide_testcases = 0;
	$help_file = "";
  break;

  case 'removeTC':
	$menuUrl = "lib/plan/planTCRemove.php";
	$title = lang_get('title_test_plan_navigator');
	$filters->hide_testcases = 0;
	$help_file = "testSetRemove.html";
  break;

  case 'plan_risk_assignment':
	$menuUrl = "lib/plan/plan_risk_assignment.php";
	$title = lang_get('title_test_plan_navigator');
	$filters->hide_testcases = 1;
	$help_file = "priority.html";
  break;

  case 'tc_exec_assignment':
	// BUGID 1427
	$testers = getTestersForHtmlOptions($db,$args->tplan_id,$args->tproject_id);
	$filters->assignedTo = $args->filter_assigned_to;
	$filters->include_unassigned = 0;

	$menuUrl = "lib/plan/tc_exec_assignment.php";
	$title = lang_get('title_test_plan_navigator');
	$filters->hide_testcases = 0;
	$help_file = "planOwnerAndPriority.html";
	break;

  default:
	tLog("Wrong or missing GET argument 'feature'.", 'ERROR');
	exit();
	break;

}

// Build arguments that will be passed from tree menu to lauched
// pages, when user do some action on tree (example clicks on a folder)
$getArguments = '&tplan_id=' . $args->tplan_id;
if ($args->keyword_id)
{
	$getArguments .= '&keyword_id='.$args->keyword_id;
}
// BUGID 1427
if($filters->assignedTo > 0)
{
    $getArguments .= '&assigned_to=' . $filters->assignedTo;
}
$sMenu = generateExecTree($db,$menuUrl,$args->tproject_id,$args->tproject_name,
                          $args->tplan_id,$args->tplan_name,$getArguments,$filters,$additionalInfo);

$tree = invokeMenu($sMenu,'',null);

$smarty = new TLSmarty();
$smarty->assign('workframe',$args->workframe);
$smarty->assign('args',$getArguments);
$smarty->assign('tplan_id',$args->tplan_id);
$smarty->assign('map_tplans',$map_tplans);
$smarty->assign('testers', $testers);
$smarty->assign('filter_assigned_to',$filters->assignedTo);
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $args->keyword_id);
$smarty->assign('treeHeader', $title);
$smarty->assign('menuUrl',$menuUrl);
$smarty->assign('SP_html_help_file',TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] ."/". $help_file);
$smarty->assign('additional_string',$args->tplan_name);
$smarty->display($template_dir . $default_template);

function init_args(&$tplanMgr)
{
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args = new stdClass();
    $args->user_id = $_SESSION['userID'];
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;

    $args->feature = $_REQUEST['feature'];
    $args->help_topic = isset($_REQUEST['help_topic']) ? $_REQUEST['help_topic'] : $args->feature;

    if(!(isset($_REQUEST['filter']) || isset($_REQUEST['called_by_me'])))
    {
        $args->workframe = '';
    }
    else
    {
        $args->workframe = $_SESSION['basehref'] . "lib/general/show_help.php" .
                         "?help={$args->help_topic}&locale={$_SESSION['locale']}";
    }

    $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? intval($_REQUEST['filter_assigned_to']) : 0;

    // 20070120 - franciscom -
    // is possible to call this page using a Test Project that have no test plans
    // in this situation the next to entries are undefined in SESSION
    $args->tplan_id = isset($_SESSION['testPlanId']) ? intval($_SESSION['testPlanId']) : 0;
    $args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';

    if($args->tplan_id != 0)
    {
		$args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testPlanId'];
		$tplan_info = $tplanMgr->get_by_id($args->tplan_id);
		$args->tplan_name = $tplan_info['name'];
    }

    return $args;
}
?>
