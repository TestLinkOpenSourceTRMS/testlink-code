<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsAssign.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2006/04/10 09:13:23 $
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
$bAssignComponent = isset($_REQUEST['assigncomponent']) ? 1 : 0;
$bAssignCategory = isset($_REQUEST['assigncategory']) ? 1 : 0;
$bAssignTestCase = isset($_REQUEST['assigntestcase']) ? 1 : 0;

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$smarty = new TLSmarty();
$title = null;
$result = null;
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);

$project_keys = $tproject_mgr->getKeywords($testproject_id);

$opt_cfg->js_ot_name="ot";
$opt_cfg->size=8;
$opt_cfg->style="width: 300px;";

$opt_cfg->js_events->all_right_click="";
$opt_cfg->js_events->left2right_click="";
$opt_cfg->js_events->right2left_click="";
$opt_cfg->js_events->all_left_click="";


$opt_cfg->from->lbl="from";
$opt_cfg->from->name="from_select_box";
$opt_cfg->from->map=$tproject_mgr->get_keywords_map($testproject_id);

$opt_cfg->from->id_field='id';
$opt_cfg->from->desc_field='keyword';
$opt_cfg->from->desc_glue=" ";
$opt_cfg->from->desc_html_content=true;
$opt_cfg->from->required=false;
$opt_cfg->from->show_id_in_desc=true;
$opt_cfg->from->js_events->ondblclick="";

$opt_cfg->to->lbl="to";
$opt_cfg->to->name="to_select_box";
$opt_cfg->to->map=$tcase_mgr->get_keywords_map($id);
$opt_cfg->to->show_id_in_desc=true;
$opt_cfg->to->id_field='id';
$opt_cfg->to->desc_field='keyword';
$opt_cfg->to->desc_glue=" ";
$opt_cfg->to->desc_html_content=true;
$opt_cfg->to->required=false;
$opt_cfg->to->show_id_in_desc=true;
$opt_cfg->to->js_events->ondblclick="";

$rl_html_name = $opt_cfg->js_ot_name . "_newRight";
$right_list = isset($_REQUEST[$rl_html_name])? $_REQUEST[$rl_html_name] : "";



if ($edit == 'testproject')
{
	redirect($_SESSION['basehref'] . $g_rpath['help'] . '/keywordsAssign.html');
	exit();
}
else if ($edit == 'testsuite')
{
  echo "To be developed";
  exit();
}
// 20060406 - franciscom
/*
else if ($edit == 'testsuite')
{
	if($bAssignComponent) 
		$result = updateComponentKeywords($db,$id,$keyword);

	$componentData = getComponent($db,$id);
	$title = $componentData['name'];
}
else if ($edit == 'category')
{
	if($bAssignCategory) 
		$result = updateCategoryKeywords($db,$id,$keyword);

	$categoryData = getCategory($db,$id);
	$title = $categoryData['name'];
}
*/
else if($edit == 'testcase')
{

  opt_tranf_cfg($opt_cfg, $right_list);
	
	$testCase = new testcase($db);

	$tcData = $testCase->get_by_id($id);
	if (sizeof($tcData))
	{
		$tcData = $tcData[0];
		$title = $tcData['name'];
	}
	
	if($bAssignTestCase)
	{
		$a_keywords=explode(",",$right_list);
		//echo "<pre>debug"; print_r($a_keywords); echo "</pre>";
		
		$result = $testCase->deleteKeywords($id);   	 
		$result = $result && $testCase->addKeywords($id,$a_keywords);
	}
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
$smarty->assign('arrKeys', $keysOfProduct);

$smarty->assign('opt_cfg', $opt_cfg);
$smarty->display('keywordsAssign.tpl');
?>