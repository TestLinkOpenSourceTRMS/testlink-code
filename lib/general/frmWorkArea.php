<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: frmWorkArea.php,v $
 *
 * @version $Revision: 1.25 $
 * @modified $Date: 2007/12/19 20:27:19 $ by $Author: schlundus $
 *
 * @author Martin Havlat
 *
 * This page is window for navigation and working area (eg tree + edit page).
 *
 * 20060809 - franciscom - changes in validateBuildAvailability()
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

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
$req_cfg=config_get('req_cfg');

$aa_tfp = array( 
            'editTc' => 'lib/testcases/listTestCases.php?feature=edit_tc',
            'assignReqs' => 'lib/testcases/listTestCases.php?feature=assignReqs',
            'searchTc' => 'lib/testcases/searchForm.php',
            'printTestSpec' => 'lib/results/printDocOptions.php?type=testspec',
            'keywordsAssign' => 'lib/testcases/listTestCases.php?feature=keywordsAssign',
            'testSetAdd'    => 'lib/plan/planAddTCNavigator.php',
            'testSetRemove' => 'lib/plan/planTCNavigator.php?feature=removeTC&help_topic=testSetRemove',
            'show_ve' => 'lib/plan/planTCNavigator.php?feature=show_ve',  
            'newest_tcversions' => '../../lib/plan/newest_tcversions.php',
//            'printTestSet'  => 'lib/print/selectData.php?type=testplan',
            'priority' => 'lib/plan/planTCNavigator.php?feature=plan_risk_assignment',
            'tc_exec_assignment' => 'lib/plan/planTCNavigator.php?feature=tc_exec_assignment',
            'executeTest' => 'lib/execute/execNavigator.php',
            'showMetrics' => 'lib/results/resultsNavigator.php',
            'reqSpecMgmt' => $req_cfg->module . 'reqSpecListTree.php');

$full_screen = array('newest_tcversions' => 1);

/** feature to display */
$showFeature = isset($_GET['feature']) ? $_GET['feature'] : null;
if (isset($aa_tfp[$showFeature]) === FALSE)
{
	// argument is wrong
	tLog("Wrong get argument 'feature'.", 'ERROR');
	exit();
}
// features that need to run the validate build function
if (in_array($showFeature,array('executeTest','showMetrics')))
{
  
  // BUGID 623
  // Check if for test project selected at least a test plan exist
  if( isset($_SESSION['testPlanId']) )
  {
  	validateBuildAvailability($db,$_SESSION['testPlanId'],
	                                $_SESSION['testPlanName'],
	                                $_SESSION['testprojectName']);
     
  }
  else
  {
  	redirect("../plan/planView.php");
		exit();
  }   
}

/// 1. get path from global var
/// 2. the URL made easier after setting some rules for help/instruction files
///    naming convention.
/// </enhancement>
$smarty = new TLSmarty();

if( isset($full_screen[$showFeature]) )
{
  redirect($aa_tfp[$showFeature]);
}
else
{
  $smarty->assign('treewidth', TL_FRMWORKAREA_LEFT_FRAME_WIDTH);
  $smarty->assign('treeframe', $aa_tfp[$showFeature]);
  $smarty->assign('workframe', "lib/general/show_help.php?help=$showFeature&locale={$_SESSION['locale']}");
  $smarty->display('frmInner.tpl');
}


/** 
 * 	validate that some build exists (for Test Plan related features).
 *  If no valid build is found give feedback to user and exit.
 *
 * 	@author Martin Havlat 
 *  20060809 - franciscom - check if user can create builds,
 *                          then put a link on the message page
 *                          to create link feature
 *
 **/
function validateBuildAvailability(&$db,$tpID, $tpName, $prodName)
{
	require_once("exec.inc.php");
	
	$can_create_build=has_rights($db,"testplan_create_build");
	
	$message='<p>'  . lang_get('no_build_warning_part1') . 
	          "<b> " . htmlspecialchars($tpName) . "</b>";

	if (!buildsNumber($db,$tpID))
	{	           
	  $link_to_op='';
	  $hint_text='';
	  if($can_create_build=='yes')
	  {
	     // final url will be composed adding to $basehref 
	     // (one TL variable available on smarty templates) to $link_to_op
	     $link_to_op="lib/plan/buildNew.php?do_action=create";
	     $hint_text=lang_get('create_a_build');
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

?>
