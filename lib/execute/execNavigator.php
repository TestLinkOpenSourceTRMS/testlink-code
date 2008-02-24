<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.55 $
 * @modified $Date: 2008/02/24 17:54:59 $ by $Author: franciscom $
 *
 * 20080224 - franciscom - BUGID 1056 
 * 20071229 - franciscom - refactoring tree colouring and counters config
 * 20071006 - franciscom - changes on exec_cfield_mgr() call
 * 20070912 - jbarchibald - custom field search BUGID - 1051
 * 20070630 - franciscom - set default value for filter_assigned_to
 * 20070607 - franciscom - BUGID 887 - problem with builds
 **/
require_once('../../config.inc.php');
require_once('common.php');
require_once('treeMenu.inc.php');
require_once('exec.inc.php');
require_once('builds.inc.php');
testlinkInitPage($db);

$template_dir = 'execute/';
$gui_cfg = config_get('gui');
$exec_cfg = config_get('exec_cfg');
$args = init_args($exec_cfg);

$exec_cfield_mgr = new exec_cfield_mgr($db,$args->tproject_id);

// jbarchibald 20070911 - adding custom field filtering
$cf_selected = null;
$cf_smarty = null;
if($gui_cfg->enable_custom_fields)
{
	  $cf_smarty = $exec_cfield_mgr->html_table_of_custom_field_inputs();
    $cf_selected = $exec_cfield_mgr->get_set_values();
}

$tplan_mgr = new testplan($db);
$disable_filter_assigned_to = false;
$assigned_to_user = '';
$effective_role = $args->user->getEffectiveRole($db,$args->tproject_id,$args->tplan_id);

//SCHLUNDUS: hmm, for user defined roles, this wont work correctly
// Need to check right no role
$exec_view_mode = ($effective_role->dbID == TL_ROLES_TESTER) ? $exec_cfg->view_mode->tester : 'all';
switch ($exec_view_mode)
{
	case 'all':
		break;
		
	case 'assigned_to_me':
		$args->filter_assigned_to = $args->user->dbID;
		$assigned_to_user = $args->user->getDisplayName();
		$disable_filter_assigned_to = true;
		break;
}

// 20070123 - franciscom -  only active builds no matter user role
$optBuild = $tplan_mgr->get_builds_for_html_options($args->tplan_id,ACTIVE);

// 20070607 - franciscom - BUGID 887
$maxBuildID = $tplan_mgr->get_max_build_id($args->tplan_id,GET_ACTIVE_BUILD, GET_OPEN_BUILD);
$args->buildSelected = $args->buildSelected > 0 ? $args->buildSelected : $maxBuildID;
if (!$args->buildSelected && sizeof($optBuild))
	$$args->buildSelected = key($optBuild);

$keywords_map = $tplan_mgr->get_keywords_map($args->tplan_id,' order by keyword ');
if(!is_null($keywords_map))
{
	// add the blank option
	// 0 -> id for no keyword
	$blank_map[0] = '';
	$keywords_map = $blank_map + $keywords_map;
}
$menuUrl = 'lib/execute/execSetResults.php';

$getArguments=initializeGetArguments($args,$cf_selected);

if ($args->optResultSelected == 'all')
	$args->optResultSelected = null;

$useCounters = $exec_cfg->enable_tree_testcase_counters;
$useColours = $exec_cfg->enable_tree_colouring;

// 20080224 - franciscom - $args->include_unassigned
// 20070914 - jbarchibald - added $cf_selected parameter
$sMenu = generateExecTree($db,$menuUrl,$args->tproject_id,$args->tproject_name,$args->tplan_id,$args->tplan_name,
                          $args->buildSelected,$getArguments,$args->keyword_id,$args->tc_id,false,
                          $args->filter_assigned_to,$args->optResultSelected,$cf_selected,
                          $useCounters,$useColours,$args->include_unassigned);


