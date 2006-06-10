<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.17 $
 * @modified $Date: 2006/06/10 20:22:20 $
 *
 * Purpose:  Assign keywords to set of testcases in tree structure
 *
 * 20060410 - franciscom - using option transfer
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../testcases/archive.inc.php");
require_once("keywords.inc.php");
require_once("../functions/opt_transfer.php");

testlinkInitPage($db);

$_REQUEST = strings_stripSlashes($_REQUEST);
$id = isset($_REQUEST['data']) ? intval($_REQUEST['data']) : null;
$keyword = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : null;
$edit = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : null;
$bAssignTestCase = isset($_REQUEST['assigntestcase']) ? 1 : 0;

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$smarty = new TLSmarty();
$title = null;
$result = null;
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$project_keys = $tproject_mgr->getKeywords($testproject_id);

$opt_cfg = opt_transf_empty_cfg();
$opt_cfg->js_ot_name = 'ot';
$opt_cfg->global_lbl = lang_get('title_assign_kw_to_tc');
$opt_cfg->from->lbl = lang_get('available_kword');
$opt_cfg->from->map = $tproject_mgr->get_keywords_map($testproject_id);
$opt_cfg->to->lbl = lang_get('assigned_kword');
$opt_cfg->to->map = $tcase_mgr->get_keywords_map($id," ORDER BY keyword ASC ");

$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
$right_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";

if ($edit == 'testproject')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/keywordsAssign.html');
	exit();
}
else if ($edit == 'testsuite')
{
	/*
	* SCHLUNDUS: not yes finished, later...
	$tsuite = new testsuite($db);
	$tcs = $tsuite->get_testcases_deep($id);
	if (sizeof($tcs))
	{
		if ($bAssignTestCase)
		{
			$result = 'ok';
			$tcase_mgr = new testcase($db);
			$list = trim($right_list);
			$bListEmpty = strlen($list);
			$a_keywords = null;
			if (!$bListEmpty)
				$a_keywords = explode(",",$list);
			for($i = 0;$i < sizeof($tcs);$i++)
			{
				$tcID = $tcs[$i]['id'];
				$tcase_mgr->deleteKeywords($id);   	 
				if (!$bListEmpty)
				{
					$tcase_mgr->addKeywords($tcID,$a_keywords);				
				}
			}
			$opt_cfg->to->map = $tcase_mgr->get_keywords_map($tcID," ORDER BY keyword ASC ");
		}
	}*/
	echo "To be developed";
	exit();
}
else if($edit == 'testcase')
{
	$tcase_mgr = new testcase($db);
	$tcData = $tcase_mgr->get_by_id($id);
	if (sizeof($tcData))
	{
		$tcData = $tcData[0];
		$title = $tcData['name'];
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
	
	keywords_opt_transf_cfg($opt_cfg, $right_list);
}
else
{
	tlog("keywordsAssigns> Missing GET/POST arguments.");
	exit();
}

$smarty->assign('sqlResult', $result);
$smarty->assign('data', $id);
$smarty->assign('level', $edit);
$smarty->assign('title',$title);
$smarty->assign('opt_cfg', $opt_cfg);
$smarty->display('keywordsAssign.tpl');
?>