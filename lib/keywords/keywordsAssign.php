<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	keywordsAssign.php
 * @package 	TestLink
 * @copyright 	2007-2012, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal revisions
 * @since 1.9.4
 * 20120623 - franciscom -	TICKET 5074: Bulk Keyword assignment - 
 *							All keywords are assigned to the test case instead of the selected keyword
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
// $itemID = null;


// Important Development Notice
// option transfer do the magic on GUI, 
// analizing content of from->map and to->map, is able to populate
// each side as expected.
//
$opt_cfg->global_lbl = '';
$opt_cfg->additional_global_lbl = null;
$opt_cfg->from->lbl = lang_get('available_kword');
$opt_cfg->from->map = $tproject_mgr->get_keywords_map($args->testproject_id);

if ($args->edit == 'testsuite')
{
	$opt_cfg->to->lbl = lang_get('kword_to_be_assigned_to_testcases');
	$opt_cfg->to->map = null;

	// We are going to walk all test suites contained
	// in the selected container, and assign/remove keywords on each test case.
	$tsuite_mgr = new testsuite($db);
	$testsuite = $tsuite_mgr->get_by_id($args->id);
	$keyword_assignment_subtitle = lang_get('test_suite') . TITLE_SEP . $testsuite['name'];
	$tcs = $tsuite_mgr->get_testcases_deep($args->id,'only_id');
	if( ($loop2do = sizeof($tcs)) )
	{
		$can_do = 1;
		if ($args->bAssignTestSuite)
		{
			$result = 'ok';
			for($idx = 0; $idx < $loop2do; $idx++)
			{
				if(is_null($args->keywordArray))
				{	
					$tcase_mgr->deleteKeywords($tcs[$idx]);
				}
				else
				{
					$tcase_mgr->addKeywords($tcs[$idx],$args->keywordArray);
				}	
			}
		}
	}
}
else if($args->edit == 'testcase')
{
	$doRecall = true;
	$can_do = 1;
	$tcData = $tcase_mgr->get_by_id($args->id);
	if (sizeof($tcData))
	{
   		$keyword_assignment_subtitle = lang_get('test_case') . TITLE_SEP . $tcData[0]['name'];
	}
	if($args->bAssignTestCase)
	{
		$result = 'ok';
		$tcase_mgr->setKeywords($args->id,$args->keywordArray);
		$doRecall = !is_null($args->keywordArray);	
	}
	$opt_cfg->to->lbl = lang_get('assigned_kword');
	$opt_cfg->to->map = $doRecall ? $tcase_mgr->get_keywords_map($args->id,
		                              array('orderByClause' =>" ORDER BY keyword ASC ")) : null;
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
    $args->edit = $pParams["edit"];
    $args->bAssignTestCase = ($pParams["assigntestcase"] != "") ? 1 : 0;
    $args->bAssignTestSuite = ($pParams["assigntestsuite"] != "") ? 1 : 0;
    $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

    $args->keywordArray = null;
    $args->keywordList = $pParams[$rl_html_name];
    if ($args->keywordList != "")
    {	
    	$args->keywordArray = explode(",",$args->keywordList);
    }

        
    return $args;
}

function checkRights(&$db,&$user)
{
	return ($user->hasRight($db,'mgt_modify_key') && $user->hasRight($db,'mgt_view_key'));
}
?>