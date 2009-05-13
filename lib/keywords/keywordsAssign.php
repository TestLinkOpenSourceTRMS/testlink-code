<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.41 $
 * @modified $Date: 2009/05/13 19:30:18 $ $Author: schlundus $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$args = init_args($opt_cfg);

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

$opt_cfg->global_lbl = '';
$opt_cfg->additional_global_lbl = null;
$opt_cfg->from->lbl = lang_get('available_kword');
$opt_cfg->to->lbl = lang_get('assigned_kword');
$opt_cfg->from->map = $tproject_mgr->get_keywords_map($args->testproject_id);
$opt_cfg->to->map = $tcase_mgr->get_keywords_map($args->id," ORDER BY keyword ASC ");

if ($args->edit == 'testsuite')
{
	// We are going to walk all test suites contained
	// in the selected container, and assign/remove keywords on each test case.
	$tsuite_mgr = new testsuite($db);
	$testsuite = $tsuite_mgr->get_by_id($args->id);
	$keyword_assignment_subtitle = lang_get('test_suite') . TITLE_SEP . $testsuite['name'];
	$tcs = $tsuite_mgr->get_testcases_deep($args->id,'only_id');
	if (sizeof($tcs))
	{
		$can_do = 1;
		if ($args->bAssignTestSuite)
		{
			$result = 'ok';
			for($i = 0;$i < sizeof($tcs);$i++)
			{
				$tcID = $tcs[$i];
				$tcase_mgr->setKeywords($tcID,$args->keywordArray);
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
		$tcase_mgr->setKeywords($args->id,$args->keywordArray);
		$itemID = $args->id;
	}
}
if ($itemID)
{
	$opt_cfg->to->map = $tcase_mgr->get_keywords_map($itemID," ORDER BY keyword ASC ");
}

keywords_opt_transf_cfg($opt_cfg, $args->keywordList);
$smarty->assign('can_do', $can_do);
$smarty->assign('sqlResult', $result);
$smarty->assign('data', $args->id);
$smarty->assign('level', $args->edit);
$smarty->assign('opt_cfg', $opt_cfg);
$smarty->assign('keyword_assignment_subtitle',$keyword_assignment_subtitle);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args(&$opt_cfg)
{
	$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
	
    $iParams = array(
			"id" => array(tlInputParameter::INT_N),
			"edit" => array(tlInputParameter::STRING_N,0,100),
			"assigntestcase" => array(tlInputParameter::STRING_N,0,1),
    		"assigntestsuite" => array(tlInputParameter::STRING_N,0,1),
    		$rl_html_name => array(tlInputParameter::STRING_N),
    );
		
	$pParams = R_PARAMS($iParams);
    
	$args = new stdClass();
    $args->id = $pParams["id"];
    $args->keywordArray = null;
    $args->keywordList = $pParams[$rl_html_name];
    if ($args->keywordList != "")
    	$args->keywordArray = explode(",",$args->keywordList);
    
    $args->edit = $pParams["edit"];
    $args->bAssignTestCase = ($pParams["assigntestcase"] != "") ? 1 : 0;
    $args->bAssignTestSuite = ($pParams["assigntestsuite"] != "") ? 1 : 0;
    $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
        
    return $args;
}

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'mgt_modify_key') && $user->hasRight($db,'mgt_view_key'));
}
?>