// link to load frame named 'workframe' when the update button is pressed
$src_workframe = null;
if(isset($_REQUEST['submitOptions']))
	$src_workframe = $_SESSION['basehref'].$menuUrl . "?level=testproject&id={$args->tproject_id}" . $getArguments;
                     
$smarty = new TLSmarty();
$smarty->assign('include_unassigned',$args->include_unassigned); 
$smarty->assign('design_time_cf',$cf_smarty); 
$smarty->assign('disable_filter_assigned_to',$disable_filter_assigned_to);
$smarty->assign('assigned_to_user',$assigned_to_user);
$smarty->assign('src_workframe',$src_workframe);
$smarty->assign('tplan_name',$args->tplan_name);
$smarty->assign('tplan_id', $args->tplan_id);
$smarty->assign('users',getUsersForHtmlOptions($db,null,true));
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('treeColored', $args->treeColored);
$smarty->assign('optBuild', $optBuild);
$smarty->assign('optBuildSelected', $args->buildSelected);
$smarty->assign('optResult', createResultsMenu());
$smarty->assign('optResultSelected', $args->optResultSelected); 
$smarty->assign('filter_assigned_to', $args->filter_assigned_to);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $args->keyword_id);
$smarty->assign('tcID', intval($args->tc_id) > 0 ? $args->tc_id : '');
$smarty->assign('tree', invokeMenu($sMenu,null,null));
$smarty->assign('menuUrl',$menuUrl);
$smarty->assign('args',$getArguments);
$smarty->assign('SP_html_help_file',TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . "/executeTest.html");
$smarty->display($template_dir . 'execNavigator.tpl');


/*
  function: 

  args:
  
  returns: 

  schlundus: changed the user_id to the currentUser of the session
*/
function init_args($exec_cfg)
{
    $args->tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
    $args->user = $_SESSION['currentUser'];
    $args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
    $args->tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'null';
    $args->treeColored = (isset($_REQUEST['colored']) && ($_REQUEST['colored'] == 'result')) ? 'selected="selected"' : null;
    $args->tc_id = isset($_REQUEST['tcID']) ? intval($_REQUEST['tcID']) : null;
    $args->keyword_id = isset($_REQUEST['keyword_id']) ? $_REQUEST['keyword_id'] : 0;             
    $args->optResultSelected = isset($_REQUEST['filter_status']) ? $_REQUEST['filter_status'] : 'all';

    $user_filter_default = 0;
    switch($exec_cfg->user_filter_default)
    {
    	case 'logged_user':
    		$user_filter_default = $args->user->dbID;
    		break;  

    	case 'none':
    	default:
    		break;  
    }
    $args->filter_assigned_to = isset($_REQUEST['filter_assigned_to']) ? intval($_REQUEST['filter_assigned_to']) : $user_filter_default;             
    $args->buildSelected = isset($_POST['build_id']) ? $_POST['build_id'] : -1;

    // Checkbox
    $args->include_unassigned=isset($_REQUEST['include_unassigned']) ? $_REQUEST['include_unassigned'] : 0;

    return $args;
}    


/*
  function: initializeGetArguments
            build arguments that will be passed to execSetResults.php
            with a http call 

  args:
  
  returns: 

  rev: 20080224 - franciscom - added include_unassigned
  
*/
function initializeGetArguments($argsObj,$customFieldSelected)
{
    $settings = '&build_id=' . $argsObj->buildSelected;
    
    if ($argsObj->keyword_id)
    	$settings .= '&keyword_id='.$argsObj->keyword_id;
    
    if ($argsObj->tc_id)
    	$settings .= '&tc_id='.$argsObj->tc_id;
    
    if ($argsObj->filter_assigned_to)
    	$settings .= '&filter_assigned_to='.$argsObj->filter_assigned_to;
    
    if ($argsObj->optResultSelected != 'all')
    	$settings .= '&filter_status='.$argsObj->optResultSelected;
  
  	$settings .= '&include_unassigned=' . $argsObj->include_unassigned;
    	
    if ($customFieldSelected)
    	$settings .= '&cfields='. serialize($customFieldSelected);

    return $settings;
}
?>
