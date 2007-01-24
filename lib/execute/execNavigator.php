<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: execNavigator.php,v $
 *
 * @version $Revision: 1.31 $
 * @modified $Date: 2007/01/24 08:10:24 $ by $Author: franciscom $
 *
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

$treeColored = (isset($_POST['colored']) && ($_POST['colored'] == 'result')) ? 'selected="selected"' : null;
$selectedOwner = isset($_POST['owner']) ? intval($_POST['owner']) : 0;             

$tc_id = isset($_POST['tcID']) ? intval($_POST['tcID']) : null;
$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;             

$tplan_id   = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
$tplan_name = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : 'null';
$tplan_mgr = new testplan($db);

// 20070123 - franciscom - 
// only active builds no matter user role
//
$optBuild = $tplan_mgr->get_builds_for_html_options($tplan_id,ACTIVE);
$optResultSelected = isset($_POST['result']) ? $_POST['result'] : 'all';

$maxBuildID = $tplan_mgr->get_max_build_id($tplan_id);
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
$getArguments = '&build_id=' . $optBuildSelected;
if ($keyword_id)
	$getArguments .= '&keyword_id='.$keyword_id;
if ($tc_id)
	$getArguments .= '&tc_id='.$tc_id;
if ($selectedOwner)
	$getArguments .= '&owner='.$selectedOwner;
if ($optResultSelected != 'all')
	$getArguments .= '&status='.$optResultSelected;

$optResult = createResultsMenu();

if ($optResultSelected == 'all')
	$optResultSelected = null;
$sMenu = generateExecTree($db,$menuUrl,$tproject_id,$tproject_name,$tplan_id,$tplan_name,
                          $optBuildSelected,$getArguments,$keyword_id,$tc_id,false,$selectedOwner,$optResultSelected);

// link to load frame named 'workframe' when the update button is pressed
$src_workframe = null;
if(isset($_REQUEST['submitOptions']))
{
	$src_workframe = $menuUrl . "?level=testproject&id={$tproject_id}" . $getArguments;
}
                     
$tree = invokeMenu($sMenu);
$tcData = null;
$testCaseID = null;
$testCaseID = null;

$users = get_users_for_html_options($db,null,true);

$smarty = new TLSmarty();
$smarty->assign('src_workframe',$src_workframe);
$smarty->assign('tplan_name',$tplan_name);
$smarty->assign('users',$users);
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('treeColored', $treeColored);
$smarty->assign('optBuild', $optBuild);
$smarty->assign('optBuildSelected', $optBuildSelected);
$smarty->assign('optResult', $optResult);
$smarty->assign('optResultSelected', $optResultSelected); 
$smarty->assign('selectedOwner', $selectedOwner);
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