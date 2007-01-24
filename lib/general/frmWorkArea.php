<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: frmWorkArea.php,v $
 *
 * @version $Revision: 1.15 $
 * @modified $Date: 2007/01/24 08:10:24 $ by $Author: franciscom $
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

/// <enhancement version="???" date="2005-04-09" author="fm" >
/// Changes a lot of if/else for configuration
/// </enhancement>
// Associative Array TreeFramePath 
// key  : feature
// value: page to lauch
//
// 20060908 - franciscom - added tc_exec_assignment
$aa_tfp = array( 
            'editTc' => 'lib/testcases/listTestCases.php?feature=edit_tc',
            'assignReqs' => 'lib/testcases/listTestCases.php?feature=assignReqs',
            'searchTc' => 'lib/testcases/searchForm.php',
            'printTc' => 'lib/print/selectData.php?type=testproject',
            'keywordsAssign' => 'lib/testcases/listTestCases.php?feature=keywordsAssign',
            'testSetAdd'    => 'lib/plan/planAddTCNavigator.php',
            'testSetRemove' => 'lib/plan/testSetNavigator.php?feature=removeTC',
            'printTestSet'  => 'lib/print/selectData.php?type=testSet',
            'priority' => 'lib/plan/testSetNavigator.php?feature=priorityAssign',
            'tc_exec_assignment' => 'lib/plan/testSetNavigator.php?feature=tc_exec_assignment',
            'executeTest' => 'lib/execute/execNavigator.php',
            'showMetrics' => 'lib/results/resultsNavigator.php',
            'planAssignTesters' => 'lib/plan/planTestersNavigator.php');

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
	validateBuildAvailability($db,$_SESSION['testPlanId'],
	                              $_SESSION['testPlanName'],
	                              $_SESSION['testprojectName']);
}
/// <enhancement version="???" date="2005-04-09" author="fm" >
/// 1. get path from global var
/// 2. the URL made easier after setting some rules for help/instruction files
///    naming convention.
/// </enhancement>
$smarty = new TLSmarty();

/** default width of left pane */
// 20050828 - fm
$smarty->assign('treewidth', TL_FRMWORKAREA_LEFT_FRAME_WIDTH);
$smarty->assign('treeframe', $aa_tfp[$showFeature]);
// $smarty->assign('workframe', $g_rpath['help'] . "/{$showFeature}" . ".html");
$smarty->assign('workframe', "lib/general/show_help.php?help=$showFeature&locale={$_SESSION['locale']}");

$smarty->display('frmInner.tpl');
// $smarty->display("help/" . $_SESSION['locale'] . "/{$showFeature}" . ".html");



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
	          "<b>" . htmlspecialchars($prodName) . "::" . 
	                  htmlspecialchars($tpName) . "</b>" .
	                  " (Product::Test Plan) ";

	if (!buildsNumber($db,$tpID))
	{	           
	  $link_to_op='';
	  $hint_text='';
	  if($can_create_build=='yes')
	  {
	     // final url will be composed adding to $basehref 
	     // (one TL variable available on smarty templates) to $link_to_op
	     $link_to_op="lib/plan/buildNew.php";
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