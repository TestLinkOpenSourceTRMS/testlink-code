<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: frmWorkArea.php,v $
 *
 * @version $Revision: 1.11 $
 * @modified $Date: 2006/03/20 18:02:27 $ by $Author: franciscom $
 *
 * @author Martin Havlat
 *
 *This page is window for navigation and working area (eg tree + edit page).
 *
 * @author Francisco Mancardi - 20050828
 * get default value for treewidth from config.inc.php DEFINE
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
$aa_tfp = array( 
            'editTc' => 'lib/testcases/listTestCases.php?feature=edit_tc',
            'assignReqs' => 'lib/testcases/listTestCases.php?feature=assignReqs',
            'searchTc' => 'lib/testcases/searchForm.php',
            'printTc' => 'lib/print/selectData.php?type=product',
            'keywordsAssign' => 'lib/testcases/listTestCases.php?feature=keywordsAssign',
            'testSetAdd'    => 'lib/plan/planAddTCNavigator.php',
            'testSetRemove' => 'lib/plan/testSetNavigator.php?feature=removeTC',
            'printTestSet'  => 'lib/print/selectData.php?type=testSet',
            'priority' => 'lib/plan/testSetNavigator.php?feature=priorityAssign',
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
$smarty->assign('workframe', $g_rpath['help'] . "/{$showFeature}" . ".html");
$smarty->display('frmInner.tpl');

/** 
 * 	validate that some build exists (for Test Plan related features).
 *  If no valid build is found give feedback to user and exit.
 *
 * 	@author Martin Havlat 
 *  @author Andreas Morsing - added escaping of productNames and build identifiers
 *
 * @author Francisco Mancardi - 20050905 - added tpID, tpName, prodName
 *
 **/
/// <enhancement date="2005-04-16" author="fm"> improved user message </<enhancement>
function validateBuildAvailability(&$db,$tpID, $tpName, $prodName)
{
	require_once("exec.inc.php");
	
	
	$message = '<p>' . lang_get('no_build_warning_part1') . 
	          "<b>". htmlspecialchars($prodName) . "::" . 
	                 htmlspecialchars($tpName) . "</b>" .
	          "  (Product::Test Plan) "  . 
	          '</p><p>' . lang_get('no_build_warning_part2') . '</p>';
		
	if (!buildsNumber($db,$tpID))
	{	           
		// show info and exit
		$smarty = new TLSmarty;
		$smarty->assign('content', $message);
		$smarty->display('workAreaSimple.tpl');
		exit();
	}
}
?>