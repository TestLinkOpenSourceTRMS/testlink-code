<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.36 $
 * @modified $Date: 2008/11/19 21:02:58 $ $Author: schlundus $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * 20080925 - franciscom - refactoring
 * 20070106 - franciscom - give feedback is choosen a Test suite without test cases.
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();

if ($args->edit == 'testproject')
{
	// We can NOT assign/remove keywords on a whole test project
	show_instructions('keywordsAssign');
	exit();
}

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$result = null;
$keyword_assignment_subtitle = null;
$can_do = 0;
$itemID = null;

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$opt_cfg->global_lbl = '';
$opt_cfg->additional_global_lbl = null;
$opt_cfg->from->lbl = lang_get('available_kword');
$opt_cfg->to->lbl = lang_get('assigned_kword');
$opt_cfg->from->map = $tproject_mgr->get_keywords_map($args->testproject_id);
$opt_cfg->to->map = $tcase_mgr->get_keywords_map($args->id," ORDER BY keyword ASC ");

$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
//@TODO: schlundus, should be moved to init_args()
$right_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";

if ($args->edit == 'testsuite')
{
	// We are going to walk all test suites contained
	// in the selected container, and assign/remove keywords on each test case.
	$tsuite_mgr = new testsuite($db);
	$testsuite = $tsuite_mgr->get_by_id($args->id);
	$keyword_assignment_subtitle = lang_get('test_suite') . TITLE_SEP . $testsuite['name'];
	
	$tcs = $tsuite_mgr->get_testcases_deep($args->id,true);
	if (sizeof($tcs))
	{
		$can_do = 1;
		if ($args->bAssignTestSuite)
		{
			$result = 'ok';
			$a_keywords = getKeywordsArray($right_list);
			for($i = 0;$i < sizeof($tcs);$i++)
			{
				$tcID = $tcs[$i];
				$tcase_mgr->setKeywords($tcID,$a_keywords);
			}
		}
		$itemID = $tcs;
	}
}
else if($args->edit == 'testcase')
{
	$can_do = 1;
	$tcData = $tcase_mgr->get_by_id($args->id);
	if (sizeof($tcData))
	{
		$tcData = $tcData[0];
   		$keyword_assignment_subtitle = lang_get('test_case') . TITLE_SEP . $tcData['name'];
	}
	if($args->bAssignTestCase)
	{
		$result = 'ok';
		$tcase_mgr->setKeywords($args->id,getKeywordsArray($right_list));
		$itemID = $args->id;
	}
}
if ($itemID)
{
	$opt_cfg->to->map = $tcase_mgr->get_keywords_map($itemID," ORDER BY keyword ASC ");
}

keywords_opt_transf_cfg($opt_cfg, $right_list);
$smarty->assign('can_do', $can_do);
$smarty->assign('sqlResult', $result);
$smarty->assign('data', $args->id);
$smarty->assign('level', $args->edit);
$smarty->assign('opt_cfg', $opt_cfg);
$smarty->assign('keyword_assignment_subtitle',$keyword_assignment_subtitle);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

//@TODO: schlundus, should be moved to init_args()
function getKeywordsArray($right_list)
{
	$a_keywords = null;
	$list = trim($right_list);
	$bListNotEmpty = strlen($list);
	if ($bListNotEmpty)
		$a_keywords = explode(",",$list);
		
	return $a_keywords;
}


function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args = new stdClass();
    $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
    $args->keyword = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : null;
    $args->edit = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
    $args->bAssignTestCase = isset($_REQUEST['assigntestcase']) ? 1 : 0;
    $args->bAssignTestSuite = isset($_REQUEST['assigntestsuite']) ? 1 : 0;
    $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    
    return $args;
}
?>
