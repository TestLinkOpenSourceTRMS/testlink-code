<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: frmWorkArea.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $
 *
 * @author Martin Havlat
 *
 *This page is window for navigation and working area (eg tree + edit page).
 *
**/
require_once('../../config.inc.php');
require_once("common.php");
require_once('../functions/lang_api.php');
testlinkInitPage();

/** 
 * 	validate that some build exists (for Test Plan related features).
 *  If no valid build is found give feedback to user and exit.
 *
 * 	@author Martin Havlat 
 *  @author Andreas Morsing - added escaping of productNames and build identifiers
 **/
/// <enhancement date="2005-04-16" author="fm"> improved user message </<enhancement>
function validateBuildAvailability()
{
	require_once("exec.inc.php");
	
	// 20050807 - fm - to be used
	$idPlan = $_SESSION['testPlanId'];
	
	$message = '<p>' . lang_get('no_build_warning_part1') . 
	          "<b>". htmlspecialchars($_SESSION['productName']) . "::" . htmlspecialchars($_SESSION['testPlanName']) . "</b>" .
	          "  (Product::Test Plan) "  . 
	          '</p><p>' . lang_get('no_build_warning_part2') . '</p>';
		
	if (!buildsNumber())
	{	           
		// show info and exit
		$smarty = new TLSmarty;
		$smarty->assign('content', $message);
		$smarty->display('workAreaSimple.tpl');
		exit();
	}
}

/// <enhancement version="???" date="2005-04-09" author="fm" >
/// Changes a lot of if/else for configuration
/// </enhancement>
// Associative Array TreeFramePath 
// key  : feature
// value: page to lauch
$aa_tfp = array( 
            'editTc' => 'lib/testcases/listTestCases.php?feature=tcEdit',
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
if (in_array($showFeature,array('executeTest','metrics')))
	validateBuildAvailability();

/// <enhancement version="???" date="2005-04-09" author="fm" >
/// 1. get path from global var
/// 2. the URL made easier after setting some rules for help/instruction files
///    naming convention.
/// </enhancement>
$smarty = new TLSmarty();
/** default width of left pane */
$smarty->assign('treewidth', '30%');
$smarty->assign('treeframe', $aa_tfp[$showFeature]);
$smarty->assign('workframe', $g_rpath['help'] . "/{$showFeature}" . ".html");
$smarty->display('frmInner.tpl');
?>
