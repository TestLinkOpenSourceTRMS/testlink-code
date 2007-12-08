<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.27 $
 * @modified $Date: 2007/12/08 20:34:14 $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * 20070124 - franciscom
 * use show_help.php to apply css configuration to help pages
 *
 * 20070106 - franciscom - give feedback is choosen a Test suite without test cases.
 * 20061223 - franciscom - improvements on user feedback
 * 20060410 - franciscom - using option transfer
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
testlinkInitPage($db);

$template_dir = 'keywords/';

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
$keyword = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : null;
$edit = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
$bAssignTestCase = isset($_REQUEST['assigntestcase']) ? 1 : 0;
$bAssignTestSuite = isset($_REQUEST['assigntestsuite']) ? 1 : 0;
$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

if ($edit == 'testproject')
{
	  // We can NOT assign/remove keywords on a whole test project
  	redirect($_SESSION['basehref'] . "/lib/general/show_help.php?help=keywordsAssign&locale={$_SESSION['locale']}");
	exit();
}

$smarty = new TLSmarty();
$result = null;
$keyword_assignment_subtitle = null;
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$opt_cfg->global_lbl = '';
$opt_cfg->additional_global_lbl=null;

$opt_cfg->from->lbl = lang_get('available_kword');
$opt_cfg->to->lbl = lang_get('assigned_kword');
$opt_cfg->from->map = $tproject_mgr->get_keywords_map($testproject_id);
$opt_cfg->to->map = $tcase_mgr->get_keywords_map($id," ORDER BY keyword ASC ");

$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
$right_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";

if ($edit == 'testsuite')
{
	// We are going to walk all test suites contained
	// in the selected container, and assign/remove keywords on each test case.
	//
	$tsuite_mgr = new testsuite($db);
	$testsuite = $tsuite_mgr->get_by_id($id);
	$keyword_assignment_subtitle = lang_get('test_suite') . TITLE_SEP . $testsuite['name'];
	
	$tcs = $tsuite_mgr->get_testcases_deep($id,true);
	$can_do = 0;
	
	if (sizeof($tcs))
	{
		$can_do=1;
		if ($bAssignTestSuite)
		{
			$result = 'ok';
			$tcase_mgr = new testcase($db);
			$list = trim($right_list);
			$bListNotEmpty = strlen($list);
			$a_keywords = null;
			if ($bListNotEmpty)
				$a_keywords = explode(",",$list);
			for($i = 0;$i < sizeof($tcs);$i++)
			{
				$tcID = $tcs[$i];
				$tcase_mgr->deleteKeywords($tcID);
				if ($bListNotEmpty)
				{
					$tcase_mgr->addKeywords($tcID,$a_keywords);
				}
			}
		}
		$opt_cfg->to->map = $tcase_mgr->get_keywords_map($tcs," ORDER BY keyword ASC ");
	}
	else
	{
	}
}
else if($edit == 'testcase')
{
	$can_do = 1;
	$tcase_mgr = new testcase($db);
	$tcData = $tcase_mgr->get_by_id($id);
	if (sizeof($tcData))
	{
		$tcData = $tcData[0];
    	$keyword_assignment_subtitle=lang_get('test_case') . TITLE_SEP . $tcData['name'];
	}
	if($bAssignTestCase)
	{
		$result = 'ok';
		$tcase_mgr->deleteKeywords($id);   	 
		if(strlen(trim($right_list)))
		{
			$a_keywords = explode(",",$right_list);
			$tcase_mgr->addKeywords($id,$a_keywords);
		}
		$opt_cfg->to->map = $tcase_mgr->get_keywords_map($id," ORDER BY keyword ASC ");
	}
}
else
{
	tlog("keywordsAssigns> Missing GET/POST arguments.");
	exit();
}
keywords_opt_transf_cfg($opt_cfg, $right_list);

$smarty->assign('can_do', $can_do);
$smarty->assign('sqlResult', $result);
$smarty->assign('data', $id);
$smarty->assign('level', $edit);
$smarty->assign('opt_cfg', $opt_cfg);
$smarty->assign('keyword_assignment_subtitle',$keyword_assignment_subtitle);
$smarty->display($template_dir . 'keywordsAssign.tpl');
?>
