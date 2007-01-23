<?php
/** 
*	TestLink Open Source Project - http://testlink.sourceforge.net/ 
* 	@version $Id: testSetNavigator.php,v 1.20 2007/01/23 18:26:41 franciscom Exp $
*	@author Martin Havlat 
*
* Used in the remove test case feature
*
* 20070123 - franciscom - define moved to const.inc.php
* 20070120 - franciscom - fixed init of tplan_id.
* 20061030 - franciscom
* added management of $getArguments() - [wrongly forgetted]
*/ 	
require('../../config.inc.php');
require_once("common.php");
require_once("treeMenu.inc.php");
testlinkInitPage($db);

$workPath = null;

$tplan_mgr = new testplan($db);

// 20070120 - franciscom - 
// is possible to call this page using a Test Project that have no test plans
// in this situation the next to entries are undefined in SESSION
$tplan_id = isset($_SESSION['testPlanId']) ? intval($_SESSION['testPlanId']) : 0;
$tplan_name =isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : '';

$tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';


$keyword_id = 0;

// We only want to use in the filter, keywords present in the test cases that are
// linked to test plan, and NOT all keywords defined for test project
$keywords_map = $tplan_mgr->get_keywords_map($tplan_id, " order by keyword "); 

if(!is_null($keywords_map))
{
	$keywords_map = array( 0 => '') + $keywords_map;
}
if(isset($_POST['filter']))
{
	$keyword_id = isset($_POST['keyword_id']) ? $_POST['keyword_id'] : 0;
}


// set feature data
if ($_GET['feature'] == 'removeTC')
{
	$menuUrl = "lib/plan/testSetRemove.php";
	$title = lang_get('title_test_plan_navigator');
	$tcHide = 0;
	$help_file = "testSetRemove.html";
}
elseif ($_GET['feature'] == 'priorityAssign')
{
	$menuUrl = "lib/plan/planOwner.php";
	$title = lang_get('title_test_plan_navigator');
	$tcHide = 1;
	$help_file = "planOwnerAndPriority.html";
}
elseif ($_GET['feature'] == 'tc_exec_assignment')
{
	$menuUrl = "lib/plan/tc_exec_assignment.php";
	$title = lang_get('title_test_plan_navigator');
	$tcHide = 1;
	$help_file = "planOwnerAndPriority.html";
}
else
{
	tLog("Wrong or missing GET argument 'feature'.", 'ERROR');
	exit();
}

$getArguments='';
if ($keyword_id)
{
	$getArguments .= '&keyword_id='.$keyword_id;
}

$sMenu = generateExecTree($db,$menuUrl,$tproject_id,$tproject_name,$tplan_id,$tplan_name,
                          FILTER_BY_BUILD_OFF,$getArguments,$keyword_id,FILTER_BY_TC_OFF);

$tree = invokeMenu($sMenu);

$smarty = new TLSmarty();
$smarty->assign('treeKind', TL_TREE_KIND);
$smarty->assign('tree', $tree);
$smarty->assign('keywords_map', $keywords_map);
$smarty->assign('keyword_id', $keyword_id);

$smarty->assign('treeHeader', $title);
$smarty->assign('menuUrl',$menuUrl);
$smarty->assign('SP_html_help_file',TL_INSTRUCTIONS_RPATH . $_SESSION['locale'] ."/". $help_file);
$smarty->assign('additional_string',$tplan_name);
$smarty->display('testSetNavigator.tpl');
?>
