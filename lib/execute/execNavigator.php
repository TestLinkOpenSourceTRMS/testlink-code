<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.40 $
 * @modified $Date: 2007/10/31 17:26:06 $ by $Author: asielb $
 *
 * 20071006 - franciscom - changes on exec_cfield_mgr() call
 * 
 * 20070912 - jbarchibald - custom field search BUGID - 1051
 * 20070630 - franciscom - set default value for filter_assigned_to
 * 20070607 - franciscom - BUGID 887 - problem with builds
 * 20070212 - franciscom - name changes on html inputs
 *
 * 20070123 - franciscom - 
 * 1. added logic to only show ACTIVE BUILDS
 * 2. removed deprecated functions
 *
 * 20061030 - franciscom
 * added new variable to allow the update of frame name='workframe', 
 * when filter is changed in frame name='treeframe'
 *
 **/
require_once('../../config.inc.php');
require_once('common.php');
require_once('treeMenu.inc.php');
require_once('exec.inc.php');
require_once('builds.inc.php');

testlinkInitPage($db);


$tproject_id = $_SESSION['testprojectID'];
$user_id = $_SESSION['userID'];
$tplan_id   = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'null';
$treeColored = (isset($_POST['colored']) && ($_POST['colored'] == 'result')) ? 'selected="selected"' : null;

$exec_cfield_mgr = new exec_cfield_mgr($db,$tproject_id);


$gui_cfg = config_get('gui');
$exec_cfg = config_get('exec_cfg');

// jbarchibald 20070911 - adding custom field filtering
if($gui_cfg->enable_custom_fields) {
	$cf_smarty = $exec_cfield_mgr->html_table_of_custom_field_inputs();
    $cf_selected = $exec_cfield_mgr->get_set_values();
} else {
    $cf_selected = null;
}

switch($exec_cfg->user_filter_default)
{
   case 'logged_user':
   $user_filter_default=$user_id;
   break;  

   case 'none':
   $user_filter_default=0;
   break;  
   
   default:
   $user_filter_default=0;
   break;  
}

$filter_assigned_to = isset($_POST['filter_assigned_to']) ? intval($_POST['filter_assigned_to']) : $user_filter_default;             

$tc_id = isset($_POST['tcID']) ? intval($_POST['tcID']) : null;
$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;             

$tplan_mgr = new testplan($db);

$effective_role=get_effective_role($db,$user_id,$tproject_id,$tplan_id);
$all_roles = getAllRoles($db);
$exec_view_mode='all';

if( $all_roles[$effective_role] == 'tester' )
{
  $exec_view_mode=$exec_cfg->view_mode->tester;
}

$disable_filter_assigned_to=false;
$assigned_to_user='';
switch ($exec_view_mode)
{
   case 'all':
   break;
   
   case 'assigned_to_me':
   $filter_assigned_to=$user_id;
   $user_info = getUserById($db,$user_id);
   $assigned_to_user=format_username($user_info[0]);
   $disable_filter_assigned_to=true;
   break;
}



// 20070123 - franciscom - 
// only active builds no matter user role
//
$optBuild = $tplan_mgr->get_builds_for_html_options($tplan_id,ACTIVE);
$optResultSelected = isset($_POST['filter_status']) ? $_POST['filter_status'] : 'all';

// 20070607 - franciscom - BUGID 887
$maxBuildID = $tplan_mgr->get_max_build_id($tplan_id,GET_ACTIVE_BUILD, GET_OPEN_BUILD);
$optBuildSelected = isset($_POST['build_id']) ? $_POST['build_id'] : $maxBuildID;
if (!$optBuildSelected && sizeof($optBuild))
	$optBuildSelected = key($optBuild);

$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';


$keywords_map = $tplan_mgr->get_keywords_map($_SESSION['testPlanId'],' order by keyword ');
if(!is_null($keywords_map))
{
	// add the blank option
	// 0 -> id for no keyword
	$blank_map[0] = '';
	$keywords_map = $blank_map+$keywords_map;
}

$menuUrl = null;
$SP_html_help_file = TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] . "/executeTest.html";

$menuUrl = 'lib/execute/execSetResults.php';

/*
$getArguments = '&build_id=' . $optBuildSelected;
if ($keyword_id)
	$getArguments .= '&keyword_id='.$keyword_id;
if ($tc_id)
	$getArguments .= '&tc_id='.$tc_id;
if ($selectedOwner)
	$getArguments .= '&owner='.$selectedOwner;
if ($optResultSelected != 'all')
	$getArguments .= '&status='.$optResultSelected;
*/

// 20070212 - franciscom
$getArguments = '&build_id=' . $optBuildSelected;
if ($keyword_id)
	$getArguments .= '&keyword_id='.$keyword_id;
if ($tc_id)
	$getArguments .= '&tc_id='.$tc_id;
if ($filter_assigned_to)
	$getArguments .= '&filter_assigned_to='.$filter_assigned_to;
if ($optResultSelected != 'all')
	$getArguments .= '&filter_status='.$optResultSelected;
if ($cf_selected)
	$getArguments .= '&cfields='.serialize($cf_selected);

$optResult = createResultsMenu();

if ($optResultSelected == 'all')
	$optResultSelected = null;

// 20070914 - jbarchibald - added $cf_selected parameter
$sMenu = generateExecTree($db,$menuUrl,$tproject_id,$tproject_name,$tplan_id,$tplan_name,
                          $optBuildSelected,$getArguments,$keyword_id,$tc_id,false,
                          $filter_assigned_to,$optResultSelected,$cf_selected);

// link to load frame named 'workframe' when the update button is pressed
$src_workframe = null;
if(isset($_REQUEST['submitOptions']))
{
	$src_workframe = $_SESSION['basehref'].$menuUrl . "?level=testproject&id={$tproject_id}" . $getArguments;
}
                     
$tree = invokeMenu($sMenu,null,null);
$tcData = null;
$testCaseID = null;


$users = get_users_for_html_options($db,null,true);

$smarty = new TLSmarty();

$smarty->assign('design_time_cf',$cf_smarty); 
$smarty->assign('disable_filter_assigned_to',$disable_filter_assigned_to);
$smarty->assign('assigned_to_user',$assigned_to_user);

$smarty->assign('src_workframe',$src_workframe);
$smarty->assign('tplan_name',$tplan_name);
$smarty->assign('tplan_id', $tplan_id);
$smarty->assign('users',$users);
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('treeColored', $treeColored);
$smarty->assign('optBuild', $optBuild);
$smarty->assign('optBuildSelected', $optBuildSelected);

$smarty->assign('optResult', $optResult);
$smarty->assign('optResultSelected', $optResultSelected); 

$smarty->assign('filter_assigned_to', $filter_assigned_to);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $keyword_id);
$smarty->assign('tcID', intval($tc_id) > 0 ? $tc_id : '');
$smarty->assign('testCaseID',$testCaseID);
$smarty->assign('tcIDFound', $tcData ? 1 : 0);
$smarty->assign('tree', $tree);
$smarty->assign('menuUrl',$menuUrl);
$smarty->assign('args',$getArguments);
$smarty->assign('SP_html_help_file',$SP_html_help_file);
$smarty->display('execNavigator.tpl');
?>
