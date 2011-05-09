<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	frmWorkArea.php
 * @package 	TestLink
 * @copyright 	2005,2011 TestLink community 
 * @author 		Martin Havlat
 * @link 		http://www.teamst.org/index.php
 *
 * This page is window for navigation and working area (eg tree + edit page).
 *
 * @internal revisions
 *  20110417 - franciscom - added tproject id as new argument on links
 *  20101116 - asimon - BUGID 4007: Strange empty TestPlan combo boxes in same navigator panes
 *  20101013 - asimon - if execution is wanted, check for open builds in testplan
 *  20100822 - asimon - BUGID 3697: Assign Test Case execution - problems 
 *                                  when no build is defined on test plan
 *  20100106 - asimon - contribution for 2976 req/reqspec search
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$args = init_args();

// --------------------------------------------------------------------------------------
// Important Notes for Developers
// --------------------------------------------------------------------------------------
//
// if key found in this map, at User Interface level, screen will be divided 
// vertically in two frames.
// Normally on left will exists a tree menu. 
// On right frame an html named $key.html will be launched.
// Example:
//         if key = printTc, an html page printTc.html must exists on help directory
//
// (aa_tfp -> Associative Array TreeFramePath)
// key  : feature
// value: page to lauch
//
$req_cfg = config_get('req_cfg');

$aa_tfp = array( 
     'assignReqs' => 'lib/testcases/listTestCases.php?feature=assignReqs',
     'editTc' => 'lib/testcases/listTestCases.php?feature=edit_tc',
     'executeTest' => "lib/execute/execNavigator.php?tplan_id={$args->tplan_id}",
     'keywordsAssign' => 'lib/testcases/listTestCases.php?feature=keywordsAssign',
     'newest_tcversions' => '../../lib/plan/newest_tcversions.php',
     'planAddTC'    => 'lib/plan/planAddTCNavigator.php',
     'planRemoveTC' => 'lib/plan/planTCNavigator.php?feature=removeTC&help_topic=planRemoveTC',
     'planUpdateTC'    => 'lib/plan/planTCNavigator.php?feature=planUpdateTC',
     'printTestSpec' => 'lib/results/printDocOptions.php?type=testspec',
     'printReqSpec' => 'lib/results/printDocOptions.php?type=reqspec',
     'reqSpecMgmt' => 'lib/requirements/reqSpecListTree.php',
     'searchTc' => 'lib/testcases/tcSearchForm.php',
     'searchReq' => 'lib/requirements/reqSearchForm.php',
     'searchReqSpec' => 'lib/requirements/reqSpecSearchForm.php',
     'show_ve' => 'lib/plan/planTCNavigator.php?feature=show_ve',  
     'showMetrics' => 'lib/results/resultsNavigator.php',
     'test_urgency' => 'lib/plan/planTCNavigator.php?feature=test_urgency',
     'tc_exec_assignment' => 'lib/plan/planTCNavigator.php?feature=tc_exec_assignment'
);

$full_screen = array('newest_tcversions' => 1);
//cleanup session var
$_SESSION['currentSrsId'] = null;  // need to be removed due to TABBED BROWSING

/** feature to display */
$showFeature = $args->feature;
if (isset($aa_tfp[$showFeature]) === FALSE)
{
	// argument is wrong
	tLog("Wrong page argument feature = ".$showFeature, 'ERROR');
	exit();
}

// features that need to run the validate build function
// BUGID 3697: added "Assign Test Case execution to list of features for
// which build availability needs to be checked
if (in_array($showFeature,array('executeTest','showMetrics','tc_exec_assignment')))
{
	// Check if for test project selected at least a test plan exist (BUGID 623)
	if( $args->tplan_id > 0 )
	{
		// 20101013 - asimon - if execution is wanted, check for open builds
		$open = ($showFeature == 'executeTest') ? true : null;
  		validateBuildAvailability($db,$args->tplan_id,$args->tproject_id, $open);
	}
  	else
	{
  		redirect("../plan/planView.php?tproject_id={$args->tproject_id}");
		exit();
	}   
}

/// 1. get path from global var
/// 2. the URL made easier after setting some rules for help/instruction files
///    naming convention.
/// </enhancement>
$smarty = new TLSmarty();

$target = $aa_tfp[$showFeature];
$target .= (strpos($target,"?") === false) ? "?" : "&"; 
$target .= "tproject_id={$args->tproject_id}";

if(isset($full_screen[$showFeature]))
{
	// need to understand how to add tproject_id
	// redirect($aa_tfp[$showFeature]);
	redirect($target);
}
else
{
	$smarty->assign('treewidth', TL_FRMWORKAREA_LEFT_FRAME_WIDTH);
	$smarty->assign('treeframe', $target);
	$smarty->assign('workframe', 'lib/general/staticPage.php?key='.$showFeature);
	$smarty->display('frmInner.tpl');
}


/** 
 * 	validate that some build exists (for Test Plan related features).
 *  If no valid build is found give feedback to user and exit.
 *
 * 	@author Martin Havlat
 *  20101013 - asimon - new parameter $open: if execution is wanted, check for open builds
 *  20060809 - franciscom - check if user can create builds,
 *                          then put a link on the message page
 *                          to create link feature
 *
 **/
function validateBuildAvailability(&$db,$tplanID, $tprojectID, $open)
{
	$tplanMrg = new testplan($db);
	
	// 20101013 - asimon - if execution is wanted, check for open builds
	// BUGID 4007 - use open parameter also for active check
	if (!$tplanMrg->getNumberOfBuilds($tplanID, $open, $open))
	{	           
		$message = '<p>' . lang_get('no_build_warning_part1') . "<b> " . htmlspecialchars($tpName) . "</b>";
		
		$link_to_op = '';
		$hint_text = '';
		if($_SESSION['currentUser']->hasRight($db,"testplan_create_build",$tproject_id,$tplan_id) == 'yes')
		{	
			// final url will be composed adding to $basehref 
			// (one TL variable available on smarty templates) to $link_to_op
			$link_to_op = "lib/plan/buildEdit.php?tproject_id=$tprojectID&tplan_id=$tplanID&do_action=create";
			$hint_text = lang_get('create_a_build');
		}  
  		else
  		{
     		$message .= '</p><p>' . lang_get('no_build_warning_part2') . '</p>';
  		}
  		
		// show info and exit
		$smarty = new TLSmarty;
		$smarty->assign('content', $message);
		$smarty->assign('link_to_op', $link_to_op);
		$smarty->assign('hint_text', $hint_text);
		$smarty->display('workAreaSimple.tpl');
		exit();
	}
}


function init_args()
{
	$_REQUEST=strings_stripSlashes($_REQUEST);
	$args = new stdClass();

	$iParams = array("feature" => array(tlInputParameter::STRING_N),
					 "tproject_id" => array(tlInputParameter::INT),
					 "tplan_id" => array(tlInputParameter::INT));
	R_PARAMS($iParams,$args);
	
	return $args;
}
?>