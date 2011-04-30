<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	keywordsAssign.php
 * @package 	TestLink
 * @copyright 	2005-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 *
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("opt_transfer.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$args = init_args($opt_cfg);
checkRights($db,$_SESSION['currentUser'],$args);


$gui = new stdClass();
$gui->tproject_id = $args->tproject_id;
$gui->can_do = 0;
$gui->keyword_assignment_subtitle = null;
$gui->sqlResult = null;

if ($args->edit == 'testproject')
{
	// We can NOT assign/remove keywords on a whole test project
	show_instructions('keywordsAssign');
	exit();
}

$smarty = new TLSmarty();
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$itemID = null;

$opt_cfg->global_lbl = '';
$opt_cfg->additional_global_lbl = null;
$opt_cfg->from->lbl = lang_get('available_kword');
$opt_cfg->to->lbl = lang_get('assigned_kword');
$opt_cfg->from->map = $tproject_mgr->get_keywords_map($args->tproject_id);
$opt_cfg->to->map = $tcase_mgr->get_keywords_map($args->id," ORDER BY keyword ASC ");

if ($args->edit == 'testsuite')
{
	// We are going to walk all test suites contained
	// in the selected container, and assign/remove keywords on each test case.
	$tsuite_mgr = new testsuite($db);
	$testsuite = $tsuite_mgr->get_by_id($args->id);
	$gui->keyword_assignment_subtitle = lang_get('test_suite') . TITLE_SEP . $testsuite['name'];
	$tcs = $tsuite_mgr->get_testcases_deep($args->id,'only_id');
	if(	($loop2do = sizeof($tcs) )
	{
		$gui->can_do = 1;
		if ($args->bAssignTestSuite)
		{
			$gui->sqlResult = 'ok';
			for($idx = 0;$idx < $loop2do; $idx++)
			{
				$tcID = $tcs[$idx];
				$tcase_mgr->setKeywords($tcID,$args->keywordArray);
			}
		}
		$itemID = $tcs;
	}
}
else if($args->edit == 'testcase')
{
	$gui->can_do = 1;
	$tcData = $tcase_mgr->get_by_id($args->id);
	if (sizeof($tcData))
	{
		$tcData = $tcData[0];
   		$gui->keyword_assignment_subtitle = lang_get('test_case') . TITLE_SEP . $tcData['name'];
	}
	if($args->bAssignTestCase)
	{
		$gui->sqlResult = 'ok';
		$tcase_mgr->setKeywords($args->id,$args->keywordArray);
		$itemID = $args->id;
	}
}
if ($itemID)
{
	$opt_cfg->to->map = $tcase_mgr->get_keywords_map($itemID," ORDER BY keyword ASC ");
}

$gui->level = $args->edit;
$gui->id = $args->id;

keywords_opt_transf_cfg($opt_cfg, $args->keywordList);

$smarty->assign('gui', $gui);
$smarty->assign('opt_cfg', $opt_cfg);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args(&$opt_cfg)
{
	$_REQUEST=strings_stripSlashes($_REQUEST);
	$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
	
    $iParams = array("id" => array(tlInputParameter::INT_N),
					 "edit" => array(tlInputParameter::STRING_N,0,100),
					 "assigntestcase" => array(tlInputParameter::STRING_N,0,1),
    				 "assigntestsuite" => array(tlInputParameter::STRING_N,0,1),
    				 $rl_html_name => array(tlInputParameter::STRING_N),
    				 "tproject_id" => array(tlInputParameter::INT_N));
		
	$pParams = R_PARAMS($iParams);
    
	$args = new stdClass();
    $args->id = $pParams["id"];
    $args->tproject_id = $pParams["tproject_id"];

    $args->keywordArray = null;
    $args->keywordList = $pParams[$rl_html_name];
    $args->edit = $pParams["edit"];
    $args->bAssignTestCase = ($pParams["assigntestcase"] != "") ? 1 : 0;
    $args->bAssignTestSuite = ($pParams["assigntestsuite"] != "") ? 1 : 0;
    if ($args->keywordList != "")
    {
    	$args->keywordArray = explode(",",$args->keywordList);
    }
    return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('mgt_modify_key','mgt_view_key'),'and');
}
?>